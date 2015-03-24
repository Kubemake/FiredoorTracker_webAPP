<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class User_model  extends CI_Model 
{
 
	function __construct()
	{
		// Call the Model constructor
		parent::__construct();
	}

	function verifyUserLogin($login=FALSE, $password=FALSE)
	{
		if (!$login or !$password) return FALSE;
			
		$user_data = array(
			'email' 	=> $login,
			'password' 	=> $password,
			'deleted'	=> 0
		);
		$this->db->where($user_data);
		return $this->db->get('Users')->row_array();
	}

	function get_user_info_by_email($email)
	{
		if (!$email) return FALSE;
		
		$this->db->where('email', $email);
		$result = $this->db->get('Users')->row_array();
		if (empty($result))
			return FALSE;
		return $result;
	}

	function get_user_info_by_user_id($user_id)
	{
		if (!$user_id) return FALSE;
		
		$this->db->where('idUsers', $user_id);
		
		return $this->db->get('Users')->row_array();
	}

	function update_user_data($user_id, $updateData)
	{
		$this->db->where('idUsers', $user_id);
		$this->db->update('Users', $updateData);
	}

	function delete_user_tokens($user_id)
	{
		$this->db->where('user_id', $user_id);
		return $this->db->delete('UserTokens');
	}

	function get_user_buildings($user_id)
	{
		$this->db->select('ub.*');
		$this->db->from('UserBuildings ub');
		$this->db->join('Buildings b', 'b.idBuildings = ub.Buildings_idBuildings');
		$this->db->where('ub.Users_idUsers', $user_id);
		$this->db->order_by('b.buildingOrder', 'asc');
		return $this->db->get()->result_array();
	}

	function get_building_data($building_id)
	{
		$this->db->where('idBuildings', $building_id);
		return $this->db->get('Buildings')->row_array();
	}

	function get_all_buildings_by_parent($parent_id)
	{
		$this->db->select('b.*');
		$this->db->from('UserBuildings ub');
		$this->db->join('Buildings b', 'b.idBuildings = ub.Buildings_idBuildings');
		$this->db->where(array('b.parent' => $parent_id, 'b.deleted' => 0));
		$this->db->order_by('b.buildingOrder', 'asc');
		return $this->db->get()->result_array();
	}

	function get_building_by_id($id)
	{
		$this->db->where('idBuildings', $id);
        return $this->db->get('Buildings')->row_array();
	}

	function update_building_data($data)
    {
        $id = $data['idBuildings'];
        unset($data['idBuildings']);

        return $this->db->where('idBuildings', $id)->update('Buildings', $data);
    }

    function delete_building_by_id($id)
    {
        $this->db->where('idBuildings', $id)->delete('Buildings');

        $this->db->where('Buildings_idBuildings', $id);
        $this->db->where('Users_idUsers', $this->session->userdata('user_id'));
        $this->db->delete('UserBuildings');
        $elems = $this->db->where('parent', $id)->get('Buildings')->result_array();
        foreach ($elems as $elem) {
            $this->delete_building_by_id($elem['idBuildings']);
        }
    }

    function add_building($adddata)
	{
		$this->db->insert('Buildings', $adddata);
		$bid = $this->db->insert_id();
		
		
		//add root param for building element
		$data['idBuildings'] = $bid;
		if ($adddata['parent'] == 0)
			$data['root']		 = $bid;
		else
		{
			$root = $this->db->select('root')->where('idBuildings', $adddata['parent'])->get('Buildings')->row_array();
			$data['root'] = @$root['root'];
		}
		$this->update_building_data($data);

		//add building to user link
		$ubData = array(
			'Buildings_idBuildings' => $bid,
			'Users_idUsers'			=> $this->session->userdata('user_id')
		);
		$this->db->insert('UserBuildings', $ubData);

		return $bid;
	}

	function get_all_buildings()
	{
		$this->db->select('b.*');
		$this->db->from('UserBuildings ub');
		$this->db->join('Buildings b', 'b.idBuildings = ub.Buildings_idBuildings');
		$this->db->where('ub.Users_idUsers', $this->session->userdata('user_parent'));
		$this->db->where('b.deleted', 0);
		$result = $this->db->get()->result_array();

		$output = array();
		foreach ($result as $value) {
			$output[$value['idBuildings']] = $value;
		}

		return $output;
	}

	function get_users_by_role_and_user_parent($roleid, $parent)
	{
		$this->db->where('role', $roleid);
		$this->db->where('parent', $parent);
		return $this->db->get('Users')->result_array();
	}

}