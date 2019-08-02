<?= view($config->views['header']) ?>

	<div class="row">
		<div class="col">
			<div class="float-right">
				<button type="button" class="btn btn-primary" data-toggle="modal" data-target="#dropzoneModal">
					<i class="fas fa-file-upload"></i> Add Files
				</button>
			</div>
			
			<h1>My Files</h1>

<?php if (empty($files)): ?>
			<p>
				You have no files! Would you like to
				<a class="dropzone-button" href="<?= site_url('files/new') ?>" data-toggle="modal" data-target="#dropzoneModal">add some now</a>?
			</p>

<?php else: ?>
			<div class="card-deck">
	<?php foreach ($files as $file): ?>
				<div class="card mb-4" style="min-width: 10rem; max-width: 200px;">
					<img src="<?= $file->thumbnail ?>" class="card-img-top img-thumbnail" alt="<?= $file->filename ?>">
					<div class="card-header">
						<button class="btn btn-secondary btn-sm dropdown-toggle <?= rand(0,1)==1 ? 'disabled' : '' ?>" type="button" id="export-<?= $file->id ?>" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
							<i class="fas fa-share-square"></i> Export
						</button>
						<div class="dropdown-menu" aria-labelledby="export-<?= $file->id ?>">
							<h6 class="dropdown-header">Send To</h6>
							<a class="dropdown-item" href="#">Preview</a>
							<a class="dropdown-item" href="<?= site_url('files/export/download/' . $file->id) ?>">Download</a>
							<div class="dropdown-divider"></div>
							<h6 class="dropdown-header">Manage</h6>
							<a class="dropdown-item" href="#">Rename</a>
							<a class="dropdown-item" href="#">Delete</a>
						</div>
					</div>
					<div class="card-body">
						<h6 class="card-title"><?= bytes2human($file->size) ?></h6>
						<p class="card-text"><?= $file->filename ?></p>
					</div>
					<div class="card-footer">
						<small class="text-muted">Added <?= $file->created_at->humanize(); ?></small>
					</div>
				</div>
	<?php endforeach; ?>
			</div>
<?php endif; ?>
			
		</div>
	</div>

<?= view($config->views['footer'], ['config' => $config]) ?>
