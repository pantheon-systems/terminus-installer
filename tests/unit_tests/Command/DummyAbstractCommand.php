<?php

namespace Pantheon\TerminusInstaller\UnitTests\Command;

use Pantheon\TerminusInstaller\Command\AbstractCommand;

/**
 * Class DummyAbstractCommand
 * Aids in testing Pantheon\TerminusInstaller\Command\AbstractCommand
 * @package Pantheon\TerminusInstaller\UnitTests\Command
 */
class DummyAbstractCommand extends AbstractCommand
{
    use NosyTrait;

    /**
     * Sets required attributes for a command
     */
    protected function configure()
    {
        $this->setName('dummy');
    }
}
