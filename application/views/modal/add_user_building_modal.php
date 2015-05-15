<?php if (has_permission('Allow modify buildings tree')): ?>
<!-- Add User Building Modal -->
<div class="modal fade" id="AddUserBuildingModal" tabindex="-1" role="dialog" aria-labelledby="AddUserBuildingModal" aria-hidden="true">
	<div class="modal-dialog modal-md">
		<div class="modal-content">
	 		<form method="POST" name="add_user_building_modal" id="addbtnform" class="form-horizontal">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
					<h4 class="modal-title text-center" id="myModalLabel"><?php echo ($parent>0)? 'Add Element' : 'Add Building'; ?></h4>
				</div>
				<div class="modal-body">
					<div class="row pad15">
						<div class="form-group">
							<label for="first_name" class="control-label col-xs-4">Name</label>
							<div class="col-xs-8">
								<input name="name" id="name" class="form-control" required="required" type="text" value="" />
							</div>
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<input type="hidden" name="form_type" value="add_user_building">
					<input type="hidden" name="parent" value="<?=$parent?>" />
					<input type="hidden" name="level" value="<?=$level?>" />
					<button type="submit" class="btn btn-primary">Add element</button>
					<button type="button" class="btn btn-default" data-dismiss="modal">Cancel changes</button>
				</div>
			</form>
		</div>
	</div>
</div>
<?php endif; ?>