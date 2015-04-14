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
			$this->load->library('History_library');

			$adddata = array(
				'Buildings_idBuildings'	=> @$postdata['location'],
				'idAperture'			=> @$postdata['aperture'],
				'UserId'				=> $this->session->userdata('user_parent'),
			);

			if ($postdata['reviewer'] > 0)
				$adddata['Inspector']= $postdata['reviewer'];
			
			switch ($postdata['form_type'])
			{
				case 'add_inspection':
					$avail = $this->resources_model->get_inspection_by_aperture_id($postdata['aperture']);

					if (!empty($avail))
						$adddata['revision'] = $avail['revision'] + 1;

					$adddata['InspectionStatus'] = 'New';
					$iid = $this->resources_model->add_inspection($adddata);

					$this->history_library->saveInspections(array('line_id' => $iid, 'new_val' => json_encode($adddata), 'type' => 'add'));
				break;

				case 'edit_inspection':
					$this->history_library->saveInspections(array('line_id' => $postdata['idInspections'], 'new_val' => json_encode($adddata), 'type' => 'edit'));

					$this->resources_model->update_inspection($postdata['idInspections'], $adddata);
				break;
			}
		}

		$this->table->set_heading(
			'Id',
			'Location',
			'Door Id',
			array('data' => 'Start date', 'class' => 'not-mobile'),
			array('data' => 'Completion', 'class' => 'not-mobile'),
			array('data' => 'Reviewer'	, 'class' => 'not-mobile'),
			array('data' => 'Status'	, 'class' => 'not-mobile')
		);

		$inspections = $this->resources_model->get_user_inspections_by_parent($this->session->userdata('user_parent'));

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
				$item = (in_array($inspection['InspectionStatus'], array('In Progress', 'Complete'))) ? '<a href="javascript:;" onclick="confirmation_review(' . $inspection['aperture_id'] . ', ' . $inspection['id'] . ')">' . $inspection['barcode'] . '</a>' : $inspection['barcode'];
				$this->table->add_row($inspection['id'], $inspection['location_name'], $item, $inspection['StartDate'], $inspection['Completion'], $inspection['firstName'].' '.$inspection['lastName'], $inspection['InspectionStatus']);
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
		$footer['scripts'] .= '<script type="text/javascript" src="/js/jquery.jqplot.toImage.js"></script>';

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
			$output .= '<option value="' . $aperture['idDoors'] . '">' . $aperture['barcode'] . '</option>';
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

		$points 		= array('diamond', 'circle', 'square', 'x', 'plus', 'dash', 'filledDiamond', 'filledCircle', 'filledSquare');
		$buildins_root 	= $this->resources_model->get_user_buildings_root();

		$inspections 	= $this->resources_model->get_user_inspections_by_parent($this->session->userdata('user_parent'));

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
						$graphlabel[$inspection['building_id']] = isset($buildins_root[$inspection['building_id']]) ? $buildins_root[$inspection['building_id']]['name'] : 'Missing building';
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

	function getexport($type = '')
	{
		switch ($type) {
			case 'pdf':
				file_force_download(FCPATH . 'upload/' . $this->session->userdata('user_id') . '/pdf_export.pdf', 'pdf_export.pdf');
			break;
			
			case 'csv':
				$inspections = $this->resources_model->get_user_inspections_by_parent($this->session->userdata('user_parent'));

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

					$tbl = '"Id", "Location", "Door Id", "Start date", "Completion", "Reviewer", "Status"' . "\r\n";

					foreach ($inspections as $inspection)
					{
						$tbl .= '"' . @$inspection['id'] . '",';
						$tbl .= '"' . @$inspection['location_name'] . '",';
						$tbl .= '"' . @$inspection['barcode'] . '",';
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
						<th>Id</th>
						<th>Location</th>
						<th>Door Id</th>
						<th>Start date</th>
						<th>Completion</th>
						<th>Reviewer</th>
						<th>Status</th>
					</tr>
				</thead>';

		$inspections = $this->resources_model->get_user_inspections_by_parent($this->session->userdata('user_parent'));

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
				$tbl .= '<tr>';
				$tbl .= '<td>' . @$inspection['id'] . '</td>';
				$tbl .= '<td>' . @$inspection['location_name'] . '</td>';
				$tbl .= '<td>' . @$inspection['barcode'] . '</td>';
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

		// $file = FCPATH . 'upload';
		$file = FCPATH . 'upload/' . $this->session->userdata('user_id');
		
		if (!is_dir($file)) 
			mkdir($file);

		$file .= '/html_export.html';

		if (file_exists($file))
			unlink($file);

		$tbl = '<center><img src="' . $data_image . '"><br></center>';

		$tbl .= '<h2>Reviews</h2><br>
		<table cellspacing="0" cellpadding="1" border="1">
				<thead>
					<tr>
						<th>Id</th>
						<th>Location</th>
						<th>Door Id</th>
						<th>Start date</th>
						<th>Completion</th>
						<th>Reviewer</th>
						<th>Status</th>
					</tr>
				</thead>
				<tbody>';

		$inspections = $this->resources_model->get_user_inspections_by_parent($this->session->userdata('user_parent'));

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
				$tbl .= '<tr>';
				$tbl .= '<td>' . @$inspection['id'] . '</td>';
				$tbl .= '<td>' . @$inspection['location_name'] . '</td>';
				$tbl .= '<td>' . @$inspection['barcode'] . '</td>';
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
}

/* End of file dashboard.php */
/* Location: ./application/controllers/dashboard.php */