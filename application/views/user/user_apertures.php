<?php include 'user_head.php' ?>
<?php if (has_permission('Allow view doors tab')): ?>
<?php if (has_permission('Allow modify doors')): ?>
<div class="row">
	<div class="container">
		<div class="text-right col-md-push-6 col-md-6 col-sm-12">
			<div class="btn-group">
				<a href="javascript:;" onclick="button_add_action();return false;" class="btn btn-default button-add">Add</a>
				<a href="javascript:;" onclick="button_edit_action();return false;" class="btn btn-default button-edit">Edit</a>
				<a href="javascript:;" onclick="button_delete_action();return false;" class="btn btn-default button-delete">Delete</a>
			</div>
		</div>
	</div>
</div>
<?php endif; ?>
<?=@$result_table?>

<?php if (has_permission('Allow modify doors')): ?>
<script type="text/javascript">
	$('td').dblclick(function(){ //edit row on double clicking
		$(this).click();
		button_edit_action();
	});

	function button_edit_action()
	{
		var oTT = TableTools.fnGetInstance('DataTables_Table_0');
		if (oTT.fnGetSelectedIndexes().length < 1) return false;
		seldata = oTT.fnGetSelectedData();
		$('#modalacceptor').empty().load("/ajax/ajax_load_modal",{page: 'edit_aperture_modal', id: seldata[0][0]},function(){$('#EditApertureModal').modal({show: true})});
	}

	function button_add_action()
	{
		$('#modalacceptor').empty().load("/ajax/ajax_load_modal",{page: 'add_aperture_modal'},function(){$('#AddApertureModal').modal({show: true})});
		
	}

	function button_delete_action()
	{
		var oTT = TableTools.fnGetInstance('DataTables_Table_0');
		if (oTT.fnGetSelectedIndexes().length < 1) return false;
		if (!confirm('Sure?')) return false;
		seldata = oTT.fnGetSelectedData();
		$.ajax({
			url: "/user/ajax_delete_aperture",
			type: "POST",
			data: {id: seldata[0][0]},
			success: function(msg) {
				// console.log(msg);
				if (msg=='done')
					$('#DataTables_Table_0 tr.active').remove();
			}
		});
	}

</script>
<?php endif; ?>
<?php endif; ?>
