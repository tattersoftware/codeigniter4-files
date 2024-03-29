<?php

use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class HelperTest extends CIUnitTestCase
{
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        helper(['files']);
    }

    /**
     * @dataProvider bytesProvider
     *
     * @param mixed $bytes
     * @param mixed $expected
     */
    public function testBytesToHuman($bytes, $expected)
    {
        $this->assertSame($expected, bytes2human($bytes));
    }

    public function bytesProvider()
    {
        return [
            [1, '1 bytes'],
            [1024, '1024 bytes'],
            [1025, '1 KB'],
            [1024 * 1024, '1024 KB'],
            [1024 * 1025, '1 MB'],
            [1024 * 1024 * 1024, '1024 MB'],
            [1024 * 1024 * 1025, '1 GB'],
            [1024 * 1024 * 1024 * 1024, '1024 GB'],
            [1024 * 1024 * 1024 * 1025, '1 TB'],
            [1024 * 1024 * 1024 * 1024 * 1024, '1024 TB'],
            [1024 * 1024 * 1024 * 1024 * 1025, '1 PB'],
        ];
    }

    /**
     * @dataProvider iniProvider
     *
     * @param mixed $ini
     * @param mixed $expected
     */
    public function testReturnBytes($ini, $expected)
    {
        $this->assertSame($expected, return_bytes($ini));
    }

    public function iniProvider()
    {
        return [
            ['1', 1],
            ['1025', 1025],
            ['1k', 1024],
            ['1m', 1024 * 1024],
            ['1g', 1024 * 1024 * 1024],
        ];
    }

    public function testMergeFileChunks()
    {
        $path = merge_file_chunks(
            SUPPORTPATH . 'chunks/0',
            SUPPORTPATH . 'chunks/1',
            SUPPORTPATH . 'chunks/2',
            SUPPORTPATH . 'chunks/3'
        );

        $this->assertIsString($path);
        $this->assertFileExists($path);

        $expected = 'All your base are belong to us.';
        $result   = file_get_contents($path);

        $this->assertSame($expected, $result);
    }
}
