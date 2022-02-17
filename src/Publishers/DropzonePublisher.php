<?php

namespace Tatter\Files\Publishers;

use Tatter\Frontend\FrontendPublisher;

class DropzonePublisher extends FrontendPublisher
{
    protected string $vendorPath = 'enyo/dropzone/dist';
    protected string $publicPath = 'dropzone';

    /**
     * Reads files from the sources and copies them out to their destinations.
     * This method should be reimplemented by child classes intended for
     * discovery.
     */
    public function publish(): bool
    {
        return $this
            ->addPath('/')
            ->copy(true);
    }
}
