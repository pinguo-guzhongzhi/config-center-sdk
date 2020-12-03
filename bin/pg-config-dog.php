<?php
/**
 * Created by PhpStorm.
 * User: pinguokeji
 * Date: 2020/12/3
 * Time: 10:03 AM
 */


if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require __DIR__ . '/../vendor/autoload.php';
} elseif (file_exists(__DIR__ . '/../../../autoload.php')) {
    require __DIR__ . '/../../../autoload.php';
} elseif (file_exists(__DIR__ . '/../autoload.php')) {
    require __DIR__ . '/../autoload.php';
} else {
    throw new Exception('Unable to locate autoload.php file.');
}

use Symfony\Component\Console\Application;

$application = new Application();
$application->add(new \PGConfig\Console\Config());
$application->run();
