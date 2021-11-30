<?php

namespace Tatter\Files\Database\Seeds;

use Tatter\Settings\Models\SettingModel;

class FileSeeder extends \CodeIgniter\Database\Seeder
{
    public function run()
    {
        // Compatible with Settings v1 and v2
        $templates = [
            [
                'name'      => 'perPage',
                'datatype'  => 'int',
                'summary'   => 'Number of items to show per page',
                'content'   => '10',
                'scope'     => 'user',
                'protected' => 1,
            ],
            [
                'name'      => 'filesFormat',
                'datatype'  => 'string',
                'summary'   => 'Display format for listing files',
                'content'   => 'cards',
                'scope'     => 'user',
                'protected' => 0,
            ],
            [
                'name'      => 'filesSort',
                'datatype'  => 'string',
                'summary'   => 'Sort field for listing files',
                'content'   => 'filename',
                'scope'     => 'user',
                'protected' => 0,
            ],
            [
                'name'      => 'filesOrder',
                'datatype'  => 'string',
                'summary'   => 'Sort order for listing files',
                'content'   => 'asc',
                'scope'     => 'user',
                'protected' => 0,
            ],
        ];

        // Check for each template and create it if it is missing
        foreach ($templates as $template) {
            if (! model(SettingModel::class)->where('name', $template['name'])->first()) { // @phpstan-ignore-line
                model(SettingModel::class)->insert($template);
            }
        }
    }
}
