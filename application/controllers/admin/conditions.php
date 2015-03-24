<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Conditions extends CI_Controller {

	function __construct()
	{
		parent::__construct();
		verifyLogged('admin');
		$this->load->model('resources_model');
		$this->load->library('table');
	}

	function index($wall_rate_id = 1)
	{
		$data['current_wall_rate_id'] = $wall_rate_id;
		
		$data['issues'] = $this->resources_model->get_all_issues();

		$data['param']['wall_rates'] = $this->config->item('wall_rates');
		$data['param']['rates_types'] = $this->config->item('rates_types');
		$data['param']['door_rating'] = $this->config->item('door_rating');
		$data['param']['door_matherial'] = $this->config->item('door_matherial');
		$data['param']['door_state'] = $this->config->item('door_state');

		$data['order'] = array();$itt=0;
		foreach ($data['issues'] as $issue_id => $value) //make order for view choices table
			$data['order'][$issue_id] = $itt++;

		$data['order'] = array_flip($data['order']); //key as itterator

		foreach ($this->resources_model->get_all_choices($wall_rate_id) as $element)
		{
			$data['choices'][$element['ratesTypes']][$element['doorMatherial']][$element['doorRating']] = 1;
			$data['choices_rows'][$element['idField']][$element['ratesTypes']][$element['doorMatherial']][$element['doorRating']] = $element['value'];
		}

		$data['table_rows'] = 0;
	 	foreach ($data['choices'] as $ratesTypesId => $ratesTypes)
	 	{
	 		$data['table_rows_colspan'][$ratesTypesId] = 0;
	 		foreach ($ratesTypes as $doorMatherial)
	 		{
	 			foreach ($doorMatherial as $doorRating)
	 			{
	 				$data['table_rows']++;
	 				$data['table_rows_colspan'][$ratesTypesId]++;
	 			}
	 		}
	 	}
	 	/*echo '<pre>';
	 	print_r($data['choices']);
	 	print_r($data['choices_rows']);die();*/

		$header['page_title'] = 'Conditional Choices';
		
		$this->load->view('header', $header);
		$this->load->view('admin/admin_conditions', $data);
		$this->load->view('footer');
	}

}

/* End of file conditions.php */
/* Location: ./application/controllers/conditions.php */