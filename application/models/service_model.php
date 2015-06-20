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
		$this->db->select('Building, Floor, Wing, Area, Level, IntExt, wall_Rating, smoke_Rating, material, rating, width, height, door_type, vision_Light_Present, vision_Light, singage, auto_Operator, doorLabel_Type, doorLabel_Rating, doorLabel_Testing_Lab, doorLabel_Manufacturer, doorLabel_serial, doorLabel_Min_Latch, doorLabel_Temp_Rise, frameLabel_Type, frameLabel_Rating, frameLabel_Testing_Lab, frameLabel_Manufacturer, frameLabel_serial, number_Doors, barcode, comment'); 
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

	function get_aperture_issues_tabs($inspection_id)
	{
		$this->db->where('type', 'answer');
		$this->db->where('parent', 0);
		$this->db->where('level', 0);
		return $this->db->get('FormFields')->result_array();
	}

	function get_aperture_issues_and_selected($input_data, $light = FALSE)
	{
		$aperture_id = $this->db->where('idInspections', $input_data['inspection_id'])->get('Inspections')->row_array();
		$aperture_id = $aperture_id['idAperture'];
		
		$imgss = $light ? '' : ', f.path, f.name as fname';
		$this->db->select('ff.*, cc.value as status, dff.value as selected' . $imgss); //, f.idFiles
		$this->db->from('FormFields ff');
		$this->db->join('DoorsFormFields dff', 'dff.FormFields_idFormFields = ff.idFormFields AND dff.Inspections_idInspections = ' . $input_data['inspection_id'], 'left');
		$this->db->join('ConditionalChoices cc', 'cc.idField = ff.idFormFields AND cc.wallRates = ' . $input_data['wall_Rating'] . ' AND cc.ratesTypes = ' . $input_data['smoke_Rating'] . ' AND cc.doorRating = ' . $input_data['rating'] . ' AND cc.doorMatherial = ' . $input_data['material'], 'left');
		$this->db->join('InspectionFieldFiles iff', 'iff.FormFields_idFormFields = ff.idFormFields AND iff.deleted = 0 AND iff.Doors_idDoors = ' . $aperture_id, 'left');
		
		if (!$light)
			$this->db->join('Files f', 'f.idFiles = iff.Files_idFiles', 'left');

		$this->db->where('ff.deleted', 0);
		$results = $this->db->get()->result_array();

		$issues = array(); $tabs = array();
		
		$addbtnQ = 0;

		foreach ($results as $result)
		{
			$temp = $result;
			unset($temp['deleted'], $temp['level']);
			
			if (!$light)
				unset($temp['path']);

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

					if (!$light && !empty($result['path']))
					{
						$tabs[$result['idFormFields']]['images'][] = $result['path'];
						$tabs[$result['idFormFields']]['images_comments'][] = $result['fname'];
					}
				}
				else
				{
					$issues[$result['questionId']]['answers'][$result['idFormFields']] = $temp;

					if (!isset($issues[$result['questionId']]['images']))

						$issues[$result['questionId']]['images'] = array();

					if (!$light && !empty($result['path']))
					{
						$issues[$result['questionId']]['images'][] = $result['path'];
						$issues[$result['questionId']]['images_comments'][] = $result['fname'];
					}
				}
			}
			else
			{
				$issues[$result['idFormFields']] = $temp;

				if (!isset($issues[$result['idFormFields']]['images']))
					$issues[$result['idFormFields']]['images'] = array();
				
				if (!$light && !empty($result['path']))
				{
					$issues[$result['idFormFields']]['images'][] = $result['path'];
					$issues[$result['idFormFields']]['images_comments'][] = $result['fname'];
				}
			}
		}

		unset($result, $results);

		$result = array(
			'signage'			=> $signage,
			'glzng'				=> $glzng,
			'tabs' 				=> $tabs,
			'issues' 			=> $issues
		);

		unset($issues, $tabs);

		/*spec code for signs*/
		$addbtnqs = array(637, 640, 78);
		foreach ($addbtnqs as $addbtnq)
		{
			if ($addbtnq > 0)
			{
				$signs = $result['issues'][$addbtnq]['answers'];
				
				foreach ($signs as &$sign)
				{
					$sign['forceRefresh'] = 1;

					if (strlen($sign['selected']) > 0)
					{
						//replace if sign size over 5% of door square
						$square = 0;
						$signsize = 0;
						$dim = explode(',',$sign['selected']);
						$square = trim(@$dim[0]) * trim(@$dim[1]); //calc sings square
						if ($square > 0)
							$signsize = $square * 100 / ($input_data['width']*$input_data['height']);
						if ($input_data['wall_Rating'] < 4 && $signsize > 5)
							$sign['status'] = 4;
					}
					else
						unset($signs[$sign['idFormFields']]);
				}

				//add sign btn
				$signs['789789'] = array(
					'idFormFields' => '789789',
					'type' => 'answer',
					'nextQuestionId' => 0,
					'name' => 'AddSignBtn',
					'label' => 'Add Sign',
					'questionId' => $addbtnq,
					'questionOrder' => count($signs)+1,
					'status' => '',
					'forceRefresh' => 1,
					'selected' => ''
				);
				$result['issues'][$addbtnq]['answers'] = $signs;
			}
		}
		/*END spec code for signs*/

		/*spec code for holes*/
		$h1addbtnqs = array(323,344,566,50);
		foreach ($h1addbtnqs as $h1addbtnq)
		{
			if ($h1addbtnq > 0)
			{
				$addbtn = FALSE;
				$holes = $result['issues'][$h1addbtnq]['answers'];
				foreach ($holes as &$hole)
				{
					if (!$addbtn) //Save params for AddBtn
						$addbtn = $hole;

					if (strlen($hole['selected']) == 0)
						unset($holes[$hole['idFormFields']]);
				}

				//add hole btn
				$holes['789790'] = array(
					'idFormFields' => '789790',
					'type' => 'answer',
					'nextQuestionId' => 0,
					'name' => 'AddHoleBtn',
					'label' => 'Add Hole',
					'questionId' => $addbtn['questionId'],
					'questionOrder' => count($holes)+1,
					'status' => '',
					'alert' => 'Are you sure you want to add hole?',
					'forceRefresh' => 1,
					'selected' => ''
				);
				$result['issues'][$h1addbtnq]['answers'] = $holes;
			}
		}
		/*END spec code for frame holes*/

		/*spec code for hinges*/
		$hingesaddbtns = array(662,663,664,665,666,105);
		foreach ($hingesaddbtns as $hngsbtn)
		{
			if ($hngsbtn > 0)
			{
				$addbtn = FALSE;
				$hinges = $result['issues'][$hngsbtn]['answers'];
				foreach ($hinges as &$hinge)
				{
					if (!$addbtn) //Save params for AddBtn
						$addbtn = $hinge;

					if (strlen($hinge['selected']) == 0)
						unset($hinges[$hinge['idFormFields']]);
				}

				//add hinge btn
				$hinges['789791'] = array(
					'idFormFields' => '789791',
					'type' => 'answer',
					'nextQuestionId' => 0,
					'name' => 'AddHingeBtn',
					'label' => 'Add Hinge',
					'questionId' => $addbtn['questionId'],
					'questionOrder' => count($hinges)+1,
					'status' => '',
					'alert' => 'Are you sure you want to add hinge?',
					'forceRefresh' => 1,
					'selected' => ''
				);
				$result['issues'][$hngsbtn]['answers'] = $hinges;
			}
		}
		/*END spec code for frame holes*/

		/*spec code for signage question */
		//hide Signage answer variant 
		if (empty($input_data['singage']) or $input_data['singage'] != 'Yes')
		{
			unset($result['issues'][$result['signage']['parent']]['answers'][$result['signage']['idFormFields']]);
			unset($result['issues'][$result['signage']['nextQuestionId']]);
			foreach ($addbtnqs as $addbtnq) 
				unset($result['issues'][$addbtnq]);
		}
		/*END spec code for signage question */
		
		/*spec code for Vision Light (Glass) Present? question */
		//hide Glazing review TAB!
		if (empty($input_data['vision_Light_Present']) or $input_data['vision_Light_Present'] == 'No')
		{
			unset($result['tabs'][$result['glzng']['idFormFields']]);
			unset($result['issues'][$result['glzng']['nextQuestionId']]);
			unset($result['issues'][86]['answers'][90]);

		}
		/*END spec code for signage question */

		/*spec code for hidding fields for wall_rating_id < 4*/
		if ($input_data['wall_Rating'] < 4)
		{
			foreach (array(219,220) as $hid)
				unset($result['issues'][217]['answers'][$hid]);
		}
		/*END spec code for hidding fields for wall_rating_id > 3*/

		/*spec code for change label for door label rating = 45 */
		/*
		if ($input_data['rating'] == 3)
		{
			$result['issues'][254]['answers'][255]['label'] = 'Less Than 1,296 Square Inches';
			$result['issues'][254]['answers'][256]['label'] = 'Greater Than 1,296 Square Inches';
		}*/
		/*END spec code for hidding fields for wall_rating_id > 3*/

		return $result;
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

	function delete_inspection_data($inspection, $field)
	{
		$this->db->where(array(
			'FormFields_idFormFields' 	=> $field,
			'Inspections_idInspections' => $inspection
		));
		return $this->db->delete('DoorsFormFields');
	}
	
	function add_inspection_data($inspection, $field, $value)
	{
		$insdata = array(
			'FormFields_idFormFields' 	=> $field,
			'Inspections_idInspections' => $inspection,
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
		$this->db->join('DoorsFormFields dff', 'dff.FormFields_idFormFields = ff.idFormFields AND dff.Inspections_idInspections = ' . $inspection, 'left');
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

		$this->db->select('iff.Files_idFiles as file_id, f.path, iff.Doors_idDoors as aperture_id, f.name');
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