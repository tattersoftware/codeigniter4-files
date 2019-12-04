<?= $this->extend($config->layouts['public']) ?>
<?= $this->section('main') ?>

	<div class="row">
		<div class="col">
			<h1>Rename File</h1>
			<?= view('Tatter\Files\Views\\forms\rename') ?>
		</div>
	</div>

<?= $this->endSection() ?>
