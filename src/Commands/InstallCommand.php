<?php

namespace Pantheon\Janus\Commands;

/**
 * Class InstallCommand
 * @package Pantheon\Janus\Commands
 */
class InstallCommand
{
    /**
     * Test command for testing
     *
     * @command install
     * @aliases install:terminus
     *
     * @option string $message A message to print
     */
    public function install($options = ['message' => null,])
    {
        if (!is_null($message = $options['message'])) {
            return "The message is '$message'.";
        }
        return "There is no message.";
    }
}
