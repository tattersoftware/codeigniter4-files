<?php

namespace Tatter\Files\Publishers;

use Tatter\Frontend\Test\PublishersTestCase;

/**
 * @internal
 */
final class PublishersTest extends PublishersTestCase
{
    public function publisherProvider(): array
    {
        return [
            [
                DropzonePublisher::class,
                [
                    'dropzone/basic.css',
                    'dropzone/dropzone.css',
                    'dropzone/dropzone.css.map',
                    'dropzone/dropzone-min.js',
                    'dropzone/dropzone.mjs',
                ],
            ],
        ];
    }
}
