<?php

namespace Tests\Support;

use CodeIgniter\Config\Factories;
use CodeIgniter\Test\FeatureTestTrait;
use Myth\Auth\Test\AuthTestTrait;
use Tatter\Files\Models\FileModel;
use Tests\Support\Models\UserModel;

/**
 * @internal
 */
abstract class FeatureTestCase extends FilesTestCase
{
    use AuthTestTrait;
    use FeatureTestTrait;

    /**
     * If present, will override application
     * routes when using call().
     *
     * @var \CodeIgniter\Router\RouteCollection
     */
    protected $routes;

    /**
     * Values to be set in the SESSION global
     * before running the test.
     *
     * @var array
     */
    protected $session = [];

    /**
     * Enabled auto clean op buffer after request call
     *
     * @var bool
     */
    protected $clean = true;

    protected function setUp(): void
    {
        parent::setUp();

        $this->resetAuthServices();

        // Make sure we use the correct UserModel for permissions
        Factories::injectMock('models', UserModel::class, new UserModel());
    }

    /**
     * Injects a permission mode into the shared FileModel.
     *
     * @param int $mode Octal mode
     */
    protected function setMode(int $mode)
    {
        $model = new FileModel();
        $model->setMode($mode);

        Factories::injectMock('models', FileModel::class, $model);
    }
}
