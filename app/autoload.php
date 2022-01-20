<?php

use Composer\Autoload\ClassLoader;
use Doctrine\Common\Annotations\AnnotationRegistry;

#ini_set('memory_limit', '512M'); // For phpunit test runs
#error_reporting(error_reporting() & ~E_USER_DEPRECATED);



/**
 * @var ClassLoader $loader
 */
$loader = require __DIR__.'/../vendor/autoload.php';

AnnotationRegistry::registerLoader(array($loader, 'loadClass'));

return $loader;
