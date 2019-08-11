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
	
	// Views to display for each function
	public $views = [
		'header'    => 'Tatter\Files\Views\templates\header',
		'footer'    => 'Tatter\Files\Views\templates\footer',
		'dropzone'  => 'Tatter\Files\Views\dropzone\config',
	];
	
	// Default display format; built in are 'cards', 'list', 'select'
	public $defaultFormat = 'cards';
}
