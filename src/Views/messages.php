<?= $this->extend($config->layouts['public']) ?>
<?= $this->section('main') ?>

	<h2>Information</h2>

<?php
if (! empty($message)):
	?>
	<div class="alert alert-success">
		<?= $message ?>
	</div>
	<?php
endif;

if (! empty($error)):
	?>
	<div class="alert alert-danger">
		<?= $error ?>
	</div>
	<?php
endif;

if (! empty($errors)):
	?>
	<ul class="alert alert-danger">
	<?php
	foreach ($errors as $error):
		?>
		<li><?= $error ?></li>
		<?php
	endforeach;
	?>
	</ul>
	<?php
endif;
?>

<?= $this->endSection() ?>
