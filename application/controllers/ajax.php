<?php
class Ajax extends CI_Controller {

	function __construct()
	{
		parent::__construct();
		verifyLogged();
		$this->load->model('resources_model');
	}

	function ajax_load_modal()
	{
		if (!$page = $this->input->post('page')) return '';
		$params = array();

		switch ($page) {
			case 'add_info_modal':
				$params['info_type'] 			= $this->input->post('type');
			break;
			
			case 'add_user_building_modal':
				$params['parent'] 				= $this->input->post('parent');
				$params['level'] 				= $this->input->post('level');
			break;
			
			case 'edit_info_modal':
				if (!$info_id = $this->input->post('id')) return '';
				$this->load->model('info_model');
				$params['info_type']  			= $this->input->post('type');
				$params['info'] 				= $this->info_model->get_info_info_by_info_id($info_id);
			break;

			case 'edit_expert_modal':
				if (!$expert_id = $this->input->post('id')) return '';
				$this->load->model('info_model');
				$params['expert'] 				= $this->info_model->get_expert_info_by_expert_id($expert_id);
				// echo '<pre>';
				// print_r($params['expert']);die();
			break;

			case 'add_inspection_modal':
				$this->load->model('user_model');
				$params['user_buildings'] 		= $this->resources_model->get_user_buildings();
				$params['user_apertures'] 		= $this->resources_model->get_user_apertures_without_review();
				// $params['inspection_statuses'] 	= $this->resources_model->get_all_inspection_statuses();
				
				$params['users_reviewer'] = $this->session->userdata('user_id');
				if ($this->session->userdata('user_role')!=3)
					$params['users_reviewer'] 		= $this->user_model->get_users_by_role_and_user_parent($this->session->userdata('user_role'), $this->session->userdata('user_parent'));
			break;

			case 'edit_inspection_modal':
				if (!$inspection_id = $this->input->post('id')) return '';
				$this->load->model('user_model');
				$params['inspection']  			= $this->resources_model->get_inspection_info_by_inspection_id($inspection_id);

				$bid = ($params['inspection']['Level'] > 0 ) ? $params['inspection']['Level'] : $params['inspection']['Area']; //find location of inspection door
				if ($params['inspection']['Area'] < 1)
				{
					$bid = $params['inspection']['Wing'];
					if ($params['inspection']['Wing'] < 1)
					{
						$bid = $params['inspection']['Floor'];
						if ($params['inspection']['Floor'] < 1)
							$bid = $params['inspection']['Building'];
					}
				}
				$params['user_buildings'] 		= $this->resources_model->get_user_buildings();
				$params['current_location']		= $bid;
				$params['user_apertures'] 		= $this->resources_model->get_user_apertures_without_review($bid);
				$params['user_apertures'][] 	= array('idDoors' => $params['inspection']['idAperture'], 'barcode' => $params['inspection']['barcode']);
				$params['inspection_statuses'] 	= $this->resources_model->get_all_inspection_statuses();

				$params['users_reviewer'] = $this->session->userdata('user_id');
				if ($this->session->userdata('user_role')!=3)
					$params['users_reviewer'] 		= $this->user_model->get_users_by_role_and_user_parent($this->session->userdata('user_role'), $this->session->userdata('user_parent'));
			break;

			case 'show_inspection_modal':
				if (!$aperture_id = $this->input->post('door_id')) return '';
				if (!$inspection_id = $this->input->post('insp_id')) return '';
				
				$this->load->model('service_model');

				$door_settings = $this->resources_model->get_aperture_info_by_aperture_id($aperture_id);

				$door_settings['inspection_id'] = $inspection_id;
				
				$result = $this->service_model->get_aperture_issues_and_selected($door_settings);

				$params['tabs'] = $result['tabs'];
				$params['aperture_id'] 		= $aperture_id;
				$params['inspection_id'] 	= $inspection_id;

				$params['oth'] = array();
				
				foreach ($result['issues'] as $issue)
					if (!empty($issue['answers']))
						foreach ($issue['answers'] as $answer)
							if (strpos($answer['name'], 'Other') !== FALSE)
								$params['oth'][] = $answer;

				unset($result);
			break;

			case 'add_employeer_modal':
				$params['user_roles'] 			= $this->resources_model->get_all_employeers_roles();
			break;

			case 'edit_employeer_modal':
				if (!$employeer_id = $this->input->post('id')) return '';
				$params['user_roles'] 			= $this->resources_model->get_all_employeers_roles();
				$params['employeer']  			= $this->resources_model->get_employeer_info_by_employeer_id($employeer_id);
			break;

			case 'add_client_modal':
				$params['user_roles'] 			= $this->resources_model->get_all_employeers_roles();
			break;

			case 'edit_client_modal':
				if (!$employeer_id = $this->input->post('id')) return '';
				$this->load->model('licensing_model');
				$params['user_roles'] 			= $this->resources_model->get_all_employeers_roles();
				$params['client']  				= $this->resources_model->get_employeer_info_by_employeer_id($employeer_id);
				$params['licensing']			= $this->licensing_model->get_lic_info_by_client_id($employeer_id);
			break;

			case 'add_aperture_modal':
				$this->load->model('user_model');

				$builds = $this->resources_model->get_user_buildings_root();

				foreach ($builds as $key => $value)
					$params['building'][$key] = $value['name'];

				if (empty($params['building']))
					echo ('<script type="text/javascript">alert(\'You need fill at least one building in Building tab, before adding door.\' + "\n" + \'You well be redirected to Building tab after press OK\');window.location = "/user/buildings";</script>');

				$params['wall_rating'] 			= $this->config->item('wall_rates');
				$params['smoke_rating'] 		= $this->config->item('rates_types');
				$params['material'] 			= $this->config->item('door_matherial');
				$params['rating'] 				= $this->config->item('door_rating');
			break;

			case 'edit_aperture_modal':
				if (!$aperture_id = $this->input->post('id')) return '';

				$builds = $this->resources_model->get_user_buildings_root();

				foreach ($builds as $key => $value)
					$params['building'][$key] = $value['name'];

				$params['aperture'] 			= $this->resources_model->get_aperture_info_by_aperture_id($aperture_id);
			break;

			case 'add_issue_modal':
				$params['issue_types'] = $this->resources_model->get_issue_types();
			break;

			case 'edit_file_modal':
				if (!$file_id = $this->input->post('id')) return '';
				$this->load->model('media_model');
				$params['user_buildings'] 		= $this->resources_model->get_user_buildings();
				$params['file']  				= $this->media_model->get_file_data_by_id($file_id);
				$params['user_apertures'] 		= $this->resources_model->get_user_apertures();
			break;

			case 'choose_condition_value_modal':
				if (!$id 				= $this->input->post('id')) 				return '';
				if (!$wall_rate_id 		= $this->input->post('wall_rate_id')) 		return '';
				if (!$ratestypesid 		= $this->input->post('ratestypesid')) 		return '';
				if (!$doormatherialid 	= $this->input->post('doormatherialid')) 	return '';
				if (!$doorratingid 		= $this->input->post('doorratingid')) 		return '';
				
				$params['id'] 					= $id;
				$params['wall_rate_id']			= $wall_rate_id;
				$params['ratestypesid'] 		= $ratestypesid;
				$params['doormatherialid'] 		= $doormatherialid;
				$params['doorratingid'] 		= $doorratingid;
				$params['door_states'] 			= $this->config->item('door_state');
				$params['thisvalue'] 			= @$this->input->post('thisvalue');
			break;

			case 'customize_review_list_modal':
				$this->load->model('user_model');
				$this->load->model('resources_model');

				$params['users'] 		= $this->user_model->get_users_by_role_and_user_parent($this->session->userdata('user_role'), $this->session->userdata('user_parent'));
				$params['buildings']	= $this->resources_model->get_user_buildings_root();
				$params['statuses']		= $this->resources_model->get_all_inspection_statuses();
				$params['criteria'] 	= array(
					'wall_Rating' 				=> array('label' => 'Wall Rating', 'data' => $this->config->item('wall_rates')),
					'smoke_Rating' 				=> array('label' => 'Smoke Rating', 'data' => array('Yes', 'No')),
					'material' 					=> array('label' => 'Material', 'data' => $this->config->item('door_matherial')),
					'door_type'					=> array('label' => 'Door Type', 'data' => $this->resources_model->get_available_values_by_parent('Doors', 'door_type')),
					'width'						=> array('label' => 'Width', 'data' => $this->resources_model->get_available_values_by_parent('Doors', 'width')),
					'height'					=> array('label' => 'Height', 'data' => $this->resources_model->get_available_values_by_parent('Doors', 'height')),
					'number_Doors'				=> array('label' => 'Number Doors', 'data' => $this->resources_model->get_available_values_by_parent('Doors', 'number_Doors')),
					'vision_Light_Present'		=> array('label' => 'Vision Light Present', 'data' => array('Yes', 'No')),
					'vision_Light'				=> array('label' => 'Vision Light', 'data' => $this->resources_model->get_available_values_by_parent('Doors', 'vision_Light')),
					'singage'					=> array('label' => 'Singage', 'data' => array('Yes', 'No')),
					'frameLabel_Type'			=> array('label' => 'Frame Label Type', 'data' => $this->resources_model->get_available_values_by_parent('Doors', 'frameLabel_Type')),
					'doorLabel_Type'			=> array('label' => 'Door Label Type', 'data' => $this->resources_model->get_available_values_by_parent('Doors', 'doorLabel_Type')),
					'frameLabel_Rating'			=> array('label' => 'Frame Label Rating', 'data' => $this->resources_model->get_available_values_by_parent('Doors', 'frameLabel_Rating')),
					'rating'		 			=> array('label' => 'Door Label Rating', 'data' => $this->config->item('door_rating')),
					'frameLabel_Testing_Lab'	=> array('label' => 'Frame Label Testing_Lab', 'data' => $this->resources_model->get_available_values_by_parent('Doors', 'frameLabel_Testing_Lab')),
					'doorLabel_Testing_Lab'		=> array('label' => 'Door Label Testing Lab', 'data' => $this->resources_model->get_available_values_by_parent('Doors', 'doorLabel_Testing_Lab')),
					'frameLabel_Manufacturer' 	=> array('label' => 'Frame Label Manufacturer', 'data' => $this->resources_model->get_available_values_by_parent('Doors', 'frameLabel_Manufacturer')),
					'doorLabel_Manufacturer'	=> array('label' => 'Door Label Manufacturer', 'data' => $this->resources_model->get_available_values_by_parent('Doors', 'doorLabel_Manufacturer')),
					'frameLabel_serial'			=> array('label' => 'Frame Label serial', 'data' => $this->resources_model->get_available_values_by_parent('Doors', 'frameLabel_serial')),
					'doorLabel_serial'			=> array('label' => 'Door Label serial', 'data' => $this->resources_model->get_available_values_by_parent('Doors', 'doorLabel_serial')),
					'doorLabel_Min_Latch'		=> array('label' => 'Door Label Min_Latch', 'data' => $this->resources_model->get_available_values_by_parent('Doors', 'doorLabel_Min_Latch')),
					'doorLabel_Temp_Rise'		=> array('label' => 'Door Label Temp Rise', 'data' => $this->resources_model->get_available_values_by_parent('Doors', 'doorLabel_Temp_Rise')),
					'auto_Operator'				=> array('label' => 'Auto Operator', 'data' => array('Yes', 'No')),
				);
				
				$tabs = $issues = array();
				foreach ($this->resources_model->get_all_issues() as $issue)
				{
					if ($issue['type'] == 'answer')
					{
						if ($issue['parent'] == 0)
							$tabs[$issue['idFormFields']] = $issue;
						else
							$issues[$issue['questionId']]['answers'][$issue['idFormFields']] = $issue;
					}
					else
						$issues[$issue['idFormFields']] = $issue;
				}
				$params['issues'] 	= $issues;
				$params['tabs']		= $tabs;

				$params['oth'] = array();
				
				foreach ($issues as $issue)
					if (!empty($issue['answers']))
						foreach ($issue['answers'] as $answer)
							if (strpos($answer['name'], 'Other') !== FALSE)
								$params['oth'][] = $answer;

			break;

			case 'send_email_modal':
				$this->load->model('user_model');
				$params['users'] 		= $this->user_model->get_users_by_role_and_user_parent($this->session->userdata('user_role'), $this->session->userdata('user_parent'));
			break;

			default:
				# code...
			break;
		}
		$this->load->view('modal/' . $page, $params);
	}

	function ajax_check_email()
	{
		if (!$email = $this->input->post('email')) return '';
		$user = $this->resources_model->get_user_by_email($email);
		$msg = 'busy';
		if (empty($user))
			$msg = 'free';
		return print($msg);
	}

	function ajax_load_inspection_issues_by_tab()
	{
		if (!$aperture_id = $this->input->post('door_id')) return '';
		if (!$tab_id = $this->input->post('tabid')) return '';
		if (!$inspection_id = $this->input->post('inspection_id')) return '';

		$this->load->model('service_model');

		$door_settings = $this->resources_model->get_aperture_info_by_aperture_id($aperture_id);

		$door_settings['inspection_id'] = $inspection_id;
		
		$result = $this->service_model->get_aperture_issues_and_selected($door_settings);
		if (isset($result['tabs'][$tab_id]))
		{
			$params['tabnextQuestionId'] = $result['tabs'][$tab_id]['nextQuestionId'];
			$params['issues'] = $result;
			$params['inspection_id'] = $inspection_id;

			$this->load->view('modal/view_issues_by_tab', $params);
		}
	}

	function ajax_addbtnaction()
	{
		if (!$inspection_id = $this->input->post('inspection_id')) return '';
		if (!$question_id = $this->input->post('question_id')) return '';
		if (!$btnid = $this->input->post('btnid')) return '';

		$this->load->model('service_model');

		foreach ($this->service_model->get_question_answers_by_question_id_and_inspection_id($question_id, $inspection_id) as $answer)
		{
		 	if (empty($answer['selected']))
		 	{
		 		$this->service_model->add_inspection_data($inspection_id, $answer['idFormFields'], @$this->input->post('val'));
		 		break;
		 	}
		} 
		echo 'ok';
	}
}

/* End of file ajax.php */
/* Location: ./application/controllers/ajax.php */