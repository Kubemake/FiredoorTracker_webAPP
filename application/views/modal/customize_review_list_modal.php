<!-- Customize Review List Modal -->
<div class="modal fade" id="CustomizeReviewListModal" tabindex="-1" role="dialog" aria-labelledby="CustomizeReviewListModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-md">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
				<h4 class="modal-title text-center" id="ShowInspectionModalLabel">Review list filter</h4>
			</div>
			<form method="POST" name="add_user_building_modal" id="addbtnform" class="form-horizontal">
				<div class="modal-body">
					<div class="row pad15">
						<div class="panel-group" id="accordion">
							<div class="panel panel-default">
								<div class="panel-heading">
									<h4 class="panel-title">
										<a data-toggle="collapse" data-parent="#accordion" href="#collapseOne">STEP 1</a>
									</h4>
								</div>
								<div id="collapseOne" class="panel-collapse collapse in">
									<div class="panel-body">
										<div class="form-group">
											<label for="start_date" class="control-label col-xs-4">Dates between</label>
											<div class="col-xs-4">
												 <div class="input-group date" id="start_date">
													<input name="start_date" class="form-control" value="" />
													<span class="input-group-addon"><i class="glyphicon glyphicon-calendar"></i></span>
												</div>
											</div>
											<div class="col-xs-4">
												 <div class="input-group date" id="end_date">
													<input name="end_date" class="form-control" value="" />
													<span class="input-group-addon"><i class="glyphicon glyphicon-calendar"></i></span>
												</div>
											</div>
										</div>
										<div class="form-group">
											<label for="users[]" class="control-label col-xs-4">Reviewers</label>
											<div class="col-xs-8">
												 <div class="input-group fullwidth" id="users">
													<select name="users[]" class="selectpicker fullwidth" data-live-search="true" multiple>
														<option value="all">All Users</option>
														<?php foreach ($users as $user): ?>
															<option value="<?=$user['idUsers']?>"><?=$user['firstName'] . ' ' . $user['lastName']?></option>
														<?php endforeach; ?>
													</select>
												</div>
											</div>
										</div>
										<div class="form-group">
											<label for="creators[]" class="control-label col-xs-4">Creators</label>
											<div class="col-xs-8">
												 <div class="input-group fullwidth" id="creators">
													<select name="creators[]" class="selectpicker fullwidth" data-live-search="true" multiple>
														<option value="all">All Users</option>
														<?php foreach ($users as $user): ?>
															<option value="<?=$user['idUsers']?>"><?=$user['firstName'] . ' ' . $user['lastName']?></option>
														<?php endforeach; ?>
													</select>
												</div>
											</div>
										</div>
										<div class="form-group">
											<label for="buildings[]" class="control-label col-xs-4">Buildings</label>
											<div class="col-xs-8">
												 <div class="input-group fullwidth" id="buildings">
													<select name="buildings[]" class="selectpicker fullwidth" data-live-search="true" multiple>
														<option value="all">All Buildings</option>
														<?php foreach ($buildings as $building): ?>
															<option value="<?=$building['idBuildings']?>"><?=$building['name']?></option>
														<?php endforeach; ?>
													</select>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
							<div class="panel panel-default">
							    <div class="panel-heading">
							    	<h4 class="panel-title">
							        	<a data-toggle="collapse" data-parent="#accordion" href="#collapseTwo">STEP 2<?//Select Criteria?></a>
							        </h4>
							    </div>
							    <div id="collapseTwo" class="panel-collapse collapse">
							    	<div class="panel-body">
							    		<?php foreach ($criteria as $crit_name => $crit_vals): ?>
								        	<div class="form-group">
												<label for="criteria[<?=$crit_name?>][]" class="control-label col-xs-4"><?=$crit_name?></label>
												<div class="col-xs-8">
													 <div class="input-group fullwidth" id="criteria">
														<select name="criteria[<?=$crit_name?>][]" class="selectpicker fullwidth" data-live-search="true" multiple>
															<option value="all">All <?=$crit_name?></option>
															<?php foreach ($crit_vals as $crit_id => $ctit_val): ?>
																<option value="<?=$crit_id?>"><?=$ctit_val?></option>
															<?php endforeach; ?>
														</select>
													</div>
												</div>
											</div>
										<?php endforeach; ?>
							    	</div>
							    </div>
							</div>
							<div class="panel panel-default">
							    <div class="panel-heading">
							    	<h4 class="panel-title">
							        	<a data-toggle="collapse" data-parent="#accordion" href="#collapseThree">STEP 3<?//Select non-compliance area of review?></a>
							        </h4>
							    </div>
							    <div id="collapseThree" class="panel-collapse collapse">
							    	<div class="panel-body">
							        	<div class="form-group">
											<label for="users[]" class="control-label col-xs-4">Users</label>
											<div class="col-xs-8">
												 <div class="input-group fullwidth" id="users">
													<select name="users[]" class="selectpicker fullwidth" data-live-search="true" multiple>
														<option value="all">All Users</option>
														<?php foreach ($users as $user): ?>
															<option value="<?=$user['idUsers']?>"><?=$user['firstName'] . ' ' . $user['lastName']?></option>
														<?php endforeach; ?>
													</select>
												</div>
											</div>
										</div>
							    	</div>
							    </div>
							</div>
							
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<input type="hidden" name="form_type" value="customize_review" />
					<button type="submit" class="btn btn-primary">Show list</button>
					<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
				</div>
			</form>
		</div>
	</div>
</div>
<script type="text/javascript">
$(function(){
	$('.selectpicker').selectpicker();

	$('#start_date').datepicker({format:'yyyy-mm-dd'}).on('changeDate', function(){
		$('#start_date').datepicker('hide');
	});

	$('#end_date').datepicker({format:'yyyy-mm-dd'}).on('changeDate', function(){
		$('#end_date').datepicker('hide');
	});
});

$('.selectpicker').on('change', function() {
	vals = $(this).val();

	if ((':'+vals.join(':')+':').search(":all:") != -1)
	{
		$(this).selectpicker('selectAll');
	};
	
});
</script>