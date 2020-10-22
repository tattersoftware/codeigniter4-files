<?php namespace Tatter\Files\Structures;

use CodeIgniter\Files\Exceptions\FileNotFoundException;
use CodeIgniter\Files\File;

/**
 * Class FileObject
 *
 * An extension of the framework's File class
 * (which extends SplFileInfo) to allow entity
 * filenames to supercede disk names.
 */
class FileObject extends File
{
	/**
	 * Base file name to override disk version
	 *
	 * @var string|null
	 */
	protected $basename;

	/**
	 * Returns the full path to this file
	 *
	 * @param string|null $basename
	 *
	 * @return $this
	 */
	public function setBasename(string $basename = null): self
	{
		$this->basename = $basename;

		return $this;
	}

	/**
	 * Returns the full path to this file
	 *
	 * @param string $suffix Optional suffix to omit from the base name returned
	 *
	 * @return string
	 */
	public function getBasename($suffix = null): string
	{
		if ($this->basename)
		{
			return basename($this->basename, $suffix);
		}

		return parent::getBasename($suffix);
	}
}
