<?php namespace Tatter\Files\Config;

use CodeIgniter\Config\BaseConfig;

class Files extends BaseConfig
{
	/**
	 * Session variable to check for a logged-in user ID
	 *
	 * @var string
	 */
	public $userSource = 'logged_in';

	/**
	 * Directory to store files (with trailing slash)
	 *
	 * @var string
	 */
	public $storagePath = ROOTPATH . 'writable/files/';

	/**
	 * Layouts to use for general access and for administration
	 *
	 * @var string[]
	 */
	public $layouts = [
		'public' => 'Tatter\Files\Views\layout',
		'manage' => 'Tatter\Files\Views\layout',
	];

	/**
	 * View file aliases
	 *
	 * @var string[]
	 */
	public $views = [
		'dropzone' => 'Tatter\Files\Views\dropzone\config',
	];

	/**
	 * Default display format; built in are 'cards', 'list', 'select'
	 *
	 * @var string
	 */
	public $defaultFormat = 'cards';

	/**
	 * Path to the default thumbnail file
	 *
	 * @var string
	 */
	public $defaultThumbnail = '\\Tatter\\Files\\Assets\\Unavailable.jpg';
}
