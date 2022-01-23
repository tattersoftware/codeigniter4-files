<?php

namespace Tests\Support;

use CodeIgniter\Router\RouteCollection;
use CodeIgniter\Config\Factories;
use CodeIgniter\Test\FeatureTestTrait;
use Tests\Support\Models\UserModel;

/**
 * @internal
 */
abstract class FeatureTestCase extends TestCase
{
    use FeatureTestTrait;

    /**
     * If present, will override application
     * routes when using call().
     *
     * @var RouteCollection
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

        // Make sure we use the correct UserModel for permissions
        Factories::injectMock('models', UserModel::class, new UserModel());

        // Make sure everything is published once
        $this->publishAll();
    }
}
