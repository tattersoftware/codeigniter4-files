<?php

use Myth\Auth\Test\Fakers\UserFaker;
use Tatter\Files\Controllers\Files;
use Tatter\Files\Exceptions\FilesException;
use Tests\Support\FeatureTestCase;
use Tests\Support\Fakers\FileFaker;

class DisplayTest extends FeatureTestCase
{
	public function testNoFiles()
	{
		$result = $this->get('files');

		$result->assertStatus(200);
		$result->assertSee('You have no files');
	}

	public function testDefaultDisplaysCards()
	{
		$file = fake(FileFaker::class);
		
		$result = $this->get('files');

		$result->assertStatus(200);
		$result->assertSee($file->filename);
	}
}
