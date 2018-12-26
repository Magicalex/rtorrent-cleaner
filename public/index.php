<?php

require __DIR__.'/../vendor/autoload.php';

use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

try
{
    // Init basic route
    $lol = new Route(
      '/lol',
      array('controller' => 'lolController')
    );

    // Init route with dynamic placeholders
    $index = new Route(
      '/user/{name}',
      array('controller' => 'IndexController', 'method'=>'load'),
      array('name' => '[a-z]+')
    );

    // Add Route object(s) to RouteCollection object
    $routes = new RouteCollection();
    $routes->add('index', $index);
    $routes->add('lol', $lol);

    // Init RequestContext object
    $context = new RequestContext();
    $context->fromRequest(Request::createFromGlobals());

    // Init UrlMatcher object
    $matcher = new UrlMatcher($routes, $context);

    // Find the current route
    $parameters = $matcher->match($context->getPathInfo());

    echo '<pre>';
    print_r($parameters);
    echo '</pre>';
}
catch (ResourceNotFoundException $e)
{
  echo $e->getMessage();
}
