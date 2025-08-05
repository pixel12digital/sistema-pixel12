<?php
/**
 * 🔍 VERIFICAR SE HÁ QR CODES DISPONÍVEIS
 */

echo "🔍 VERIFICANDO QR CODES DISPONÍVEIS\n";
echo "==================================\n\n";

$vps_ip = '212.85.11.238';
$canais = [3000, 3001];

foreach ($canais as $porta) {
    echo "🔄 CANAL $porta\n";
    echo "==============\n";
    
    // Obter status
    $ch = curl_init("http://$vps_ip:$porta/status");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    $response = curl_exec($ch);
    curl_close($ch);
    
    $status = json_decode($response, true);
    
    if (isset($status['clients_status'])) {
        foreach ($status['clients_status'] as $sessao => $dados) {
            echo "📱 Sessão: $sessao\n";
            echo "   Ready: " . ($dados['ready'] ? 'true' : 'false') . "\n";
            echo "   HasQR: " . ($dados['hasQR'] ? 'true' : 'false') . "\n";
            
            if (isset($dados['qr'])) {
                $qr = $dados['qr'];
                echo "   ✅ QR ENCONTRADO!\n";
                echo "   📋 QR Code: " . substr($qr, 0, 50) . "...\n";
                echo "   📏 Tamanho: " . strlen($qr) . " caracteres\n";
                
                // Salvar QR em arquivo para teste
                $arquivo_qr = "qr_canal_{$porta}_sessao_{$sessao}.txt";
                file_put_contents($arquivo_qr, $qr);
                echo "   💾 QR salvo em: $arquivo_qr\n";
                
                // Tentar gerar QR code visual
                if (function_exists('imagecreate')) {
                    // Se tiver GD, poderia gerar imagem do QR
                    echo "   🖼️ Extensão GD disponível para gerar imagem\n";
                } else {
                    echo "   ⚠️ Extensão GD não disponível\n";
                }
                
            } else {
                echo "   ❌ Sem QR code\n";
            }
            echo "\n";
        }
    } else {
        echo "❌ Nenhuma sessão encontrada\n\n";
    }
}

echo "🎯 RESULTADO:\n";
echo "============\n";
echo "Se QR codes foram encontrados e salvos, você pode:\n";
echo "1. Usar um gerador online de QR code para visualizar\n";
echo "2. Copiar o conteúdo do arquivo .txt e gerar o QR\n";
echo "3. Ou implementar exibição direta no painel\n";
?> 