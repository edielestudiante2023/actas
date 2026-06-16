<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->get('/', 'Auth::loginForm');
$routes->get('login', 'Auth::loginForm');
$routes->post('login', 'Auth::login');
$routes->get('logout', 'Auth::logout');
$routes->get('password/forgot', 'PasswordReset::requestForm');
$routes->post('password/forgot', 'PasswordReset::send');
$routes->get('password/reset/(:segment)', 'PasswordReset::resetForm/$1');
$routes->post('password/reset/(:segment)', 'PasswordReset::update/$1');
$routes->get('verificar', 'ActaVerificacion::form');
$routes->post('verificar', 'ActaVerificacion::buscar');
$routes->get('verificar/(:segment)', 'ActaVerificacion::resultado/$1');

$routes->get('dashboard', 'Dashboard::index', ['filter' => 'auth']);

$routes->get('compromisos', 'Compromisos::index', ['filter' => ['auth', 'cliente']]);
$routes->post('compromisos/(:num)', 'Compromisos::update/$1', ['filter' => ['auth', 'cliente']]);

$routes->group('clientes', ['filter' => 'auth'], static function ($routes) {
    $routes->get('/', 'Clientes::index');
    $routes->get('nuevo', 'Clientes::createForm');
    $routes->post('/', 'Clientes::create');
    $routes->post('activo', 'Clientes::setActive');
    $routes->get('(:num)/consejo', 'ClienteConsejo::index/$1');
    $routes->post('(:num)/consejo', 'ClienteConsejo::create/$1');
    $routes->post('(:num)/consejo/(:num)/estado', 'ClienteConsejo::status/$1/$2');
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

$routes->group('actas', ['filter' => ['auth', 'cliente']], static function ($routes) {
    $routes->get('/', 'Actas::index');
    $routes->get('nuevo', 'Actas::createForm');
    $routes->post('/', 'Actas::create');
    $routes->get('(:num)/asistentes', 'ActaAsistentes::index/$1');
    $routes->post('(:num)/asistentes/importar-consejo', 'ActaAsistentes::importarConsejo/$1');
    $routes->post('(:num)/asistentes/(:num)', 'ActaAsistentes::update/$1/$2');
    $routes->get('(:num)/compromisos', 'ActaCompromisos::index/$1');
    $routes->post('(:num)/compromisos', 'ActaCompromisos::create/$1');
    $routes->post('(:num)/compromisos/(:num)', 'ActaCompromisos::update/$1/$2');
    $routes->get('(:num)/votaciones', 'ActaVotaciones::index/$1');
    $routes->post('(:num)/votaciones', 'ActaVotaciones::create/$1');
    $routes->post('(:num)/votaciones/abrir', 'ActaVotaciones::abrir/$1');
    $routes->post('(:num)/votaciones/(:num)/votar', 'ActaVotaciones::votar/$1/$2');
    $routes->post('(:num)/votaciones/(:num)/cerrar', 'ActaVotaciones::cerrar/$1/$2');
    $routes->post('(:num)/votaciones/(:num)/enviar-email', 'ActaVotaciones::enviarVotoEmails/$1/$2');
    $routes->post('(:num)/votaciones/(:num)', 'ActaVotaciones::update/$1/$2');
    $routes->get('(:num)/anexos', 'ActaAnexos::index/$1');
    $routes->post('(:num)/anexos', 'ActaAnexos::create/$1');
    $routes->get('(:num)/anexos/(:num)/descargar', 'ActaAnexos::download/$1/$2');
    $routes->post('(:num)/anexos/(:num)/eliminar', 'ActaAnexos::delete/$1/$2');
    $routes->get('(:num)/pdf', 'ActaPdf::pdf/$1');
    $routes->get('(:num)/word', 'ActaPdf::word/$1');
    $routes->post('(:num)/cerrar', 'ActaFirmas::cerrar/$1');
    $routes->get('(:num)/firmas', 'ActaFirmas::estado/$1');
    $routes->post('(:num)/firmas/email', 'ActaFirmas::enviarEmailTodos/$1');
    $routes->post('(:num)/firmas/email/(:num)', 'ActaFirmas::enviarEmailIndividual/$1/$2');
    $routes->post('(:num)/firmas/enlace/(:num)/regenerar', 'ActaFirmas::regenerarEnlace/$1/$2');
    $routes->post('(:num)/firmas/enlace/(:num)/cancelar', 'ActaFirmas::cancelarEnlace/$1/$2');
    $routes->post('(:num)/firmas/solicitudes-reapertura', 'ActaFirmas::crearSolicitudReapertura/$1');
    $routes->post('(:num)/firmas/solicitudes-ausente/(:num)/aprobar', 'ActaFirmas::aprobarSolicitudAusente/$1/$2');
    $routes->post('(:num)/firmas/solicitudes-ausente/(:num)/rechazar', 'ActaFirmas::rechazarSolicitudAusente/$1/$2');
    $routes->post('(:num)/firmas/solicitudes-reapertura/(:num)/aprobar', 'ActaFirmas::aprobarSolicitudReapertura/$1/$2');
    $routes->post('(:num)/firmas/solicitudes-reapertura/(:num)/rechazar', 'ActaFirmas::rechazarSolicitudReapertura/$1/$2');
    $routes->get('(:num)/editar', 'Actas::edit/$1');
    $routes->post('(:num)', 'Actas::update/$1');
});

// Firma pública por token (SIN autenticación).
$routes->get('firmar/(:segment)', 'ActaFirmaPublica::firmar/$1');
$routes->post('firmar/(:segment)', 'ActaFirmaPublica::procesarFirma/$1');
$routes->post('firmar/(:segment)/ausente', 'ActaFirmaPublica::solicitarAusente/$1');
$routes->get('firma-exitosa', 'ActaFirmaPublica::exitosa');

// Voto público por token (SIN autenticación).
$routes->get('votar/(:segment)', 'VotoPublico::votar/$1');
$routes->post('votar/(:segment)', 'VotoPublico::procesarVoto/$1');
$routes->get('voto-exitoso', 'VotoPublico::exitoso');
$routes->get('firma-solicitud-recibida', 'ActaFirmaPublica::solicitudRecibida');
