<?php

namespace Tatter\Files\Models;

use CodeIgniter\Model;
use Tatter\Files\Entities\File;

/**
 * Export Model
 *
 * Used to track records for file
 * exports.
 */
class ExportModel extends Model
{
    protected $table           = 'exports';
    protected $primaryKey      = 'id';
    protected $returnType      = 'object';
    protected $useTimestamps   = true;
    protected $updatedField    = '';
    protected $useSoftDeletes  = false;
    protected $skipValidation  = false;
    protected $allowedFields   = ['handler', 'file_id', 'user_id'];
    protected $validationRules = [
        'handler' => 'required|max_length[63]',
        'file_id' => 'required|integer',
        'user_id' => 'permit_empty|integer',
    ];
}
