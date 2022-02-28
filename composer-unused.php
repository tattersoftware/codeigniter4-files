<?php

declare(strict_types=1);

use ComposerUnused\ComposerUnused\Configuration\Configuration;
use ComposerUnused\ComposerUnused\Configuration\NamedFilter;

return static function (Configuration $config): Configuration {
    return $config
        ->addNamedFilter(NamedFilter::fromString('enyo/dropzone'))
        ->setAdditionalFilesFor('tatter/preferences', [
            __DIR__ . '/vendor/tatter/preferences/src/Helpers/preferences_helper.php',
        ]);
};
