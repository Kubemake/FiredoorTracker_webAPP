<?php if (has_permission('Allow modify review')): ?>
<div class="container">
	<div class="row">
		<div class="text-right col-md-push-6 col-md-6 col-sm-12">
			<div class="btn-group">
				<a href="javascript:;" onclick="button_add_action();return false;" class="btn btn-default button-add">Add</a>
				<a href="javascript:;" onclick="button_edit_action();return false;" class="btn btn-default button-edit">Edit</a>
				<?/*<button type="button" class="btn btn-default button-pdf">View PDF</button>
				<button type="button" class="btn btn-default button-import">Import</button>
				<button type="button" class="btn btn-default button-assign">Assign</button>*/?>
				<?php if (has_permission('Allow delete review')): ?><a href="javascript:;" onclick="button_delete_action();return false;" class="btn btn-default button-delete">Delete</a><?php endif; ?>
				<a href="javascript:;" onclick="button_reinspect_action();return false;" class="btn btn-default button-reinspect">Reinspect</a>
			</div>
		</div>
	</div>
</div>
<?php endif; ?>
<?=@$result_table?>

<div class="row">
	<div class="col-md-10 col-md-push-1" id="chartwrapper">
		<div id="charttitle"></div>
		<span id="chartmagnify" class="glyphicon glyphicon-zoom-out"></span>
		<div id="chartacceptor"></div>
	</div>
</div>
<div class="row">
	<div class="col-md-4 graphacceptor" id="startdate">
		<span class="glyphicon glyphicon-zoom-in"></span>
		<img src="/images/startdatechart.jpg" />
	</div>
	<div class="col-md-4 graphacceptor" id="completiondate">
		<span class="glyphicon glyphicon-zoom-in"></span>
		<img src="/images/completiondatechart.jpg" />
	</div>
	<div class="col-md-4 graphacceptor" id="statuschart">
		<span class="glyphicon glyphicon-zoom-in"></span>
		<img src="/images/statuschart.jpg" />
	</div>
	<div class="col-md-4 graphacceptor" id="companyreview">
		<span class="glyphicon glyphicon-zoom-in"></span>
		<img src="/images/companyreviewchart.jpg" />
	</div>
	<div class="col-md-4 graphacceptor" id="totalinmonth">
		<span class="glyphicon glyphicon-zoom-in"></span>
		<img src="/images/totalinmonthchart.jpg" />
	</div>
	<div class="col-md-4 graphacceptor" id="reviewer">
		<span class="glyphicon glyphicon-zoom-in"></span>
		<img src="/images/reviewerchart.jpg" />
	</div>
	<div class="col-md-4 graphacceptor" id="totalnumberofreviewers">
		<span class="glyphicon glyphicon-zoom-in"></span>
		<img src="/images/totalnumberofreviewerschart.jpg" />
	</div>
</div>

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
	$('#chartmagnify').on('click', function(){
		$('#charttitle').html('');
		$('.graphacceptor').show();
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
		$(this).hide();
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