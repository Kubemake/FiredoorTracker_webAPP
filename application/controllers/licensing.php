<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Licensing extends CI_Controller {

	function __construct()
	{
		parent::__construct();
		verifyLogged();
		$this->load->model('licensing_model');
		$this->load->library('table');
	}

	function index()
	{
		if ($this->session->userdata('user_role') != 1)
			redirect('/');

		// $licdata = $this->licensing_model->get_lic_info_by_client_id2($this->session->userdata('user_id'));
		// printdata($licdata);

		$this->table->set_heading(
			'id',
			'Expire',
			array('data' => 'Directors account',	 'class' => 'not-mobile'),
			array('data' => 'Supervisors account',	 'class' => 'not-mobile'),
			array('data' => 'Mechanics account',	 'class' => 'not-mobile'),
			array('data' => 'Max. amount of doors',	 'class' => 'not-mobile'),
			'Status'
		);

		$licdata = $this->licensing_model->get_all_client_licensing($this->session->userdata('user_id'));

		if (!empty($licdata))
		{
			foreach ($licdata as $lic)
			{
				$status = (strtotime($lic['expired']) < time()) ? '<span style="color:red;">Expired</span>' : '<span style="color:blue;">Active</span>';
				$this->table->add_row($lic['id'], $lic['expired'], $lic['dir'], $lic['sv'], $lic['mech'], $lic['inspections'], $status);
			}
		}
		

		$tmpl = array ( 'table_open'  => '<table class="table table-striped table-hover table-bordered table-responsive table-condensed" width="100%">' );
		$this->table->set_template($tmpl); 
		$data['result_table'] = $this->table->generate(); 

		$header['page_title'] = 'LICENSING';

		//datatables
		$header['styles']  = addDataTable('css');
		$footer['scripts'] = addDataTable('js');

		$this->load->view('header', $header);
		$this->load->view('user/user_licenses', $data);
		$this->load->view('footer', $footer);
	}

}

/* End of file resources.php */
/* Location: ./application/controllers/resources.php */