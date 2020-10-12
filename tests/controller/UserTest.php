<?php

use CodeIgniter\Test\ControllerTester;
use Tatter\Files\Controllers\Files;
use Tests\Support\Fakers\FileFaker;
use Tests\Support\FilesTestCase;

class UserTest extends FilesTestCase
{
	use ControllerTester;

	public function testShowsOwnFiles()
	{
		$file = fake(FileFaker::class);
		$user = $this->login();

		$result = $this->controller(Files::class)
						->execute('user', $user->id);
		$this->assertTrue($result->see($file->filename));
	}
}
