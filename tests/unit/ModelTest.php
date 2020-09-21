<?php

use Tests\Support\Fakers\FileFaker;
use Tests\Support\FilesTestCase;

class ModelTest extends FilesTestCase
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

	public function testAddToUser()
	{
		$this->files->addToUser(7, 42);

		$this->seeInDatabase('files_users', [
			'file_id' => 7,
			'user_id' => 42,
		]);
	}

	public function testGetForUser()
	{
		$file1 = fake(FileFaker::class);
		$file2 = fake(FileFaker::class);

		$this->files->addToUser($file1->id, 10);
		$this->files->addToUser($file2->id, 10);

		$result = $this->files->getForUser(10);
		$this->assertCount(2, $result);
		
		$ids = array_column($result, 'id');
		
		$this->assertEquals([$file1->id, $file2->id], $ids);
	}
}
