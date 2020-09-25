<?php namespace Tests\Support;

use CodeIgniter\Test\CIDatabaseTestCase;
use Tatter\Files\Config\Files;
use Tests\Support\Fakers\FileFaker;

class FilesTestCase extends CIDatabaseTestCase
{
	/**
	 * Should the database be refreshed before each test?
	 *
	 * @var boolean
	 */
	protected $refresh = true;

	/**
	 * The namespace to help us find the migration classes.
	 *
	 * @var string
	 */
	protected $namespace = 'Tatter\Files';

	/**
	 * Configuration
	 *
	 * @var Files
	 */
	protected $config;

	/**
	 * @var FileFaker
	 */
	protected $model;

	/**
	 * A test file to work with
	 *
	 * @var string
	 */
	protected $testFile = SUPPORTPATH . 'image.jpg';

	protected function setUp(): void
	{
		parent::setUp();

		$this->config = new Files();
		$this->config->storagePath = SUPPORTPATH . 'storage/';

		$this->model = new FileFaker();
	}
}
