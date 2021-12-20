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
     * View file aliases
     *
     * @var string[]
     */
    public $views = [
        'dropzone' => 'Tatter\Files\Views\Dropzone\config',
    ];

    /**
     * Path to the default thumbnail file
     *
     * @var string
     */
    public $defaultThumbnail = 'Tatter\Files\Assets\Unavailable.jpg';

    //--------------------------------------------------------------------
    // Display Preferences
    //--------------------------------------------------------------------

    /**
     * Display format for listing files.
     * Included options are 'cards', 'list', 'select'
     *
     * @var string
     */
    public $format = 'cards';

    /**
     * Default sort column.
     * See FileModel::$allowedFields for options.
     *
     * @var string
     */
    public $sort = 'filename';

    /**
     * Default sort ordering. "asc" or "desc"
     *
     * @var string
     */
    public $order = 'asc';
}
