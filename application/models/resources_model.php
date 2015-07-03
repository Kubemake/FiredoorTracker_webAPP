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

	function delete_employeer_by_id($employeer_id, $delnumber)
	{
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
		$this->db->where('UserId', $this->session->userdata('user_parent'));
        $this->db->where('deleted', 0);
        $doors = $this->db->get('Doors')->result_array();

        $builds = $this->get_user_buildings();
        foreach ($builds as $building)
        	$buildings[$building['idBuildings']] = $building['name'];

        foreach ($doors as &$door)
        {
        	$door['Building'] 	= ($door['Building']==0) ? '' : @$buildings[$door['Building']];
        	$door['Floor'] 		= ($door['Floor']==0) ? '' : @$buildings[$door['Floor']];
        	$door['Wing'] 		= ($door['Wing']==0) ? '' : @$buildings[$door['Wing']];
        	$door['Area'] 		= ($door['Area']==0) ? '' : @$buildings[$door['Area']];
        	$door['Level'] 		= ($door['Level']==0) ? '' : @$buildings[$door['Level']];
        }

        return $doors;
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
		$this->db->join('Buildings b', 'b.idBuildings = d.Building');
		$this->db->where('d.idDoors', $aperture_id);
		$this->db->where('d.deleted', 0);
		return $this->db->get()->row_array();
	}

	function get_apertures_info_by_aperture_ids($aperture_ids)
	{
		if (!is_array($aperture_ids))
			$aperture_ids = array($aperture_ids);

		$this->db->select('idDoors, wall_Rating, rating, smoke_Rating, material');
		$this->db->from('Doors');
		$this->db->where_in('idDoors', $aperture_ids);
		$this->db->where('deleted', 0);
		return $this->db->get()->result_array();
	}

	function get_aperture_info_by_barcode($barcode, $user_parent = FALSE)
	{
		$parent =  $user_parent ? $user_parent : $this->session->userdata('user_parent'); //use director id

		$this->db->where('barcode', $barcode);
		$this->db->where('deleted', 0);
		$this->db->where('UserId', $parent);
		$result = $this->db->get('Doors')->row_array();

		return $result;
	}

	function update_aperture_data($aperture_id, $upddata)
	{
		return $this->db->where('idDoors', $aperture_id)->update('Doors', $upddata);
	}

	function delete_aperture_by_id($aperture_id, $user = FALSE)
	{
		$parent =  $user ? $user['parent'] : $this->session->userdata('user_parent'); //use director id
		$userid =  $user ? $user['idUsers'] : $this->session->userdata('user_id');

		$this->db->where('idDoors', $aperture_id);
		$this->db->where('UserId', $parent);

		return $this->db->update('Doors', array('deleted' => $userid));
	}
	
	function delete_review_by_id($review_id, $user = FALSE)
	{
		$parent =  $user ? $user['parent'] : $this->session->userdata('user_parent'); //use director id
		$userid =  $user ? $user['idUsers'] : $this->session->userdata('user_id');

		$this->db->where('idInspections', $review_id);
		$this->db->where('UserId', $parent);

		return $this->db->update('Inspections', array('deleted' => $userid));
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
		$result = $this->db->get()->result_array();

		return $result;
	}

	function get_user_buildings_root($user_parent = FALSE)
	{
		$parent = $user_parent ? $user_parent : $this->session->userdata('user_parent'); //use director id

		$this->db->select('b.*');
		$this->db->from('UserBuildings ub');
		$this->db->join('Buildings b', 'b.idBuildings = ub.Buildings_idBuildings', 'left');
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
		$this->db->where('deleted', 0);
		
		return $this->db->get('Doors')->result_array();
	}

	function get_user_apertures_without_review($location_id = FALSE)
	{
		$this->db->select('d.*, i.idInspections, i.deleted as inspdeluser');
		$this->db->from('Doors d');
		$this->db->join('Inspections i', 'i.idAperture = d.idDoors', 'left');
		$this->db->where('d.UserId', $this->session->userdata('user_parent'));

		if ($location_id)
			$this->db->where('d.Building', $location_id);

		$this->db->where('d.deleted', 0);

		$result = $this->db->get()->result_array();

		$output = array();
		foreach ($result as $door)
			if (empty($door['idInspections']) or $door['inspdeluser'] > 0)
				$output[] = $door;

		return $output;
	}

	function add_inspection($adddata)
	{
		$this->db->insert('Inspections', $adddata);
		return $this->db->insert_id();
	}

	function get_user_inspections()
	{
		$this->db->select('i.idInspections as id, i.idAperture as aperture_id, i.StartDate, i.Completion, i.InspectionStatus, i.Inspector, u.firstName, u.lastName, d.barcode');
		$this->db->from('Inspections i');

		$this->db->join('Doors d', 'd.idDoors = i.idAperture', 'left');
		$this->db->join('Users u', 'u.idUsers = i.Inspector', 'left');
		$this->db->where('i.deleted', 0);

		return $this->db->get()->result_array();
	}

	function get_user_inspections_by_parent($parent_id)
	{
		$this->db->select('i.idInspections as id, i.idAperture as aperture_id, i.CreateDate, i.StartDate, i.Completion, i.InspectionStatus, i.Creator, u2.firstName as CreatorfirstName, u2.lastName as CreatorlastName, i.Inspector, u.firstName, u.lastName, d.barcode, d.Building, d.Floor, d.Wing, d.Area, d.Level');
	   	$this->db->from('Inspections i');

	   	$this->db->join('Doors d', 'd.idDoors = i.idAperture', 'left');
	   	$this->db->join('Users u', 'u.idUsers = i.Inspector', 'left');
	   	$this->db->join('Users u2', 'u2.idUsers = i.Creator', 'left');

	   	$this->db->where('i.deleted', 0);
	   	$this->db->where('i.UserId', $parent_id);
	   	$this->db->order_by('i.CreateDate', 'desc');

	   	$result = $this->db->get()->result_array();

	   	return $result;
	}

	function get_user_inspections_by_user_id($user_id)
	{
		$this->db->select('i.idInspections as id, i.idAperture as aperture_id, i.StartDate, i.Completion, i.InspectionStatus, i.Inspector, u.firstName, u.lastName, d.barcode');
	   	$this->db->from('Inspections i');

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

		$this->db->select('i.idInspections as id, i.idAperture as aperture_id, i.StartDate, i.Completion, i.InspectionStatus, i.Inspector, u.firstName, u.lastName, d.barcode');//i.Buildings_idBuildings as location_id,, b.root as building_id, b.name as location_name
	   	$this->db->from('Inspections i');

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
		$this->db->select('i.*, b.name, d.barcode, d.Building, d.Floor, d.Wing, d.Area, d.Level, u.firstName, u.lastName, d.barcode');
		$this->db->join('Doors d', 'd.idDoors = i.idAperture', 'left');
		$this->db->join('Users u', 'u.idUsers = i.Inspector', 'left');
		$this->db->join('Buildings b', 'b.idBuildings = d.Building', 'left');
		$this->db->where('i.idInspections', $inspection_id);
		$result = $this->db->get('Inspections i')->row_array();
		return $result;
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

	function get_client_inspection_by_aperture_id($aperture_id, $user_parent = FALSE)
	{

		$parent = $user_parent ? $user_parent : $this->session->userdata('user_parent'); //use director id

		$this->db->where('idAperture', $aperture_id);
		$this->db->where('UserId', $parent);
		$this->db->order_by('idInspections','desc');
		return $this->db->get('Inspections')->row_array();
	}

	function get_aperture_issues_with_status_and_selected($aperture_id, $inspection_id)
	{
		$input_data = $this->db->where('idDoors', $aperture_id)->get('Doors')->row_array();

		$this->db->select('ff.*, dff.value as selected'); 
		$this->db->from('FormFields ff');
		$this->db->join('DoorsFormFields dff', 'dff.FormFields_idFormFields = ff.idFormFields AND dff.Inspections_idInspections = ' . $inspection_id, 'left');
		$this->db->join('ConditionalChoices cc', 'cc.idField = ff.idFormFields AND cc.wallRates = ' . $input_data['wall_Rating'] . ' AND cc.ratesTypes = ' . $input_data['smoke_Rating'] . ' AND cc.doorRating = ' . $input_data['rating'] . ' AND cc.doorMatherial = ' . $input_data['material'], 'left');
		$this->db->where('ff.deleted', 0);
		$this->db->where('cc.value >', 1);
		$this->db->where('dff.value !=', 'NO');
		$result = $this->db->get()->result_array();
		return $result;
	}

	function get_inspection_data($inspection_id)
	{
		return $this->db->where('Inspections_idInspections', $inspection_id)->get('DoorsFormFields')->result_array();
	}

	function delete_inspectiod_data($inspection_id)
	{
		return $this->db->where('Inspections_idInspections', $inspection_id)->delete('DoorsFormFields');
	}

	function get_inspections_statuses($user_parent, $inspections = FALSE)
	{
		$this->db->select('i.idInspections as inspection_id, cc.value as status');
		$this->db->from('Inspections i');
		$this->db->join('DoorsFormFields dff', 'dff.Inspections_idInspections = i.idInspections', 'left');
		$this->db->join('Doors d', 'd.idDoors = i.idAperture', 'left');
		$this->db->join('ConditionalChoices cc', 'cc.idField = dff.FormFields_idFormFields AND cc.wallRates = d.wall_Rating AND cc.ratesTypes = d.smoke_Rating AND cc.doorRating = d.rating AND cc.doorMatherial = d.material', 'left');
		
		if ($inspections)
		{
			if (!is_array($inspections))
				$inspections = array(1 => $inspections);

			$this->db->where_in('i.idInspections', $inspections);
		} 
		else
			$this->db->where('i.UserId', $user_parent);
		
		$this->db->where('cc.value IS NOT ', 'NULL', FALSE);
		
		$result = $this->db->get()->result_array();

		return $result;
	}

	function get_user_buildings_by_building_parent($parent_id, $user_parent=FALSE)
	{
		$parent = $user_parent ? $user_parent : $this->session->userdata('user_parent'); //use director id

		$this->db->select('b.*');
		$this->db->from('UserBuildings ub');
		$this->db->join('Buildings b', 'b.idBuildings = ub.Buildings_idBuildings', 'left');
		$this->db->where('ub.Users_idUsers', $parent);
		$this->db->where('b.parent', $parent_id);
		$this->db->where('b.deleted', 0);
		$this->db->order_by('b.buildingOrder', 'asc');
		
		$output = array();
		foreach ($this->db->get()->result_array() as $bld)
			$output[$bld['idBuildings']] = $bld;

		return $output;
	}

	function get_inspections_by_complete_date($datetype, $user_parent=FALSE)
	{
		$parent = $user_parent ? $user_parent : $this->session->userdata('user_parent'); //use director id
		$this->db->select('idInspections');
		$this->db->where('deleted', 0);
		$this->db->where('UserId', $parent);
		switch ($datetype)
		{
			case 'ahjreport1':
				$this->db->like('Completion', date('Y'), 'after');
			break;
			case 'ahjreport2':
				$this->db->like('Completion', date('Y'), 'after');
				$this->db->or_like('Completion', (date('Y')-1), 'after');
			break;
			case 'ahjreport2':
				$this->db->like('Completion', date('Y'), 'after');
				$this->db->or_like('Completion', (date('Y')-1), 'after');
				$this->db->or_like('Completion', (date('Y')-2), 'after');
				$this->db->or_like('Completion', (date('Y')-3), 'after');
				$this->db->or_like('Completion', (date('Y')-4), 'after');
			break;
			case 'activityreport':
				$this->db->where('Completion IS NOT ', 'NULL', FALSE);
			break;
		}
		$result = $this->db->get('Inspections')->result_array();
		
		$output = array();
		foreach ($result as $value)
			$output[] = $value['idInspections'];
// echo '<pre>';
// print_r($this->db->last_query());die();
		return $output;
	}

	function get_cached_report_data($type, $inspcetions)
	{
		if (!is_array($inspcetions))
			$inspcetions = array($inspcetions);

		switch ($type)
		{
			case 'ahjreport1':
				$this->db->like('InspectionTime', date('Y'), 'after');
			break;
			case 'ahjreport2':
				$this->db->like('InspectionTime', date('Y'), 'before');
				$this->db->or_like('InspectionTime', (date('Y')-1), 'before');
			break;
			/*case 'ahjreport3':
				$this->db->like('InspectionTime', date('Y'), 'none');
				$this->db->or_like('InspectionTime', (date('Y')-1), 'none');
				$this->db->or_like('InspectionTime', (date('Y')-2), 'none');
				$this->db->or_like('InspectionTime', (date('Y')-3), 'none');
				$this->db->or_like('InspectionTime', (date('Y')-4), 'none');
			break;*/
			/*case 'ahjreport3':
				$this->db->like('InspectionTime', date('Y'), 'none');
				$this->db->or_like('InspectionTime', (date('Y')-1), 'none');
				$this->db->or_like('InspectionTime', (date('Y')-2), 'none');
				$this->db->or_like('InspectionTime', (date('Y')-3), 'none');
				$this->db->or_like('InspectionTime', (date('Y')-4), 'none');
			break;*/
		}
		
		$this->db->where('ReportType', $type);
		$this->db->where_in('Inspections_idInspections', $inspcetions);

		$result = $this->db->get('ReportCache')->result_array();

		$output = array();
		foreach ($result as $value)
			$output[$value['Inspections_idInspections']][$value['InspectionTime']] = $value;

		return $output;
	}

	function add_cache_data($type, $inspections, $date, $value)
	{
		$adddata = array(
			'Inspections_idInspections' => $inspections,
			'ReportType' 				=> $type,
			'InspectionTime' 			=> $date,
			'value'						=> $value
		);
		
		switch ($type) {
				case 'ahjreport1':
					if ($date != date('Y-m'))
						$this->db->insert('ReportCache', $adddata);
				break;
				case 'ahjreport2':
					if ($date != 'Q' . (date('m')/3) . ' - ' . date('Y'))
						$this->db->insert('ReportCache', $adddata);
				break;
				case 'ahjreport3':
					if ($date != date('Y'))
						$this->db->insert('ReportCache', $adddata);
				break;
				case 'activityreport':
					if ($date != date('Y-m-d'))
						$this->db->insert('ReportCache', $adddata);
				break;
				
				default:
					$this->db->insert('ReportCache', $adddata);
				break;
			}	
		

		return $adddata;
	}

	function get_aperture_info_by_inspection_id($inspection_id)
	{
		$this->db->select('d.*');
		$this->db->from('Doors d');
		$this->db->join('Inspections i', 'i.idAperture = d.idDoors', 'left');
		$this->db->where('i.idInspections', $inspection_id);

		return $this->db->get()->row_array();
	}

	function get_aperture_issues_with_status($aperture_info)
	{
		$this->db->select('idField, value');
		$this->db->where('wallRates', $aperture_info['wall_Rating']);
		$this->db->where('ratesTypes', $aperture_info['smoke_Rating']);
		$this->db->where('doorRating', $aperture_info['rating']);
		$this->db->where('doorMatherial', $aperture_info['material']);
		$result = $this->db->get('ConditionalChoices')->result_array();
		$values = $this->config->item('door_state');
		$output = array();
		foreach ($result as $value)
			$output[$value['idField']] = $values[$value['value']];
		return $output;
	}

	function get_available_values_by_parent($table_name, $field_name, $user_parent = FALSE)
	{
		$parent = $user_parent ? $user_parent : $this->session->userdata('user_parent'); //use director id

		$this->db->select($field_name);
		$this->db->from($table_name);
		$this->db->where('UserId', $parent);
		$this->db->where($field_name . ' IS NOT ', 'NULL', FALSE);
		$this->db->group_by($field_name);

		$result = $this->db->get()->result_array();
		
		$output = array();
		foreach ($result as $key => $value)
			$output[] = $value[$field_name];

		return $output;		
	}

	function get_door_form_fields($inspection_id)
	{
		$this->db->where('Inspections_idInspections', $inspection_id);
		$result = $this->db->get('DoorsFormFields')->result_array();
		// echo '<pre>';
		// print_r($this->db->last_query());
		$output = array();
		foreach ($result as $value)
			$output[$value['FormFields_idFormFields']] = $value;

		return $output;
	}

	function get_all_inspections_by_parent($user_parent = FALSE)
	{
		$parent = $user_parent ? $user_parent : $this->session->userdata('user_parent'); //use director id
		$this->db->select('idInspections');
		$this->db->where('UserId', $parent);
		$this->db->where('deleted', 0);
		$this->db->group_by('idAperture');
		$result = $this->db->get('Inspections')->result_array();

		return $result;
	}
}

/* End of file resources_model.php */
/* Location: ./application/model/resources_model.php */