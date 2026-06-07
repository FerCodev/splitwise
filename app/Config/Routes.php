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
$routes->get('/gastos', 'Gastos::index', ['filter' => 'auth']);
$routes->get('/gastos/nuevo', 'Gastos::new', ['filter' => 'auth']);
$routes->post('/gastos', 'Gastos::create', ['filter' => 'auth']);
$routes->get('/gastos/(:num)', 'Gastos::show/$1', ['filter' => 'auth']);
$routes->get('/gastos/(:num)/editar', 'Gastos::edit/$1', ['filter' => 'auth']);
$routes->put('/gastos/(:num)', 'Gastos::update/$1', ['filter' => 'auth']);
$routes->delete('/gastos/(:num)', 'Gastos::delete/$1', ['filter' => 'auth']);
$routes->get('/pagos', 'Pagos::index', ['filter' => 'auth']);
$routes->get('/pagos/nuevo', 'Pagos::new', ['filter' => 'auth']);
$routes->post('/pagos', 'Pagos::create', ['filter' => 'auth']);
$routes->get('/pagos/(:num)', 'Pagos::show/$1', ['filter' => 'auth']);
$routes->get('/pagos/(:num)/editar', 'Pagos::edit/$1', ['filter' => 'auth']);
$routes->put('/pagos/(:num)', 'Pagos::update/$1', ['filter' => 'auth']);
$routes->delete('/pagos/(:num)', 'Pagos::delete/$1', ['filter' => 'auth']);
