<?php namespace Tatter\Files\Entities;

use CodeIgniter\Entity;
use CodeIgniter\Files\Exceptions\FileNotFoundException;
use CodeIgniter\Files\File as CIFile;

class File extends Entity
{
	protected $dates = ['created_at', 'updated_at', 'deleted_at'];

	/**
	 * Resolved path to the default thumbnail
	 *
	 * @var string|null
	 */
	protected static $defaultThumbnail;

	/**
	 * Returns the absolute path to the configured default thumbnail
	 *
	 * @return string
	 * @throws FileNotFoundException
	 */
	protected static function locateDefaultThumbnail(): string
	{
		// If the path has not been resolved yet then try to now
		if (is_null(self::$defaultThumbnail))
		{
			$path = config('Files')->defaultThumbnail;
			$ext  = pathinfo($path, PATHINFO_EXTENSION);

			if (! self::$defaultThumbnail = service('locator')->locateFile($path, null, $ext))
			{
				throw new FileNotFoundException('Could not locate default thumbnail: ' . $path);
			}
		}

		return self::$defaultThumbnail;
	}

	/**
	 * Returns the path to this file's thumbnail, or the default from config
	 *
	 * @return string
	 */
	public function getThumbnail(): string
	{
		$path = config('Files')->storagePath . 'thumbnails' . DIRECTORY_SEPARATOR . ($this->attributes['thumbnail'] ?? '');

		if (! is_file($path))
		{
			$path = self::locateDefaultThumbnail();
		}

		return realpath($path) ?: $path;
	}

	/**
	 * Returns the full path to this file
	 *
	 * @return string
	 */
	public function getPath(): string
	{
		$path = config('Files')->storagePath . $this->attributes['localname'];

		return realpath($path) ?: $path;
	}

	/**
	 * Returns a framework File object for the local file
	 *
	 * @return CIFile|null  `null` for missing file
	 */
	public function getObject(): ?CIFile
	{
		try
		{
			return new CIFile($this->getPath(), true);
		}
		catch (FileNotFoundException $e)
		{
			return null;
		}
	}
}
