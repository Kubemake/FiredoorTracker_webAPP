<!-- Edit Inspection Modal -->
<div class="modal fade" id="EditInspectionModal" tabindex="-1" role="dialog" aria-labelledby="EditInspectionModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-md">
		<div class="modal-content">
	 		<form method="POST" name="edit_inspection_modal" id="editbtnform" class="form-horizontal">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
					<h4 class="modal-title text-center" id="EditInspectionModalLabel">Edit review</h4>
				</div>
				<div class="modal-body">
					<div class="row pad15">
						<div class="form-group">
							<label for="first_name" class="control-label col-xs-4">Select door location</label>
							<div class="col-xs-8">
								<div class="dropdown locationselect">
									<button type="button" role="button" data-toggle="dropdown" class="btn btn-primary fullwidth" data-target="#"><?php echo empty($inspection['name']) ? 'Select location' : $inspection['name']; ?> <span class="caret"></span></button>
									<?php echo make_buildings_dropdown($user_buildings); ?>
									<input name="location" type="hidden" value="<?=$inspection['Buildings_idBuildings']?>" />
								</div>
							</div>
						</div>
						<div class="form-group">
							<label for="aperture" class="control-label col-xs-4">Select door</label>
							<div class="col-xs-8 apertureselect">
								<select name="aperture" id="aperture" class="selectpicker fullwidth" data-live-search="true">
									<option value="0">Choose door</option>
									<?php foreach ($user_apertures as $aperture): ?>
										<?php $select = ($inspection['idAperture'] == $aperture['idDoors']) ? ' selected="selected"' : '';?>
										<option<?=$select?> value="<?=$aperture['idDoors']?>"><?=$aperture['barcode']?></option>
									<?php endforeach; ?>
								</select>
							</div>
						</div>
						<div class="form-group">
							<label for="reviewer" class="control-label col-xs-4">Reviewer</label>
							<div class="col-xs-8">
								<select name="reviewer" class="selectpicker fullwidth" data-live-search="true">
									<option<?php echo ($inspection['Inspector'] == 0) ? ' selected="selected"':'';?> value="0">Choose reviewer</option>
									<?php foreach ($users_reviewer as $reviewer): ?>
										<?php $select = ($inspection['Inspector'] == $reviewer['idUsers']) ? ' selected="selected"' : '';?>
										<option<?=$select?> value="<?=$reviewer['idUsers']?>"><?=$reviewer['firstName'] . ' ' . $reviewer['lastName']?></option>
									<?php endforeach; ?>
									
									
								</select>
							</div>
						</div>
						<div class="form-group">
							<label for="state" class="control-label col-xs-4">Review state</label>
							<div class="col-xs-8">
								<select name="state" class="selectpicker fullwidth" data-live-search="true">
									<?php foreach ($inspection_statuses as $status): ?>
										<?php $select = ($inspection['InspectionStatus'] == $status) ? ' selected="selected"' : '';?>
										<option<?=$select?> value="<?=$status?>"><?=$status?></option>
									<?php endforeach; ?>
								</select>
							</div>
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<input type="hidden" name="form_type" value="edit_inspection" />
					<input type="hidden" name="idInspections" value="<?=$inspection['idInspections']?>" />
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
		$('#start_date').datepicker({format:'yyyy-mm-dd'}).on('changeDate', function(){
			$('#start_date').datepicker('hide');
		});
		$('#completion_date').datepicker({format:'yyyy-mm-dd'}).on('changeDate', function(){
			$('#completion_date').datepicker('hide');
		});
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

	$("#editbtnform").submit(function(e){
	    if ($('.apertureselect select').val()=='Choose door')
		{
			alert('Please choose door!');
			return false;
		}
	});
</script>