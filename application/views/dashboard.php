<div class="container">
	<div class="row">
		<div class="col-md-3 graphacceptor" id="startdate">
			<?/*<span class="glyphicon glyphicon-zoom-in"></span>*/?>
			<img width="95%" src="/images/startdatechart.jpg" />
		</div>
		<?/*<div class="col-md-4 graphacceptor" id="completiondate">
			<span class="glyphicon glyphicon-zoom-in"></span>
			<img src="/images/completiondatechart.jpg" />
		</div>*/?>
		<div class="col-md-3 graphacceptor" id="statuschart">
			<?/*<span class="glyphicon glyphicon-zoom-in"></span>*/?>
			<img width="95%" src="/images/statuschart.jpg" />
		</div>
		<div class="col-md-3 graphacceptor" id="companyreview">
			<?/*<span class="glyphicon glyphicon-zoom-in"></span>*/?>
			<img width="95%" src="/images/companyreviewchart.jpg" />
		</div>
		<div class="col-md-3 graphacceptor" id="totalinmonth">
			<?/*<span class="glyphicon glyphicon-zoom-in"></span>*/?>
			<img width="95%" src="/images/totalinmonthchart.jpg" />
		</div>
		<?/*<div class="col-md-4 graphacceptor" id="reviewer">
			<span class="glyphicon glyphicon-zoom-in"></span>
			<img src="/images/reviewerchart.jpg" />
		</div>
		<div class="col-md-4 graphacceptor" id="totalnumberofreviewers">
			<span class="glyphicon glyphicon-zoom-in"></span>
			<img src="/images/totalnumberofreviewerschart.jpg" />
		</div>*/?>
	</div>
	<div class="row">
		<div class="col-md-10" id="chartwrapper">
			<div id="charttitle"></div>
			<?/*<span id="chartmagnify" class="glyphicon glyphicon-zoom-out"></span>*/?>
			<div id="chartacceptor"></div>
		</div>
		<div class="col-md-2">
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




<script type="text/javascript">
	function confirmation_review(door_id, insp_id)
	{
		$('#modalacceptor').empty().load("/ajax/ajax_load_modal",{page: 'show_inspection_modal', door_id: door_id, insp_id: insp_id},function(){$('#ShowInspectionModal').modal({show: true})});
	}

	$('#pdfexport').on('click', function() {
		picture = jqplotToImg($('#chartacceptor'));
		$.ajax({
			url: "/dashboard/ajax_export_to_pdf",
			type: "POST",
			data: {img: picture},
			success: function(result) {
				console.log(result);
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
	$(function(){
		$('#startdate').click();
	});
	$('#chartmagnify').on('click', function(){
		$('#charttitle').html('');
		// $('.graphacceptor').show();
		$('#chartwrapper').hide();
	});

	$('.graphacceptor').on('click', function(){
		id = $(this).attr('id');
		
		switch (id)
		{
			case 'startdate':
				title = 'Start Date';
			break;
			case 'completiondate':
				title = 'Completion Date';
			break;
			case 'statuschart':
				title = 'Status';
			break;
			case 'companyreview':
				title = 'Company Review';
			break;
			case 'totalinmonth':
				title = 'Total in Month';
			break;
		}

		$('#charttitle').html(title);
		showgraph(id);
		$('.graphacceptor').show();
		// $(this).hide();
	});

	function showgraph(graph_id)
	{
		$.ajax({
			url: '/dashboard/ajax_make_graph',
			type: 'POST',
			data: {graph_id: graph_id},
			success: function(result) {
				// console.log(result);
				$('#chartacceptor').empty();
				$('#chartwrapper').show();
				eval("$.jqplot('chartacceptor'," + result + ")");
			}
		})
	}
</script>