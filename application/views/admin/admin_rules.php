<div class="container-fluid">
	<div class="row">
		<div class="col-md-12">
			<form method="POST">
				<table class="table table-striped table-hover table-bordered table-responsive table-condensed" width="100%">
					<tr>
						<th>Rule\Role</th>
					<?php foreach ($roles as $role_id => $role): ?>
						<th><?=$role['name']?></th>
					<?php endforeach; ?>
					</tr>

					<?php foreach ($rulesroles as $rule_id => $rule): ?>
						<tr>
							<td><strong><?=$rules[$rule_id]['name']?></strong><br /><?=$rules[$rule_id]['description']?></td>
							<?php foreach ($roles as $role_id => $role): ?>
								<td><input type="checkbox" name="rr[<?=$rule_id?>][<?=$role_id?>]" <?=($rule[$role_id] == 1) ? 'checked="checked"' : ''?> /></td>
							<?php endforeach; ?>
						</tr>
					<?php endforeach; ?>
				</table>
				<div class="form-group text-center">
					<button type="submit" class="btn btn-default">Save permissions</button>
				</div>
			</form>
		</div>
	</div>
</div>