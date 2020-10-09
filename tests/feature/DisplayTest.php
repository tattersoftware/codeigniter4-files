<?php

use Config\Services;
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

	public function provideSort()
	{
		yield ['filename', 'filename'];
		yield ['localname', 'localname'];
		yield ['clientname', 'clientname'];
		yield ['type', 'type'];
		yield ['size', 'size'];
		yield ['thumbnail', 'thumbnail'];
		yield ['invalidsort', 'filename'];
	}

	/**
	 * @dataProvider provideSort
	 */
	public function testSorts(string $sort, string $configSort)
	{
		$_REQUEST['sort'] = $sort;

		$file = fake(FileFaker::class);
		$result = $this->get('files');

		$result->assertStatus(200);
		$this->assertEquals($configSort, service('settings')->filesSort);
	}
}
