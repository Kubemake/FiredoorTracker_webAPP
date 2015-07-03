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
												<input id="generatepass" type="radio" value="generate" name="password_generator">
												Generate and send by email
											</label>
										</div>
									</div>
									<div class="form-group">
										<label for="new_password" class="control-label col-xs-4">New password</label>
										<div class="col-xs-8">
											<input type="password" name="new_password" id="new_password" class="form-control" value=""  onchange="checkPasswords()"  onclick="$('input:radio[name=password_generator]:nth(0)').attr('checked',true);"/>
										</div>
									</div>
									<div class="form-group">
										<label for="repeat_password" class="control-label col-xs-4">Repeat password</label>
										<div class="col-xs-8">
											<input type="password" name="repeat_password" id="repeat_password" class="form-control"  onchange="checkPasswords()"  value="" />
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="form-group">
							<label class="control-label col-xs-4 text-right">Role</label>
							<div class="col-xs-8" id="user_role">
								<?php foreach ($user_roles as $role_id => $role_name): ?>
									<label class="radio-inline">
										<input type="radio" name="user_role" id="user_role<?=$role_id?>" value="<?=$role_id?>" <?php if (@$employeer['role']==$role_id) echo ' checked';?>/>
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
			</form>
		</div>
	</div>
</div>

<script type="text/javascript">
	$(function () {
		$('#new_password,#repeat_password').password();

		$('#generatepass').on('click', function(){
			pass=str_rand();
			$('#new_password, #repeat_password').val(pass);
		});
	});
</script>
<script type="text/javascript">
	function str_rand() {
		var result	   		= '';
		var words			= '0123456789qwertyuiopasdfghjklzxcvbnmQWERTYUIOPASDFGHJKLZXCVBNM';
		var max_position 	= words.length - 1;
			for( i = 0; i < 12; ++i ) {
				position = Math.floor ( Math.random() * max_position );
				result = result + words.substring(position, position + 1);
			}
		return result;
	}

	function checkPasswords() {
		var passl = document.getElementById('new_password');
		var pass2 = document.getElementById('repeat_password');
		if (passl.value!=pass2.value)
			passl.setCustomValidity("Password mismatch. Please check password in both fields!");
		else
			passl.setCustomValidity("");
	}

	$('#editbtnform').submit(function(e){
		role = $('#user_role input[type=radio]:checked').val();
		$.ajax({
			url: '/user/ajax_check_lic_limit',
			method: 'POST',
			data:{role: role},
			async: false,
			success: function(result) {
				// console.log(result);
				if (result != 'ok')
				{
					$('#warnacceptor').html(result);
					$('#ShowWarnModal').modal({show: true});
					e.preventDefault();
					return false;
				}
			}
		});
	});

</script>