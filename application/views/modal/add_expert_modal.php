<!-- Add Expert Modal -->
<script type="text/javascript" src="/js/uploader/src/dmuploader.min.js"></script>
<script type="text/javascript" src="/js/custom-upload.js"></script>
<div class="modal fade" id="AddExpertModal" tabindex="-1" role="dialog" aria-labelledby="AddExpertModal" aria-hidden="true">
	<div class="modal-dialog modal-md">
		<div class="modal-content">
	 		<form method="POST" name="add_expert_modal" id="addbtnform" class="form-horizontal">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
					<h4 class="modal-title text-center" id="myModalLabel">Add record</h4>
				</div>
				<div class="modal-body">
					<div class="row pad15">
						<div class="form-group">
							<label for="name" class="control-label col-xs-4">Name</label>
							<div class="col-xs-8">
								<input name="name" id="name" class="form-control" value="" />
							</div>
						</div>
						<div class="form-group">
							<label for="description" class="control-label col-xs-4">Description</label>
							<div class="col-xs-8">
								<textarea name="description" id="description" class="form-control"></textarea>
							</div>
						</div>
						<div class="form-group">
							<label for="expert_logo" class="control-label col-xs-4">Logo image</label>
							<div class="col-xs-8">
								<div id="drop-area" class="text-center"><?//container for d&d upload?>
									<span class="upbtnwrap"><input type="file" class="fullwidth" name="expert_logo" multiple="multiple" title="Click to add Files" /></span>
									<div id="progress-files"></div><?//container for upload progress?>
								</div>
								<div id="drop-area-result">
									<button type="button" class="close close-uploaded">×</button>
									<div id="upload-acceptor" class="text-center"></div>
								</div>
							</div>
						</div>
						<div class="form-group">
							<label for="link" class="control-label col-xs-4">Url</label>
							<div class="col-xs-8">
								<input name="link" id="link" class="form-control" value="" />
							</div>
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<input type="hidden" name="form_type" value="add_expert">
					<input type="hidden" name="file_path" id="file_path" value="">
					<button type="submit" class="btn btn-primary">Accept chages</button>
					<button type="button" class="btn btn-default" data-dismiss="modal">Cancel changes</button>
				</div>
			</form>
		</div>
	</div>
</div>

<script type="text/javascript">
	makeupload('#drop-area', <?=$this->session->userdata('user_id')?>, function(data){
		$('#drop-area').hide();
		ftype = data.substr(-3);
		if ($.inArray(ftype,['jpg','png','jpeg','gif']) > -1)
			$('#upload-acceptor').html('<img id="upload-logo" src="' + data + '" />');
		$('#file_path').val(data);
		$('#drop-area-result').show();
	});

	$('.close-uploaded').on('click', function(){
		$('#upload-acceptor').html('');
		$('#drop-area').show();
		$('#drop-area-result').hide();
		$('#savemedia').addClass('disabled');
		$('#progress-files').prop('file-counter', '0').html('');
	})
</script>