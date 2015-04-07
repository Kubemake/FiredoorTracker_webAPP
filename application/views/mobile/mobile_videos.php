<div class="row">
	<ul class="faq-items">
		<?php foreach ($videos as $video): ?>
			<li class="video-item" id="info<?=$video['idInfo']?>">
				<div class="video-title"><a href="<?=$video['description']?>" target="_blank"><?=$video['name']?></a></div>
			</li>
		<?php endforeach; ?>
	</ul>
</div>
