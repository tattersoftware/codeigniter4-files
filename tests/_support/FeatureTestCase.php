<?php

namespace Tests\Support;

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
     * Enabled auto clean op buffer after request call
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
