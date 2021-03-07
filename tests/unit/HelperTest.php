<?php

use Tatter\Files\Entities\File;
use Tests\Support\Fakers\FileFaker;
use Tests\Support\FilesTestCase;

class HelperTest extends FilesTestCase
{

	public static function setUpBeforeClass(): void
	{
		parent::setUpBeforeClass();

		helper('files');
	}

	/**
	 * @dataProvider bytesProvider
	 */
	public function testBytesToHuman($bytes, $expected)
	{
		$this->assertEquals($expected, bytes2human($bytes));
	}

	public function bytesProvider()
	{
		return [
			[1, '1 bytes'],
			[1024, '1024 bytes'],
			[1025, '1 KB'],
			[1024*1024, '1024 KB'],
			[1024*1025, '1 MB'],
			[1024*1024*1024, '1024 MB'],
			[1024*1024*1025, '1 GB'],
			[1024*1024*1024*1024, '1024 GB'],
			[1024*1024*1024*1025, '1 TB'],
			[1024*1024*1024*1024*1024, '1024 TB'],
			[1024*1024*1024*1024*1025, '1 PB'],
		];
	}

	/**
	 * @dataProvider iniProvider
	 */
	public function testReturnBytes($ini, $expected)
	{
		$this->assertEquals($expected, return_bytes($ini));
	}

	public function iniProvider()
	{
		return [
			['1', 1],
			['1025', 1025],
			['1k', 1024],
			['1m', 1024*1024],
			['1g', 1024*1024*1024],
		];
	}
}
