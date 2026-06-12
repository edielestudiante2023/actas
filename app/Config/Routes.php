<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->get('/', 'Auth::loginForm');
$routes->get('login', 'Auth::loginForm');
$routes->post('login', 'Auth::login');
$routes->get('logout', 'Auth::logout');

$routes->get('dashboard', 'Dashboard::index', ['filter' => 'auth']);

$routes->group('clientes', ['filter' => 'auth'], static function ($routes) {
    $routes->get('/', 'Clientes::index');
    $routes->get('nuevo', 'Clientes::createForm');
    $routes->post('/', 'Clientes::create');
    $routes->post('activo', 'Clientes::setActive');
    $routes->get('(:num)/editar', 'Clientes::edit/$1');
    $routes->post('(:num)', 'Clientes::update/$1');
    $routes->post('(:num)/estado', 'Clientes::deactivate/$1');
    $routes->get('(:num)/logo', 'Clientes::logo/$1');
});

$routes->group('usuarios', ['filter' => 'auth'], static function ($routes) {
    $routes->get('/', 'Usuarios::index');
    $routes->get('nuevo', 'Usuarios::createForm');
    $routes->post('/', 'Usuarios::create');
    $routes->get('(:num)/editar', 'Usuarios::edit/$1');
    $routes->post('(:num)', 'Usuarios::update/$1');
    $routes->post('(:num)/estado', 'Usuarios::status/$1');
});
