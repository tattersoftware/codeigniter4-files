<?php

use Tests\Support\FeatureTestCase;
use Tests\Support\Fakers\FileFaker;

class UserTest extends FeatureTestCase
{
	public function testShowsOwnFiles()
	{
		$file = fake(FileFaker::class);
		$user = $this->login();

		model('FileModel')->addToUser($file->id, $user->id);

		$result = $this->get('files/user/' . $user->id);
		$result->assertSee('Manage My Files', 'h1');
		$result->assertSee($file->filename);
	}

	public function testShowsOtherFiles()
	{
		$file = fake(FileFaker::class);
		$user = $this->login();

		model('FileModel')->addToUser($file->id, $user->id);

		$result = $this->get('files/user/1000');
		$result->assertSee('Browse User Files', 'h1');
		$result->assertDontSee($file->filename);
	}
}
