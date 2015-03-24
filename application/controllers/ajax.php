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
				$params['info_type'] 		= $this->input->post('type');
			break;
			
			case 'edit_info_modal':
				if (!$info_id = $this->input->post('id')) return '';
				$this->load->model('info_model');
				$params['info_type']  = $this->input->post('type');
				$params['info'] = $this->info_model->get_info_info_by_info_id($info_id);
			break;

			case 'add_inspection_modal':
				$this->load->model('user_model');
				$params['user_buildings'] 		= $this->resources_model->get_user_buildings();
				$params['user_apertures'] 		= $this->resources_model->get_user_apertures();
				$params['inspection_statuses'] 	= $this->resources_model->get_all_inspection_statuses();
				$params['users_reviewer'] 		= $this->user_model->get_users_by_role_and_user_parent(3, $this->session->userdata('user_parent')); //Only mechanics
			break;

			case 'edit_inspection_modal':
				if (!$inspection_id = $this->input->post('id')) return '';
				$this->load->model('user_model');
				$params['inspection']  			= $this->resources_model->get_inspection_info_by_inspection_id($inspection_id);
				$params['user_buildings'] 		= $this->resources_model->get_user_buildings();
				$params['user_apertures'] 		= $this->resources_model->get_user_apertures();
				$params['inspection_statuses'] 	= $this->resources_model->get_all_inspection_statuses();
				$params['users_reviewer'] 		= $this->user_model->get_users_by_role_and_user_parent(3, $this->session->userdata('user_parent')); //Only mechanics
			break;

			case 'add_employeer_modal':
				$params['user_roles'] = $this->resources_model->get_all_employeers_roles();
			break;

			case 'edit_employeer_modal':
				if (!$employeer_id = $this->input->post('id')) return '';
				$params['user_roles'] = $this->resources_model->get_all_employeers_roles();
				$params['employeer']  = $this->resources_model->get_employeer_info_by_employeer_id($employeer_id);
			break;

			case 'add_aperture_modal':
				$this->load->model('user_model');
				$params['wall_rating'] 		= $this->config->item('wall_rates');
				$params['smoke_rating'] 	= $this->config->item('rates_types');
				$params['material'] 		= $this->config->item('door_matherial');
				$params['rating'] 			= $this->config->item('door_rating');
				$params['user_buildings'] 	= $this->resources_model->get_user_buildings();
			break;

			case 'edit_aperture_modal':
				$this->load->model('user_model');
				$params['wall_rating'] 		= $this->config->item('wall_rates');
				$params['smoke_rating'] 	= $this->config->item('rates_types');
				$params['material'] 		= $this->config->item('door_matherial');
				$params['rating'] 			= $this->config->item('door_rating');
				$params['user_buildings'] 	= $this->resources_model->get_user_buildings();
				if (!$aperture_id = $this->input->post('id')) return '';
				$params['aperture'] = $this->resources_model->get_aperture_info_by_aperture_id($aperture_id);
				// echo '<pre>';
				// print_r($params['wall_rating']);
				// print_r($params['aperture']);die();
			break;

			case 'add_issue_modal':
				$params['issue_types'] = $this->resources_model->get_issue_types();
			break;

			case 'edit_file_modal':
				if (!$file_id = $this->input->post('id')) return '';
				$this->load->model('media_model');
				$params['user_buildings'] 	= $this->resources_model->get_user_buildings();
				$params['file']  			= $this->media_model->get_file_data_by_id($file_id);
				$params['user_apertures'] 	= $this->resources_model->get_user_apertures();
				// echo '<pre>';
				// print_r($params['file']);die();
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

}

/* End of file ajax.php */
/* Location: ./application/controllers/ajax.php */