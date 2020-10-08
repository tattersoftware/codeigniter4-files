<?php namespace Tatter\Files\Models;

use CodeIgniter\Files\File as CIFile;
use CodeIgniter\Model;
use Tatter\Files\Entities\File;
use Tatter\Thumbnails\Exceptions\ThumbnailsException;

class FileModel extends Model
{
	use \Tatter\Audits\Traits\AuditsTrait;
	use \Tatter\Permits\Traits\PermitsTrait;

	protected $table      = 'files';
	protected $primaryKey = 'id';
	protected $returnType = File::class;

	protected $useTimestamps  = true;
	protected $useSoftDeletes = true;
	protected $skipValidation = false;

	protected $allowedFields = [
		'filename',
		'localname',
		'clientname',
		'type',
		'size',
		'thumbnail',
	];

	protected $validationRules = [
		'filename' => 'required|max_length[255]',
		// file size in bytes
		'size'     => 'permit_empty|is_natural',
	];

	// Audits
	protected $afterInsert = ['auditInsert'];
	protected $afterUpdate = ['auditUpdate'];
	protected $afterDelete = ['auditDelete'];

	// Permits
	protected $mode       = 04660;
	protected $pivotKey   = 'file_id';
	protected $usersPivot = 'files_users';

	//--------------------------------------------------------------------

	/**
	 * Associates a file with a user
	 *
	 * @param integer $fileId
	 * @param integer $userId
	 *
	 * @return boolean
	 */
	public function addToUser(int $fileId, int $userId): bool
	{
		return (bool) $this->db->table('files_users')->insert([
			'file_id' => $fileId,
			'user_id' => $userId,
		]);
	}

	/**
	 * Returns an array of all a user's Files
	 *
	 * @param integer $userId
	 *
	 * @return array
	 */
	public function getForUser(int $userId): array
	{
		return $this->whereUser($userId)->findAll();
	}

	/**
	 * Adds a where filter for a specific user.
	 *
	 * @param integer $userId
	 *
	 * @return $this
	 */
	public function whereUser(int $userId): self
	{
		$this->select('files.*')
			->join('files_users', 'files_users.file_id = files.id', 'left')
			->where('user_id', $userId);

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Creates a new File from a path File. See createFromFile().
	 *
	 * @param string $path
	 * @param string|null $originalName A name to use for clientname
	 *
	 * @return File
	 */
	public function createFromPath(string $path, string $originalName = null): File
	{
		return $this->createFromFile(new CIFile($path, true), $originalName);
	}

	/**
	 * Creates a new File from a framework File. Adds it to the
	 * database and moves it into storage (if it is not already).
	 *
	 * @param CIFile $file
	 * @param string|null $originalName A name to use for clientname
	 *
	 * @return File
	 */
	public function createFromFile(CIFile $file, string $originalName = null): File
	{
		$originalName = $originalName ?? $file->getFilename();

		// Gather file info
		$row = [
			'filename'   => $originalName,
			'localname'  => $file->getRandomName(),
			'clientname' => $originalName,
			'type'       => $file->getMimeType(),
			'size'       => $file->getSize(),
		];

		// Normalize paths
		$storage  = realpath(config('Files')->storagePath) ?: config('Files')->storagePath;
		$storage  = rtrim($storage, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
		$filePath = $file->getRealPath() ?: $file->__toString();

		// Determine if we need to move the file
		if (strpos($filePath, $storage) === false)
		{
			// Move the file
			$file = $file->move($storage, $row['localname']);
			chmod($storage . $row['localname'], 0664);
		}

		// Record it in the database
		$fileId = $this->insert($row);

		// If a user is logged in then associate the File
		if ($userId = user_id())
		{
			$this->addToUser($fileId, $userId);
		}

		// Try to create a Thumbnail
		$thumbnail = pathinfo($row['localname'], PATHINFO_FILENAME);
		$thumbPath = $storage . 'thumbnails' . DIRECTORY_SEPARATOR . $thumbnail;
		try
		{
			service('thumbnails')->create($filePath, $thumbPath);

			// If it succeeds then update the database
			$this->update($fileId, [
				'thumbnail' => $thumbnail,
			]);
		}
		catch (\Throwable $e)
		{
			log_message('debug', $e->getMessage());
			log_message('debug', 'Unable to create thumbnail for ' . $row['filename']);
		}

		// Return the File entity
		return $this->find($fileId);
	}
}
