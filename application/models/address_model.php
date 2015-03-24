<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Address_model  extends CI_Model 
{
 
	function __construct()
	{
		// Call the Model constructor
		parent::__construct();
	}

	function get_user_address($user_id)
	{
		$this->db->select('a.*');
		$this->db->from('Users u');
		$this->db->join('Address a', 'a.idAddress = u.idAddress');
		$this->db->where('u.idUsers', $user_id);

		return $this->db->get()->row_array();
	}

	function get_cities_by_text($text)
	{
		$this->db->like('city', $text);
		return $this->db->get('Address')->result_array();
	}

	function update_address($insdata)
	{
		$insert_query = $this->db->insert_string('Address', $insdata);
        $insert_query = str_replace('INSERT INTO', 'INSERT IGNORE INTO', $insert_query);
        $this->db->query($insert_query); 
        $useraddress = $this->db->where($insdata)->get('Address')->row_array();
        return $useraddress['idAddress'];
	}

	function update_user_address($idaddress, $user_id)
	{
		return $this->db->where('idUsers', $user_id)->update('Users', array('idAddress' => $idaddress));
	}
}

/* End of file address_model.php */
/* Location: ./application/model/address_model.php */