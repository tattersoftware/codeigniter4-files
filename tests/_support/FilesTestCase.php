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
	 * The namespace(s) to help us find the migration classes.
	 * Empty is equivalent to running `spark migrate -all`.
	 * Note that running "all" runs migrations in date order,
	 * but specifying namespaces runs them in namespace order (then date)
	 *
	 * @var string|array|null
	 */
    protected $namespace = null;

	/**
	 * The seed file(s) used for all tests within this test case.
	 * Should be fully-namespaced or relative to $basePath
	 *
	 * @var string|array
	 */
	protected $seed = 'Tatter\Files\Database\Seeds\FileSeeder';

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

		$this->config              = new Files();
		$this->config->storagePath = SUPPORTPATH . 'storage/';

		$this->model = new FileFaker();
	}
}
