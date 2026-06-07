<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->get('/', 'Auth::login');
$routes->get('/login', 'Auth::login');
$routes->post('/login', 'Auth::doLogin');
$routes->get('/logout', 'Auth::logout');
$routes->get('/dashboard', 'Dashboard::index', ['filter' => 'auth']);
$routes->get('/grupos', 'Grupos::index', ['filter' => 'auth']);
$routes->get('/grupos/nuevo', 'Grupos::new', ['filter' => 'auth']);
$routes->post('/grupos', 'Grupos::create', ['filter' => 'auth']);
$routes->get('/grupos/(:num)', 'Grupos::show/$1', ['filter' => 'auth']);
$routes->get('/grupos/(:num)/editar', 'Grupos::edit/$1', ['filter' => 'auth']);
$routes->put('/grupos/(:num)', 'Grupos::update/$1', ['filter' => 'auth']);
$routes->delete('/grupos/(:num)', 'Grupos::delete/$1', ['filter' => 'auth']);
