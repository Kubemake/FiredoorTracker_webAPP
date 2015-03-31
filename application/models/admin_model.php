<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Admin_model  extends CI_Model 
{
 
	function __construct()
	{
		// Call the Model constructor
		parent::__construct();
	}

	function get_all_clients_data($show_director = FALSE)
	{
		$this->db->select('u.*, r.name as role_name');
		$this->db->from('Users u');
		$this->db->join('Roles r', 'r.idRoles = u.role');
		$this->db->where_in('u.role', array(1,4)); //clients view only
		$this->db->where('u.deleted', 0);
		return $this->db->get()->result_array();
	}
}

/* End of file admin_model.php */
/* Location: ./application/model/admin_model.php */