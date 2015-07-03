<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Media extends CI_Controller {

	function __construct()
	{
		parent::__construct();
		verifyLogged();
		$this->load->model('media_model');
	}

	function index($image_page=1, $video_page=1)
	{
		$this->load->model('resources_model');


		if ($postdata = $this->input->post())
		{
			$this->load->library('History_library');
			
			if ($postdata['form_type'] == 'edit_file') {
				
				$upddata['Files_idFiles'] = $postdata['idfiles'];
				
				$aperture_id = FALSE;
				if (isset($postdata['aperture']) && !empty($postdata['aperture']))
				{
					$upddata['Doors_idDoors'] = $postdata['aperture'];
					$aperture_id = $postdata['aperture'];
				}
				
				$field_id = FALSE;
				if (isset($postdata['fffile']) && !empty($postdata['fffile']))
				{
					$field_id = $postdata['fffile'];
					$upddata['FormFields_idFormFields'] = $postdata['fffile'];
				}

				$this->history_library->saveIff(array('line_id' => $postdata['idfiles'], 'new_val' => json_encode($upddata), 'type' => 'edit'));

				$this->media_model->update_aperture_file($postdata['idfiles'], $aperture_id, $field_id);
				
			}
			else
			{
				$adddata = array(
					'Users_idUsers'  => $this->session->userdata('user_id'),
					'path' 			 => $postdata['file_path'],
					'type' 			 => $postdata['file_type'],
					'FileUploadDate' => date('Y-m-d H:i:s', $postdata['file_time'])
				);
				$fileid = $this->media_model->add_uploaded_file($adddata);

				$this->history_library->saveFiles(array('line_id' => $fileid, 'new_val' => json_encode($adddata), 'type' => 'add'));

				if ($postdata['aperture'] > 0)
				{
					$iffid = $this->media_model->add_aperture_file($fileid, $postdata['aperture']);
					
					$upddata = array(
						'Files_idFiles' => $fileid,
						'Doors_idDoors' => $postdata['aperture']
					);
					$this->history_library->saveIff(array('line_id' => $iffid, 'new_val' => json_encode($upddata), 'type' => 'add'));
				}
			}

		}

		$data['user_apertures'] 	= $user_apertures = $this->resources_model->get_user_apertures();
		
		$data['user_buildings'] 	= $this->resources_model->get_user_buildings();
		
		$data['files'] 				= $this->media_model->get_user_files();

		$data['video_files'] 		= $this->media_model->get_user_files('video');

		//datatable
		$header['styles']   = addDataTable('css');
		$footer['scripts']  = addDataTable('js');

		//uploader
		$footer['scripts'] .= '<script type="text/javascript" src="/js/uploader/src/dmuploader.min.js"></script>' . "\n";
		$footer['scripts'] .= '<script type="text/javascript" src="/js/custom-upload.js"></script>' . "\n";
		
		//lightbox
		$header['styles']  .= '<link rel="stylesheet" type="text/css" href="/js/lightbox/dist/ekko-lightbox.min.css">' . "\n";
		$footer['scripts'] .= '<script type="text/javascript" src="/js/lightbox/dist/ekko-lightbox.min.js"></script>' . "\n";
		
		
		//video player
		$footer['scripts'] .= '<script type="text/javascript" src="/js/flowplayer/flowplayer-3.2.2.min.js"></script>' . "\n";
		
		$header['page_title'] = 'MEDIA';

		$this->load->view('header', $header);
		$this->load->view('media', $data);
		$this->load->view('footer', $footer);
	}

	function upload()
	{
		if (!$_FILES)  die();
		
		if (!isset($_POST['owner']) or empty($_POST['owner'])) {
			echo 'user must be logged in for upload files!';die;
		}
	
		$name = $_FILES['file']['name'];
		$ext = substr($name, -4);
		$name = substr($name, 0, -4);
		$name = translate($_FILES['file']['name']);
		$creation_time = time();

		$upload_dir = '/upload/' . $_POST['owner'];

		if (!is_dir($_SERVER['DOCUMENT_ROOT'] . $upload_dir)) 
			mkdir($_SERVER['DOCUMENT_ROOT'] . $upload_dir);

		$file_path = $upload_dir . '/' . $name . '_' . $creation_time . $ext;

		if (move_uploaded_file($_FILES['file']['tmp_name'], $_SERVER['DOCUMENT_ROOT'] . $file_path)) 
		{
			echo base_url($file_path); die;
		}

		echo 'Error when upload file!';die;
	}

	function ajax_load_video()
	{
		if (!$params = $this->input->post()) return '';
		$data['title'] = $params['title'];
		$data['remote'] = $params['url'];

		$this->load->view('/modal/view_video_modal', $data);
	}

	function ajax_file_delete()
	{
		if (!$params = $this->input->post()) return '';
		if ($this->media_model->delete_user_file($params['id']))
			die('done');
	}
	
}

/* End of file media.php */
/* Location: ./application/controllers/media.php */