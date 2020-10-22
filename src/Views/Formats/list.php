
	<?php if (empty($files)): ?>
	<p>No files to display.</p>
	<?php else: ?>
	<table class="table table-striped">
		<thead>
			<tr>
				<th scope="col"></th>
				<th scope="col">Filename</th>
				<th scope="col">Type</th>
				<th scope="col">Size</th>
				<th scope="col">Added</th>
				<th scope="col">Options</th>
			</tr>
		</thead>
		<tbody>

			<?php foreach ($files as $file): ?>
			<tr>
				<td><img src="<?= img_data($file->getThumbnail()) ?>" class="img-fluid rounded" alt="<?= $file->filename ?>" style="max-height:40px;"></td>
				<td class="align-middle"><?= $file->filename ?></td>
				<td class="align-middle"><?= $file->type ?></td>
				<td class="align-middle"><?= bytes2human($file->size) ?></td>
				<td class="align-middle"><?= $file->created_at->humanize(); ?></td>
				<td class="align-middle">
					<?= view('Tatter\Files\Views\Menus\single', ['file' => $file, 'access' => $access]) ?>
				</td>
			</tr>
			<?php endforeach; ?>

		</tbody>
	</table>
	<?php endif; ?>
