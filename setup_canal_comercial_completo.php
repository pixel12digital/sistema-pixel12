<?php
require_once 'config.php';
require_once 'painel/db.php';

echo "🎯 SETUP COMPLETO - CANAL COMERCIAL\n";
echo "===================================\n\n";

echo "📋 OBJETIVO:\n";
echo "   ✅ Configurar canal comercial na porta 3001\n";
echo "   ✅ Capturar número automaticamente\n";
echo "   ✅ Evitar erros de digitação\n\n";

// 1. Status atual
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

// 2. Verificar se canal comercial está configurado corretamente
echo "🔧 VERIFICANDO CONFIGURAÇÃO:\n";
$canal_comercial = $mysqli->query("SELECT * FROM canais_comunicacao WHERE nome_exibicao LIKE '%Comercial%'")->fetch_assoc();

if ($canal_comercial && $canal_comercial['porta'] == 3001) {
    echo "✅ Canal comercial já configurado para porta 3001\n";
} else {
    echo "🔧 Configurando canal comercial para porta 3001...\n";
    $update = $mysqli->query("UPDATE canais_comunicacao SET porta = 3001, status = 'pendente', data_conexao = NULL WHERE nome_exibicao LIKE '%Comercial%'");
    if ($update) {
        echo "✅ Canal comercial configurado para porta 3001\n";
    }
}

// 3. Verificar servidor na porta 3001
echo "\n🔍 VERIFICANDO SERVIDOR PORTA 3001:\n";
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
            
            // Atualizar automaticamente
            $update = $mysqli->query("UPDATE canais_comunicacao SET identificador = '$numero_detectado', status = 'conectado', data_conexao = NOW() WHERE nome_exibicao LIKE '%Comercial%'");
            if ($update) {
                echo "✅ Número capturado automaticamente: $numero_detectado\n";
                echo "✅ Canal comercial conectado!\n";
            }
        } else {
            echo "   Aguardando QR code ser lido...\n";
        }
    }
} else {
    echo "❌ Servidor não está rodando na porta 3001\n";
    echo "   HTTP Code: $http_code\n";
    echo "   Erro: $error\n";
}

// 4. Criar script de monitoramento contínuo
echo "\n🔧 CRIANDO MONITOR CONTÍNUO:\n";

$monitor_script = '<?php
// Monitor contínuo para canal comercial
require_once "config.php";
require_once "painel/db.php";

$vps_ip = "212.85.11.238";
$max_attempts = 60; // 5 minutos (5 segundos cada)
$attempt = 0;

echo "🔍 Monitorando porta 3001 para capturar número...\n";

while ($attempt < $max_attempts) {
    $attempt++;
    
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
            
            // Verificar se já está configurado
            $canal = $mysqli->query("SELECT identificador FROM canais_comunicacao WHERE nome_exibicao LIKE \"%Comercial%\"")->fetch_assoc();
            
            if (!$canal["identificador"] || $canal["identificador"] !== $numero) {
                $update = $mysqli->query("UPDATE canais_comunicacao SET identificador = \"$numero\", status = \"conectado\", data_conexao = NOW() WHERE nome_exibicao LIKE \"%Comercial%\"");
                if ($update) {
                    echo "✅ Número capturado automaticamente: $numero\n";
                    echo "✅ Canal comercial conectado com sucesso!\n";
                    file_put_contents("logs/captura_numero.log", date("Y-m-d H:i:s") . " - Número capturado: $numero\n", FILE_APPEND);
                    exit(0);
                }
            } else {
                echo "✅ Número já configurado: $numero\n";
                exit(0);
            }
        } else {
            echo "⏳ Aguardando QR code ser lido... (tentativa $attempt/$max_attempts)\n";
        }
    } else {
        echo "❌ Servidor não responde... (tentativa $attempt/$max_attempts)\n";
    }
    
    sleep(5);
}

echo "❌ Timeout: QR code não foi lido em 5 minutos\n";
echo "   Verifique se o servidor está rodando na porta 3001\n";
exit(1);
?>';

file_put_contents('monitor_continuo_comercial.php', $monitor_script);
echo "✅ Monitor contínuo criado: monitor_continuo_comercial.php\n";

// 5. Instruções finais
echo "\n🎯 PRÓXIMOS PASSOS:\n";
echo "   1. Configure o servidor WhatsApp na porta 3001 da VPS\n";
echo "   2. Execute: php monitor_continuo_comercial.php\n";
echo "   3. Escaneie o QR code com o WhatsApp comercial\n";
echo "   4. O sistema capturará o número automaticamente\n\n";

echo "🔧 COMANDOS NA VPS:\n";
echo "   ssh root@212.85.11.238\n";
echo "   # Configure servidor na porta 3001\n";
echo "   # (depende da sua configuração atual)\n\n";

echo "🔧 COMANDOS LOCAIS:\n";
echo "   # Monitorar e capturar número\n";
echo "   php monitor_continuo_comercial.php\n\n";

// 6. Status final
echo "📊 STATUS FINAL:\n";
$result_final = $mysqli->query("SELECT id, nome_exibicao, porta, status, identificador FROM canais_comunicacao ORDER BY porta, id");
if ($result_final && $result_final->num_rows > 0) {
    while ($canal = $result_final->fetch_assoc()) {
        $status_icon = $canal['status'] === 'conectado' ? '🟢' : '🔴';
        echo "   {$status_icon} {$canal['nome_exibicao']} (ID: {$canal['id']})\n";
        echo "      Porta: {$canal['porta']} | Status: {$canal['status']}\n";
        echo "      Identificador: " . ($canal['identificador'] ?: 'Não definido') . "\n\n";
    }
}

echo "✅ SETUP COMPLETO CONCLUÍDO!\n";
echo "Configure o servidor na porta 3001 e execute o monitor.\n";
?> 