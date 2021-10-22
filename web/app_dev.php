<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Debug\Debug;

// If you don't want to setup permissions the proper way, just uncomment the following PHP line
// read http://symfony.com/doc/current/setup.html#checking-symfony-application-configuration-and-setup
// for more information
//umask(0000);

// This check prevents access to debug front controllers that are deployed by accident to production servers.
// Feel free to remove this, extend it, or make something more sophisticated.
// PC DPT, VPN, docker
//if (isset($_SERVER['HTTP_CLIENT_IP'])
//    || isset($_SERVER['HTTP_X_FORWARDED_FOR'])
//    || !(in_array(@$_SERVER['REMOTE_ADDR'], array('134.214.89.161','172.29.2.53','10.247.1.1', '::1')) || php_sapi_name() === 'cli-server')
//) {
//    header('HTTP/1.0 403 Forbidden');
//    exit('You are not allowed to access this file. Check '.basename(__FILE__).' for more information.');
//}

$loader = require_once __DIR__.'/../app/bootstrap.php.cache';
Debug::enable();

require_once __DIR__.'/../app/AppKernel.php';

Doctrine\Common\Annotations\AnnotationReader::addGlobalIgnoredName('OA\Get');
Doctrine\Common\Annotations\AnnotationReader::addGlobalIgnoredName('OA\Put');
Doctrine\Common\Annotations\AnnotationReader::addGlobalIgnoredName('OA\Post');
Doctrine\Common\Annotations\AnnotationReader::addGlobalIgnoredName('OA\Delete');
Doctrine\Common\Annotations\AnnotationReader::addGlobalIgnoredName('OA\Parameter');
Doctrine\Common\Annotations\AnnotationReader::addGlobalIgnoredName('OA\Response');
Doctrine\Common\Annotations\AnnotationReader::addGlobalIgnoredName('OA\Info');
Doctrine\Common\Annotations\AnnotationReader::addGlobalIgnoredName('OA\Server');

$kernel = new AppKernel('dev', true);
$kernel->loadClassCache();
$request = Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
