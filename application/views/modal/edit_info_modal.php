<!-- Edit Info Modal -->
<div class="modal fade" id="EditInfoModal" tabindex="-1" role="dialog" aria-labelledby="EditInfoModal" aria-hidden="true">
	<div class="modal-dialog modal-md">
		<div class="modal-content">
	 		<form method="POST" name="edit_info_modal" id="editbtnform" class="form-horizontal">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
					<h4 class="modal-title text-center" id="myModalLabel">Edit <?=$info_type?></h4>
				</div>
				<div class="modal-body">
					<div class="row pad15">
						<div class="form-group">
							<label for="name" class="control-label col-xs-4">Name</label>
							<div class="col-xs-8">
								<input name="name" id="name" class="form-control" value="<?=$info['name']?>" />
							</div>
						</div>
						<div class="form-group">
							<label for="description" class="control-label col-xs-4">Description</label>
							<div class="col-xs-8">
								<input name="description" id="description" class="form-control" value="<?=$info['description']?>" />
							</div>
						</div>
						
					</div>
				</div>
				<div class="modal-footer">
					<input type="hidden" name="form_type" value="edit_info">
					<input type="hidden" name="type" value="<?=$info['type']?>">
					<input type="hidden" name="info_id" value="<?=$info['idInfo']?>">
					<button type="submit" class="btn btn-primary">Accept chages</button>
					<button type="button" class="btn btn-default" data-dismiss="modal">Cancel changes</button>
				</div>
			</form>
		</div>
	</div>
</div>