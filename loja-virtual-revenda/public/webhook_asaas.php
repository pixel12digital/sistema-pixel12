<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../src/Services/AsaasService.php';
require_once __DIR__ . '/../db.php';

$service = new AsaasService(
    $config['asaas_api_url'],
    $config['asaas_api_key'],
    $mysqli
);

echo "Sincronização iniciada: " . date('Y-m-d H:i:s') . "\n";
$resultado = $service->sincronizarCobrancas();
echo "Cobranças sincronizadas: {$resultado}\n";
file_put_contents(__DIR__ . '/ultima_sincronizacao.log', date('Y-m-d H:i:s'));
echo "Sincronização concluída: " . date('Y-m-d H:i:s') . ""; 