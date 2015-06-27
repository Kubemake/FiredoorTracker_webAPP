<!-- Send Email Modal -->
<div class="modal fade" id="SendEmailModal" tabindex="-1" role="dialog" aria-labelledby="SendEmailModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-md">
		<div class="modal-content">
			<form method="POST" name="send_email_modal" id="sendemailform" class="form-horizontal">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
					<h4 class="modal-title text-center" id="myModalLabel">Send Email</h4>
				</div>
				<div class="modal-body">
					<div class="row pad15">
						<div class="form-group">
							<label for="from" class="control-label col-xs-4">From</label>
							<div class="col-xs-8">
								<input name="from" id="from" class="form-control" pattern="[a-z0-9._%+-]+@[a-z0-9\.-]+\.[a-z]{2,4}" value="info@firedoortracker.org" />
							</div>
						</div>
						<div class="form-group">
							<label for="to" class="control-label col-xs-4">To</label>
							<div class="col-xs-8">
								<select name="to[]" id="to" class="selectpicker fullwidth" data-live-search="true" multiple>
									<option value="all">Select All</option>
									<?php foreach ($users as $user): ?>
										<option value="<?=$user['email']?>"><?=$user['email']?><<?=$user['firstName'] . ' ' . $user['lastName']?>></option>
									<?php endforeach; ?>
								</select>
							</div>
						</div>
						<div class="form-group">
							<label for="subject" class="control-label col-xs-4">Subject</label>
							<div class="col-xs-8">
								<input name="subject" id="subject" class="form-control" value="New report from info@firedoortracker.org" />
							</div>
						</div>
						<div class="form-group">
							<label for="body" class="control-label col-xs-4">Body</label>
							<div class="col-xs-8">
								<textarea name="body" id="body" class="form-control"></textarea>
							</div>
						</div>
						<div class="form-group">
							<label for="body" class="control-label col-xs-4">File</label>
							<div class="col-xs-8">
								<p class="form-control-static"><a href="/upload/<?=$this->session->userdata('user_id')?>/pdf_export.pdf">pdf_export.pdf</a></p>
							</div>
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<input type="hidden" name="form_type" value="send_email">
					<button type="submit" class="btn btn-primary">Send Email</button>
					<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
				</div>
			</form>
		</div>
	</div>
</div>
<script type="text/javascript">
	$(function(){
		$('.selectpicker').selectpicker();

		$('#start_date').datepicker({format:'yyyy-mm-dd'}).on('changeDate', function(){
			$('#start_date').datepicker('hide');
		});

		$('#end_date').datepicker({format:'yyyy-mm-dd'}).on('changeDate', function(){
			$('#end_date').datepicker('hide');
		});
	});

	$('.selectpicker').on('change', function() {
		vals = $(this).val();

		if ((':'+vals.join(':')+':').search(":all:") != -1)
		{
			$(this).selectpicker('selectAll');
		};
		
	});

	$('.selectpicker').on('change', function() {
		vals = $(this).val();

		if ((':'+vals.join(':')+':').search(":all:") != -1)
		{
			$(this).selectpicker('selectAll');
		};
		
	});

	$('#sendemailform').submit(function(e) {
	    if ($('#to').val() == null)
		{
			alert('Please choose at least one recipient');
			return false;
		}
	});
</script>