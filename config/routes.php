<?php

use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route;

$route = new Route('/', ['_controller' => '../src/Controller/IndexController']);

$routes = new RouteCollection();
$routes->add('index', $route);

$context = new RequestContext('/');

$matcher = new UrlMatcher($routes, $context);

$parameters = $matcher->match('/');
// array('_controller' => 'MyController', '_route' => 'route_name')
