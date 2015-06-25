<?php foreach ($issues['issues'][$tabnextQuestionId]['answers'] as $section): ?>
	<div class="row">
		<div class="col-md-6"><?=$section['label']?></div>
		<div class="col-md-6">
			<div class="btn-group">
			<?php if ($section['label'] == 'Other' && $section['nextQuestionId'] == $tabnextQuestionId): ?>
				<input id="id<?=$section['name']?>" name="<?=$section['name']?>" value="<?=$section['idFormFields']?>" type="checkbox" class="otherinput" <?php echo (!empty($section['selected'])) ? ' checked="checked"' : ''; ?>>
				<input class="form-control" type="text" style="display: inline;width: auto;" value="<?=@$section['selected']?>" onkeyup="$('#<?=$section['name']?>tex').val($(this).val());if(this.value.length > 0){$('#id<?=$section['name']?>').prop('checked','checked');}else{$('#id<?=$section['name']?>').prop('checked','');};">
			<?php else: ?>
				<button data-toggle="dropdown" class="btn btn-default dropdown-toggle" data-placeholder="false">Manage<span class="caret"></span></button>
				<ul class="dropdown-menu noclose">
			    	<?php foreach ($issues['issues'][$section['nextQuestionId']]['answers'] as $answer): ?>
			    		<?php unset($issues['issues'][$section['nextQuestionId']]['answers'][$answer['idFormFields']]); ?>

			    		<li<?php echo ($answer['nextQuestionId'] != $tabnextQuestionId) ? ' class="dropdown-submenu"' : ''; ?>>
			    			<input 
			    				type="checkbox" 
			    				id="id<?=$answer['name']?>" 
			    				name="<?=$answer['name']?>" 
			    				value="<?=$answer['idFormFields']?>"
			    				<?php echo (!empty($answer['selected'])) ? ' checked="checked"' : ''; ?>
			    			>
			    			<label for="id<?=$answer['name']?>">
			    				<?php echo ($answer['nextQuestionId'] != $tabnextQuestionId) ? '<a href="#" tabindex="-1" data-toggle="dropdown">' . $answer['label'] . '</a>': $answer['label'];?>
			    				<?php if ($answer['label'] == 'Other' && $answer['nextQuestionId'] == $tabnextQuestionId): ?>
			    					<input class="form-control" type="text" style="display: inline;width: auto;" value="<?=@$answer['selected']?>" onkeyup="$('#<?=$answer['name']?>tex').val($(this).val())">
			    				<?php endif; ?>
			    			</label>

			    			<?php if ($answer['nextQuestionId'] != $tabnextQuestionId)
			    				echo make_children_answers($root_question = $tabnextQuestionId, $question_id = $answer['nextQuestionId'], $issues);
			    			 ?>
			    		</li>
			    	<?php endforeach; ?>
			    	<?php unset($issues['issues'][$section['nextQuestionId']]); ?>
				</ul>
			<?php endif; ?>
			</div>
		</div>
	</div>
<?php endforeach; ?>