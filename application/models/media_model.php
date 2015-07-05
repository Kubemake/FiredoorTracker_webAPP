<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Media_model  extends CI_Model 
{
 
	function __construct()
	{
		// Call the Model constructor
		parent::__construct();
	}

	function add_uploaded_file($insdata)
	{
		if ($this->db->insert('Files', $insdata))
			return $this->db->insert_id();
		else
			return FALSE;
	}

	function get_user_files($type = FALSE)
	{
		$this->db->select('f.*, d.barcode as aperture');
		$this->db->from('Files f');
		$this->db->join('InspectionFieldFiles iff', 'iff.Files_idFiles = f.idFiles', 'left');
		$this->db->join('Doors d', 'd.idDoors = iff.Doors_idDoors', 'left');
		
		if ($type)
			$this->db->where('f.type', $type);
		$this->db->where('UserId', $this->session->userdata('user_parent'));
		$this->db->where('f.deleted', 0);
		$this->db->order_by('idFiles', 'desc');
		return $this->db->get()->result_array();
	}

	function get_file_data_by_id($file_id)
	{
		$this->db->select('f.*, d.idDoors as aperture_id, d.name as aperture, d.Buildings_idBuildings, b.name as location_name, iff.FormFields_idFormFields');
		$this->db->from('Files f');
		$this->db->join('InspectionFieldFiles iff', 'iff.Files_idFiles = f.idFiles AND iff.deleted = 0', 'left');
		$this->db->join('Doors d', 'd.idDoors = iff.Doors_idDoors', 'left');
		$this->db->join('Buildings b', 'b.idBuildings = d.Buildings_idBuildings', 'left');
		$this->db->where('f.idFiles', $file_id);
		return $this->db->get()->row_array();
	}

	function add_aperture_file($file_id, $door_id, $field_id=FALSE)
	{
		$this->db->where('Files_idFiles', $file_id)->delete('InspectionFieldFiles');
		
		$insdata = array(
			'Doors_idDoors' => $door_id,
			'Files_idFiles' => $file_id
		);
		
		if ($field_id)
			$insdata['FormFields_idFormFields'] = $field_id;

		$insert_query = $this->db->insert_string('InspectionFieldFiles', $insdata);
		$insert_query = str_replace('INSERT INTO', 'INSERT IGNORE INTO', $insert_query);
		$this->db->query($insert_query);

		$iffid = $this->db->where($insdata)->get('InspectionFieldFiles')->row_array();
        return $iffid['id'];

	}

	function update_aperture_file($file_id, $door_id=FALSE, $field_id=FALSE)
	{
		$upddata['Doors_idDoors'] = 'NULL';
		if ($door_id)
			$upddata['Doors_idDoors'] = $door_id;
		
		$upddata['FormFields_idFormFields'] = 'NULL';
		if ($field_id)
			$upddata['FormFields_idFormFields'] = $field_id;

		return $this->db->where('Files_idFiles', $file_id)->update('InspectionFieldFiles', $upddata);
		
	}

	function delete_user_file($file_id)
	{
		$this->db->where('Files_idFiles', $file_id)->update('InspectionFieldFiles', array('deleted' => $this->session->userdata('user_id')));

		return $this->db->where('idFiles', $file_id)->update('Files', array('deleted' => $this->session->userdata('user_id')));
	}
}

/* End of file media_model.php */
/* Location: ./application/model/media_model.php */