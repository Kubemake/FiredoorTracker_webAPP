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
		ini_set('memory_limit', '-1');
		set_time_limit(0);
		$data['current_wall_rate_id'] = $wall_rate_id;
		
		$data['issues'] = $this->resources_model->get_all_issues();

		$data['param']['wall_rates'] = $this->config->item('wall_rates');
		$data['param']['rates_types'] = $this->config->item('rates_types');
		$data['param']['door_rating'] = $this->config->item('door_rating');
		$data['param']['door_matherial'] = $this->config->item('door_matherial');
		$data['param']['door_state'] = $this->config->item('door_state');

		$data['order'] = array();
		$data['order'] = $this->_get_issues_by_parent(0, $data['order']);  // issues order

		$data['choices'] = array();
		foreach ($this->resources_model->get_all_choices($wall_rate_id) as $element)
		{
			$data['choices'][$element['ratesTypes']][$element['doorMatherial']][$element['doorRating']] = 1;
			$data['choices_rows'][$element['idField']][$element['ratesTypes']][$element['doorMatherial']][$element['doorRating']] = $element['value'];
		}
		
		ksort($data['choices']);
		foreach ($data['choices'] as &$rateTypes)
		{
			ksort($rateTypes);
			foreach ($rateTypes as &$doorMatherial)
				ksort($doorMatherial);
		}

		$data['table_rows'] = 0;
	 	foreach ($data['choices'] as $ratesTypesId => $ratesTypes_data)
	 	{
	 		$data['table_rows_colspan'][$ratesTypesId] = 0;
	 		foreach ($ratesTypes_data as $doorMatherial_data)
	 		{
	 			foreach ($doorMatherial_data as $doormat_id => $doorRating_data)
	 			{
	 				$data['table_rows']++;
	 				$data['table_rows_colspan'][$ratesTypesId]++;
	 			}
	 		}
	 	}

		$header['page_title'] = 'Conditional Choices';
		
		$this->load->view('header', $header);
		$this->load->view('admin/admin_conditions', $data);
		$this->load->view('footer');
	}

	function _get_issues_by_parent($parent_id, $order)
	{
		$issues = $this->resources_model->get_all_issues_by_parent($parent_id);

		if (empty($issues))
			return $order;

		foreach ($issues as $issue)
		{
			$order[] = $issue['idFormFields'];
			$order =  $this->_get_issues_by_parent($issue['idFormFields'], $order);

		}
		return $order;
	}

	function ajax_update_condition()
	{
		if (!$postdata = $this->input->post()) 												return print('empty post');
		if (!isset($postdata['id']) or empty($postdata['id']))  							return print('wrong id');
		if (!isset($postdata['wall_rate_id']) or empty($postdata['wall_rate_id']))  		return print('wrong wall_rate_id');
		if (!isset($postdata['ratesTypesId']) or empty($postdata['ratesTypesId']))  		return print('wrong ratesTypesId');
		if (!isset($postdata['doorMatherialid']) or empty($postdata['doorMatherialid'])) 	return print('wrong doorMatherialid');
		if (!isset($postdata['doorRatingId']) or empty($postdata['doorRatingId']))  		return print('wrong doorRatingId');
		if (!isset($postdata['val']))  														return print('wrong value');

		$value = $postdata['val'];

		if (empty($value))
			$result = $this->resources_model->delete_choice($postdata['id'], $postdata['wall_rate_id'], $postdata['ratesTypesId'], $postdata['doorMatherialid'], $postdata['doorRatingId']);
		else
			$result = $this->resources_model->update_choice($postdata['id'], $postdata['wall_rate_id'], $postdata['ratesTypesId'], $postdata['doorMatherialid'], $postdata['doorRatingId'], $value);

		if ($result) 
			die('done');

		die('error');
	}
}

/* End of file conditions.php */
/* Location: ./application/controllers/conditions.php */