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

$routes->get('login', 'AuthWebController::loginPage');
$routes->post('auth/login', 'AuthWebController::login');
$routes->get('auth/logout', 'AuthWebController::logout');
$routes->get('auth/forgot-password', 'AuthWebController::forgotPassword');

$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
    $routes->get('/', 'AdminController::index');
    $routes->get('dashboard', 'AdminController::dashboard');

    $routes->get('sondagens', 'SondagemWebController::index');
    $routes->get('sondagens/create', 'SondagemWebController::create');
    $routes->get('sondagens/(:num)', 'SondagemWebController::show/$1');
    $routes->get('sondagens/(:num)/edit', 'SondagemWebController::edit/$1');
});

$routes->group('api', ['namespace' => 'App\Controllers\API'], function($routes) {

    $routes->post('auth/login', 'AuthController::login');
    $routes->post('auth/register', 'AuthController::register');
    $routes->get('auth/me', 'AuthController::me');
    $routes->put('auth/password', 'AuthController::updatePassword');

    $routes->resource('empresas', ['controller' => 'EmpresaController']);
    $routes->get('empresas/ativas', 'EmpresaController::ativas');
    $routes->get('empresas/cnpj/(:any)', 'EmpresaController::byCnpj/$1');

    $routes->resource('projetos', ['controller' => 'ProjetoController']);
    $routes->get('projetos/empresa/(:num)/ativos', 'ProjetoController::ativos/$1');

    $routes->resource('obras', ['controller' => 'ObraController']);

    $routes->resource('sondagens', ['controller' => 'SondagemController']);
    $routes->post('sondagens/(:num)/aprovar', 'SondagemController::aprovar/$1');
    $routes->post('sondagens/(:num)/rejeitar', 'SondagemController::rejeitar/$1');
    $routes->post('sondagens/(:num)/duplicar', 'SondagemController::duplicar/$1');
    $routes->get('sondagens/(:num)/validar', 'SondagemController::validar/$1');
    $routes->get('sondagens/(:num)/conformidade', 'SondagemController::calcularConformidade/$1');
    $routes->get('sondagens/(:num)/camadas', 'SondagemController::camadas/$1');
    $routes->get('sondagens/(:num)/amostras', 'SondagemController::amostras/$1');

    $routes->get('pdf/gerar/(:num)', 'PDFController::gerarSondagem/$1');
    $routes->get('pdf/preview/(:num)', 'PDFController::preview/$1');
    $routes->get('pdf/download/(:any)', 'PDFController::download/$1');

    $routes->get('sondagens/(:num)/fotos', 'FotoController::index/$1');
    $routes->post('sondagens/(:num)/fotos', 'FotoController::upload/$1');
    $routes->get('fotos/(:num)', 'FotoController::show/$1');
    $routes->put('fotos/(:num)', 'FotoController::atualizar/$1');
    $routes->delete('fotos/(:num)', 'FotoController::delete/$1');
    $routes->post('sondagens/(:num)/fotos/reordenar', 'FotoController::reordenar/$1');

    $routes->post('import/excel', 'ImportController::excel');
    $routes->get('import/template', 'ImportController::template');
});
