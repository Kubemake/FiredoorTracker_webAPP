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

	function get_lic_info_by_client_id($user_id)
	{
		$this->db->where('idUsers', $user_id);
		$result = $this->db->get('Licensing')->row_array();

		return $result;
	}
}

/* End of file admin_model.php */
/* Location: ./application/model/admin_model.php */