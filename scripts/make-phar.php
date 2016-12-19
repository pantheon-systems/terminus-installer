#!/usr/bin/env php
<?php

require './vendor/autoload.php';

use Symfony\Component\Finder\Finder;

if (!isset($argv[1])) {
    echo "usage: php -dphar.readonly=0 $argv[0] <path> [--quiet]\n";
    exit(1);
}

define('DEST_PATH', $argv[1]);

define('BE_QUIET', in_array('--quiet', $argv));

/**
 * Adds a file to the PHAR
 *
 * @param Phar   $phar Phar archive resource
 * @param string $path Path to the file to add
 * @param array $options Options to alter the file being added
 *      integer offset The number of lines to skip at the beginning of the file
 */
function addFile($phar, $path, $options = ['offset' => 0,])
{
    $key = str_replace('./', '', $path);
    if (!BE_QUIET) {
        echo "$key - $path\n";
    }
    $file_contents = file_get_contents($path);
    if (isset($options['offset']) && (boolean)$options['offset']) {
        $file_contents = implode("\n", array_slice(explode("\n", $file_contents), $options['offset']));
    }
    $phar[$key] = $file_contents;
}

$phar = new Phar(DEST_PATH, 0, 'installer.phar');

$phar->startBuffering();

addFile($phar, './bin/installer', ['offset' => 1,]);

// PHP files
$finder = new Finder();
$finder
    ->files()
    ->ignoreVCS(true)
    ->in('./src')
    ->in('./vendor/composer')
    ->in('./vendor/justinrainbow')
    ->in('./vendor/psr')
    ->in('./vendor/seld')
    ->in('./vendor/symfony');

foreach ($finder as $file) {
    if (strpos($file->getPath(), 'Test') === false) {
        addFile($phar, $file);
    }
}

addFile($phar, './vendor/autoload.php');
addFile($phar, './vendor/rmccue/requests/library/Requests/Transport/cacert.pem');

$phar->setStub(
    <<<EOB
#!/usr/bin/env php
<?php
Phar::mapPhar();
include 'phar://installer.phar/bin/installer';
__HALT_COMPILER();
?>
EOB
);

$phar->stopBuffering();
