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
		$this->session->unset_userdata('filters_array');

		if ($postdata = $this->input->post())
		{
			$this->load->library('History_library');

			$adddata = array(
				// 'Buildings_idBuildings'	=> @$postdata['location'],
				'idAperture'			=> @$postdata['aperture'],
				'UserId'				=> $this->session->userdata('user_parent'),
			);

			if (isset($postdata['reviewer']) && $postdata['reviewer'] > 0)
				$adddata['Inspector']= $postdata['reviewer'];
			
			switch ($postdata['form_type'])
			{
				case 'send_email':
					send_mail($postdata['to'], $postdata['subject'], $postdata['body'], $postdata['from']);
					$header['msg'] = msg('success', 'Mail successfuly sent');
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
					$this->history_library->saveInspections(array('line_id' => $postdata['idInspections'], 'new_val' => json_encode($adddata), 'type' => 'edit'));

					$this->resources_model->update_inspection($postdata['idInspections'], $adddata);
					$header['msg'] = msg('success', 'Review successfuly updated');
				break;

				case 'customize_review':
					//filter reviews by Customize button
					$filters = array();

					if(isset($postdata['start_date']) && !empty($postdata['start_date']))
						$filters['start_date'] = $postdata['start_date'];

					if(isset($postdata['end_date']) && !empty($postdata['end_date']))
						$filters['end_date'] = $postdata['end_date'];

					if(isset($postdata['users']) && !empty($postdata['users']) && !in_array('all', $postdata['users']))
						$filters['users'] = $postdata['users'];

					if(isset($postdata['buildings']) && !empty($postdata['buildings']) && !in_array('all', $postdata['buildings']))
						$filters['buildings'] = $postdata['buildings'];

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

					foreach ($postdata as $key => $value)
					{
						if (strpos($key, 'Other') !== FALSE && strpos($key, 'tex') !== FALSE) //skip text field cause it save to 'Other' field
							continue;

						$val = 'YES';
						if (strpos($key, 'Other') !== FALSE && strpos($key, 'tex') === FALSE && isset($postdata[$key.'tex']) && strlen($postdata[$key.'tex']) > 0)
						{
							$val = $postdata[$key.'tex'];
						}

						$this->service_model->add_inspection_data($Pinspection_id, $value, $Puser, $val);
					}
					$new_dff = $this->resources_model->get_inspection_data($Pinspection_id);

					$this->history_library->saveDff(array('user_id' => $Puser, 'line_id' => '-', 'new_val' => json_encode($new_dff), 'cur_val' => json_encode($cur_dff)));

					$this->history_library->saveInspections(array('line_id' => $Pinspection_id, 'new_val' => json_encode(array('Inspector' => $this->session->userdata('user_id'))), 'type' => 'edit'));

					$this->resources_model->update_inspection($Pinspection_id, array('Inspector' => $this->session->userdata('user_id')));

					$header['msg'] = msg('success', 'Inspection data updated successfuly');
				break;

				case 'graph_click_data':
					$filters = $this->session->userdata('filters_array');
					
					if ($postdata['graphpid'] == 'compliance')
					{
						$filters['graph'] = array(
							'graphpid' 		=> $postdata['graphpid'],
							'graphpdata'	=> $postdata['graphpdata']
						);
					}
					
					$this->session->set_userdata('filters_array', $filters);
				break;
			}
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

				$loca[] = $userlocation[$inspection['Building']]['name'];
				
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

				$item = (in_array($inspection['InspectionStatus'], array('In Progress', 'Complete'))) ? '<a href="javascript:;" onclick="confirmation_review(' . $inspection['aperture_id'] . ', ' . $inspection['id'] . ')">' . $inspection['barcode'] . '</a>' : $inspection['barcode'];
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
		if (!$inspection_id = $this->input->post('id')) return '';
		if (!$inspection_state = $this->input->post('state')) return '';

		if ($inspection_state == 'Reinspect')
		{
			$adddata = $this->resources_model->get_inspection_info_by_inspection_id($inspection_id);
			unset($adddata['idInspections'], $adddata['name']);
			$adddata['InspectionStatus'] = $inspection_state;
			$adddata['Creator'] 		 = $this->session->userdata('user_id');
			$adddata['CreateDate'] 		 = data('Y-m-d');
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
						if ($inspection['status']<1)
							continue;

						$graphdata[$inspection['status']][$inspection['inspection_id']] = 1;
						$inspstats[$inspection['inspection_id']][$inspection['status']] = 1;
					}

					foreach ($inspstats as $insp_id => $insp)
					{
						if (count($insp) > 1)
						{
							if (isset($insp[1]))
								unset($graphdata[1][$insp_id]);
						}
					}

					$statuss = $this->config->item('door_state');

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

			case 'statuschart':
				$inspections 	= $this->_build_reviews_list();

				if (!empty($inspections))
				{
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
				}
			break;

			case 'companyreview':
				$inspections 	= $this->_build_reviews_list();
				if (!empty($inspections))
				{
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
				}
			break;

			case 'totalinmonth':
				$min = time()+60*60*24*30;
				$max = 0;

				$inspections 	= $this->_build_reviews_list();
				if (!empty($inspections))
				{
					foreach ($inspections as $inspection)
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
						$tbl .= '"' . @$inspection['location_name'] . '",';
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
				$tbl .= '<td>' . @$inspection['location_name'] . '</td>';
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
				$tbl .= '<td>' . @$inspection['location_name'] . '</td>';
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

		$filter_data = $this->session->userdata('filters_array');

		foreach ($inspections as $inspection)
		{
			//filter reviews by Customize button
			if(isset($filter_data['start_date']) && !empty($filter_data['start_date']))
				if (!empty($inspection['StartDate']) && strtotime($inspection['StartDate']) < strtotime($filter_data['start_date']))
					continue;

			if(isset($filter_data['end_date']) && !empty($filter_data['end_date']))
				if (!empty($inspection['Completion']) && strtotime($inspection['Completion']) < strtotime($filter_data['end_date']))
					continue;

			if(isset($filter_data['users']) && !empty($filter_data['users']) && !in_array('all', $filter_data['users']))
				if (!empty($inspection['Inspector']) && !in_array($inspection['Inspector'], $filter_data['users']))
					continue;

			if(isset($filter_data['buildings']) && !empty($filter_data['buildings']) && !in_array('all', $filter_data['buildings']))
				if (!empty($inspection['building_id']) && !in_array($inspection['building_id'], $filter_data['buildings']))
					continue;

			if (!$skip_graph && isset($filter_data['graph']))
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
					continue;

				if (count($inspstats) > 1 && isset($inspstats[1]))
					unset($inspstats[1]);
				
				if (!isset($inspstats[$statuss[$filter_data['graph']['graphpdata']]]))
					continue;
			}
			//end filter

			if (!$revision_no_filter)
				$output[$inspection['aperture_id']] = $inspection;
			else
			{
				if (!isset($output[$inspection['aperture_id']]))
					$output[$inspection['aperture_id']] = $inspection;
				elseif ($output[$inspection['aperture_id']]['revision'] < $inspection['revision'])
					$output[$inspection['aperture_id']] = $inspection;
			}
		}
	
		return $output;
	}
}

/* End of file dashboard.php */
/* Location: ./application/controllers/dashboard.php */