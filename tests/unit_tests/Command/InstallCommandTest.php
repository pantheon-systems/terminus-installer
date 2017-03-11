<?php

namespace Pantheon\TerminusInstaller\UnitTests\Command;

use Composer\Console\Application as ComposerApp;
use Pantheon\TerminusInstaller\Command\InstallCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Class InstallCommandTest
 * Testing class for Pantheon\TerminusInstaller\Command\InstallCommand
 * @package Pantheon\TerminusInstaller\UnitTests\Command
 */
class InstallCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DummyInstallCommand
     */
    protected $command;
    /**
     * @var ComposerApp
     */
    protected $composer;
    /**
     * @var Filesystem
     */
    protected $filesystem;
    /**
     * @var InputInterface
     */
    protected $input;
    /**
     * @var OutputInterface
     */
    protected $output;

    /**
     * @inheritdoc
     */
    public function setUp()
    {
        parent::setUp();

        $this->command = new DummyInstallCommand();
        $this->composer = $this->getMockBuilder(ComposerApp::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->filesystem = $this->getMockBuilder(Filesystem::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->input = $this->getMockBuilder(InputInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->output = $this->getMockBuilder(OutputInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->command->setComposer($this->composer);
        $this->command->filesystem = $this->filesystem;
    }

    /**
     * Tests InstallCommand::configure()
     */
    public function testConfigure()
    {
        $this->command->runFunction('configure');
        $this->assertEquals('install', $this->command->getName());
        $this->assertEquals('Installs Terminus via Composer', $this->command->getDescription());
        $this->assertEquals('Installs the Terminus CLI.', $this->command->getHelp());
    }

    /**
     * Tests InstallCommand::execute()
     */
    public function testExecute()
    {
        $bin_dir = '/tmp';
        $dir = 'some dir';
        $version = 'some version';
        $package_name = InstallCommand::PACKAGE_NAME . ':' . $version;
        $expected = [
            'command' => 'require',
            'packages' => [$package_name,],
            '--working-dir' => $dir,
        ];
        $status_code = 'some status code';
        $ds = DIRECTORY_SEPARATOR;

        $this->input->expects($this->at(0))
            ->method('getOption')
            ->with('install-dir')
            ->willReturn($dir);
        $this->input->expects($this->at(1))
            ->method('getOption')
            ->with('install-version')
            ->willReturn($version);
        $this->output->expects($this->at(0))
            ->method('writeln')
            ->with('Installing Terminus...');
        $this->composer->expects($this->once())
            ->method('run')
            ->with(new ArrayInput($expected), $this->output)
            ->willReturn($status_code);
        $this->input->expects($this->at(2))
            ->method('getOption')
            ->with('bin-dir')
            ->willReturn($bin_dir);
        $this->output->expects($this->at(1))
            ->method('writeln');

        $this->assertEquals($status_code, $this->command->runFunction('execute', [$this->input, $this->output,]));
    }

    /**
     * Tests InstallCommand::makeSymlink($bin_dir, $install_dir)
     */
    public function testMakeSymlink()
    {
    }
}
