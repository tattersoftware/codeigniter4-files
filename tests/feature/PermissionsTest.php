<?php

use CodeIgniter\Config\Factories;
use CodeIgniter\Files\File;
use CodeIgniter\HTTP\Files\UploadedFile;
use Tatter\Files\Models\FileModel;
use Tatter\Permits\Models\PermitModel;
use Tests\Support\FeatureTestCase;

/**
 * @internal
 */
final class PermissionsTest extends FeatureTestCase
{
    /**
     * A user with files and no special permissions.
     */
    private const USER_ID = 101;

    /**
     * A user with files and global read access.
     */
    private const SUPER_ID = 102;

    /**
     * A user with files and full access.
     */
    private const ADMIN_ID = 103;

    /**
     * A user without files but list access.
     */
    private const PROCTOR_ID = 104;

    protected FileModel $model;
    protected $seeded = false;

    /**
     * Creates some test files and users with different permissions.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->model = model(FileModel::class); // @phpstan-ignore-line

        if (! $this->seeded) {
            // Set permissions
            model(PermitModel::class)->insertBatch([
                [
                    'name'    => 'listFiles',
                    'user_id' => self::USER_ID,
                ],
                [
                    'name'    => 'listFiles',
                    'user_id' => self::PROCTOR_ID,
                ],
                [
                    'name'    => 'listFiles',
                    'user_id' => self::SUPER_ID,
                ],
                [
                    'name'    => 'readFiles',
                    'user_id' => self::SUPER_ID,
                ],
                [
                    'name'    => 'adminFiles',
                    'user_id' => self::ADMIN_ID,
                ],
            ]);

            foreach ([self::USER_ID, self::SUPER_ID, self::ADMIN_ID, self::PROCTOR_ID] as $userId) {
                $this->createUserFiles($userId);
            }

            $this->seeded = true;
        }

        // Make sure all files are on a single page
        $_REQUEST['perPage'] = 200;
    }

    /**
     * Creates random files for a user.
     *
     * @param int $userId User ID to own the files
     * @param int $count  Number of files to create
     */
    protected function createUserFiles(int $userId, int $count = 2)
    {
        // Create files and assign them to the user
        for ($i = 0; $i < abs($count); $i++) {
            $file = fake(FileModel::class);

            $this->model->addToUser($file->id, $userId);
        }
    }

    /**
     * Injects a permission mode into the shared FileModel.
     *
     * @param int $mode Octal mode
     */
    protected function setMode(int $mode)
    {
        $this->model->setMode($mode);

        Factories::injectMock('models', FileModel::class, $this->model);
    }

    //--------------------------------------------------------------------

    public function testDefaultAccessListsAllFiles()
    {
        $this->assertSame(04660, $this->model->getMode());
        $result = $this->get('files');
        $result->assertStatus(200);

        $files = $this->model->getForUser(self::SUPER_ID);
        $result->assertSee($files[0]->filename);

        $files = $this->model->getForUser(self::ADMIN_ID);
        $result->assertSee($files[0]->filename);
    }

    public function testDenyListRedirects()
    {
        $this->setMode(00660);
        $result = $this->get('files');

        $result->assertStatus(302);
        $result->assertSessionHas('error', lang('Permits.notPermitted'));
    }

    public function testDenyAjaxReturnsError()
    {
        $this->setMode(00660);
        $result = $this->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])->get('files');

        $result->assertStatus(403);
        $result->assertJSONFragment(['error' => lang('Permits.notPermitted')]);
    }

    public function testAuthenticatedAddOnlyEmptyFile()
    {
        $this->setMode(04664);
        service('auth')->login(self::ADMIN_ID);

        $result = $this->withSession()
            ->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
            ->post('files/upload');
        $result->assertStatus(400);
    }

    public function testAuthenticatedAddOnlyWithInvalidFile()
    {
        $this->setMode(04664);
        service('auth')->login(self::ADMIN_ID);

        $_FILES = [
            'file' => [
                'name'     => 'someFile.txt',
                'type'     => 'text/plain',
                'size'     => '124',
                'tmp_name' => '/tmp/myTempFile.txt',
                'error'    => 0,
            ],
        ];

        $result = $this->withSession()
            ->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
            ->post('files/upload');
        $result->assertSee('The file uploaded with success.(0)');
    }

    /*
        public function testAuthenticatedAddOnlyWithValidFile()
        {
            $this->setMode(04664);
            service('auth')->login(self::ADMIN_ID);

            $_FILES = [
                'file' => [
                    'name'     => 'file.txt',
                    'type'     => 'text/plain',
                    'size'     => '33',
                    'tmp_name' => 'tests/_support/vfs/file.txt',
                    'error'    => 0,
                ],
            ];

            p\redefine(UploadedFile::class . '::isValid', static function () {
                return true;
            });

            p\redefine(File::class . '::move', static function ($args) {
                return new File('tests/_support/vfs/file.txt');
            });

            $this->modelClass = get_class($this->model);
            p\redefine($this->modelClass . '::createFromPath', static function ($args) {
                return fake(FileModel::class);
            });

            $result = $this->withSession()
                ->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
                ->post('files/upload');

            $this->assertSame('', $result->response()->getBody());
        }
    */
    public function testProctorListsAllFiles()
    {
        $this->createUserFiles(self::ADMIN_ID);

        $this->setMode(00660);
        service('auth')->login(self::PROCTOR_ID);

        $result = $this->withSession()->get('files');
        $result->assertStatus(200);

        $files = $this->model->getForUser(self::ADMIN_ID);
        $result->assertSee($files[0]->filename);
    }

    public function testAuthenticatedListOwnOnly()
    {
        $this->setMode(00660);
        service('auth')->login(self::PROCTOR_ID);

        $fileOwnByProctor = fake(FileModel::class);
        $this->model->addToUser($fileOwnByProctor->id, self::PROCTOR_ID);

        $fileOwnByProctor2 = fake(FileModel::class);
        $this->model->addToUser($fileOwnByProctor2->id, self::PROCTOR_ID);

        $fileOwnByAdmin = fake(FileModel::class);
        $this->model->addToUser($fileOwnByAdmin->id, self::ADMIN_ID);

        $result = $this->withSession()->get('files/user/' . self::PROCTOR_ID);
        $result->assertStatus(200);

        $files = $this->model->getForUser(self::PROCTOR_ID);
        $result->assertSee($files[0]->filename);
        $result->assertSee($files[1]->filename);

        $files = $this->model->getForUser(self::ADMIN_ID);
        $result->assertDontSee($files[0]->filename);
    }

    /**
     * @dataProvider accessProvider
     */
    public function testAdminAccess(int $mode)
    {
        $this->setMode($mode);
        service('auth')->login(self::ADMIN_ID);

        $result = $this->withSession()->get('files');
        $result->assertStatus(200);

        $files = $this->model->getForUser(self::ADMIN_ID);
        $result->assertSee($files[0]->filename);
        $files = $this->model->getForUser(self::USER_ID);
        $result->assertSee($files[0]->filename);
    }

    public function accessProvider()
    {
        return [
            ['read' => 00444],
            ['write'              => 00222],
            ['execute'            => 00111],
            ['read write execute' => 00777],
        ];
    }
}
