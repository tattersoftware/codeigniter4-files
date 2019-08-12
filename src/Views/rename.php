<?= view($config->views['header']) ?>

	<div class="row">
		<div class="col">
			<h1>Rename File</h1>
			<?= view('Tatter\Files\Views\\forms\rename') ?>
		</div>
	</div>

<?= view($config->views['footer'], ['config' => $config]) ?>
