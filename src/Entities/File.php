<?php namespace Tatter\Files\Entities;

use CodeIgniter\Entity;
use CodeIgniter\Files\Exceptions\FileNotFoundException;
use CodeIgniter\Files\File as CIFile;
use Config\Mimes;

class File extends Entity
{
	protected $dates = [
		'created_at',
		'updated_at',
		'deleted_at',
	];

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
	 *
	 * @throws FileNotFoundException
	 */
	public static function locateDefaultThumbnail(): string
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

		return (string) self::$defaultThumbnail;
	}

	//--------------------------------------------------------------------

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
	 * Returns the most likely actual file extension
	 *
	 * @param string $method Explicit method to use for determining the extension
	 *
	 * @return string
	 */
	public function getExtension($method = ''): string
	{
		if (! $method || $method === 'type')
		{
			if ($extension = Mimes::guessExtensionFromType($this->attributes['type']))
			{
				return $extension;
			}
		}

		if (! $method || $method === 'mime')
		{
			if ($file = $this->getObject())
			{
				if ($extension = $file->guessExtension())
				{
					return $extension;
				}
			}
		}

		foreach (['clientname', 'localname', 'filename'] as $attribute)
		{
			if (! $method || $method === $attribute)
			{
				if ($extension = pathinfo($this->attributes[$attribute], PATHINFO_EXTENSION))
				{
					return $extension;
				}
			}
		}

		return '';
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

	/**
	 * Returns class names of Exports applicable to this file's extension
	 *
	 * @param boolean $asterisk Whether to include generic "*" extensions
	 *
	 * @return string[]
	 */
	public function getExports($asterisk = true): array
	{
		$exports = [];

		if ($extension = $this->getExtension())
		{
			$exports = handlers('Exports')->where(['extensions has' => $extension])->findAll();
		}

		if ($asterisk)
		{
			$exports = array_merge(
				$exports,
				handlers('Exports')->where(['extensions' => '*'])->findAll()
			);
		}

		return $exports;
	}

	/**
	 * Returns the path to this file's thumbnail, or the default from config.
	 * Should always return a path to a valid file to be safe for img_data()
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
}
