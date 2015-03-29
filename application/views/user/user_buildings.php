<?php include 'user_head.php' ?>
<?php if (has_permission('Allow view buildings tree tab')): ?>
<div class="row">
	<div class="container">
		<?php if (has_permission('Allow modify buildings tree')): ?>
		<div class="row">
			<div class="text-right col-md-6 col-sm-12">
				<div class="btn-group" id="nestableMenu">
					<button type="button" class="btn btn-default button-expand" data-action="expand-all">Expand All</button>
					<button type="button" class="btn btn-default button-collapse"  data-action="collapse-all">Collapse All</button>
					<?//<button type="button" onclick="button_add_building_action();return false;" class="btn btn-default button-add">Add Building</button>?>
					<button type="button" onclick="button_add_element_action();return false;" class="btn btn-default button-add">Add Element</button>
				</div>
			</div>
		</div>
		<?php endif; ?>
		<div class="row">
			<div class="cf">
				<div class='dd' id="nestable">
					<?=$buildings?>
				</div>
			</div>
		</div>
	</div>
</div>
<?php if (has_permission('Allow modify buildings tree')): ?>
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
					url: '/user/ajax_buildings_reorder',
					type: 'POST',
					data: {buildings: output},
					success: function(msg){
						console.log(msg);
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
		function button_add_element_action()
		{
			$('#modalacceptor').empty().load("/ajax/ajax_load_modal",{page: 'add_user_building_modal'},function(){$('#AddUserBuildingModal').modal({show: true})});
			
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
				url: '/user/ajax_get_building_by_id',
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

			if (confirm("If you press YES, it delete this element and all it childs.\n Are you shure ?")) {
				$.ajax({
					url: '/user/ajax_delete_building',
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
<?php endif; ?>
<?php endif; ?>