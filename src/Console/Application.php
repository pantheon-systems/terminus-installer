<?php

namespace Pantheon\TerminusInstaller\Console;

use Pantheon\TerminusInstaller\Command\InstallCommand;
use Symfony\Component\Console\Application as BaseApplication;

/**
 * Class Application
 * @package Pantheon\TerminusInstaller\Console
 */
class Application extends BaseApplication
{
    const NAME = 'Terminus Installer';
    const VERSION = '0.0.1';
    private $logo_file = __DIR__ . '/../../assets/fist.txt';

    /**
     * @inheritdoc
     */
    public function __construct()
    {
        parent::__construct(self::NAME, self::VERSION);
    }

    /**
     * @inheritdoc
     */
    public function getHelp()
    {
        return base64_decode(file_get_contents($this->logo_file))
            . PHP_EOL
            . parent::getHelp();
    }

    /**
     * @inheritdoc
     */
    protected function getDefaultCommands()
    {
        return array_merge(parent::getDefaultCommands(), [new InstallCommand(),]);
    }
}
