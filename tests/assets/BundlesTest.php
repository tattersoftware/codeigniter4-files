<?php

namespace Tatter\Files\Bundles;

use Tatter\Assets\Test\BundlesTestCase;

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
            [
                FilesBundle::class,
                [
                    'all.min.css',
                    'bootstrap.min.css',
                    'dropzone.css',
                    'jquery.min.js', // Note that unlike most JS files this goes in <head>
                ],
                [
                    'bootstrap.bundle.min.js',
                    'dropzone-min.js',
                ],
            ],
        ];
    }
}
