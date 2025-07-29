<?php
/**
 * TESTADOR DE PORTA COMERCIAL - VPS
 * 
 * Script para testar se a porta 3001 está funcionando
 * para o canal comercial
 */

header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');

$vps_ip = '212.85.11.238';
$porta_atual = 3000;
$porta_comercial = 3001;

$resultados = [
    'timestamp' => date('Y-m-d H:i:s'),
    'vps_ip' => $vps_ip,
    'porta_atual' => $porta_atual,
    'porta_comercial' => $porta_comercial,
    'testes' => []
];

// Teste 1: Verificar porta atual (3000)
$ch_atual = curl_init();
curl_setopt($ch_atual, CURLOPT_URL, "http://{$vps_ip}:{$porta_atual}/status");
curl_setopt($ch_atual, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch_atual, CURLOPT_TIMEOUT, 5);
curl_setopt($ch_atual, CURLOPT_CONNECTTIMEOUT, 3);

$response_atual = curl_exec($ch_atual);
$http_code_atual = curl_getinfo($ch_atual, CURLINFO_HTTP_CODE);
$error_atual = curl_error($ch_atual);
curl_close($ch_atual);

$resultados['testes']['porta_atual'] = [
    'nome' => 'Porta Atual (3000) - Canal Financeiro',
    'url' => "http://{$vps_ip}:{$porta_atual}/status",
    'http_code' => $http_code_atual,
    'erro' => $error_atual,
    'resposta' => $response_atual,
    'status' => $http_code_atual === 200 ? 'sucesso' : 'falha'
];

// Teste 2: Verificar porta comercial (3001)
$ch_comercial = curl_init();
curl_setopt($ch_comercial, CURLOPT_URL, "http://{$vps_ip}:{$porta_comercial}/status");
curl_setopt($ch_comercial, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch_comercial, CURLOPT_TIMEOUT, 5);
curl_setopt($ch_comercial, CURLOPT_CONNECTTIMEOUT, 3);

$response_comercial = curl_exec($ch_comercial);
$http_code_comercial = curl_getinfo($ch_comercial, CURLINFO_HTTP_CODE);
$error_comercial = curl_error($ch_comercial);
curl_close($ch_comercial);

$resultados['testes']['porta_comercial'] = [
    'nome' => 'Porta Comercial (3001) - Canal Comercial',
    'url' => "http://{$vps_ip}:{$porta_comercial}/status",
    'http_code' => $http_code_comercial,
    'erro' => $error_comercial,
    'resposta' => $response_comercial,
    'status' => $http_code_comercial === 200 ? 'sucesso' : 'falha'
];

// Teste 3: Verificar conectividade TCP na porta 3001
$conexao_tcp = @fsockopen($vps_ip, $porta_comercial, $errno, $errstr, 3);
$resultados['testes']['tcp_comercial'] = [
    'nome' => 'Conectividade TCP - Porta 3001',
    'porta' => $porta_comercial,
    'errno' => $errno,
    'erro' => $errstr,
    'status' => $conexao_tcp ? 'sucesso' : 'falha'
];

if ($conexao_tcp) {
    fclose($conexao_tcp);
}

// Teste 4: Verificar se a porta está aberta no firewall
$resultados['testes']['firewall'] = [
    'nome' => 'Status Firewall - Porta 3001',
    'observacao' => 'Verificar manualmente: ufw status | grep 3001',
    'status' => 'pendente'
];

// Resumo dos resultados
$porta_atual_ok = $resultados['testes']['porta_atual']['status'] === 'sucesso';
$porta_comercial_ok = $resultados['testes']['porta_comercial']['status'] === 'sucesso';
$tcp_comercial_ok = $resultados['testes']['tcp_comercial']['status'] === 'sucesso';

$resultados['resumo'] = [
    'porta_atual_funcionando' => $porta_atual_ok,
    'porta_comercial_funcionando' => $porta_comercial_ok,
    'tcp_comercial_funcionando' => $tcp_comercial_ok,
    'status_geral' => $porta_atual_ok ? 'sistema_atual_ok' : 'sistema_atual_com_problema',
    'recomendacao' => $porta_comercial_ok ? 'porta_comercial_pronta' : 'configurar_porta_comercial'
];

// Adicionar mensagens de recomendação
if (!$porta_comercial_ok) {
    $resultados['acoes_necessarias'] = [
        '1. Acessar VPS via SSH: ssh root@212.85.11.238',
        '2. Verificar se porta 3001 está livre: netstat -tulpn | grep :3001',
        '3. Abrir porta no firewall: ufw allow 3001',
        '4. Configurar servidor WhatsApp na porta 3001',
        '5. Testar novamente'
    ];
}

echo json_encode($resultados, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
?> 