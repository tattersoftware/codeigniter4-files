<?php

namespace Tests\Support\Models;

use CodeIgniter\Model;
use Tatter\Permits\Interfaces\PermitsUserModelInterface;

/**
 * A barebones UserModel that is compatible with Tatter\Permits.
 */
class UserModel extends Model implements PermitsUserModelInterface
{
    protected $table      = 'users';
    protected $primaryKey = 'id';

    /**
     * Returns an empty array since groups are
     * not currently implemented.
     *
     * @param mixed $userId = null
     */
    public function groups($userId = null): array
    {
        return [];
    }
}
