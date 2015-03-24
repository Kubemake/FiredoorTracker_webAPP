<div id="dd-<?=$building['idBuildings']?>" class="dd-content editfieldform well">
	<div class="row">
		<div id="updatemsg"></div>
	</div>
	<form class="row">
		<div class="col-md-5">
			<div class="form-group">
		    	<label class="control-label col-xs-3" for="idBuildings">id:</label>
			    <div class="col-xs-9"> 
			    <p class="form-control-static"><?=$building['idBuildings']?></p>
			    	<input type="hidden" class="form-control" name="idBuildings" id="idBuildings" value="<?=$building['idBuildings']?>" />
			    	<input type="hidden" class="form-control" name="idBuildings" id="idBuildings" value="<?=$building['root']?>" />
			    </div>
		  	</div>
		  	<div class="form-group">
		    	<label class="control-label col-xs-3" for="name">name:</label>
			    <div class="col-xs-9">
			      <input required="required" onkeyup="update_label(this);" type="text" class="form-control input-xs" name="name" id="name" value="<?=$building['name']?>" />
			    </div>
		  	</div>
		</div>
		<div class="col-md-7">
				<div class="form-group">
			    	<label class="control-label col-xs-4" for="parent">parent id:</label>
				    <div class="col-xs-8">
				      <input required="required" type="text" class="form-control input-xs" name="parent" id="parent" value="<?=@$building['parent']?>" />
				    </div>
			  	</div>
			<div class="form-group">
		  		<label class="control-label col-xs-4" for="label">actions:</label>
		  		<div class="col-xs-8">
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
				url: '/user/ajax_update_building',
				type: 'POST',
				data: formdata,
				success: function(result){
					result = JSON.parse(result);

					if (result.status=='ok'){
						$('#dd-' + elem_id).remove();
							alert('Successfully updated');
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