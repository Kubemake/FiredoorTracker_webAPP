<div class="container">
	<div class="row mb-10">
		<div class="col-xs-3 graphacceptor" id="compliance">
			<img width="95%" src="/images/compliance.jpg" />
		</div>
		<div class="col-xs-3 graphacceptor" id="inventorychart">
			<img width="95%" src="/images/inventory.jpg" />
		</div>
		<div class="col-xs-3 graphacceptor" id="ahjreport">
			<img width="95%" src="/images/ahjreport.jpg" />
		</div>
		<div class="col-xs-3 graphacceptor" id="activityreport">
			<img width="95%" src="/images/activityreport.jpg" />
		</div>
	</div>
	<div class="row">
		<div class="col-xs-9 col-md-10" id="chartwrapper">
			<div id="charttitle"></div>
			<div id="inventorytab" style="display:none;">
				<ul class="nav nav-pills nav-justified">
					<li><a href="javascript:;" id="inventorychart1"  class="graphacceptor">Door Rating</a></li>
					<li><a href="javascript:;" id="inventorychart2" class="graphacceptor">Wall Rating</a></li>
					<li><a href="javascript:;" id="inventorychart3" class="graphacceptor">Door Type</a></li>
					<li><a href="javascript:;" id="inventorychart4" class="graphacceptor">Door Material</a></li>
				</ul>
			</div>
			<div id="ahjtab" style="display:none;">
				<ul class="nav nav-pills nav-justified">
					<li><a href="javascript:;" id="ahjreport1"  class="graphacceptor">By Month</a></li>
					<li><a href="javascript:;" id="ahjreport2"  class="graphacceptor">By Quarter</a></li>
					<li><a href="javascript:;" id="ahjreport3"  class="graphacceptor">By Year</a></li>
				</ul>
			</div>
			<div id="activitytab" style="display:none;">
				<ul class="nav nav-pills nav-justified">
					<li><a href="javascript:;" id="activityreport1"  class="graphacceptor">By Day</a></li>
					<li><a href="javascript:;" id="activityreport2"  class="graphacceptor">By Month</a></li>
					<li><a href="javascript:;" id="activityreport3"  class="graphacceptor">By Year</a></li>
				</ul>
			</div>

			<div id="chartacceptor"></div>
		</div>
		<div class="col-xs-2">
			<a class="thumbnail" href="javascript:;" id="emailing"><img alt="Send e-mail" title="Send e-mail" data-src="holder.js/60x60" style="height: 60px; width: 60px; display: block;" src="/images/email.png"></img></a>
			<a class="thumbnail" href="/dashboard/getexport/csv" id="xlsexport"><img alt="Export in CSV" title="Export in CSV" data-src="holder.js/60x60" style="height: 60px; width: 60px; display: block;" src="/images/csv.png"></img></a>
			<a class="thumbnail" href="javascript:;" id="pdfexport"><img alt="Export in PDF" title="Export in PDF" data-src="holder.js/60x60" style="height: 60px; width: 60px; display: block;" src="/images/pdf.png"></img></a>
			<a class="thumbnail" href="javascript:;" id="htmlexport"><img alt="Export in HTML" title="Export in HTML" data-src="holder.js/60x60" style="height: 60px; width: 60px; display: block;" src="/images/html.png"></img></a>
			<a class="thumbnail" href="javascript:;" id="customizing"><img alt="Customize" title="Customize" data-src="holder.js/60x60" style="height: 60px; width: 60px; display: block;" src="/images/customize.png"></img></a>
		</div>
	</div>
</div>

<?php if (has_permission('Allow modify review')): ?>
<div class="container">
	<div class="row">
		<div class="text-right col-md-push-6 col-md-6 col-sm-12">
			<div class="btn-group">
				<a href="javascript:;" onclick="button_add_action();return false;" class="btn btn-default button-add">Add</a>
				<a href="javascript:;" onclick="button_edit_action();return false;" class="btn btn-default button-edit">Edit</a>
				<?php if (has_permission('Allow delete review')): ?><a href="javascript:;" onclick="button_delete_action();return false;" class="btn btn-default button-delete">Delete</a><?php endif; ?>
				<a href="javascript:;" onclick="button_reinspect_action();return false;" class="btn btn-default button-reinspect">Reinspect</a>
			</div>
		</div>
	</div>
</div>
<?php endif; ?>

<?=@$result_table?>

<form method="POST" id="graphform">
	<input type="hidden" id="graphdata" name="graphdata" value="">
	<input type="hidden" id="graphpid" name="graphpid" value="">
	<input type="hidden" name="form_type" value="graph_click_data">
</form>


<script type="text/javascript">
	function confirmation_review(door_id, insp_id)
	{
		$('#modalacceptor').empty().load("/ajax/ajax_load_modal",{page: 'show_inspection_modal', door_id: door_id, insp_id: insp_id},function(){$('#ShowInspectionModal').modal({show: true})});
	}

	$('#emailing').on('click', function() {
		picture = jqplotToImg($('#chartacceptor'));
		$.ajax({
			url: "/dashboard/ajax_export_to_pdf",
			type: "POST",
			data: {img: picture},
			success: function(result) {
				if (result == 'done') {
					$('#modalacceptor').empty().load("/ajax/ajax_load_modal",{page: 'send_email_modal'},function(){$('#SendEmailModal').modal({show: true})});
				};
			}
		})
	});

	$('#pdfexport').on('click', function() {
		picture = jqplotToImg($('#chartacceptor'));
		$.ajax({
			url: "/dashboard/ajax_export_to_pdf",
			type: "POST",
			data: {img: picture},
			success: function(result) {
				// console.log(result);
				if (result == 'done') {
					window.location = "/dashboard/getexport/pdf";
				};
			}
		})
	});

	$('#htmlexport').on('click', function() {
		picture = jqplotToImg($('#chartacceptor'));
		$.ajax({
			url: "/dashboard/ajax_export_to_html",
			type: "POST",
			data: {img: picture},
			success: function(result) {
				console.log(result);
				if (result == 'done') {
					window.location = "/dashboard/getexport/html";
				};
			}
		})
	});

	$('#customizing').on('click', function() {
		$('#modalacceptor').empty().load("/ajax/ajax_load_modal",{page: 'customize_review_list_modal'},function(){$('#CustomizeReviewListModal').modal({show: true})});
		
	});
</script>

<?php if (has_permission('Allow modify review')): ?>
<script type="text/javascript">

	$('td').dblclick(function(){ //edit row on double clicking
		$(this).click();
		button_edit_action();
	});

	function button_add_action()
	{
		$('#modalacceptor').empty().load("/ajax/ajax_load_modal",{page: 'add_inspection_modal'},function(){$('#AddInspectionModal').modal({show: true})});
		
	}

	function button_edit_action()
	{
		var oTT = TableTools.fnGetInstance('DataTables_Table_0');
		if (oTT.fnGetSelectedIndexes().length < 1) return false;
		seldata = oTT.fnGetSelectedData();
		$('#modalacceptor').empty().load("/ajax/ajax_load_modal",{page: 'edit_inspection_modal', id: seldata[0][0]},function(){$('#EditInspectionModal').modal({show: true})});
		
	}

	function button_reinspect_action()
	{
		var oTT = TableTools.fnGetInstance('DataTables_Table_0');
		if (oTT.fnGetSelectedIndexes().length < 1) return false;
		seldata = oTT.fnGetSelectedData();
		$.ajax({
			url: '/dashboard/ajax_update_inspection_state',
			type: 'POST',
			data: {id: seldata[0][0], state: 'Reinspect'},
			success: function(result){
				// console.log(result);return false;
				if (result != 'Error') {
					window.location = "<?=current_url()?>"
				} else {
					alert('Error');
				}
			}
		});
	}

<?php if (has_permission('Allow delete review')): ?>
	function button_delete_action()
	{
		var oTT = TableTools.fnGetInstance('DataTables_Table_0');
		if (oTT.fnGetSelectedIndexes().length < 1) return false;
		if (!confirm('Sure?')) return false;
		seldata = oTT.fnGetSelectedData();
		$.ajax({
			url: "/dashboard/ajax_review_delete",
			type: "POST",
			data: {
				id: seldata[0][0]
			},
			success: function(msg) {
				// console.log(msg);
				if (msg=='done')
					$('#DataTables_Table_0 tr.active').remove();
			}
		});
	}
<?php endif; ?>
</script>
<?php endif; ?>


<?/* CHARTS STARTER */?>
<script type="text/javascript">
	$(function(){ //Load graph after full page load.
		makegraph('<?=$selected_graph?>');
	});

	$('.graphacceptor').on('click', function(){
		id = $(this).attr('id');
		makegraph(id);
	});

	function makegraph(id)
	{
		switch (id)
		{
			case 'compliance':
				title = 'Compliance Report';
				$('#inventorytab').hide();
				$('#ahjtab').hide();
				$('#activitytab').hide();
			break;
			case 'compliance2':
				title = 'Compliance Report';
				$('#inventorytab').hide();
				$('#ahjtab').hide();
				$('#activitytab').hide();
			break;
			case 'inventorychart':
			case 'inventorychart1':
				title = 'Inventory Report';
				$('#inventorytab').show();
				$('#ahjtab').hide();
				$('#activitytab').hide();
				$('#inventorytab li').removeClass('active');
				$('#inventorychart1').parent().addClass('active');
			break;
			case 'inventorychart2':
				title = 'Inventory Report';
				$('#inventorytab').show();
				$('#ahjtab').hide();
				$('#activitytab').hide();
				$('#inventorytab li').removeClass('active');
				$('#inventorychart2').parent().addClass('active');
			break;
			case 'inventorychart3':
				title = 'Inventory Report';
				$('#inventorytab').show();
				$('#ahjtab').hide();
				$('#activitytab').hide();
				$('#inventorytab li').removeClass('active');
				$('#inventorychart3').parent().addClass('active');
			break;
			case 'inventorychart4':
				title = 'Inventory Report';
				$('#inventorytab').show();
				$('#ahjtab').hide();
				$('#activitytab').hide();
				$('#inventorytab li').removeClass('active');
				$('#inventorychart4').parent().addClass('active');
			break;
			case 'ahjreport':
			case 'ahjreport1':
				title = 'AHJ Report';
				$('#ahjtab').show();
				$('#inventorytab').hide();
				$('#activitytab').hide();
				$('#ahjtab li').removeClass('active');
				$('#ahjreport1').parent().addClass('active');
			break;
			case 'ahjreport2':
				title = 'AHJ Report';
				$('#ahjtab').show();
				$('#inventorytab').hide();
				$('#activitytab').hide();
				$('#ahjtab li').removeClass('active');
				$('#ahjreport2').parent().addClass('active');
			break;
			case 'ahjreport3':
				title = 'AHJ Report';
				$('#ahjtab').show();
				$('#inventorytab').hide();
				$('#activitytab').hide();
				$('#ahjtab li').removeClass('active');
				$('#ahjreport3').parent().addClass('active');
			break;
			case 'activityreport':
			case 'activityreport1':
				title = 'User Activity Report';
				$('#activitytab').show();
				$('#activitytab li').removeClass('active');
				$('#activityreport1').parent().addClass('active');
				$('#ahjtab').hide();
				$('#inventorytab').hide();
			break;
			case 'activityreport2':
				title = 'User Activity Report';
				$('#activitytab').show();
				$('#activitytab li').removeClass('active');
				$('#activityreport2').parent().addClass('active');
				$('#ahjtab').hide();
				$('#inventorytab').hide();
			break;
			case 'activityreport3':
				title = 'User Activity Report';
				$('#activitytab').show();
				$('#activitytab li').removeClass('active');
				$('#activityreport3').parent().addClass('active');
				$('#ahjtab').hide();
				$('#inventorytab').hide();
			break;
		}

		$('#charttitle').html(title);
		showgraph(id);
		$('.graphacceptor').show();

		
		$('#chartacceptor').bind('jqplotDataClick', function (ev, seriesIndex, pointIndex, data)
		{
			if (id == 'compliance' && data[0] == 'Non-Compliant Doors')
			{
				showgraph('compliance2');
				// id = 'compliance2';
			}
			else if (id == 'compliance' && data[0] != 'Non-Compliant Doors')
			{
				$('#graphdata').val(data[0]);
				$('#graphform').submit();
			}

			else if (id == 'compliance2')
			{
		   		$('#graphdata').val(data[0]);
				$('#graphform').submit();
			}
			else if (id == 'inventorychart' || id == 'inventorychart1' || id == 'inventorychart2' || id == 'inventorychart3' || id == 'inventorychart4')
			{

		   		$('#graphdata').val(data[0]);
				$('#graphform').submit();
			}
			else
			{
				return;
			}
		});
	}

	function showgraph(graph_id)
	{
		$('#graphpid').val(graph_id);
		$.ajax({
			url: '/dashboard/ajax_make_graph',
			type: 'POST',
			data: {graph_id: graph_id},
			success: function(result) {
				// console.log(result);
				if (result == '<scr' + 'ipt type="text/javascript">window.location = "/user/login"</scr' + 'ipt>')
				{
					window.location = "/user/login";
				}
				$('#chartacceptor').empty();
				$('#chartwrapper').show();
				eval("$.jqplot('chartacceptor'," + result + ")");

			}
		})
	}
</script>
