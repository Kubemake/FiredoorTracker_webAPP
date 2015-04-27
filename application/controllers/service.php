<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Service extends CI_Controller {

	function __construct()
	{
		parent::__construct();
		$this->load->model('service_model');
	}
	
	function index()
	{
		redirect('/user/login');
	}
	
	function videopage()
	{
		$this->load->model('info_model');
		$data['videos']	  = $this->info_model->get_all_videos();
		$this->load->view('mobile/mobile_videos', $data);
	}

	function faqpage()
	{
		$this->load->model('info_model');
		$data['faqs'] = $this->info_model->get_all_faq();
		$this->load->view('mobile/mobile_faq', $data);
	}


	/*
	 * Main function to dispatch API requests
	 * It redirect api call for specified function
	 *
	 * Input data:
	 * type => kind of request api
	 * other parameters depending of api type request
	 */
	function dispatcher()
	{
		// if (!$this->input->post() && !$this->input->get())  		//DEBUG
		$data = (array)json_decode($this->input->get('json')); 	//DEBUG
		if (empty($data))										//DEBUG
		{
		
			$postdata = @file_get_contents("php://input");
			if (empty($postdata))
				$this->_show_output(array('status' => 'error', 'error' => 'no data'));
			
			$data = (array)json_decode($postdata); //name of JSON post data
		}
		
			

		if ($data['type']!='auth' && $data['type']!='restore' ) //check token if not login api
		{
			if (!isset($data['token']) or empty($data['token']))
				$this->_show_output(array('status' => 'error', 'error' => 'empty token parameter'));
			
			$tokendata = $this->service_model->get_token_info($data['token']);
			
			if (empty($tokendata)) 
				$this->_show_output(array('status' => 'error', 'error' => 'wrong token'));
			
			if (strtotime($tokendata['expires']) < time())
				$this->_show_output(array('status' => 'error', 'error' => 'token expired'));
	
			$data['tokendata'] = $tokendata;
		}

		$func = '_exec_function_' . $data['type']; //exec request api type
		
	    call_user_func(array($this,$func), $data);
	}

	/*
	 * Common function for show request result
	 */
	function _show_output($outputtext)
	{
		header("Content-Type: application/json; charset=utf-8");
		if (!is_array($outputtext)) $outputtext = array($outputtext);
		die(json_encode($outputtext));
	}

	/*
	 * Upload file acceptor
	 *
	 * Input data:
	 * token 			=> user token
	 * file 			=> file data
	 * file_type 		=> type of file image or video, image by default
	 * file_name 		=> file name for display
	 * file_descr 		=> file description
	 * aperture_id		=> aperture_id
	 * idFormFields		=> Q or A id (fieldId in DB)
	 * inspection_id 	=> instead if aperture_id
	 *
	 * Output data:
	 * status 	=> ok
	 * file_id 	=> id uploaded
	 */
	function upload()
	{
		$this->load->model('media_model');
	
		$postdata = $this->input->post();
// file_put_contents($_SERVER['DOCUMENT_ROOT'].'/upload/post', json_encode($postdata));
// $this->_show_output(array('status' => 'error', 'error' => 'debug'));
		if (empty($postdata))
			$this->_show_output(array('status' => 'error', 'error' => 'no POST data'));

		if (!isset($postdata['token']) or empty($postdata['token']))
			$this->_show_output(array('status' => 'error', 'error' => 'no token data'));
		
		$tokendata = $this->service_model->get_token_info($postdata['token']);
		
		if (empty($tokendata)) 
			$this->_show_output(array('status' => 'error', 'error' => 'wrong token'));
		
		if (strtotime($tokendata['expires']) < time())
			$this->_show_output(array('status' => 'error', 'error' => 'token expired'));

		if (!$_FILES)
			$this->_show_output(array('status' => 'error', 'error' => 'empty uploaded file'));
		
		if (empty($postdata['file_name']))
			$this->_show_output(array('status' => 'error', 'error' => 'empty file name'));
		
		if (
			isset($postdata['idFormFields']) && 
			$postdata['idFormFields'] > 0 && 
			(
				(
					!isset($postdata['aperture_id']) or 
					empty($postdata['aperture_id']) or 
					$postdata['aperture_id']==0
				) &&
				(
					!isset($postdata['inspection_id']) or 
					empty($postdata['inspection_id']) or 
					$postdata['inspection_id']==0
				)
			)
		)
			$this->_show_output(array('status' => 'error', 'error' => 'get idFormFields but empty aperture_id'));

		// $name = $_FILES['file']['name'];
		// $ext = substr($name, -4);
		$ext = '.png';
		// $name = substr($name, 0, -4);
		$name = translate($_FILES['file']['name']);
		$creation_time = time();

		$upload_dir = '/upload/' . $tokendata['user_id'];

		if (!is_dir($_SERVER['DOCUMENT_ROOT'] . $upload_dir)) 
			mkdir($_SERVER['DOCUMENT_ROOT'] . $upload_dir);

		$file_path = $upload_dir . '/' . $name . '_' . $creation_time . $ext;
		

		if (move_uploaded_file($_FILES['file']['tmp_name'], $_SERVER['DOCUMENT_ROOT'] . $file_path)) 
		{
			$adddata = array(
				'Users_idUsers'  => $tokendata['user_id'],
				'path' 			 => base_url($file_path),
				'name'			 => $postdata['file_name'],
				'description' 	 => empty($postdata['file_descr']) ? '' : $postdata['file_descr'],
				'type' 			 => (isset($postdata['file_type']) && !empty($postdata['file_type'])) ? $postdata['file_type'] : 'image',
				'FileUploadDate' => date('Y-m-d H:i:s', $creation_time)
			);

			$fileid = $this->media_model->add_uploaded_file($adddata);

			$this->load->library('History_library');
			
			$this->history_library->saveFiles(array('user_id' => $tokendata['user_id'], 'line_id' => $fileid, 'new_val' => json_encode($adddata), 'type' => 'add'));

			$aperture_id = FALSE;
			$idFormFields = FALSE;
			$images = array();

			if ((isset($postdata['aperture_id']) && $postdata['aperture_id'] > 0) or (isset($postdata['inspection_id']) && $postdata['inspection_id'] > 0))
			{
				$aperture_id = @$postdata['aperture_id'];
				
				if (isset($postdata['inspection_id']) && $postdata['inspection_id'] > 0)
				{
					$aperture_id = $this->service_model->get_aperture_id_by_inspection_id($postdata['inspection_id']);
					$aperture_id = $aperture_id['idAperture'];
				}

				if (isset($postdata['idFormFields']) && $postdata['idFormFields'] > 0)
					$idFormFields = $postdata['idFormFields'];

				$iff = $this->media_model->add_aperture_file($fileid, $aperture_id, $idFormFields);

				$this->history_library->saveIff(array('user_id' => $tokendata['user_id'], 'line_id' => $iff, 'new_val' => json_encode(array('Doors_idDoors' => $aperture_id, 'Files_idFiles' => $fileid, 'FormFields_idFormFields' => $idFormFields)), 'type' => 'add'));

				$imgs = $this->service_model->get_images_by_aperture_id_and_field_id($aperture_id, $idFormFields);

				foreach ($imgs as $image)
				{
					$images[] = $image['path'];
					// $images[] = array('file_id'	=> $image['file_id'],'url'		=> $image['path']);
				}

			}
			else
				$images[] = base_url($file_path);
				// $images[] = array('file_id'	=> $fileid,'url'		=> base_url($file_path));

			$this->_show_output(array('status' => 'ok', 'images' => $images, 'aperture_id' => $aperture_id ? $aperture_id : '', 'idFormFields' => $idFormFields ? $idFormFields : ''));
		}
	}


	/*
	 * User password recovery function
	 * send new user password by email
	 *
	 * Input data:
	 * login => user email
	 *
	 * Output data:
	 * status => ok
	 */
	function _exec_function_restore($data)
	{
		if (!isset($data['login']) or empty($data['login']))
			$this->_show_output(array('status' => 'error', 'error' => 'empty login'));
		
		$this->load->model('user_model');

		$userdata = $this->user_model->get_user_info_by_email($data['login']);

		if (empty($userdata))
			$this->_show_output(array('status' => 'error', 'error' => 'wrong login'));

		$this->load->helper('string');
		
		$pass = random_string('alnum', 10);
		
		$this->user_model->update_user_data($userdata['idUsers'], array('password' => pass_crypt($pass)));
		$this->user_model->delete_user_tokens($userdata['idUsers']);
		
		$ans = 'Your new account details:<br>';
		$ans .= 'Login: ' . $data['login'] . '<br>';
		$ans .= 'Password: ' . $pass . '<br>';

		send_mail($data['login'], 'Recovery user password', $ans);
		
		$this->_show_output(array('status' => 'ok'));
	}

	/*
	 * User authorization function
	 	 *
	 * Input data:
	 * login    => user email
	 * password => user password
	 *
	 * Output data:
	 * status => ok
	 * token  => user auth id required for other requests
	 */
	function _exec_function_auth($data)
	{
		if (!isset($data['login']) or empty($data['login']))
			$this->_show_output(array('status' => 'error', 'error' => 'empty login'));

		if (!isset($data['password']) or empty($data['password'])) 
			$this->_show_output(array('status' => 'error', 'error' => 'empty password'));
		
		$userdata = $this->service_model->get_user_data_by_login_password($data['login'], pass_crypt($data['password']));

		if (empty($userdata))
			$this->_show_output(array('status' => 'error', 'error' => 'wrong login or password'));

		$token = md5(uniqid($data['login'], true));

		$this->service_model->delete_user_token($userdata['idUsers']);

		$this->service_model->set_user_token($userdata['idUsers'], $token);

		$this->_show_output(array('status' => 'ok', 'token' => $token));
	}

	/*
	 * Get user profile data
	 *
	 * Input data:
	 * token    => auth id from login
	 *
	 * Output data:
	 * status => ok
	 * all profile fields data
	 */
	function _exec_function_get_profile_data($data)
	{
		$user_id = $data['tokendata']['user_id'];

		$this->load->model('user_model');

		$userData = $this->user_model->get_user_info_by_user_id($user_id);

		$userData['status'] = 'ok';

		$this->_show_output($userData);
	}

	/*
	 * Update user profile data
	 *
	 * Input data:
	 * token    => auth id from login
	 * fields which need to be changed
	 *
	 * Output data:
	 * status => ok
	 * all profile fields data (with updated fields)
	 */
	function _exec_function_update_profile_data($data)
	{
		$user_id = $data['tokendata']['user_id'];

		unset($data['type'], $data['token'], $data['tokendata']); //remove waste data

		$this->load->model('user_model');

		if (empty($data['password']))
			unset($data['password']);
		else
		{
			$data['password'] = pass_crypt($data['password']);
			$this->user_model->delete_user_tokens($user_id);
		}

		if (empty($data))
			$this->_show_output(array('status' => 'error', 'error' => 'not isset update parameters'));

		$this->load->library('History_library');
		
		$this->history_library->saveUsers(array('user_id' => $user_id, 'line_id' =>  $user_id, 'new_val' => json_encode($data), 'type' => 'edit'));

		$this->user_model->update_user_data($user_id, $data);
		
		$userData = $this->user_model->get_user_info_by_user_id($user_id);

		$userData['status'] = 'ok';

		$this->_show_output($userData);
	}

	/*
	 *
	 * DEPRECATED!!!!
	 * Get version of issues list
	 *
	 * Input data:
	 * token    => auth id from login
	  *
	 * Output data:
	 * status => ok
	 * version => current version of issues list
	 */
	function _exec_function_get_issues_version($data)
	{
		$userData['version'] = $this->service_model->get_issues_version();
		$userData['version'] = $userData['version']['value'];

		$userData['status'] = 'ok';

		$this->_show_output($userData);
	}

	/*
	 * DEPRECATED!!!!
	 * Get issues list
	 *
	 * Input data:
	 * token    => auth id from login
	 *
	 * Output data:
	 * status => ok
	 * issues => issues list
	 */
	function _exec_function_get_issues_list($data)
	{
		$userData['issues'] = $this->service_model->get_issues_list();

		$userData['status'] = 'ok';

		$this->_show_output($userData);
	}
	
	/*
	 * Get inspections list by user
	 *
	 * Input data:
	 * token    => auth id from login
	 * keyword  => key filter for filtering inspections list
	 *
	 * Output data:
	 * status => ok
	 * inspections => inspections list
	 */
	function _exec_function_get_inspection_list_by_user($data)
	{
		$user_id = $data['tokendata']['user_id'];

		$this->load->model('resources_model');
		$this->load->model('user_model');

		$keyword = (isset($data['keyword']) && !empty($data['keyword'])) ? $data['keyword'] : '';
		$keyword = strtolower($keyword);
		
		$user = $this->user_model->get_user_info_by_user_id($user_id);

		$userData['inspections'] = $this->resources_model->get_user_inspections_by_parent($user['parent']);//($user_id);

		if (!empty($userData['inspections']))
		{
			$output = array();

			foreach ($userData['inspections'] as $inspection)
			{
				if (!empty($keyword) && //for search
					strpos(strtolower($inspection['barcode']), $keyword) === FALSE &&
					strpos(strtolower($inspection['location_name']), $keyword) === FALSE &&
					strpos(strtolower($inspection['firstName']), $keyword) === FALSE &&
					strpos(strtolower($inspection['lastName']), $keyword) === FALSE &&
					strpos(strtolower($inspection['CreatorfirstName']), $keyword) === FALSE &&
					strpos(strtolower($inspection['CreatorlastName']), $keyword) === FALSE &&
					strpos($inspection['CreateDate'], $keyword) === FALSE &&
					strpos($inspection['StartDate'], $keyword) === FALSE &&
					strpos($inspection['Completion'], $keyword) === FALSE &&
					strpos(strtolower($inspection['InspectionStatus']), $keyword) === FALSE &&
					strpos($inspection['id'], $keyword) === FALSE
				) {
					continue;
				}

				if (!isset($output[$inspection['aperture_id']]))
					$output[$inspection['aperture_id']] = $inspection;
				elseif ($output[$inspection['aperture_id']]['revision'] < $inspection['revision'])
					$output[$inspection['aperture_id']] = $inspection;
			}
		
			$userData['inspections'] = $output;

			
			$inspections_images = array();
			$allimgs = $this->service_model->get_images_by_aperture_id_and_field_id(array_keys($userData['inspections']));
			if (!empty($allimgs))
			{
				foreach ($allimgs as $image)
				{
					$inspections_images[$image['aperture_id']][] = $image['path'];
					// $inspections_images[$image['aperture_id']][] = array('file_id'	=> $image['file_id'],'url'		=> $image['path']);
				}
			
			}			
			

			foreach ($userData['inspections'] as &$inspection) {

				$building = $this->resources_model->get_building_name_by_building_id($inspection['building_id']);

				$anwers = $this->service_model->get_inspection_answers($inspection['id'], $inspection['aperture_id'], $user_id);
				
				$color = array();
				foreach ($anwers as $answer)
					$color[$answer['value']] = 1;

				if (isset($color[1]) && count($color) > 1)
					unset($color[1]);

				$colorresult = array();
				foreach ($color as $key => $value)
					$colorresult[] = $key;
				
				$inspection['colorcode'] = $colorresult;
				$inspection['building_name'] = @$building['building_name'];
				$inspection['images'] = isset($inspections_images[$inspection['aperture_id']]) ? $inspections_images[$inspection['aperture_id']] : array();
			}
		}

		$userData['status'] = 'ok';

		$this->_show_output($userData);
	}
	
	/*
	 * Update inspection status
	 *
	 * Input data:
	 * token    		=> auth id from login
	 * inspection_id 	=> inspection id
	 *
	 * Output data:
	 * status => ok
	 */
	function _exec_function_set_inspection_confirmation($data)
	{
		if (!isset($data['inspection_id']) or empty($data['inspection_id']))
		{
			$userData['status'] = 'error';
			$userData['error'] = 'not isset or empty input parameter inspection_id';
			$this->_show_output($userData);
		}

		$user_id = $data['tokendata']['user_id'];

		$total_status_answ = $this->service_model->get_all_questions_with_status_answers($data['inspection_id']);

		$total_curent_status_answ = $this->service_model->get_curent_questions_with_status_answers($data['inspection_id'], $user_id);

		$this->load->model('resources_model');

		$upddata = array();

		$upddata['InspectionStatus'] = ($total_status_answ==$total_curent_status_answ) ? 'Complete' : 'In Progress';
		$upddata['Completion'] 		 = date('Y-m-d');

		$this->load->library('History_library');
		
		$this->history_library->saveInspections(array('user_id' => $user_id, 'line_id' => $data['inspection_id'], 'new_val' => json_encode($upddata), 'type' => 'edit'));

		$this->resources_model->update_inspection($data['inspection_id'], $upddata);

		$userData['status'] = 'ok';
		$this->_show_output($userData);
	}	

	/*
	 * Get glossary non-empty letters
	 *
	 * Input data:
	 * token    => auth id from login
	 *
	 * Output data:
	 * status => ok
	 * letters => non-empty glossary letters
	 */
	function _exec_function_get_glossary_letters($data)
	{
		$this->load->model('info_model');
		
		$glossary_letters = $this->info_model->get_all_glossary_letters();

		$userData['letters'] = array();
		if (!empty($glossary_letters))
		{
			foreach ($glossary_letters as $let)
				$userData['letters'][] = $let['letter'];
		}
		
		$userData['status'] = 'ok';

		$this->_show_output($userData);
	}

	/*
	 * Get glossary terms by letter
	 *
	 * Input data:
	 * token    => auth id from login
	 * letter   => letter for makeing output
	 *
	 * Output data:
	 * status => ok
	 * terms => termin list for letter
	 */
	function _exec_function_get_terms_by_letter($data)
	{
		$this->load->model('info_model');
		

		$userData['status'] = 'ok';
		$data['letter'] = isset($data['letter']) ? $data['letter'] : FALSE;
		$userData['terms']  = $this->info_model->get_glossary_by_letter($data['letter']);

		$this->_show_output($userData);
	}

	/*
	 * Get glossary terms by letter
	 *
	 * Input data:
	 * token    => auth id from login
	 * needle   => peace of text for searching terms
	 *
	 * Output data:
	 * status => ok
	 * terms => termin list for search needle
	 */
	function _exec_function_search_glossary_terms($data)
	{
		$this->load->model('info_model');
		

		$userData['status'] = 'ok';
		$userData['terms']  = $this->info_model->search_glossary_terms($data['needle']);

		$this->_show_output($userData);
	}

	/*
	 * Get contact an expert list
	 *
	 * Input data:
	 * token    => auth id from login
	 *
	 * Output data:
	 * status => ok
	 * experts => contact an expert list
	 */
	function _exec_function_get_experts($data)
	{
		$this->load->model('info_model');

		$userData['status'] = 'ok';
		$userData['experts']  = $this->info_model->get_experts_list();
		
		foreach ($userData['experts'] as &$expert)
			$expert['logo'] = base_url($expert['logo']);

		$this->_show_output($userData);
	}

	/*
	 * Get aperture overview info
	 *
	 * Input data:
	 * token    	=> auth id from login
	 * aperture_id  => aperture id
	 *
	 * Output data:
	 * status => ok
	 * info => array of parameters and selected values
	 */
	function _exec_function_get_aperture_overview_info($data)
	{
		if (!isset($data['aperture_id']) or empty($data['aperture_id']))
		{
			$userData['status'] = 'error';
			$userData['error'] = 'not isset or empty input parameter aperture_id';
			$this->_show_output($userData);
		}
		
		$userData['status'] = 'ok';
		$userData['info']  = $this->service_model->get_aperture_info_and_selected($data['aperture_id']);

		$this->_show_output($userData);
	}

	/*
	 * Get aperture issues and update overview info
	 *
	 * Input data:
	 * token    		=> auth id from login
	 * inspection_id  	=> inspection id
	 * wall_rating  	=> wall rating aperture value
	 * smoke_rating 	=> smoke rating aperture value
	 * material  		=> material aperture value
	 * rating  			=> rating aperture value
	 *
	 * Output data:
	 * status => ok
	 * tabs => list of issues tabs 
	 * issues => list of issues according input data
	 */
	function _exec_function_get_aperture_issues($data)
	{
		// {"type":"get_aperture_issues","token":"ffa54203ce3c46ec3c12dd7adf892dd5","inspection_id":"3","wall_Rating":"Smoke Wall","smoke_Rating":"Smoke Rated Door","material":"Aluminum","rating":"45"}
		if (!isset($data['inspection_id']) or empty($data['inspection_id']))
		{
			$userData['status'] = 'error';
			$userData['error'] = 'not isset or empty input parameter inspection_id';
			$this->_show_output($userData);
		}
		
		if (!isset($data['wall_Rating']) or empty($data['wall_Rating']))
		{
			$userData['status'] = 'error';
			$userData['error'] = 'not isset or empty input parameter wall_Rating';
			$this->_show_output($userData);
		}

		if (!isset($data['smoke_Rating']) or empty($data['smoke_Rating']))
		{
			$userData['status'] = 'error';
			$userData['error'] = 'not isset or empty input parameter smoke_Rating';
			$this->_show_output($userData);
		}

		if (!isset($data['material']) or empty($data['material']))
		{
			$userData['status'] = 'error';
			$userData['error'] = 'not isset or empty input parameter material';
			$this->_show_output($userData);
		}

		if (!isset($data['rating']) or (empty($data['rating']) && $data['rating']===0))
		{
			$userData['status'] = 'error';
			$userData['error'] = 'not isset or empty input parameter rating';
			$this->_show_output($userData);
		}

		$wall_rating 	= array_flip($this->config->item('wall_rates'));
		$smoke_rating 	= array_flip($this->config->item('rates_types'));
		$material 		= array_flip($this->config->item('door_matherial'));
		$rating 		= array_flip($this->config->item('door_rating'));

		$user_id = $data['tokendata']['user_id'];
		
		$data['wall_Rating']  = $wall_rating[$data['wall_Rating']];
		$data['smoke_Rating'] = $smoke_rating[$data['smoke_Rating']];
		$data['rating'] 	  = $rating[$data['rating']];
		$data['material'] 	  = $material[$data['material']];

		$upddata 					= $data; //save selected overview parameters
		unset($upddata['type'], $upddata['token'], $upddata['tokendata'], $upddata['inspection_id']); //remove waste data

		$this->load->library('History_library');
		
		$this->history_library->saveDoors(array('user_id' => $user_id, 'iid' => $data['inspection_id'], 'new_val' => json_encode($upddata), 'type' => 'edit'));

		$result = $this->service_model->update_aperture_overview_info($data['inspection_id'], $upddata, $user_id);

		$result = $this->service_model->get_aperture_issues_and_selected($data);

		ksort($result['tabs']);
		$out = array();
		foreach ($result['tabs'] as $tab)
			$out[] = $tab;
		$result['tabs'] = $out;

		$userData['status'] = 'ok';
		$userData['issues'] = $result['issues'];
		$userData['tabs'] = $result['tabs'];
		
		$this->_show_output($userData);
	}


	/*
	 * Update inspection data
	 *
	 * Input data:
	 * token    		=> auth id from login
	 * inspection_id  	=> aperture id
	 * idFormFields  	=> Answer id
	 * status 		 	=> Answer status
	 * selected 	 	=> value of selected field
	 *
	 * Output data:
	 * status => ok
	 */
	function _exec_function_update_inspection_data($data)
	{
		// {"type":"update_inspection_data","token":"ffa54203ce3c46ec3c12dd7adf892dd5","inspection_id":"3","idFormFields":"5","selected":"1"}
		if (!isset($data['inspection_id']) or empty($data['inspection_id']))
		{
			$userData['status'] = 'error';
			$userData['error'] = 'not isset or empty input parameter inspection_id';
			$this->_show_output($userData);
		}
		
		if (!isset($data['idFormFields']) or empty($data['idFormFields']))
		{
			$userData['status'] = 'error';
			$userData['error'] = 'not isset or empty input parameter idFormFields';
			$this->_show_output($userData);
		}

		if (!isset($data['selected']))
		{
			$userData['status'] = 'error';
			$userData['error'] = 'not isset input parameter selected';
			$this->_show_output($userData);
		}

		if (!isset($data['status']))
		{
			$userData['status'] = 'error';
			$userData['error'] = 'not isset input parameter status';
			$this->_show_output($userData);
		}

		$this->load->model('resources_model');

		$userData['status'] = 'error';

		$field 		= $data['idFormFields'];
		$inspection = $data['inspection_id'];
		$user 		= $data['tokendata']['user_id'];
		$value 		= $data['selected'];
		$status  	= $data['status'];

		$this->load->library('History_library');

		$cur_dff = $this->history_library->get_cur_dff($inspection, $field, $user);

		$this->service_model->delete_inspection_data($inspection, $field, $user); //del current answ record


		if (!empty($data['selected']) && $data['selected'] != 'NO') //if not unselect action
		{
			$ansvers = $this->service_model->get_question_answers_by_answer_id_and_inspection_id($field, $inspection); //get all answers from this answer question 

			if ($status > 1 ) //remove all compliant answers
			{
				foreach ($ansvers as $answer)
				{
					if ($answer['status'] == 1)
						$this->service_model->delete_inspection_data($inspection, $answer['idFormFields'], $user);
				}
						
			}
			elseif ($status == 1) //if user send compliant answer - remove all non-compliant
			{
				foreach ($ansvers as $answer)
				{
					if ($answer['status'] > 1)
						$this->service_model->delete_inspection_data($inspection, $answer['idFormFields'], $user);
				}
			}
			$dffid = $this->service_model->add_inspection_data($inspection, $field, $user, $value);

			$this->history_library->saveDff(array('user_id' => $user, 'line_id' => $dffid, 'new_val' => json_encode(array('Inspections_idInspections' => $inspection, 'FormFields_idFormFields' => $field, 'Users_idUsers' => $user, 'value' => $value)), 'cur_val' => json_encode($cur_dff)));

			if ($dffid)
				$this->resources_model->update_inspection($data['inspection_id'], array('InspectionStatus' => 'In Progress', 'Inspector' => $user));

		}

		$userData['status'] = 'ok';

		$this->_show_output($userData);
	}

	/* DEPRECATED!!!
	 * Get client locations tree
	 *
	 * Input data:
	 * token    		=> auth id from login
	 *
	 * Output data:
	 * status 	=> ok
	 * tree 	=> locations tree array
	 */
	function _exec_function_get_locations_tree($data)
	{
		$this->load->model('user_model');
		$this->load->model('resources_model');

		$user_id = $data['tokendata']['user_id'];

		$user = $this->user_model->get_user_info_by_user_id($user_id);

		$user_buildings	= $this->resources_model->get_user_buildings($user['parent']);

		$userData['status'] = 'ok';
		$userData['tree'] = $user_buildings;

		$this->_show_output($userData);
	}

	/* DEPRECATED!!!
	 * Get apertures by location id
	 *
	 * Input data:
	 * token    		=> auth id from login
	 * location_id  	=> location id
	 *
	 * Output data:
	 * status 		=> ok
	 * apertures	=> apertures array
	 */
	function _exec_function_get_apertures_by_location_id($data)
	{
		if (!isset($data['location_id']) or empty($data['location_id']))
		{
			$userData['status'] = 'error';
			$userData['error'] = 'not isset or empty input parameter location_id';
			$this->_show_output($userData);
		}

		$this->load->model('user_model');
		$this->load->model('resources_model');

		$user_id = $data['tokendata']['user_id'];

		$user = $this->user_model->get_user_info_by_user_id($user_id);

		$apertures = $this->resources_model->get_user_apertures($data['location_id'], $user['parent']);

		$userData['status'] 	= 'ok';
		$userData['apertures'] 	= $apertures;

		$this->_show_output($userData);
	}

	/*
	 * Add Inspection
	 *
	 * Input data:
	 * token    		=> auth id from login
	 * barcode 			=> QR or barcode of door
	 * StartDate 		=> StartDate in table
	 * location_id 		=> Buildings_idBuildings in table
	 * summary			=> review description
	 *
	 * Output data:
	 * status 		=> ok
	 */
	function _exec_function_add_inspection($data)
	{
		if (!isset($data['barcode']) or empty($data['barcode']))
		{
			$userData['status'] = 'error';
			$userData['error'] = 'not isset or empty input parameter barcode';
			$this->_show_output($userData);
		}
		
/*		if (!isset($data['StartDate']) or empty($data['StartDate']))
		{
			$userData['status'] = 'error';
			$userData['error'] = 'not isset or empty input parameter StartDate';
			$this->_show_output($userData);
		}*/

		if (!isset($data['location_id']) or empty($data['location_id']))
		{
			$userData['status'] = 'error';
			$userData['error'] = 'not isset or empty input parameter location_id';
			$this->_show_output($userData);
		}

		$this->load->model('user_model');
		$this->load->model('resources_model');
		
		$user_id = $data['tokendata']['user_id'];

		$user = $this->user_model->get_user_info_by_user_id($user_id);
		
		$aperture = $this->resources_model->get_aperture_info_by_barcode($data['barcode']);

		$this->load->library('History_library');

		if (empty($aperture))
		{
			$adddata = array(
				'barcode' 					=> $data['barcode'],
				'Buildings_idBuildings' 	=> $data['location_id'],
				'UserId' 					=> $user['parent'],
				'name'						=> $data['barcode']
			);
			$aperture_id = $this->resources_model->add_aperture($adddata);
					
			$this->history_library->saveDoors(array('user_id' => $user_id, 'line_id' => $aperture_id, 'new_val' => json_encode($adddata), 'type' => 'add'));
		}
		else
		{
			$aperture_id = $aperture['idDoors'];

			$available_review = $this->resources_model->get_client_inspection_by_aperture_id($aperture_id, $user['parent']);

			if (!empty($available_review)) {
				$userData['status'] = 'error';
				$userData['error'] = 'review allready exist';
				$this->_show_output($userData);
			}
		}

		$adddata = array(
			'idAperture' 				=> $aperture_id,
			'Buildings_idBuildings' 	=> $data['location_id'],
			'InspectionStatus' 			=> 'New',
			'Inspector' 				=> $user_id,
			'Creator'	 				=> $user_id,
			'CreateDate' 				=> date('Y-m-d'),
			'UserId' 					=> $user['parent'],
			'summary' 					=> @$data['summary']
		);

		$iid = $this->resources_model->add_inspection($adddata);
		
		$this->history_library->saveInspections(array('user_id' => $user_id, 'line_id' => $iid, 'new_val' => json_encode($adddata), 'type' => 'add'));

		$userData['status'] 	= 'ok';
		
		$adddata['id'] = $iid;

		$userData['CreatedInspection'] 	= $adddata;

		$this->_show_output($userData);
	}

	/*
	 * Check door Unique ID
	 *
	 * Input data:
	 * token    		=> auth id from login
	 * barcode 			=> QR or scancode of door in 1-6 digit format
	 *
	 * Output data:
	 * status 			=> ok if inspection not present
	 * case 			=> new unique id OR inspection allready exists OR door UID is present
	 * location			=> one or more location if door is present or new door
	 */
	function  _exec_function_check_door_uid($data)
	{
		if (!isset($data['barcode']) or empty($data['barcode']))
		{
			$userData['status'] = 'error';
			$userData['error'] = 'not isset or empty input parameter barcode';
			$this->_show_output($userData);
		}

		$this->load->model('user_model');
		$this->load->model('resources_model');
		$user_id = $data['tokendata']['user_id'];

		$aperture = $this->resources_model->get_aperture_info_by_barcode($data['barcode']);

		$user = $this->user_model->get_user_info_by_user_id($user_id);
		
		$userData['location'] 	= $this->resources_model->get_user_buildings($user['parent']);;

		$buildings = array();
		foreach ($userData['location'] as $loc)
			$buildings[$loc['idBuildings']] = $loc;
		$userData['location'] = $buildings;
		
		if (empty($aperture))
		{
			$userData['status'] 	= 'ok';
			$userData['case'] 		= 'new unique id';
			
			$this->_show_output($userData);
		}

		$buildings = array();
		$buildings[0] = $userData['location'][$aperture['Buildings_idBuildings']];
		$buildings[1] = $userData['location'][$buildings[0]['root']];
		$userData['location'] = $buildings;

		$inspection = $this->resources_model->get_inspection_by_aperture_id($aperture['idDoors']);
		if (!empty($inspection))
		{
			$userData['status'] = 'ok';
			$userData['case'] 	= 'inspection allready exists';
			
			$this->_show_output($userData);
		}
		

		$userData['status'] 		= 'ok';
		$userData['case'] 			= 'door UID is present';

		$this->_show_output($userData);

	}
}

/* End of file service.php */
/* Location: ./application/controllers/service.php */