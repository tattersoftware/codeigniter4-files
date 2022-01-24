<?php

use Tatter\Files\Entities\File;
use Tests\Support\TestCase;

/**
 * @internal
 */
final class EntityTest extends TestCase
{
    protected $refreshVfs = true;

    public function testGetPathReturnsAbsolutePath()
    {
        $file = $this->model->createFromPath($this->testPath);

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
