<?php

use Symfony\Component\HttpFoundation\Request;

$loader = require __DIR__.'/../app/autoload.php';

$kernel = new AppKernel('prod', false);

Doctrine\Common\Annotations\AnnotationReader::addGlobalIgnoredName('OA\Get');
Doctrine\Common\Annotations\AnnotationReader::addGlobalIgnoredName('OA\Put');
Doctrine\Common\Annotations\AnnotationReader::addGlobalIgnoredName('OA\Post');
Doctrine\Common\Annotations\AnnotationReader::addGlobalIgnoredName('OA\Delete');
Doctrine\Common\Annotations\AnnotationReader::addGlobalIgnoredName('OA\Parameter');
Doctrine\Common\Annotations\AnnotationReader::addGlobalIgnoredName('OA\Response');
Doctrine\Common\Annotations\AnnotationReader::addGlobalIgnoredName('OA\Info');
Doctrine\Common\Annotations\AnnotationReader::addGlobalIgnoredName('OA\Server');

$kernel->loadClassCache();
//$kernel = new AppCache($kernel);

// When using the HttpCache, you need to call the method in your front controller instead of relying on the configuration parameter
//Request::enableHttpMethodParameterOverride();
$request = Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
