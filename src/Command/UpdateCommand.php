<?php

namespace Pantheon\TerminusInstaller\Command;

use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class UpdateCommand
 * @package Pantheon\TerminusUpdateer\Command
 */
class UpdateCommand extends AbstractCommand
{
    protected function configure()
    {
        $this->setName('update')
            ->setDescription('Updates Terminus via Composer')
            ->setDefinition([
                new InputOption('dir', null, InputOption::VALUE_OPTIONAL, 'The directory in which to find Terminus', getcwd()),
            ])
            ->setHelp('Updates the Terminus CLI.');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return integer The status code returned from Composer
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;
        $install_dir = $this->getDir($input->getOption('dir'));
        $outdated_package_info = $this->standardizeOutdatedPackageInfo(
            $this->getOutdatedPackageInfo($install_dir)
        );
        $outdated_terminus_info = $this->getOutdatedTerminusInfo($outdated_package_info);
        if (!empty((array)$outdated_terminus_info)) {
            $this->validateTerminusData($outdated_terminus_info);
            if ($this->isTerminusOutdatedByMajorVersion($outdated_terminus_info)) {
                return $this->installTerminus($install_dir, $outdated_terminus_info->latest);
            } else {
                return $this->updateTerminus($install_dir);
            }
        } else {
            $this->output->writeln('Terminus does not require updating in this location');
        }
        return 0;
    }

    /**
     * Gets information for outdated packages
     *
     * @param string $update_dir Directory to which to update Terminus
     * @return object $packageInfo Data returned by composer about outdated packages
     */
    private function getOutdatedPackageInfo($update_dir)
    {
        $arguments = [
            'command' => 'outdated',
            '--working-dir' => $update_dir,
            '--format' => 'json',
        ];

        $this->output->writeln('Checking Terminus for updates...');
        $outdatedOutput = new BufferedOutput();
        $this->getComposer()->run(new ArrayInput($arguments), $outdatedOutput);
        $packageInfo = json_decode($outdatedOutput->fetch());
        return $packageInfo;
    }

    /**
     * Standardizes the possible information coming back from the Composer Outdated command
     *
     * @param object|null $packageInfo Data in JSON format returned from composer outdated
     * @return array The list of outdated installed packages
     */
    private function standardizeOutdatedPackageInfo($packageInfo) {
        if (($packageInfo === null) || !isset($packageInfo->installed)) {
            return [];
        }
        return $packageInfo->installed;
    }

    /**
     * Ensures the vital properties for checking Terminus versions are present
     *
     * @param object $terminus_info Should have a latest and a version property
     * @throws \Exception Is thrown if either of those properties are missing
     */
    private function validateTerminusData($terminus_info) {
        if (!property_exists($terminus_info, 'latest')) {
            throw new \Exception(
                'Data returned from composer outdated did not include a latest version.'
            );
        }
        if (!property_exists($terminus_info, 'version')) {
            throw new \Exception(
                'Data returned from composer outdated did not include a currently installed version.'
            );
        }
    }

    /**
     * Checks for Terminus being outdated
     *
     */
    private function isTerminusOutdatedByMajorVersion($terminus_info) {
        if (!empty($terminus_info)) {
            $latest_version = explode('.', $terminus_info->latest);
            $latest_major_version = array_shift($latest_version);

            $installed_version = explode('.', $terminus_info->version);
            $latest_installed_version = array_shift($installed_version);

            return $latest_installed_version < $latest_major_version;
        }
        return false;
    }

    /**
     * @param object $package_info Stores outdated packages in the 'installed' property possibly DNE
     * @return array
     */
    private function getOutdatedTerminusInfo($package_info) {
        $filtered_info = array_filter($package_info, function($package) {
            return $package->name === self::PACKAGE_NAME;
        });
        if (empty($filtered_info)) {
            return (object)[];
        }
        return array_pop($filtered_info);
    }

    /**
     * Uses Composer to update Terminus
     *
     * @param string $update_dir Directory to which to update Terminus
     * @return integer $status_code The status code of the update operation run
     */
    private function updateTerminus($update_dir) {
        $arguments = [
            'command' => 'update',
            'packages' => [$this->getPackageTitle(),],
            '--working-dir' => $update_dir,
        ];

        $this->output->writeln('Updating Terminus...');
        $status_code = $this->getComposer()->run(new ArrayInput($arguments), $this->output);
        return $status_code;
    }
}
