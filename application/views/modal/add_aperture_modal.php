<!-- Add Aperture Modal -->
<div class="modal fade" id="AddApertureModal" tabindex="-1" role="dialog" aria-labelledby="AddApertureModal" aria-hidden="true">
	<div class="modal-dialog modal-md">
		<div class="modal-content">
	 		<form method="POST" name="add_aperture_modal" id="addbtnform" class="form-horizontal">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
					<h4 class="modal-title text-center" id="myModalLabel">Add record</h4>
				</div>
				<div class="modal-body">
					<div class="row pad15">
						<div class="form-group">
							<label for="barcode" class="control-label col-xs-4">Door Id</label>
							<div class="col-xs-8">
								<input name="barcode" required pattern="[\d]{6}" placeholder="6 digits only" id="barcode" class="form-control" value="" />
							</div>
						</div>
						<div class="form-group">
							<label for="building" class="control-label col-xs-4">Select Building</label>
							<div class="col-xs-8">
								<select name="building" id="building" class="form-control fullwidth" data-live-search="true">
									<option value="0">Choose Building</option>
									<?php foreach ($building as $key => $val): ?>
										<option value="<?=$key?>"><?=$val?></option>
									<?php endforeach; ?>
								</select>
							</div>
						</div>
						<div class="form-group">
							<label for="floor" class="control-label col-xs-4">Select Floor</label>
							<div class="col-xs-8">
								<select name="floor" id="floor" class="form-control fullwidth" data-live-search="true">
									<option value="0">Choose Floor</option>
								</select>
							</div>
						</div>
						<div class="form-group">
							<label for="wing" class="control-label col-xs-4">Select Wing</label>
							<div class="col-xs-8">
								<select name="wing" id="wing" class="form-control fullwidth" data-live-search="true">
									<option value="0">Choose Wing</option>
								</select>
							</div>
						</div>
						<div class="form-group">
							<label for="area" class="control-label col-xs-4">Select Area</label>
							<div class="col-xs-8">
								<select name="area" id="area" class="form-control fullwidth" data-live-search="true">
									<option value="0">Choose Area</option>
								</select>
							</div>
						</div>
						<div class="form-group">
							<label for="level" class="control-label col-xs-4">Select Level</label>
							<div class="col-xs-8">
								<select name="level" id="level" class="form-control fullwidth" data-live-search="true">
									<option value="0">Choose Level</option>
								</select>
							</div>
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<input type="hidden" name="form_type" value="add_aperture">
					<button type="submit" class="btn btn-primary">Accept chages</button>
					<button type="button" class="btn btn-default" data-dismiss="modal">Cancel changes</button>
				</div>
				<script type="text/javascript">
					$(document).ready(function(){
						floor = $('#floor').html();
						wing  = $('#wing').html();
						area  = $('#area').html();
						level = $('#level').html();
					});

					$('#building').on('change', function(){
						if($(this).val() != 0)
						{
							$('#wing').html(wing);
							$('#area').html(area);
							$('#level').html(level);
							$.ajax({
								url: '/user/ajax_get_building_childs/' + $(this).val(),
								success: function(result){
									// console.log(result);
									$('#floor').html(floor+result);
								}
							})

						}
					});

					$('#floor').on('change', function(){
						if($(this).val() != 0)
						{
							$('#area').html(area);
							$('#level').html(level);
							$.ajax({
								url: '/user/ajax_get_building_childs/' + $(this).val(),
								success: function(result){
									// console.log(result);
									$('#wing').html(wing+result);
								}
							})

						}
					});

					$('#wing').on('change', function(){
						if($(this).val() != 0)
						{
							$('#level').html(level);
							$.ajax({
								url: '/user/ajax_get_building_childs/' + $(this).val(),
								success: function(result){
									// console.log(result);
									$('#area').html(area+result);
								}
							})

						}
					});

					$('#area').on('change', function(){
						if($(this).val() != 0)
						{
							$.ajax({
								url: '/user/ajax_get_building_childs/' + $(this).val(),
								success: function(result){
									// console.log(result);
									$('#level').html(level+result);
								}
							})

						}
					});

					$("#addbtnform").submit(function(e){
						$.ajax({
							url: '/user/ajax_check_barcode',
							method: 'POST',
							data:{barcode: $('#barcode').val(), doorid: '-'},
							async: false,
							success: function(result) {
								if (result == 'exist')
								{
									alert('Door Id allready exist!');
									e.preventDefault();
									return false;
								}
							}
						});
						if ($('#building').val()==0)
						{
							alert('Please choose Building!');
							e.preventDefault();
							return false;
						}
					}); 
				</script>
			</form>
		</div>
	</div>
</div>