<?= $this->extend(config('Files')->layouts['public']) ?>
<?= $this->section('main') ?>

	<div class="row">
		<div class="col">
			<div class="btn-toolbar float-right" role="toolbar" aria-label="Toolbar with button groups">				
				<div class="btn-group mr-2" role="group" aria-label="Action group">
					<button type="button" class="btn btn-primary" data-toggle="modal" data-target="#dropzoneModal">
						<i class="fas fa-file-upload"></i> Add Files
					</button>
				</div>

				<div class="btn-group mr-2" role="group" aria-label="Format group">
					<a class="btn <?= $format === 'cards' ? 'btn-secondary' : 'btn-outline-secondary' ?>" href="<?= site_url("files/{$source}") ?>?format=cards" role="button"><i class="fas fa-th-large"></i></a>
					<a class="btn <?= $format === 'list' ? 'btn-secondary' : 'btn-outline-secondary' ?>" href="<?= site_url("files/{$source}") ?>?format=list" role="button"><i class="fas fa-list"></i></a>
					<a class="btn <?= $format === 'select' ? 'btn-secondary' : 'btn-outline-secondary' ?>" href="<?= site_url("files/{$source}") ?>?format=select" role="button"><i class="fas fa-tasks"></i></a>
				</div>
			</div>
			
			<h1><?= $access === 'manage' ? 'Manage' : 'Browse' ?> <?= $username ?? '' ?> Files</h1>
			
			<div id="files-wrapper">
				<?php if (empty($files)): ?>
				<p>
					You have no files! Would you like to
					<a class="dropzone-button" href="<?= site_url('files/new') ?>" data-toggle="modal" data-target="#dropzoneModal">add some now</a>?
				</p>

				<?php else: ?>
				<form name="files-form" method="post" action="<?= site_url('files/bulk') ?>">
					<?= $format === 'select' ? view('Tatter\Files\Views\Menus\bulk', ['access' => $access, 'bulks' => $bulks]) : '' ?>
					<?= view('Tatter\Files\Views\Formats\\' . $format, ['files' => $files, 'access' => $access, 'exports' => $exports]); ?>
				</form>
				<?php endif; ?>
			</div>
		</div>
	</div>

	<?= view('Tatter\Files\Views\Dropzone\modal') ?>

	<!-- Modal -->
	<div class="modal fade" id="globalModal" tabindex="-1" role="dialog" aria-labelledby="globalModalTitle" aria-hidden="true">
		<div class="modal-dialog modal-lg modal-dialog-centered" role="document">
			<div class="modal-content" style="max-height:600px;">
				<div class="modal-header">
					<h5 class="modal-title" id="globalModalTitle"></h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body overflow-auto"></div>
				<div class="modal-footer">
					<button type="button" class="btn btn-primary" data-dismiss="modal">Close</button>
				</div>
			</div>
		</div>
	</div>

	<?= view(config('Files')->views['dropzone']) ?>

<?= $this->endSection() ?>
