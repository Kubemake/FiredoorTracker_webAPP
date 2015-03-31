<div class="container">
	<div class="row">
	<?php if (has_permission('Allow modify experts')): ?><div class="col-md-12"><a href="javascript:;" onclick="button_add_action();return false;" class="btn btn-default button-add">Add</a></div><?php endif; ?>
		<?php $i=1; foreach ($experts as $expert): ?>
			<div class="col-md-6" id="expert<?=$expert['idExperts']?>">
				<div class="expert">
					<h3><?=$expert['name']?></h3><?php if (has_permission('Allow modify experts')): ?><span> ( <a data-id="<?=$expert['idExperts']?>" href="javascript:;" onclick="edit_action(this);return false;">Edit</a> | <a data-id="<?=$expert['idExperts']?>" href="javascript:;" onclick="delete_action(this);return false;">Delete</a> )</span><?php endif; ?>
					<div class="clearfix"></div>
					<div class="col-md-5 expert-img"><img title="<?=$expert['name']?>" alt="<?=$expert['name']?>" src="<?=$expert['logo']?>" /></div>
					<div class="col-md-7 expert-text"><?=$expert['description']?></div>
					<div class="col-md-12 text-right expert-link"><a href="<?=$expert['link']?>"><?=preg_replace('#(http://|https://)?([^/]+)/?(.*?)#', '$2', $expert['link'])?></a></div>
					<div class="clearfix"></div>
				</div>
			</div>
			<?php echo ($i++%2==0) ? '</div><div class="row">' : ''; ?>
		<?php endforeach; ?>
	</div>
</div>

<?php if (has_permission('Allow modify experts')): ?>
<script type="text/javascript">
	function button_add_action(type)
	{
		$('#modalacceptor').empty().load("/ajax/ajax_load_modal",{page: 'add_expert_modal', type: type},function(){$('#AddExpertModal').modal({show: true})});
		
	}

	function edit_action(e)
	{
		infoid = $(e).data('id');
		$('#modalacceptor').empty().load("/ajax/ajax_load_modal",{page: 'edit_expert_modal', id: infoid},function(){$('#EditExpertModal').modal({show: true})});
	}

	function delete_action(e)
	{
		infoid = $(e).data('id');
		if (!confirm('Sure?')) return false;
		$.ajax({
			url: "/contactanexpert/ajax_delete_expert",
			type: "POST",
			data: {
				id: infoid
			},
			success: function(msg) {
				console.log(msg);
				if (msg=='done')
				{
					$('#expert' + infoid).remove();
					$('.msgbox').html('<?=msg("success", "Successfuly deleted")?>');
				}
			}
		});
	}

</script>
<?php endif; ?>