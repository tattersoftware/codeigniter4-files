<?php

use Tatter\Exports\Factories\ExporterFactory;

// Gather applicable exporters
$exporters = ExporterFactory::getAttributesForExtension($file->getExtension());

// Make sure there is something to display
if ($exporters === [] && $access === 'display') {
    return;
}
?>
	<button class="btn btn-secondary btn-sm dropdown-toggle" type="button" id="export-<?= $file->id ?>" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
		<i class="fas fa-share-square"></i> Actions
	</button>
	<div class="dropdown-menu" aria-labelledby="export-<?= $file->id ?>">

		<?php if ($exporters !== []): ?>
		<h6 class="dropdown-header">Send To</h6>
		<?php foreach ($exporters as $attributes): ?>
		<?php if ($attributes['ajax']): ?>

		<a class="dropdown-item"
			href="<?= site_url('files/export/' . $attributes['id'] . '/' . $file->id) ?>"
			onclick="
				$('#globalModal .modal-body').load('<?= site_url('files/export/' . $attributes['id'] . '/' . $file->id) ?>');
				$('#globalModal').modal('show');
				return false;"
		><?= $attributes['name'] ?></a>

		<?php else: ?>
		<a class="dropdown-item"
			href="<?= site_url('files/export/' . $attributes['id'] . '/' . $file->id) ?>"
		><?= $attributes['name'] ?></a>

		<?php endif; ?>
		<?php endforeach; ?>
		<?php endif; ?>

		<?php if ($access === 'manage'): ?>
		<div class="dropdown-divider"></div>
		<h6 class="dropdown-header">Manage</h6>
		<a class="dropdown-item"
			href="<?= site_url('files/rename/' . $file->id) ?>"
			onclick="
				$('#globalModal .modal-body').load('<?= site_url('files/rename/' . $file->id) ?>');
				$('#globalModal').modal();
				return false;"
		>Rename</a>

		<a class="dropdown-item"
			href="<?= site_url('files/delete/' . $file->id) ?>"
		>Delete</a>
		<?php endif; ?>

	</div>
