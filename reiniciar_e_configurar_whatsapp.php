<?php
/**
 * Script para Reiniciar e Configurar WhatsApp
 * Executa todos os comandos necessários na sequência correta
 */

echo "🚀 REINICIANDO E CONFIGURANDO WHATSAPP\n";
echo "=====================================\n\n";

function executeCommand($command, $description) {
    echo "📋 $description\n";
    echo "🔗 Comando: $command\n";
    echo "⏳ Executando...\n";
    
    $output = shell_exec($command . ' 2>&1');
    $returnCode = $this->getLastReturnCode();
    
    echo "📄 Resultado:\n";
    echo $output ? $output : "(nenhum output)\n";
    echo "🔢 Código de retorno: $returnCode\n";
    echo "---\n\n";
    
    return $output;
}

function testEndpoint($url, $description) {
    echo "🔗 Testando: $description\n";
    echo "📡 URL: $url\n";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_NOBODY, false);
    curl_setopt($ch, CURLOPT_USERAGENT, 'WhatsApp-Config/1.0');
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    curl_close($ch);
    
    echo "📊 Status: $http_code\n";
    if ($curl_error) {
        echo "❌ Erro: $curl_error\n";
    } else {
        $parts = explode("\r\n\r\n", $response, 2);
        $body = $parts[1] ?? '';
        $json = json_decode($body, true);
        
        if ($json) {
            echo "✅ JSON válido\n";
            if (isset($json['success'])) {
                echo "📊 Success: " . ($json['success'] ? 'true' : 'false') . "\n";
            }
            if (isset($json['sessions'])) {
                echo "📱 Sessões: " . $json['sessions'] . "\n";
            }
            if (isset($json['clients_status'])) {
                echo "📋 Status das sessões:\n";
                foreach ($json['clients_status'] as $session => $status) {
                    echo "   - $session: " . ($status['status'] ?? 'unknown') . "\n";
                }
            }
            if (isset($json['qr'])) {
                echo "🎯 QR: " . (empty($json['qr']) ? 'não disponível' : 'disponível (' . strlen($json['qr']) . ' chars)') . "\n";
            }
        } else {
            echo "⚠️ Resposta não é JSON\n";
            echo "📄 Body: " . substr($body, 0, 300) . "...\n";
        }
    }
    echo "---\n\n";
}

echo "📋 PASSO 1: REINICIAR PROCESSOS PM2\n";
echo "-----------------------------------\n";

// Nota: Como estamos no Windows, não podemos executar comandos SSH diretamente
// Vou mostrar os comandos que devem ser executados na VPS

echo "⚠️ ATENÇÃO: Os seguintes comandos devem ser executados na VPS via SSH:\n\n";

echo "1. Conectar na VPS:\n";
echo "   ssh root@212.85.11.238\n\n";

echo "2. Reiniciar processos PM2:\n";
echo "   pm2 restart whatsapp-3000\n";
echo "   pm2 restart whatsapp-3001\n\n";

echo "3. Verificar status:\n";
echo "   pm2 ls\n\n";

echo "4. Iniciar sessão comercial:\n";
echo "   curl -X POST \"http://212.85.11.238:3001/session/start/comercial\"\n\n";

echo "5. Verificar status da sessão comercial:\n";
echo "   curl -i \"http://212.85.11.238:3001/status\"\n\n";

echo "6. Iniciar sessão default (se necessário):\n";
echo "   curl -X POST \"http://212.85.11.238:3000/session/start/default\"\n\n";

echo "7. Verificar status da sessão default:\n";
echo "   curl -i \"http://212.85.11.238:3000/status\"\n\n";

echo "📋 PASSO 2: TESTAR ENDPOINTS APÓS REINICIALIZAÇÃO\n";
echo "------------------------------------------------\n";

echo "🔗 Testando status geral da VPS 3001:\n";
testEndpoint('http://212.85.11.238:3001/status', 'Status geral VPS 3001');

echo "🔗 Testando status geral da VPS 3000:\n";
testEndpoint('http://212.85.11.238:3000/status', 'Status geral VPS 3000');

echo "🔗 Testando QR da sessão default:\n";
testEndpoint('http://212.85.11.238:3000/qr?session=default', 'QR sessão default');

echo "🔗 Testando QR da sessão comercial:\n";
testEndpoint('http://212.85.11.238:3001/qr?session=comercial', 'QR sessão comercial');

echo "📋 PASSO 3: TESTAR PROXY LOCAL\n";
echo "-------------------------------\n";

echo "🔗 Testando proxy com action=status (porta 3000):\n";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://localhost:8080/loja-virtual-revenda/painel/ajax_whatsapp.php');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, 'action=status&porta=3000');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "📊 Status: $http_code\n";
if ($http_code == 200) {
    $json = json_decode($response, true);
    if ($json) {
        echo "✅ JSON válido\n";
        if (isset($json['success'])) {
            echo "📊 Success: " . ($json['success'] ? 'true' : 'false') . "\n";
        }
        if (isset($json['status'])) {
            echo "📋 Status: " . $json['status'] . "\n";
        }
        if (isset($json['debug'])) {
            echo "🔍 Debug info disponível\n";
        }
    }
} else {
    echo "❌ Erro HTTP: $http_code\n";
}

echo "---\n\n";

echo "🔗 Testando proxy com action=qr (porta 3001):\n";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://localhost:8080/loja-virtual-revenda/painel/ajax_whatsapp.php');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, 'action=qr&porta=3001');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "📊 Status: $http_code\n";
if ($http_code == 200) {
    $json = json_decode($response, true);
    if ($json) {
        echo "✅ JSON válido\n";
        if (isset($json['success'])) {
            echo "📊 Success: " . ($json['success'] ? 'true' : 'false') . "\n";
        }
        if (isset($json['qr'])) {
            echo "🎯 QR: " . (empty($json['qr']) ? 'não disponível' : 'disponível (' . strlen($json['qr']) . ' chars)') . "\n";
        }
        if (isset($json['debug'])) {
            echo "🔍 Debug info disponível\n";
        }
    }
} else {
    echo "❌ Erro HTTP: $http_code\n";
}

echo "---\n\n";

echo "📋 INSTRUÇÕES FINAIS\n";
echo "--------------------\n";

echo "1. Execute os comandos SSH na VPS conforme listado acima\n";
echo "2. Após executar os comandos, execute este script novamente para verificar\n";
echo "3. Se tudo estiver funcionando, teste no painel administrativo:\n";
echo "   - Clique em 'Atualizar Status (CORS-FREE)'\n";
echo "   - Clique em 'Conectar' em cada canal\n";
echo "   - Escaneie os QR Codes\n";
echo "   - Confirme que o status mude para 'Conectado'\n\n";

echo "✅ Script de configuração finalizado!\n";
?> 