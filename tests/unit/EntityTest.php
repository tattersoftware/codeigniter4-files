<?php

use CodeIgniter\Files\File as CIFile;
use Tatter\Files\Entities\File;
use Tests\Support\FilesTestCase;

class EntityTest extends FilesTestCase
{
	public function testGetThumbnailUsesDefault()
	{
		$expected = HOMEPATH . 'src/Assets/Unavailable.jpg';
		$expected = realpath($expected) ?: $expected;

		$file = new File();

		$this->assertEquals($expected, $file->getThumbnail());
	}
}
