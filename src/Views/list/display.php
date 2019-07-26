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
			<ul>
	<?php foreach ($files as $file): ?>
				<li><?= $file->summary ?></li>
	<?php endforeach; ?>
			</ul>
<?php endif; ?>
			
		</div>
	</div>

<?= view($config->views['footer']) ?>
