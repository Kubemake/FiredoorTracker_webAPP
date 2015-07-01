<div class="container-fluid">
	<div class="row">
		<div class="col-lg-push-3 col-lg-6 overviz col-md-12">
			<div class="col-md-6">
				<div id="drop-area-div" class="text-center"><?//container for d&d upload?>
					<div class="line-1">Upload your media files here</div>
					<div class="line-2">drag and drop files to upload</div>
					<div class="line-3">or <span class="upbtnwrap"><input type="file" name="files[]" multiple="multiple" title="Click to add Files" /></span></div>
					<div id="progress-files"></div><?//container for upload progress?>
				</div>
				<div id="drop-area-div-result">
					<button type="button" class="close close-uploaded" data-dismiss="modal" aria-hidden="true">×</button>
					<div id="upload-acceptor" class="text-center"></div>
				</div>
			</div>
			<div class="col-md-6">
				<form method="POST" name="add_uploaded_file" id="adduploadedfile" class="form-horizontal">
					<div class="form-group">
						<label for="aperture" class="control-label col-xs-4"><span>Door Id</span></label>
						<div class="col-xs-8 apertureselect">
							<select name="aperture" id="aperture" class="selectpicker fullwidth" data-live-search="true">
								<option value="0">Choose door id</option>
								<?php foreach ($user_apertures as $aperture): ?>
									<option value="<?=$aperture['idDoors']?>"><?=$aperture['barcode']?></option>
								<?php endforeach; ?>
							</select>
						</div>
					</div>
					<div class="form-group col-md-6 col-md-push-6 uploadaddsubmit">
						<input type="hidden" name="form_type" value="add_file" />
						<input type="hidden" name="file_path" id="file_path" class="form-control" value="" required="required" />
						<input type="hidden" name="file_time" id="file_time" class="form-control" value="" required="required" />
						<input type="hidden" name="file_type" id="file_type" class="form-control" value="" required="required" />
						<button type="submit" id="savemedia" class="btn btn-block btn-primary disabled">Save</button>
					</div>
				</form>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-lg-push-3 col-lg-6 col-md-12">
			<table class="table table-striped table-hover table-bordered table-responsive table-condensed" width="100%">
				<thead>
					<tr>
						<th>Door Id</th>
						<th>Preview</th>
						<th>Upload date</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($files as $file): ?>
						<tr>
							<td>
								<span class="fileactions">
									<?/*<a href="javascript:;" onclick="edit_image_action(this);return false;" data-id="<?=$file['idFiles']?>"><span class="glyphicon glyphicon-pencil"></span></a>*/?>
									<a href="javascript:;" onclick="delete_image_action(this);return false;" data-id="<?=$file['idFiles']?>"><span class="glyphicon glyphicon-trash"></span></a>
								</span>
								<?=$file['aperture']?>
							</td> 
							<td style="text-align:center;">
								<a href="javascript:;" class="<?php echo ($file['type']=='image') ? 'file-link' : 'v-file-link'; ?>" data-remote="<?=$file['path']?>" data-title="<?=$file['aperture']?>">
									<span class="file-icon glyphicon glyphicon-<?php echo ($file['type']=='image') ? 'picture' : 'film'; ?>"></span>
								</a>
							</td>
							<td><?=$file['FileUploadDate']?></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
	</div>
</div>

<script type="text/javascript">
	$(function(){
		$('.selectpicker').selectpicker();

		makeupload('#drop-area-div', <?=$this->session->userdata('user_id')?>, function(data){
			$('#drop-area-div').hide();
			ftype = data.substr(-3).toLowerCase();
			if ($.inArray(ftype,['jpg','png','jpeg','gif']) > -1) {
				$('#upload-acceptor').html('<img id="upload-result" src="' + data + '" />');
				filetype="image";
			} else {
				$('#upload-acceptor').html('<a href="' + data + '" style="display: block; width: 500px; height: 400px;" id="upload-result"></a><scr' + 'ipt type="text/javascript">flowplayer("upload-result", {src : "/js/flowplayer/flowplayer-3.2.2.swf", wmode: "transparent"});</scr' + 'ipt>');
				filetype="video";
			}
			
			$('#savemedia').removeClass('disabled');

			ftime = data.substr(-14).substr(0, 10);
			$('#file_path').val(data);
			$('#file_time').val(ftime);
			$('#file_type').val(filetype);
			$('#drop-area-div-result').show();
		});

		$('a.file-link').on('click', function(){
			$(this).ekkoLightbox();
		});

		$('tr').on('click', function(){
			unselectall();

			// $(this).find('.file-icon').hide();
			$(this).find('.fileactions').show();

		});
	})

	$('.close-uploaded').on('click', function(){
		$('#upload-acceptor').html('');
		$('#drop-area-div').show();
		$('#drop-area-div-result').hide();
		$('#savemedia').addClass('disabled');
		$('#progress-files').prop('file-counter', '0').html('');
	})

	<?/*function edit_image_action(e)
	{
		seldata = $(e).data('id');
		$('#modalacceptor').empty().load("/ajax/ajax_load_modal",{page: 'edit_file_modal', id: seldata},function(){unselectall();$('#EditFileModal').modal({show: true})});
	}*/?>

	function delete_image_action(e)
	{
		if (confirm('A you sure?'))
		{
			seldata = $(e).data('id');
			$.ajax({
				url: "/media/ajax_file_delete",
				type: "POST",
				data: {
					id: seldata
				},
				success: function(msg) {
					console.log(msg);
					if (msg=='done')
						$(e).closest('tr').remove();
				}
			});
		};
		
	}

	function unselectall()
	{
		$('.file-icon').each(function(){
			$(this).show();
		})

		$('.fileactions').each(function(){
			$(this).hide();
		})
	}

	$('.v-file-link').on('click', function() {
		$('#modalacceptor').empty().load("/media/ajax_load_video",{title: $(this).data('title'), url: $(this).data('remote')},function(){unselectall();$('#v-file-link').modal({show: true})});
	});

	$("#adduploadedfile").submit(function(e){
		if ($('#aperture').val()==0)
		{
			alert('Please choose Door Id!');
			e.preventDefault();
			return false;
		}
	}); 
</script>