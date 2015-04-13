<!-- Show Inspection Modal -->
<div class="modal fade" id="ShowInspectionModal" tabindex="-1" role="dialog" aria-labelledby="ShowInspectionModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-md">
		<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
					<h4 class="modal-title text-center" id="ShowInspectionModalLabel">Review information</h4>
				</div>
				<div class="modal-body">
					<div class="row pad15">
						<?php
						foreach ($trouble_list as $group => $troubles)
						{
							echo '<h2>' . $group . '</h2>';
							echo '<ul>';
							foreach ($troubles as $section => $issues)
							{
								echo '<li>' . $section . '<ul>';
								foreach ($issues as $issue)
								{
									echo '<li>' . $issue['label'] . '</li>';
								}
								echo "</ul>";
								echo "</li>";
							}
							echo '</ul>';
						} 
						?>
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