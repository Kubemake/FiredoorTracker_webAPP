<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class History_library {

	protected $CI, $line_id = FALSE, $new_val = '', $user, $time, $current_data = '', $entity, $type;

	public function __construct($params = FALSE)
	{
		$this->CI =& get_instance();

		$this->user 	= $this->CI->session->userdata('user_id');
		$this->line_id 	= $params['line_id'];
		$this->new_val 	= $params['new_val'];
		$this->type 	= $params['type'];
		$this->time 	= time();

		$this->CI->load->model('history_model');
		
		$this->CI->${$params['entity']}();
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

	public function saveAddress()
	{

	}

	public function saveBuildings()
	{

	}

	public function saveCC()
	{

	}

	public function saveDoors()
	{

	}

	public function saveDff()
	{

	}

	public function saveExperts()
	{
		$this->entity 		= 'experts';
		
		if ($this->type != 'add')
			$this->current_data = json_encode($this->CI->history_model->get_expert_by_expert_id($this->line_id));

		$this->saveHistory();
	}

	public function saveFiles()
	{

	}

	public function saveFF()
	{

	}

	public function saveInfo()
	{

	}

	public function saveIff()
	{

	}

	public function saveInspections()
	{

	}

	public function saveRR()
	{

	}

	public function saveUb()
	{

	}

	public function saveUsers()
	{

	}

}