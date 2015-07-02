<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Dashboard extends CI_Controller {

	function __construct()
	{
		parent::__construct();
		verifyLogged();
		$this->load->model('resources_model');
		$this->load->library('table');
	}

	function index($clear = FALSE)
	{
		if ($clear)
			$this->session->unset_userdata('filters_array');

		$data['selected_graph'] = 'compliance'; //by default show compliance graph

		if ($postdata = $this->input->post())
		{
			$this->load->library('History_library');

			$adddata = array(
				'idAperture'			=> @$postdata['aperture'],
				'UserId'				=> $this->session->userdata('user_parent'),
			);

			if (isset($postdata['reviewer']) && $postdata['reviewer'] > 0)
				$adddata['Inspector']= $postdata['reviewer'];
			
			switch ($postdata['form_type'])
			{
				case 'send_email':
					if (!isset($postdata['to']) && empty($postdata['extra']))
						$header['msg'] = msg('warning', 'Please set at least one recipient!');
					else
					{
						if (!empty($postdata['extra']))
						{
							if (!isset($postdata['to']))
								$postdata['to'] = array();

							$extra = explode(',', $postdata['extra']);
							$extra = array_map('trim', $extra);
							foreach ($extra as $email)
								$postdata['to'][] = $email;
						}

						$filepath = FCPATH . 'upload/' . $this->session->userdata('user_id') . '/pdf_export.pdf';

						send_mail($postdata['to'], $postdata['subject'], $postdata['body'], $postdata['from'], $filepath);
						
						$header['msg'] = msg('success', 'Mail successfully sent');
					}
				break;

				case 'add_inspection':
					$avail = $this->resources_model->get_inspection_by_aperture_id($postdata['aperture']);

					if (!empty($avail))
						$adddata['revision'] = $avail['revision'] + 1;

					$adddata['Creator'] = $this->session->userdata('user_id');
					$adddata['InspectionStatus'] = 'New';
					$adddata['CreateDate'] = date('Y-m-d');
					$iid = $this->resources_model->add_inspection($adddata);

					$this->history_library->saveInspections(array('line_id' => $iid, 'new_val' => json_encode($adddata), 'type' => 'add'));
					$header['msg'] = msg('success', 'New review successfuly added');
				break;

				case 'edit_inspection':
					if ($adddata['idAperture'] > 0)
					{
						$this->history_library->saveInspections(array('line_id' => $postdata['idInspections'], 'new_val' => json_encode($adddata), 'type' => 'edit'));

						$this->resources_model->update_inspection($postdata['idInspections'], $adddata);
						$header['msg'] = msg('success', 'Review successfuly updated');
					}
				break;

				case 'customize_review':
					//filter reviews by Customize button
					$filters = array();
					
					// echo '<pre>';
					// print_r($postdata);die();
					
					if(isset($postdata['start_date']) && !empty($postdata['start_date']))
						$filters['start_date'] = $postdata['start_date'];

					if(isset($postdata['end_date']) && !empty($postdata['end_date']))
						$filters['end_date'] = $postdata['end_date'];

					if(isset($postdata['users']) && !empty($postdata['users']) && !in_array('all', $postdata['users']))
						$filters['users'] = $postdata['users'];

					if(isset($postdata['status']) && !empty($postdata['status']) && !in_array('all', $postdata['status']))
						$filters['status'] = $postdata['status'];

					if(isset($postdata['buildings']) && !empty($postdata['buildings']) && !in_array('all', $postdata['buildings']))
						$filters['buildings'] = $postdata['buildings'];
					
					if (isset($postdata['criteria']) && !empty($postdata['criteria']))
					{
						foreach ($postdata['criteria'] as $key => &$value)
						{
							if ($key == 'wall_Rating' && !in_array('all', $postdata['criteria'][$key]))
							{
								$wallratungs = $this->config->item('wall_rates');
								$wallratungs = array_flip($wallratungs);
								foreach ($value as $k => $rts)
									$value[$k] = $wallratungs[$rts];
							}
							if ($key == 'material' && !in_array('all', $postdata['criteria'][$key]))
							{
								$doormat = $this->config->item('door_matherial');
								$doormat = array_flip($doormat);
								foreach ($value as $k => $rts)
									$value[$k] = $doormat[$rts];
							}
							if ($key == 'smoke_Rating' && !in_array('all', $postdata['criteria'][$key]))
							{
								$smokerat = $this->config->item('rates_types');
								$smokerat = array_flip($smokerat);
								foreach ($value as $k => $rts)
									$value[$k] = $smokerat[$rts];
							}
							if ($key == 'rating' && !in_array('all', $postdata['criteria'][$key]))
							{
								$smokerat = $this->config->item('door_rating');
								$smokerat = array_flip($smokerat);
								foreach ($value as $k => $rts)
									$value[$k] = $smokerat[$rts];
							}

							if(!in_array('all', $value))
								$filters['criteria'][$key] = $value;
						}
					}

					if (isset($postdata['FrameReview']) && !empty($postdata['FrameReview']))
						foreach ($postdata['FrameReview'] as $key => &$value)
							$filters['FrameReview'][$key] = $value;

					if (isset($postdata['DoorReview']) && !empty($postdata['DoorReview']))
						foreach ($postdata['DoorReview'] as $key => &$value)
							$filters['DoorReview'][$key] = $value;

					if (isset($postdata['HardwareReview']) && !empty($postdata['HardwareReview']))
						foreach ($postdata['HardwareReview'] as $key => &$value)
							$filters['HardwareReview'][$key] = $value;

					if (isset($postdata['GlazingReview']) && !empty($postdata['GlazingReview']))
						foreach ($postdata['GlazingReview'] as $key => &$value)
							$filters['GlazingReview'][$key] = $value;

					if (isset($postdata['OperationalTestReview']) && !empty($postdata['OperationalTestReview']))
						foreach ($postdata['OperationalTestReview'] as $key => &$value)
							$filters['OperationalTestReview'][$key] = $value;

					if (isset($postdata['other']) && !empty($postdata['other']))
						foreach ($postdata['other'] as $key => &$value)
							if (!empty($value))
								$filters['other'][$key] = $value;

					$this->session->set_userdata('filters_array', $filters);
				break;

				case 'show_inspection':
					$this->load->model('service_model');

					$Puser = $this->session->userdata('user_id');

					$Pinspection_id = $postdata['inspection_id'];
					$Paperture_id = $postdata['aperture_id'];

					unset($postdata['form_type'], $postdata['inspection_id'], $postdata['aperture_id']);

					$cur_dff = $this->resources_model->get_inspection_data($Pinspection_id);

					$this->resources_model->delete_inspectiod_data($Pinspection_id);

					foreach ($postdata as $key => $field_id)
					{
						if (strpos($key, 'Other') !== FALSE && strpos($key, 'tex') !== FALSE) //skip text field cause it save to 'Other' field
							continue;

						$val = 'Yes';
						if (strpos($key, 'Other') !== FALSE && strpos($key, 'tex') === FALSE && isset($postdata[$key.'tex']) && strlen($postdata[$key.'tex']) > 0)
						{
							$val = $postdata[$key.'tex'];
						}

						$this->service_model->add_inspection_data($Pinspection_id, $field_id, $val);
					}
					$new_dff = $this->resources_model->get_inspection_data($Pinspection_id);

					$this->history_library->saveDff(array('user_id' => $Puser, 'line_id' => '-', 'new_val' => json_encode($new_dff), 'cur_val' => json_encode($cur_dff)));

					$this->history_library->saveInspections(array('line_id' => $Pinspection_id, 'new_val' => json_encode(array('Inspector' => $this->session->userdata('user_id'))), 'type' => 'edit'));

					$this->resources_model->update_inspection($Pinspection_id, array('Inspector' => $this->session->userdata('user_id')));

					$header['msg'] = msg('success', 'Inspection data updated successfuly');
				break;

				case 'graph_click_data':
					$filters = $this->session->userdata('filters_array');
					
					$data['selected_graph'] = $postdata['graphpid'];

					if (!empty($postdata['graphpid']) && !empty($postdata['graphdata']))
					{
						$filters['graph'] = array(
							'graphpid' 		=> $postdata['graphpid'],
							'graphdata'	=> ($postdata['graphpid'] == 'compliance') ? 'Compliant' : $postdata['graphdata']
						);
					}
					
					$this->session->set_userdata('filters_array', $filters);
				break;
			}
		}
		
		$this->load->model('user_model');
		$data['totalinspections'] = $this->resources_model->get_all_inspections_by_parent($this->session->userdata('user_parent'));
		$data['totalinspections'] = count($data['totalinspections']);
		
		$dbusers = $this->user_model->get_users_by_parent($this->session->userdata('user_parent'));
		$data['totalusers'] = $data['activeusers'] = 0;
		foreach ($dbusers as $user)
		{
			$data['totalusers']++;
			if ($user['deleted'] == 0)
				$data['activeusers']++;
		}

		$this->table->set_heading(
			array('data' => ''   , 'style' => 'display: none !important;'),
			'Door Id',
			'Location',
			array('data' => 'Create by'   , 'class' => 'not-mobile'),
			array('data' => 'Create date' , 'class' => 'not-mobile'),
			array('data' => 'Start date'  , 'class' => 'not-mobile'),
			array('data' => 'Completion' , 'class' => 'not-mobile'),
			array('data' => 'Reviewer'	 , 'class' => 'not-mobile'),
			array('data' => 'Status'	 , 'class' => 'not-mobile')
		);

		$inspections = $this->_build_reviews_list();

		$userlocation 	= $this->resources_model->get_user_buildings($this->session->userdata['user_parent']);;
		$buildings = array();
		foreach ($userlocation as $loc)
			$buildings[$loc['idBuildings']] = $loc;
		$userlocation = $buildings;

		if (!empty($inspections))
		{
			foreach ($inspections as $inspection)
			{
				$loca = array();

				$loca[] = @$userlocation[$inspection['Building']]['name'];
				
				if ($inspection['Floor'] > 0 && isset($userlocation[$inspection['Floor']]['name']))
					$loca[] = $userlocation[$inspection['Floor']]['name'];
				if ($inspection['Wing'] > 0 && isset($userlocation[$inspection['Wing']]['name']))
					$loca[] = $userlocation[$inspection['Wing']]['name'];
				if ($inspection['Area'] > 0 && isset($userlocation[$inspection['Area']]['name']))
					$loca[] = $userlocation[$inspection['Area']]['name'];
				if ($inspection['Level'] > 0 && isset($userlocation[$inspection['Level']]['name']))
					$loca[] = $userlocation[$inspection['Level']]['name'];
				
				if (!empty($loca))
					$loca =  implode(' ', $loca);
				else
					$loca = '';

				$item = ($inspection['wall_Rating'] > 0 && $inspection['smoke_Rating'] > 0 && $inspection['material'] > 0 && $inspection['rating'] > 0) ? '<a href="javascript:;" onclick="confirmation_review(' . $inspection['aperture_id'] . ', ' . $inspection['id'] . ')">' . $inspection['barcode'] . '</a>' : $inspection['barcode'];
				// $item = (in_array($inspection['InspectionStatus'], array('In Progress', 'Complete'))) ? '<a href="javascript:;" onclick="confirmation_review(' . $inspection['aperture_id'] . ', ' . $inspection['id'] . ')">' . $inspection['barcode'] . '</a>' : $inspection['barcode'];
				// $item = '<a href="javascript:;" onclick="confirmation_review(' . $inspection['aperture_id'] . ', ' . $inspection['id'] . ')">' . $inspection['barcode'] . '</a>';
				// $item = $inspection['barcode'];
				$cell = array('data' => $inspection['id'], 'style' => 'display: none !important;');
				$this->table->add_row($cell, $item, $loca, $inspection['CreatorfirstName'].' '.$inspection['CreatorlastName'], $inspection['CreateDate'], $inspection['StartDate'], $inspection['Completion'], $inspection['firstName'].' '.$inspection['lastName'], $inspection['InspectionStatus']);
			}
		}

		$tmpl = array ( 'table_open'  => '<table class="table table-striped table-hover table-bordered table-responsive table-condensed" width="100%">' );
		$this->table->set_template($tmpl); 
		
		$data['result_table'] = $this->table->generate(); 
		
		//datatable
		$header['page_title'] = 'CLIENT DASHBOARD';
		$header['styles']  = addDataTable('css');
		$footer['scripts'] = addDataTable('js');

		//nice select
		$header['styles']  .= '<link rel="stylesheet" type="text/css" href="/js/bootstrap-select/css/bootstrap-select.css">';
		$footer['scripts'] .= '<script type="text/javascript" src="/js/bootstrap-select/bootstrap-select.js"></script>';

		//datepicker
		$header['styles']  .= '<link href="/js/bootstrap-datepicker/datepicker.css" rel="stylesheet">';
		$footer['scripts'] .= '<script type="text/javascript" src="/js/bootstrap-datepicker/bootstrap-datepicker.js"></script>';
		
		//jqplot
		$header['styles']  .= '<link rel="stylesheet" type="text/css" href="/js/jqplot/jquery.jqplot.css" />';
		$footer['scripts'] .= '<script type="text/javascript" src="/js/jqplot/jquery.jqplot.min.js"></script>';
		$footer['scripts'] .= '<!--[if lt IE 9]><script type="text/javascript" src="/js/jqplot/excanvas.js"></script><![endif]-->';
		$footer['scripts'] .= '<script type="text/javascript" src="/js/jqplot/plugins/jqplot.barRenderer.min.js"></script>';
		$footer['scripts'] .= '<script type="text/javascript" src="/js/jqplot/plugins/jqplot.canvasAxisTickRenderer.min.js"></script>';
		$footer['scripts'] .= '<script type="text/javascript" src="/js/jqplot/plugins/jqplot.canvasTextRenderer.min.js"></script>';
		$footer['scripts'] .= '<script type="text/javascript" src="/js/jqplot/plugins/jqplot.categoryAxisRenderer.min.js"></script>';
		$footer['scripts'] .= '<script type="text/javascript" src="/js/jqplot/plugins/jqplot.cursor.min.js"></script>';
		$footer['scripts'] .= '<script type="text/javascript" src="/js/jqplot/plugins/jqplot.dateAxisRenderer.min.js"></script>';
		$footer['scripts'] .= '<script type="text/javascript" src="/js/jqplot/plugins/jqplot.logAxisRenderer.min.js"></script>';
		$footer['scripts'] .= '<script type="text/javascript" src="/js/jqplot/plugins/jqplot.ohlcRenderer.min.js"></script>';
		$footer['scripts'] .= '<script type="text/javascript" src="/js/jqplot/plugins/jqplot.pieRenderer.min.js"></script>';
		$footer['scripts'] .= '<script type="text/javascript" src="/js/jquery.jqplot.toImage.js"></script>';

		$this->load->view('header', $header);
		$this->load->view('dashboard', $data);
		$this->load->view('footer', $footer);
	}

	function ajax_get_apertures()
	{
		if (!$building_id = $this->input->post('locid')) return '';
		
		$user_apertures = $this->resources_model->get_user_apertures_without_review($building_id);
		
		$output = '<select name="aperture" class="selectpicker fullwidth" data-live-search="true">';
		$output .= '<option value="0">Choose door</option>';
		foreach ($user_apertures as $aperture)
		{
			$output .= '<option value="' . $aperture['idDoors'] . '">' . $aperture['barcode'] . '</option>';
		}

		echo $output . '</select>';
	}

	function ajax_update_inspection_state()
	{
		$this->load->library('History_library');
		if (!$inspection_id = $this->input->post('id')) return '';
		if (!$inspection_state = $this->input->post('state')) return '';

		if ($inspection_state == 'Reinspect')
		{
			$inspectiondata = $this->resources_model->get_inspection_info_by_inspection_id($inspection_id);
			$adddata['idAperture'] 		 = $inspectiondata['idAperture'];
			$adddata['InspectionStatus'] = $inspection_state;
			$adddata['Creator'] 		 = $this->session->userdata('user_id');
			$adddata['CreateDate'] 		 = date('Y-m-d');
			$adddata['revision']		 = $inspectiondata['revision']+1;
			$adddata['UserId']			 = $inspectiondata['UserId'];

			$iid = $this->resources_model->add_inspection($adddata);

			$this->history_library->saveInspections(array('line_id' => $iid, 'new_val' => json_encode($adddata), 'type' => 'add'));
			return $iid;
		}

		return $this->resources_model->update_inspection_state($inspection_id, $inspection_state);
	}

	function ajax_review_delete()
	{
		if (!$review_id = $this->input->post('id')) return print('empty id');
		if (!$this->resources_model->delete_review_by_id($review_id))  return print('can\'t delete review by id');
		return print('done');
	}

	function ajax_make_graph()
	{
		if (!$graph_id = $this->input->post('graph_id')) return '';

		$points = array('diamond', 'circle', 'square', 'x', 'plus', 'dash', 'filledDiamond', 'filledCircle', 'filledSquare');
		$output = '';

		$graphdata = array();
		switch ($graph_id)
		{
			case 'compliance':
				$inspections 	= $this->_build_reviews_list(FALSE, TRUE);
				$insp = array();
				foreach ($inspections as $inspection)
				{
					$insp[] = $inspection['id'];
				}
				$inspections = $this->resources_model->get_inspections_statuses($this->session->userdata('user_parent'), $insp);

				if (!empty($inspections))
				{
					foreach ($inspections as $inspection)
					{
						if ($inspection['status'] < 1)
							continue;

						if ($inspection['status'] == 1)
							$graphdata[1][$inspection['inspection_id']] = 1; //Compliant
						else
							$graphdata[9][$inspection['inspection_id']] = 1; //Non-Compliant
					}


					$total = 0;

					foreach ($graphdata[9] as $insp_id => $noncomp)
					{
						unset($graphdata[1][$insp_id]);
						$total++;
					}

					foreach ($insp as $inspect)
					{
						if (!isset($graphdata[1][$inspect]) && !isset($graphdata[9][$inspect]))
							$graphdata[1][$inspect] = 1;
					}

					foreach ($graphdata[1] as $comp)
						$total++;

					$tempdata = array();
					$datalabels = array();
					$comtproc = round(count($graphdata[1])/$total*100);
					$tempdata[]   = '[\'Compliant Doors\', ' . count($graphdata[1]) . ']';
					$datalabels[] = "'" . $comtproc . '% (' . count($graphdata[1]) . ")'";
					
					$tempdata[]   = '[\'Non-Compliant Doors\', ' . count($graphdata[9]) . ']';
					$datalabels[] = "'" . (100 - $comtproc) . '% (' . count($graphdata[9]) . ")'";

					$output = "[[" . implode(', ', $tempdata) . "]], {
						seriesDefaults: {
							// Make this a pie chart.
							renderer: jQuery.jqplot.PieRenderer, 
							rendererOptions: {
							  sliceMargin: 10,
							  showDataLabels: true,
							  dataLabels: [" . implode(', ', $datalabels) . "]
							}
						  }, 
						  legend: { show:true, location: 'e' }
						}";
				}
			break;

			case 'compliance2':
				$inspections 	= $this->_build_reviews_list(FALSE, TRUE);
				$insp = array();
				foreach ($inspections as $inspection)
				{
					$insp[] = $inspection['id'];
				}

				$inspections = $this->resources_model->get_inspections_statuses($this->session->userdata('user_parent'), $insp);

				if (!empty($inspections))
				{
					foreach ($inspections as $inspection)
					{
						if ($inspection['status']<1)
							continue;

						$graphdata[$inspection['status']][$inspection['inspection_id']] = 1;
					}

					$statuss = $this->config->item('door_state');
					
					unset($statuss[1], $graphdata[1]); //remove inspection with Comliant only

					$total = 0;
					foreach ($graphdata as $key => $val)
						$total += count($val);

					$tempdata = array();
					$datalabels = array();
					foreach ($graphdata as $key => $val)
					{
						if (empty($val))
							continue;

						$tempdata[]   = '[\'' . $statuss[$key] . '\', ' . count($val) . ']';
						$datalabels[] = "'" . round(count($val)/$total*100) . '% (' . count($val) . ")'";
					}

					$output = "[[" . implode(', ', $tempdata) . "]], {
						seriesDefaults: {
							// Make this a pie chart.
							renderer: jQuery.jqplot.PieRenderer, 
							rendererOptions: {
							  sliceMargin: 10,
							  showDataLabels: true,
							  dataLabels: [" . implode(', ', $datalabels) . "]
							}
						  }, 
						  legend: { show:true, location: 'e' }
						}";
				}
			break;

			case 'inventorychart':
			case 'inventorychart1':
			case 'inventorychart2':
			case 'inventorychart3':
			case 'inventorychart4':

				$inspections 	= $this->_build_reviews_list(FALSE, TRUE);
				
				$apertures = array();
				foreach ($inspections as $inspection)
					$apertures[] = $inspection['aperture_id'];

				$apertdata = $this->resources_model->get_apertures_info_by_aperture_ids($apertures);

				if (!empty($apertdata))
				{
					$doorrating = $this->config->item('door_rating');
					$wallrating = $this->config->item('wall_rates');
					$smoke = array(1 => 'Smoke', 2 => 'Fire');
					$materials = $this->config->item('door_matherial');

					foreach ($apertdata as $doorinfo)
					{
						switch ($graph_id)
						{
							case 'inventorychart':
							case 'inventorychart1':
								if ($doorinfo['rating'] == 0)
									continue;
								$graphdata[$doorinfo['rating']] = isset($graphdata[$doorinfo['rating']]) ? ++$graphdata[$doorinfo['rating']] : 1;		
							break;
							case 'inventorychart2':
								if ($doorinfo['wall_Rating'] == 0)
									continue;
								$graphdata[$doorinfo['wall_Rating']] = isset($graphdata[$doorinfo['wall_Rating']]) ? ++$graphdata[$doorinfo['wall_Rating']] : 1;		
							break;
							case 'inventorychart3':
								if ($doorinfo['smoke_Rating'] == 0)
									continue;
								$graphdata[$doorinfo['smoke_Rating']] = isset($graphdata[$doorinfo['smoke_Rating']]) ? ++$graphdata[$doorinfo['smoke_Rating']] : 1;		
							break;
							case 'inventorychart4':
								if ($doorinfo['material'] == 0)
									continue;
								$graphdata[$doorinfo['material']] = isset($graphdata[$doorinfo['material']]) ? ++$graphdata[$doorinfo['material']] : 1;		
							break;
						}
					}

					switch ($graph_id)
					{
						case 'inventorychart':
						case 'inventorychart1':
							for ($i=1; $i < 7; $i++)
								if (!isset($graphdata[$i]))
									$graphdata[$i] = 0;
						break;
						case 'inventorychart2':
							for ($i=1; $i < 5; $i++)
								if (!isset($graphdata[$i]))
									$graphdata[$i] = 0;
						break;
						case 'inventorychart3':
							for ($i=1; $i < 3; $i++)
								if (!isset($graphdata[$i]))
									$graphdata[$i] = 0;
						break;
						case 'inventorychart4':
							for ($i=1; $i < 7; $i++)
								if (!isset($graphdata[$i]))
									$graphdata[$i] = 0;
						break;
					}

					ksort($graphdata);

					foreach ($graphdata as $key => $val)
					{
						// $text = ($graph_id == 'inventorychart' or $graph_id == 'inventorychart1') ? $key . ' Minute' : $key;
						switch ($graph_id) {
							case 'inventorychart':
							case 'inventorychart1':
								$tempdata[]   = '[\'' . $doorrating[$key] . ' Minute' . '\', ' . $val . ']';
							break;
							case 'inventorychart2':
								$tempdata[]   = '[\'' . $wallrating[$key] . '\', ' . $val . ']';
							break;
							case 'inventorychart3':
								$tempdata[]   = '[\'' . $smoke[$key] . '\', ' . $val . ']';
							break;
							case 'inventorychart4':
								$tempdata[]   = '[\'' . $materials[$key] . '\', ' . $val . ']';
							break;
						}
						
						$datalabels[] = "'" . round(count($val)/count($apertdata)*100) . '% (' . count($val) . ")'";
					}

					$output = "[[" . implode(', ', $tempdata) . "]], {
						seriesDefaults:{
							renderer:$.jqplot.BarRenderer,
							rendererOptions: {
								varyBarColor: true
							},
							pointLabels: {show:true}
						},
						axes: {
						  xaxis: {
							renderer: $.jqplot.CategoryAxisRenderer,
							tickOptions: {
							  labelPosition: 'middle'
							}
						  },
						  yaxis: {
							autoscale:true,
							tickRenderer: $.jqplot.CanvasAxisTickRenderer,
							tickOptions: {
							  labelPosition: 'start'
							}
						  }
						},
					}";
				}
			break;

			case 'ahjreport':
			case 'ahjreport1':
				$query['type'] = 'ahjreport1';
				$query['inspections'] = $this->resources_model->get_inspections_by_complete_date($query['type']);
				$inspdata = $this->_get_report_cache($query); //take or make and take data for report using params above

				if (!empty($inspdata))
				{
					foreach ($inspdata as $inspection)
					{
						foreach ($inspection as  $YearMonth => $value)
						{
							if (!empty($value['value']))
							{
								$colorcodes = json_decode($value['value']);
								foreach ($colorcodes as $code)
									$graphdata[$code][date('F', strtotime($YearMonth))] = isset($graphdata[$code][date('F', strtotime($YearMonth))]) ? ++$graphdata[$code][date('F', strtotime($YearMonth))] : 1;
							}
						}
					}

					$ticks = array();
					for ($i=1; $i <= date('m'); $i++)
						$ticks[] = '"' . date('F', strtotime(date('Y') . '-' . $i . '-1' )) . '"';

					$codes = $this->config->item('door_state');
					$labels = array();
					foreach ($codes as $code) {
						$labels[] = '{label: \'' . $code . '\'}';
					}

					$tempdata = array();
					foreach ($codes as $cod)
					{
						$monthdata = array();
						for ($i=1; $i <= date('m'); $i++)
							$monthdata[] = isset($graphdata[$cod][date('F', strtotime(date('Y') . '-' . $i . '-1' ))]) ? $graphdata[$cod][date('F', strtotime(date('Y') . '-' . $i . '-1' ))] : 0;

						$tempdata[]   = '[' . implode(',', $monthdata) . ']';
					}

					$output = "[" . implode(', ', $tempdata) . "], {
						legend: {
							show: true, 
								location: 'e',
								placement: 'outsideGrid'
						},
						seriesDefaults: {
							renderer: $.jqplot.BarRenderer,
							rendererOptions: {
							   barPadding: 2
							}
						},
						series: [".implode(',', $labels)."],
						axes: {
							xaxis: {
								renderer: $.jqplot.CategoryAxisRenderer,
								tickRenderer: $.jqplot.CanvasAxisTickRenderer,
								labelRenderer: $.jqplot.CanvasAxisLabelRenderer,
								ticks: [".implode(',', $ticks)."],
							}
						}
					}";
				}
			break;
			case 'ahjreport2':
				$query['type'] = 'ahjreport2';
				$query['inspections'] = $this->resources_model->get_inspections_by_complete_date($query['type']);
				$inspdata = $this->_get_report_cache($query); //take or make and take data for report using params above

				if (!empty($inspdata))
				{
					foreach ($inspdata as $inspection)
					{
						foreach ($inspection as  $YearMonth => $value) {
							if (!empty($value['value']))
							{
								$colorcodes = json_decode($value['value']);
								foreach ($colorcodes as $code)
									$graphdata[$code][$YearMonth] = isset($graphdata[$code][$YearMonth]) ? ++$graphdata[$code][$YearMonth] : 1;
							}
						}
					}

					$ticks = array();
					for ($i=1; $i <= 4; $i++)
						$ticks[] = '"' .'Q' . $i . ' - ' . (date('y')-1) . '"';
					for ($i=1; $i <= ceil(date('m')/3); $i++)
						$ticks[] = '"' .'Q' . $i . ' - ' . date('y') . '"';

					$codes = $this->config->item('door_state');
					$labels = array();
					foreach ($codes as $code) {
						$labels[] = '{label: \'' . $code . '\'}';
					}

					$tempdata = array();
					foreach ($codes as $cod)
					{
						$monthdata = array();
						for ($i=1; $i <= 4; $i++)
							$monthdata[] = isset($graphdata[$cod]['Q' . $i . ' - ' . (date('Y')-1)]) ? $graphdata[$cod]['Q' . $i . ' - ' . (date('Y')-1)] : 0;
						for ($i=1; $i <= ceil(date('m')/3); $i++)
							$monthdata[] = isset($graphdata[$cod]['Q' . $i . ' - ' . date('Y')]) ? $graphdata[$cod]['Q' . $i . ' - ' . date('Y')] : 0;
						$tempdata[]   = '[' . implode(',', $monthdata) . ']';
					}

					$output = "[" . implode(', ', $tempdata) . "], {
						legend: {
							show: true, 
								location: 'e',
								placement: 'outsideGrid'
						},
						seriesDefaults: {
							renderer: $.jqplot.BarRenderer,
							rendererOptions: {
							   barPadding: 2
							}
						},
						series: [".implode(',', $labels)."],
						axes: {
							xaxis: {
								renderer: $.jqplot.CategoryAxisRenderer,
								tickRenderer: $.jqplot.CanvasAxisTickRenderer,
								labelRenderer: $.jqplot.CanvasAxisLabelRenderer,
								ticks: [".implode(',', $ticks)."],
							}
						}
					}";
				}
			break;
			case 'ahjreport3':
				$query['type'] = 'ahjreport3';
				$query['inspections'] = $this->resources_model->get_inspections_by_complete_date($query['type']);
				$inspdata = $this->_get_report_cache($query); //take or make and take data for report using params above

				if (!empty($inspdata))
				{
					foreach ($inspdata as $inspection)
					{
						$minyear = date('Y');
						foreach ($inspection as  $YearMonth => $value) {
							// echo $YearMonth . "\r\n";
							$minyear = ($YearMonth < $minyear) ? $YearMonth : $minyear;
							// $minyear--;
							if (!empty($value['value']))
							{
								$colorcodes = json_decode($value['value']);
								foreach ($colorcodes as $code)
									$graphdata[$code][$YearMonth] = isset($graphdata[$code][$YearMonth]) ? ++$graphdata[$code][$YearMonth] : 1;
							}
						}
					}

					$ticks = array();

					for ($i=$minyear; $i <= date('Y'); $i++)
						$ticks[] = '"' .$i . '"';

					$codes = $this->config->item('door_state');
					$labels = array();
					foreach ($codes as $code) {
						$labels[] = '{label: \'' . $code . '\'}';
					}

					$tempdata = array();
					foreach ($codes as $cod)
					{
						$monthdata = array();
						for ($i=$minyear; $i <= date('Y'); $i++)
							$monthdata[] = isset($graphdata[$cod][$i]) ? $graphdata[$cod][$i] : 0;

						$tempdata[]   = '[' . implode(',', $monthdata) . ']';
					}

					$output = "[" . implode(', ', $tempdata) . "], {
						legend: {
							show: true, 
								location: 'e',
								placement: 'outsideGrid'
						},
						seriesDefaults: {
							renderer: $.jqplot.BarRenderer,
							rendererOptions: {
							   barPadding: 2
							}
						},
						series: [".implode(',', $labels)."],
						axes: {
							xaxis: {
								renderer: $.jqplot.CategoryAxisRenderer,
								tickRenderer: $.jqplot.CanvasAxisTickRenderer,
								labelRenderer: $.jqplot.CanvasAxisLabelRenderer,
								ticks: [".implode(',', $ticks)."],
							}
						}
					}";
				}
			break;

			case 'activityreport':
			case 'activityreport1':
			case 'activityreport2':
			case 'activityreport3':
				$query['type'] = 'activityreport';
				$query['inspections'] = $this->resources_model->get_inspections_by_complete_date($query['type']);
				$inspdata = $this->_get_report_cache($query); //take or make and take data for report using params above

				if (!empty($inspdata))
				{
					$this->load->model('user_model');
					$dbusers = $this->user_model->get_users_by_parent($this->session->userdata('user_parent'));
					
					foreach ($inspdata as $inspection)
					{
						foreach ($inspection as  $YearMonth => $value) {
							if (!empty($value['value']))
							{
								$cdate = date('Y-m-d', $YearMonth);
								$graphformat = '%v';
								
								if ($graph_id == 'activityreport2')
								{
									$cdate = date('Y-m', $YearMonth);
									$graphformat = '%b-%Y';
								}
								if ($graph_id == 'activityreport3')
								{
									$cdate = date('Y', $YearMonth);
									$graphformat = '%Y';
								}

								$users = json_decode($value['value']);
								foreach ($users as $user)
								{
									$curuser = $dbusers[$user]['firstName'] .' ' . $dbusers[$user]['lastName'];
									$graphdata[$curuser][$cdate] = isset($graphdata[$curuser][$cdate]) ? ++$graphdata[$curuser][$cdate] : 1;
								}
							}
						}
					}

					$tempdata = array();
					$total = array();

					foreach ($graphdata as $user => $datas)
					{
						foreach ($datas as $dat => $value)
						{
							if (!isset($total[$user]))
								$total[$user] = $value;
							else
								$total[$user] += $value;
							$temp[]   = '[\'' . $dat . '\', ' . $value . ']';
						}

						$tempdata[] = '[' . implode(', ', $temp) . ']';
						
						$tempseries[] = "{label: '{$user}'}";
					}

					$templegend = array();
					foreach ($total as $user => $value)
						$templegend[] = $user . ' (' . $value . ')';

					$output = "[" . implode(', ', $tempdata) . "], {
						legend: {show: true, labels:['" . implode("', '", $templegend) . "']},
					  	axes: {xaxis:{renderer:$.jqplot.DateAxisRenderer,tickOptions:{formatString:'$graphformat'}}},
					  	series: [".implode(',', $tempseries)."],
					  	cursor:{show: true, zoom: true, showTooltip: true,followMouse: true} 
					}";
				}
			break;

			default:
			die('default');
			break;
		}
		
		echo $output;
	}

	function getexport($type = '')
	{
		switch ($type) {
			case 'pdf':
				file_force_download(FCPATH . 'upload/' . $this->session->userdata('user_id') . '/pdf_export.pdf', 'pdf_export.pdf');
			break;
			
			case 'csv':
				$inspections = $this->_build_reviews_list();

				if (!empty($inspections))
				{
					$tbl = '"Door Id", "Location", "Create by", "Create date", "Start date", "Completion", "Reviewer", "Status"' . "\r\n";

					foreach ($inspections as $inspection)
					{
						$tbl .= '"' . @$inspection['barcode'] . '",';
						$tbl .= '"' . @$inspection['location'] . '",';
						$tbl .= '"' . @$inspection['CreatorfirstName'] . ' ' . @$inspection['CreatorlastName'] . '",';
						$tbl .= '"' . @$inspection['CreateDate'] . '",';
						$tbl .= '"' . @$inspection['StartDate'] . '",';
						$tbl .= '"' . @$inspection['Completion'] . '",';
						$tbl .= '"' . @$inspection['firstName'].' ' . @$inspection['lastName'] . '",';
						$tbl .= '"' . @$inspection['InspectionStatus'] . '"';
						$tbl .= "\r\n";
					}
				}

				data_force_download($tbl, 'csv_export.csv');
			break;

			case 'html':
				file_force_download(FCPATH . 'upload/' . $this->session->userdata('user_id') . '/html_export.html', 'html_export.html');
			break;
			
			default:
			break;
		}
		if ($this->session->flashdata('refferer') && strpos($this->session->flashdata('refferer'), 'ajax') !== FALSE)
			redirect($this->session->flashdata('refferer'));
		redirect('user/profile/');
	}

	function ajax_export_to_pdf()
	{
		$data_image = @$this->input->post('img');
		$data_image = str_replace('data:image/png;base64,', '', $data_image);

		// $file = FCPATH . 'upload';
		$file = FCPATH . 'upload/' . $this->session->userdata('user_id');
		
		if (!is_dir($file)) 
			mkdir($file);

		$file .= '/pdf_export.pdf';

		if (file_exists($file))
			unlink($file);

		require_once(APPPATH . 'third_party/tcpdf/tcpdf.php');

		// create new PDF document
		$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

		// set document information
		$pdf->SetCreator(PDF_CREATOR);
		$pdf->SetAuthor('FDT');
		$pdf->SetTitle('List of Reviews');

		// remove default header/footer
		$pdf->setPrintHeader(false);
		$pdf->setPrintFooter(false);

		// set default monospaced font
		$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

		// set margins
		$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);

		// set auto page breaks
		$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

		// set image scale factor
		$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

		// set font
		// $pdf->SetFont('times', 'BI', 20);

		// add a page
		$pdf->AddPage();

		//*******************TABLE AND GRAPH
		// Image method signature:
		// Image($file, $x='', $y='', $w=0, $h=0, $type='', $link='', $align='', $resize=false, $dpi=300, $palign='', $ismask=false, $imgmask=false, $border=0, $fitbox=false, $hidden=false, $fitonpage=false)

		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

		// Example of Image from data stream ('PHP rules')
		if (!empty($data_image))
		{
			$imgdata = base64_decode($data_image);

			// The '@' character is used to indicate that follows an image data stream and not an image file name
			$pdf->Image('@'.$imgdata, '', '', 0, 0, 'PNG', '', 'B', FALSE, 300, 'L', FALSE, FALSE, 0, FALSE, FALSE, TRUE);
		}

		$pdf->Ln();

		$tbl = '<h2>Reviews</h2><br>
		<table cellspacing="0" cellpadding="1" border="1">
				<thead>
					<tr>
						<th>Door Id</th>
						<th>Location</th>
						<th>Create by</th>
						<th>Create date</th>
						<th>Start date</th>
						<th>Completion</th>
						<th>Reviewer</th>
						<th>Status</th>
					</tr>
				</thead>';

		$inspections = $this->_build_reviews_list();

		if (!empty($inspections))
		{
			foreach ($inspections as $inspection)
			{
				
				$tbl .= '<tr>';
				$tbl .= '<td>' . @$inspection['barcode'] . '</td>';
				$tbl .= '<td>' . @$inspection['location'] . '</td>';
				$tbl .= '<td>' . @$inspection['CreatorfirstName'] . ' ' . @$inspection['CreatorlastName'] . '</td>';
				$tbl .= '<td>' . @$inspection['CreateDate'] . '</td>';
				$tbl .= '<td>' . @$inspection['StartDate'] . '</td>';
				$tbl .= '<td>' . @$inspection['Completion'] . '</td>';
				$tbl .= '<td>' . @$inspection['firstName'].' ' . @$inspection['lastName'] . '</td>';
				$tbl .= '<td>' . @$inspection['InspectionStatus'] . '</td>';
				$tbl .= '</tr>';
			}
		}

		$tbl .= '</table>';

		$pdf->writeHTML($tbl, true, false, true, false, '');

		$pdf->lastPage();

		$pdf->Output($file, 'F');

		echo 'done';
		exit;
	}

	function ajax_export_to_html()
	{
		$data_image = @$this->input->post('img');

		$file = FCPATH . 'upload/' . $this->session->userdata('user_id');

		if (!is_dir($file)) 
			mkdir($file);
		
		chmod($file, 0777);
		
		$file .= '/html_export.html';

		if (file_exists($file))
			unlink($file);

		$tbl = '<center><img src="' . $data_image . '"><br></center>';

		$tbl .= '<h2>Reviews</h2><br>
		<table cellspacing="0" cellpadding="1" border="1">
				<thead>
					<tr>
						<th>Door Id</th>
						<th>Location</th>
						<th>Create by</th>
						<th>Create date</th>
						<th>Start date</th>
						<th>Completion</th>
						<th>Reviewer</th>
						<th>Status</th>
					</tr>
				</thead>
				<tbody>';
		$inspections = $this->_build_reviews_list();

		if (!empty($inspections))
		{

			foreach ($inspections as $inspection)
			{
				$tbl .= '<tr>';
				$tbl .= '<td>' . @$inspection['barcode'] . '</td>';
				$tbl .= '<td>' . @$inspection['location'] . '</td>';
				$tbl .= '<td>' . @$inspection['CreatorfirstName'] . ' ' . @$inspection['CreatorlastName'] . '</td>';
				$tbl .= '<td>' . @$inspection['CreateDate'] . '</td>';
				$tbl .= '<td>' . @$inspection['StartDate'] . '</td>';
				$tbl .= '<td>' . @$inspection['Completion'] . '</td>';
				$tbl .= '<td>' . @$inspection['firstName'].' ' . @$inspection['lastName'] . '</td>';
				$tbl .= '<td>' . @$inspection['InspectionStatus'] . '</td>';
				$tbl .= '</tr>';
			}
		}

		$tbl .= '</tbody></table>';

		file_put_contents($file, $tbl);

		echo 'done';
		exit;
	}

	function _build_reviews_list($revision_no_filter = FALSE, $skip_graph = FALSE)
	{
		$output = array();

		$inspections = $this->resources_model->get_user_inspections_by_parent($this->session->userdata('user_parent'));

		$userlocation 	= $this->resources_model->get_user_buildings($this->session->userdata['user_parent']);;
		$buildings = array();
		foreach ($userlocation as $loc)
			$buildings[$loc['idBuildings']] = $loc;
		$userlocation = $buildings;

		$filter_data = $this->session->userdata('filters_array');
// echo '<pre>';
// print_r($filter_data);
		foreach ($inspections as $inspection)
		{
			unset($inspdata, $dff);

			$inspdata = $this->resources_model->get_aperture_info_by_inspection_id($inspection['id']);
			$inspection['wall_Rating'] 	= $inspdata['wall_Rating'];
			$inspection['smoke_Rating'] = $inspdata['smoke_Rating'];
			$inspection['material'] 	= $inspdata['material'];
			$inspection['rating'] 		= $inspdata['rating'];
			//filter reviews by Customize button
			if(isset($filter_data['start_date']) && !empty($filter_data['start_date']))
				if (!empty($inspection['CreateDate']) && strtotime($inspection['CreateDate']) < strtotime($filter_data['start_date']))
					continue;

			if(isset($filter_data['end_date']) && !empty($filter_data['end_date']))
				if (!empty($inspection['Completion']) && strtotime($inspection['Completion']) < strtotime($filter_data['end_date']))
					continue;

			if(isset($filter_data['users']) && !empty($filter_data['users']) && !in_array('all', $filter_data['users']))
				if ((!empty($inspection['Inspector']) or !empty($inspection['Creator'])) && (!in_array($inspection['Inspector'], $filter_data['users']) && !in_array($inspection['Creator'], $filter_data['users'])))
					continue;

			if(isset($filter_data['status']) && !empty($filter_data['status']) && !in_array('all', $filter_data['status']))
				if (!empty($inspection['InspectionStatus']) && !in_array($inspection['InspectionStatus'], $filter_data['status']))
					continue;

			if(isset($filter_data['buildings']) && !empty($filter_data['buildings']) && !in_array('all', $filter_data['buildings']))
				if (!empty($inspection['Building']) && !in_array($inspection['Building'], $filter_data['buildings']))
					continue;

			if(isset($filter_data['criteria']) && !empty($filter_data['criteria']))
			{
				if (!isset($inspdata) or empty($inspdata))
					$inspdata = $this->resources_model->get_aperture_info_by_inspection_id($inspection['id']);

				foreach ($filter_data['criteria'] as $aperture_param => $values)
				{
					if (empty($inspdata[$aperture_param]) or !in_array($inspdata[$aperture_param], $values))
					continue 2;
				}
			}


			if(isset($filter_data['FrameReview']) && !empty($filter_data['FrameReview']))
			{
				if (!isset($dff))
					$dff = $this->resources_model->get_door_form_fields($inspection['id']);
				// echo '<pre>';
				// print_r($filter_data);
				// echo '<pre>';
				// print_r($dff);
				foreach ($filter_data['FrameReview'] as $fieldname => $fieldid)
				{
					if (!isset($dff[$fieldid]))
						continue 2;
					if (preg_match('#^Other\-#si', $fieldname) && (!isset($filter_data['other'][$fieldname . 'tex']) or $filter_data['other'][$fieldname . 'tex'] != $fieldid)) 
						continue 2;
						
				}
			}

			if(isset($filter_data['DoorReview']) && !empty($filter_data['DoorReview']))
			{
				if (!isset($dff))
					$dff = $this->resources_model->get_door_form_fields($inspection['id']);

				foreach ($filter_data['DoorReview'] as $fieldname => $fieldid)
				{
					if (!isset($dff[$fieldid]))
						continue 2;
					if (preg_match('#^Other\-#si', $fieldname) && (!isset($filter_data['other'][$fieldname . 'tex']) or $filter_data['other'][$fieldname . 'tex'] != $fieldid)) 
						continue 2;
						
				}
			}

			if(isset($filter_data['HardwareReview']) && !empty($filter_data['HardwareReview']))
			{
				if (!isset($dff))
					$dff = $this->resources_model->get_door_form_fields($inspection['id']);

				foreach ($filter_data['HardwareReview'] as $fieldname => $fieldid)
				{
					if (!isset($dff[$fieldid]))
						continue 2;
					if (preg_match('#^Other\-#si', $fieldname) && (!isset($filter_data['other'][$fieldname . 'tex']) or $filter_data['other'][$fieldname . 'tex'] != $fieldid)) 
						continue 2;
						
				}
			}

			if(isset($filter_data['GlazingReview']) && !empty($filter_data['GlazingReview']))
			{
				if (!isset($dff))
					$dff = $this->resources_model->get_door_form_fields($inspection['id']);

				foreach ($filter_data['GlazingReview'] as $fieldname => $fieldid)
				{
					if (!isset($dff[$fieldid]))
						continue 2;
					if (preg_match('#^Other\-#si', $fieldname) && (!isset($filter_data['other'][$fieldname . 'tex']) or $filter_data['other'][$fieldname . 'tex'] != $fieldid)) 
						continue 2;
						
				}
			}

			if(isset($filter_data['OperationalTestReview']) && !empty($filter_data['OperationalTestReview']))
			{
				if (!isset($dff))
					$dff = $this->resources_model->get_door_form_fields($inspection['id']);

				foreach ($filter_data['OperationalTestReview'] as $fieldname => $fieldid)
				{
					if (!isset($dff[$fieldid]))
						continue 2;
					if (preg_match('#^Other\-#si', $fieldname) && (!isset($filter_data['other'][$fieldname . 'tex']) or $filter_data['other'][$fieldname . 'tex'] != $fieldid)) 
						continue 2;
						
				}
			}


			if (!$skip_graph && isset($filter_data['graph']) && ($filter_data['graph']['graphpid'] == 'compliance' or $filter_data['graph']['graphpid'] == 'compliance2'))
			{
				$ins = $this->resources_model->get_inspections_statuses($this->session->userdata('user_parent'), $inspection['id']);

				$statuss = $this->config->item('door_state');
				$statuss = array_flip($statuss);

				if (empty($ins))
					continue;

				$inspstats = array();
				foreach ($ins as $in)
				{
					if ($in['status']<1)
						continue;

					$inspstats[$in['status']] = 1;
				}

				if (empty($inspstats))
					$inspstats[1] = 1;

				if (count($inspstats) > 1 && isset($inspstats[1]))
					unset($inspstats[1]);

				if ($filter_data['graph']['graphdata'] != 'Non-Compliant Doors' && !isset($inspstats[$statuss[$filter_data['graph']['graphdata']]]))
					continue;
			}
			if (!$skip_graph && isset($filter_data['graph']) && in_array($filter_data['graph']['graphpid'], array('inventorychart', 'inventorychart1', 'inventorychart2', 'inventorychart3', 'inventorychart4')))
			{
				if (!isset($inspdata) or empty($inspdata))
					$inspdata = $this->resources_model->get_aperture_info_by_inspection_id($inspection['id']);
				switch ($filter_data['graph']['graphpid']) {
					case 'inventorychart':
					case 'inventorychart1':
						if ($inspdata['rating'] != $filter_data['graph']['graphdata'])
							continue 2;
					break;
					case 'inventorychart2':
						if ($inspdata['wall_Rating'] != $filter_data['graph']['graphdata'])
							continue 2;
					break;
					case 'inventorychart3':
						if ($inspdata['smoke_Rating'] != $filter_data['graph']['graphdata'])
							continue 2;
					break;
					case 'inventorychart4':
						if ($inspdata['material'] != $filter_data['graph']['graphdata'])
							continue 2;
					break;
				}
			}
			//end filter

			$loca = array();

			$loca[] = @$userlocation[$inspection['Building']]['name'];
			
			if ($inspection['Floor'] > 0 && isset($userlocation[$inspection['Floor']]['name']))
				$loca[] = $userlocation[$inspection['Floor']]['name'];
			if ($inspection['Wing'] > 0 && isset($userlocation[$inspection['Wing']]['name']))
				$loca[] = $userlocation[$inspection['Wing']]['name'];
			if ($inspection['Area'] > 0 && isset($userlocation[$inspection['Area']]['name']))
				$loca[] = $userlocation[$inspection['Area']]['name'];
			if ($inspection['Level'] > 0 && isset($userlocation[$inspection['Level']]['name']))
				$loca[] = $userlocation[$inspection['Level']]['name'];
			
			if (!empty($loca))
				$inspection['location'] =  implode(' ', $loca);
			else
				$inspection['location'] = '';

			if ($revision_no_filter)
				$output[$inspection['aperture_id']] = $inspection;
			else
			{
				if (!isset($output[$inspection['aperture_id']]))
					$output[$inspection['aperture_id']] = $inspection;
				elseif ($output[$inspection['aperture_id']]['revision'] < $inspection['revision'])
					$output[$inspection['aperture_id']] = $inspection;
			}
		}/*die();*/
		return $output;
	}

	function _get_report_cache($params)
	{
		$this->load->model('history_model');
		
		$cached_data = array();

		switch ($params['type'])
		{
			case 'ahjreport1':
				$cached_data = $this->resources_model->get_cached_report_data($params['type'],$params['inspections']);

				foreach ($params['inspections'] as $inspec_id)
				{
					for ($m=1; $m <= date('m'); $m++)
					{ 
						$m = (strlen($m) < 2) ? '0' . $m : $m;
						if (!isset($cached_data[$inspec_id][date('Y') . '-' . $m])) //CALC AND MAKE CACHE IF ABSENT
						{
							$histdata = $this->history_model->get_data_by_date_and_type('dff', $inspec_id, strtotime(date('Y') . '-' . $m . '-' . idate('t',strtotime(date('Y') . '-' . $m)) . ' 23:59:59'));

							if (empty($histdata))
								$val = '';
							else
							{
								$doorinfo = $this->resources_model->get_aperture_info_by_inspection_id($inspec_id);		//get DIO by inspection id
								$cc = $this->resources_model->get_aperture_issues_with_status($doorinfo);				//get CC values
								
								$val = array();
								foreach ($histdata as $element)															//concat all colorcoded values
								{
									$fieldinfo = json_decode($element['new_val']);
									if (empty($fieldinfo->value) && isset($fieldinfo->FormFields_idFormFields))
										unset($val[$fieldinfo->FormFields_idFormFields]);
									elseif (isset($fieldinfo->FormFields_idFormFields) && isset($cc[$fieldinfo->FormFields_idFormFields]))
										$val[$fieldinfo->FormFields_idFormFields]=$cc[$fieldinfo->FormFields_idFormFields];
								}

								$val = array_flip($val);																//if has noncompliant value kill complant
								if (count($val) > 1 && isset($val['Compliant']))
									unset($val['Compliant']);
								$val = json_encode(array_keys($val));
							}
							$cached_data[$inspec_id][date('Y') . '-' . $m] = $this->resources_model->add_cache_data($params['type'], $inspec_id, date('Y') . '-' . $m, $val); //save to DB and add to array
						}
					}
					
				}
			break;
			case 'ahjreport2':
				$cached_data = $this->resources_model->get_cached_report_data($params['type'],$params['inspections']);

				foreach ($params['inspections'] as $inspec_id)
				{
					for ($m=1; $m <= 4; $m++)
					{ 
						if (!isset($cached_data[$inspec_id]['Q' . $m . ' - ' . (date('Y')-1)])) //CALC AND MAKE CACHE IF ABSENT
						{
							$histdata = $this->history_model->get_data_by_date_and_type('dff', $inspec_id, strtotime((date('Y')-1) . '-' . $m*3 . '-' . idate('t',strtotime((date('Y')-1) . '-' . $m*3)) . ' 23:59:59'));
							if (empty($histdata))
								$val = '';
							else
							{
								$doorinfo = $this->resources_model->get_aperture_info_by_inspection_id($inspec_id);		//get DIO by inspection id
								$cc = $this->resources_model->get_aperture_issues_with_status($doorinfo);				//get CC values
								
								$val = array();
								foreach ($histdata as $element)															//concat all colorcoded values
								{
									$fieldinfo = json_decode($element['new_val']);
									if (empty($fieldinfo->value))
										unset($val[$fieldinfo->FormFields_idFormFields]);
									elseif (isset($cc[$fieldinfo->FormFields_idFormFields]))
										$val[$fieldinfo->FormFields_idFormFields]=$cc[$fieldinfo->FormFields_idFormFields];
								}

								$val = array_flip($val);																//if has noncompliant value kill complant
								if (count($val) > 1 && isset($val['Compliant']))
									unset($val['Compliant']);
								$val = json_encode(array_keys($val));
							}
							$cached_data[$inspec_id]['Q' . $m . ' - ' . (date('Y')-1)] = $this->resources_model->add_cache_data($params['type'], $inspec_id, 'Q' . $m . ' - ' . (date('Y')-1), $val); //save to DB and add to array
						}
					}
					for ($m=1; $m <= ceil(date('m')/3); $m++)
					{ 
						if (!isset($cached_data[$inspec_id]['Q' . $m . ' - ' . date('Y')])) //CALC AND MAKE CACHE IF ABSENT
						{
							$histdata = $this->history_model->get_data_by_date_and_type('dff', $inspec_id, strtotime(date('Y') . '-' . $m*3 . '-' . idate('t',strtotime(date('Y') . '-' . $m*3)) . ' 23:59:59'));
							if (empty($histdata))
								$val = '';
							else
							{
								$doorinfo = $this->resources_model->get_aperture_info_by_inspection_id($inspec_id);		//get DIO by inspection id
								$cc = $this->resources_model->get_aperture_issues_with_status($doorinfo);				//get CC values
								
								$val = array();
								foreach ($histdata as $element)															//concat all colorcoded values
								{
									$fieldinfo = json_decode($element['new_val']);
									if (empty($fieldinfo->value) && isset($fieldinfo->FormFields_idFormFields))
										unset($val[$fieldinfo->FormFields_idFormFields]);
									elseif (isset($cc[$fieldinfo->FormFields_idFormFields]))
										$val[$fieldinfo->FormFields_idFormFields]=$cc[$fieldinfo->FormFields_idFormFields];
								}

								$val = array_flip($val);																//if has noncompliant value kill complant
								if (count($val) > 1 && isset($val['Compliant']))
									unset($val['Compliant']);
								$val = json_encode(array_keys($val));
							}
							$cached_data[$inspec_id]['Q' . $m . ' - ' . date('Y')] = $this->resources_model->add_cache_data($params['type'], $inspec_id, 'Q' . $m . ' - ' . date('Y'), $val); //save to DB and add to array
						}
					}
					
				}
			break;
			case 'ahjreport3':
				$cached_data = $this->resources_model->get_cached_report_data($params['type'],$params['inspections']);

				foreach ($params['inspections'] as $inspec_id)
				{
					for ($m = 2014; $m <= date('Y'); $m++)
					{ 
						if (!isset($cached_data[$inspec_id][$m])) //CALC AND MAKE CACHE IF ABSENT
						{
							$histdata = $this->history_model->get_data_by_date_and_type('dff', $inspec_id, strtotime($m . '-12-' . idate('t',strtotime($m . '-12')) . ' 23:59:59'));
							if (empty($histdata))
								$val = '';
							else
							{
								$doorinfo = $this->resources_model->get_aperture_info_by_inspection_id($inspec_id);		//get DIO by inspection id
								$cc = $this->resources_model->get_aperture_issues_with_status($doorinfo);				//get CC values
								
								$val = array();
								foreach ($histdata as $element)															//concat all colorcoded values
								{
									$fieldinfo = json_decode($element['new_val']);
									if (empty($fieldinfo->value) && isset($fieldinfo->FormFields_idFormFields))
										unset($val[$fieldinfo->FormFields_idFormFields]);
									elseif (isset($fieldinfo->FormFields_idFormFields) && isset($cc[$fieldinfo->FormFields_idFormFields]))
										$val[$fieldinfo->FormFields_idFormFields]=$cc[$fieldinfo->FormFields_idFormFields];
								}

								$val = array_flip($val);																//if has noncompliant value kill complant
								if (count($val) > 1 && isset($val['Compliant']))
									unset($val['Compliant']);
								$val = json_encode(array_keys($val));
							}
							$cached_data[$inspec_id][$m] = $this->resources_model->add_cache_data($params['type'], $inspec_id, $m, $val); //save to DB and add to array
						}
					}
				}
			break;
			case 'activityreport':
				//here save in cache only days with completitions
				$cached_data = $this->resources_model->get_cached_report_data($params['type'],$params['inspections']);

				foreach ($params['inspections'] as $inspec_id)
				{
					$minz = !empty($cached_data[$inspec_id]) ? max(array_keys($cached_data[$inspec_id])) : 1405886400;//1430427600; //2015-05-01 or last record
					$day = 86400;
					for ($i = $minz+86400; $i < date('U'); $i = $i+$day) //loop +1 day
					{ 
						$nowdate = date('Y-m-d', $i);
						$histdata = $this->history_model->get_data_by_date_and_type('inspections', $inspec_id, $nowdate);
						
						if (!empty($histdata))
						{
							$val = array();
							foreach ($histdata as $record)
								$val[$record['user_id']] = 1;
							$val = json_encode(array_keys($val));

							$cached_data[$inspec_id][$i] = $this->resources_model->add_cache_data($params['type'], $inspec_id, strtotime($nowdate . ' 00:00:00'), $val); //save to DB and add to array
						}
					}
				}
			break;
		}
		return $cached_data;
	}

}

/* End of file dashboard.php */
/* Location: ./application/controllers/dashboard.php */