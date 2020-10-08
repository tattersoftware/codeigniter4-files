	<h6><?= $file->filename ?></h6>
	<form name="file-rename" class="form-inline" action="<?= site_url('files/rename') ?>" method="post">
		<div class="form-group mr-2">
			<input name="filename" type="text" class="form-control" id="filename" value="<?= $file->filename ?>" autofocus>
		</div>
		<div class="form-group">
			<input name="file_id" type="hidden" value="<?= $file->id ?>">
			<button type="submit" class="btn btn-primary">Rename</button>
		</div>
	</form>
