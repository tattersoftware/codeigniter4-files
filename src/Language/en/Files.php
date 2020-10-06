<?php

return [
	// Exceptions
	'noAuth'        => 'Missing dependency: authentication function user_id()',
	'dirFail'       => 'Unable to create storage directory: {0}',
	'chunkDirFail'  => 'Unable to create directory for chunk uploads: {0}',
	'noChunks'      => 'No valid files found for chunk merge in: {0}',
	'noFile'        => 'No file provided.',
	'newFileFail'   => 'Unable to create file for merging: {0}',
	'writeFileFail' => 'Unable to open file for merging: {0}',

	'uploadSuccess' => 'Upload of {0} was successful.',
	'exportSuccess' => '{0} export was successful.',
	'renameSuccess' => 'File renamed to {0}.',
	'deleteSuccess' => 'File deleted.',
];
