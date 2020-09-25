<?php

use Tests\Support\Fakers\FileFaker;
use Tests\Support\FilesTestCase;

class ModelTest extends FilesTestCase
{
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
		$file1 = fake(FileFaker::class);
		$file2 = fake(FileFaker::class);

		$this->model->addToUser($file1->id, 10);
		$this->model->addToUser($file2->id, 10);

		$result = $this->model->getForUser(10);
		$this->assertCount(2, $result);
		
		$ids = array_column($result, 'id');
		
		$this->assertEquals([$file1->id, $file2->id], $ids);
	}
}
