<?php
class Rules extends CI_Controller {

	function __construct()
	{
		parent::__construct();
		verifyLogged();
		$this->load->model('rules_model');
	}

	function index()
	{
		if (!in_array($this->session->userdata('user_role'), array(1,4))) 
			redirect('/user/profile');

		$rules 		= $this->rules_model->get_all_rules();
		$roles 		= $this->rules_model->get_all_roles();

		if ($postdata = $this->input->post())
		{
			$this->load->library('History_library');

			$this->history_library->saveRR(array('line_id' => $this->session->userdata('user_parent'), 'new_val' => json_encode($postdata), 'type' => 'edit'));

			foreach ($rules as $rule)
			{
				foreach ($roles as $role)
				{
					$this->rules_model->update_role_permission($rule['idRules'], $role['idRoles'], $this->session->userdata('user_parent'), @$postdata['rr'][$rule['idRules']][$role['idRoles']]);
				}
			}

			$data['msg'] = '<div class="alert alert-success alert-dismissable">User rules successfully updated</div>';
		}

		$result		= $this->rules_model->get_all_rolesrules($this->session->userdata('user_parent'));

		foreach ($result as $rolesrulesdata)
			$rulesroles[$rolesrulesdata['idRules']][$rolesrulesdata['idRoles']] = $rolesrulesdata['value'];

		$output = array();
		foreach ($rules as $rule)
		{
			$data['rules'][$rule['idRules']] = $rule;
			

			foreach ($roles as $role)
			{
				$data['roles'][$role['idRoles']] = $role;
				
				$data['rulesroles'][$rule['idRules']][$role['idRoles']]=isset($rulesroles[$rule['idRules']][$role['idRoles']]) ? $rulesroles[$rule['idRules']][$role['idRoles']] : 0;
				
				if ($this->session->userdata('user_role') != 4)
				{
					unset($data['roles'][4]);
					if (	$rule['group'] == 'admin')
						unset($data['rulesroles'][$rule['idRules']], $data['rules'][$rule['idRules']]);
				}
			}
		}

		if ($this->session->userdata('user_role') != 4)
		{
			unset($data['roles'][4]);
		}

		$header['page_title'] = 'USER RULES';

		$this->load->view('header', $header);
		$this->load->view('admin/admin_rules', $data);
		$this->load->view('footer');
	}

}

/* End of file rules.php */
/* Location: ./application/controllers/rules.php */