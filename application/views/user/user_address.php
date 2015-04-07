<?php include 'user_head.php' ?>
<?php if ($this->session->userdata('user_role') == 1): ?>
	<div class="row">
		<div class="container">
			<div class="row">
				<div class="col-xs-12 col-sm-6 col-sm-push-3">
					<form method="POST" id="profileform" class="form-horizontal">
						<div class="alert alert-info">
							You can change your company address and primary contact information here
						</div>
						<div class="form-group">
							<label for="address" class="control-label col-xs-4">Address</label>
							<div class="col-xs-8">
								<input name="address" id="address" class="form-control" value="<?=@$address['address']?>" />
							</div>
						</div>
						<div class="form-group">
							<label for="city" class="control-label col-xs-4">City</label>
							<div class="col-xs-8">
								<input name="city" id="city" class="form-control typeahead" value="<?=@$address['city']?>" />
							</div>
						</div>
						<div class="form-group">
							<label for="state" class="control-label col-xs-4">State</label>
							<div class="col-xs-8">
								<input name="state" id="state" class="form-control" value="<?=@$address['state']?>" />
							</div>
						</div>
						<div class="form-group">
							<label for="zip" class="control-label col-xs-4">ZIP</label>
							<div class="col-xs-8">
								<input name="zip" id="zip" class="form-control" value="<?=@$address['zip']?>" />
							</div>
						</div>
						<div class="form-group">
						    <div class="col-xs-offset-4 col-xs-8">
						    	<button type="submit" class="btn btn-primary">Save</button>
						    </div>
						</div>
					</form>
				</div>
			</div>		
		</div>
	</div>

	<script type="text/javascript">
		$(document).ready(function(){
			$('#city').typeahead({
			    source: function(query, process) {
			        return $.ajax({
			            url: '/user/ajax_city_autocomplpite/city',
						type: 'POST',
			            data: {text: query},
			            dataType: 'json',
			            success: function(json) {
			            	console.log(json);
			                return json.length > 0 ? process(json) : false;
			            }
			        });
			    }
			}).change(function() {
			    var current = $(this).typeahead("getActive");
			    if (current) {
			    	$('#city').val(current.city);
			    	$('#state').val(current.state);
			    	$('#zip').val(current.zip);
			    };
			});
		});
	</script>
<?php else: ?>
	<div class="row">
		<div class="container">
			<div class="row">
				<div class="col-xs-12 col-sm-6 col-sm-push-3">
					<form method="POST" id="profileform" class="form-horizontal">
						<div class="form-group">
							<label for="address" class="control-label col-xs-4">Address</label>
							<div class="col-xs-8">
								<p id="address" class="form-control-static"><?=@$address['address']?></p>
							</div>
						</div>
						<div class="form-group">
							<label for="city" class="control-label col-xs-4">City</label>
							<div class="col-xs-8">
								<p id="city" class="form-control-static"><?=@$address['city']?></p>
							</div>
						</div>
						<div class="form-group">
							<label for="state" class="control-label col-xs-4">State</label>
							<div class="col-xs-8">
								<p id="state" class="form-control-static"><?=@$address['state']?></p>
							</div>
						</div>
						<div class="form-group">
							<label for="zip" class="control-label col-xs-4">ZIP</label>
							<div class="col-xs-8">
								<p id="zip" class="form-control-static"><?=@$address['zip']?></p>
							</div>
						</div>
					</form>
				</div>
			</div>		
		</div>
	</div>
<?php endif; ?>