<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Resources_model  extends CI_Model 
{
 
	function __construct()
	{
		// Call the Model constructor
		parent::__construct();
	}

	function add_employer($adddata)
	{
		$this->db->insert('Users', $adddata);
		return $this->db->insert_id();
	}
	
	function get_all_employeers_roles()
	{
		$curent_role = $this->db->where('idRoles', $this->session->userdata('user_role'))->get('Roles')->row_array();
		$roleOrder = $curent_role['rolesOrder'];

		if ($curent_role['idRoles'] == 4) //for admin
			$this->db->where_in('idRoles', array(1,4));
		else
			$this->db->where('rolesOrder >=', $roleOrder);

		$result = $this->db->get('Roles')->result_array();
		$output = array();
		foreach ($result as $role) 
			$output[$role['idRoles']] = $role['name'];

		return $output;
	}

	function get_employeer_info_by_employeer_id($employeer_id)
	{
		$this->db->where('idUsers', $employeer_id);
		return $this->db->get('Users')->row_array();
	}

	function update_employer_data($employer_id, $upddata)
	{
		return $this->db->where('idUsers', $employer_id)->update('Users', $upddata);
	}

	function delete_employeer_by_id($employeer_id)
	{
		$user = $this->db->where('idUsers', $employeer_id)->get('Users')->row_array();

		$delnumber = ($user['deleted'] > 0) ? '0' : $this->session->userdata('user_id');

		$this->db->where('idUsers', $employeer_id);
		return $this->db->update('Users', array('deleted' => $delnumber));
	}

	function get_user_by_email($email)
	{
		$this->db->where('email', $email);
		return $this->db->get('Users')->row_array();
	}

	function get_all_user_data(/*$show_director = FALSE*/)
	{
		$roleOrder = $this->db->where('idRoles', $this->session->userdata('user_role'))->get('Roles')->row_array();
		$roleOrder = $roleOrder['rolesOrder'];

		$this->db->select('u.*, r.name as role_name');
		$this->db->from('Users u');
		$this->db->join('Roles r', 'r.idRoles = u.role', 'left');
		$this->db->where('u.parent', $this->session->userdata('user_parent'));
		// $this->db->where('u.deleted', 0);
		$this->db->where('r.rolesOrder >=', $roleOrder); //show only less weight order

		return $this->db->get()->result_array();
	}

	function get_all_user_apertures()
	{
		$this->db->select('d.*, b.name as location_name');
		$this->db->from('Doors d');
		$this->db->join('Buildings b', 'b.idBuildings = d.Buildings_idBuildings');
		$this->db->where('d.UserId', $this->session->userdata('user_parent'));
        $this->db->where('d.deleted', 0);
        return $this->db->get()->result_array();
	}

	function add_aperture($adddata)
	{
		$this->db->insert('Doors', $adddata);
		return $this->db->insert_id();
	}
	
	function get_aperture_info_by_aperture_id($aperture_id)
	{
		$this->db->select('d.*, b.name as location_name');
		$this->db->from('Doors d');
		$this->db->join('Buildings b', 'b.idBuildings = d.Buildings_idBuildings');
		$this->db->where('d.idDoors', $aperture_id);
		$this->db->where('d.deleted', 0);
		return $this->db->get()->row_array();
	}

	function get_aperture_info_by_barcode($barcode)
	{
		
		$this->db->select('d.*, b.name as location_name');
		$this->db->from('Doors d');
		$this->db->join('Buildings b', 'b.idBuildings = d.Buildings_idBuildings');
		$this->db->where('barcode', $barcode);
		$this->db->where('d.deleted', 0);
		return $this->db->get()->row_array();
	}

	function update_aperture_data($aperture_id, $upddata)
	{
		return $this->db->where('idDoors', $aperture_id)->update('Doors', $upddata);
	}

	function delete_aperture_by_id($aperture_id)
	{
		$this->db->where('idDoors', $aperture_id);
		return $this->db->update('Doors', array('deleted' => $this->session->userdata('user_id')));
	}
	
	function delete_review_by_id($review_id)
	{
		$this->db->where('idInspections', $review_id);
		return $this->db->update('Inspections', array('deleted' => $this->session->userdata('user_id')));
	}

	function add_issue($adddata)
	{
		$this->db->insert('FormFields', $adddata);
		return $this->db->insert_id();
	}

	function get_all_issues_by_parent($parent_id)
	{
		$this->db->where(array('parent' => $parent_id, 'deleted' => 0));
		$this->db->order_by('questionOrder', 'asc');
		return $this->db->get('FormFields')->result_array();
	}

	function get_all_issues()
	{
		$result = $this->db->where('deleted', 0)->get('FormFields')->result_array();
		$output = array();
		foreach ($result as $value) {
			$output[$value['idFormFields']] = $value;
		}

		return $output;
	}

	function get_issue_by_id($id)
	{
		$this->db->where('idFormFields', $id);
		return $this->db->get('FormFields')->row_array();
	}

	function get_issue_types()
	{
		$type = $this->db->query("SHOW COLUMNS FROM FormFields LIKE 'type'")->row( 0 )->Type;
		preg_match("/^enum\(\'(.*)\'\)$/", $type, $matches);
		$enum = explode("','", $matches[1]);
		return $enum;
	}

   	function update_issue_data($data)
	{
		$id = $data['idFormFields'];
		unset($data['idFormFields']);

		$elem = $this->db->like('name', $data['name'], 'none')->get('FormFields')->row_array(); //Check if not dublicate name in record

		if (!empty($elem) && $elem['idFormFields'] != $id)
			return 'duplicate';

		return $this->db->where('idFormFields', $id)->update('FormFields', $data);
	}

	function delete_issue_by_id($id)
	{
		$this->db->where('idFormFields', $id)->update('FormFields', array('deleted' => $this->session->userdata('user_id')));
		$elems = $this->db->where('parent', $id)->get('FormFields')->result_array();
		foreach ($elems as $elem) {
			$this->delete_issue_by_id($elem['idFormFields']);
		}
	}

	function get_all_choices($wall_rate_id)
	{
		$this->db->where('wallRates', $wall_rate_id);
		return $this->db->get('ConditionalChoices')->result_array();

		/*$this->db->select('cc.*');
		$this->db->from('FormFields ff');
		$this->db->join('ConditionalChoices cc', 'cc.idField = ff.idFormFields', 'left');
		$this->db->where('ff.deleted', 0);
		$this->db->where('cc.wallRates', $wall_rate_id);
		return $this->db->get()->result_array();*/
	}

	function update_choice($field_id, $wall_rate_id, $ratesTypesId, $doorMatherialid, $doorRatingId, $value)
	{
		$whr = array(
			'idField' 		=> $field_id,
			'wallRates' 	=> $wall_rate_id,
			'ratesTypes' 	=> $ratesTypesId,
			'doorRating' 	=> $doorRatingId,
			'doorMatherial' => $doorMatherialid

		);
		$isseted = $this->db->where($whr)->get('ConditionalChoices')->row_array();
		if (!empty($isseted))
		{
			$this->db->where($whr);
			return $this->db->update('ConditionalChoices', array('value' => $value));
		}
		else
			$whr['value'] = $value;
			return $this->db->insert('ConditionalChoices', $whr);
	}

	function delete_choice($field_id, $wall_rate_id, $ratesTypesId, $doorMatherialid, $doorRatingId)
	{
		$this->db->where(array(
			'idField' 		=> $field_id,
			'wallRates' 	=> $wall_rate_id,
			'ratesTypes' 	=> $ratesTypesId,
			'doorRating' 	=> $doorRatingId,
			'doorMatherial' => $doorMatherialid

		));
		return $this->db->delete('ConditionalChoices');
	}

	function get_user_buildings($user_parent = FALSE)
	{
		$parent = $user_parent ? $user_parent : $this->session->userdata('user_parent'); //use director id

		$this->db->select('b.*');
		$this->db->from('UserBuildings ub');
		$this->db->join('Buildings b', 'b.idBuildings = ub.Buildings_idBuildings');
		$this->db->where('ub.Users_idUsers', $parent);
		$this->db->where('b.deleted', 0);
		$this->db->order_by('b.buildingOrder', 'asc');
		return $this->db->get()->result_array();
	}

	function get_user_buildings_root($user_parent = FALSE)
	{
		$parent = $user_parent ? $user_parent : $this->session->userdata('user_parent'); //use director id

		$this->db->select('b.*');
		$this->db->from('UserBuildings ub');
		$this->db->join('Buildings b', 'b.idBuildings = ub.Buildings_idBuildings');
		$this->db->where('ub.Users_idUsers', $parent);
		$this->db->where('b.parent', 0);
		$this->db->where('b.deleted', 0);
		$this->db->order_by('b.buildingOrder', 'asc');
		
		$output = array();
		foreach ($this->db->get()->result_array() as $bld)
			$output[$bld['idBuildings']] = $bld;

		return $output;
	}

	function get_user_apertures($location_id = FALSE, $user_parent = FALSE)
	{
		$parent =  $user_parent ? $user_parent : $this->session->userdata('user_parent'); //use director id
		
		$this->db->where('UserId', $parent);
		if ($location_id)
			$this->db->where('Buildings_idBuildings', $location_id);
		
		return $this->db->get('Doors')->result_array();
	}

	function add_inspection($adddata)
	{
		return $this->db->insert('Inspections', $adddata);
	}

	function get_user_inspections()
	{
		$this->db->select('i.revision, i.idInspections as id, i.Buildings_idBuildings as location_id, b.name as location_name, i.idAperture as aperture_id, d.name as aperture_name,i.StartDate, i.Completion, i.InspectionStatus, i.Inspector, u.firstName, u.lastName, b.root as building_id, d.barcode');
		$this->db->from('Inspections i');
		$this->db->join('Buildings b', 'b.idBuildings = i.Buildings_idBuildings', 'left');
		$this->db->join('Doors d', 'd.idDoors = i.idAperture', 'left');
		$this->db->join('Users u', 'u.idUsers = i.Inspector', 'left');
		$this->db->where('i.deleted', 0);

		return $this->db->get()->result_array();
	}

	function get_user_inspections_by_parent($parent_id)
	{
		$this->db->select('i.revision, i.idInspections as id, i.Buildings_idBuildings as location_id, b.name as location_name, i.idAperture as aperture_id, d.name as aperture_name,i.StartDate, i.Completion, i.InspectionStatus, i.Inspector, u.firstName, u.lastName, b.root as building_id, d.barcode');
	   	$this->db->from('Inspections i');
	   	$this->db->join('Buildings b', 'b.idBuildings = i.Buildings_idBuildings', 'left');
	   	$this->db->join('Doors d', 'd.idDoors = i.idAperture', 'left');
	   	$this->db->join('Users u', 'u.idUsers = i.Inspector', 'left');
	   	$this->db->where('i.deleted', 0);
	   	$this->db->where('i.UserId', $parent_id);

	   	return $this->db->get()->result_array();
	}

	function get_user_inspections_by_user_id($user_id)
	{
		$this->db->select('i.revision, i.idInspections as id, i.Buildings_idBuildings as location_id, b.name as location_name, i.idAperture as aperture_id, d.name as aperture_name,i.StartDate, i.Completion, i.InspectionStatus, i.Inspector, u.firstName, u.lastName, b.root as building_id, d.barcode');
	   	$this->db->from('Inspections i');
	   	$this->db->join('Buildings b', 'b.idBuildings = i.Buildings_idBuildings', 'left');
	   	$this->db->join('Doors d', 'd.idDoors = i.idAperture', 'left');
	   	$this->db->join('Users u', 'u.idUsers = i.Inspector', 'left');
	   	$this->db->where('i.deleted', 0);
	   	$this->db->where('i.Inspector', $user_id);

	   	return $this->db->get()->result_array();
	}

	function get_user_inspections_by_user_role($user_role, $parent_id)
	{
		$curent_role = $this->db->where('idRoles', $user_role)->get('Roles')->row_array();
		$roleOrder = $curent_role['rolesOrder'];

		$this->db->select('i.revision, i.idInspections as id, i.Buildings_idBuildings as location_id, b.name as location_name, i.idAperture as aperture_id, d.name as aperture_name,i.StartDate, i.Completion, i.InspectionStatus, i.Inspector, u.firstName, u.lastName, b.root as building_id, d.barcode');
	   	$this->db->from('Inspections i');
	   	$this->db->join('Buildings b', 'b.idBuildings = i.Buildings_idBuildings', 'left');
	   	$this->db->join('Doors d', 'd.idDoors = i.idAperture', 'left');
	   	$this->db->join('Users u', 'u.idUsers = i.Inspector', 'left');
	   	$this->db->join('Roles r', 'r.idRoles = u.role', 'left');
	   	$this->db->where('i.deleted', 0);
	   	$this->db->where('r.rolesOrder >=', $roleOrder, FALSE);
		$this->db->where('i.UserId', $parent_id);

	   	return $this->db->get()->result_array();
	}

	function get_inspection_info_by_inspection_id($inspection_id)
	{
		$this->db->select('i.*, b.name');
		$this->db->where('i.idInspections', $inspection_id);
		$this->db->join('Buildings b', 'b.idBuildings = i.Buildings_idBuildings');
		return $this->db->get('Inspections i')->row_array();
	}

	function get_inspection_by_aperture_id($aperture_id)
	{
		$this->db->where('idAperture', $aperture_id);
		$this->db->where('deleted', 0);
		$this->db->where('UserId', $this->session->userdata('user_parent'));
		return $this->db->get('Inspections')->row_array();	
	}

	function get_building_name_by_building_id($building_id)
	{
		$this->db->select('name as building_name');
		$this->db->where('idBuildings', $building_id);
		return $this->db->get('Buildings')->row_array();
	}

	function get_all_inspection_statuses()
	{
		$type = $this->db->query("SHOW COLUMNS FROM Inspections LIKE 'InspectionStatus'")->row( 0 )->Type;
		preg_match("/^enum\(\'(.*)\'\)$/", $type, $matches);
		$output = explode("','", $matches[1]);
		return $output;
	}

	function update_inspection($inspection_id, $upddata)
	{
		return $this->db->where('idInspections', $inspection_id)->update('Inspections', $upddata);
	}

	function update_inspection_state($inspection_id, $status)
	{
		return $this->db->where('idInspections', $inspection_id)->update('Inspections', array('InspectionStatus' => $status));
	}

	function get_client_inspection_by_aperture_id($aperture_id)
	{
		$this->db->where('idAperture', $aperture_id);
		$this->db->order_by('revision','desc');
		return $this->db->get('Inspections')->result_array();
	}
}

/* End of file resources_model.php */
/* Location: ./application/model/resources_model.php */