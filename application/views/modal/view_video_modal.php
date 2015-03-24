<div class="modal fade" id="v-file-link" tabindex="-1" role="dialog" aria-labelledby="v-file-link" aria-hidden="true">
	<div class="modal-dialog modal-md">
		<div class="modal-content">
				<div class="modal-header">
					<button class="close" aria-hidden="true" data-dismiss="modal" type="button">×</button>
					<h4 class="modal-title text-center"><?=$title?></h4>
				</div>
				<div class="modal-body">
					<div class="row pad15 text-center">
						<a href="<?=$remote?>" style="display: inline-block; width: 500px; height: 400px;" id="upload-result"></a>
						<script type="text/javascript">flowplayer("upload-result", {src : "/js/flowplayer/flowplayer-3.2.2.swf", wmode: "transparent"});</script>
					</div>
				</div>
			</form>
		</div>
	</div>
</div>