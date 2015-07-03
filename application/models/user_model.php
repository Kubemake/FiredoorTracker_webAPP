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
		$user = $this->db->get('Users')->row_array();

		if (!empty($user))
		{
			$logofile = $this->db->where('idUsers', $user['parent'])->select('logoFilePath')->get('Users')->row_array();
			$user['logoFilePath'] = (strlen($logofile['logoFilePath']) > 0) ? $logofile['logoFilePath'] : '/images/head-logo.png';
		}

		return $user;
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
		$this->db->where('b.deleted', 0);
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
        $this->db->where('idBuildings', $id)->update('Buildings', array('deleted' => $this->session->userdata('user_id')));

        // $this->db->where('Buildings_idBuildings', $id);
        // $this->db->where('Users_idUsers', $this->session->userdata('user_id'));
        // $this->db->delete('UserBuildings');
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
			'Users_idUsers'			=> $this->session->userdata('user_parent')
		);
		$this->db->insert('UserBuildings', $ubData);

		return $bid;
	}

	function get_all_buildings($user_parent = FALSE)
	{
		$parent = $user_parent ? $user_parent : $this->session->userdata('user_parent'); //use director id
		
		$this->db->select('b.*');
		$this->db->from('UserBuildings ub');
		$this->db->join('Buildings b', 'b.idBuildings = ub.Buildings_idBuildings');
		$this->db->where('ub.Users_idUsers', $parent);
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
		$curent_role = $this->db->where('idRoles', $this->session->userdata('user_role'))->get('Roles')->row_array();
		$roleOrder = $curent_role['rolesOrder'];
		
		$this->db->select('u.*');
		$this->db->from('Users u');
		$this->db->join('Roles r', 'r.idRoles=u.role', 'left');
		$this->db->where('u.deleted', 0);
		$this->db->where('r.rolesOrder >=', $roleid);
		$this->db->where('u.parent', $parent);
		$result = $this->db->get()->result_array();
		return $result;
	}

	function get_users_by_parent($parent_id)
	{
		$this->db->select('idUsers, firstName, lastName, deleted');
		$this->db->where('parent', $parent_id);
		$result = $this->db->get('Users')->result_array();
		
		$output = array();
		foreach ($result as $value)
			$output[$value['idUsers']] = $value;

		return $output;
	}

	function get_all_users_by_role($role_id, $user_parent = FALSE)
	{
		$parent = $user_parent ? $user_parent : $this->session->userdata('user_parent'); //use director id

		$this->db->where('role', $role_id);
		$this->db->where('parent', $parent);
		$this->db->where('deleted', 0);
		$result = $this->db->get('Users')->result_array();

		return $result;
	}

}