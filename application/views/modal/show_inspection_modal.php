<!-- Show Inspection Modal -->
<link rel="stylesheet" type="text/css" href="/js/bootstrap-dropdowns_enhancement/dropdowns-enhancement.min.css">
<script type="text/javascript" src="/js/bootstrap-dropdowns_enhancement/dropdowns-enhancement.js"></script>
<div class="modal fade" id="ShowInspectionModal" tabindex="-1" role="dialog" aria-labelledby="ShowInspectionModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-md">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
				<h4 class="modal-title text-center" id="ShowInspectionModalLabel">Review information</h4>
			</div>
			<form method="POST" name="show_inspection_modal" id="editreviewform" class="form-horizontal">
				<div class="modal-body">
					<div class="row pad15">
						<div class="panel-group" id="accordion">
							<?php $it=1; foreach ($tabs as $tab): ?>
								<div class="panel panel-default">
									<div class="panel-heading">
										<h4 class="panel-title">
											<a id="<?=$tab['name']?>" data-tabid="<?=$tab['idFormFields']?>" data-toggle="collapse" data-parent="#accordion" href="#collapse<?=$it?>"><?=$tab['label']?></a>
										</h4>
									</div>
									<div id="collapse<?=$it++?>" class="panel-collapse collapse<?php echo ($it==2) ? ' in' : '';?>">
										<div class="panel-body">
											Loading...
										</div>
									</div>
								</div>
							<?php endforeach; ?>
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<?php foreach ($oth as $of): ?>
						<input type="hidden" id="<?=$of['name']?>tex" name="<?=$of['name']?>tex" value="<?=$of['selected']?>">
					<?php endforeach; ?>
					<input type="hidden" name="form_type" value="show_inspection" />
					<input type="hidden" name="aperture_id" value="<?=$aperture_id?>" />
					<input type="hidden" name="inspection_id" value="<?=$inspection_id?>" />
					<button type="submit" class="btn btn-primary">Accept chages</button>
					<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
				</div>
			</form>
		</div>
	</div>
</div> 
<script type="text/javascript">
	function get_issues_by_tabNQid(tabname)
	{
		inside = $('#' + tabname).prop('href');
		inside = inside.replace('http://firedoortracker.org/','');
		inside = $(inside + ' .panel-body');
		
		$.ajax({
			url: '/ajax/ajax_load_inspection_issues_by_tab',
			type: 'POST',
			data: {inspection_id: <?=$inspection_id?>, door_id: <?=$aperture_id?>, tabid: $('#' + tabname).data('tabid')},
			async: false,
			success: function(result) {
				// console.log(result);
				inside.empty().html(result);
			}
		});
	}

	readydocstate = 0;
	$(document).ready(function(){
		get_issues_by_tabNQid('OperationalTestReview');
		get_issues_by_tabNQid('HardwareReview');
		get_issues_by_tabNQid('DoorReview');
		get_issues_by_tabNQid('FrameReview');
		if ($('#GlazingReview').length)
			get_issues_by_tabNQid('GlazingReview');
		readydocstate = 1;
	})

	$("#editreviewform").submit(function(e){
		if (readydocstate == 0)
		{
			alert('Please wait for loading all review data!');
			return false;
		}
	}); 

	function addbtnaction(inspection_id, qid, btnid)
	{
		switch(btnid)
		{
			case 789789:
				elem = 	'sign';
			break;
			case 789790:
				elem = 	'hole';
			break;
			case 789791:
				elem = 	'hinge';
			break;

		}

		if (confirm('Are you sure you want to add ' + elem + '?'))
		{
			if (btnid == 789789)
			{
				w = prompt('Set sign width');
				w = w.replace('/[^\d]+', '');
				if (w.length > 0)
				{
					h = prompt('Set sign height');
					h = h.replace('/[^\d]+', '');
					if (h.length > 0)
					{
						$.ajax({
							url: '/ajax/ajax_addbtnaction',
							type: 'POST',
							data: {inspection_id: inspection_id, question_id: qid, btnid: btnid, val: w + ',' + h},
							async: false,
							success: function(result) {
								if (result == 'ok')
								{
									tab = $('#qid' + qid).closest('.panel-default').find('.panel-heading a').prop('id');
									get_issues_by_tabNQid(tab, 1);
								}
								
							}
						});
					}
				};
			}
			else
			{
				$.ajax({
					url: '/ajax/ajax_addbtnaction',
					type: 'POST',
					data: {inspection_id: inspection_id, question_id: qid, btnid: btnid, val: 'Yes'},
					async: false,
					success: function(result) {
						if (result == 'ok')
						{
							tab = $('#qid' + qid).closest('.panel-default').find('.panel-heading a').prop('id');
							get_issues_by_tabNQid(tab, 1);
						}
						
					}
				});
			}
		};
	}

</script>