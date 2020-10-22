
	<?php if (empty($files)): ?>
	<p>No files to display.</p>
	<?php else: ?>
	<div class="card-deck">

		<?php foreach ($files as $file): ?>
		<div class="card mb-4" style="min-width: 10rem; max-width: 200px;">
			<img src="<?= img_data($file->getThumbnail()) ?>" class="card-img-top img-thumbnail" alt="<?= $file->filename ?>">
			<div class="card-header">
				<?= view('Tatter\Files\Views\Menus\single', ['file' => $file, 'access' => $access]) ?>
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
