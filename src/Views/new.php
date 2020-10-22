<?= $this->extend(config('Files')->layouts[$layout ?? 'public']) ?>
<?= $this->section('main') ?>

	<?= view('Tatter\Files\Views\Dropzone\modal') ?>

<?= $this->endSection() ?>
<?= $this->section('footerAssets') ?>

	<?= view(config('Files')->views['dropzone']) ?>

<script>
$(function() {
	$('#dropzoneModal').modal('show');
});
</script>

<?= $this->endSection() ?>
