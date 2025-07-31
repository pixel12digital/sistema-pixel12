<?php
require_once 'config.php';
require_once 'painel/db.php';

echo "📱 CAPTURANDO NÚMERO AUTOMATICAMENTE\n";
echo "===================================\n\n";

echo "📋 FUNCIONAMENTO:\n";
echo "   ✅ Sistema detecta automaticamente o número conectado\n";
echo "   ✅ Atualiza o identificador do canal comercial\n";
echo "   ✅ Evita erros de digitação manual\n\n";

// 1. Verificar status atual
echo "📊 STATUS ATUAL:\n";
$result = $mysqli->query("SELECT id, nome_exibicao, porta, status, identificador FROM canais_comunicacao ORDER BY porta, id");
if ($result && $result->num_rows > 0) {
    while ($canal = $result->fetch_assoc()) {
        $status_icon = $canal['status'] === 'conectado' ? '🟢' : '🔴';
        echo "   {$status_icon} {$canal['nome_exibicao']} (ID: {$canal['id']})\n";
        echo "      Porta: {$canal['porta']} | Status: {$canal['status']}\n";
        echo "      Identificador: " . ($canal['identificador'] ?: 'Não definido') . "\n\n";
    }
}

// 2. Verificar se porta 3001 está funcionando
echo "🔍 VERIFICANDO PORTA 3001:\n";
$vps_ip = '212.85.11.238';

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "http://{$vps_ip}:3001/status");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

if ($http_code === 200) {
    $data = json_decode($response, true);
    if ($data) {
        echo "✅ Servidor na porta 3001 está funcionando!\n";
        echo "   Status: " . ($data['ready'] ? 'Conectado' : 'Aguardando QR') . "\n";
        
        if ($data['ready'] && isset($data['clients_status']['default']['number'])) {
            $numero_detectado = $data['clients_status']['default']['number'];
            echo "   Número detectado: $numero_detectado\n";
            
            // Atualizar automaticamente o identificador do canal comercial
            $update = $mysqli->query("UPDATE canais_comunicacao SET identificador = '$numero_detectado' WHERE nome_exibicao LIKE '%Comercial%'");
            if ($update) {
                echo "✅ Número capturado automaticamente: $numero_detectado\n";
            } else {
                echo "❌ Erro ao atualizar número: " . $mysqli->error . "\n";
            }
        } else {
            echo "   Aguardando QR code ser lido...\n";
            echo "   Conecte o WhatsApp na porta 3001 primeiro\n";
        }
    }
} else {
    echo "❌ Servidor não está rodando na porta 3001\n";
    echo "   HTTP Code: $http_code\n";
    echo "   Erro: $error\n";
    echo "\n🚨 PRIMEIRO PASSO:\n";
    echo "   Configure o servidor WhatsApp na porta 3001 da VPS\n";
}

// 3. Criar script de monitoramento automático
echo "\n🔧 CRIANDO SCRIPT DE MONITORAMENTO:\n";

$monitor_script = '<?php
// Monitor automático para capturar número do canal comercial
require_once "config.php";
require_once "painel/db.php";

$vps_ip = "212.85.11.238";

// Verificar porta 3001
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "http://{$vps_ip}:3001/status");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code === 200) {
    $data = json_decode($response, true);
    if ($data && $data["ready"] && isset($data["clients_status"]["default"]["number"])) {
        $numero = $data["clients_status"]["default"]["number"];
        
        // Verificar se o número já está configurado
        $canal = $mysqli->query("SELECT identificador FROM canais_comunicacao WHERE nome_exibicao LIKE \"%Comercial%\"")->fetch_assoc();
        
        if (!$canal["identificador"] || $canal["identificador"] !== $numero) {
            $update = $mysqli->query("UPDATE canais_comunicacao SET identificador = \"$numero\", status = \"conectado\", data_conexao = NOW() WHERE nome_exibicao LIKE \"%Comercial%\"");
            if ($update) {
                echo "✅ Número capturado automaticamente: $numero\n";
                file_put_contents("logs/captura_numero.log", date("Y-m-d H:i:s") . " - Número capturado: $numero\n", FILE_APPEND);
            }
        }
    }
}
?>';

file_put_contents('monitor_captura_numero.php', $monitor_script);
echo "✅ Script de monitoramento criado: monitor_captura_numero.php\n";

// 4. Instruções para uso
echo "\n🎯 COMO USAR:\n";
echo "   1. Configure o servidor WhatsApp na porta 3001 da VPS\n";
echo "   2. Execute: php monitor_captura_numero.php\n";
echo "   3. Ou configure para rodar automaticamente\n";
echo "   4. O sistema capturará o número automaticamente\n\n";

echo "🔧 COMANDOS NA VPS:\n";
echo "   # Configurar servidor na porta 3001\n";
echo "   # (depende da sua configuração atual)\n\n";

echo "🔧 COMANDOS LOCAIS:\n";
echo "   # Monitorar e capturar número\n";
echo "   php monitor_captura_numero.php\n\n";

// 5. Verificar configuração final
echo "📊 CONFIGURAÇÃO FINAL:\n";
$result_final = $mysqli->query("SELECT id, nome_exibicao, porta, status, identificador FROM canais_comunicacao ORDER BY porta, id");
if ($result_final && $result_final->num_rows > 0) {
    while ($canal = $result_final->fetch_assoc()) {
        $status_icon = $canal['status'] === 'conectado' ? '🟢' : '🔴';
        echo "   {$status_icon} {$canal['nome_exibicao']} (ID: {$canal['id']})\n";
        echo "      Porta: {$canal['porta']} | Status: {$canal['status']}\n";
        echo "      Identificador: " . ($canal['identificador'] ?: 'Não definido') . "\n\n";
    }
}

echo "✅ SISTEMA DE CAPTURA AUTOMÁTICA CONFIGURADO!\n";
echo "Agora configure o servidor na porta 3001 e execute o monitor.\n";
?> 