	<!-- DropzoneJS CSS -->
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.5.1/min/dropzone.min.css" integrity="sha256-e47xOkXs1JXFbjjpoRr1/LhVcqSzRmGmPqsrUQeVs+g=" crossorigin="anonymous" />
	<!-- DropzoneJS JavaScript -->
	<script src="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.5.1/min/dropzone.min.js" integrity="sha256-cs4thShDfjkqFGk5s2Lxj35sgSRr4MRcyccmi0WKqCM=" crossorigin="anonymous"></script>
	<script>
<?php
// Determine upload limit from PHP settings
helper('files');
$uploadLimitBytes = max_file_upload_in_bytes();

// Buffer chunks to be just under the limit (maintain bytes)
$chunkSize = $uploadLimitBytes - 1000;

// Limit files to the MB equivalent of 500 chunks
$maxFileSize = round($chunkSize * 500 / 1024 / 1024, 1);
?>
		Dropzone.options.filesDropzone = {
		
			// Reload file list after uploads
			init: function() {
				this.on("queuecomplete", function() {
					$("#files-wrapper").load('<?= current_url() ?>');
				});
			},
			
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
	</script>
