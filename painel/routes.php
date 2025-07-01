<?php
use Controllers\FinanceiroController;

// ADMIN – Financeiro
$router->get('/admin/financeiro', 'FinanceiroController@index');
$router->get('/admin/financeiro/create', 'FinanceiroController@create');
$router->post('/admin/financeiro', 'FinanceiroController@store');

// WEBHOOK – Asaas
$router->post('/webhook/asaas', 'FinanceiroController@webhook'); 