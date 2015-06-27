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
		$data = json_decode($this->input->get('json'), TRUE); 	//DEBUG
		if (empty($data))										//DEBUG
			$data = json_decode($this->input->post('json'), TRUE); 	//DEBUG

		if (empty($data))										//DEBUG
		{
			$postdata = @file_get_contents("php://input");
			if (empty($postdata))
				$this->_show_output(array('status' => 'error', 'error' => 'no data'));
			
			file_put_contents('/home/firedoors/public_html/application/cache/postdata', $postdata . "\r\n", FILE_APPEND);  //DEBUG
			$data = json_decode($postdata, TRUE); //name of JSON post data
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
			// unset($postdata['file_name']);
		
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

		$ext = '.png';

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
				'name'			 => empty($postdata['file_name']) ? '' : $postdata['file_name'],
				'description' 	 => empty($postdata['file_descr']) ? '' : $postdata['file_descr'],
				'type' 			 => (isset($postdata['file_type']) && !empty($postdata['file_type'])) ? $postdata['file_type'] : 'image',
				'FileUploadDate' => date('Y-m-d H:i:s', $creation_time)
			);

			// if ($postdata['file_name'])
				// $adddata['name'] = $postdata['file_name'];
	
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
					$images_comments[] = $image['name'];
				}

			}
			else
				$images[] = base_url($file_path);

			$this->_show_output(array('status' => 'ok', 'images' => $images, 'images_comments' => $images_comments, 'aperture_id' => $aperture_id ? $aperture_id : '', 'idFormFields' => $idFormFields ? $idFormFields : ''));
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
	 * login	=> user email
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
	 * token	=> auth id from login
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
	 * token	=> auth id from login
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
	 * Get inspections list by user
	 *
	 * Input data:
	 * token	=> auth id from login
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
			$output = array();
			unset($inspection);
			foreach ($userData['inspections'] as $inspection)
			{
				if (isset($keyword) && !empty($keyword) && //for search
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
				if (empty($output[$inspection['aperture_id']]))
					$output[$inspection['aperture_id']] = $inspection;
				else
				{
					if ($output[$inspection['aperture_id']]['revision'] < $inspection['revision'])
						$output[$inspection['aperture_id']] = $inspection;
				}
			}
			$userData['inspections'] = $output;

			//add images to inspection
			$inspections_images = array();
			$allimgs = $this->service_model->get_images_by_aperture_id_and_field_id(array_keys($userData['inspections']));
			if (!empty($allimgs))
			{
				foreach ($allimgs as $image)
				{
					$inspections_images[$image['aperture_id']][] = $image['path'];
					$images_comments[$image['aperture_id']][] = @$image['name'];
				}
			}			
			

			//make answers list
			unset($inspection);
			foreach ($userData['inspections'] as &$inspection)
			{
				// echo '<pre>';
				// print_r($inspection);die();
				$anwers = $this->service_model->get_inspection_answers($inspection['id'], $inspection['aperture_id']);
								
				//total inspection troubles in colorcodes
				$color = array();
				foreach ($anwers as $answer)
					$color[$answer['value']] = 1;

				if (isset($color[1]) && count($color) > 1)
					unset($color[1]);

				$colorresult = array();
				foreach ($color as $key => $value)
					$colorresult[] = $key;
				//make review compliant if new
				if (empty($colorresult))
					$colorresult[] = 1;

				$inspection['colorcode'] = $colorresult;
				$inspection['images'] = isset($inspections_images[$inspection['aperture_id']]) ? $inspections_images[$inspection['aperture_id']] : array();
				$inspection['images_comments'] = isset($images_comments[$inspection['aperture_id']]) ? $images_comments[$inspection['aperture_id']] : array();
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
	 * token			=> auth id from login
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

		$this->load->model('resources_model');

		$upddata = array();

		$upddata['InspectionStatus'] = 'Complete';
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
	 * token	=> auth id from login
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
	 * token	=> auth id from login
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
	 * token	=> auth id from login
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
	 * token	=> auth id from login
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
	 * Get aperture overview info tab fields
	 *
	 * Input data:
	 * token		=> auth id from login
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
		
		if (isset($data['olddata']) && isset($data['newdata']))
		{
			$newdata = $data['newdata'];
			$olddata = $data['olddata'];
		}

		if ($user['role'] == 1)
		{
			//--------------------------------
			//get buildings
			foreach ($this->resources_model->get_user_buildings_root($user['parent']) as $value)
			{
				$buildings[$value['idBuildings']] = $value['name'];
				$buildings_values[] = $value['name'];
			}

			if (empty($buildings_values))
			{
				$userData['status'] = 'error';
				$userData['error'] = 'empty user buildings list';
				$this->_show_output($userData);
			}

			$building = (!empty($doorval['Building']) && $doorval['Building'] != 0 && isset($buildings[$doorval['Building']])) ? $buildings[$doorval['Building']] : $buildings_values[0];

			if (isset($olddata) && isset($newdata) && isset($newdata['Building']) && $newdata['Building'] > 0)
				$building = $newdata['Building'];

			$locatio[] = array('name' => 'Building', 'label' => 'Building', 'selected' => $building, 'type' => 'enum', 'values' => $buildings_values, 'enabled' => TRUE, 'force_refresh' => 1, 'alert' => 'Are you sure you want to change?');

			//get floors
			$buildings = array_flip($buildings);
			
			foreach ($this->resources_model->get_user_buildings_by_building_parent($buildings[$building], $user['parent']) as $value)
			{
				$floors[$value['idBuildings']] = $value['name'];
				$floors_values[] = $value['name'];
			}

			if (!empty($floors_values))
			{
				array_unshift($floors_values, 'N/A');
				if (isset($olddata) && isset($newdata))
				{
					if (isset($olddata['Building']) && isset($newdata['Building']) && $olddata['Building'] == $newdata['Building'])
						$floor = $newdata['Floor'];
					else
					{
						$floor = $floors_values[0];
						$newdata["Floor"] = $floor;
					} 
				}
				else
					$floor = (!empty($doorval['Floor']) && $doorval['Floor'] != 0 && isset($floors[$doorval['Floor']])) ? $floors[$doorval['Floor']] : $floors_values[0];
				
				$locatio[] = array('name' => 'Floor', 'label' => 'Floor', 'selected' => $floor, 'type' => 'enum', 'values' => $floors_values, 'enabled' => TRUE, 'force_refresh' => 1, 'alert' => 'Are you sure you want to change?');

				//get wing
				$floors = array_flip($floors);

				$wings_values = array();
				if ($floor != 'N/A')
				{
					foreach ($this->resources_model->get_user_buildings_by_building_parent($floors[$floor], $user['parent']) as $value)
					{
						$wings[$value['idBuildings']] = $value['name'];
						$wings_values[] = $value['name'];
					}
				}

				if (!empty($wings_values))
				{
					array_unshift($wings_values, 'N/A');
					if (isset($olddata) && isset($newdata))
					{
						if (isset($olddata['Floor']) && isset($newdata['Floor']) && $olddata['Floor'] == $newdata['Floor'])
							$wing = $newdata['Wing'];
						else
						{
							$wing = $wings_values[0];
							$newdata['Wing'] = $wing;
						}
					}
					else
						$wing = (!empty($doorval['Wing']) && $doorval['Wing'] != 0 && isset($wings[$doorval['Wing']])) ? $wings[$doorval['Wing']] : $wings_values[0];
					
					$locatio[] = array('name' => 'Wing', 'label' => 'Wing', 'selected' => $wing, 'type' => 'enum', 'values' => $wings_values, 'enabled' => TRUE, 'force_refresh' => 1, 'alert' => 'Are you sure you want to change?');

					//get area
					$wings = array_flip($wings);

					$areas_values = array();
					if ($wing != 'N/A')
					{
						foreach ($this->resources_model->get_user_buildings_by_building_parent($wings[$wing], $user['parent']) as $value)
						{
							$areas[$value['idBuildings']] = $value['name'];
							$areas_values[] = $value['name'];
						}
					}
					if (!empty($areas_values))
					{
						array_unshift($areas_values, 'N/A');
						if (isset($olddata) && isset($newdata))
						{
							if (isset($olddata['Wing']) && isset($newdata['Wing']) && $olddata['Wing'] == $newdata['Wing'])
								$area = $newdata['Area'];
							else {
								$area = $areas_values[0];
								$newdata['Area'] = $area;
							} 
						}
						else
							$area = (!empty($doorval['Area']) && $doorval['Area'] != 0 && isset($areas[$doorval['Area']])) ? $areas[$doorval['Area']] : $areas_values[0];
						
						$locatio[] = array('name' => 'Area', 'label' => 'Area', 'selected' => $area, 'type' => 'enum', 'values' => $areas_values, 'enabled' => TRUE, 'force_refresh' => 1, 'alert' => 'Are you sure you want to change?');

						//get level
						$areas = array_flip($areas);

						$levels_values = array();
						if ($area != 'N/A')
						{
							foreach ($this->resources_model->get_user_buildings_by_building_parent($areas[$area], $user['parent']) as $value)
							{
								$levels[$value['idBuildings']] = $value['name'];
								$levels_values[] = $value['name'];
							}
						}
						if (!empty($levels_values))
						{
							array_unshift($levels_values, 'N/A');
							if (isset($olddata) && isset($newdata))
							{
								if (isset($olddata['Area']) && isset($newdata['Area']) && $olddata['Area'] == $newdata['Area'])
									$level = $newdata['Level'];
								else
								{
									$level =  $levels_values[0];
									$newdata['Level'] = $level;
								}
							}
							else
								$level = (!empty($doorval['Level']) && $doorval['Level'] != 0 && isset($levels[$doorval['Level']])) ? $levels[$doorval['Level']] : $levels_values[0];
							
							$locatio[] = array('name' => 'Level', 'label' => 'Level', 'selected' => $level, 'type' => 'enum', 'values' => $levels_values, 'enabled' => TRUE, 'force_refresh' => 1, 'alert' => 'Are you sure you want to change?');
						}
						else
							$locatio[] = array('name' => 'Level', 'label' => 'Level', 'selected' => 'N/A', 'type' => 'enum', 'values' => array('N/A'), 'enabled' => FALSE, 'force_refresh' => 1, 'alert' => 'Are you sure you want to change?');
					}
					else
					{
						$locatio[] = array('name' => 'Area', 'label' => 'Area', 'selected' => 'N/A', 'type' => 'enum', 'values' => array('N/A'), 'enabled' => FALSE, 'force_refresh' => 1, 'alert' => 'Are you sure you want to change?');
						$locatio[] = array('name' => 'Level', 'label' => 'Level', 'selected' => 'N/A', 'type' => 'enum', 'values' => array('N/A'), 'enabled' => FALSE, 'force_refresh' => 1, 'alert' => 'Are you sure you want to change?');
					}
				}
				else
				{
					$locatio[] = array('name' => 'Wing', 'label' => 'Wing', 'selected' => 'N/A', 'type' => 'enum', 'values' => array('N/A'), 'enabled' => FALSE, 'force_refresh' => 1, 'alert' => 'Are you sure you want to change?');
					$locatio[] = array('name' => 'Area', 'label' => 'Area', 'selected' => 'N/A', 'type' => 'enum', 'values' => array('N/A'), 'enabled' => FALSE, 'force_refresh' => 1, 'alert' => 'Are you sure you want to change?');
					$locatio[] = array('name' => 'Level', 'label' => 'Level', 'selected' => 'N/A', 'type' => 'enum', 'values' => array('N/A'), 'enabled' => FALSE, 'force_refresh' => 1, 'alert' => 'Are you sure you want to change?');
				}
			}
			else
			{
				$locatio[] = array('name' => 'Floor', 'label' => 'Floor', 'selected' => 'N/A', 'type' => 'enum', 'values' => array('N/A'), 'enabled' => FALSE, 'force_refresh' => 1, 'alert' => 'Are you sure you want to change?');
				$locatio[] = array('name' => 'Wing', 'label' => 'Wing', 'selected' => 'N/A', 'type' => 'enum', 'values' => array('N/A'), 'enabled' => FALSE, 'force_refresh' => 1, 'alert' => 'Are you sure you want to change?');
				$locatio[] = array('name' => 'Area', 'label' => 'Area', 'selected' => 'N/A', 'type' => 'enum', 'values' => array('N/A'), 'enabled' => FALSE, 'force_refresh' => 1, 'alert' => 'Are you sure you want to change?');
				$locatio[] = array('name' => 'Level', 'label' => 'Level', 'selected' => 'N/A', 'type' => 'enum', 'values' => array('N/A'), 'enabled' => FALSE, 'force_refresh' => 1, 'alert' => 'Are you sure you want to change?');
			}

			$IntExt = $this->service_model->get_enum_values('Doors', 'IntExt');
			if (isset($olddata) && isset($newdata))
				$selected = $newdata['IntExt'];
			else
				$selected = (!empty($doorval['IntExt']) && $doorval['IntExt'] != 0) ? $doorval['IntExt'] : $IntExt[0];
			$locatio[] = array('name' => 'IntExt', 'label' => 'Interior / Exterior?', 'selected' => $selected, 'type' => 'enum', 'values' => $IntExt, 'enabled' => TRUE, 'force_refresh' => 0, 'alert' => '');

			$userData['info']['Locations'] = $locatio;
			$userData['sections'][] = 'Locations';
		}


		//--------------------------------
		$four_params = $this->config->item('four_params_conditions');

		foreach ($this->config->item('wall_rates') as $key => $value)
		{
			$wall_rating[] = $value;
			$wall_ratings[$key] = $value;
		}
		if (isset($olddata) && isset($newdata) && isset($newdata['wall_Rating'])) 	$selected_wall_rating = $newdata['wall_Rating'];
		else 																		$selected_wall_rating = (!empty($doorval['wall_Rating']) && $doorval['wall_Rating'] != 0 && isset($wall_ratings[$doorval['wall_Rating']])) ? $wall_ratings[$doorval['wall_Rating']] : 'Please select value';
		$ratings[] = array('name' => 'wall_Rating',  'label' => 'Wall Rating',					'selected' => $selected_wall_rating, 'type' => 'enum', 'values' => $wall_rating,  'enabled' => TRUE, 'force_refresh' => 1, 'alert' => '');
		

		foreach ($this->config->item('rates_types') as $key => $value)
		{
			$smoke_rating[] = $value;
			$smoke_ratings[$key] = $value;
		}
		if (isset($olddata) && isset($newdata) && isset($newdata['smoke_Rating'])) 	$selected_smoke_rating = $newdata['smoke_Rating'];
		else 																		$selected_smoke_rating = (!empty($doorval['smoke_Rating']) && $doorval['smoke_Rating'] != 0 && isset($smoke_ratings[$doorval['smoke_Rating']])) ? $smoke_ratings[$doorval['smoke_Rating']] : 'Please select value';
		$ratings[] = array('name' => 'smoke_Rating', 'label' => 'Smoke Rating',					'selected' => $selected_smoke_rating, 'type' => 'enum', 'values' => $smoke_rating, 'enabled' => TRUE, 'force_refresh' => 1, 'alert' => '');


		if ($selected_smoke_rating !== 'Please select value')
		{
			$material = $this->config->item('door_matherial');
			if ($selected_smoke_rating == 'No') 
				unset($material[6]);
			
			$materials = array_flip($material);
		}
		else
			$material = array('Please select Smoke Rating first');

		if (isset($olddata) && isset($newdata) && isset($newdata['material']) && isset($materials[$newdata['material']])) 	$selected_material = $newdata['material'];
		else 																												$selected_material = (!empty($doorval['material']) && $doorval['material'] != 0 && isset($material[$doorval['material']])) ? $material[$doorval['material']] : 'Please select value';
		
		$min_req_door_rating = $this->config->item('min_req_door_rating');
		$min_req_door_rating = ($selected_wall_rating !== 'Please select value' && $selected_smoke_rating !== 'Please select value') ? $min_req_door_rating[$selected_wall_rating][$selected_smoke_rating] : '';
		$ratings[] = array('name' => 'min_req_rating', 		 'label' => 'Minimum Required Door Rating', 'selected' => $min_req_door_rating, 'type' => 'string', 'values' => array($min_req_door_rating), 	 'enabled' => FALSE, 'force_refresh' => 0, 'alert' => '');

		$userData['info']['Ratings'] = $ratings;
		$userData['sections'][] = 'Ratings';


		//--------------------------------Door Details

		
		if (isset($olddata) && isset($newdata) && isset($newdata['barcode']))
		{
			$existing_aperure = $this->resources_model->get_aperture_info_by_barcode($newdata['barcode'], $user['parent']);
			if (!empty($existing_aperure) && $existing_aperure['idDoors'] != $data['aperture_id'])
			{
				$userData['status'] = 'error';
				$userData['error'] = 'Door Id exists already. Please enter new Door id';
				$this->_show_output($userData);
			}
			if (strlen($newdata['barcode']) != 6)
			{
				$userData['status'] = 'error';
				$userData['error'] = 'Door id must be 6 characters';
				$this->_show_output($userData);
			}
			$selected = $newdata['barcode'];	
		}
		else 
			$selected = !empty($doorval['barcode']) ? $doorval['barcode'] : '';
	
		$doordetails[] = array('name' => 'barcode',				'label' => 'Door Id',							'selected' => $selected, 'type' => 'string', 'enabled' => TRUE, 'force_refresh' => 1, 'alert' => 'Are you sure you want to change?');

		$door_type = $this->service_model->get_enum_values('Doors', 'door_type');

		if (isset($olddata) && isset($newdata) && isset($newdata['door_type'])) 			$selected = $newdata['door_type'];
		else 																				$selected = !empty($doorval['door_type']) ? $doorval['door_type'] : 'Please select value';
		$doordetails[] = array('name' => 'door_type', 			 'label' => 'Door Type',						'selected' => $selected, 'type' => 'enum', 'values' => $door_type, 			  'enabled' => TRUE, 'force_refresh' => 0, 'alert' => '');

		$number_Doors = $this->service_model->get_enum_values('Doors', 'number_Doors');
		if (isset($olddata) && isset($newdata) && isset($newdata['number_Doors'])) 			$selected = $newdata['number_Doors'];
		else 																				$selected = !empty($doorval['number_Doors']) ? $doorval['number_Doors'] : 'Please select value';
		$doordetails[] = array('name' => 'number_Doors',		 'label' => '# of Doors in Frame',				'selected' => $selected, 'type' => 'enum', 'values' => $number_Doors, 'enabled' => TRUE, 'force_refresh' => 0, 'alert' => '');

		unset($materials);
		foreach ($material as $value)
				$materials[] = $value;
		$doordetails[] = array('name' => 'material', 			 'label' => 'Door Material', 					'selected' => $selected_material, 'type' => 'enum', 'values' => $materials, 	 'enabled' => TRUE, 'force_refresh' => 0, 'alert' => '');

		$width = $this->service_model->get_enum_values('Doors', 'width');
		if (isset($olddata) && isset($newdata) && isset($newdata['width']))					$selected = $newdata['width'];
		else 																				$selected = !empty($doorval['width']) ? $doorval['width'] : 'Please select value';
		$doordetails[] = array('name' => 'width', 				 'label' => 'Door Width (inches)', 				'selected' => $selected, 'type' => 'enum', 'values' => $width, 'enabled' => TRUE, 'force_refresh' => 0, 'alert' => '');

		$height = $this->service_model->get_enum_values('Doors', 'height');
		if (isset($olddata) && isset($newdata) && isset($newdata['height'])) 				$selected = $newdata['height'];
		else 																				$selected = !empty($doorval['height']) ? $doorval['height'] : 'Please select value';
		$doordetails[] = array('name' => 'height', 				 'label' => 'Door Height (inches)', 				'selected' => $selected, 'type' => 'enum', 'values' => $height, 'enabled' => TRUE, 'force_refresh' => 0, 'alert' => '');

		$vision_Light_Present = $this->service_model->get_enum_values('Doors', 'vision_Light_Present');
		if (isset($olddata) && isset($newdata) && isset($newdata['vision_Light_Present'])) 	$selected_vlp = $newdata['vision_Light_Present'];
		else 																				$selected_vlp = !empty($doorval['vision_Light_Present']) ? $doorval['vision_Light_Present'] : 'Please select value';
		$doordetails[] = array('name' => 'vision_Light_Present', 'label' => 'Vision Light (Glass) Present?',	'selected' => $selected_vlp, 'type' => 'enum', 'values' => $vision_Light_Present, 'enabled' => TRUE, 'force_refresh' => 1, 'alert' => '');

		if ($selected_vlp == 'Yes')
		{
			$vision_Light = $this->service_model->get_enum_values('Doors', 'vision_Light');
			if (isset($olddata) && isset($newdata) && isset($newdata['vision_Light'])) 	$selected = $newdata['vision_Light'];
			else 																		$selected = !empty($doorval['vision_Light']) ? $doorval['vision_Light'] : $vision_Light[0];
			$doordetails[] = array('name' => 'vision_Light', 	 'label' => 'Dimensions (inches)',			  		'selected' => $selected, 'type' => 'enum', 'values' => $vision_Light, 		  'enabled' => TRUE, 'force_refresh' => 0, 'alert' => '');
		}

		$singage = $this->service_model->get_enum_values('Doors', 'singage');
		if (isset($olddata) && isset($newdata) && isset($newdata['singage'])) 			$selected = $newdata['singage'];
		else 																			$selected = !empty($doorval['singage']) ? $doorval['singage'] : 'Please select value';
		$doordetails[] = array('name' => 'singage', 			 'label' => 'Signage Present?',		  			'selected' => $selected, 'type' => 'enum', 'values' => $singage, 			  'enabled' => TR