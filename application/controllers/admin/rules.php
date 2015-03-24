<?php
class Rules extends CI_Controller {

	function __construct()
	{
		parent::__construct();
		verifyLogged('admin');
		$this->load->model('rules_model');
	}

	function index()
	{
		$rules 		= $this->rules_model->get_all_rules();
		$roles 		= $this->rules_model->get_all_roles();

		if ($postdata = $this->input->post()) {

			foreach ($rules as $rule)
			{
				foreach ($roles as $role)
				{
					$this->rules_model->update_role_permission($rule['idRules'], $role['idRoles'], @$postdata['rr'][$rule['idRules']][$role['idRoles']]);
				}
			}
		}

		$result		= $this->rules_model->get_all_rolesrules();

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
			}
		}

		$header['page_title'] = 'USER ROLES RULES';

		$this->load->view('header', $header);
		$this->load->view('admin/admin_rules', $data);
		$this->load->view('footer');
	}

}

/* End of file rules.php */
/* Location: ./application/controllers/rules.php */