<?php

use Tests\Support\FilesTestCase;

/**
 * @internal
 */
final class SeederTest extends FilesTestCase
{
    /**
     * Note that the seeder has already been run during setUp()
     *
     * @dataProvider seederProvider
     *
     * @param mixed $key
     * @param mixed $default
     */
    public function testSeederCreatesSettings($key, $default)
    {
        $result = service('settings')->{$key};

        $this->assertSame($default, $result);
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
