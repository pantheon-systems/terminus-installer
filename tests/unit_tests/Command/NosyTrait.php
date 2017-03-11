<?php

namespace Pantheon\TerminusInstaller\UnitTests\Command;

/**
 * Class NosyTrait
 * @package Pantheon\TerminusInstaller\UnitTests
 */
trait NosyTrait
{
    /**
     * Runs a member function
     *
     * @param $function_name
     * @param array $args
     * @return mixed
     */
    public function runFunction($function_name, array $args = [])
    {
        $args = array_values($args);
        switch (count($args)) {
            case 2:
                return $this->$function_name($args[0], $args[1]);
            case 1:
                return $this->$function_name($args[0]);
            case 0:
            default:
                return $this->$function_name();
        }
    }
}
