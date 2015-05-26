<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Service_model  extends CI_Model 
{
 
    function __construct()
    {
        // Call the Model constructor
        parent::__construct();
    }

    function get_token_info($token)
    {
    	$this->db->where('token', $token);
    	return $this->db->get('UserTokens')->row_array();
    }

	function get_user_data_by_login_password($login, $password)
	{
		$this->db->where('email', $login);
		$this->db->where('password', $password);
    	return $this->db->get('Users')->row_array();
	}

	function set_user_token($user_id, $token)
	{
		$data = array(
			'user_id' 	=> $user_id,
			'token'		=> $token,
			'expires'	=> date('Y-m-d H:i:s', strtotime('+1 day'))
		);
		$this->db->insert('UserTokens', $data);
	}

	function delete_user_token($user_id)
	{
		$this->db->where('user_id', $user_id);
		$this->db->delete('UserTokens');
	}

	//DEPRECATED
	function get_issues_version()
	{
		$this->db->select('value');
		$this->db->where('name', 'issues_version');
		return $this->db->get('Settings')->row_array();
	}

	function get_issues_list()
	{
		$this->db->where('deleted', 0);
		return $this->db->get('FormFields')->result_array();
	}

	function get_aperture_info_and_selected($aperture_id)
	{
		$this->db->select('Building, Floor, Wing, Area, Level, IntExt, wall_Rating, smoke_Rating, material, rating, width, height, door_type, vision_Light_Present, vision_Light, singage, auto_Operator, doorLabel_Type, doorLabel_Rating, doorLabel_Testing_Lab, doorLabel_Manufacturer, doorLabel_serial, doorLabel_Min_Latch, doorLabel_Temp_Rise, frameLabel_Type, frameLabel_Rating, frameLabel_Testing_Lab, frameLabel_Manufacturer, frameLabel_serial, number_Doors, barcode'); 
		$this->db->where('idDoors', $aperture_id);
		$doorval = $this->db->get('Doors')->row_array();
		
		return $doorval;
	}

	function update_aperture_overview_info($inspection_id, $updateData, $user_id)
	{
		$app_id = $this->db->where('idInspections', $inspection_id)->get('Inspections')->row_array();
		
		if ($app_id['Inspector'] != $user_id) //update reviewer if changed
			$this->db->where('idInspections', $inspection_id)->update('Inspections', array('Inspector' => $user_id));

		$this->db->where('idDoors', $app_id['idAperture']);
		$this->db->update('Doors', $updateData);
	}

	function get_aperture_issues_and_selected($input_data)
	{
		$aperture_id = $this->db->where('idInspections', $input_data['inspection_id'])->get('Inspections')->row_array();
		$aperture_id = $aperture_id['idAperture'];
		
		$this->db->select('ff.*, cc.value as status, dff.value as selected, f.path'); //, f.idFiles
		$this->db->from('FormFields ff');
		$this->db->join('DoorsFormFields dff', 'dff.FormFields_idFormFields = ff.idFormFields AND dff.Inspections_idInspections = ' . $input_data['inspection_id'], 'left');
		$this->db->join('ConditionalChoices cc', 'cc.idField = ff.idFormFields AND cc.wallRates = ' . $input_data['wall_Rating'] . ' AND cc.ratesTypes = ' . $input_data['smoke_Rating'] . ' AND cc.doorRating = ' . $input_data['rating'] . ' AND cc.doorMatherial = ' . $input_data['material'], 'left');
		$this->db->join('InspectionFieldFiles iff', 'iff.FormFields_idFormFields = ff.idFormFields AND iff.deleted = 0 AND iff.Doors_idDoors = ' . $aperture_id, 'left');
		$this->db->join('Files f', 'f.idFiles = iff.Files_idFiles', 'left');
		$this->db->where('ff.deleted', 0);
		$results = $this->db->get()->result_array();

		$issues = array(); $tabs = array();
		
		$addbtnQ = 0;

		foreach ($results as $result) {
			$temp = $result;
			unset($temp['deleted'], $temp['level'], $temp['parent'], $temp['path']);

			if ($result['type'] == 'answer')
			{
				//special part for signage
				if ($result['name'] == 'Signage')
					$signage = $result;
				
				if ($result['parent'] == 0)
				{
					//special part for Glazing Review Tab
					if ($result['name'] == 'GlazingReview')
						$glzng = $result;

					$tabs[$result['idFormFields']] = $temp;

					if (!isset($tabs[$result['idFormFields']]['images']))
						$tabs[$result['idFormFields']]['images'] = array();

					if (!empty($result['path']))
						$tabs[$result['idFormFields']]['images'][] = $result['path'];
				}
				else
				{
					$issues[$result['questionId']]['answers'][$result['idFormFields']] = $temp;

					if (!isset($issues[$result['questionId']]['images']))
						$issues[$result['questionId']]['images'] = array();

					if (!empty($result['path']))
						$issues[$result['questionId']]['images'][] = $result['path'];
				}
			}
			else
			{
				$issues[$result['idFormFields']] = $temp;

				if (!isset($issues[$result['idFormFields']]['images']))
					$issues[$result['idFormFields']]['images'] = array();
				
				if (!empty($result['path']))
					$issues[$result['idFormFields']]['images'][] = $result['path'];
			}
		}

		return array(
			'signage'			=> $signage,
			'glzng'				=> $glzng,
			'tabs' 				=> $tabs,
			'issues' 			=> $issues
		);
	}

	function get_question_answers_by_answer_id_and_inspection_id($idField, $inspection)
	{
		$this->db->select('d.*');
		$this->db->from('Inspections i');
		$this->db->join('Doors d', 'i.idAperture = d.idDoors');
		$this->db->where('idInspections', $inspection);
		$apert = $this->db->get()->row_array();

		$quest = $this->db->select('questionId')->where('idFormFields', $idField)->get('FormFields')->row_array();

		$this->db->select('ff.*, cc.value as status, dff.value as selected');
		$this->db->from('FormFields ff');
		$this->db->join('DoorsFormFields dff', 'dff.FormFields_idFormFields = ff.idFormFields AND dff.Inspections_idInspections = ' . $inspection, 'left');
		$this->db->join('ConditionalChoices cc', 'cc.idField = ff.idFormFields AND cc.wallRates = ' . $apert['wall_Rating'] . ' AND cc.ratesTypes = ' . $apert['smoke_Rating'] . ' AND doorRating = ' . $apert['rating'] . ' AND doorMatherial = ' . $apert['material'], 'left');
		$this->db->where('ff.questionId', $quest['questionId']);
		return $this->db->get()->result_array();
	}

function get_question_answers_by_question_id_and_inspection_id($quest, $inspection)
	{
		$this->db->select('d.*');
		$this->db->from('Inspections i');
		$this->db->join('Doors d', 'i.idAperture = d.idDoors');
		$this->db->where('idInspections', $inspection);
		$apert = $this->db->get()->row_array();

		$this->db->select('ff.*, cc.value as status, dff.value as selected');
		$this->db->from('FormFields ff');
		$this->db->join('DoorsFormFields dff', 'dff.FormFields_idFormFields = ff.idFormFields AND dff.Inspections_idInspections = ' . $inspection, 'left');
		$this->db->join('ConditionalChoices cc', 'cc.idField = ff.idFormFields AND cc.wallRates = ' . $apert['wall_Rating'] . ' AND cc.ratesTypes = ' . $apert['smoke_Rating'] . ' AND doorRating = ' . $apert['rating'] . ' AND doorMatherial = ' . $apert['material'], 'left');
		$this->db->where('ff.questionId', $quest);
		$this->db->order_by('ff.questionOrder', 'ASC');
		return $this->db->get()->result_array();
	}

	function delete_inspection_data($inspection, $field, $user)
	{
		$this->db->where(array(
			'FormFields_idFormFields' 	=> $field,
			'Inspections_idInspections' => $inspection,
    		'Users_idUsers' 		  	=> $user
		));
		return $this->db->delete('DoorsFormFields');
	}
	
	function add_inspection_data($inspection, $field, $user, $value)
	{
		$insdata = array(
			'FormFields_idFormFields' 	=> $field,
			'Inspections_idInspections' => $inspection,
    		'Users_idUsers' 		  	=> $user,
    		'value' 				  	=> $value
		);

		$this->db->insert('DoorsFormFields', $insdata);
		return $this->db->insert_id();
	}

	function get_all_questions_with_status_answers($inspection)
	{
		$this->db->select('d.*');
		$this->db->from('Inspections i');
		$this->db->join('Doors d', 'i.idAperture = d.idDoors');
		$this->db->where('idInspections', $inspection);
		$apert = $this->db->get()->row_array();


		$this->db->select('ff.questionId');
		$this->db->from('FormFields ff');
		$this->db->join('ConditionalChoices cc', 'cc.idField = ff.idFormFields AND cc.wallRates = ' . $apert['wall_Rating'] . ' AND cc.ratesTypes = ' . $apert['smoke_Rating'] . ' AND cc.doorRating = ' . $apert['rating'] . ' AND cc.doorMatherial = ' . $apert['material'], 'left');
		$this->db->group_by('ff.questionId');
		$this->db->where('cc.value >', 0);
		
		return count($this->db->get()->result_array());
	}

	function get_curent_questions_with_status_answers($inspection, $user)
	{
		$this->db->select('d.*');
		$this->db->from('Inspections i');
		$this->db->join('Doors d', 'i.idAperture = d.idDoors');
		$this->db->where('idInspections', $inspection);
		$apert = $this->db->get()->row_array();

		$this->db->select('ff.questionId');
		$this->db->from('FormFields ff');
		$this->db->join('ConditionalChoices cc', 'cc.idField = ff.idFormFields AND cc.wallRates = ' . $apert['wall_Rating'] . ' AND cc.ratesTypes = ' . $apert['smoke_Rating'] . ' AND cc.doorRating = ' . $apert['rating'] . ' AND cc.doorMatherial = ' . $apert['material'], 'left');
		$this->db->join('DoorsFormFields dff', 'dff.FormFields_idFormFields = ff.idFormFields AND dff.Inspections_idInspections = ' . $inspection . ' AND dff.Users_idUsers = ' . $user, 'left');
		$this->db->group_by('ff.questionId');
		$this->db->where('cc.value >', 0);
		$this->db->where('dff.value IS NOT ', 'NULL', FALSE);

		return count($this->db->get()->result_array());
	}

	function get_inspection_answers($inspection, $aperture_id)
	{
		$apert = $this->db->where('idDoors', $aperture_id)->get('Doors')->row_array();
		$this->db->select('cc.value');
		$this->db->from('DoorsFormFields dff');
		$this->db->join('ConditionalChoices cc', 'cc.idField=dff.FormFields_idFormFields AND cc.wallRates=' . $apert['wall_Rating'] . ' AND cc.ratesTypes=' . $apert['smoke_Rating'] . ' AND cc.doorRating =' . $apert['rating'] . ' AND cc.doorMatherial=' . $apert['material'], 'left');
		$this->db->where('dff.Inspections_idInspections', $inspection);
		$this->db->group_by('cc.value');
		$this->db->where('cc.value !=', '');
		$result = $this->db->get()->result_array();
		return $result;
	}

	function get_images_by_aperture_id_and_field_id($aperture_id, $field_id = FALSE)
	{
		if (empty($aperture_id))
			return array();
		
		if (!is_array($aperture_id))
			$aperture_id = array($aperture_id);

		
		if ($field_id)
		{
			$inspection_id = $this->db->select('idInspections')->where_in('idAperture', $aperture_id)->get('Inspections')->row_array();
			$inspection_id = $inspection_id['idInspections'];

			$fields_ids = $this->get_question_answers_by_answer_id_and_inspection_id($field_id, $inspection_id);

			$field_id = array();
			
			foreach ($fields_ids as $value)
				$field_id[] = $value['idFormFields'];

			$this->db->where_in('iff.FormFields_idFormFields', $field_id);
		}
		// else
		// 	$this->db->where('iff.FormFields_idFormFields IS ', 'NULL', FALSE);

		$this->db->select('iff.Files_idFiles as file_id, f.path, iff.Doors_idDoors as aperture_id');
		$this->db->from('InspectionFieldFiles iff');
		$this->db->join('Files f', 'f.idFiles = iff.Files_idFiles', 'left');
		$this->db->where_in('iff.Doors_idDoors', $aperture_id);

		return $this->db->get()->result_array();
	}

	function get_aperture_id_by_inspection_id($inspection_id)
	{
		$this->db->select('idAperture');
		$this->db->where('idInspections', $inspection_id);

		return $this->db->get('Inspections')->row_array();
	}

	function get_enum_values($table_name, $field_name)
	{
		$type = $this->db->query("SHOW COLUMNS FROM {$table_name} LIKE '{$field_name}'")->row( 0 )->Type;
		preg_match("/^enum\(\'(.*)\'\)$/", $type, $matches);
		$enum = explode("','", $matches[1]);
		return $enum;
	}

}