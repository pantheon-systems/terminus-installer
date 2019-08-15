<?php

namespace Pantheon\TerminusInstaller\Command;

use Pantheon\TerminusInstaller\Composer\ComposerAwareInterface;
use Pantheon\TerminusInstaller\Composer\ComposerAwareTrait;
use Pantheon\TerminusInstaller\Utils\LocalSystem;
use Pantheon\TerminusInstaller\Utils\TerminusPackage;
use Robo\Common\OutputAwareTrait;
use Robo\Contract\OutputAwareInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\OutputInterface;

abstract class AbstractCommand extends Command implements ComposerAwareInterface, OutputAwareInterface
{
    use ComposerAwareTrait;
    use OutputAwareTrait;

    const PACKAGE_NAME = 'pantheon-systems/terminus';

    /**
     * @var OutputInterface
     */
    protected $output;
    /**
     * @var TerminusPackage
     */
    protected $package;

    protected function getPackage()
    {
        if (empty($this->package)) {
            $this->package = new TerminusPackage();
        }
        return $this->package;
    }

    /**
     * @param string $install_version The specific version of Terminus to install
     * @return string The name of the package for Composer install
     */
    protected function getPackageTitle($install_version = null)
    {
        $package = self::PACKAGE_NAME;
        if (!is_null($version = $install_version)) {
            $package .= ":^$version";
        }
        return $package;
    }

    /**
     * Uses Composer to install Terminus
     *
     * @param string $install_dir Directory to which to install Terminus
     * @param string $install_version Version of Terminus to install
     * @return integer $status_code The status code of the installation run
     */
    protected function installTerminus($install_dir, $install_version = null) {
        $arguments = [
            'command' => 'require',
            'packages' => [$this->getPackageTitle($install_version),],
            '--working-dir' => $install_dir,
        ];

        $this->output->writeln('Installing Terminus...');
        $status_code = $this->getComposer()->run(new ArrayInput($arguments), $this->output());
        return $status_code;
    }

    /**
     * @param string $dir The directory indicated for the update location
     */
    protected function setDir($dir = null)
    {
        $this->working_directory = LocalSystem::sanitizeLocation($dir);
    }
}
