<?php

namespace Pantheon\TerminusInstaller\Composer;

use Composer\Console\Application as ComposerApp;

/**
 * Interface ComposerAwareInterface
 * @package Pantheon\TerminusInstaller\Composer
 */
interface ComposerAwareInterface
{
    /**
     * @return ComposerApp
     */
    public function getComposer();

    /**
     * @param ComposerApp
     */
    public function setComposer(ComposerApp $composer);
}
