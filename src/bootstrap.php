<?php

/**
 * This is a copy of Composer's src/boostrap.php file.
 *
 * @author Nils Adermann <naderman@naderman.de> & Jordi Boggiano <j.boggiano@seld.be>
 * @url https://github.com/composer/composer/blob/1.2/LICENSE
 */

function includeIfExists($file)
{
    return file_exists($file) ? include $file : false;
}

if ((!$loader = includeIfExists(__DIR__ . '/../vendor/autoload.php')) && (!$loader = includeIfExists(__DIR__ . '/../../../autoload.php'))) {
    echo 'You must set up the project dependencies using `composer install`' . PHP_EOL
        . 'See https://getcomposer.org/download/ for instructions on installing Composer' . PHP_EOL;
    exit(1);
}

return $loader;
