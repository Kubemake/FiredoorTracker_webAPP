<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class History_model  extends CI_Model 
{
 
	function __construct()
	{
		// Call the Model constructor
		parent::__construct();
	}

	function add_history_info($user_id, $entity, $line_id, $time, $new_val, $current_data)
	{
		$insdata = array(
			'entity' 	=> $entity,
			'user_id' 	=> $user_id,
			'line_id' 	=> $line_id,
			'timestamp' => $time,
			'new_val' 	=> $new_val,
			'prev_val' 	=> $current_data
		);
		return $this->db->insert('History', $insdata);
	}

	function get_expert_by_expert_id($line_id)
	{
		return $this->db->where('idExperts', $line_id)->get('Experts')->row_array();
	}

	function get_file_by_file_id($line_id)
	{
		return $this->db->where('idFiles', $line_id)->get('Files')->row_array();
	}

	function get_info_by_info_id($line_id)
	{
		return $this->db->where('idInfo', $line_id)->get('Info')->row_array();
	}

	function get_user_by_user_id($line_id)
	{
		return $this->db->where('idUsers', $line_id)->get('Users')->row_array();
	}

	function get_location_by_location_id($line_id)
	{
		return $this->db->where('idBuildings', $line_id)->get('Buildings')->row_array();
	}

	function get_door_by_door_id($line_id)
	{
		return $this->db->where('idDoors', $line_id)->get('Doors')->row_array();
	}

	function get_rr_by_user_parent($line_id)
	{
		return $this->db->where('UserId', $line_id)->get('RolesRules')->result_array();
	}

	function get_review_by_review_id($line_id)
	{
		return $this->db->where('idInspections', $line_id)->get('Inspections')->result_array();
	}

	function get_iff_by_iff_id($line_id)
	{
		return $this->db->where('id', $line_id)->get('InspectionFieldFiles')->result_array();
	}

	function get_aperture_id_by_inspection_id($inspection_id)
	{
		$app_id = $this->db->select('idAperture')->where('idInspections', $inspection_id)->get('Inspections')->row_array();

		return $app_id['idAperture'];
	}
}

/* End of file history_model.php */
/* Location: ./application/model/history_model.php */