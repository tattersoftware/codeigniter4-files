<?php

if (! function_exists('bytes2human'))
{
	/**
	 * Converts bytes to a human-friendly format.
	 *
	 * @param integer $bytes
	 *
	 * @return string
	 */
	function bytes2human(int $bytes): string
	{
		$unit = 'bytes';
		if ($bytes > 1024)
		{
			$bytes /= 1024;
			$unit   = 'KB';
		}
		if ($bytes > 1024)
		{
			$bytes /= 1024;
			$unit   = 'MB';
		}
		if ($bytes > 1024)
		{
			$bytes /= 1024;
			$unit   = 'GB';
		}
		if ($bytes > 1024)
		{
			$bytes /= 1024;
			$unit   = 'TB';
		}
		if ($bytes > 1024)
		{
			$bytes /= 1024;
			$unit   = 'PB';
		}

		return round($bytes, 1) . ' ' . $unit;
	}
}

if (! function_exists('max_file_upload_in_bytes'))
{
	/**
	 * Determines the maximum allowed file size for uploads.
	 * Thanks to Thanks to AoEmaster (https://stackoverflow.com/users/1732818/aoemaster)
	 *
	 * @return integer
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

if (! function_exists('return_bytes'))
{
	/**
	 * Converts ini-style sizes to bytes.
	 *
	 * @param string $value
	 *
	 * @return integer
	 */
	function return_bytes(string $value): int
	{
		$value = strtolower(trim($value));
		$unit  = $value[strlen($value) - 1];
		$num   = (int)rtrim($value, $unit);

		switch ($unit)
		{
			case 'g': $num *= 1024;
			case 'm': $num *= 1024;
			case 'k': $num *= 1024;
		}
		return $num;
	}
}
