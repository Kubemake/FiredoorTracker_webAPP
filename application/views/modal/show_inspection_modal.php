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
				<div class="modal-body">
					<div class="row pad15">
						<div class="panel-group" id="accordion">
							<form method="POST" name="show_inspection_modal" id="editreviewform" class="form-horizontal">
								<?php $it=1; foreach ($issues['tabs'] as $tab): ?>
									<div class="panel panel-default">
										<div class="panel-heading">
											<h4 class="panel-title">
												<a data-toggle="collapse" data-parent="#accordion" href="#collapse<?=$it?>"><?=$tab['label']?></a>
											</h4>
										</div>
										<div id="collapse<?=$it++?>" class="panel-collapse collapse<?php echo ($it==2) ? ' in' : '';?>">
											<div class="panel-body">
												<?php foreach ($issues['issues'][$tab['nextQuestionId']]['answers'] as $section): ?>
													<div class="row">
														<div class="col-md-6"><?=$section['label']?></div>
														<div class="col-md-6">
															<div class="btn-group">
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
															    			<label for="id<?=$answer['name']?>"><?php echo ($answer['nextQuestionId'] != $tab['nextQuestionId']) ? '<a href="#" tabindex="-1" data-toggle="dropdown">' . $answer['label'] . '</a>': $answer['label'];?></label>
															    			<?php if ($answer['nextQuestionId'] != $tab['nextQuestionId'])
															    				echo make_children_answers($root_question = $tab['nextQuestionId'], $question_id = $answer['nextQuestionId'], $issues);
															    			 ?>
															    		</li>
															    	<?php endforeach; ?>
																</ul>
															</div>
														</div>
													</div>
												<?php endforeach; ?>
											</div>
										</div>
									</div>
								<?php endforeach; ?>
							</form>
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
				</div>
			</form>
		</div>
	</div>
</div>
<script type="text/javascript">

</script>