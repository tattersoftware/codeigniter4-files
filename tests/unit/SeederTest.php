<?php

use Tests\Support\FilesTestCase;

class SeederTest extends FilesTestCase
{
 	/**
 	 * Note that the seeder has already been run during setUp()
 	 *
 	 * @dataProvider seederProvider
 	 */
	public function testSeederCreatesSettings($key, $default)
	{
		$result = service('settings')->$key;

		$this->assertEquals($default, $result);
	}

	public function seederProvider()
	{
		return [
			['filesFormat', 'cards'],
			['filesSort', 'filename'],
			['filesOrder', 'asc'],
		];			
	}
}
