<?php

use Tatter\Files\Entities\File;
use Tatter\Files\Models\FileModel;
use Tests\Support\TestCase;

/**
 * @internal
 */
final class ModelTest extends TestCase
{
    /**
     * @var FileModel
     */
    protected $model;

    protected function setUp(): void
    {
        parent::setUp();

        $this->model = model(FileModel::class); // @phpstan-ignore-line
    }

    public function testAddToUser()
    {
        $this->model->addToUser(7, 42);

        $this->seeInDatabase('files_users', [
            'file_id' => 7,
            'user_id' => 42,
        ]);
    }

    public function testGetForUser()
    {
        $file1 = fake(FileModel::class);
        $file2 = fake(FileModel::class);

        $this->model->addToUser($file1->id, 10);
        $this->model->addToUser($file2->id, 10);

        $result = $this->model->getForUser(10);
        $this->assertCount(2, $result);

        $ids = array_column($result, 'id');

        $this->assertSame([$file1->id, $file2->id], $ids);
    }

    public function testGetForUserBuildsOnModelMethods()
    {
        $file1 = fake(FileModel::class);
        $file2 = fake(FileModel::class);

        $this->model->addToUser($file1->id, 11);
        $this->model->addToUser($file2->id, 11);

        $this->model->where(['filename' => $file2->filename]);
        $result = $this->model->getForUser(11);

        $this->assertCount(1, $result);
        $this->assertSame($file2->id, $result[0]->id);
    }

    public function testCreateFromPathReturnsFile()
    {
        $result = $this->model->createFromPath($this->testPath);

        $this->assertInstanceOf(File::class, $result);
    }

    public function testCreateFromPathAddsToDatabase()
    {
        $result = $this->model->createFromPath($this->testPath);

        $this->seeInDatabase('files', ['filename' => $result->filename]);
    }

    public function testCreateFromPathAssignsToUser()
    {
        $userId = 3;
        service('auth')->login($userId);

        $this->model->createFromPath($this->testPath);

        $result = $this->model->getForUser($userId);

        $this->assertCount(1, $result);
    }

    public function testCreateAddsThumbnail()
    {
        helper('filesystem');
        $file = $this->model->createFromPath($this->testPath);

        $thumbnail = pathinfo($file->localname, PATHINFO_FILENAME);
        $array     = $file->toRawArray();
        $this->assertSame($thumbnail, $array['thumbnail']);

        $path = config('Files')->getPath() . 'thumbnails' . DIRECTORY_SEPARATOR . $thumbnail;
        $this->assertFileExists($path);
    }

    public function testCreateUsesAdditionalData()
    {
        $file = $this->model->createFromPath($this->testPath, [
            'size' => 42,
        ]);

        $this->assertSame(42, $file->size);
    }
}
