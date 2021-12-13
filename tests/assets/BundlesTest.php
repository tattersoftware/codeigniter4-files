<?php

namespace Tatter\Files\Bundles;

use Tatter\Frontend\Test\BundlesTestCase;

/**
 * @internal
 */
final class BundlesTest extends BundlesTestCase
{
    public function bundleProvider(): array
    {
        return [
            [
                DropzoneBundle::class,
                [
                    'dropzone.css',
                ],
                [
                    'dropzone-min.js',
                ],
            ],
        ];
    }
}
