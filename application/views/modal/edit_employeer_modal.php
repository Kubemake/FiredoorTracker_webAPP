<!-- Edit Employeer Modal -->
<div class="modal fade" id="EditEmployeerModal" tabindex="-1" role="dialog" aria-labelledby="EditEmployeerModal" aria-hidden="true">
	<div class="modal-dialog modal-md">
		<div class="modal-content">
	 		<form method="POST" name="edit_employeer_modal" id="editbtnform" class="form-horizontal">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
					<h4 class="modal-title text-center" id="myModalLabel">Edit record</h4>
				</div>
				<div class="modal-body">
					<div class="row pad15">
						<div class="form-group">
							<label for="first_name" class="control-label col-xs-4">First Name</label>
							<div class="col-xs-8">
								<input name="first_name" id="first_name" class="form-control" value="<?=@$employeer['firstName']?>" />
							</div>
						</div>
						<div class="form-group">
							<label for="last_name" class="control-label col-xs-4">Last Name</label>
							<div class="col-xs-8">
								<input name="last_name" id="last_name" class="form-control" value="<?=@$employeer['lastName']?>" />
							</div>
						</div>
						<div class="form-group">
							<label for="officePhone" class="control-label col-xs-4">Office Phone</label>
							<div class="col-xs-8">
								<input name="officePhone" id="officePhone" class="form-control" value="<?=@$employeer['officePhone']?>" />
							</div>
						</div>
						<div class="form-group">
							<label for="mobilePhone" class="control-label col-xs-4">Mobile Phone</label>
							<div class="col-xs-8">
								<input name="mobilePhone" id="mobilePhone" class="form-control" value="<?=@$employeer['mobilePhone']?>" />
							</div>
						</div>
						<div class="form-group">
							<label for="email" class="control-label col-xs-4">Email</label>
							<div class="col-xs-8">
								<input type="email" name="email" id="email" class="form-control" value="<?=@$employeer['email']?>" pattern="[a-z0-9._%+-]+@[a-z0-9\.-]+\.[a-z]{2,4}" placeholder="Format ex.: john@yahoo.com" />
							</div>
						</div>
						<div class="form-group">
							<label for="expiration_date" class="control-label col-xs-4">Expiration Date</label>
							<div class="col-xs-8">
								 <div class="input-group date" id="expiration_date">
									<input name="expiration_date" class="form-control" value="<?=@$employeer['expired']?>" />
									<span class="input-group-addon"><i class="glyphicon glyphicon-calendar"></i></span>
								</div>
							</div>
						</div>
						<div class="form-group">
							<label for="license_number" class="control-label col-xs-4">License Number</label>
							<div class="col-xs-8">
								<input name="license_number" id="license_number" class="form-control" value="<?=@$employeer['license']?>" />
							</div>
						</div>
					</div>
					<div class="row">
						<div class="panel panel-primary">
							<div class="panel-heading">
								<div class="row pad15">
									<p class="lead text-center"><strong>PASSWORD GENERATION</strong></p>
									<div class="form-group">
										<div class="col-xs-4">
											<label class="radio-inline">
												<input type="radio" value="manually" name="password_generator">
												Manually
											</label>
										</div>
										<div class="col-xs-8">
											<label class="radio-inline">
												<input type="radio" value="generate" name="password_generator">
												Generate and send by email
											</label>
										</div>
									</div>
									<div class="form-group">
										<label for="new_password" class="control-label col-xs-4">New password</label>
										<div class="col-xs-8">
											<input type="password" name="new_password" id="new_password" class="form-control" value="" />
										</div>
									</div>
									<div class="form-group">
										<label for="repeat_password" class="control-label col-xs-4">Repeat password</label>
										<div class="col-xs-8">
											<input type="password" name="repeat_password" id="repeat_password" class="form-control" value="" />
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="form-group">
							<label class="control-label col-xs-4 text-right">Role</label>
							<div class="col-xs-8">
								<?php foreach ($user_roles as $role_id => $role_name): ?>
									<label class="radio-inline">
										<input type="radio" name="user_role" id="user_role" value="<?=$role_id?>" <?php if (@$employeer['role']==$role_id) echo ' checked';?>/>
										<?=$role_name?>
									</label>
								<?php endforeach; ?>
							</div>
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<input type="hidden" name="form_type" value="edit_employeer">
					<input type="hidden" name="user_id" value="<?=@$employeer['idUsers']?>">
					<button type="submit" class="btn btn-primary">Accept chages</button>
					<button type="button" class="btn btn-default" data-dismiss="modal">Cancel changes</button>
				</div>
				<script type="text/javascript">
					$(function () {
						$('#expiration_date').datepicker({format:'yyyy-mm-dd'}).on('changeDate', function(){
							$('#expiration_date').datepicker('hide');
						});
						$('#new_password,#repeat_password').password();
					});
				</script>
			</form>
		</div>
	</div>
</div>