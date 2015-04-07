<?php include 'user_head.php' ?>
<div class="row">
	<div class="container">
		<div class="container">
			<div class="row">
				<div class="col-md-6 col-md-push-3">
					<?php if ($this->session->userdata('user_role') == 1): ?>
					<div class="row">
						<div class="col-md-6">
							<div id="drop-area-div" class="text-center"<?php if (strlen($profile['logoFilePath']) > 0) echo ' style="display:none;"'; ?>> <?//container for d&d upload?>
								<div class="line-1">Upload your company logo file here</div>
								<div class="line-2">drag and drop files to upload</div>
								<div class="line-3">or <span class="upbtnwrap"><input type="file" name="files[]" title="Click to add Files" /></span></div>
								<div id="progress-files"></div><?//container for upload progress?>
							</div>
							<div class="company-logo" id="drop-area-div-result"<?php if (strlen($profile['logoFilePath']) > 0) echo ' style="display:block;"'; ?>>
								<button type="button" class="close close-uploaded" data-dismiss="modal" aria-hidden="true">×</button>
								<div id="upload-acceptor" class="text-center">
									<?php if (strlen($profile['logoFilePath']) > 0) echo '<img id="upload-result" src="' . $profile['logoFilePath'] . '" />'; ?>
								</div>
							</div>
						</div>
					</div>
					<?php endif; ?>
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
									<input type="hidden" id="logoFilePath" name="logoFilePath" class="btn btn-primary" value="<?=@$profile['logoFilePath']?>">
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
<?php if ($this->session->userdata('user_role') == 1): ?>
<script type="text/javascript">
	$(function(){
		makeupload('#drop-area-div', <?=$this->session->userdata('user_id')?>, function(data){
			$('#drop-area-div').hide();
			$('#upload-acceptor').html('<img id="upload-result" src="' + data + '" />');
			$('#logoFilePath').val(data);
			$('#drop-area-div-result').show();
		});
	})

	$('.close-uploaded').on('click', function(){
		$('#upload-acceptor').html('');
		$('#drop-area-div').show();
		$('#logoFilePath').val('');
		$('#drop-area-div-result').hide();
		$('#progress-files').prop('file-counter', '0').html('');
	})
</script>
<?php endif; ?>