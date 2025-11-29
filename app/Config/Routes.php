<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

$routes->setDefaultNamespace('App\Controllers');
$routes->setDefaultController('Home');
$routes->setDefaultMethod('index');
$routes->setTranslateURIDashes(false);
$routes->set404Override();
$routes->setAutoRoute(false);

$routes->get('/', 'Home::index');

$routes->group('auth', ['namespace' => 'App\Controllers\Auth'], function($routes) {
    $routes->get('login', 'AuthController::login');
    $routes->post('login', 'AuthController::attemptLogin');
    $routes->get('logout', 'AuthController::logout');
    $routes->get('register', 'AuthController::register');
    $routes->post('register', 'AuthController::attemptRegister');
});

$routes->group('api', ['namespace' => 'App\Controllers\Api', 'filter' => 'jwt'], function($routes) {
    $routes->resource('empresas', ['controller' => 'EmpresaController']);
    $routes->resource('projetos', ['controller' => 'ProjetoController']);
    $routes->resource('obras', ['controller' => 'ObraController']);
    $routes->resource('sondagens', ['controller' => 'SondagemController']);
    $routes->get('sondagens/(:num)/conformidade', 'SondagemController::conformidade/$1');
    $routes->post('sondagens/(:num)/aprovar', 'SondagemController::aprovar/$1');
    $routes->get('sondagens/(:num)/camadas', 'CamadaController::index/$1');
    $routes->post('sondagens/(:num)/camadas', 'CamadaController::create/$1');
    $routes->get('sondagens/(:num)/amostras', 'AmostraController::index/$1');
    $routes->post('sondagens/(:num)/amostras', 'AmostraController::create/$1');
    $routes->get('sondagens/(:num)/fotos', 'FotoController::index/$1');
    $routes->post('sondagens/(:num)/fotos', 'FotoController::upload/$1');
    $routes->delete('fotos/(:num)', 'FotoController::delete/$1');
    $routes->get('reports/sondagem/(:num)/pdf', 'Reports\SondagemReportController::pdf/$1');
    $routes->get('reports/sondagem/(:num)/conformidade', 'Reports\SondagemReportController::conformidade/$1');
    $routes->post('reports/sondagens/batch', 'Reports\SondagemReportController::batch');
    $routes->post('import/excel', 'ImportController::excel');
    $routes->get('import/template', 'ImportController::template');
});

$routes->group('admin', ['namespace' => 'App\Controllers\Admin', 'filter' => 'auth'], function($routes) {
    $routes->get('/', 'AdminController::index');
    $routes->get('dashboard', 'AdminController::dashboard');
    $routes->get('sondagens', 'SondagemWebController::index');
    $routes->get('sondagens/create', 'SondagemWebController::create');
    $routes->post('sondagens', 'SondagemWebController::store');
    $routes->get('sondagens/(:num)', 'SondagemWebController::show/$1');
    $routes->get('sondagens/(:num)/edit', 'SondagemWebController::edit/$1');
    $routes->put('sondagens/(:num)', 'SondagemWebController::update/$1');
    $routes->delete('sondagens/(:num)', 'SondagemWebController::delete/$1');
    $routes->get('usuarios', 'UsuarioController::index');
    $routes->get('configuracoes', 'ConfiguracaoController::index');
});

$routes->get('reports/sondagem/(:num)/preview', 'Reports\SondagemReportController::preview/$1', ['filter' => 'auth']);
