<?php

namespace Tatter\Files\Exceptions;

use CodeIgniter\Exceptions\ExceptionInterface;
use RuntimeException;

class FilesException extends RuntimeException implements ExceptionInterface
{
    public static function forDirFail($dir)
    {
        return new static(lang('Files.dirFail', [$dir]));
    }

    public static function forChunkDirFail($dir)
    {
        return new static(lang('Files.chunkDirFail', [$dir]));
    }

    public static function forNoChunks($dir)
    {
        return new static(lang('Files.noChunks', [$dir]));
    }

    public static function forNewFileFail($file)
    {
        return new static(lang('Files.newFileFail', [$file]));
    }

    public static function forWriteFileFail($file)
    {
        return new static(lang('Files.writeFileFail', [$file]));
    }
}
