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
}
