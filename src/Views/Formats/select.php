
	<?php if (empty($files)): ?>
	<p>No files to display.</p>
	<?php else: ?>
	<?php $selected = $selected ?? []; ?>
	<table class="table table-sm table-striped">
		<thead>
			<tr>
				<th scope="col">Filename</th>
				<th scope="col">Type</th>
				<th scope="col">Size</th>
				<th scope="col">Added</th>
			</tr>
		</thead>
		<tbody>

			<?php foreach ($files as $file): ?>
			<tr>
				<td>
					<div class="form-check">
						<input class="form-check-input"
							name="file<?= $file->id ?>"
							id="file<?= $file->id ?>"
							type="checkbox"
							value="<?= $file->id ?>"
							<?= in_array($file->id, $selected) ? 'checked' : '' ?>
						>
						<label class="form-check-label" for="file<?= $file->id ?>"><?= $file->filename ?></label>
					</div>
				</td>
				<td class="align-middle"><?= $file->type ?></td>
				<td class="align-middle"><?= bytes2human($file->size) ?></td>
				<td class="align-middle"><?= $file->created_at->humanize(); ?></td>
			</tr>
			<?php endforeach; ?>

		</tbody>
	</table>
	<?php endif; ?>
