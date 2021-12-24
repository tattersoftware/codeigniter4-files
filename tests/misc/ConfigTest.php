<?php

use Tatter\Files\Exceptions\FilesException;
use Tests\Support\TestCase;

/**
 * @internal
 */
final class ConfigTest extends TestCase
{
    protected $migrate = false;

    public function testThrowsWithInvalidStoragePath()
    {
        $path = HOMEPATH . 'README.md';
        config('Files')->setPath($path);

        $this->expectException(FilesException::class);
        $this->expectExceptionMessage(lang('Files.dirFail', [$path]));

        config('Files')->getPath();
    }

    public function testCreatesMissingStoragePath()
    {
        $config = config('Files');

        $path = $config->getPath() . 'subdirectory/';
        $config->setPath($path);

        $config->getPath();

        $this->assertDirectoryExists($path);
        $this->assertDirectoryIsWritable($path);
    }
}
