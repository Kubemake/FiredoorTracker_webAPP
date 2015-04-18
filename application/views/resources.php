<!-- <div class="container-fluid"> -->
	<div class="row">
		<div class="col-lg-7 col-md-12">
			<div class="row glossary"><h2>GLOSSARY</h2><?php if (has_permission('Allow modify resources')): ?><a href="javascript:;" onclick="button_add_action('glossary');return false;" class="btn btn-default button-add">Add</a><?php endif; ?></div>
			<div class="row">
				<div id="letters">
					<ul class="list-inline text-center">
						<?php foreach ($letters as $letter): ?>
							<?php if (isset($letter_available[$letter])): ?>
								<li><a href="/resources/<?=$letter?>"><?=$letter?></a></li>
							<?php else: ?>
								<li><?=$letter?></li>
							<?php endif; ?>
						<?php endforeach; ?>
					</ul>
				</div>
			</div>
			<div class="row">
				<ul class="glossary-items">
					<?php foreach ($glossary as $glos): ?>
						<li class="glossary-item" id="info<?=$glos['idInfo']?>">
							<div class="glos-title"><?=$glos['name']?><?php if (has_permission('Allow modify resources')): ?> ( <a data-id="<?=$glos['idInfo']?>" href="javascript:;" onclick="edit_action(this, 'glossary');return false;">Edit</a> | <a data-id="<?=$glos['idInfo']?>" href="javascript:;" onclick="delete_action(this);return false;">Delete</a> )<?php endif; ?></div>
							<div class="glos-descr"><?=$glos['description']?></div>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>
		</div>
		<div class="col-lg-5 col-md-12">
			<div class="row faq"><h2>FREQUENTLY ASKED QUESTIONS (FAQ)</h2><?php if (has_permission('Allow modify resources')): ?><a href="javascript:;" onclick="button_add_action('faq');return false;" class="btn btn-default button-add">Add</a><?php endif; ?></div>
			<div class="row">
				<ul class="faq-items">
					<?php foreach ($faqs as $faq): ?>
						<li class="faq-item" id="info<?=$faq['idInfo']?>">
							<div class="faq-title"><?=$faq['name']?><?php if (has_permission('Allow modify resources')): ?> ( <a data-id="<?=$faq['idInfo']?>" href="javascript:;" onclick="edit_action(this, 'faq');return false;">Edit</a> | <a data-id="<?=$faq['idInfo']?>" href="javascript:;" onclick="delete_action(this);return false;">Delete</a> )<?php endif; ?></div>
							<div class="faq-descr"><?=$faq['description']?></div>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>

			<div class="row faq"><h2>VIDEO TUTORIALS</h2><?php if (has_permission('Allow modify resources')): ?><a href="javascript:;" onclick="button_add_action('video');return false;" class="btn btn-default button-add">Add</a><?php endif; ?></div>
			<div class="row">
				<ul class="faq-items">
					<?php foreach ($videos as $video): ?>
						<li class="video-item" id="info<?=$video['idInfo']?>">
							<div class="video-title"><a href="<?=$video['description']?>" target="_blank"><?=$video['name']?></a><?php if (has_permission('Allow modify resources')): ?> ( <a data-id="<?=$video['idInfo']?>" href="javascript:;" onclick="edit_action(this, 'video');return false;">Edit</a> | <a data-id="<?=$video['idInfo']?>" href="javascript:;" onclick="delete_action(this);return false;">Delete</a> )<?php endif; ?></div>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>
		</div>
	<!-- </div> -->
</div>

<?php if (has_permission('Allow modify resources')): ?>
<script type="text/javascript">
	function button_add_action(type)
	{
		$('#modalacceptor').empty().load("/ajax/ajax_load_modal",{page: 'add_info_modal', type: type},function(){$('#AddInfoModal').modal({show: true})});
		
	}

	function edit_action(e, type)
	{
		infoid = $(e).data('id');
		$('#modalacceptor').empty().load("/ajax/ajax_load_modal",{page: 'edit_info_modal', id: infoid, type: type},function(){$('#EditInfoModal').modal({show: true})});
	}

	function delete_action(e)
	{
		infoid = $(e).data('id');
		if (!confirm('Sure?')) return false;
		$.ajax({
			url: "/resources/ajax_delete_info",
			type: "POST",
			data: {
				id: infoid
			},
			success: function(msg) {
				console.log(msg);
				if (msg=='done')
				{
					$('#info' + infoid).remove();
					$('.msgbox').html('<div class="alert alert-success alert-dismissable"><button class="close" aria-hidden="true" data-dismiss="alert" type="button"></button>Successfuly deleted</div>');
				}
			}
		});
	}

</script>
<?php endif; ?>