<?php namespace Tatter\Files\Models;

use Tatter\Permits\Models\PModel;

class FileModel extends PModel
{
	// Audits
	use \Tatter\Audits\Traits\AuditsTrait;
	protected $afterInsert = ['auditInsert'];
	protected $afterUpdate = ['auditUpdate'];
	protected $afterDelete = ['auditDelete'];
	
	// Permits
	protected $tableMode  = 0664;
	protected $rowMode    = 0660;
	protected $usersPivot = 'files_users';
	protected $pivotKey   = 'file_id';
	
	// Model
	protected $table      = 'files';
	protected $primaryKey = 'id';

	protected $returnType = 'Tatter\Files\Entities\File';
	protected $useSoftDeletes = true;

	protected $allowedFields = ['filename', 'localname', 'clientname', 'type', 'size', 'thumbnail'];

	protected $useTimestamps = true;

	protected $validationRules    = [
		'filename'    => 'required|max_length[255]',
	];
	protected $validationMessages = [];
	protected $skipValidation     = false;
	
	// Associate a file with a user    
	public function addToUser(int $fileId, int $userId)
	{
		$row = [
			'file_id'  => (int)$fileId,
			'user_id'  => (int)$userId,
		];
		
		return $this->db->table('files_users')->insert($row);
	}

	// Returns an array of all a user's files
    public function getForUser(int $userId): array
    {
        return $this->builder()
			->select('files.*')
			->join('files_users', 'files_users.file_id = files.id', 'left')
			->where('user_id', $userId)
			->get()->getResult();
    }
}
