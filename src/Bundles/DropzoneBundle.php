<?php

namespace Tatter\Files\Bundles;

use Tatter\Frontend\FrontendBundle;

class DropzoneBundle extends FrontendBundle
{
    /**
     * Applies any initial inputs after the constructor.
     */
    protected function define(): void
    {
        $this
            ->addPath('dropzone/dropzone.css')
            ->addPath('dropzone/dropzone-min.js');
    }
}
