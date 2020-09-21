<?php

use CodeIgniter\Files\File as CIFile;
use Tatter\Files\Entities\File;
use Tests\Support\Fakers\FileFaker;
use Tests\Support\FilesTestCase;

class EntityTest extends FilesTestCase
{
	/**
	 * @var FileModel
	 */
	protected $files;

	protected function setUp(): void
	{
		parent::setUp();

		$this->files = new FileFaker();
	}

	public function testGetThumbnailUsesDefault()
	{
		$expected = HOMEPATH . 'src/Views/thumbnail.jpg';
		$expected = realpath($expected) ?: $expected;

		$file = new File();

		$this->assertEquals($expected, $file->getThumbnail(true));
	}
}
