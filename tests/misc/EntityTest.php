<?php

use Tatter\Files\Entities\File;
use Tatter\Files\Models\FileModel;
use Tests\Support\TestCase;

/**
 * @internal
 */
final class EntityTest extends TestCase
{
    public function testGetPathReturnsAbsolutePath()
    {
        /** @var FileModel $model */
        $model = model(FileModel::class);
        $file  = $model->createFromPath($this->testPath);

        $expected = config('Files')->getPath() . $file->localname;

        $this->assertSame($expected, $file->getPath());
    }

    public function testGetThumbnailUsesDefault()
    {
        $expected = HOMEPATH . 'src/Assets/Unavailable.jpg';
        $expected = realpath($expected) ?: $expected;

        $file = new File();

        $this->assertSame($expected, $file->getThumbnail());
    }
}
