<!-- Add Issue Modal -->
<div class="modal fade" id="AddIssueModal" tabindex="-1" role="dialog" aria-labelledby="AddIssueModal" aria-hidden="true">
	<div class="modal-dialog modal-md">
		<div class="modal-content">
	 		<form method="POST" name="add_issue_modal" id="addbtnform" class="form-horizontal">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
					<h4 class="modal-title text-center" id="myModalLabel">Add issue</h4>
				</div>
				<div class="modal-body">
					<div class="row pad15">
						<div class="form-group">
							<label for="first_name" class="control-label col-xs-4">Name</label>
							<div class="col-xs-8">
								<input name="name" id="name" class="form-control" required="required" onkeyup="this.value = this.value.replace(/[^\w\d\-.]/, '')" type="text" value="" />
							</div>
						</div>
						<div class="form-group">
							<label for="last_name" class="control-label col-xs-4">Label</label>
							<div class="col-xs-8">
								<input name="label" id="label" class="form-control" required="required" type="text" value="" />
							</div>
						</div>
						<div class="form-group">
							<label for="phone" class="control-label col-xs-4">Parent id</label>
							<div class="col-xs-8">
								<input name="parent" id="parent" class="form-control" required="required" type="text" value="" />
							</div>
						</div>
						<div class="form-group">
							<label for="email" class="control-label col-xs-4">Type</label>
							<div class="col-xs-8">
								<select name="type" id="type" class="form-control" type="text">
							      	<?php foreach ($issue_types as $isstype) {
							      		$sel = ($isstype == $issue['type']) ? ' selected="selected"' : '';
							      		echo '<option value="' . $isstype . '"' . $sel . '>' . $isstype . '</option>';
							      	} ?>
							    </select>
							</div>
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<input type="hidden" name="form_type" value="add_issue">
					<button type="submit" class="btn btn-primary">Add issue</button>
					<button type="button" class="btn btn-default" data-dismiss="modal">Cancel changes</button>
				</div>
			</form>
		</div>
	</div>
</div>