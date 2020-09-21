<?php
// Outputs a human-friendly conversion of bytes
if (! function_exists('bytes2human'))
{
	function bytes2human($bytes)
	{
		$unit = 'bytes';
		if ($bytes > 1024):
			$bytes /= 1024;
			$unit   = 'KB';
		endif;
		if ($bytes > 1024):
			$bytes /= 1024;
			$unit   = 'MB';
		endif;
		if ($bytes > 1024):
			$bytes /= 1024;
			$unit   = 'GB';
		endif;
		if ($bytes > 1024):
			$bytes /= 1024;
			$unit   = 'TB';
		endif;
		if ($bytes > 1024):
			$bytes /= 1024;
			$unit   = 'PB';
		endif;

		return round($bytes, 1) . ' ' . $unit;
	}
}

// Thanks to AoEmaster (https://stackoverflow.com/users/1732818/aoemaster)
// https://stackoverflow.com/questions/2840755/how-to-determine-the-max-file-upload-limit-in-php

if (! function_exists('max_file_upload_in_bytes'))
{
	function max_file_upload_in_bytes()
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
	function return_bytes($val)
	{
		$val  = strtolower(trim($val));
		$unit = $val[strlen($val) - 1];
		$val  = (int)rtrim($val, $unit);

		switch($unit)
		{
			case 'g': $val *= 1024;
			case 'm': $val *= 1024;
			case 'k': $val *= 1024;
		}
		return $val;
	}
}
