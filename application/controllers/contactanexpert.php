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
			$this->load->library('History_library');
			$insdata = array(
				'name' 			=> $postdata['name'],
				'description' 	=> $postdata['description'],
				'logo'			=> $postdata['file_path'],
				'link'			=> $postdata['link']
			);
			
			if (!empty($insdata['logo']))
			{
				$insdata['logo'] = str_replace('http://firedoortracker.org', '', $insdata['logo']);
				$insdata['logo'] = get_image_by_height($insdata['logo'], 125, 'resize');
			}
			switch ($postdata['form_type'])
			{
				case 'add_expert':
					$newins = $this->info_model->add_expert($insdata);
					
					$this->history_library->saveExpert(array('line_id' => $newins, 'new_val' => json_encode($insdata), 'type' => 'add'));

					if ($newins)
						$header['msg'] = msg('success', 'Expert contact added successfuly');
				break;

				case 'edit_expert':
					$this->history_library->saveExpert(array('line_id' => $postdata['expert_id'], 'new_val' => json_encode($insdata), 'type' => 'edit'));

					$this->info_model->update_expert($postdata['expert_id'], $insdata);

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
		if (!$expert_id = $this->input->post('id')) return print('empty id');

		if (!$this->info_model->delete_expert_by_id($expert_id))  return print('can\'t delete expert contact by id');

		return print('done');
		exit;
	}
	
}

/* End of file resources.php */
/* Location: ./application/controllers/resources.php */