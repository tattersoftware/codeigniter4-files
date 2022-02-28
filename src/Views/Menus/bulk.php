<?php
// Make sure there is something to display
if (empty($bulks) && $access === 'display') {
    return;
}
?>
	<button class="btn btn-primary btn-sm mb-3 dropdown-toggle float-right" type="button" id="export-bulk" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
		<i class="fas fa-share-square"></i> Actions
	</button>
	<div class="dropdown-menu" aria-labelledby="export-bulk">

		<?php if (! empty($bulks)): ?>

		<h6 class="dropdown-header">Send To</h6>

		<?php foreach ($bulks as $exporter): ?>
		<?php $attributes = $exporter::attributes(); ?>

		<?php if ($attributes['ajax']): ?>
		<input name="<?= $attributes['id'] ?>" type="submit" class="dropdown-item" value="<?= $attributes['name'] ?>" onclick="$('#globalModal .modal-body').load('<?= site_url('files/bulk/' . $attributes['id']) ?>'); $('#globalModal').modal(); return false;">
		<?php else: ?>
		<input name="<?= $attributes['id'] ?>" type="submit" class="dropdown-item" value="<?= $attributes['name'] ?>">
		<?php endif; ?>

		<?php endforeach; ?>

		<?php endif; ?>

		<?php if ($access === 'manage'): ?>

		<div class="dropdown-divider"></div>
		<h6 class="dropdown-header">Manage</h6>
		<input name="delete" type="submit" class="dropdown-item" value="Delete">

		<?php endif; ?>

	</div>
