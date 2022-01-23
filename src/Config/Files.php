<?php

namespace Tatter\Files\Config;

use CodeIgniter\Config\BaseConfig;
use Tatter\Files\Exceptions\FilesException;

class Files extends BaseConfig
{
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

    //--------------------------------------------------------------------
    // Storage Options
    //--------------------------------------------------------------------

    /**
     * Directory to store files (with trailing slash).
     * Usually best to use getPath()
     *
     * @var string
     */
    protected $path = WRITEPATH . 'files' . DIRECTORY_SEPARATOR;

    /**
     * Normalizes and creates (if necessary) the storage and thumbnail paths,
     * returning the normalized storage path.
     *
     * @throws FilesException
     */
    public function getPath(): string
    {
        $storage = $this->path;

        // Verify the storage directory
        if (! is_dir($storage) && ! @mkdir($storage, 0775, true)) {
            throw FilesException::forDirFail($storage);
        }

        // Normalize the storage path
        $storage    = realpath($storage) ?: $storage;
        $this->path = rtrim($storage, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

        // Check or create the thumbnails subdirectory
        $thumbnails = $storage . 'thumbnails';
        if (! is_dir($thumbnails) && ! @mkdir($thumbnails, 0775, true)) {
            throw FilesException::forDirFail($thumbnails); // @codeCoverageIgnore
        }

        return $storage;
    }

    /**
     * Changes the storage path value. Mostly just for testing.
     */
    public function setPath(string $path)
    {
        $this->path = $path;
    }
}
