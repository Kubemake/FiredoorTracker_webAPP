<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Issues extends CI_Controller {

	function __construct()
	{
		parent::__construct();
		verifyLogged('admin');
		$this->load->model('resources_model');
		$this->load->library('table');
	}

	function index()
	{
		if ($this->input->post())
		{
			if ($this->input->post('form_type'))
			{
				$postdata = $this->input->post();
				switch ($postdata['form_type'])
				{
					case 'add_issue':
						unset($postdata['form_type']);
						$this->resources_model->add_issue($postdata);
						$data['msg'] = '<div class="alert alert-success alert-dismissable">Issue successfully added</div>';
					break;
				}
			}
		}

		$data['issues'] = $this->_get_issues_by_parent(0);

		$header['page_title'] = 'List of Issues';
		$footer['scripts'] = '<script type="text/javascript" src="/js/jquery.nestable.js"></script>';

		$this->load->view('header', $header);
		$this->load->view('admin/admin_issues', $data);
		$this->load->view('footer', $footer);
	}

	function _get_issues_by_parent($parent_id)
	{
		$issues = $this->resources_model->get_all_issues_by_parent($parent_id);

		if (empty($issues))
			return '';

		$result = '<ol class="dd-list">' . "\n";
		foreach ($issues as $issue)
		{
			$color = ($issue['type'] != 'answer') ? ' question-color' : ' answer-color';
			$result .= '<li class="dd-item" data-id="' . $issue['idFormFields'] . '">' . "\n";
			$result .= '<div class="dd-handle' . $color . '"><span class="glyphicon glyphicon-align-justify"></span><span class="label-text">' . $issue['label'] . '</span> <small>(id:' . $issue['idFormFields'] . ')</small></div><a onclick="editfield(this);return false;" class="btn btn-xs btn-default btn-pencil"><span class="glyphicon glyphicon-pencil"></span></a>
						<a onclick="deletefield(this);return false;" class="btn btn-xs btn-default btn-trash"><span class="glyphicon glyphicon-trash"></span></a>' . "\n";
			$result .=  $this->_get_issues_by_parent($issue['idFormFields']);
			$result .= '</li>' . "\n";
		}
		$result .= '</ol>' . "\n";

		return $result;
	}

	function ajax_get_issue_by_id()
	{
		if (!$id = $this->input->post('id')) return '';
		
		$data['issue'] = $this->resources_model->get_issue_by_id($id);
		
		$data['issue_types'] = $this->resources_model->get_issue_types();
		
		$this->load->view('admin/admin_issues_edit', $data);
	}

	function ajax_update_issue()
	{
		if (!$fielddata = $this->input->post()) die(json_encode(array('status' => 'error')));
		
		$result = $this->resources_model->update_issue_data($fielddata);

		if ($result === 'duplicate') {
            echo json_encode(array('status' => 'duplicate'));
            exit;
        }
        
		echo json_encode(array('status' => 'ok'));
	}

	function ajax_delete_issue()
	{
		if (!$field_id = $this->input->post('id')) die(json_encode(array('status' => 'error')));
		// $field_id = $this->input->get();
		$result = $this->resources_model->delete_issue_by_id($field_id);

		if (!$result) {
            echo json_encode(array('status' => 'error'));
            exit;
        }
		echo json_encode(array('status' => 'ok'));
	}

	function ajax_issues_reorder()
	{
		if (!$postdata = $this->input->post()) die('no post');

		if (!isset($postdata['issues']) or empty($postdata['issues']) or strpos($postdata['issues'], 'jQuery') !== FALSE) 
			return;

		$all_elem_list = $this->resources_model->get_all_issues();

		$order = array();

		foreach (json_decode($postdata['issues']) as $issue)
		{
			$issdata = $all_elem_list[$issue->id];
 
			$issdata['parent'] = 0;
			$issdata['level'] = 0;

			if (!isset($order[$issdata['parent']]))
				$order[$issdata['parent']] = 0;
			else
				$order[$issdata['parent']]++;

			$issdata['questionId'] = 0;

			$issdata['questionOrder'] = $order[$issdata['parent']];

			$issdata['name'] = trim($issdata['name']);

			$this->resources_model->update_issue_data($issdata);

			if (isset($issue->children)) {
				$order = $this->_submit_reorder($issue->children, $all_elem_list, $order, $issue->id, 1/*, $issdata['nextQuestionId']*/);
			}
		}
	}

	function _submit_reorder($elemtree, $all_elem_list, $order, $parent_id, $level/*, $root_question*/)
	{
		foreach ($elemtree as $issue) {
			
			$issdata = $all_elem_list[$issue->id];

			$issdata['parent'] = $parent_id;
			$issdata['level'] = $level;
			
			if (!isset($order[$issdata['parent']]))
				$order[$issdata['parent']] = 0;
			else
				$order[$issdata['parent']]++;

			$issdata['questionId'] = ($issdata['type'] == 'answer') ? $parent_id: 0;

			$issdata['questionOrder'] = $order[$issdata['parent']];
			
			$issdata['name'] = trim($issdata['name']);

			$this->resources_model->update_issue_data($issdata);

			if (isset($issue->children)) {
				$this->_submit_reorder($issue->children, $all_elem_list, $order, $issue->id, $level+1/*, $root_question*/);
			}
		}

		return $order;
	}
}

/* End of file resources.php */
/* Location: ./application/controllers/resources.php */