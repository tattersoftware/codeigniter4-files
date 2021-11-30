<?php

use Tatter\Files\Entities\File;
use Tests\Support\FilesTestCase;

/**
 * @internal
 */
final class EntityTest extends FilesTestCase
{
    public function testGetPathReturnsAbsolutePath()
    {
        $file = $this->model->createFromPath($this->testPath);

        $expected = $this->config->storagePath . $file->localname;

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
