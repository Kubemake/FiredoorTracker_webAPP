<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Clients extends CI_Controller {

	function __construct()
	{
		parent::__construct();
		verifyLogged('admin');
		$this->load->model('admin_model');
		$this->load->library('table');
	}

		function index()
	{
		$this->lang->load('resources');
		$this->load->library('table');
		$data = array();
		if ($this->input->post())
		{
			if ($this->input->post('form_type'))
			{
				$this->load->library('History_library');

				$this->load->model('resources_model');
				
				$postdata = $this->input->post();

				// echo '<pre>';
				// print_r($postdata);die();
				$adddata = array(
					'email'			=> $postdata['email'],
				    'FirstName'		=> $postdata['first_name'],
				    'LastName'		=> $postdata['last_name'],
				    'officePhone'	=> $postdata['officePhone'], 
				    'mobilePhone'	=> $postdata['mobilePhone'], 
				    'role'			=> $postdata['user_role'],
				    'parent'		=> $this->session->userdata('user_parent')
				);

				$addlicdata = FALSE;
				if ($this->session->userdata('user_role') == 4 && $postdata['user_role'] == 1) //if add client(director) and admin do it
				{
					$this->load->model('licensing_model');
					$addlicdata = array(
					    'expired'		=> $postdata['license_expiration_date'],
						'dir'			=> $postdata['license_dir'],
						'sv'			=> $postdata['license_sv'],
						'mech'			=> $postdata['license_mech'],
						'inspections'	=> $postdata['license_inspections']
					);
				}

				if ($postdata['user_role'] == 4)
					$adddata['parent'] = 0;

				if (isset($postdata['new_password']) && !empty($postdata['new_password']))
					$adddata['password'] = pass_crypt($postdata['new_password']);

				if (isset($postdata['password_generator']) && $postdata['password_generator']=='generate')			//if selected generate password - send it by email
				{
					$mail = send_mail(
						$adddata['email'],
						$this->lang->line('email_add_employeer_subject'),
						sprintf($this->lang->line('email_add_employeer_body'),  $_SERVER['HTTP_HOST'], $adddata['email'], $postdata['new_password'])
					);
				}

				switch ($postdata['form_type'])
				{
					case 'add_client':
						$user = $this->resources_model->get_user_by_email($adddata['email']); //check if it email used
						if (!empty($user)) {
							$data['msg'] = '<div class="alert alert-warning alert-dismissable">This email allready used</div>';
							break;
						}
						$newemp = $this->resources_model->add_employer($adddata);	//add new user
												
						if ($postdata['user_role'] == 1)
						{
							$this->resources_model->update_employer_data($newemp, array('parent' => $newemp));
							$adddata['parent'] = $newemp;
						}

						$this->history_library->saveUsers(array('line_id' => $newemp, 'new_val' => json_encode($adddata), 'type' => 'add'));

						if ($this->session->userdata('user_role') == 4 && $postdata['user_role'] == 1)
						{
							$addlicdata['idUsers'] = $newemp;
							$licid = $this->licensing_model->add_licensing_data($addlicdata);

							$this->history_library->saveLic(array('line_id' => $licid, 'new_val' => json_encode($addlicdata), 'type' => 'add'));
						}
						
						$mail 	= TRUE;
				
						$data['msg'] = '<div class="alert alert-warning alert-dismissable">Something wrong!</div>';

						if ($newemp && $mail)
							$data['msg'] = '<div class="alert alert-success alert-dismissable">User successfully added</div>';
					break;

					case 'edit_client':
						if ($postdata['user_role']==1)
							$adddata['parent'] = $postdata['user_id'];

						$this->history_library->saveUsers(array('line_id' => $postdata['user_id'], 'new_val' => json_encode($adddata), 'type' => 'edit'));
						
						$this->resources_model->update_employer_data($postdata['user_id'], $adddata);
						
						if ($this->session->userdata('user_role') == 4 && $postdata['user_role'] == 1)
						{
							$this->history_library->saveLic(array('line_id' => $postdata['user_id'], 'new_val' => json_encode($addlicdata), 'type' => 'edit'));

							$this->licensing_model->update_licensing_data($postdata['user_id'], $addlicdata);
						}

						$data['msg'] = '<div class="alert alert-success alert-dismissable">User successfully updated</div>';
					break;
					default:
					break;
				}

			}
		}

		if (has_permission('Allow view clients tab'))
		{
			$this->table->set_heading(
				'id',
				'First Name',
				'Last Name',
				array('data' => 'Office Phone'	, 'class' => 'not-mobile'),
				array('data' => 'Mobile Phone'	, 'class' => 'not-mobile'),
				array('data' => 'Email'			, 'class' => 'not-mobile'),
				array('data' => 'Role'			, 'class' => 'not-mobile')
			);

			$users = $this->admin_model->get_all_clients_data();

			if (!empty($users))
			{
				foreach ($users as $user)
				{
					if ($user['parent'] != $user['idUsers'])
						continue;

					$this->table->add_row($user['idUsers'], $user['firstName'], $user['lastName'], $user['officePhone'], $user['mobilePhone'], $user['email'], $user['role_name']);
				}
			}
			

			$tmpl = array ( 'table_open'  => '<table class="table table-striped table-hover table-bordered table-responsive table-condensed" width="100%">' );
			$this->table->set_template($tmpl); 
			$data['result_table'] = $this->table->generate(); 
		}

		$header['page_title'] = 'CLIENTS';

		//datatables
		$header['styles']  = addDataTable('css');
		$footer['scripts'] = addDataTable('js');

		//datepicker
		$header['styles']  .= '<link href="/js/bootstrap-datepicker/datepicker.css" rel="stylesheet">';
		$footer['scripts'] .= '<script type="text/javascript" src="/js/bootstrap-datepicker/bootstrap-datepicker.js"></script>';

		//password
		$footer['scripts'] .= '<script type="text/javascript" src="/js/bootstrap-show-password.min.js"></script>';

		$this->load->view('header', $header);
		$this->load->view('user/user_clients', $data);
		$this->load->view('footer', $footer);
	}

}

/* End of file resources.php */
/* Location: ./application/controllers/resources.php */