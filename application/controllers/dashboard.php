<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Dashboard extends CI_Controller {

	function __construct()
	{
		parent::__construct();
		verifyLogged();
		$this->load->model('resources_model');
		$this->load->library('table');
	}

	function index()
	{
		// printdbg(array());
		if ($postdata = $this->input->post())
		{
			
			$adddata = array(
				'Buildings_idBuildings'	=> @$postdata['location'],
				'idAperture'			=> @$postdata['aperture'],
				'InspectionStatus'		=> @$postdata['state'],
				'UserId'				=> $this->session->userdata('user_parent'),
			);

			if (!empty($postdata['start_date']))
				$adddata['StartDate'] = date('Y-m-d', strtotime($postdata['start_date']));

			if (!empty($postdata['completion_date']))
				$adddata['Completion'] = date('Y-m-d', strtotime($postdata['completion_date']));

			if ($postdata['reviewer'] > 0)
				$adddata['Inspector']= $postdata['reviewer'];
			
			switch ($postdata['form_type'])
			{
				case 'add_inspection':
					$avail = $this->resources_model->get_inspection_by_aperture_id($postdata['aperture']);

					if (!empty($avail))
						$adddata['revision'] = $avail['revision'] + 1;

					$this->resources_model->add_inspection($adddata);
				break;
				case 'edit_inspection':
					$this->resources_model->update_inspection($postdata['idInspections'], $adddata);
				break;
			}
		}

		$this->table->set_heading(
			'Id',
			'Location',
			'Door',
			array('data' => 'Start date', 'class' => 'not-mobile'),
			array('data' => 'Completion', 'class' => 'not-mobile'),
			array('data' => 'Reviewer'	, 'class' => 'not-mobile'),
			array('data' => 'Status'	, 'class' => 'not-mobile')
		);

		
		if (has_permission('Allow view all reviews'))
			$inspections = $this->resources_model->get_user_inspections();
		elseif (has_permission('Allow view users review'))
			$inspections = $this->resources_model->get_user_inspections_by_parent($this->session->userdata('user_parent'));
		else
			$inspections = $this->resources_model->get_user_inspections_by_user_id($this->session->userdata('user_id'));

		if (!empty($inspections))
		{
			$output = array();
			foreach ($inspections as $inspection)
			{
				if (!isset($output[$inspection['aperture_id']]))
					$output[$inspection['aperture_id']] = $inspection;
				elseif ($output[$inspection['aperture_id']]['revision'] < $inspection['revision'])
					$output[$inspection['aperture_id']] = $inspection;
			}
		
			$inspections = $output;

			foreach ($inspections as $inspection)
			{
				$this->table->add_row($inspection['id'], $inspection['location_name'], $inspection['aperture_name'], $inspection['StartDate'],$inspection['Completion'], $inspection['firstName'].' '.$inspection['lastName'], $inspection['InspectionStatus']);
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
		
		//highcharts 
		// $footer['scripts'] .= '<script src="http://code.highcharts.com/highcharts.js"></script>';
		// $footer['scripts'] .= '<script src="http://code.highcharts.com/modules/exporting.js"></script>';

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

		$this->load->view('header', $header);
		$this->load->view('dashboard', $data);
		$this->load->view('footer', $footer);
	}

	function ajax_get_apertures()
	{
		if (!$building_id = $this->input->post('locid')) return '';
		
		$user_apertures = $this->resources_model->get_user_apertures($building_id);
		
		$output = '<select name="aperture" class="selectpicker fullwidth" data-live-search="true">';
		$output .= '<option value="0">Choose door</option>';
		foreach ($user_apertures as $aperture)
		{
			$output .= '<option value="' . $aperture['idDoors'] . '">' . $aperture['name'] . '</option>';
		}

		echo $output . '</select>';
	}

	function ajax_update_inspection_state()
	{
		if (!$inspection_id = $this->input->post('id')) return '';
		if (!$inspection_state = $this->input->post('state')) return '';

		if ($inspection_state == 'Reinspect')
		{
			$adddata = $this->resources_model->get_inspection_info_by_inspection_id($inspection_id);
			unset($adddata['idInspections'], $adddata['name']);
			$adddata['InspectionStatus'] = $inspection_state;
			$adddata['revision']++;
			return $this->resources_model->add_inspection($adddata);
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
		$buildins_root = $this->resources_model->get_user_buildings_root();

		if (has_permission('Allow view all reviews'))
			$inspections = $this->resources_model->get_user_inspections();
		elseif (has_permission('Allow view users review'))
			$inspections = $this->resources_model->get_user_inspections_by_parent($this->session->userdata('user_parent'));
		else
			$inspections = $this->resources_model->get_user_inspections_by_user_id($this->session->userdata('user_id'));

		if (!empty($buildins_root) && !empty($inspections)) {
			
			$output = array(); //make working array
			
			$revisions = $inspections;

			foreach ($inspections as $inspection)
			{
				if (!isset($output[$inspection['aperture_id']]))
					$output[$inspection['aperture_id']] = $inspection;
				elseif ($output[$inspection['aperture_id']]['revision'] < $inspection['revision'])
					$output[$inspection['aperture_id']] = $inspection;
			}
			$inspections = $output;

			$graphdata = array();
			switch ($graph_id) {
				case 'startdate':
					$min = time()+60*60*24*30;
					$max = 0;

					foreach ($inspections as $inspection)
					{
						if (empty($inspection['StartDate']) or empty($inspection['firstName']) or empty($inspection['lastName']) )
							continue;

						if ($min > strtotime($inspection['StartDate']))
							$min = strtotime($inspection['StartDate']);
						
						if ($max < strtotime($inspection['StartDate']))
							$max = strtotime($inspection['StartDate']);

						$graphdata[$inspection['building_id']][] = "'{$inspection['StartDate']}', '{$inspection['firstName']} {$inspection['lastName']}'";
						$graphlabel[$inspection['building_id']] = $buildins_root[$inspection['building_id']]['name'];
					}

					$size = 3;
					foreach ($graphlabel as $key => $building_name) {
						$tempdata[]   = '[[' . implode('], [', $graphdata[$key]) . ']]';
						$tempseries[] = "{
					    	showLine:false,
					    	label: '{$building_name}',
					    	size: {$size},
					    	markerOptions:{style:'{$points[rand(1,count($points)-1)]}'}
				    	}";
				    	$size++;
					}
					$title = "";
					$output = "[" . implode(', ', $tempdata) . "], {
					  	animate: !$.jqplot.use_excanvas,
					    axes:{
					        xaxis:{
					            renderer:$.jqplot.DateAxisRenderer,
					            syncTicks: true,
					            min: '" . date('Y-m-d', $min-60*60*24*3) . "',
					            max: '" . date('Y-m-d', $max+60*60*24*3) . "'
					        },
					        yaxis:{
					            renderer:$.jqplot.CategoryAxisRenderer
					        }
					    },
					    series:[" . implode(',', $tempseries) . "],
					    legend: {
					    	show: true,
							location: 's',
							showLabels: true,
							showSwatches: true,
							placement: 'outsideGrid',
							renderer: $.jqplot.TableLegendRenderer,
							preDraw: true
					    },
					    cursor:{
				            show: true, 
				            zoom: true
				        } 
					}";
				break;

				case 'completiondate':
					$min = time()+60*60*24*30;
					$max = 0;

					foreach ($inspections as $inspection)
					{
						if (empty($inspection['Completion']) or empty($inspection['firstName']) or empty($inspection['lastName']) )
							continue;

						if ($min > strtotime($inspection['Completion']))
							$min = strtotime($inspection['Completion']);
						
						if ($max < strtotime($inspection['Completion']))
							$max = strtotime($inspection['Completion']);

						$graphdata[$inspection['building_id']][] = "'{$inspection['Completion']}', '{$inspection['firstName']} {$inspection['lastName']}'";
						$graphlabel[$inspection['building_id']] = $buildins_root[$inspection['building_id']]['name'];
					}

					$size = 3;
					foreach ($graphlabel as $key => $building_name) {
						$tempdata[]   = '[[' . implode('], [', $graphdata[$key]) . ']]';
						$tempseries[] = "{
					    	showLine:false,
					    	label: '{$building_name}',
					    	size: {$size},
					    	markerOptions:{style:'{$points[rand(1,count($points)-1)]}'}
				    	}";
				    	$size++;
					}
					$title = "";
					$output = "[" . implode(', ', $tempdata) . "], {
					  	animate: !$.jqplot.use_excanvas,
					    axes:{
					        xaxis:{
					            renderer:$.jqplot.DateAxisRenderer,
					            syncTicks: true,
					            min: '" . date('Y-m-d', $min-60*60*24*3) . "',
					            max: '" . date('Y-m-d', $max+60*60*24*3) . "'
					        },
					        yaxis:{
					            renderer:$.jqplot.CategoryAxisRenderer
					        }
					    },
					    series:[" . implode(',', $tempseries) . "],
					    legend: {
					    	show: true,
							location: 's',
							showLabels: true,
							showSwatches: true,
							placement: 'outsideGrid',
							renderer: $.jqplot.TableLegendRenderer,
							preDraw: true
					    },
					    cursor:{
				            show: true, 
				            zoom: true
				        } 
					}";
				break;

				case 'statuschart':
					foreach ($inspections as $inspection)
						$graphdata[$inspection['InspectionStatus']] = isset($graphdata[$inspection['InspectionStatus']]) ? ++$graphdata[$inspection['InspectionStatus']] : 1;

					foreach ($graphdata as $key => $val)
						$tempdata[]   = '[\'' . $key . '\', ' . $val . ']';

					$output = "[[" . implode(', ', $tempdata) . "]], {
						seriesDefaults: {
					        // Make this a pie chart.
					        renderer: jQuery.jqplot.PieRenderer, 
					        rendererOptions: {
							  sliceMargin: 10,
					          showDataLabels: true
					        }
					      }, 
					      legend: { show:true, location: 'e' }
						}";
				break;

				case 'companyreview':
					foreach ($inspections as $inspection)
						$graphdata[$inspection['location_name']] = isset($graphdata[$inspection['location_name']]) ? ++$graphdata[$inspection['location_name']] : 1;

					foreach ($graphdata as $key => $val)
						$tempdata[]   = '[\'' . $key . '\', ' . $val . ']';

					$output = "[[" . implode(', ', $tempdata) . "]], {
						seriesDefaults: {
					        // Make this a pie chart.
					        renderer: jQuery.jqplot.PieRenderer, 
					        rendererOptions: {
							  sliceMargin: 10,
					          showDataLabels: true
					        }
					      }, 
					      legend: { show:true, location: 'e' }
						}";
				break;

				case 'totalinmonth':
					$min = time()+60*60*24*30;
					$max = 0;
					foreach ($revisions as $inspection)
					{
						if (empty($inspection['StartDate']) or empty($inspection['firstName']) or empty($inspection['lastName']) )
							continue;

						if ($min > strtotime($inspection['StartDate']))
							$min = strtotime($inspection['StartDate']);
						
						if ($max < strtotime($inspection['StartDate']))
							$max = strtotime($inspection['StartDate']);

						if ($inspection['revision']==0)
							$graphdata[$inspection['InspectionStatus']][date('Y-m-d', strtotime($inspection['StartDate']))] = isset($graphdata[$inspection['InspectionStatus']][date('Y-m-d', strtotime($inspection['StartDate']))]) ? ++$graphdata[$inspection['InspectionStatus']][date('Y-m-d', strtotime($inspection['StartDate']))] : 1;
						else
							$graphdata['Reinspect'][date('Y-m-d', strtotime($inspection['StartDate']))] = isset($graphdata[$inspection['InspectionStatus']][date('Y-m-d', strtotime($inspection['StartDate']))]) ? ++$graphdata[$inspection['InspectionStatus']][date('Y-m-d', strtotime($inspection['StartDate']))] : 1;
					}

					$size = 3;
					$tempdata = '';
					foreach ($graphdata as $status => $datas) {
						foreach ($datas as $dat => $value)
							$temp[]   = '[\'' . $dat . '\', ' . $value . ']';

						$tempdata[] = '[' . implode(', ', $temp) . ']';
						
						$tempseries[] = "{label: '{$status}'}";
				    	$size++;
					}

					$output = "[" . implode(', ', $tempdata) . "], {
					  	animate: !$.jqplot.use_excanvas,
					  	seriesDefaults:{
				            renderer:$.jqplot.BarRenderer,
				            rendererOptions: {fillToZero: true}
				        },
					    axes:{
					        xaxis:{
					            renderer:$.jqplot.DateAxisRenderer,
					            syncTicks: true,
					            min: '" . date('Y-m-d', $min-60*60*24*3) . "',
					            max: '" . date('Y-m-d', $max+60*60*24*3) . "'
					        },
					        yaxis:{
					            renderer:$.jqplot.CategoryAxisRenderer,
					            pad: 1.05
					        }
					    },
					    series:[" . implode(',', $tempseries) . "],
					    legend: {
					    	show: true,
							location: 's',
							showLabels: true,
							showSwatches: true,
							placement: 'outsideGrid',
							renderer: $.jqplot.TableLegendRenderer,
							preDraw: true
					    },
					    cursor:{
				            show: true, 
				            zoom: true
				        }
					}";
				break;

				default:
				die('default');
				break;
			}
		}
		
	echo $output;
	}
}

/* End of file dashboard.php */
/* Location: ./application/controllers/dashboard.php */