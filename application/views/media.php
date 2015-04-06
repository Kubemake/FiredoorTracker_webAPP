<div class="container-fluid">
	<div class="row">
		<div class="col-md-push-2 col-md-8 overviz">
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
						<label for="first_name" class="control-label col-xs-4">Select door location</label>
						<div class="col-xs-8">
							<div class="dropdown locationselect">
								<button type="button" role="button" data-toggle="dropdown" class="btn btn-primary fullwidth" data-target="#">Select location <span class="caret"></span></button>
								<?php echo make_buildings_dropdown($user_buildings); ?>
								<input name="location" type="hidden" />
							</div>
						</div>
					</div>
					<div class="form-group">
						<label for="aperture" class="control-label col-xs-4"><span>Door</span></label>
						<div class="col-xs-8 apertureselect">
							<select name="aperture" id="aperture" class="selectpicker fullwidth" data-live-search="true">
								<option value="0">Choose door</option>
								<?php foreach ($user_apertures as $aperture): ?>
									<option value="<?=$aperture['idDoors']?>"><?=$aperture['name']?></option>
								<?php endforeach; ?>
							</select>
						</div>
					</div>
					<div class="form-group">
						<label for="file_name" class="control-label col-xs-4"><span>File name</span></label>
						<div class="col-xs-8">
							<input name="file_name" id="file_name" class="form-control" value="" required="required" />
							<input type="hidden" name="file_path" id="file_path" class="form-control" value="" required="required" />
							<input type="hidden" name="file_time" id="file_time" class="form-control" value="" required="required" />
							<input type="hidden" name="file_type" id="file_type" class="form-control" value="" required="required" />
						</div>
					</div>
					<div class="form-group descrfield">
						<label for="file_descr" class="control-label col-xs-4"><span>Description</span></label>
						<div class="col-xs-8">
							<textarea name="file_descr" id="file_descr" class="form-control" ></textarea>
						</div>
					</div>
					<div class="form-group col-md-6 col-md-push-6 uploadaddsubmit">
						<input type="hidden" name="form_type" value="add_file" />
						<button type="submit" id="savemedia" class="btn btn-block btn-primary disabled">Save</button>
					</div>
				</form>
			</div>
		</div>
		<div class="col-md-5"></div>
	</div>
	<div class="row">
		<div class="col-md-6">
			<table class="table table-striped table-hover table-bordered table-responsive table-condensed" width="100%">
				<thead>
					<tr>
						<th>Photos</th>
						<th>Door</th>
						<th>Upload date</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($image_files as $image): ?>
						<tr>
							<td>
								<span class="file-icon glyphicon glyphicon-picture"></span>
								<span class="fileactions">
									<a href="javascript:;" onclick="edit_image_action(this);return false;" data-id="<?=$image['idFiles']?>"><span class="glyphicon glyphicon-pencil"></span></a>
									<a href="javascript:;" onclick="delete_image_action(this);return false;" data-id="<?=$image['idFiles']?>"><span class="glyphicon glyphicon-trash"></span></a>
								</span>
								<a href="javascript:;" class="file-link" data-remote="<?=$image['path']?>" data-title="<?=$image['name']?>"><?=$image['name']?></a>
							</td> 
							<td><?=$image['aperture']?></td>
							<td><?=$image['FileUploadDate']?></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
		<div class="col-md-6">
			<table class="table table-striped table-hover table-bordered table-responsive table-condensed" width="100%">
				<thead>
					<tr>
						<th>Videos</th>
						<th>Door</th>
						<th>Upload date</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($video_files as $video): ?>
						<tr>
							<td>
								<span class="file-icon glyphicon glyphicon-film"></span>
								<span class="fileactions">
									<a href="javascript:;" onclick="edit_image_action(this);return false;" data-id="<?=$video['idFiles']?>"><span class="glyphicon glyphicon-pencil"></span></a>
									<a href="javascript:;" onclick="delete_image_action(this);return false;" data-id="<?=$video['idFiles']?>"><span class="glyphicon glyphicon-trash"></span></a>
								</span>
								<a href="javascript:;" class="v-file-link" data-remote="<?=$video['path']?>" data-title="<?=$video['name']?>"><?=$video['name']?></a>
							</td>
							<td><?=$video['aperture']?></td>
							<td><?=$video['FileUploadDate']?></td>
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
			ftype = data.substr(-3);
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

			$(this).find('.file-icon').hide();
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

	function edit_image_action(e)
	{
		seldata = $(e).data('id');
		$('#modalacceptor').empty().load("/ajax/ajax_load_modal",{page: 'edit_file_modal', id: seldata},function(){unselectall();$('#EditFileModal').modal({show: true})});
	}

	function delete_image_action(e)
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

	$('.locationselect ul li a').on('click', function(){
		$('.locationselect button').html($(this).html());
		$('.locationselect input').val($(this).data('id'));
		$.ajax({
			url: '/dashboard/ajax_get_apertures',
			type: 'POST',
			data: {locid: $(this).data('id')},
			success: function(result) {
				$('.apertureselect .dropdown-menu').remove();
				$('.apertureselect').html(result);
				$('.selectpicker').selectpicker();
			}

		});
	});

	$('.v-file-link').on('click', function() {
		$('#modalacceptor').empty().load("/media/ajax_load_video",{title: $(this).data('title'), url: $(this).data('remote')},function(){unselectall();$('#v-file-link').modal({show: true})});
	});
</script>