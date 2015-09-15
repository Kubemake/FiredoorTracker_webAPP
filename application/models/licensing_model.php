<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Licensing_model  extends CI_Model 
{
 
	function __construct()
	{
		// Call the Model constructor
		parent::__construct();
	}

	function add_licensing_data($licdata)
	{
		$this->db->insert('Licensing', $licdata);
		return $this->db->insert_id();
	}

	function update_licensing_data($user_id, $licdata)
	{
		$this->db->where('idUsers', $user_id);
		return $this->db->update('Licensing', $licdata);
	}

	 /*old version without multilicensing*/
/*	function get_lic_info_by_client_id($user_id)
	{
		$this->db->where('idUsers', $user_id);
		$result = $this->db->get('Licensing')->row_array();

		return $result;
	}*/

	function get_lic_info_by_client_id($user_id)
	{
		$this->db->select('id, idUsers, SUM(dir) as dir, SUM(sv) as sv, SUM(mech) as mech, SUM(inspections) as inspections, MIN(expired) as expired');
		$this->db->where('idUsers', $user_id);
		$this->db->where('expired > ', 'CURDATE()', FALSE);
		$result = $this->db->get('Licensing')->row_array();

		return $result;
	}

	function get_all_client_licensing($user_id)
	{
		$this->db->where('idUsers', $user_id);
		$result = $this->db->get('Licensing')->result_array();

		return $result;
	}

	function get_active_users_by_client_id($user_id)
	{
		$this->db->where('parent', $user_id);
		$this->db->where('deleted', 0);
		$result = $this->db->get('Users')->result_array();

		$output = array();
		foreach ($result as $user)
			$output[$user['role']] = (isset($output[$user['role']])) ? ++$output[$user['role']] : 1;

		return $output;
	}

	function deactivate_by_limitation($role, $count, $parent)
	{
		$this->db->select('idUsers');
		$this->db->where('deleted', 0);
		$this->db->where('parent', $parent);
		$this->db->where('idUsers != ', $parent, FALSE);
		$this->db->where('role', $role);
		$result = $this->db->get('Users')->result_array();

		for ($i=0; $i < $count; $i++)
		{
			$userid = array_pop($result);
			$userid = $userid['idUsers'];
			$this->db->where('idUsers', $userid);
			$this->db->update('Users', array('deleted' => $parent));
		}
	}
}

/* End of file admin_model.php */
/* Location: ./application/model/admin_model.php */