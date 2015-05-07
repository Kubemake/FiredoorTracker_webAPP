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

		$userlocation 	= $this->resources_model->get_user_buildings($user['parent']);
		$buildings = array();
		foreach ($userlocation as $loc)
			$buildings[$loc['idBuildings']] = $loc;
		$userlocation = $buildings;

		$userData['inspections'] = $this->resources_model->get_user_inspections_by_parent($user['parent']);//($user_id);

		if (!empty($userData['inspections']))
		{
			$output = array();

			//make building and location names 
			foreach ($userData['inspections'] as &$inspection)
			{
				$inspection['building_name'] = @$userlocation[$inspection['Building']]['name'];
				
				$inspection['location_name'] = array();
				if ($inspection['Floor'] > 0 && isset($userlocation[$inspection['Floor']]['name']))
					$inspection['location_name'][] = $userlocation[$inspection['Floor']]['name'];
				if ($inspection['Wing'] > 0 && isset($userlocation[$inspection['Wing']]['name']))
					$inspection['location_name'][] = $userlocation[$inspection['Wing']]['name'];
				if ($inspection['Area'] > 0 && isset($userlocation[$inspection['Area']]['name']))
					$inspection['location_name'][] = $userlocation[$inspection['Area']]['name'];
				if ($inspection['Level'] > 0 && isset($userlocation[$inspection['Level']]['name']))
					$inspection['location_name'][] = $userlocation[$inspection['Level']]['name'];
				
				$inspection['location_name'] = (!empty($inspection['location_name'])) ? implode(' ', $inspection['location_name']) : '';
			}

			//filter inspection if keyword
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

				//show only last revision
				if (!isset($output[$inspection['aperture_id']]))
					$output[$inspection['aperture_id']] = $inspection;
				elseif ($output[$inspection['aperture_id']]['revision'] < $inspection['revision'])
					$output[$inspection['aperture_id']] = $inspection;
			}
		
			//add images to inspection
			$inspections_images = array();
			$allimgs = $this->service_model->get_images_by_aperture_id_and_field_id(array_keys($userData['inspections']));
			if (!empty($allimgs))
			{
				foreach ($allimgs as $image)
					$inspections_images[$image['aperture_id']][] = $image['path'];
			}			
			

			//make answers list
			foreach ($userData['inspections'] as &$inspection)
			{
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
				$inspection['images'] = isset($inspections_images[$inspection['aperture_id']]) ? $inspections_images[$inspection['aperture_id']] : array();
			}

			$output = array();
			foreach ($userData['inspections'] as $value) //make array for return
				$output[] = $value;
			$userData['inspections'] = $output;
		}

		$userData['status'] = 'ok';
		
		// echo '<pre>';
		// print_r($userData);die();

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
	 * olddata  	=> marker, if presend means that need refresh locations fields
	 * newdata  	=> to compare with olddata for refresh locations fields
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
		
		$this->load->model('user_model');
		$this->load->model('resources_model');

		$user_id = $data['tokendata']['user_id'];

		$user = $this->user_model->get_user_info_by_user_id($user_id);

		$doorval = $this->service_model->get_aperture_info_and_selected($data['aperture_id']);
		
		//get buildings
		foreach ($this->resources_model->get_user_buildings_root($user['parent']) as $value)
		{
			$buildings[$value['idBuildings']] = $value['name'];
			$buildings_values[] = $value['name'];
		}

		$building = (!empty($doorval['Building']) && $doorval['Building'] != 0 && isset($buildings[$doorval['Building']])) ? $buildings[$doorval['Building']] : $buildings_values[0];
		
		if (isset($data['olddata']['info']['Location']) && isset($data['newdata']['info']['Location']))
		{
			$newdata = $data['newdata']['info']['Location'];
			$olddata = $data['olddata']['info']['Location'];

			$building = $newdata[0]['selected'];
		}
		$locatio[] = array('name' => 'Building', 'label' => 'Building', 'selected' => $building, 'type' => 'enum', 'values' => $buildings_values, 'forcerefresh' => 1);

		//get floors
		$buildings = array_flip($buildings);

		foreach ($this->resources_model->get_user_buildings_by_building_parent($buildings[$building], $user['parent']) as $value)
		{
			$floors[$value['idBuildings']] = $value['name'];
			$floors_values[] = $value['name'];
		}

		if (isset($floors_values))
		{
			if (isset($olddata) && isset($newdata))
				$floor = ($olddata[0]['selected'] == $newdata[0]['selected']) ? $newdata[1]['selected'] : $floors_values[0];
			else
				$floor = (!empty($doorval['Floor']) && $doorval['Floor'] != 0 && isset($floors[$doorval['Floor']])) ? $floors[$doorval['Floor']] : $floors_values[0];
			
			$locatio[] = array('name' => 'Floor', 'label' => 'Floor', 'selected' => $floor, 'type' => 'enum', 'values' => $floors_values, 'forcerefresh' => 1);

			//get wing
			$floors = array_flip($floors);

			foreach ($this->resources_model->get_user_buildings_by_building_parent($floors[$floor], $user['parent']) as $value)
			{
				$wings[$value['idBuildings']] = $value['name'];
				$wings_values[] = $value['name'];
			}

			if (isset($wings_values))
			{
				if (isset($olddata) && isset($newdata))
					$wing = ($olddata[1]['selected'] == $newdata[1]['selected']) ? $newdata[2]['selected'] : $wings_values[0];
				else
					$wing = (!empty($doorval['Wing']) && $doorval['Wing'] != 0 && isset($wings[$doorval['Wing']])) ? $wings[$doorval['Wing']] : $wings_values[0];
				
				$locatio[] = array('name' => 'Wing', 'label' => 'Wing', 'selected' => $wing, 'type' => 'enum', 'values' => $wings_values, 'forcerefresh' => 1);

				//get area
				$wings = array_flip($wings);

				foreach ($this->resources_model->get_user_buildings_by_building_parent($wings[$wing], $user['parent']) as $value)
				{
					$areas[$value['idBuildings']] = $value['name'];
					$areas_values[] = $value['name'];
				}

				if (isset($areas_values))
				{
					if (isset($olddata) && isset($newdata))
						$area = ($olddata[2]['selected'] == $newdata[2]['selected']) ? $newdata[3]['selected'] : $areas_values[0];
					else
						$area = (!empty($doorval['Area']) && $doorval['Area'] != 0 && isset($areas[$doorval['Area']])) ? $areas[$doorval['Area']] : $areas_values[0];
					
					$locatio[] = array('name' => 'Area', 'label' => 'Area', 'selected' => $area, 'type' => 'enum', 'values' => $areas_values, 'forcerefresh' => 1);

					//get level
					$areas = array_flip($areas);

					foreach ($this->resources_model->get_user_buildings_by_building_parent($areas[$area], $user['parent']) as $value)
					{
						$levels[$value['idBuildings']] = $value['name'];
						$levels_values[] = $value['name'];
					}

					if (isset($levels_values))
					{
						if (isset($olddata) && isset($newdata))
							$level = ($olddata[3]['selected'] == $newdata[3]['selected']) ? $newdata[4]['selected'] : $levels_values[0];
						else
							$level = (!empty($doorval['Level']) && $doorval['Level'] != 0 && isset($levels[$doorval['Level']])) ? $levels[$doorval['Level']] : $levels_values[0];
						
						$locatio[] = array('name' => 'Level', 'label' => 'Level', 'selected' => $level, 'type' => 'enum', 'values' => $levels_values, 'forcerefresh' => 1);

						
					}
					
				}

			}
		}
		
		$IntExt = $this->service_model->get_enum_values('Doors', 'IntExt');
		$locatio[] = array('name'  	=> 'IntExt',    'label' 	=> 'Interior / Exterior?',   'selected' => (!empty($doorval['IntExt']) && $doorval['IntExt'] != 0) ? $doorval['IntExt'] : $IntExt[0],  				'type' => 'enum', 'values' => $IntExt, 'forcerefresh' => 0);

		$userData['info']['Location'] = $locatio;

		foreach ($this->config->item('wall_rates') as $value)
			$wall_rating[] = $value;
		foreach ($this->config->item('rates_types') as $value)
			$smoke_rating[] = $value;
		foreach ($this->config->item('door_matherial') as $value)
			$material[] = $value;
		foreach ($this->config->item('door_rating') as $value)
			$rating[] = $value;
		
		$others[] = array('name' => 'wall_Rating',  'label' => 'Wall Rating',	'selected' => (!empty($doorval['wall_Rating']) && $doorval['wall_Rating'] != 0) ? $wall_rating[$doorval['wall_Rating']] : $wall_rating[0], 	 	'type' => 'enum', 'values' => $wall_rating,  'forcerefresh' => 0);
		$others[] = array('name' => 'smoke_Rating', 'label' => 'Smoke Rating',	'selected' => (!empty($doorval['smoke_Rating']) && $doorval['smoke_Rating'] != 0) ? $smoke_rating[$doorval['smoke_Rating']] : $smoke_rating[0], 'type' => 'enum', 'values' => $smoke_rating, 'forcerefresh' => 0);
		$others[] = array('name' => 'material',     'label' => 'Material', 		'selected' => (!empty($doorval['material']) && $doorval['material'] != 0) ? $material[$doorval['material']] : $material[0], 	  		 	 	'type' => 'enum', 'values' => $material, 	 'forcerefresh' => 0);
		$others[] = array('name' => 'rating', 		'label' => 'Rating', 		'selected' => (!empty($doorval['rating']) && $doorval['rating'] != 0) ? $rating[$doorval['rating']] : $rating[0], 	  				 	 		'type' => 'enum', 'values' => $rating, 		 'forcerefresh' => 0);
		$others[] = array('name' => 'width', 		'label' => 'Width', 		'selected' => (!empty($doorval['width']) && $doorval['width'] != 0) ? $doorval['width'] : '',		  				 			 				'type' => 'string', 'forcerefresh' => 0);
		$others[] = array('name' => 'height', 		'label' => 'Height', 		'selected' => (!empty($doorval['height']) && $doorval['height'] != 0) ? $doorval['height'] : '', 	  				 			 				'type' => 'string', 'forcerefresh' => 0);
		
		$door_type = $this->service_model->get_enum_values('Doors', 'door_type');
		$vision_Light_Present = $this->service_model->get_enum_values('Doors', 'vision_Light_Present');
		$vision_Light = $this->service_model->get_enum_values('Doors', 'vision_Light');
		$singage = $this->service_model->get_enum_values('Doors', 'singage');
		$auto_Operator = $this->service_model->get_enum_values('Doors', 'auto_Operator');
		$others[] = array('name' => 'door_type', 			'label' => 'Door Type',				'selected' => (!empty($doorval['door_type']) && $doorval['door_type'] != 0) ? $doorval['door_type'] : $door_type[0],												'type' => 'enum', 'values' => $door_type, 			 'forcerefresh' => 0);
		$others[] = array('name' => 'vision_Light_Present', 'label' => 'Vision Light Present?',	'selected' => (!empty($doorval['vision_Light_Present']) && $doorval['vision_Light_Present'] != 0) ? $doorval['vision_Light_Present'] : $vision_Light_Present[0],	'type' => 'enum', 'values' => $vision_Light_Present, 'forcerefresh' => 0);
		$others[] = array('name' => 'vision_Light', 		'label' => 'Vision Light',			'selected' => (!empty($doorval['vision_Light']) && $doorval['vision_Light'] != 0) ? $doorval['vision_Light'] : $vision_Light[0],									'type' => 'enum', 'values' => $vision_Light, 		 'forcerefresh' => 0);
		$others[] = array('name' => 'singage', 				'label' => 'Signage',				'selected' => (!empty($doorval['singage']) && $doorval['singage'] != 0) ? $doorval['singage'] : $singage[0],														'type' => 'enum', 'values' => $singage, 			 'forcerefresh' => 0);
		$others[] = array('name' => 'auto_Operator', 		'label' => 'Auto Operator',			'selected' => (!empty($doorval['auto_Operator']) && $doorval['auto_Operator'] != 0) ? $doorval['auto_Operator'] : $auto_Operator[0],								'type' => 'enum', 'values' => $auto_Operator, 		 'forcerefresh' => 0);
		
		$userData['info']['Others'] = $others;

		$doorLabel_Type			= $this->service_model->get_enum_values('Doors', 'doorLabel_Type');
		$doorLabel_Time			= $this->service_model->get_enum_values('Doors', 'doorLabel_Time');
		$doorLabel_Testing_Lab	= $this->service_model->get_enum_values('Doors', 'doorLabel_Testing_Lab');
		$doorLabel_Manufacturer	= $this->service_model->get_enum_values('Doors', 'doorLabel_Manufacturer');
		$doorLabel_Min_Latch	= $this->service_model->get_enum_values('Doors', 'doorLabel_Min_Latch');
		$doorLabel_Temp_Rise	= $this->service_model->get_enum_values('Doors', 'doorLabel_Temp_Rise');
		$doorLabel[] = array('name' => 'doorLabel_serial',		 	'label' => 'Serial #',						'selected' => (!empty($doorval['doorLabel_serial']) && $doorval['doorLabel_serial'] != 0) ? $doorval['doorLabel_serial'] : '',						'type' => 'string');
		$doorLabel[] = array('name' => 'doorLabel_Type',		 	'label' => 'Type',							'selected' => (!empty($doorval['doorLabel_Type']) && $doorval['doorLabel_Type'] != 0) ? $doorval['doorLabel_Type'] : $doorLabel_Type[0],							 	 'type' => 'enum', 'values' => $doorLabel_Type, 		'forcerefresh' => 0);
		$doorLabel[] = array('name' => 'doorLabel_Time',		 	'label' => 'Time',							'selected' => (!empty($doorval['doorLabel_Time']) && $doorval['doorLabel_Time'] != 0) ? $doorval['doorLabel_Time'] : $doorLabel_Time[0],							 	 'type' => 'enum', 'values' => $doorLabel_Time, 		'forcerefresh' => 0);
		$doorLabel[] = array('name' => 'doorLabel_Testing_Lab',	 	'label' => 'Testing Lab',					'selected' => (!empty($doorval['doorLabel_Testing_Lab']) && $doorval['doorLabel_Testing_Lab'] != 0) ? $doorval['doorLabel_Testing_Lab'] : $doorLabel_Testing_Lab[0], 	 'type' => 'enum', 'values' => $doorLabel_Testing_Lab, 	'forcerefresh' => 0);
		$doorLabel[] = array('name' => 'doorLabel_Manufacturer', 	'label' => 'Manufacturer',					'selected' => (!empty($doorval['doorLabel_Manufacturer']) && $doorval['doorLabel_Manufacturer'] != 0) ? $doorval['doorLabel_Manufacturer'] : $doorLabel_Manufacturer[0], 'type' => 'enum', 'values' => $doorLabel_Manufacturer, 'forcerefresh' => 0);
		$doorLabel[] = array('name' => 'doorLabel_Min_Latch',	 	'label' => 'Min. Latch Throw Requirement',	'selected' => (!empty($doorval['doorLabel_Min_Latch']) && $doorval['doorLabel_Min_Latch'] != 0) ? $doorval['doorLabel_Min_Latch'] : $doorLabel_Min_Latch[0],			 'type' => 'enum', 'values' => $doorLabel_Min_Latch, 	'forcerefresh' => 0);
		$doorLabel[] = array('name' => 'doorLabel_Temp_Rise',	 	'label' => 'Temp. Rise Requirement',		'selected' => (!empty($doorval['doorLabel_Temp_Rise']) && $doorval['doorLabel_Temp_Rise'] != 0) ? $doorval['doorLabel_Temp_Rise'] : $doorLabel_Temp_Rise[0],			 'type' => 'enum', 'values' => $doorLabel_Temp_Rise, 	'forcerefresh' => 0);
		$userData['info']['Door Label'] = $doorLabel;

		$frameLabel_Type			= $this->service_model->get_enum_values('Doors', 'frameLabel_Type');
		$frameLabel_Time			= $this->service_model->get_enum_values('Doors', 'frameLabel_Time');
		$frameLabel_Testing_Lab		= $this->service_model->get_enum_values('Doors', 'frameLabel_Testing_Lab');
		$frameLabel_Manufacturer	= $this->service_model->get_enum_values('Doors', 'frameLabel_Manufacturer');
		$frameLabel_Min_Latch		= $this->service_model->get_enum_values('Doors', 'frameLabel_Min_Latch');
		$frameLabel_Temp_Rise		= $this->service_model->get_enum_values('Doors', 'frameLabel_Temp_Rise');
		$frameLabel_Number_Doors	= $this->service_model->get_enum_values('Doors', 'frameLabel_Number_Doors');
		$frameLabel[] = array('name' => 'frameLabel_serial',		'label' => 'Serial #',						'selected' => (!empty($doorval['frameLabel_serial']) && $doorval['frameLabel_serial'] != 0) ? $doorval['frameLabel_serial'] : '',					'type' => 'string');
		$frameLabel[] = array('name' => 'frameLabel_Type',			'label' => 'Type',							'selected' => (!empty($doorval['frameLabel_Type']) && $doorval['frameLabel_Type'] != 0) ? $doorval['frameLabel_Type'] : $frameLabel_Type[0],								 'type' => 'enum', 'values' => $frameLabel_Type, 		 'forcerefresh' => 0);
		$frameLabel[] = array('name' => 'frameLabel_Time',			'label' => 'Time',							'selected' => (!empty($doorval['frameLabel_Time']) && $doorval['frameLabel_Time'] != 0) ? $doorval['frameLabel_Time'] : $frameLabel_Time[0],								 'type' => 'enum', 'values' => $frameLabel_Time, 		 'forcerefresh' => 0);
		$frameLabel[] = array('name' => 'frameLabel_Testing_Lab',	'label' => 'Testing Lab',					'selected' => (!empty($doorval['frameLabel_Testing_Lab']) && $doorval['frameLabel_Testing_Lab'] != 0) ? $doorval['frameLabel_Testing_Lab'] : $frameLabel_Testing_Lab[0],	 'type' => 'enum', 'values' => $frameLabel_Testing_Lab,  'forcerefresh' => 0);
		$frameLabel[] = array('name' => 'frameLabel_Manufacturer',	'label' => 'Manufacturer',					'selected' => (!empty($doorval['frameLabel_Manufacturer']) && $doorval['frameLabel_Manufacturer'] != 0) ? $doorval['frameLabel_Manufacturer'] : $frameLabel_Manufacturer[0], 'type' => 'enum', 'values' => $frameLabel_Manufacturer, 'forcerefresh' => 0);
		$frameLabel[] = array('name' => 'frameLabel_Min_Latch',		'label' => 'Min. Latch Throw Requirement',	'selected' => (!empty($doorval['frameLabel_Min_Latch']) && $doorval['frameLabel_Min_Latch'] != 0) ? $doorval['frameLabel_Min_Latch'] : $frameLabel_Min_Latch[0],			 'type' => 'enum', 'values' => $frameLabel_Min_Latch, 	 'forcerefresh' => 0);
		$frameLabel[] = array('name' => 'frameLabel_Temp_Rise',		'label' => 'Temp. Rise Requirement',		'selected' => (!empty($doorval['frameLabel_Temp_Rise']) && $doorval['frameLabel_Temp_Rise'] != 0) ? $doorval['frameLabel_Temp_Rise'] : $frameLabel_Temp_Rise[0],			 'type' => 'enum', 'values' => $frameLabel_Temp_Rise, 	 'forcerefresh' => 0);
		$frameLabel[] = array('name' => 'frameLabel_Number_Doors',	'label' => 'Number of Doors',				'selected' => (!empty($doorval['frameLabel_Number_Doors']) && $doorval['frameLabel_Number_Doors'] != 0) ? $doorval['frameLabel_Number_Doors'] : $frameLabel_Number_Doors[0], 'type' => 'enum', 'values' => $frameLabel_Number_Doors, 'forcerefresh' => 0);
		$userData['info']['Frame Label'] = $frameLabel;

		$userData['status'] = 'ok';

		// echo '<pre>';
		// print_r($userData);die();	
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
		
		$data['width'] = preg_replace('@[^\d\.]+@si', '', $data['width']);
		if (!isset($data['width']) or empty($data['width']) or $data['width']===0)
		{
			$userData['status'] = 'error';
			$userData['error'] = 'not isset or empty input parameter width';
			$this->_show_output($userData);
		}

		$data['height'] = preg_replace('@[^\d\.]+@si', '', $data['height']);
		if (!isset($data['height']) or empty($data['height']) or $data['height']===0)
		{
			$userData['status'] = 'error';
			$userData['error'] = 'not isset or empty input parameter height';
			$this->_show_output($userData);
		}

		if (!isset($data['Building']) or (empty($data['Building']) && $data['Building']===0))
		{
			$userData['status'] = 'error';
			$userData['error'] = 'not isset or empty input parameter Building';
			$this->_show_output($userData);
		}

		$wall_rating 	= array_flip($this->config->item('wall_rates'));
		$smoke_rating 	= array_flip($this->config->item('rates_types'));
		$material 		= array_flip($this->config->item('door_matherial'));
		$rating 		= array_flip($this->config->item('door_rating'));

		$user_id = $data['tokendata']['user_id'];
		
		//make right data for save for some fields
		$data = $this->_make_locations_data($data);
		$data['wall_Rating']  = $wall_rating[$data['wall_Rating']];
		$data['smoke_Rating'] = $smoke_rating[$data['smoke_Rating']];
		$data['rating'] 	  = $rating[$data['rating']];
		$data['material'] 	  = $material[$data['material']];

		$upddata 					= $data; //save selected overview parameters
		unset($upddata['type'], $upddata['token'], $upddata['tokendata'], $upddata['inspection_id']); //remove waste data

		$this->load->library('History_library');
		
		$this->history_library->saveDoors(array('user_id' => $user_id, 'iid' => $data['inspection_id'], 'new_val' => json_encode($upddata), 'type' => 'edit'));

		$this->service_model->update_aperture_overview_info($data['inspection_id'], $upddata, $user_id);

		$result = $this->service_model->get_aperture_issues_and_selected($data);

		/*spec code for signs*/
		if ($result['addbtnq'] > 0)
		{
			$signs = $result['issues'][$result['addbtnq']]['answers'];
			$square = 0; //total signs square
			foreach ($signs as &$sign)
			{
				if (!isset($nextq))
					$nextq = $sign['nextQuestionId'];
				$sign['status'] = 1;
				if (strlen($sign['selected']) > 0)
				{
					$dim = explode(',',$sign['selected']);
					$square += trim(@$dim[0]) * trim(@$dim[1]);
				}
				else
					unset($signs[$sign['idFormFields']]);
			}

			$signsize = 0;
			if ($square > 0)
				$signsize = $square * 100 / ($data['width']*$data['height']);
			
			if (count($signs) > 0 && $signsize > 5)
				foreach ($signs as &$value)
					$value['status'] = 4;
	
			//add sign btn
			$signs['789789'] = array(
				'idFormFields' => '789789',
                'type' => 'answer',
                'nextQuestionId' => $nextq,
                'name' => 'AddSignBtn',
                'label' => 'Add Sign',
                'questionId' => '78',
                'questionOrder' => count($signs)+1,
                'status' => '',
                'selected' => ''
			);
			$result['issues'][$result['addbtnq']]['answers'] = $signs;
		}
		/*END spec code for signs*/

		ksort($result['tabs']);
		$out = array();
		foreach ($result['tabs'] as $tab)
			$out[] = $tab;
		$result['tabs'] = $out;

		$userData['status'] = 'ok';
		$userData['issues'] = $result['issues'];
		$userData['tabs'] = $result['tabs'];

		// echo '<pre>';
		// print_r($userData);die();

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
			if ($data['idFormFields'] != 789789)
				$answers = $this->service_model->get_question_answers_by_answer_id_and_inspection_id($field, $inspection); //get all answers from this answer question 

			if (isset($data['Special']) && $data['Special'] != 'null')
			{
				
				$answers = $this->service_model->get_question_answers_by_question_id_and_inspection_id($data['Special'], $inspection); //get all answers from this answer question 
				
				$apert_id = $this->service_model->get_aperture_id_by_inspection_id($inspection);
				$apertredata = $this->service_model->get_aperture_info_and_selected($apert_id['idAperture']);

				$square = 0; //total signs square

				//sort
				$out = array();
				foreach ($answers as $answer)
				{
					$answer['status'] = 1;
					$out[$answer['questionOrder']] = $answer;
					if (!isset($nextq))
						$nextq = $answer['nextQuestionId'];
					if (!isset($qid))
						$qid = $answer['questionId'];

					if ($answer['idFormFields'] == $field && $value == '0,0')
						$answer['selected'] = '';
					
					if (strlen($answer['selected']) > 0)
					{
						$dim = explode(',',$answer['selected']);
						$square += trim(@$dim[0]) * trim(@$dim[1]);
					}
				}
				ksort($out);
				$answers = $out;
				
				//add new if btn add pressed
				if ($data['idFormFields'] == 789789)
				{
					if (strlen($value) > 0)
					{
						$dim = explode(',',$value);
						$square += trim(@$dim[0]) * trim(@$dim[1]);
					}

					foreach ($out as $key => $answer)
					{
						if (!empty($answer['selected']))
							continue;
						$answers[$key]['selected'] = $value;
						$field = $answers[$key]['idFormFields'];
						break;
					}
				}

				//calc size of signs
				$signsize = 0;
				if ($square > 0)
					$signsize = $square * 100 / ($apertredata['width']['selected']*$apertredata['height']['selected']);
				
				//make answers array
				$out = array();
				foreach ($answers as $answer)
				{
					if (!empty($answer['selected']))
					{
						if ($signsize > 5) //if sizeof signs > 5% make it replace
							$answer['status'] = 4;
						$out[] = $answer;
					}
				}
				$answers = $out;
			
				// add add btn
				$answers[] = array(
					'idFormFields' => '789789',
	                'type' => 'answer',
	                'nextQuestionId' => $nextq,
	                'name' => 'AddSignBtn',
	                'label' => 'Add Sign',
	                'questionId' => $qid,
	                'questionOrder' => count($answers)+1,
	                'status' => '',
	                'selected' => ''
				);
		
				$userData['answers'] = $answers;

				$dffid = 0;
			}
			elseif ($status > 1 ) //remove all compliant answers
			{
				foreach ($answers as $answer)
				{
					if ($answer['status'] == 1)
						$this->service_model->delete_inspection_data($inspection, $answer['idFormFields'], $user);
				}
			}
			elseif ($status == 1) //if user send compliant answer - remove all non-compliant
			{
				foreach ($answers as $answer)
				{
					if ($answer['status'] > 1)
						$this->service_model->delete_inspection_data($inspection, $answer['idFormFields'], $user);
				}
			}
			if ($data['Special'] != 'null' && $value != '0,0')
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
	 * location_id 		=> Buildings_idBuildings in table DEPRECATED!!
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
		
		$this->load->model('user_model');
		$this->load->model('resources_model');
		
		$user_id = $data['tokendata']['user_id'];

		$user = $this->user_model->get_user_info_by_user_id($user_id);

		//for compatability with old version
		if (isset($data['location_id']) && !empty($data['location_id']))
		{
			$lvls = array(
				0 => 'Building',
				1 => 'Floor',
				2 => 'Wing',
				3 => 'Area',
				4 => 'Level'
			);

			$userlocation 	= $this->resources_model->get_user_buildings($user['parent']);;
			$buildings = array();
			foreach ($userlocation as $loc)
				$buildings[$loc['idBuildings']] = $loc;
			$userlocation = $buildings;

			$lv = $userlocation[$data['location_id']]['level'];
			$apert_adddata[$lvls[$lv]] = $data['location_id'];
			$nextid = $userlocation[$data['location_id']]['parent'];
			
			for ($i=$lv-1; $i >=0 ; $i--)
			{ 
				$apert_adddata[$lvls[$i]] = $nextid;
				$nextid = $userlocation[$data['location_id']]['parent'];
			}
		}

		$aperture = $this->resources_model->get_aperture_info_by_barcode($data['barcode'], $user['parent']);

		$this->load->library('History_library');

		//if new aperture add it
		if (empty($aperture))
		{
			$apert_adddata['barcode'] = $data['barcode'];
			$apert_adddata['UserId'] 	= $user['parent'];
			$apert_adddata['name']	= $data['barcode'];

			$aperture_id = $this->resources_model->add_aperture($apert_adddata);
					
			$this->history_library->saveDoors(array('user_id' => $user_id, 'line_id' => $aperture_id, 'new_val' => json_encode($apert_adddata), 'type' => 'add'));
		}
		else $aperture_id = $aperture['idDoors'];

		$available_review = $this->resources_model->get_client_inspection_by_aperture_id($aperture_id, $user['parent']);
		
		//if aperture present and inspection present
		if (!empty($available_review))
		{
			$available_review['id'] = $available_review['idInspections'];
			unset($available_review['Completion'], $available_review['StartDate'], $available_review['revision'], $available_review['Buildings_idBuildings'], $available_review['idInspections'], $available_review['deleted']);

			$userData['CreatedInspection'] 	= $available_review;
			$userData['status'] = 'ok';
			$this->_show_output($userData);
		}
		
		//if only new inspection

		$adddata['idAperture'] 		 = $aperture_id;
		$adddata['InspectionStatus'] = 'New';
		$adddata['Inspector'] 		 = $user_id;
		$adddata['Creator']	 		 = $user_id;
		$adddata['CreateDate'] 		 = date('Y-m-d');
		$adddata['UserId'] 			 = $user['parent'];
		$adddata['summary'] 		 = @$data['summary'];

		$iid = $this->resources_model->add_inspection($adddata);
		
		$this->history_library->saveInspections(array('user_id' => $user_id, 'line_id' => $iid, 'new_val' => json_encode($adddata), 'type' => 'add'));

		$adddata['id'] = $iid;
		
		$userData['status'] = 'ok';
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
		
		// echo '<pre>';
		// print_r($userData['location']);die();

		if (empty($aperture))
		{
			$userData['status'] 	= 'ok';
			$userData['case'] 		= 'new unique id';
			
			$this->_show_output($userData);
		}

		$buildings = array();
		$buildings[0] = $userData['location'][$aperture['Building']];
		$buildings[1] = $userData['location'][$buildings[0]['root']];
		$userData['location'] = $buildings;

		$inspection = $this->resources_model->get_inspection_by_aperture_id($aperture['idDoors']);
		if (!empty($inspection))
		{
			$userData['status'] = 'ok';
			$userData['case'] 	= 'review allready exists';
			
			$this->_show_output($userData);
		}
		

		$userData['status'] 		= 'ok';
		$userData['case'] 			= 'door UID is present';

		$this->_show_output($userData);

	}

	function _make_locations_data($data)
	{
		$this->load->model('user_model');
		$this->load->model('resources_model');
		$user_id = $data['tokendata']['user_id'];
		$user = $this->user_model->get_user_info_by_user_id($user_id);

		foreach ($this->resources_model->get_user_buildings($user['parent']) as $value)
			$userbuildings[$value['idBuildings']] = $value;
		
		foreach ($this->resources_model->get_user_buildings_root($user['parent']) as $value)
			$userrootbuildings[$value['name']] = $value['idBuildings'];

		if (!empty($data['Building']))
		{
		 	if (!isset($userrootbuildings[$data['Building']]))
			{	
				$adddata = array(
					'name' 		=> $data['Building'],
					'parent'	=> 0,
					'level'		=> 0
				);
				$data['Building'] = $this->user_model->add_building($adddata);
			}
			else
				$data['Building'] = $userrootbuildings[$data['Building']];
		}

		if (!empty($data['Floor']) && !empty($data['Building']))
		{
			$floor = FALSE;
			foreach ($userbuildings as $building)
			{
				if ($building['name'] == $data['Floor'] && $building['level'] == 1)
				{
					$data['Floor'] = $building['idBuildings'];
					$floor = TRUE;
					break;
				}
			}
			if (!$floor)
			{
				$adddata = array(
					'name' 		=> $data['Floor'],
					'parent'	=> $data['Building'],
					'level'		=> 1
				);
				$data['Floor'] = $this->user_model->add_building($adddata);
			}
		}
	
		if (!empty($data['Wing']) && !empty($data['Floor']))
		{
			$Wing = FALSE;
			foreach ($userbuildings as $building)
			{
				if ($building['name'] == $data['Wing'] && $building['level'] == 2)
				{
					$data['Wing'] = $building['idBuildings'];
					$Wing = TRUE;
					break;
				}
			}
			if (!$Wing)
			{
				$adddata = array(
					'name' 		=> $data['Wing'],
					'parent'	=> $data['Floor'],
					'level'		=> 2
				);
				$data['Wing'] = $this->user_model->add_building($adddata);
			}
		}
	
		if (!empty($data['Area']) && !empty($data['Wing']))
		{
			$Area = FALSE;
			foreach ($userbuildings as $building)
			{
				if ($building['name'] == $data['Area'] && $building['level'] == 3)
				{
					$data['Area'] = $building['idBuildings'];
					$Area = TRUE;
					break;
				}
			}
			if (!$Area)
			{
				$adddata = array(
					'name' 		=> $data['Area'],
					'parent'	=> $data['Floor'],
					'level'		=> 3
				);
				$data['Area'] = $this->user_model->add_building($adddata);
			}
		}
	
		if (!empty($data['Level']) && !empty($data['Area']))
		{
			$Level = FALSE;
			foreach ($userbuildings as $building)
			{
				if ($building['name'] == $data['Level'] && $building['level'] == 4)
				{
					$data['Level'] = $building['idBuildings'];
					$Level = TRUE;
					break;
				}
			}
			if (!$Level)
			{
				$adddata = array(
					'name' 		=> $data['Level'],
					'parent'	=> $data['Floor'],
					'level'		=> 4
				);
				$data['Level'] = $this->user_model->add_building($adddata);
			}
		}
	
		return $data;
	}
}

/* End of file service.php */
/* Location: ./application/controllers/service.php */