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

	public function provideOrder()
	{
		yield ['asc', 'asc'];
		yield ['desc', 'desc'];
		yield ['invalid', 'asc'];
	}

	/**
	 * @dataProvider provideOrder
	 */
	public function testOrders(string $order, string $configOrder)
	{
		$_REQUEST['order'] = $order;

		$file = fake(FileFaker::class);
		$result = $this->get('files');

		$result->assertStatus(200);
		$this->assertEquals($configOrder, service('settings')->filesOrder);
	}

	public function provideSearch()
	{
		yield ['Heathcote'];
	}

	/**
	 * @dataProvider provideSearch
	 */
	public function testSearches(string $keyword)
	{
		$_REQUEST['search'] = $keyword;

		$file = fake(FileFaker::class);
		$result = $this->get('files');

		$result->assertStatus(200);
		$content = $result->response->getBody();

		if (strpos($content, $keyword) !== false) {
			$result->assertSee($keyword);
		} else {
			$result->assertSee('You have no files!');
		}
	}
}
