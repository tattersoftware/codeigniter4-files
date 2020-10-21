<?php namespace Tatter\Files\Models;

use CodeIgniter\Files\File as CIFile;
use CodeIgniter\Model;
use Tatter\Files\Entities\File;
use Tatter\Files\Exceptions\FilesException;
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
	protected $userKey    = 'user_id';
	protected $pivotKey   = 'file_id';
	protected $usersPivot = 'files_users';

	//--------------------------------------------------------------------

	/**
	 * Normalizes and creates (if necessary) the storage and thumbnail paths.
	 *
	 * @return string The normalized storage path
	 *
	 * @throws FilesException
	 */
	public static function storage(): string
	{
		// Normalize the path
		$storage = realpath(config('Files')->storagePath) ?: config('Files')->storagePath;
		$storage = rtrim($storage, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
		if (! is_dir($storage) && ! @mkdir($storage, 0775, true))
		{
			throw FilesException::forDirFail($storage);
		}

		// Normalize the path
		$thumbnails = $storage . 'thumbnails';
		if (! is_dir($thumbnails) && ! @mkdir($thumbnails, 0775, true))
		{
			throw FilesException::forDirFail($thumbnails);
		}

		return $storage;
	}

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
	 * @param array  $data Additional data to pass to insert()
	 *
	 * @return File
	 */
	public function createFromPath(string $path, array $data = []): File
	{
		return $this->createFromFile(new CIFile($path, true), $data);
	}

	/**
	 * Creates a new File from a framework File. Adds it to the
	 * database and moves it into storage (if it is not already).
	 *
	 * @param CIFile $file
	 * @param array  $data Additional data to pass to insert()
	 *
	 * @return File
	 */
	public function createFromFile(CIFile $file, array $data = []): File
	{
		// Gather file info
		$row = [
			'filename'   => $file->getFilename(),
			'localname'  => $file->getRandomName(),
			'clientname' => $file->getFilename(),
			'type'       => $file->getMimeType(),
			'size'       => $file->getSize(),
		];

		// Merge additional data
		$row = array_merge($row, $data);

		// Normalize paths
		$storage  = self::storage();
		$filePath = $file->getRealPath() ?: (string) $file;

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
		$output    = $storage . 'thumbnails' . DIRECTORY_SEPARATOR . $thumbnail;

		try
		{
			service('thumbnails')->create((string) $file, $output);

			// If it succeeds then update the database
			$this->update($fileId, [
				'thumbnail' => $thumbnail,
			]);
		}
		catch (\Throwable $e)
		{
			log_message('error', $e->getMessage());
			log_message('error', 'Unable to create thumbnail for ' . $row['filename']);
		}

		// Return the File entity
		return $this->find($fileId);
	}
}
