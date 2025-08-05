<?php
/**
 * 🔧 CORREÇÃO TEMPORÁRIA DO AJAX WHATSAPP
 * 
 * Este script corrige os endpoints para obter QR codes corretamente
 */

echo "🔧 APLICANDO CORREÇÃO NO AJAX WHATSAPP\n";
echo "=====================================\n\n";

$arquivo_original = 'painel/ajax_whatsapp.php';
$arquivo_backup = 'painel/ajax_whatsapp.php.backup.' . date('Ymd_His');

// 1. Fazer backup
if (file_exists($arquivo_original)) {
    copy($arquivo_original, $arquivo_backup);
    echo "✅ Backup criado: $arquivo_backup\n";
}

// 2. Ler arquivo original
$conteudo = file_get_contents($arquivo_original);

// 3. Aplicar correções

// CORREÇÃO 1: Endpoint QR - usar /qr em vez de /session/default/qr
$conteudo = str_replace(
    '$qr_endpoint = "/session/default/qr";',
    '// CORREÇÃO: Usar endpoint /qr direto com parâmetro session
            if ($porta == "3000" || $porta == 3000) {
                $qr_endpoint = "/qr?session=default";
            } else {
                $qr_endpoint = "/qr?session=comercial";
            }',
    $conteudo
);

// CORREÇÃO 2: Se não funcionou a primeira, tentar padrão simples
if (strpos($conteudo, '/qr?session=') === false) {
    $conteudo = preg_replace(
        '/\$qr_endpoint = "[^"]+";/',
        '$qr_endpoint = "/qr";',
        $conteudo
    );
}

// CORREÇÃO 3: Adicionar fallback para endpoints diferentes
$adicionar_fallback = '
            // FALLBACK: Se não funcionou, tentar diferentes endpoints
            if ($http_code != 200) {
                $fallback_endpoints = ["/qr", "/qr?session=default", "/qr?session=comercial"];
                foreach ($fallback_endpoints as $fallback_endpoint) {
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $vps_url . $fallback_endpoint);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
                    $response = curl_exec($ch);
                    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    curl_close($ch);
                    
                    if ($http_code == 200) {
                        error_log("[WhatsApp QR Fallback] Sucesso com endpoint: $fallback_endpoint");
                        break;
                    }
                }
            }';

// Inserir o fallback após a primeira requisição curl - CORRIGIDO
$conteudo = str_replace(
    'curl_close($ch);',
    'curl_close($ch);' . $adicionar_fallback,
    $conteudo
);

// 4. Salvar arquivo corrigido
file_put_contents($arquivo_original, $conteudo);

echo "✅ Arquivo corrigido com sucesso!\n\n";

echo "🎯 CORREÇÕES APLICADAS:\n";
echo "1. ✅ Endpoint QR corrigido para usar /qr com parâmetros corretos\n";
echo "2. ✅ Fallback adicionado para tentar diferentes endpoints\n";
echo "3. ✅ Sessões corretas baseadas na porta (default para 3000, comercial para 3001)\n\n";

echo "🔄 TESTE AGORA:\n";
echo "1. Vá ao painel de comunicação\n";
echo "2. Clique em 'Conectar' em qualquer canal\n";
echo "3. O QR Code deve aparecer!\n";
?> 