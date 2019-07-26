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
				<div class="card">
					<img src="<?= $file->thumbnail ?>" class="card-img-top img-thumbnail" alt="<?= $file->filename ?>">
					<div class="card-body">
						<h5 class="card-title"><?= $file->filename ?></h5>
						<p class="card-text"><?= $file->type ?></p>
						<a href="#" class="btn btn-primary">Download</a>
					</div>
				</div>
	<?php endforeach; ?>
			</div>
<?php endif; ?>
			
		</div>
	</div>

<?= view($config->views['footer'], ['config' => $config]) ?>
