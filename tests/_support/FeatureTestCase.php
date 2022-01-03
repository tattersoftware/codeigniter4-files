<?php

namespace Tests\Support;

use CodeIgniter\Test\FeatureTestTrait;

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

        // Make sure everything is published once
        $this->publishAll();
    }
}
