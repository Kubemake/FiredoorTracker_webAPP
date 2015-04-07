<div class="container">
	<div class="row">
		<div class="text-right col-md-6 col-sm-12">
			<div class="btn-group" id="nestableMenu">
				<button type="button" class="btn btn-default button-expand" data-action="expand-all">Expand All</button>
				<button type="button" class="btn btn-default button-collapse"  data-action="collapse-all">Collapse All</button>
				<button type="button" onclick="button_add_action();return false;" class="btn btn-default button-add">Add</button>
			</div>
		</div>
	</div>
</div>
<div class="container">
	<div class="row">
		<div class="cf nestable-lists">
			<div class='dd' id="nestable">
				<?=$issues?>
			</div>
		</div>
	</div>
</div>


<script type="text/javascript"><?//Nestable scripts?>
	$(document).ready(function(){
		var updateOutput = function(e)
		{
			var list   = e.length ? e : $(e.target);
			
			if (!window.JSON) {
				alert('JSON browser support required for this demo.');
				return false;
			}
			output = window.JSON.stringify(list.nestable('serialize'));

			$.ajax({
				url: '/admin/issues/ajax_issues_reorder',
				type: 'POST',
				data: {issues: output},
				success: function(result){
					console.log(result);
				}
			})
		};

		$('#nestable').nestable({maxDepth: 30}).on('change', updateOutput);

		$('#nestableMenu button').on('click', function(e)
		{
			var target = $(e.target),
				action = target.data('action');
			if (action === 'expand-all') {
				$('#nestable').nestable('expandAll');
			}
			if (action === 'collapse-all') {
				$('#nestable').nestable('collapseAll');
			}
		});
	})
</script>

<script type="text/javascript">
	function button_add_action()
	{
		$('#modalacceptor').empty().load("/ajax/ajax_load_modal",{page: 'add_issue_modal'},function(){$('#AddIssueModal').modal({show: true})});
		
	}

	function editfield(e)
	{
		var li = $(e).parent(),
		elem_id = li.data('id');
		
		cont = li.find('#dd-' + elem_id); //remove if present. work like toggle
		if (cont.length != 0) 
		{
			cont.remove();
			return false;
		}

		$.ajax({
			url: '/admin/issues/ajax_get_issue_by_id',
			type: "POST",
			data: {
				id: elem_id
			},
			success: function(output) {
				li.find('.dd-handle').eq(0).after(output);
			}
		})
	}

	function deletefield(e)
	{
		var li = $(e).parent(),
		elem_id = li.data('id');

		if (confirm("Are you sure you want to delete?\nIf you press yes, all data for the door and door review will be deleted permanently")) {
			$.ajax({
				url: '/admin/issues/ajax_delete_issue',
				type: "POST",
				data: {
					id: elem_id
				},
				success: function(output) {
					li.remove();
				}
			})
		};
	}
</script>