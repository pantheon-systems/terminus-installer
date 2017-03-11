<?php

namespace Pantheon\TerminusInstaller\UnitTests\Command;

use Pantheon\TerminusInstaller\Command\InstallCommand;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Class DummyInstallCommand
 * Aids in testing Pantheon\TerminusInstaller\Command\InstallCommand
 * @package Pantheon\TerminusInstaller\UnitTests\Command
 */
class DummyInstallCommand extends InstallCommand
{
    use NosyTrait;

    /**
     * @var Filesystem
     */
    public $filesystem;

    /**
     * Overrides the parent getFilesystem() function to make it replacable with a mock object
     *
     * @return Filesystem
     */
    public function getFilesystem()
    {
        return $this->filesystem;
    }
}
