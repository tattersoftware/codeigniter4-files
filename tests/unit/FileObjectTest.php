<?php

use Tatter\Files\Structures\FileObject;
use Tests\Support\FilesTestCase;

class FileObjectTest extends FilesTestCase
{
	public function testSetBasename()
	{
		$name = 'foo.bar';
		$file = new FileObject($this->testPath);
		$file->setBasename($name);

		$result = $this->getPrivateProperty($file, 'basename');

		$this->assertEquals($name, $result);
	}

	public function testGetBasenameUsesDefault()
	{
		$file = new FileObject($this->testPath);

		$this->assertEquals('image.jpg', $file->getBasename());
	}

	public function testGetBasenameUsesOverride()
	{
		$name = 'foo.bar';
		$file = new FileObject($this->testPath);
		$file->setBasename($name);

		$this->assertEquals($name, $file->getBasename());
	}

	public function testGetBasenameOverrideRespectsSuffix()
	{
		$name = 'foo.bar';
		$file = new FileObject($this->testPath);
		$file->setBasename($name);

		$this->assertEquals('foo', $file->getBasename('.bar'));
	}
}
