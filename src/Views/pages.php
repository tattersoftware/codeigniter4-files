
	<div class="row">
		<div class="col-sm-6">
			<?= $pager->only(['search'])->links('default', 'files_bootstrap') ?>
		</div>
		<div class="col-sm-4"></div>
		<div class="col-2 float-right">
			<form name="files-pages" method="get" action="<?= current_url() ?>">
				<label class="sr-only" for="perPage">Per page</label>
				<select class="form-control" name="perPage" id="perPage" onchange="this.form.submit();">
					<?php foreach ([5, 10, 25, 50, 100, 200] as $num): ?>
					<option value="<?= $num ?>" <?= $num === $perPage ? 'selected' : '' ?>><?= $num ?></option>
					<?php endforeach; ?>
				</select>
			</form>
		</div>
	</div>