<!-- Add Aperture Modal -->
<div class="modal fade" id="AddApertureModal" tabindex="-1" role="dialog" aria-labelledby="AddApertureModal" aria-hidden="true">
	<div class="modal-dialog modal-md">
		<div class="modal-content">
	 		<form method="POST" name="add_aperture_modal" id="addbtnform" class="form-horizontal">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
					<h4 class="modal-title text-center" id="myModalLabel">Add record</h4>
				</div>
				<div class="modal-body">
					<div class="row pad15">
						<div class="form-group">
							<label for="name" class="control-label col-xs-4">Name</label>
							<div class="col-xs-8">
								<input name="name" id="name" class="form-control" value="" />
							</div>
						</div>
						<div class="form-group">
							<label for="first_name" class="control-label col-xs-4">Select aperture location</label>
							<div class="col-xs-8">
								<div class="dropdown locationselect">
									<button type="button" role="button" data-toggle="dropdown" class="btn btn-primary fullwidth" data-target="#">Select location <span class="caret"></span></button>
									<?php echo make_buildings_dropdown($user_buildings); ?>
									<input name="location" type="hidden" />
								</div>
							</div>
						</div>
						<div class="form-group">
							<label for="wallRating" class="control-label col-xs-4">Wall Rating</label>
							<div class="col-xs-8">
								<select name="wallRating" id="wallRating" class="form-control fullwidth" data-live-search="true">
									<option value="0">Choose Wall Rating</option>
									<?php foreach ($wall_rating as $key => $val): ?>
										<option value="<?=$key?>"><?=$val?></option>
									<?php endforeach; ?>
								</select>
							</div>
						</div>
						<div class="form-group">
							<label for="smokeRating" class="control-label col-xs-4">Smoke Rating</label>
							<div class="col-xs-8">
								<select name="smokeRating" id="smokeRating" class="form-control fullwidth" data-live-search="true">
									<option value="0">Choose Smoke Rating</option>
									<?php foreach ($smoke_rating as $key => $val): ?>
										<option value="<?=$key?>"><?=$val?></option>
									<?php endforeach; ?>
								</select>
							</div>
						</div>
						<div class="form-group">
							<label for="material" class="control-label col-xs-4">Material</label>
							<div class="col-xs-8">
								<select name="material" id="material" class="form-control fullwidth" data-live-search="true">
									<option value="0">Choose Material</option>
									<?php foreach ($material as $key => $val): ?>
										<option value="<?=$key?>"><?=$val?></option>
									<?php endforeach; ?>
								</select>
							</div>
						</div>
						<div class="form-group">
							<label for="rating" class="control-label col-xs-4">Rating</label>
							<div class="col-xs-8">
								<select name="rating" id="rating" class="form-control fullwidth" data-live-search="true">
									<option value="0">Choose Rating</option>
									<?php foreach ($rating as $key => $val): ?>
										<option value="<?=$key?>"><?=$val?></option>
									<?php endforeach; ?>
								</select>
							</div>
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<input type="hidden" name="form_type" value="add_aperture">
					<button type="submit" class="btn btn-primary">Accept chages</button>
					<button type="button" class="btn btn-default" data-dismiss="modal">Cancel changes</button>
				</div>
				<script type="text/javascript">
					$(function () {
						$('.locationselect ul li a').on('click', function(){
							$('.locationselect button').html($(this).html());
							$('.locationselect input').val($(this).data('id'));
						});
					});
				</script>
			</form>
		</div>
	</div>
</div>