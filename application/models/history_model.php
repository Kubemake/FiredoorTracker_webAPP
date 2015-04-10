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
}

/* End of file history_model.php */
/* Location: ./application/model/history_model.php */