<?php
$menu = $menu ?? '';
?><!doctype html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<meta name="description" content="">
	<meta name="author" content="Matthew Gatner">
	<title><?= $title ?? 'Files' ?></title>

	<?= service('assets')->tag('vendor/jquery/jquery.min.js') ?>
	<?= service('assets')->tag('vendor/bootstrap/bootstrap.min.css') ?>
	<?= service('assets')->tag('vendor/font-awesome/css/all.min.css') ?>
	<?= service('assets')->tag('vendor/dropzone/dropzone.min.css') ?>

	<?= service('alerts')->css() ?>

	<?= $this->renderSection('headerAssets') ?>

</head>
<body>
	<nav class="navbar navbar-expand-md navbar-dark bg-dark">
		<a class="navbar-brand" href="<?= site_url() ?>">Home</a>
		<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbars" aria-controls="navbars" aria-expanded="false" aria-label="Toggle navigation">
			<span class="navbar-toggler-icon"></span>
		</button>

		<div class="collapse navbar-collapse" id="navbars">
			<ul class="navbar-nav mr-auto">
				<li class="nav-item <?= ($menu === 'index') ? 'active' : '' ?>">
					<a class="nav-link" href="<?= site_url('files') ?>">All Files <?= ($menu === 'index') ? '<span class="sr-only">(current)</span>' : '' ?></a>
				</li>
				<li class="nav-item <?= ($menu === 'user') ? 'active' : '' ?>">
					<a class="nav-link" href="<?= site_url('files/user') ?>">User Files <?= ($menu === 'user') ? '<span class="sr-only">(current)</span>' : '' ?></a>
				</li>
				<li class="nav-item <?= ($menu === 'new') ? 'active' : '' ?>">
					<a class="nav-link" href="<?= site_url('files/new') ?>" data-toggle="modal" data-target="#dropzoneModal">Add Files</a>
				</li>
			</ul>
		</div>
	</nav>

	<?= service('alerts')->display() ?>

	<main role="main" class="container my-5">

		<?= $this->renderSection('main') ?>

	</main><!-- /.container -->

	<?= service('assets')->tag('vendor/bootstrap/bootstrap.bundle.min.js') ?>
	<?= service('assets')->tag('vendor/dropzone/dropzone.min.js') ?>

	<?= $this->renderSection('footerAssets') ?>

</body>
</html>
