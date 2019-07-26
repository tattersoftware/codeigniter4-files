<?php
// Determine upload limit from PHP settings
helper('upload');
$uploadLimitBytes = max_file_upload_in_bytes();

// Buffer chunks to be just under the limit (maintain bytes)
$chunkSize = $uploadLimitBytes - 1000;

// Limit files to the MB equivalent of 500 chunks
$maxFileSize = $chunkSize * 500 / 1024 / 1024;
?>
Dropzone.options.filesDropzone = {
	
	// Maximum file size in MB
	maxFilesize: <?= $maxFileSize ?>,
	
	// Disable parallel uploads
	// (CodeIgniter4 Sessions chokes on interspersed AJAX calls)
	parallelUploads: 1,
	
	// Enable chunking
	chunking: true,
	chunkSize: <?= $chunkSize ?>, // bytes
	retryChunks: true,
	retryChunksLimit: 3,
	
	// When chunking include chunk data as POST fields
	params: function(files, xhr, chunk) {
		return chunk ? { uuid: chunk.file.upload.uuid, totalChunks: chunk.file.upload.totalChunkCount, chunkIndex: chunk.index } : null;
	}
};
