<?php

namespace Tests\Support;

use CodeIgniter\Config\Factories;
use CodeIgniter\Publisher\Publisher;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use Tatter\Assets\Asset;
use Tatter\Files\Config\Files;

/**
 * @internal
 */
abstract class TestCase extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $refresh = false;
    protected $namespace;

    /**
     * Configuration
     *
     * @var Files
     */
    protected $config;

    /**
     * @var vfsStreamDirectory|null
     */
    protected $root;

    /**
     * Whether the publishers have been run.
     *
     * @var bool
     */
    private $published = false;

    /**
     * Path to a test file to work with
     *
     * @var string
     */
    protected $testPath;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        helper(['auth', 'preferences']);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $_REQUEST = [];
        $_POST    = [];
        $_GET     = [];

        // Create the virtual filesystem
        $this->root = vfsStream::setup();
        vfsStream::copyFromFileSystem(SUPPORTPATH . 'vfs/', $this->root);

        // Force our config to the virtual path
        $path         = $this->root->url() . '/storage/';
        $this->config = config('Files');
        $this->config->setPath($path);
        Factories::injectMock('config', 'Files', $this->config);

        $this->testPath = $path . 'image.jpg';

        // Configure Assets for the VFS
        $assets                = config('Assets');
        $assets->directory     = $path;
        $assets->useTimestamps = false; // These make testing much harder

        Asset::useConfig($assets);

        // Add VFS as an allowed Publisher directory
        config('Publisher')->restrictions[$assets->directory] = '*';
    }

    /**
     * Publishes all files once so they are
     * available for bundles.
     */
    protected function publishAll(): void
    {
        if ($this->published) {
            return;
        }

        foreach (Publisher::discover() as $publisher) {
            $publisher->publish();
        }

        $this->published = true;
    }
}
