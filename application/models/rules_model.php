<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Rules_model  extends CI_Model 
{
 
	function __construct()
	{
		// Call the Model constructor
		parent::__construct();
	}

	function get_all_rules()
	{
		$this->db->order_by('group, idRules');
		return $this->db->get('Rules')->result_array();
	}

	function get_all_roles()
	{
		$this->db->order_by('idRoles');
		return $this->db->get('Roles')->result_array();
	}

	function get_all_rolesrules($parent_id)
	{
		$this->db->where('UserId', $parent_id);
		$this->db->order_by('idRules, idRoles');
		return $this->db->get('RolesRules')->result_array();
	}

	function delete_all_user_rules($parent_id)
	{
		return $this->db->delete('RolesRules', array('UserId' => $parent_id));
	}

	function update_role_permission($rule_id, $role_id, $parent_id, $value)
	{
		if ($value=='on') 
			$value = 1;

		if (empty($value))
			$value = 0;

		$this->db->delete('RolesRules', array('idRules'	=> $rule_id, 'idRoles'	=> $role_id, 'UserId' => $parent_id));

		$insdata = array(
			'idRules'	=> $rule_id,
			'idRoles'	=> $role_id,
			'UserId'	=> $parent_id,
			'value' 	=> $value
		);
		return $this->db->insert('RolesRules', $insdata);
	}
}

/* End of file media_model.php */
/* Location: ./application/model/media_model.php */