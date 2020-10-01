<?php

use CodeIgniter\Files\File as CIFile;
use Tatter\Files\Entities\File;
use Tests\Support\FilesTestCase;

class EntityTest extends FilesTestCase
{
	public function testGetPathReturnsAbsolutePath()
	{
		$file = $this->model->createFromPath($this->testPath);

		$expected = $this->config->storagePath . $file->localname;

		$this->assertEquals($expected, $file->getPath());
	}

	public function testGetThumbnailUsesDefault()
	{
		$expected = HOMEPATH . 'src/Assets/Unavailable.jpg';
		$expected = realpath($expected) ?: $expected;

		$file = new File();

		$this->assertEquals($expected, $file->getThumbnail());
	}
		
}
