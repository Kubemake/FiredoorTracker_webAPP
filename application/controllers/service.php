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
					$inspections_images[$image['aperture_id']][] = $image['path'];
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

			if (isset($olddata) && isset($newdata))
				$building = $newdata['Building'];

			$locatio[] = array('name' => 'Building', 'label' => 'Building', 'selected' => $building, 'type' => 'enum', 'values' => $buildings_values, 'enabled' => TRUE, 'force_refresh' => 1);

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
				
				$locatio[] = array('name' => 'Floor', 'label' => 'Floor', 'selected' => $floor, 'type' => 'enum', 'values' => $floors_values, 'enabled' => TRUE, 'force_refresh' => 1);

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
					
					$locatio[] = array('name' => 'Wing', 'label' => 'Wing', 'selected' => $wing, 'type' => 'enum', 'values' => $wings_values, 'enabled' => TRUE, 'force_refresh' => 1);

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
						
						$locatio[] = array('name' => 'Area', 'label' => 'Area', 'selected' => $area, 'type' => 'enum', 'values' => $areas_values, 'enabled' => TRUE, 'force_refresh' => 1);

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
							
							$locatio[] = array('name' => 'Level', 'label' => 'Level', 'selected' => $level, 'type' => 'enum', 'values' => $levels_values, 'enabled' => TRUE, 'force_refresh' => 1);
						}
						else
							$locatio[] = array('name' => 'Level', 'label' => 'Level', 'selected' => 'N/A', 'type' => 'enum', 'values' => array('N/A'), 'enabled' => FALSE, 'force_refresh' => 1);
					}
					else
					{
						$locatio[] = array('name' => 'Area', 'label' => 'Area', 'selected' => 'N/A', 'type' => 'enum', 'values' => array('N/A'), 'enabled' => FALSE, 'force_refresh' => 1);
						$locatio[] = array('name' => 'Level', 'label' => 'Level', 'selected' => 'N/A', 'type' => 'enum', 'values' => array('N/A'), 'enabled' => FALSE, 'force_refresh' => 1);
					}
				}
				else
				{
					$locatio[] = array('name' => 'Wing', 'label' => 'Wing', 'selected' => 'N/A', 'type' => 'enum', 'values' => array('N/A'), 'enabled' => FALSE, 'force_refresh' => 1);
					$locatio[] = array('name' => 'Area', 'label' => 'Area', 'selected' => 'N/A', 'type' => 'enum', 'values' => array('N/A'), 'enabled' => FALSE, 'force_refresh' => 1);
					$locatio[] = array('name' => 'Level', 'label' => 'Level', 'selected' => 'N/A', 'type' => 'enum', 'values' => array('N/A'), 'enabled' => FALSE, 'force_refresh' => 1);
				}
			}
			else
			{
				$locatio[] = array('name' => 'Floor', 'label' => 'Floor', 'selected' => 'N/A', 'type' => 'enum', 'values' => array('N/A'), 'enabled' => FALSE, 'force_refresh' => 1);
				$locatio[] = array('name' => 'Wing', 'label' => 'Wing', 'selected' => 'N/A', 'type' => 'enum', 'values' => array('N/A'), 'enabled' => FALSE, 'force_refresh' => 1);
				$locatio[] = array('name' => 'Area', 'label' => 'Area', 'selected' => 'N/A', 'type' => 'enum', 'values' => array('N/A'), 'enabled' => FALSE, 'force_refresh' => 1);
				$locatio[] = array('name' => 'Level', 'label' => 'Level', 'selected' => 'N/A', 'type' => 'enum', 'values' => array('N/A'), 'enabled' => FALSE, 'force_refresh' => 1);
			}

			$IntExt = $this->service_model->get_enum_values('Doors', 'IntExt');
			if (isset($olddata) && isset($newdata))
				$selected = $newdata['IntExt'];
			else
				$selected = (!empty($doorval['IntExt']) && $doorval['IntExt'] != 0) ? $doorval['IntExt'] : $IntExt[0];
			$locatio[] = array('name' => 'IntExt', 'label' => 'Interior / Exterior?', 'selected' => $selected, 'type' => 'enum', 'values' => $IntExt, 'enabled' => TRUE, 'force_refresh' => 0);

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
		$ratings[] = array('name' => 'wall_Rating',  'label' => 'Wall Rating',					'selected' => $selected_wall_rating, 'type' => 'enum', 'values' => $wall_rating,  'enabled' => TRUE, 'force_refresh' => 1);
		

		foreach ($this->config->item('rates_types') as $key => $value)
		{
			$smoke_rating[] = $value;
			$smoke_ratings[$key] = $value;
		}
		if (isset($olddata) && isset($newdata) && isset($newdata['smoke_Rating'])) 	$selected_smoke_rating = $newdata['smoke_Rating'];
		else 																		$selected_smoke_rating = (!empty($doorval['smoke_Rating']) && $doorval['smoke_Rating'] != 0 && isset($smoke_ratings[$doorval['smoke_Rating']])) ? $smoke_ratings[$doorval['smoke_Rating']] : 'Please select value';
		$ratings[] = array('name' => 'smoke_Rating', 'label' => 'Smoke Rating',					'selected' => $selected_smoke_rating, 'type' => 'enum', 'values' => $smoke_rating, 'enabled' => TRUE, 'force_refresh' => 1);


		if ($selected_smoke_rating !== 'Please select value')
		{
			foreach ($this->config->item('door_matherial') as $key => $value)
			{
				if ($selected_smoke_rating == 'No' && $value == 'Aluminum')
					continue;

				$material[] = $value;
				$materials[$key] = $value;
			}
			$materials = array_flip($material);
			// $material = array_keys($four_params[$selected_smoke_rating]);
		}
		else
			$material = array('Please select Smoke Rating first');
		
		$doorval['material']--; //to sync with materials in config
		if (isset($olddata) && isset($newdata) && isset($newdata['material']) && isset($materials[$newdata['material']])) 	$selected_material = $newdata['material'];
		else 																												$selected_material = (!empty($doorval['material']) && $doorval['material'] != 0 && isset($material[$doorval['material']])) ? $material[$doorval['material']] : 'Please select value';
		
		$min_req_door_rating = $this->config->item('min_req_door_rating');
		$min_req_door_rating = ($selected_wall_rating !== 'Please select value' && $selected_smoke_rating !== 'Please select value') ? $min_req_door_rating[$selected_wall_rating][$selected_smoke_rating] : '';
		$ratings[] = array('name' => 'min_req_rating', 		 'label' => 'Minimum Required Door Rating', 'selected' => $min_req_door_rating, 'type' => 'string', 'values' => array($min_req_door_rating), 	 'enabled' => FALSE, 'force_refresh' => 0);

		$userData['info']['Ratings'] = $ratings;
		$userData['sections'][] = 'Ratings';


		//--------------------------------Door Details

		if (isset($olddata) && isset($newdata) && isset($newdata['barcode']))				$selected = $newdata['barcode'];
		else 																				$selected = !empty($doorval['barcode']) ? $doorval['barcode'] : '';
		$doordetails[] = array('name' => 'barcode',				'label' => 'Door Id',							'selected' => $selected, 'type' => 'string', 'enabled' => TRUE, 'force_refresh' => 0);

		$door_type = $this->service_model->get_enum_values('Doors', 'door_type');

		if (isset($olddata) && isset($newdata) && isset($newdata['door_type'])) 			$selected = $newdata['door_type'];
		else 																				$selected = !empty($doorval['door_type']) ? $doorval['door_type'] : 'Please select value';
		$doordetails[] = array('name' => 'door_type', 			 'label' => 'Door Type',						'selected' => $selected, 'type' => 'enum', 'values' => $door_type, 			  'enabled' => TRUE, 'force_refresh' => 0);

		$number_Doors = $this->service_model->get_enum_values('Doors', 'number_Doors');
		if (isset($olddata) && isset($newdata) && isset($newdata['number_Doors'])) 			$selected = $newdata['number_Doors'];
		else 																				$selected = !empty($doorval['number_Doors']) ? $doorval['number_Doors'] : 'Please select value';
		$doordetails[] = array('name' => 'number_Doors',		 'label' => '# of Doors in Frame',				'selected' => $selected, 'type' => 'enum', 'values' => $number_Doors, 'enabled' => TRUE, 'force_refresh' => 0);

		$doordetails[] = array('name' => 'material', 			 'label' => 'Door Material', 					'selected' => $selected_material, 'type' => 'enum', 'values' => $material, 	 'enabled' => TRUE, 'force_refresh' => 0);

		$width = $this->service_model->get_enum_values('Doors', 'width');
		if (isset($olddata) && isset($newdata) && isset($newdata['width']))					$selected = $newdata['width'];
		else 																				$selected = !empty($doorval['width']) ? $doorval['width'] : 'Please select value';
		$doordetails[] = array('name' => 'width', 				 'label' => 'Door Width (inches)', 				'selected' => $selected, 'type' => 'enum', 'values' => $width, 'enabled' => TRUE, 'force_refresh' => 0);

		$height = $this->service_model->get_enum_values('Doors', 'height');
		if (isset($olddata) && isset($newdata) && isset($newdata['height'])) 				$selected = $newdata['height'];
		else 																				$selected = !empty($doorval['height']) ? $doorval['height'] : 'Please select value';
		$doordetails[] = array('name' => 'height', 				 'label' => 'Door Height (inches)', 				'selected' => $selected, 'type' => 'enum', 'values' => $height, 'enabled' => TRUE, 'force_refresh' => 0);

		$vision_Light_Present = $this->service_model->get_enum_values('Doors', 'vision_Light_Present');
		if (isset($olddata) && isset($newdata) && isset($newdata['vision_Light_Present'])) 	$selected_vlp = $newdata['vision_Light_Present'];
		else 																				$selected_vlp = !empty($doorval['vision_Light_Present']) ? $doorval['vision_Light_Present'] : 'Please select value';
		$doordetails[] = array('name' => 'vision_Light_Present', 'label' => 'Vision Light (Glass) Present?',	'selected' => $selected_vlp, 'type' => 'enum', 'values' => $vision_Light_Present, 'enabled' => TRUE, 'force_refresh' => 1);

		if ($selected_vlp == 'Yes')
		{
			$vision_Light = $this->service_model->get_enum_values('Doors', 'vision_Light');
			if (isset($olddata) && isset($newdata) && isset($newdata['vision_Light'])) 	$selected = $newdata['vision_Light'];
			else 																		$selected = !empty($doorval['vision_Light']) ? $doorval['vision_Light'] : $vision_Light[0];
			$doordetails[] = array('name' => 'vision_Light', 	 'label' => 'Dimensions (inches)',			  		'selected' => $selected, 'type' => 'enum', 'values' => $vision_Light, 		  'enabled' => TRUE, 'force_refresh' => 0);
		}

		$singage = $this->service_model->get_enum_values('Doors', 'singage');
		if (isset($olddata) && isset($newdata) && isset($newdata['singage'])) 			$selected = $newdata['singage'];
		else 																			$selected = !empty($doorval['singage']) ? $doorval['singage'] : 'Please select value';
		$doordetails[] = array('name' => 'singage', 			 'label' => 'Signage Present?',		  			'selected' => $selected, 'type' => 'enum', 'values' => $singage, 			  'enabled' => TRUE, 'force_refresh' => 0);

		$auto_Operator = $this->service_model->get_enum_values('Doors', 'auto_Operator');
		if (isset($olddata) && isset($newdata) && isset($newdata['auto_Operator'])) 	$selected = $newdata['auto_Operator'];
		else 																			$selected = !empty($doorval['auto_Operator'])  ? $doorval['auto_Operator'] : 'Please select value';
		$doordetails[] = array('name' => 'auto_Operator', 		 'label' => 'Auto Operator Present?', 			'selected' => $selected, 'type' => 'enum', 'values' => $auto_Operator, 		  'enabled' => TRUE, 'force_refresh' => 0);
		
		$userData['info']['Door Details'] = $doordetails;
		$userData['sections'][] = 'Door Details';


		//--------------------------------
		$frameLabel_Type = $this->service_model->get_enum_values('Doors', 'frameLabel_Type');
		if (isset($olddata) && isset($newdata) && isset($newdata['frameLabel_Type']))	$selected_flt = $newdata['frameLabel_Type'];
		else 																			$selected_flt = !empty($doorval['frameLabel_Type']) ? $doorval['frameLabel_Type'] : 'Please select value';
		$frameLabel[] = array('name' => 'frameLabel_Type',			'label' => 'Type',							'selected' => $selected_flt, 'type' => 'enum', 'values' => $frameLabel_Type, 		 'enabled' => TRUE, 'force_refresh' => 1);
		
		
		if ($selected_flt != 'Not Present' && $selected_flt != 'Please select value')
		{
			$frameLabel_Rating = $this->service_model->get_enum_values('Doors', 'frameLabel_Rating');
			if (isset($olddata) && isset($newdata) && isset($newdata['frameLabel_Rating'])) 	$selected = $newdata['frameLabel_Rating'];
			else 																				$selected = !empty($doorval['frameLabel_Rating']) ? $doorval['frameLabel_Rating'] : $frameLabel_Rating[0];
			$frameLabel[] = array('name' => 'frameLabel_Rating',		'label' => 'Rating',						'selected' => $selected, 'type' => 'enum', 'values' => $frameLabel_Rating, 		 'enabled' => TRUE, 'force_refresh' => 0);
		
			$frameLabel_Testing_Lab = 								$this->service_model->get_enum_values('Doors', 'frameLabel_Testing_Lab');
			if (isset($olddata) && isset($newdata) && isset($newdata['frameLabel_Testing_Lab']))	$selected = $newdata['frameLabel_Testing_Lab'];
			else 																					$selected = !empty($doorval['frameLabel_Testing_Lab']) ? $doorval['frameLabel_Testing_Lab'] : $frameLabel_Testing_Lab[0];
			$frameLabel[] = array('name' => 'frameLabel_Testing_Lab',	'label' => 'Testing Lab',					'selected' => $selected, 'type' => 'enum', 'values' => $frameLabel_Testing_Lab,  'enabled' => TRUE, 'force_refresh' => 0);
		}

		if (isset($olddata) && isset($newdata)) 				$selected = $newdata['frameLabel_Manufacturer'];
		else 													$selected = !empty($doorval['frameLabel_Manufacturer']) ? $doorval['frameLabel_Manufacturer'] : '';
		$frameLabel[] = array('name' => 'frameLabel_Manufacturer',	'label' => 'Manufacturer',					'selected' => $selected, 'type' => 'string', 'enabled' => TRUE, 'force_refresh' => 0);
		
		if ($selected_flt !== 'Not Present' && $selected_flt !== 'Please select value')
		{
			if (isset($olddata) && isset($newdata) && isset($newdata['frameLabel_serial']))			$selected = $newdata['frameLabel_serial'];
			else 																					$selected = !empty($doorval['frameLabel_serial']) ? $doorval['frameLabel_serial'] : '';
			$frameLabel[] = array('name' => 'frameLabel_serial',		'label' => 'Serial #',						'selected' => $selected, 'type' => 'string', 'enabled' => TRUE, 'force_refresh' => 0);
		}
	
		$userData['info']['Frame Label'] = $frameLabel;
		$userData['sections'][] = 'Frame Label';


		//--------------------------------
		$doorLabel_Type = 										$this->service_model->get_enum_values('Doors', 'doorLabel_Type');
		
		if (isset($olddata) && isset($newdata) && isset($newdata['doorLabel_Type']))				$selected_dlt = $newdata['doorLabel_Type'];
		else 																						$selected_dlt = !empty($doorval['doorLabel_Type']) ? $doorval['doorLabel_Type'] : 'Please select value';
		$doorLabel[] = array('name' => 'doorLabel_Type',		 	'label' => 'Type',								'selected' => $selected_dlt, 'type' => 'enum', 'values' => $doorLabel_Type, 		'enabled' => TRUE, 'force_refresh' => 1);

		if ($selected_dlt !== 'Not Present' && $selected_dlt !== 'Please select value')
		{
			if ($selected_smoke_rating !== 'Please select value')
			{
				$doorLabel_Rating = array_keys($four_params[$selected_smoke_rating]);
				$doorLabel_Ratings = array_flip($doorLabel_Rating);
			}
			else
				$doorLabel_Rating = array('Please select Smoke Rating first');

			if (isset($olddata) && isset($newdata) && isset($newdata['doorLabel_Rating']) && isset($doorLabel_Ratings[$newdata['doorLabel_Rating']]))	$selected = $newdata['doorLabel_Rating'];
			else 																																		$selected = (!empty($doorval['doorLabel_Rating']) && isset($doorLabel_Ratings[$doorval['doorLabel_Rating']])) ? $doorval['doorLabel_Rating'] : 'Please select value';

			$doorLabel[] = array('name' => 'doorLabel_Rating',		 			'label' => 'Rating',						'selected' => $selected, 'type' => 'enum', 'values' => $doorLabel_Rating, 		'enabled' => TRUE, 'force_refresh' => 1);

			$doorLabel_Testing_Lab = 								$this->service_model->get_enum_values('Doors', 'doorLabel_Testing_Lab');
			if (isset($olddata) && isset($newdata) && isset($newdata['doorLabel_Testing_Lab']))		$selected = $newdata['doorLabel_Testing_Lab'];
			else 																					$selected = !empty($doorval['doorLabel_Testing_Lab']) ? $doorval['doorLabel_Testing_Lab'] : $doorLabel_Testing_Lab[0];
			$doorLabel[] = array('name' => 'doorLabel_Testing_Lab',	 	'label' => 'Testing Lab',					'selected' => $selected, 'type' => 'enum', 'values' => $doorLabel_Testing_Lab, 	'enabled' => TRUE, 'force_refresh' => 0);
		}

		if (isset($olddata) && isset($newdata)) 				$selected = $newdata['doorLabel_Manufacturer'];
		else 													$selected = !empty($doorval['doorLabel_Manufacturer']) ? $doorval['doorLabel_Manufacturer'] :'';
		$doorLabel[] = array('name' => 'doorLabel_Manufacturer', 	'label' => 'Manufacturer',						'selected' => $selected, 'type' => 'string', 'enabled' => TRUE, 'force_refresh' => 0);

		if ($selected_dlt !== 'Not Present' && $selected_dlt !== 'Please select value')
		{		
			if (isset($olddata) && isset($newdata) && isset($newdata['doorLabel_serial'])) 			$selected = $newdata['doorLabel_serial'];
			else 																					$selected = !empty($doorval['doorLabel_serial']) ? $doorval['doorLabel_serial'] : '';
			$doorLabel[] = array('name' => 'doorLabel_serial',		 	'label' => 'Serial #',						'selected' => $selected, 'type' => 'string', 'enabled' => TRUE, 'force_refresh' => 0);
			
			$doorLabel_Min_Latch = 									$this->service_model->get_enum_values('Doors', 'doorLabel_Min_Latch');
			if (isset($olddata) && isset($newdata) && isset($newdata['doorLabel_Min_Latch']))		$selected = $newdata['doorLabel_Min_Latch'];
			else 																					$selected = !empty($doorval['doorLabel_Min_Latch']) ? $doorval['doorLabel_Min_Latch'] : $doorLabel_Min_Latch[0];
			$doorLabel[] = array('name' => 'doorLabel_Min_Latch',	 	'label' => 'Min. Latch Throw Requirement',	'selected' => $selected, 'type' => 'enum', 'values' => $doorLabel_Min_Latch, 	'enabled' => TRUE, 'force_refresh' => 0);

			$doorLabel_Temp_Rise = 									$this->service_model->get_enum_values('Doors', 'doorLabel_Temp_Rise');
			if (isset($olddata) && isset($newdata) && isset($newdata['doorLabel_Temp_Rise']))		$selected = $newdata['doorLabel_Temp_Rise'];
			else 																					$selected = !empty($doorval['doorLabel_Temp_Rise']) ? $doorval['doorLabel_Temp_Rise'] : $doorLabel_Temp_Rise[0];
			$doorLabel[] = array('name' => 'doorLabel_Temp_Rise',	 	'label' => 'Temperature Rise Requirement',	'selected' => $selected,			 'type' => 'enum', 'values' => $doorLabel_Temp_Rise, 	'enabled' => TRUE, 'force_refresh' => 0);
		}

		$userData['info']['Door Label'] = $doorLabel;
		$userData['sections'][] = 'Door Label';

		$userData['status'] = 'ok';

		// echo '<pre>';
		// print_r($userData);die();
		$this->_show_output($userData);
	}

	/*
	 * Get aperture issues and update overview info
	 *
	 * Input data:
	 * token			=> auth id from login
	 * inspection_id  	=> inspection id
	 * wall_rating  	=> wall rating aperture value
	 * smoke_rating 	=> smoke rating aperture value
	 * material  		=> material aperture value
	 * rating  			=> rating aperture value
	 * width  			=> width aperture value
	 * height  			=> height aperture value
	 * Building 		=> Building aperture value
	 * other params	 => Other parameters from overview info
	 *
	 * Output data:
	 * status => ok
	 * tabs => list of issues tabs 
	 * issues => list of issues according input data
	 */
	function _exec_function_get_aperture_issues($data)
	{
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

		if (isset($data['doorLabel_Rating']))
			$data['rating'] = $data['doorLabel_Rating'];
		else
		{
			$data['doorLabel_Rating'] = 'N/A';
			$data['rating'] = '0';
		}

		$data['rating'] = preg_replace('@[^\d]+@si', '', $data['rating']);
		
		$data['width'] = @preg_replace('@[^\d\.]+@si', '', $data['width']);
		if (!isset($data['width']) or empty($data['width']) or $data['width']===0)
		{
			$userData['status'] = 'error';
			$userData['error'] = 'not isset or empty input parameter width';
			$this->_show_output($userData);
		}

		$data['height'] = @preg_replace('@[^\d\.]+@si', '', $data['height']);
		if (!isset($data['height']) or empty($data['height']) or $data['height']===0)
		{
			$userData['status'] = 'error';
			$userData['error'] = 'not isset or empty input parameter height';
			$this->_show_output($userData);
		}

		$wall_rating 	= array_flip($this->config->item('wall_rates'));
		$smoke_rating 	= array_flip($this->config->item('rates_types'));
		$material 		= array_flip($this->config->item('door_matherial'));
		$rating 		= array_flip($this->config->item('door_rating'));

		$user_id = $data['tokendata']['user_id'];

		$this->load->model('user_model');
		$user = $this->user_model->get_user_info_by_user_id($user_id);
		
		//make right data for save for some fields
		$data = $this->_make_locations_data($data);
		$data['wall_Rating']  = $wall_rating[$data['wall_Rating']];
		$data['smoke_Rating'] = $smoke_rating[$data['smoke_Rating']];
		$data['rating'] 	  = $rating[$data['rating']];
		$data['material'] 	  = $material[$data['material']];

		$upddata 					= $data; //save selected overview parameters

		unset($upddata['type'], $upddata['token'], $upddata['tokendata'], $upddata['inspection_id'], $upddata['min_req_rating']); //remove waste data

		$this->load->library('History_library');
		
		$this->history_library->saveDoors(array('user_id' => $user_id, 'iid' => $data['inspection_id'], 'new_val' => json_encode($upddata), 'type' => 'edit'));

		$this->service_model->update_aperture_overview_info($data['inspection_id'], $upddata, $user_id);

		$result = $this->service_model->get_aperture_issues_and_selected($data);

		/*spec code for signs*/
		$addbtnqs = array(637, 640, 78);
		foreach ($addbtnqs as $addbtnq)
		{
			if ($addbtnq > 0)
			{
				$signs = $result['issues'][$addbtnq]['answers'];
				$square = 0; //total signs square
				foreach ($signs as &$sign)
				{
					if (strlen($sign['selected']) > 0)
					{
						$dim = explode(',',$sign['selected']);
						$square += trim(@$dim[0]) * trim(@$dim[1]);
					}
					else
						unset($signs[$sign['idFormFields']]);
				}

				//calc sings square
				$signsize = 0;
				if ($square > 0)
					$signsize = $square * 100 / ($data['width']*$data['height']);
				
				//replace if sign size over 5% of door square
				if ($data['wall_Rating'] < 4 && count($signs) > 0 && $signsize > 5)
					foreach ($signs as &$value)
						$value['status'] = 4;
		
				//add sign btn
				$signs['789789'] = array(
					'idFormFields' => '789789',
					'type' => 'answer',
					'nextQuestionId' => 0,
					'name' => 'AddSignBtn',
					'label' => 'Add Sign',
					'questionId' => $addbtnq,
					'questionOrder' => count($signs)+1,
					'status' => '',
					'selected' => 'Yes'
				);
				$result['issues'][$addbtnq]['answers'] = $signs;
			}
		}
		/*END spec code for signs*/

		/*spec code for holes*/
		$h1addbtnqs = array(323,344,15,566,50);
		foreach ($h1addbtnqs as $h1addbtnq)
		{
			if ($h1addbtnq > 0)
			{
				$addbtn = FALSE;
				$holes = $result['issues'][$h1addbtnq]['answers'];
				foreach ($holes as &$hole)
				{
					if (!$addbtn) //Save params for AddBtn
						$addbtn = $hole;

					if (strlen($hole['selected']) == 0)
						unset($holes[$hole['idFormFields']]);
				}

				//add hole btn
				$holes['789790'] = array(
					'idFormFields' => '789790',
					'type' => 'answer',
					'nextQuestionId' => 0,
					'name' => 'AddHoleBtn',
					'label' => 'Add Hole',
					'questionId' => $addbtn['questionId'],
					'questionOrder' => count($holes)+1,
					'status' => '',
					'selected' => 'Yes'
				);
				$result['issues'][$h1addbtnq]['answers'] = $holes;
			}
		}
		/*END spec code for frame holes*/

		/*spec code for signage question */
		//hide Signage answer variant 
		if (empty($data['singage']) or $data['singage'] != 'Yes')
		{
			unset($result['issues'][$result['signage']['parent']]['answers'][$result['signage']['idFormFields']]);
			unset($result['issues'][$result['signage']['nextQuestionId']]);
			foreach ($addbtnqs as $addbtnq) 
				unset($result['issues'][$addbtnq]);
		}
		/*END spec code for signage question */
		
		/*spec code for Vision Light (Glass) Present? question */
		//hide Glazing review TAB!
		if (empty($data['vision_Light_Present']) or $data['vision_Light_Present'] == 'No')
		{
			unset($result['tabs'][$result['glzng']['idFormFields']]);
			unset($result['issues'][$result['glzng']['nextQuestionId']]);
		}
		/*END spec code for signage question */

		/*spec code for hidding fields for wall_rating_id < 4*/
		if ($data['wall_Rating'] < 4)
		{
			foreach (array(219,220) as $hid)
				unset($result['issues'][217]['answers'][$hid]);
		}
		/*END spec code for hidding fields for wall_rating_id > 3*/

		ksort($result['tabs']);
		$out = array();
		foreach ($result['tabs'] as $tab)
			$out[] = $tab;
		$result['tabs'] = $out;

		$userData['status'] = 'ok';
		$userData['issues'] = $result['issues'];
		
		$userlocation 	= $this->resources_model->get_user_buildings($user['parent']);
		$buildings = array();
		foreach ($userlocation as $loc)
			$buildings[$loc['idBuildings']] = $loc;
		$userlocation = $buildings;

		$updated_inspection = $this->resources_model->get_inspection_info_by_inspection_id($data['inspection_id']);//($user_id);

		$updated_inspection['building_name'] = @$userlocation[$updated_inspection['Building']]['name'];
		
		$updated_inspection['location_name'] = array();
		if ($updated_inspection['Floor'] > 0 && isset($userlocation[$updated_inspection['Floor']]['name']))
			$updated_inspection['location_name'][] = $userlocation[$updated_inspection['Floor']]['name'];
		if ($updated_inspection['Wing'] > 0 && isset($userlocation[$updated_inspection['Wing']]['name']))
			$updated_inspection['location_name'][] = $userlocation[$updated_inspection['Wing']]['name'];
		if ($updated_inspection['Area'] > 0 && isset($userlocation[$updated_inspection['Area']]['name']))
			$updated_inspection['location_name'][] = $userlocation[$updated_inspection['Area']]['name'];
		if ($updated_inspection['Level'] > 0 && isset($userlocation[$updated_inspection['Level']]['name']))
			$updated_inspection['location_name'][] = $userlocation[$updated_inspection['Level']]['name'];
		
		$updated_inspection['location_name'] = (!empty($updated_inspection['location_name'])) ? implode(' ', $updated_inspection['location_name']) : '';

		$userData['updated_inspection'] = $updated_inspection;

		$userData['tabs'] = $result['tabs'];

		// echo '<pre>';
		// print_r($userData);die();

		$this->_show_output($userData);
	}


	/*
	 * Update inspection data
	 *
	 * Input data:
	 * token			=> auth id from login
	 * inspection_id  	=> aperture id
	 * idFormFields  	=> Answer id
	 * status 		 	=> Answer status
	 * selected 	 	=> value of selected field
	 * Special 			=> question id if addbtn pressed
	 *
	 * Output data:
	 * status => ok
	 */
	function _exec_function_update_inspection_data($data)
	{
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


		if ((!empty($data['selected']) && $data['selected'] != 'NO') or in_array($data['idFormFields'], array(789789,789790))) //if not unselect action
		{
			if (!in_array($data['idFormFields'], array(789789,789790)))
				$answers = $this->service_model->get_question_answers_by_answer_id_and_inspection_id($field, $inspection); //get all answers from this answer question 

//!!!!!!			//надо такое условия чтобы все ответы которые радом с AddBtn включая её имели ид их вопроса (Special)
			if ((isset($data['Special']) && $data['Special'] != 'null') or in_array($data['idFormFields'], array(789789,789790)))
			{
				$qId = ((isset($data['Special']) && $data['Special'] != 'null')) ? $data['Special'] : $data['questionId'];
				
				$answers = $this->service_model->get_question_answers_by_question_id_and_inspection_id($qId, $inspection); //get all answers from this answer question 
				
				$apert_id = $this->service_model->get_aperture_id_by_inspection_id($inspection);
				$apertredata = $this->service_model->get_aperture_info_and_selected($apert_id['idAperture']);

				$square = 0; //total signs square

				//remove empty sign and add new value
				$addbtn = FALSE;

				//only if addbtn pressed save new value
				$newval = (in_array($data['idFormFields'], array(789789,789790))) ? FALSE : TRUE;
				foreach ($answers as $key=>&$answer)
				{
					//if send 0,0 that means DELETE 
					if ($answer['idFormFields'] == $field && $value == '0,0' && $data['idFormFields'] == 789789)
						$answer['selected'] = '';
					
					if (strlen($answer['selected']) > 0)
					{
						if ($data['idFormFields'] == 789789)
						{
							$dim = explode(',',$answer['selected']);
							$square += trim(@$dim[0]) * trim(@$dim[1]);
						}
					}
					elseif (!$newval)
					{
						$newval = $answer;
						$field = $answer['idFormFields']; //switch from addbtn id
						$answer['selected'] = $value;
					}
					else
					{
						if (!$addbtn)
							$addbtn	= $answer;
						unset($answers[$key]);
					}
				}

				//calc size of signs
				if ($data['idFormFields'] == 789789)
				{
					$signsize = 0;
					if ($square > 0)
						$signsize = $square * 100 / ($apertredata['width']['selected']*$apertredata['height']['selected']);
				}
				


				//sort answers
				$out = array();
				foreach ($answers as $answer)
				{
					if ($data['idFormFields'] == 789789 && $apertredata['wall_Rating'] < 4 && $signsize > 5) //if sizeof signs > 5% make it replace
						$answer['status'] = 4;
					$out[$answer['questionOrder']] = $answer;
				}
				ksort($out);
				$answers = $out;
			
				if (in_array($addbtn['questionId'], array(637, 640, 78)))
				{
					// add add btn
					$answers[] = array(
						'idFormFields' => '789789',
						'type' => 'answer',
						'nextQuestionId' => 0,
						'name' => 'AddSignBtn',
						'label' => 'Add Sign',
						'questionId' => $addbtn['questionId'],
						'questionOrder' => count($answers)+1,
						'status' => '',
						'selected' => ''
					);
				}
				elseif (in_array($addbtn['questionId'], array(323,344,15,566,50)))
				{
					// add add btn
					$answers[] = array(
						'idFormFields' => '789790',
						'type' => 'answer',
						'nextQuestionId' => 0,
						'name' => 'AddHoleBtn',
						'label' => 'Add Hole',
						'questionId' => $addbtn['questionId'],
						'questionOrder' => count($answers)+1,
						'status' => '',
						'selected' => ''
					);
				}
		
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

	/*
	 * Add Inspection
	 *
	 * Input data:
	 * token			=> auth id from login
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
		
		if (!isset($data['location']) or empty($data['location']))
		{
			$userData['status'] = 'error';
			$userData['error'] = 'not isset or empty input parameter location';
			$this->_show_output($userData);
		}
		
		$this->load->model('user_model');
		$this->load->model('resources_model');
		
		$user_id = $data['tokendata']['user_id'];

		$user = $this->user_model->get_user_info_by_user_id($user_id);
			
		$userlocation = $this->resources_model->get_user_buildings($user['parent']);
		$buildings 	  = array();
		foreach ($userlocation as $loc)
		{
			// if ($loc['level'] == 0 && empty($b0))
				// $b0 = $loc;
			$buildings[$loc['level']][$loc['name']] = $loc;
		}
		$userlocation = $buildings;

		//for compatability with old version
		/*if (isset($data['location_id']) && !empty($data['location_id']))
		{
			$lvls = array(
				0 => 'Building',
				1 => 'Floor',
				2 => 'Wing',
				3 => 'Area',
				4 => 'Level'
			);

			$lv = $userlocation[$data['location_id']]['level'];
			$apert_adddata[$lvls[$lv]] = $data['location_id'];
			$nextid = $userlocation[$data['location_id']]['parent'];
			
			for ($i=$lv-1; $i >=0 ; $i--)
			{ 
				$apert_adddata[$lvls[$i]] = $nextid;
				$nextid = $userlocation[$data['location_id']]['parent'];
			}
		}*/

		$aperture = $this->resources_model->get_aperture_info_by_barcode($data['barcode'], $user['parent']);

		$this->load->library('History_library');
	
		//Locations handler	
		$apert_adddata['Building']	= $userlocation[0][$data['location']['Building']]['idBuildings'];
		if (isset($data['location']['Floor']) && $data['location']['Floor'] != 'N/A')
		{
			$apert_adddata['Floor']	= $userlocation[1][$data['location']['Floor']]['idBuildings'];
			$location_name[] = $data['location']['Floor'];
		}
		if (isset($data['location']['Wing']) && $data['location']['Wing'] != 'N/A')
		{
			$apert_adddata['Wing']	= $userlocation[2][$data['location']['Wing']]['idBuildings'];
			$location_name[] = $data['location']['Wing'];
		}
		if (isset($data['location']['Area']) && $data['location']['Area'] != 'N/A')
		{
			$apert_adddata['Area']	= $userlocation[3][$data['location']['Area']]['idBuildings'];
			$location_name[] = $data['location']['Area'];
		}
		if (isset($data['location']['Level']) && $data['location']['Level'] != 'N/A')
		{
			$apert_adddata['Level']	= $userlocation[4][$data['location']['Level']]['idBuildings'];
			$location_name[] = $data['location']['Level'];
		}
		$building_name = $data['location']['Building'];
		$location_name = (!empty($location_name)) ? implode(' ', $location_name) : '';

		//if new aperture add it
		if (empty($aperture))
		{
			$location_name = array();
			$apert_adddata['barcode'] 	= $data['barcode'];
			$apert_adddata['UserId'] 	= $user['parent'];
			

			$apert_adddata['IntExt']	= $data['location']['IntExt'];

			$aperture_id = $this->resources_model->add_aperture($apert_adddata);

			$this->history_library->saveDoors(array('user_id' => $user_id, 'line_id' => $aperture_id, 'new_val' => json_encode($apert_adddata), 'type' => 'add'));
		}
		else
			$aperture_id = $aperture['idDoors'];

		$available_review = $this->resources_model->get_client_inspection_by_aperture_id($aperture_id, $user['parent']);
		
		//if aperture present and inspection present
		if (!empty($available_review))
		{
			$available_review['id'] = $available_review['idInspections'];
			unset($available_review['Completion'], $available_review['StartDate'], $available_review['revision'], $available_review['Buildings_idBuildings'], $available_review['idInspections'], $available_review['deleted']);

			$available_review['building_name'] = $building_name;
			$available_review['location_name'] = $location_name;

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
		$adddata['building_name'] = $building_name;
		$adddata['location_name'] = @$location_name;

		$userData['status'] = 'ok';
		$userData['CreatedInspection'] 	= $adddata;
		$this->_show_output($userData);
	}

	/*
	 * Check door Unique ID
	 *
	 * Input data:
	 * token			=> auth id from login
	 * barcode 			=> QR or scancode of door in 1-6 digit format
	 * olddata  		=> marker, if presend means that need refresh locations fields
	 * newdata  		=> to compare with olddata for refresh locations fields
	 *
	 * Output data:
	 * status 			=> ok if inspection not present
	 * barcode 			=> same barcode from input data
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
		$user = $this->user_model->get_user_info_by_user_id($user_id);

		$aperture = $this->resources_model->get_aperture_info_by_barcode($data['barcode'], $user['parent']);
		
		if (!empty($aperture))
		{
			$userData['status'] = 'error';
			$userData['error'] = 'Door ID exists already. Please enter in new Door ID or click on existing Door ID to edit.';
			$this->_show_output($userData);
		}

		if (isset($data['olddata']) && isset($data['newdata']))
		{
			$newdata = $data['newdata'];
			$olddata = $data['olddata'];
		}


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

		$building = (!empty($aperture['Building']) && $aperture['Building'] != 0 && isset($buildings[$aperture['Building']])) ? $buildings[$aperture['Building']] : $buildings_values[0];
		
		if (isset($olddata) && isset($newdata))
			$building = $newdata['Building'];

		$locatio[] = array('name' => 'Building', 'label' => 'Building', 'selected' => $building, 'type' => 'enum', 'values' => $buildings_values, 'enabled' => TRUE, 'force_refresh' => 1);

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
				$floor = (!empty($aperture['Floor']) && $aperture['Floor'] != 0 && isset($floors[$aperture['Floor']])) ? $floors[$aperture['Floor']] : $floors_values[0];
			
			$locatio[] = array('name' => 'Floor', 'label' => 'Floor', 'selected' => $floor, 'type' => 'enum', 'values' => $floors_values, 'enabled' => TRUE, 'force_refresh' => 1);

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
					$wing = (!empty($aperture['Wing']) && $aperture['Wing'] != 0 && isset($wings[$aperture['Wing']])) ? $wings[$aperture['Wing']] : $wings_values[0];
				
				$locatio[] = array('name' => 'Wing', 'label' => 'Wing', 'selected' => $wing, 'type' => 'enum', 'values' => $wings_values, 'enabled' => TRUE, 'force_refresh' => 1);

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
						$area = (!empty($aperture['Area']) && $aperture['Area'] != 0 && isset($areas[$aperture['Area']])) ? $areas[$aperture['Area']] : $areas_values[0];
					
					$locatio[] = array('name' => 'Area', 'label' => 'Area', 'selected' => $area, 'type' => 'enum', 'values' => $areas_values, 'enabled' => TRUE, 'force_refresh' => 1);

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
							$level = (!empty($aperture['Level']) && $aperture['Level'] != 0 && isset($levels[$aperture['Level']])) ? $levels[$aperture['Level']] : $levels_values[0];
						
						$locatio[] = array('name' => 'Level', 'label' => 'Level', 'selected' => $level, 'type' => 'enum', 'values' => $levels_values, 'enabled' => TRUE, 'force_refresh' => 1);
					}
					else
						$locatio[] = array('name' => 'Level', 'label' => 'Level', 'selected' => 'N/A', 'type' => 'enum', 'values' => array('N/A'), 'enabled' => FALSE, 'force_refresh' => 1);
				}
				else
				{
					$locatio[] = array('name' => 'Area', 'label' => 'Area', 'selected' => 'N/A', 'type' => 'enum', 'values' => array('N/A'), 'enabled' => FALSE, 'force_refresh' => 1);
					$locatio[] = array('name' => 'Level', 'label' => 'Level', 'selected' => 'N/A', 'type' => 'enum', 'values' => array('N/A'), 'enabled' => FALSE, 'force_refresh' => 1);
				}
			}
			else
			{
				$locatio[] = array('name' => 'Wing', 'label' => 'Wing', 'selected' => 'N/A', 'type' => 'enum', 'values' => array('N/A'), 'enabled' => FALSE, 'force_refresh' => 1);
				$locatio[] = array('name' => 'Area', 'label' => 'Area', 'selected' => 'N/A', 'type' => 'enum', 'values' => array('N/A'), 'enabled' => FALSE, 'force_refresh' => 1);
				$locatio[] = array('name' => 'Level', 'label' => 'Level', 'selected' => 'N/A', 'type' => 'enum', 'values' => array('N/A'), 'enabled' => FALSE, 'force_refresh' => 1);
			}
		}
		else
		{
			$locatio[] = array('name' => 'Floor', 'label' => 'Floor', 'selected' => 'N/A', 'type' => 'enum', 'values' => array('N/A'), 'enabled' => FALSE, 'force_refresh' => 1);
			$locatio[] = array('name' => 'Wing', 'label' => 'Wing', 'selected' => 'N/A', 'type' => 'enum', 'values' => array('N/A'), 'enabled' => FALSE, 'force_refresh' => 1);
			$locatio[] = array('name' => 'Area', 'label' => 'Area', 'selected' => 'N/A', 'type' => 'enum', 'values' => array('N/A'), 'enabled' => FALSE, 'force_refresh' => 1);
			$locatio[] = array('name' => 'Level', 'label' => 'Level', 'selected' => 'N/A', 'type' => 'enum', 'values' => array('N/A'), 'enabled' => FALSE, 'force_refresh' => 1);
		}

		$IntExt = $this->service_model->get_enum_values('Doors', 'IntExt');
		if (isset($olddata) && isset($newdata))
			$selected = $newdata['IntExt'];
		else
			$selected = (!empty($aperture['IntExt']) && $aperture['IntExt'] != 0) ? $aperture['IntExt'] : $IntExt[0];
		$locatio[] = array('name' => 'IntExt', 'label' => 'Interior / Exterior?', 'selected' => $selected, 'type' => 'enum', 'values' => $IntExt, 'enabled' => TRUE, 'force_refresh' => 0);

		$userData['location'] = $locatio;
		$userData['status']   = 'ok';
		$userData['barcode']  = $data['barcode'];

// echo '<pre>';
// print_r($userData);die();

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

	/*
	 * Delete inspection and it door
	 *
	 * Input data:
	 * token			=> auth id from login
	 * inspection_id  	=> aperture id
	 * aperture_id  	=> aperture id
	 *
	 * Output data:
	 * status => ok
	 */
	function _exec_function_delete_inspection($data)
	{
		if (!isset($data['inspection_id']) or empty($data['inspection_id']))
		{
			$userData['status'] = 'error';
			$userData['error'] = 'not isset or empty input parameter inspection_id';
			$this->_show_output($userData);
		}
		
		if (!isset($data['aperture_id']) or empty($data['aperture_id']))
		{
			$userData['status'] = 'error';
			$userData['error'] = 'not isset or empty input parameter aperture_id';
			$this->_show_output($userData);
		}

		$this->load->model('resources_model');
		$this->load->model('user_model');
		
		$user_id = $data['tokendata']['user_id'];
		$user = $this->user_model->get_user_info_by_user_id($user_id);

		$this->resources_model->delete_aperture_by_id($data['aperture_id'], $user);
		$this->resources_model->delete_review_by_id($data['inspection_id'], $user);

		$userData['status']   = 'ok';

		$this->_show_output($userData);
	}
}

/* End of file service.php */
/* Location: ./application/controllers/service.php */