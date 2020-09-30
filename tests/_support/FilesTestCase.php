<?php namespace Tests\Support;

use CodeIgniter\Config\Config;
use CodeIgniter\Test\CIDatabaseTestCase;
use Tatter\Files\Config\Files;
use Tests\Support\Fakers\FileFaker;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;

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
	 * @var vfsStreamDirectory|null
	 */
	protected $root;

	/**
	 * A test file to work with
	 *
	 * @var string
	 */
	protected $testFile;

	protected function setUp(): void
	{
		parent::setUp();
		Config::reset();
		helper('auth');

		$_REQUEST    = [];
		$this->model = new FileFaker();

		// Start the virtual filesystem
		$this->root = vfsStream::setup();
        vfsStream::copyFromFileSystem(SUPPORTPATH . 'storage/', $this->root);

		// Force our config to the virtual path
		$this->config              = new Files();
		$this->config->storagePath = $this->root->url() . '/storage/';
		Config::injectMock('Files', $this->config);

		$this->testFile = $this->config->storagePath . 'image.jpg';
	}

	protected function tearDown(): void
	{
		parent::tearDown();

		$this->root = null;
	}
}
