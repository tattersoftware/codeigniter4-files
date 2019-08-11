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
						<td><img src="<?= $file->thumbnail ?>" class="img-fluid rounded" alt="<?= $file->filename ?>" style="max-height:40px;"></td>
						<td class="align-middle"><?= $file->filename ?></td>
						<td class="align-middle"><?= $file->type ?></td>
						<td class="align-middle"><?= bytes2human($file->size) ?></td>
						<td class="align-middle"><?= $file->created_at->humanize(); ?></td>
						<td class="align-middle">
							<button class="btn btn-secondary btn-sm dropdown-toggle" type="button" id="export-<?= $file->id ?>" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
								<i class="fas fa-share-square"></i> Options
							</button>
							<div class="dropdown-menu" aria-labelledby="export-<?= $file->id ?>">
								<h6 class="dropdown-header">Send To</h6>
								<a class="dropdown-item" href="#">Preview</a>
								<a class="dropdown-item" href="<?= site_url('files/export/download/' . $file->id) ?>">Download</a>
		<?php if ($access == 'manage'): ?>
								<div class="dropdown-divider"></div>
								<h6 class="dropdown-header">Manage</h6>
								<a class="dropdown-item" href="<?= site_url('files/rename/' . $file->id) ?>">Rename</a>
								<a class="dropdown-item" href="<?= site_url('files/delete/' . $file->id) ?>">Delete</a>
		<?php endif; ?>
							</div>
						</td>
					</tr>
	<?php endforeach; ?>
				</tbody>
			</table>
<?php endif; ?>
