<?php

namespace Tests\Support;

use CodeIgniter\Config\Factories;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use Config\Services;
use Myth\Auth\Entities\User;
use Myth\Auth\Test\AuthTestTrait;
use Myth\Auth\Test\Fakers\UserFaker;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use Tatter\Assets\Asset;
use Tatter\Files\Config\Files;
use Tests\Support\Fakers\FileFaker;

/**
 * @internal
 */
abstract class FilesTestCase extends CIUnitTestCase
{
    use AuthTestTrait;
    use DatabaseTestTrait;

    /**
     * Should the database be refreshed before each test?
     *
     * @var bool
     */
    protected $refresh = true;

    /**
     * The namespace(s) to help us find the migration classes.
     * Empty is equivalent to running `spark migrate -all`.
     * Note that running "all" runs migrations in date order,
     * but specifying namespaces runs them in namespace order (then date)
     *
     * @var array|string|null
     */
    protected $namespace;

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
     * Path to a test file to work with
     *
     * @var string
     */
    protected $testPath;

    protected function setUp(): void
    {
        parent::setUp();
        helper(['auth', 'preferences']);
        $this->resetAuthServices();

        $_REQUEST = [];
        $_POST    = [];
        $_GET     = [];

        $this->model = new FileFaker();

        // Start the virtual filesystem
        $this->root = vfsStream::setup();
        vfsStream::copyFromFileSystem(SUPPORTPATH . 'vfs/', $this->root);

        // Force our config to the virtual path
        $this->config              = new Files();
        $this->config->storagePath = $this->root->url() . '/storage/';
        Factories::injectMock('config', 'Files', $this->config);

        $this->testPath = $this->config->storagePath . 'image.jpg';

        // Configure Assets for the VFS
        $config                = config('Assets');
        $config->directory     = $this->config->storagePath;
        $config->useTimestamps = false; // These make testing much harder

        Asset::useConfig($config);

        // Add VFS as an allowed Publisher directory
        config('Publisher')->restrictions[$config->directory] = '*';
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->root = null;
    }

    /**
     * Login a user, optionally created at random.
     *
     * @param int $userId
     *
     * $return User
     */
    protected function login(?int $userId = null): User
    {
        // Get or create the user
        $user = $userId ? model(Models\UserModel::class)->find($userId) : fake(UserFaker::class);

        $_SESSION['logged_in'] = $user->id;

        $auth = Services::authentication();
        $auth->login($user);
        Services::injectMock('authentication', $auth);

        return $user;
    }

    /**
     * Create a random user and give it some random files.
     *
     * @param array $data      Overriding array of user data for the faker
     * @param int   $fileCount Number of files to create
     */
    protected function createUserWithFiles(array $data = [], int $fileCount = 2): User
    {
        // Create the user
        $user = fake(UserFaker::class, $data);

        // Create files and assign them to the user
        for ($i = 0; $i < abs($fileCount); $i++) {
            $file = fake(FileFaker::class);

            $this->model->addToUser($file->id, $user->id);
        }

        return $user;
    }
}
