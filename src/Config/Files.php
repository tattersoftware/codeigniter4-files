<?php namespace Tatter\Files\Config;

use CodeIgniter\Config\BaseConfig;

class Files extends BaseConfig
{
	// Whether to continue instead of throwing exceptions
	public $silent = true;

	// Session variable to check for a logged-in user ID
	public $userSource = 'logged_in';

	// Directory to store files (with trailing slash)
	public $storagePath = ROOTPATH . 'writable/files/';

	// Layouts to use for general access and for administration
	public $layouts = [
		'public' => 'Tatter\Files\Views\layout',
		'manage' => 'Tatter\Files\Views\layout',
	];

	// Views to display for each function
	public $views = [
		'dropzone' => 'Tatter\Files\Views\dropzone\config',
	];

	// Default display format; built in are 'cards', 'list', 'select'
	public $defaultFormat = 'cards';
}
