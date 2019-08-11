	<button class="btn btn-secondary btn-sm dropdown-toggle" type="button" id="export-<?= $file->id ?>" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
		<i class="fas fa-share-square"></i> Options
	</button>
	<div class="dropdown-menu" aria-labelledby="export-<?= $file->id ?>">
		<h6 class="dropdown-header">Send To</h6>
		<a class="dropdown-item" href="#">Preview</a>
<?php
// Universally available exports
if (isset($exports['*'])):
	foreach ($exports['*'] as $export):
?>
		<a class="dropdown-item" href="<?= site_url('files/export/' . $export->uid . '/' . $file->id) ?>"><?= $export->name ?></a>
<?php
	endforeach;
endif;

// Exports specific to this extension
$extension = pathinfo($file->filename, PATHINFO_EXTENSION);
if (isset($exports[$extension])):
	foreach ($exports[$extension] as $export):
?>
		<a class="dropdown-item" href="<?= site_url('files/export/' . $export->uid . '/' . $file->id) ?>"><?= $export->name ?></a>
<?php
	endforeach;
endif;
?>
	<?php if ($access == 'manage'): ?>
		<div class="dropdown-divider"></div>
		<h6 class="dropdown-header">Manage</h6>
		<a class="dropdown-item" href="<?= site_url('files/rename/' . $file->id) ?>" onclick="$('#globalModal .modal-body').load('<?= site_url('files/rename/' . $file->id) ?>'); $('#globalModal').modal(); return false;">Rename</a>
		<a class="dropdown-item" href="<?= site_url('files/delete/' . $file->id) ?>">Delete</a>
	<?php endif; ?>
	</div>
