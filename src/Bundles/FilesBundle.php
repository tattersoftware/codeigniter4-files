<?php

namespace Tatter\Files\Bundles;

use Tatter\Assets\Bundle;
use Tatter\Frontend\Bundles\BootstrapBundle;
use Tatter\Frontend\Bundles\FontAwesomeBundle;

class FilesBundle extends Bundle
{
    /**
     * Applies any initial inputs after the constructor.
     */
    protected function define(): void
    {
        $this
            ->merge(new BootstrapBundle())
            ->merge(new DropzoneBundle())
            ->merge(new FontAwesomeBundle());
    }
}
