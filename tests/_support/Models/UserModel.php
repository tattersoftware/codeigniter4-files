<?php

namespace Tests\Support\Models;

use Myth\Auth\Models\UserModel as MythModel;
use Tatter\Permits\Interfaces\PermitsUserModelInterface;

/**
 * An extension of Myth's UserModel that is
 * compatible with Tatter\Permits.
 */
class UserModel extends MythModel implements PermitsUserModelInterface
{
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
