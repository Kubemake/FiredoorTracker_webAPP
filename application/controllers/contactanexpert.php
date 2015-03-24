<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Contactanexpert extends CI_Controller {

	function __construct()
	{
		parent::__construct();
		verifyLogged();
		$this->load->model('info_model');
	}

	function index()
	{
		if ($postdata = $this->input->post())
		{

			$insdata = array(
				'name' 			=> $postdata['name'],
				'description' 	=> $postdata['description'],
				'logo'			=> $postdata['file_path'],
				'link'			=> $postdata['link']
			);
			
			switch ($postdata['form_type'])
			{
				case 'add_expert':
					$newins = $this->info_model->add_expert($insdata);
					
					if ($newins)
						$header['msg'] = msg('success', 'Expert contact added successfuly');
				break;
				case 'edit_expert':
					$this->info_model->update_expert($postdata['info_id'], $insdata);
					$header['msg'] = msg('success', 'Expert contact successfully updated');
				break;
			}
		}

		$data['experts'] = $this->info_model->get_experts_list();

		$header['page_title'] = 'CONTACT AN EXPERT';
		
		$this->load->view('header', $header);
		$this->load->view('contactanexpert', $data);
		$this->load->view('footer');
	}

	function ajax_delete_expert()
	{
		// if (!$expert_id = $this->input->post('id')) return print('empty id');
$expert_id = 4;
		if (!$this->info_model->delete_expert_by_id($expert_id))  return print('can\'t delete expert contact by id');

		return print('done');
		exit;
	}
	
}

/* End of file resources.php */
/* Location: ./application/controllers/resources.php */