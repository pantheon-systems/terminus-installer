<?php

namespace Pantheon\TerminusInstaller\Composer;

use Composer\Console\Application as ComposerApp;

/**
 * Class ComposerAwareTrait
 * @package Pantheon\TerminusInstaller\Composer
 */
trait ComposerAwareTrait
{
    /**
     * @var ComposerApp
     */
    private $composer;

    /**
     * @return ComposerApp
     */
    public function getComposer()
    {
        if (empty($this->composer)) {
            $this->setComposer(new ComposerApp());
            $this->composer->setAutoExit(false);
        }
        return $this->composer;
    }

    /**
     * @param ComposerApp
     */
    public function setComposer(ComposerApp $composer)
    {
        $this->composer = $composer;
    }
}
