<?php
header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

$vps_ip = '212.85.11.238';
$vps_port = 3000;
$vps_url = "http://{$vps_ip}:{$vps_port}";

$resultados = [
    'timestamp' => date('Y-m-d H:i:s'),
    'vps_ip' => $vps_ip,
    'vps_port' => $vps_port,
    'vps_url' => $vps_url,
    'testes' => []
];

// Teste 1: Ping básico
$ping_result = shell_exec("ping -c 1 {$vps_ip} 2>&1");
$resultados['testes']['ping'] = [
    'nome' => 'Ping IP',
    'comando' => "ping -c 1 {$vps_ip}",
    'resultado' => $ping_result,
    'status' => strpos($ping_result, '1 received') !== false ? 'sucesso' : 'falha'
];

// Teste 2: Telnet na porta
$telnet_result = shell_exec("timeout 5 bash -c 'echo > /dev/tcp/{$vps_ip}/{$vps_port}' 2>&1 && echo 'CONECTADO' || echo 'FALHOU'");
$resultados['testes']['telnet'] = [
    'nome' => 'Teste Porta TCP',
    'comando' => "timeout 5 bash -c 'echo > /dev/tcp/{$vps_ip}/{$vps_port}'",
    'resultado' => trim($telnet_result),
    'status' => strpos($telnet_result, 'CONECTADO') !== false ? 'sucesso' : 'falha'
];

// Teste 3: cURL com timeout
$curl_start = microtime(true);
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $vps_url . '/status');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'User-Agent: VPS-Checker/1.0',
    'Accept: application/json'
]);

$curl_response = curl_exec($ch);
$curl_info = curl_getinfo($ch);
$curl_error = curl_error($ch);
$curl_time = round((microtime(true) - $curl_start) * 1000);
curl_close($ch);

$resultados['testes']['curl'] = [
    'nome' => 'HTTP Request (cURL)',
    'url' => $vps_url . '/status',
    'tempo_ms' => $curl_time,
    'http_code' => $curl_info['http_code'],
    'erro' => $curl_error,
    'resposta' => $curl_response,
    'status' => $curl_info['http_code'] === 200 ? 'sucesso' : 'falha'
];

// Teste 4: DNS lookup
$dns_result = gethostbyname($vps_ip);
$resultados['testes']['dns'] = [
    'nome' => 'Resolução DNS',
    'ip_original' => $vps_ip,
    'ip_resolvido' => $dns_result,
    'status' => $dns_result !== $vps_ip ? 'sucesso' : 'sem_mudanca'
];

// Teste 5: Traceroute (simplificado)
$trace_result = shell_exec("traceroute -m 5 {$vps_ip} 2>&1 | head -10");
$resultados['testes']['traceroute'] = [
    'nome' => 'Rota de Rede',
    'comando' => "traceroute -m 5 {$vps_ip}",
    'resultado' => $trace_result,
    'status' => !empty($trace_result) ? 'sucesso' : 'falha'
];

// Teste 6: Verificar portas comuns
$portas_teste = [80, 443, 3000, 8080];
$portas_abertas = [];

foreach ($portas_teste as $porta) {
    $conexao = @fsockopen($vps_ip, $porta, $errno, $errstr, 2);
    if ($conexao) {
        $portas_abertas[] = $porta;
        fclose($conexao);
    }
}

$resultados['testes']['portas'] = [
    'nome' => 'Scan de Portas',
    'portas_testadas' => $portas_teste,
    'portas_abertas' => $portas_abertas,
    'status' => !empty($portas_abertas) ? 'sucesso' : 'falha'
];

// Teste 7: Informações do servidor
$resultados['servidor'] = [
    'php_version' => PHP_VERSION,
    'servidor' => $_SERVER['SERVER_SOFTWARE'] ?? 'N/A',
    'ip_servidor' => $_SERVER['SERVER_ADDR'] ?? 'N/A',
    'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'N/A'
];

// Resumo final
$sucessos = 0;
$total = count($resultados['testes']);

foreach ($resultados['testes'] as $teste) {
    if ($teste['status'] === 'sucesso') {
        $sucessos++;
    }
}

$resultados['resumo'] = [
    'total_testes' => $total,
    'sucessos' => $sucessos,
    'falhas' => $total - $sucessos,
    'percentual_sucesso' => round(($sucessos / $total) * 100, 2),
    'vps_acessivel' => $sucessos > 0,
    'diagnostico' => $sucessos === 0 ? 'VPS completamente inacessível' : 
                    ($sucessos < 3 ? 'VPS com problemas de conectividade' : 
                    'VPS acessível com algumas limitações')
];

echo json_encode($resultados, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
?> 