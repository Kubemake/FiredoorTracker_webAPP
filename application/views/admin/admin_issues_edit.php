<div id="dd-<?=$issue['idFormFields']?>" class="dd-content editfieldform well">
	<div class="row">
		<div id="updatemsg"></div>
	</div>
	<form class="row">
		<div class="col-md-6">
			<div class="form-group">
		    	<label class="control-label col-xs-3" for="idFormFields">id:</label>
			    <div class="col-xs-9"> 
			    <p class="form-control-static"><?=$issue['idFormFields']?></p>
			    	<input type="hidden" class="form-control" name="idFormFields" id="idFormFields" value="<?=$issue['idFormFields']?>" />
			    </div>
		  	</div>
			<div class="form-group">
		    	<label class="control-label col-xs-3" for="name">name:</label>
			    <div class="col-xs-9">
			      <input required="required" onkeyup="this.value = this.value.replace(/[^\w\d\-.]/, '')" type="text" class="form-control input-xs" name="name" id="name" value="<?=$issue['name']?>" />
			    </div>
		  	</div>
		  	<div class="form-group">
		    	<label class="control-label col-xs-3" for="label">label:</label>
			    <div class="col-xs-9">
			      <input required="required" onkeyup="update_label(this);" type="text" class="form-control input-xs" name="label" id="label" value="<?=$issue['label']?>" />
			    </div>
		  	</div>
		</div>
		<div class="col-md-6">
			<div class="form-group">
		    	<label class="control-label col-xs-3" for="parent">parent id:</label>
			    <div class="col-xs-9">
			      <input required="required" type="text" class="form-control input-xs" name="parent" id="parent" value="<?=$issue['parent']?>" />
			    </div>
		  	</div>
			<div class="form-group">
		    	<label class="control-label col-xs-3" for="label">type:</label>
			    <div class="col-xs-9">
			      <select type="text" class="form-control input-xs" name="type" id="type">
			      	<?php foreach ($issue_types as $isstype) {
			      		$sel = ($isstype == $issue['type']) ? ' selected="selected"' : '';
			      		echo '<option value="' . $isstype . '"' . $sel . '>' . $isstype . '</option>';
			      	} ?>
			      </select>
			    </div>
		  	</div>
		  	<div class="form-group">
		  		<label class="control-label col-xs-3" for="label">actions:</label>
		  		<div class="col-xs-9">
				    <div class="btn-group btn-group-justified">
				    	<div class="btn-group">
				    		<button class="btn btn-primary" name="savefield" id="savefield">Update</button>
				    	</div>
				    	<div class="btn-group">
				    		<button class="btn btn-default" name="cancel" id="cancel">Cancel</button>
				    	</div>
				    </div>
				</div>
		  	</div>
		</div>
	</form>
	<script type="text/javascript">
		var startlabel = false;

		$('#cancel').on('click', function(e){
			e.preventDefault();
			
			elem_id = $(this).closest('li').data('id');
			
			if (startlabel)
				$('#dd-' + elem_id).prev().find('.label-text').html(startlabel);

			$('#dd-' + elem_id).remove();
		});

		$('#savefield').on('click', function(e){
			e.preventDefault();
			elem_id = $(this).closest('li').data('id');
			formdata = $(this).closest('form').serialize();

			$.ajax({
				url: '/admin/issues/ajax_update_issue',
				type: 'POST',
				data: formdata,
				success: function(result){
					result = JSON.parse(result);

					if (result.status=='ok'){
						$('#dd-' + elem_id).remove();
						alert('Successfully updated');
					} else if(result.status=='duplicate') {
						$('#updatemsg').html('<div class="alert alert-danger alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>Error: duplicate value of <strong>name</strong> field</div>');
					} else {
						$('#updatemsg').html('<div class="alert alert-danger alert-warning"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>Error while updating data</div>');
					}

				}
			})
		})

		function update_label(e)
		{
			elem_id = $(e).closest('li').data('id');
			labeltext = $('#dd-' + elem_id).prev().find('.label-text');
			if (!startlabel) startlabel = labeltext.html();
			labeltext.html($(e).val());
		}
	</script>
</div>