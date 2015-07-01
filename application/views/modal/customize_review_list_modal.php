<!-- Customize Review List Modal -->
<link rel="stylesheet" type="text/css" href="/js/bootstrap-dropdowns_enhancement/dropdowns-enhancement.min.css">
<script type="text/javascript" src="/js/bootstrap-dropdowns_enhancement/dropdowns-enhancement.js"></script>
<div class="modal fade" id="CustomizeReviewListModal" tabindex="-1" role="dialog" aria-labelledby="CustomizeReviewListModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg">
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
										<a data-toggle="collapse" data-parent="#accordion" href="#collapse1">Common parameters</a>
									</h4>
								</div>
								<div id="collapse1" class="panel-collapse collapse in">
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
											<label for="users[]" class="control-label col-xs-4">Select Users</label>
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
											<label for="status[]" class="control-label col-xs-4">Select Statuses</label>
											<div class="col-xs-8">
												 <div class="input-group fullwidth" id="status">
													<select name="status[]" class="selectpicker fullwidth" data-live-search="true" multiple>
														<option value="all">All Statuses</option>
														<?php foreach ($statuses as $status): ?>
															<option value="<?=$status?>"><?=$status?></option>
														<?php endforeach; ?>
													</select>
												</div>
											</div>
										</div>
										<div class="form-group">
											<label for="buildings[]" class="control-label col-xs-4">Select Buildings</label>
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
							        	<a data-toggle="collapse" data-parent="#accordion" href="#collapse2">Door Info Overview</a>
							        </h4>
							    </div>
							    <div id="collapse2" class="panel-collapse collapse">
							    	<div class="panel-body">
							    		<?php foreach ($criteria as $crit_name => $crit_vals): ?>
								        	<div class="col-md-6">
									        	<div class="form-group">
													<label for="criteria[<?=$crit_name?>][]" class="control-label col-xs-4"><?=$crit_vals['label']?></label>
													<div class="col-xs-8">
														 <div class="input-group fullwidth" id="criteria">
															<select name="criteria[<?=$crit_name?>][]" class="selectpicker fullwidth" data-live-search="true" multiple>
																<option value="all">All <?=$crit_vals['label']?></option>
																<?php foreach ($crit_vals['data'] as $crit_id => $ctit_val): ?>
																	<option value="<?=$ctit_val?>"><?=$ctit_val?></option>
																<?php endforeach; ?>
															</select>
														</div>
													</div>
												</div>
											</div>
										<?php endforeach; ?>
							    	</div>
							    </div>
							</div>
							<?php $it=3; foreach ($tabs as $tab): ?>
								<div class="panel panel-default">
									<div class="panel-heading">
										<h4 class="panel-title">
											<a id="<?=$tab['name']?>" data-tabid="<?=$tab['idFormFields']?>" data-toggle="collapse" data-parent="#accordion" href="#collapse<?=$it?>"><?=$tab['label']?></a>
										</h4>
									</div>
									<div id="collapse<?=$it++?>" class="panel-collapse collapse<?php echo ($it==2) ? ' in' : '';?>">
										<div class="panel-body">
											<?php foreach ($issues[$tab['nextQuestionId']]['answers'] as $section): ?>
												<div class="row">
													<div class="col-md-6"><?=$section['label']?></div>
													<div class="col-md-6">
														<div class="btn-group">
														<?php if ($section['label'] == 'Other' && $section['nextQuestionId'] == $tab['nextQuestionId'] && $answer['nextQuestionId'] != 179): ?>
															<input id="id<?=$section['name']?>" name="<?=$tab['name']?>[<?=$section['name']?>]" value="<?=$section['idFormFields']?>" type="checkbox" class="otherinput">
															<input class="form-control" type="text" style="display: inline;width: auto;" value="" onkeyup="$('#<?=$section['name']?>tex').val($(this).val());if(this.value.length > 0){$('#id<?=$section['name']?>').prop('checked','checked');}else{$('#id<?=$section['name']?>').prop('checked','');};">
														<?php else: ?>
															<button data-toggle="dropdown" class="btn btn-default dropdown-toggle" data-placeholder="false">Manage<span class="caret"></span></button>
															<ul class="dropdown-menu noclose">
														    	<?php  foreach ($issues[$section['nextQuestionId']]['answers'] as $answer): ?>
														    		<?php unset($issues[$section['nextQuestionId']]['answers'][$answer['idFormFields']]); ?>

														    		<li<?php echo ($answer['nextQuestionId'] != $tab['nextQuestionId'] && $answer['nextQuestionId'] != 179) ? ' class="dropdown-submenu"' : ''; ?>>
														    			<input 
														    				type="checkbox" 
														    				id="id<?=$answer['name']?>" 
														    				name="<?=$tab['name']?>[<?=$answer['name']?>]" 
														    				value="<?=$answer['idFormFields']?>"
														    			>
														    			<label for="id<?=$answer['name']?>">
														    				<?php echo ($answer['nextQuestionId'] != $tab['nextQuestionId'] && $answer['nextQuestionId'] != 179) ? '<a href="#" tabindex="-1" data-toggle="dropdown">' . $answer['label'] . '</a>': $answer['label'];?>
														    				<?php if ($answer['label'] == 'Other' && $answer['nextQuestionId'] == $tab['nextQuestionId'] && $answer['nextQuestionId'] != 179): ?>
														    					<input class="form-control" type="text" style="display: inline;width: auto;" value="" onkeyup="$('#<?=$answer['name']?>tex').val($(this).val())">
														    				<?php endif; ?>
														    			</label>

														    			<?php if ($answer['nextQuestionId'] != $tab['nextQuestionId'] && $answer['nextQuestionId'] != 179)
														    					echo make_children_answers_for_filter($root_question = $tab['nextQuestionId'], $question_id = $answer['nextQuestionId'], $issues, $tab['name']);
														    			 ?>
														    		</li>
														    	<?php endforeach; ?>
														    	<?php unset($issues[$section['nextQuestionId']]); ?>
															</ul>
														<?php endif; ?>
														</div>
													</div>
												</div>
											<?php endforeach; ?>
										</div>
									</div>
								</div>
							<?php endforeach; ?>
							
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<?php foreach ($oth as $of): ?>
						<input type="hidden" id="<?=$of['name']?>tex" name="other[<?=$of['name']?>tex]" value="">
					<?php endforeach; ?>
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

$(window).on('shown.bs.modal', function (e) {
	$('.modal-backdrop').css('position', 'fixed');
});

$('.selectpicker').on('change', function() {
	vals = $(this).val();

	if ((':'+vals.join(':')+':').search(":all:") != -1)
	{
		$(this).selectpicker('selectAll');
	};
	
});

</script>