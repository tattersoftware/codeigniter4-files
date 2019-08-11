<?php if (empty($files)): ?>
			<p>No files to display.</p>
<?php else: ?>
			<table class="table table-sm table-striped">
				<thead>
					<tr>
						<th scope="col"></th>
						<th scope="col">Filename</th>
						<th scope="col">Type</th>
						<th scope="col">Size</th>
						<th scope="col">Added</th>
					</tr>
				</thead>
				<tbody>
	<?php foreach ($files as $file): ?>
					<tr>
						<td><input name="file_<?= $file->id ?>" type="checkbox" class="class="form-check-input" value="1"></td>
						<td class="align-middle"><?= $file->filename ?></td>
						<td class="align-middle"><?= $file->type ?></td>
						<td class="align-middle"><?= bytes2human($file->size) ?></td>
						<td class="align-middle"><?= $file->created_at->humanize(); ?></td>
					</tr>
	<?php endforeach; ?>
				</tbody>
			</table>
<?php endif; ?>
