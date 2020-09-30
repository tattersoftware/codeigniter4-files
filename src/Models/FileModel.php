<?php namespace Tatter\Files\Models;

use Tatter\Permits\Model;

class FileModel extends Model
{
	use \Tatter\Audits\Traits\AuditsTrait;
	use \Tatter\Permits\Traits\PermitsTrait;

	protected $table      = 'files';
	protected $primaryKey = 'id';
	protected $returnType = 'Tatter\Files\Entities\File';

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
		return $this->builder()
			->select('files.*')
			->join('files_users', 'files_users.file_id = files.id', 'left')
			->where('user_id', $userId)
			->where('deleted_at IS NULL')
			->get()->getResult($this->returnType);
	}
}
