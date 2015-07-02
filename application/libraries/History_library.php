<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class History_library {

	protected $CI, $line_id = FALSE, $new_val = '', $user, $time, $current_data = '', $entity, $type;

	public function __construct($params = FALSE)
	{
		$this->CI =& get_instance();

		$this->user 	= $this->CI->session->userdata('user_id');
		$this->time 	= time();

		$this->CI->load->model('history_model');
	}
	
	// 'address','buildings','cc','doors','dff','experts','files','ff','info','iff','inspections','rr','ub','users'
	public function saveHistory()
	{
		$this->CI->history_model->add_history_info(
			$this->user,
			$this->entity,
			$this->line_id,
			$this->time,
			$this->new_val,
			$this->current_data
		);
	}

	public function saveAddress($params)
	{
		$this->entity 	= 'address';
		$this->line_id 	= $params['line_id'];
		$this->new_val 	= $params['new_val'];
		$this->type 	= $params['type'];
		
		$this->saveHistory();
	}

	public function saveBuildings($params)
	{
		$this->entity 	= 'buildings';
		$this->line_id 	= $params['line_id'];
		$this->new_val 	= $params['new_val'];
		$this->type 	= $params['type'];
		
		if ($this->type != 'add')
			$this->current_data = json_encode($this->CI->history_model->get_location_by_location_id($this->line_id));

		$this->saveHistory();
	}

	public function saveCC($params)
	{

	}

	public function saveDoors($params)
	{
		$this->entity 	= 'doors';
		$this->line_id 	= @$params['line_id'];
		$this->new_val 	= $params['new_val'];
		$this->type 	= $params['type'];
		
		if (isset($params['user_id']))
			$this->user = $params['user_id'];
	
		if (isset($params['iid']))
			$this->line_id 	= $this->CI->history_model->get_aperture_id_by_inspection_id($params['iid']);

		if ($this->type != 'add')
			$this->current_data = json_encode($this->CI->history_model->get_door_by_door_id($this->line_id));

		$this->saveHistory();
	}

	public function saveDff($params)
	{
		$this->entity 	= 'dff';
		$this->line_id 	= @$params['line_id'];
		$this->new_val 	= $params['new_val'];
		
		if (isset($params['user_id']))
			$this->user = $params['user_id'];
	
		if (isset($params['cur_val']))
			$this->current_data	= $params['cur_val'];

		$this->saveHistory();
	}

	public function get_cur_dff($inspection, $field, $user)
	{
		return $this->CI->db->where(array('Inspections_idInspections' => $inspection, 'FormFields_idFormFields' => $field, 'Users_idUsers' => $user))->get('DoorsFormFields')->row_array();
	}


	public function saveExperts($params)
	{
		$this->entity 	= 'experts';
		$this->line_id 	= $params['line_id'];
		$this->new_val 	= $params['new_val'];
		$this->type 	= $params['type'];
		
		if ($this->type != 'add')
			$this->current_data = json_encode($this->CI->history_model->get_expert_by_expert_id($this->line_id));

		$this->saveHistory();
	}

	public function saveFiles($params)
	{
		$this->entity 	= 'files';
		$this->line_id 	= $params['line_id'];
		$this->new_val 	= $params['new_val'];
		$this->type 	= $params['type'];
		
		if (isset($params['user_id']))
			$this->user = $params['user_id'];

		if ($this->type != 'add')
			$this->current_data = json_encode($this->CI->history_model->get_file_by_file_id($this->line_id));

		$this->saveHistory();
	}

	public function saveFF()
	{

	}

	public function saveInfo($params)
	{
		$this->entity 	= 'info';
		$this->line_id 	= $params['line_id'];
		$this->new_val 	= $params['new_val'];
		$this->type 	= $params['type'];
		
		if ($this->type != 'add')
			$this->current_data = json_encode($this->CI->history_model->get_info_by_info_id($this->line_id));

		$this->saveHistory();

	}

	public function saveIff($params)
	{
		$this->entity 	= 'iff';
		$this->line_id 	= $params['line_id'];
		$this->new_val 	= $params['new_val'];
		$this->type 	= $params['type'];
			
		if (isset($params['user_id']))
			$this->user = $params['user_id'];

		if ($this->type != 'add')
			$this->current_data = json_encode($this->CI->history_model->get_iff_by_iff_id($this->line_id));

		$this->saveHistory();
	}

	public function saveInspections($params)
	{
		$this->entity 	= 'inspections';
		$this->line_id 	= $params['line_id'];
		$this->new_val 	= $params['new_val'];
		$this->type 	= $params['type'];
		
		if (isset($params['user_id']))
			$this->user = $params['user_id'];

		if ($this->type != 'add')
			$this->current_data = json_encode($this->CI->history_model->get_review_by_review_id($this->line_id));

		$this->saveHistory();
	}

	public function saveRR($params)
	{
		$this->entity 	= 'rr';
		$this->line_id 	= $params['line_id'];
		$this->new_val 	= $params['new_val'];
		$this->type 	= $params['type'];
		
		if ($this->type != 'add')
			$this->current_data = json_encode($this->CI->history_model->get_rr_by_user_parent($this->line_id));

		$this->saveHistory();
	}

	public function saveUsers($params)
	{
		$this->entity 	= 'users';
		$this->line_id 	= $params['line_id'];
		$this->new_val 	= $params['new_val'];
		$this->type 	= $params['type'];
	
		if (isset($params['user_id']))
			$this->user = $params['user_id'];

		if ($this->type != 'add')
			$this->current_data = json_encode($this->CI->history_model->get_user_by_user_id($this->line_id));

		$this->saveHistory();
	}
	
	public function saveLic($params)
	{
		$this->entity 	= 'lic';
		$this->line_id 	= $params['line_id'];
		$this->new_val 	= $params['new_val'];
		$this->type 	= $params['type'];
	
		if (isset($params['user_id']))
			$this->user = $params['user_id'];

		if ($this->type != 'add')
			$this->current_data = json_encode($this->CI->history_model->get_licensing_by_user_id($this->line_id));

		$this->saveHistory();
	}

}