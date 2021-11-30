<?php

namespace Tests\Support\Fakers;

use Faker\Generator;
use Tatter\Files\Entities\File;
use Tatter\Files\Models\FileModel;

class FileFaker extends FileModel
{
    /**
     * Faked data for Fabricator.
     */
    public function fake(Generator &$faker): File
    {
        $name = $faker->company . '.' . $faker->fileExtension;

        return new File([
            'filename'   => $name,
            'localname'  => $faker->md5,
            'clientname' => $name,
            'type'       => $faker->mimeType,
            'size'       => mt_rand(1000, 4000000),
            'thumbnail'  => $faker->text(5000),
        ]);
    }
}
