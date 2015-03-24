function humanizeSize(size) {
  var i = Math.floor( Math.log(size) / Math.log(1024) );
  return ( size / Math.pow(1024, i) ).toFixed(2) * 1 + ' ' + ['B', 'kB', 'MB', 'GB', 'TB'][i];
}

function updateFileStatus(i, status, message){
	$('#progress-files' + i).find('span.progress-file-status').html(message).addClass('progress-file-status-' + status);
}

function updateFileProgress(i, percent){
	$('#progress-files' + i).find('div.progress-bar').width(percent);
	$('#progress-files' + i).find('span.sr-only').html(percent + ' Complete');
}

function makeupload(item_id, owner_id, callBackFunction)
{
	callBackFunction = callBackFunction || '';
	$(item_id).dmUploader({
		url: '/media/upload',
		// allowedTypes: 'image/*',
        extFilter: 'jpg;png;gif;jpeg;avi;mpg;mpeg;flv;mov;mp4',
		extraData: {
			owner:owner_id
		},
		onFallbackMode: function(message){
			console.log('Upload plugin can\'t be initialized: ' + message);
		},
		onNewFile: function(id, file){
        	var template = '<div id="progress-file' + id + '">' +
	                   '<span class="progress-file-id">#' + id + '</span> - ' + file.name + ' <span class="progress-file-size">(' + humanizeSize(file.size) + ')</span> - Status: <span class="progress-file-status">Waiting to upload</span>'+
	                   '<div class="progress progress-striped active">'+
	                       '<div class="progress-bar" role="progressbar" style="width: 0%;">'+
	                           '<span class="sr-only">0% Complete</span>'+
	                       '</div>'+
	                   '</div>'+
	               '</div>';
	               
			var i = $('#progress-files').attr('file-counter');
			if (!i){
				$('#progress-files').empty();
				i = 0;
			}
			
			i++;
			
			$('#progress-files').attr('file-counter', i);
			
			$('#progress-files').prepend(template);
        },
        onBeforeUpload: function(id){
        	updateFileStatus(id, 'default', 'Uploading...');
        },
        onUploadProgress: function(id, percent){
        	var percentStr = percent + '%';
        	updateFileProgress('#progress-files', percentStr);
        },
		onComplete: function(){
			updateFileStatus('#progress-files', 'success', 'Upload Complete');
    		updateFileProgress('#progress-files', '100%');
		},
		onUploadSuccess: function(id, data){
			if (callBackFunction && typeof(callBackFunction) === "function"){
				callBackFunction(data);
			}
		}
	});
}