<?php

use Tatter\Files\Models\FileModel;
use Tests\Support\FeatureTestCase;

/**
 * @internal
 */
final class UserTest extends FeatureTestCase
{
    public function testShowsOwnFiles()
    {
        $file   = fake(FileModel::class);
        $userId = 7;
        service('auth')->login($userId);

        /** @var FileModel $model */
        $model = model(FileModel::class);
        $model->addToUser($file->id, $userId);

        $result = $this->get('files/user/' . $userId);
        $result->assertSee('Manage My Files', 'h1');
        $result->assertSee($file->filename);
    }

    public function testShowsOtherFiles()
    {
        $file   = fake(FileModel::class);
        $userId = 13;
        service('auth')->login($userId);

        /** @var FileModel $model */
        $model = model(FileModel::class);
        $model->addToUser($file->id, $userId);

        $result = $this->get('files/user/1000');
        $result->assertSee('Browse User Files', 'h1');
        $result->assertDontSee($file->filename);
    }
}
