<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */

$routes->get('/', 'Login::index');

$routes->get('login', 'Login::index');
$routes->post('login/valida', 'Login::valida');
$routes->get('login/valida-test', 'Login::valida');

$routes->get('main', 'Login::main');

$routes->get('logout', 'Login::logout');