<?php

namespace Tests\Support;

use CodeIgniter\Config\Factories;
use CodeIgniter\Router\RouteCollection;
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
     */
    protected RouteCollection $routes;

    /**
     * Values to be set in the SESSION global
     * before running the test.
     */
    protected array $session = [];

    /**
     * Enabled auto clean op buffer after request call
     */
    protected bool $clean = true;

    protected function setUp(): void
    {
        parent::setUp();

        // Make sure we use the correct UserModel for permissions
        Factories::injectMock('models', UserModel::class, new UserModel());

        // Make sure everything is published once
        $this->publishAll();
    }
}
