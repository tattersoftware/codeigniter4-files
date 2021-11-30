<?php

namespace Tatter\Files\Config;

use CodeIgniter\Config\BaseConfig;

class Files extends BaseConfig
{
    /**
     * Directory to store files (with trailing slash)
     *
     * @var string
     */
    public $storagePath = WRITEPATH . 'files/';

    /**
     * Whether to include routes to the Files Controller.
     *
     * @var bool
     */
    public $routeFiles = true;

    /**
     * Layouts to use for general access and for administration
     *
     * @var array<string, string>
     */
    public $layouts = [
        'public' => 'Tatter\Files\Views\layout',
        'manage' => 'Tatter\Files\Views\layout',
    ];

    /**
     * View file aliases
     *
     * @var string[]
     */
    public $views = [
        'dropzone' => 'Tatter\Files\Views\Dropzone\config',
    ];

    /**
     * Default display format; built in are 'cards', 'list', 'select'
     *
     * @var string
     */
    public $defaultFormat = 'cards';

    /**
     * Path to the default thumbnail file
     *
     * @var string
     */
    public $defaultThumbnail = 'Tatter\Files\Assets\Unavailable.jpg';
}
