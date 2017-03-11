<?php

namespace Pantheon\TerminusInstaller\UnitTests\Command;

use Composer\Console\Application as ComposerApp;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Class AbstractCommandTest
 * Testing class for Pantheon\TerminusInstaller\Command\AbstractCommand
 * @package Pantheon\TerminusInstaller\UnitTests\Command
 */
class AbstractCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DummyCommand
     */
    protected $command;

    /**
     * @inheritdoc
     */
    public function setUp()
    {
        parent::setUp();

        $this->command = new DummyAbstractCommand();
    }

    /**
     * Tests AbstractCommand::getDir()
     */
    public function testGetDir()
    {
        // Scenario: No string replacement
        $dir = 'some dir or other';

        // Scenario: Using string replacement
        $this->assertEquals($dir, $this->command->runFunction('getDir', [$dir,]));
        $this->assertEquals(getenv('HOME'), $this->command->runFunction('getDir', ['~',]));
    }

    /**
     * Tests AbstractCommand::getFilesystem()
     */
    public function testGetFilesystem()
    {
        $out = $this->command->runFunction('getFilesystem');
        $this->assertInstanceOf(Filesystem::class, $out);
    }

    /**
     * Tests AbstractCommand::getHomeDir()
     */
    public function testGetHomeDir()
    {
        // Scenario: HOME is not false
        putenv('HOME=1');
        $this->assertEquals(getenv('HOME'), $this->command->runFunction('getHomeDir'));

        // Scenario: HOME is false, MSYSTEM is null
        $false = false;
        putenv("HOME=$false");
        putenv('MSYSTEM');
        $this->assertEquals(false, $this->command->runFunction('getHomeDir'));

        // Scenario: HOME is false, MSYSTEM is 'MING'
        putenv("HOME=$false");
        putenv('MSYSTEM=MING');
        $this->assertEquals(false, $this->command->runFunction('getHomeDir'));

        // Scenario: HOME is false, MSYSTEM is neither null nor 'MING', HOMEPATH exists
        $homepath = 'homepath';
        putenv("HOME=$false");
        putenv('MSYSTEM=notming');
        putenv("HOMEPATH=$homepath");
        $this->assertEquals($homepath, $this->command->runFunction('getHomeDir'));
    }

    /**
     * Tests AbstractCommand::getPackageTitle($version)
     */
    public function testGetPackageTitle()
    {
        // Scenario: No provided version
        $this->assertEquals(DummyAbstractCommand::PACKAGE_NAME, $this->command->runFunction('getPackageTitle'));

        // Scenario: With provided version
        $version = 'version';
        $expected = DummyAbstractCommand::PACKAGE_NAME . ':' . $version;
        $this->assertEquals($expected, $this->command->runFunction('getPackageTitle', [$version,]));
    }
}
