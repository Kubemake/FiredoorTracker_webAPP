<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Resources extends CI_Controller {

	function __construct()
	{
		parent::__construct();
		verifyLogged();
		$this->load->model('info_model');
	}

	function index($curlet = '')
	{
		if ($postdata = $this->input->post())
		{

			$insdata = array(
				'name' 			=> $postdata['name'],
				'description' 	=> $postdata['description'],
				'type'			=> $postdata['type']
			);
			
			switch ($postdata['form_type'])
			{
				case 'add_info':
					$newins = $this->info_model->add_info($insdata);
					
					if ($newins)
						$header['msg'] = msg('success', $postdata['type'] . ' added successfuly');
				break;
				case 'edit_info':
					$this->info_model->update_info($postdata['info_id'], $insdata);
					$header['msg'] = msg('success', $postdata['type'] . ' successfully updated');
				break;
			}
		}

		$data['letters'] = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z');
		
		$glossary_letters = $this->info_model->get_all_glossary_letters();
		
		$fst = '';
		foreach ($glossary_letters as $letter)
		{
			$data['letter_available'][$letter['letter']] = 1;

			if ($fst == '')
				$fst = $letter['letter'];
		}

		if ($curlet == '')
			$curlet = $fst;

		$data['glossary'] = $this->info_model->get_glossary_by_letter($curlet);
		$data['faqs'] 	  = $this->info_model->get_all_faq();

		$header['page_title'] = 'RESOURCES';
		
		$this->load->view('header', $header);
		$this->load->view('resources', $data);
		$this->load->view('footer');
	}
	
	function ajax_delete_info()
	{
		if (!$info_id = $this->input->post('id')) return print('empty id');

		if (!$this->info_model->delete_info_by_id($info_id))  return print('can\'t delete info by id');

		return print('done');
		exit;

	}
}

/* End of file resources.php */
/* Location: ./application/controllers/resources.php */