
	<form name="file-rename" class="form-inline" action="<?= site_url('files/rename') ?>" method="post">
		<div class="form-group mr-2">
			<label for="filename" class="form-label mr-2">New filename:</label>
			<input name="filename" type="text" class="form-control" id="filename" value="<?= $file->filename ?>" onclick="this.select();" autofocus>
		</div>
		<div class="form-group">
			<input name="file_id" type="hidden" value="<?= $file->id ?>">
			<button type="submit" class="btn btn-primary">Submit</button>
		</div>
	</form>
