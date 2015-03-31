<!-- Add Info Modal -->
<div class="modal fade" id="ChooseCondtionValueModal" tabindex="-1" role="dialog" aria-labelledby="ChooseCondtionValueModal" aria-hidden="true">
	<div class="modal-dialog modal-md">
		<div class="modal-content">
	 		<form method="POST" name="add_info_modal" id="chscndtn" class="form-horizontal">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
					<h4 class="modal-title text-center" id="myModalLabel">Choose value</h4>
				</div>
				<div class="modal-body">
					<div class="row pad15">
						<div class="form-group">
							<label for="description" class="control-label col-xs-4">Value</label>
							<div class="col-xs-8">
								<?php $statuscolor = array(
									1 => 'color-8',
									2 => 'color-2',
									3 => 'color-9',
									4 => 'color-5',
									5 => 'color-10'
								); ?>
								<select name="elementValue" id="elementValue" class="form-control fullwidth <?=$statuscolor[$thisvalue]?>" data-live-search="true" onchange="changecolor();return false;">
									<option class="color-0" value="">Choose value</option>
									<option class="color-0" value="0">Remove value</option>
									<?php foreach ($door_states as $key => $val): ?>
										<?php $selected = ($key == $thisvalue) ? ' selected': ''; ?>
										<option<?=$selected?> class="<?=$statuscolor[$key]?>" value="<?=$key?>"><?=$val?></option>
									<?php endforeach; ?>
								</select>
							</div>
						</div>
						
					</div>
				</div>
				<div class="modal-footer">
					<button type="submit" class="btn btn-primary">Accept chages</button>
					<button type="button" class="btn btn-default" data-dismiss="modal">Cancel changes</button>
				</div>
			</form>
		</div>
	</div>
</div>

<script type="text/javascript">
	var colors = {0:'color-8', 1:'color-8', 2:'color-2', 3:'color-9', 4:'color-5', 5:'color-10'};
	var	statuses = <?php echo json_encode($door_states); ?>;

	$('#chscndtn').submit(function(e){
		selected = $("#elementValue").val();
		$.ajax({
			url: '/admin/conditions/ajax_update_condition',
			type: 'POST',
			data: {wall_rate_id:<?=$wall_rate_id?>, id:<?=$id?>, ratesTypesId:<?=$ratestypesid?>, doorMatherialid:<?=$doormatherialid?>, doorRatingId:<?=$doorratingid?>, val:selected},
			success: function(msg) {
				if (msg =='done') {
					td = $('td[data-wall_rate_id="<?=$wall_rate_id?>"][data-id="<?=$id?>"][data-ratesTypesId="<?=$ratestypesid?>"][data-doorMatherialid="<?=$doormatherialid?>"][data-doorRatingId="<?=$doorratingid?>"]');
					td.removeClass().addClass('clicktochange');
					if(selected > 0)
					{
						td.attr('data-thisvalue', selected);
						td.addClass(colors[selected]).html(statuses[selected]);
					}
					else
					{
						td.attr('data-thisvalue', '');
						td.html('');
					}
				};
				
			}
		});
		$('#ChooseCondtionValueModal').modal('hide');
		return false;
	});

	function changecolor()
	{
		selected = $("#elementValue").val();
		$("#elementValue").removeClass().addClass('form-control fullwidth ' + colors[selected]);
	}
</script>