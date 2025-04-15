<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');
$routes->get('/documentation', 'Home::documentation');
$routes->get('/download', 'Home::download');
$routes->get('/explore', 'Home::explore');
$routes->get('/blast', 'Home::blast');
$routes->get('/entry/(:any)', 'Home::entry/$1');
$routes->post('/run', 'Project::create');
$routes->get('/project/(:any)', 'Project::id/$1');