<?php

namespace Config;

/*
*
* This file contains example values to alter default library behavior.
* Recommended usage:
*	1. Copy the file to app/Config/Files.php
*	2. Change any values
*	3. Remove any lines to fallback to defaults
*
*/

class Files extends \Tatter\Files\Config\Files
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
