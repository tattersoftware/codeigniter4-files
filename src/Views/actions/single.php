<?php
// Determine eligible exports for this file
$extension = pathinfo($file->filename, PATHINFO_EXTENSION);
$matched = [];

// Universally-available exports
if (isset($exports['*']))
	$matched = array_merge($matched, $exports['*']);
// Exports specific to this extension
if (isset($exports[$extension]))
	$matched = array_merge($matched, $exports[$extension]);

// Make sure there is something to display
if (empty($matched) && $access == 'display')
	return;
?>
	<button class="btn btn-secondary btn-sm dropdown-toggle" type="button" id="export-<?= $file->id ?>" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
		<i class="fas fa-share-square"></i> Actions
	</button>
	<div class="dropdown-menu" aria-labelledby="export-<?= $file->id ?>">

<?php if (! empty($matched)): ?>
		<h6 class="dropdown-header">Send To</h6>
	<?php foreach ($matched as $export): ?>
		<?php if ($export->ajax): ?>
		<a class="dropdown-item" href="<?= site_url('files/export/' . $export->uid . '/' . $file->id) ?>" onclick="$('#globalModal .modal-body').load('<?= site_url('files/export/' . $export->uid . '/' . $file->id) ?>'); $('#globalModal').modal(); return false;"><?= $export->name ?></a>
		
		<?php else: ?>
		<a class="dropdown-item" href="<?= site_url('files/export/' . $export->uid . '/' . $file->id) ?>"><?= $export->name ?></a>
		
		<?php endif; ?>
	<?php endforeach; ?>
<?php endif; ?>

<?php if ($access == 'manage'): ?>
		<div class="dropdown-divider"></div>
		<h6 class="dropdown-header">Manage</h6>
		<a class="dropdown-item" href="<?= site_url('files/rename/' . $file->id) ?>" onclick="$('#globalModal .modal-body').load('<?= site_url('files/rename/' . $file->id) ?>'); $('#globalModal').modal(); return false;">Rename</a>
		<a class="dropdown-item" href="<?= site_url('files/delete/' . $file->id) ?>">Delete</a>
<?php endif; ?>

	</div>
