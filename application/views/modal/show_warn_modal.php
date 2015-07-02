<!-- Show Warn Modal -->
<div class="modal fade" id="ShowWarnModal" tabindex="-1" role="dialog" aria-labelledby="ShowWarnModal" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
				<h4 class="modal-title text-center" id="myModalLabel"><?=$title?></h4>
			</div>
			<div class="modal-body">
				<?=$text?>
			</div>
			<div class="modal-footer">
				<?=@$actionbutton?>
				<?=@$canselbutton?>
			</div>
		</div>
	</div>
</div>
