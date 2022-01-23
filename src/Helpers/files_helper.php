<?php

namespace Tatter\Files\Helpers;

use Tatter\Files\Exceptions\FilesException;

if (! function_exists('bytes2human')) {
    /**
     * Converts bytes to a human-friendly format.
     */
    function bytes2human(int $bytes): string
    {
        $unit = 'bytes';
        if ($bytes > 1024) {
            $bytes /= 1024;
            $unit = 'KB';
        }
        if ($bytes > 1024) {
            $bytes /= 1024;
            $unit = 'MB';
        }
        if ($bytes > 1024) {
            $bytes /= 1024;
            $unit = 'GB';
        }
        if ($bytes > 1024) {
            $bytes /= 1024;
            $unit = 'TB';
        }
        if ($bytes > 1024) {
            $bytes /= 1024;
            $unit = 'PB';
        }

        return round($bytes, 1) . ' ' . $unit;
    }
}
if (! function_exists('max_file_upload_in_bytes')) {
    /**
     * Determines the maximum allowed file size for uploads.
     * Thanks to Thanks to AoEmaster (https://stackoverflow.com/users/1732818/aoemaster)
     *
     * @see    https://stackoverflow.com/questions/2840755/how-to-determine-the-max-file-upload-limit-in-php
     */
    function max_file_upload_in_bytes(): int
    {
        // Select maximum upload size
        $max_upload = return_bytes(ini_get('upload_max_filesize'));

        // Select post limit
        $max_post = return_bytes(ini_get('post_max_size'));

        // Select memory limit
        $memory_limit = return_bytes(ini_get('memory_limit'));

        // Return the smallest of them, this defines the real limit
        return min($max_upload, $max_post, $memory_limit);
    }
}
if (! function_exists('merge_file_chunks')) {
    /**
     * Merges the given file chunks into a single file
     * and returns the new path.
     *
     * @throws FilesException
     */
    function merge_file_chunks(string ...$files): string
    {
        // Create the temp file
        $prefix  = bin2hex(random_bytes(16));
        $tmpfile = tempnam(sys_get_temp_dir(), $prefix);

        // Open temp file for writing
        $output = @fopen($tmpfile, 'ab');
        if (! $output) {
            throw FilesException::forNewFileFail($tmpfile); // @codeCoverageIgnore
        }

        // Write each chunk to the temp file
        foreach ($files as $file) {
            $input = @fopen($file, 'rb');
            if (! $input) {
                throw FilesException::forWriteFileFail($tmpfile);
            }

            // Buffered merge of chunk
            while ($buffer = fread($input, 4096)) {
                fwrite($output, $buffer);
            }

            fclose($input);
        }

        // close output handle
        fclose($output);

        return $tmpfile;
    }
}
if (! function_exists('return_bytes')) {
    /**
     * Converts ini-style sizes to bytes.
     */
    function return_bytes(string $value): int
    {
        $value = strtolower(trim($value));
        $unit  = $value[strlen($value) - 1];
        $num   = (int) rtrim($value, $unit);

        switch ($unit) {
            case 'g': $num *= 1024;
            // no break
            case 'm': $num *= 1024;
            // no break
            case 'k': $num *= 1024;

            // If it is not one of those modifiers then it was numerical bytes, add the final digit back
            // no break
            default:
                $num = (int) ($num . $unit);
        }

        return $num;
    }
}
