<!-- Show Inspection Modal -->
<link rel="stylesheet" type="text/css" href="/js/bootstrap-dropdowns_enhancement/dropdowns-enhancement.min.css">
<script type="text/javascript" src="/js/bootstrap-dropdowns_enhancement/dropdowns-enhancement.js"></script>
<div class="modal fade" id="ShowInspectionModal" tabindex="-1" role="dialog" aria-labelledby="ShowInspectionModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-md">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
				<h4 class="modal-title text-center" id="ShowInspectionModalLabel">Review information</h4>
			</div>
			<form method="POST" name="show_inspection_modal" id="editreviewform" class="form-horizontal">
				<div class="modal-body">
					<div class="row pad15">
						<div class="panel-group" id="accordion">
							<?php $it=1; foreach ($tabs as $tab): ?>
								<div class="panel panel-default">
									<div class="panel-heading">
										<h4 class="panel-title">
											<a id="<?=$tab['name']?>" data-tabid="<?=$tab['idFormFields']?>" data-toggle="collapse" data-parent="#accordion" href="#collapse<?=$it?>"><?=$tab['label']?></a>
										</h4>
									</div>
									<div id="collapse<?=$it++?>" class="panel-collapse collapse<?php echo ($it==2) ? ' in' : '';?>">
										<div class="panel-body">
											Loading...
										<?php //echo '<pre>';
										//print_r($tab); ?>
											<?php /*foreach ($issues['issues'][$tab['nextQuestionId']]['answers'] as $section): ?>
												<div class="row">
													<div class="col-md-6"><?=$section['label']?></div>
													<div class="col-md-6">
														<div class="btn-group">
														<?php if ($section['label'] == 'Other' && $section['nextQuestionId'] == $tab['nextQuestionId']): ?>
															<input id="id<?=$section['name']?>" name="<?=$section['name']?>" value="<?=$section['idFormFields']?>" type="checkbox" class="otherinput" <?php echo (!empty($section['selected'])) ? ' checked="checked"' : ''; ?>>
															<input class="form-control" type="text" style="display: inline;width: auto;" value="<?=@$section['selected']?>" onkeyup="$('#<?=$section['name']?>tex').val($(this).val());if(this.value.length > 0){$('#id<?=$section['name']?>').prop('checked','checked');}else{$('#id<?=$section['name']?>').prop('checked','');};">
														<?php else: ?>
															<button data-toggle="dropdown" class="btn btn-default dropdown-toggle" data-placeholder="false">Manage<span class="caret"></span></button>
															<ul class="dropdown-menu noclose">
														    	<?php foreach ($issues['issues'][$section['nextQuestionId']]['answers'] as $answer): ?>
														    		<li<?php echo ($answer['nextQuestionId'] != $tab['nextQuestionId']) ? ' class="dropdown-submenu"' : ''; ?>>
														    			<input 
														    				type="checkbox" 
														    				id="id<?=$answer['name']?>" 
														    				name="<?=$answer['name']?>" 
														    				value="<?=$answer['idFormFields']?>"
														    				<?php echo (!empty($answer['selected'])) ? ' checked="checked"' : ''; ?>
														    			>
														    			<label for="id<?=$answer['name']?>">
														    				<?php echo ($answer['nextQuestionId'] != $tab['nextQuestionId']) ? '<a href="#" tabindex="-1" data-toggle="dropdown">' . $answer['label'] . '</a>': $answer['label'];?>
														    				<?php if ($answer['label'] == 'Other' && $answer['nextQuestionId'] == $tab['nextQuestionId']): ?>
														    					<input class="form-control" type="text" style="display: inline;width: auto;" value="<?=@$answer['selected']?>" onkeyup="$('#<?=$answer['name']?>tex').val($(this).val())">
														    				<?php endif; ?>
														    			</label>

														    			<?php if ($answer['nextQuestionId'] != $tab['nextQuestionId'])
														    				echo make_children_answers($root_question = $tab['nextQuestionId'], $question_id = $answer['nextQuestionId'], $issues);
														    			 ?>
														    		</li>
														    	<?php endforeach; ?>
															</ul>
														<?php endif; ?>
														</div>
													</div>
												</div>
											<?php endforeach; */?>
										</div>
									</div>
								</div>
							<?php endforeach; ?>
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<?php foreach ($oth as $of): ?>
						<input type="hidden" id="<?=$of['name']?>tex" name="<?=$of['name']?>tex" value="<?=$of['selected']?>">
					<?php endforeach; ?>
					<input type="hidden" name="form_type" value="show_inspection" />
					<input type="hidden" name="aperture_id" value="<?=$aperture_id?>" />
					<input type="hidden" name="inspection_id" value="<?=$inspection_id?>" />
					<button type="submit" class="btn btn-primary">Accept chages</button>
					<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
				</div>
			</form>
		</div>
	</div>
</div> 
<script type="text/javascript">
	function get_issues_by_tabNQid(tabname)
	{
		inside = $('#' + tabname).prop('href');
		inside = inside.replace('http://firedoortracker.org/','');
		inside = $(inside + ' .panel-body');

		if (inside.html().replace( /\s+/g, '') == 'Loading...')
		{
			$.ajax({
				url: '/ajax/ajax_load_inspection_issues_by_tab',
				type: 'POST',
				data: {inspection_id: <?=$inspection_id?>, door_id: <?=$aperture_id?>, tabid: $('#' + tabname).data('tabid')},
				async: false,
				success: function(result) {
					// console.log(result);
					inside.empty().html(result);
				}
			});
		}
	}

	readydocstate = 0;
	$(document).ready(function(){
		get_issues_by_tabNQid('OperationalTestReview');
		get_issues_by_tabNQid('GlazingReview');
		get_issues_by_tabNQid('HardwareReview');
		get_issues_by_tabNQid('DoorReview');
		get_issues_by_tabNQid('FrameReview');
		readydocstate = 1;
	})

	$("#editreviewform").submit(function(e){
		if (readydocstate == 0)
		{
			alert('Please wait for loading all review data!');
			return false;
		}
	}); 

</script>