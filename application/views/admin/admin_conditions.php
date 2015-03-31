<div class="row">
	<ul class="nav nav-tabs">
		<li<?php if ($current_wall_rate_id == 1) echo ' class="active"'; ?>><a href="/admin/conditions/1">1h Fire Wall</a></li>
		<li<?php if ($current_wall_rate_id == 2) echo ' class="active"'; ?>><a href="/admin/conditions/2">2h Fire Wall</a></li>
		<li<?php if ($current_wall_rate_id == 3) echo ' class="active"'; ?>><a href="/admin/conditions/3">3h Fire Wall</a></li>
		<li<?php if ($current_wall_rate_id == 4) echo ' class="active"'; ?>><a href="/admin/conditions/4">Smoke Wall</a></li>
		<li<?php if ($current_wall_rate_id == 5) echo ' class="active"'; ?>><a href="/admin/conditions/5">Wall Not Rated</a></li>
	</ul>
</div>
<div class="row">
	<?php
	$statuscolor = array(
		1 => 'color-8',
		2 => 'color-2',
		3 => 'color-9',
		4 => 'color-5',
		5 => 'color-10'
	);
	$doorMatherialcolor = array(
		1 => 'color-2',
		2 => 'color-3',
		3 => 'color-4',
		4 => 'color-5',
		5 => 'color-6',
		6 => 'color-7'
	);
	?>
	<table id="conditions" class="table table-striped table-hover table-bordered table-responsive table-condensed" width="100%">
	<thead>
		<tr>
			<td rowspan="3">Issues</td>
			<?php foreach ($choices as $ratesTypesId => $ratesType): //make 1part of table head?>
				<td class="color-1" colspan="<?=$table_rows_colspan[$ratesTypesId]?>"><?=$param['rates_types'][$ratesTypesId]?></td>
			<?php endforeach;?>
		</tr>
		<tr>
			<?php foreach ($choices as $ratesTypesId => $ratesType): //make 2part of table head?>
				<?php foreach ($ratesType as $doorMatherialid => $doorMatherial): //make 2part of table head?>
					<td class="<?=@$doorMatherialcolor[$doorMatherialid]?>" colspan="<?=count($doorMatherial)?>"><?=$param['door_matherial'][$doorMatherialid]?></td>
				<?php endforeach;?>
			<?php endforeach;?>
		</tr>
		<tr>
			<?php foreach ($choices as $ratesTypesId => $ratesType): //make 2part of table head?>
				<?php foreach ($ratesType as $doorMatherialid => $doorMatherial): //make 2part of table head?>
					<?php foreach ($doorMatherial as $doorRatingId => $doorRating): //make 2part of table head?>
						<td><?=$param['door_rating'][$doorRatingId]?></td>
					<?php endforeach; ?>
				<?php endforeach; ?>
			<?php endforeach; ?>
		</tr>
	</thead>
	<tbody>
		<?php for ($i=0; $i < count($order); $i++): ?>
			<tr>
				<td style="padding-left: <?=($issues[$order[$i]]['level']*20)?>px;"><?=$issues[$order[$i]]['label']?></td>
				<?php foreach ($choices as $ratesTypesId => $ratesType): ?>
					<?php foreach ($ratesType as $doorMatherialid => $doorMatherial): ?>
						<?php foreach ($doorMatherial as $doorRatingId => $doorRating): ?>
							<td 
								data-wall_rate_id="<?=$current_wall_rate_id?>" 
								data-id="<?=$order[$i]?>" 
								data-ratesTypesId="<?=$ratesTypesId?>" 
								data-doorMatherialid="<?=$doorMatherialid?>" 
								data-doorRatingId="<?=$doorRatingId?>" 
								data-thisvalue="<?=@$choices_rows[$order[$i]][$ratesTypesId][$doorMatherialid][$doorRatingId]?>" 
								class="clicktochange <?=@$statuscolor[$choices_rows[$order[$i]][$ratesTypesId][$doorMatherialid][$doorRatingId]]?>">
									<?=@$param['door_state'][$choices_rows[$order[$i]][$ratesTypesId][$doorMatherialid][$doorRatingId]] ?>
							</td>
						<?php endforeach; ?>
					<?php endforeach; ?>
				<?php endforeach; ?>
				<!-- print_r($choices_rows[$order[$i]]);die(); ?> -->
			</tr>
		<?php endfor; ?>
	</tbody>
	</table>
</div>

<script type="text/javascript">
	$(function(){
		$('body').css('width', ($('#conditions').width()+50)+'px');

		$('td').dblclick(function(){ //edit value on double click
			params = $(this).data();
			// console.log(params);
			$('#modalacceptor').empty().load("/ajax/ajax_load_modal",{page: 'choose_condition_value_modal',id:params.id, wall_rate_id:params.wall_rate_id, ratestypesid:params.ratestypesid, doormatherialid:params.doormatherialid, doorratingid:params.doorratingid, thisvalue:params.thisvalue},function(){$('#ChooseCondtionValueModal').modal({show: true})});
		});
	});
</script>