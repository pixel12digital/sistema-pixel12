<?php
use Controllers\FinanceiroController;

// ADMIN – Financeiro
$router->get('/admin/financeiro', 'FinanceiroController@index');
$router->get('/admin/financeiro/create', 'FinanceiroController@create');
$router->post('/admin/financeiro', 'FinanceiroController@store');

// WEBHOOK – Asaas
$router->post('/webhook/asaas', 'FinanceiroController@webhook');

// Rotas de Faturas
$router->get('/financeiro/faturas', 'App\Controllers\Financeiro\FaturasController@index');
$router->get('/financeiro/faturas/{id}', 'App\Controllers\Financeiro\FaturasController@show');
$router->post('/financeiro/faturas/sync', 'App\Controllers\Financeiro\FaturasController@sync');

// Rotas de Assinaturas
$router->get('/financeiro/assinaturas', 'App\Controllers\Financeiro\AssinaturasController@index');
$router->get('/financeiro/assinaturas/{id}', 'App\Controllers\Financeiro\AssinaturasController@show');
$router->post('/financeiro/assinaturas/sync', 'App\Controllers\Financeiro\AssinaturasController@sync'); 