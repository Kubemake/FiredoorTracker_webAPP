<!-- Edit File Modal -->
<div class="modal fade" id="EditFileModal" tabindex="-1" role="dialog" aria-labelledby="EditFileModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-md">
		<div class="modal-content">
	 		<form method="POST" name="edit_file_modal" id="editbtnform" class="form-horizontal">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
					<h4 class="modal-title text-center" id="EditFileModalLabel">Edit file info</h4>
				</div>
				<div class="modal-body">
					<div class="row pad15">
						<div class="form-group text-center">
						<?php if ($file['type']=='image'): ?>
							<img id="upload-result" src="<?=$file['path']?>" />
						<?php else: ?>
							<div id="upload-result"></div>
							<a href="<?=$file['path']?>" style="display: inline-block; width: 500px; height: 400px;" >&nbsp;</a>
							<script type="text/javascript">flowplayer("upload-result", {src : "/js/flowplayer/flowplayer-3.2.2.swf", wmode: "transparent"});</script>
						<?php endif; ?>
						</div>
						<div class="form-group">
							<label for="start_date" class="control-label col-xs-4">File name</label>
							<div class="col-xs-8">
								 <div class="input-group date" id="start_date">
									<input name="file_name" class="form-control" value="<?=$file['name']?>" />
								</div>
							</div>
						</div>
						<div class="form-group">
							<label for="start_date" class="control-label col-xs-4">Description</label>
							<div class="col-xs-8">
								 <div class="input-group date" id="start_date">
									<textarea name="file_descr" class="form-control"><?=$file['description']?></textarea>
								</div>
							</div>
						</div>
						<div class="form-group">
							<label for="first_name" class="control-label col-xs-4">Select door location</label>
							<div class="col-xs-8">
								<div class="dropdown locationselect">
									<button type="button" role="button" data-toggle="dropdown" class="btn btn-primary fullwidth" data-target="#"><?php echo empty($file['location_name']) ? 'Select location' : $file['location_name']; ?> <span class="caret"></span></button>
									<?php echo make_buildings_dropdown($user_buildings); ?>
									<input id="location" name="location" type="hidden" value="<?=$file['Buildings_idBuildings']?>" />
								</div>
							</div>
						</div>
						<div class="form-group">
							<label for="aperture" class="control-label col-xs-4">Select door</label>
							<div class="col-xs-8 apertureselect">
								<select name="aperture" id="aperture" class="selectpicker fullwidth" data-live-search="true">
									<option value="0">Choose door</option>
									<?php foreach ($user_apertures as $aperture): ?>
										<?php $select = ($file['aperture_id'] == $aperture['idDoors']) ? ' selected="selected"' : '';?>
										<option<?=$select?> value="<?=$aperture['idDoors']?>"><?=$aperture['name']?></option>
									<?php endforeach; ?>
								</select>
							</div>
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<input type="hidden" name="form_type" value="edit_file" />
					<input type="hidden" name="idfiles" value="<?=$file['idFiles']?>" />
					<button type="submit" class="btn btn-primary">Accept chages</button>
					<button type="button" class="btn btn-default" data-dismiss="modal">Cancel changes</button>
				</div>
			</form>
		</div>
	</div>
</div>
<script type="text/javascript">
	$(function () {
		$('.selectpicker').selectpicker();
	});

	$('.locationselect ul li a').on('click', function(){
		$('.locationselect button').html($(this).html());
		$('.locationselect input').val($(this).data('id'));
		$.ajax({
			url: '/dashboard/ajax_get_apertures',
			type: 'POST',
			data: {locid: $(this).data('id')},
			success: function(result) {
				$('.apertureselect .dropdown-menu').remove();
				$('.apertureselect').html(result);
				$('.selectpicker').selectpicker();
			}

		});
	});
</script>