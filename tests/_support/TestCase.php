<?php

namespace Tests\Support;

use CodeIgniter\Config\Factories;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use org\bovigo\vfs\vfsStream;
use Tatter\Assets\Test\AssetsTestTrait;
use Tatter\Files\Config\Files;

/**
 * @internal
 */
abstract class TestCase extends CIUnitTestCase
{
    use AssetsTestTrait;
    use DatabaseTestTrait;

    protected $refresh = false;
    protected $namespace;

    /**
     * Path to a test file to work with
     */
    protected string $testPath;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        helper(['auth', 'files', 'preferences']);
    }

    protected function setUp(): void
    {
        $this->resetServices();

        parent::setUp();

        $_REQUEST = [];
        $_POST    = [];
        $_GET     = [];
        $_FILES   = [];

        // Copy the files to VFS
        vfsStream::copyFromFileSystem(SUPPORTPATH . 'VFS', $this->root);

        // "vendor" gets ignored by .gitignore so rename it after copying to VFS
        $path = $this->root->url() . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR;
        rename($this->root->url() . '/storage', $path);
        $this->testPath = $path . 'image.jpg';

        // Force Files config to the virtual path
        $config = config('Files');
        $config->setPath($path);
        Factories::injectMock('config', 'Files', $config);
    }
}
