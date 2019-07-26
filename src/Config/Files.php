<?php namespace Tatter\Files\Config;

use CodeIgniter\Config\BaseConfig;

class Files extends BaseConfig
{
	// whether to continue instead of throwing exceptions
	public $silent = true;
	
	// the session variable to check for a logged-in user ID
	public $userSource = 'logged_in';
	
	// views to display for each function
	public $views = [
		'header'    => 'Tatter\Files\Views\templates\header',
		'footer'    => 'Tatter\Files\Views\templates\footer',
		'dropzone'  => 'Tatter\Files\Views\dropzone\config',
	];
}
