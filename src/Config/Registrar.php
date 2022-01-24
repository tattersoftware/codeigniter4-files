<?php

namespace Tatter\Files\Config;

class Registrar
{
    /**
     * Adds the Files option to available Pager templates.
     *
     * @return array<string,mixed>
     */
    public static function Pager()
    {
        return [
            'templates' => [
                'files_bootstrap' => 'Tatter\Files\Views\pager',
            ],
        ];
    }

    /**
     * Adds necessary configuration values for Permits
     * to identify the owner(s) of files.
     *
     * @return array<string,mixed>
     */
    public static function Permits()
    {
        return [
            'files' => [
                'userKey'    => 'user_id',
                'pivotKey'   => 'file_id',
                'pivotTable' => 'files_users',
            ],
        ];
    }
}
