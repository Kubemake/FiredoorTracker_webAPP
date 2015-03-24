<?php include 'user_head.php' ?>
<div class="row">
	<div class="container">
		<div class="container">
			<div class="row">
				<div class="col-md-6 col-md-push-3">
					<!--div class="row">
						<h2 class="col-xs-9 col-xs-push-3">User profile</h2>
					</div-->
					<div class="row">
						<form class="form-horizontal" method="POST">
							<div class="panel panel-default">
							<div class="panel-body">
								<div class="form-group">
									<label class="control-label col-xs-3" for="inputEmail">Email:</label>
									<div class="col-xs-9">
										<p class="form-control-static" id="inputEmail"><?=$profile['email']?></p>
									</div>
								</div>
								<div class="form-group">
									<label class="control-label col-xs-3" for="inputPassword">Password:</label>
									<div class="col-xs-9">
										<input type="password" class="form-control" name="inputPassword" id="inputPassword" placeholder="Enter new password">
									</div>
								</div>
								<div class="form-group">
									<label class="control-label col-xs-3" for="confirmPassword">Confirm password:</label>
									<div class="col-xs-9">
										<input type="password" class="form-control" name="confirmPassword" id="confirmPassword" placeholder="Confirm new password">
										<span class="help-block">Password will be changed only if you fill passwords fields</span>
									</div>
								</div>
								</div>
							</div>
							<div class="form-group">
								<label class="control-label col-xs-3" for="firstName">First Name:</label>
								<div class="col-xs-9">
									<input type="text" class="form-control" name="firstName" id="firstName" placeholder="Enter your Name" value="<?=@$profile['firstName']?>">
								</div>
							</div>
							<div class="form-group">
								<label class="control-label col-xs-3" for="lastName">Last Name:</label>
								<div class="col-xs-9">
									<input type="text" class="form-control" name="lastName" id="lastName" placeholder="Enter your Last Name" value="<?=@$profile['lastName']?>">
								</div>
							</div>
							<div class="form-group">
								<label class="control-label col-xs-3" for="officePhoneNumber">Phone:</label>
								<div class="col-xs-9">
									<input type="tel" class="form-control" name="officePhone" id="officePhone" placeholder="Office phone number" value="<?=@$profile['officePhone']?>">
									<br />
									<input type="tel" class="form-control" name="mobilePhone" id="mobilePhone" placeholder="Mobile phone number" value="<?=@$profile['mobilePhone']?>">
								</div>
							</div>
							<br />
							<div class="form-group">
								<div class="col-xs-offset-3 col-xs-9">
									<input type="submit" name="updateProfile" class="btn btn-primary" value="Update profile data">
								</div>
							</div>
						</form>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>