<div class="row">
	<ul class="faq-items">
		<?php foreach ($faqs as $faq): ?>
			<li class="faq-item" id="info<?=$faq['idInfo']?>">
				<div class="faq-title"><?=$faq['name']?></div>
				<div class="faq-descr"><?=$faq['description']?></div>
			</li>
		<?php endforeach; ?>
	</ul>
</div>