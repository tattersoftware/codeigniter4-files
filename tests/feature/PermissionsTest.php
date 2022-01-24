<?php

use CodeIgniter\Files\File;
use CodeIgniter\HTTP\Files\UploadedFile;
use Tatter\Permits\Config\Permits;
use Tests\Support\FeatureTestCase;

/**
 * @internal
 */
final class PermissionsTest extends FeatureTestCase
{
    public function testListsFiles()
    {
        $this->model->setPermits([
            'list' => Permits::ANYBODY,
        ]);

        [$user, $file] = $this->createUserWithFile();

        $result = $this->withSession()->get('files');
        $result->assertStatus(200);
        $result->assertSee($file->filename);
    }

    public function testListsOwnFiles()
    {
        $this->model->setPermits([
            'list' => Permits::NOBODY,
        ]);

        [$user, $userFile]   = $this->createUserWithFile();
        [$owner, $ownerFile] = $this->createUserWithFile();
        service('auth')->login($owner);

        $result = $this->withSession()->get('files');
        $result->assertStatus(200);
        $result->assertSee($ownerFile->filename);
        $result->assertDontSee($userFile->filename);
    }

    public function testAdminListsFiles()
    {
        $this->model->setPermits([
            'list' => Permits::NOBODY,
        ]);

        [$user, $userFile]   = $this->createUserWithFile();
        [$admin, $adminFile] = $this->createUserWithFile();
        $admin->permissions  = ['files.admin'];
        service('auth')->login($admin);

        $result = $this->withSession()->get('files');

        $result->assertStatus(200);
        $result->assertSee($adminFile->filename);
        $result->assertSee($userFile->filename);
    }

    public function testDenyListRedirects()
    {
        $this->model->setPermits([
            'list' => Permits::NOBODY,
        ]);
        $result = $this->get('files');

        $result->assertStatus(302);
        $result->assertSessionHas('error', lang('Permits.notPermitted'));
    }

    public function testDenyAjaxReturnsError()
    {
        $this->model->setPermits([
            'list' => Permits::NOBODY,
        ]);
        $result = $this->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])->get('files');

        $result->assertStatus(403);
        $result->assertJSONFragment(['error' => lang('Permits.notPermitted')]);
    }

    public function testUploadMissingFile()
    {
        [$user, ] = $this->createUserWithFile();
        service('auth')->login($user);

        $result = $this->withSession()
            ->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
            ->post('files/upload');
        $result->assertStatus(400);
    }

    public function testUploadInvalidFile()
    {
        [$user, ] = $this->createUserWithFile();
        service('auth')->login($user);

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
}
