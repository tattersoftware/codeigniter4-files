<?php $this->extend(config('Layouts')->{$layout}) ?>
<?php $this->section('navbar') ?>

	<?= view('Tatter\Files\Views\navbar') ?>

<?php $this->endSection() ?>
<?php $this->section('main') ?>

	<?= view('Tatter\Files\Views\Dropzone\modal') ?>

<?php $this->endSection() ?>
<?php $this->section('footerAssets') ?>

	<?= view(config('Files')->views['dropzone']) ?>

<script>
$(function() {
	$('#dropzoneModal').modal('show');
});
</script>

<?php $this->endSection() ?>
