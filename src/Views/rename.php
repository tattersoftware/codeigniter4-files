<?php $this->extend(config('Layouts')->{$layout}) ?>
<?php $this->section('navbar') ?>

	<?= view('Tatter\Files\Views\navbar') ?>

<?php $this->endSection() ?>
<?php $this->section('main') ?>

	<div class="row">
		<div class="col">
			<h1>Rename File</h1>
			<?= view('Tatter\Files\Views\Forms\rename') ?>
		</div>
	</div>

<?php $this->endSection() ?>
