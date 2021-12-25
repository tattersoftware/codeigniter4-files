<?php

use Tatter\Files\Structures\FileObject;
use Tests\Support\TestCase;

/**
 * @internal
 */
final class FileObjectTest extends TestCase
{
    public function testSetBasename()
    {
        $name = 'foo.bar';
        $file = new FileObject($this->testPath);
        $file->setBasename($name);

        $result = $this->getPrivateProperty($file, 'basename');

        $this->assertSame($name, $result);
    }

    public function testGetBasenameUsesDefault()
    {
        $file = new FileObject($this->testPath);

        $this->assertSame('image.jpg', $file->getBasename());
    }

    public function testGetBasenameUsesOverride()
    {
        $name = 'foo.bar';
        $file = new FileObject($this->testPath);
        $file->setBasename($name);

        $this->assertSame($name, $file->getBasename());
    }

    public function testGetBasenameOverrideRespectsSuffix()
    {
        $name = 'foo.bar';
        $file = new FileObject($this->testPath);
        $file->setBasename($name);

        $this->assertSame('foo', $file->getBasename('.bar'));
    }
}
