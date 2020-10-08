<?php

use Tatter\Files\Entities\File;
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

	public function testGetForUserBuildsOnModelMethods()
	{
		$file1 = fake(FileFaker::class);
		$file2 = fake(FileFaker::class);

		$this->model->addToUser($file1->id, 10);
		$this->model->addToUser($file2->id, 10);

		$this->model->where(['filename' => $file2->filename]);
		$result = $this->model->getForUser(10);

		$this->assertCount(1, $result);
		$this->assertEquals($file2->id, $result[0]->id);
	}

	public function testCreateFromPathReturnsFile()
	{
		$result = $this->model->createFromPath($this->testPath);

		$this->assertInstanceOf(File::class, $result);
	}

	public function testCreateFromPathAddsToDatabase()
	{
		$result = $this->model->createFromPath($this->testPath);

		$this->seeInDatabase('files', ['filename' => $result->filename]);
	}

	public function testCreateFromPathAssignsToUser()
	{
		$user = $this->login();

		$this->model->createFromPath($this->testPath);

		$result = $this->model->getForUser($user->id);

		$this->assertCount(1, $result);
	}
}
