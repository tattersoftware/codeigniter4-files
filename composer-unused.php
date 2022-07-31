<?php

declare(strict_types=1);

use ComposerUnused\ComposerUnused\Configuration\Configuration;
use ComposerUnused\ComposerUnused\Configuration\NamedFilter;
use ComposerUnused\ComposerUnused\Configuration\PatternFilter;
use Webmozart\Glob\Glob;

return static fn (Configuration $config): Configuration => $config
    ->addNamedFilter(NamedFilter::fromString('enyo/dropzone'))
    ->setAdditionalFilesFor('tatter/preferences', [
        __DIR__ . '/vendor/tatter/preferences/src/Helpers/preferences_helper.php',
    ]);
