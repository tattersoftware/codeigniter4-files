<?php

namespace Tests\Support;

use CodeIgniter\Config\Factories;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use org\bovigo\vfs\vfsStream;
use Tatter\Assets\Test\AssetsTestTrait;
use Tatter\Files\Config\Files;
use Tatter\Files\Models\FileModel;
use Tatter\Imposter\Entities\User;
use Tatter\Imposter\Factories\ImposterFactory;
use Tatter\Users\UserProvider;

/**
 * @internal
 */
abstract class TestCase extends CIUnitTestCase
{
    use AssetsTestTrait;
    use DatabaseTestTrait;

    protected $refreshVfs = false;
    protected $refresh    = false;
    protected $namespace;

    /**
     * Path to a test file to work with
     */
    protected string $testPath;

    protected FileModel $model;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        helper(['auth', 'files', 'preferences']);
        UserProvider::addFactory(ImposterFactory::class, ImposterFactory::class);
    }

    protected function setUp(): void
    {
        $this->resetServices();

        parent::setUp();

        $_POST    = [];
        $_GET     = [];
        $_FILES   = [];
        $_REQUEST = [];

        // Make sure all files are on a single page
        $_REQUEST['perPage'] = 200;

        // Force Files config to the virtual path
        $path   = self::$root->url() . DIRECTORY_SEPARATOR;
        $config = config('Files');
        $config->setPath($path);
        Factories::injectMock('config', 'Files', $config);

        // Copy the files to VFS (if necessary)
        $this->testPath = $config->getPath() . 'image.jpg';
        if (! is_file($this->testPath)) {
            vfsStream::copyFromFileSystem(SUPPORTPATH . 'VFS', self::$root);
        }

        // Set up the model
        $this->model = model(FileModel::class); // @phpstan-ignore-line
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        ImposterFactory::reset();
    }

    /**
     * Creates a test User with some files.
     *
     * @return array Tuple of [User, File]
     */
    protected function createUserWithFile(): array
    {
        $user     = ImposterFactory::fake();
        $user->id = ImposterFactory::add($user);

        $file = fake(FileModel::class);
        $this->model->addToUser($file->id, $user->id);

        return [$user, $file];
    }
}
