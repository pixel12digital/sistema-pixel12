<?php
require_once 'config.php';
require_once 'painel/db.php';

echo "🔧 CONFIGURANDO IDENTIFICADOR DO CANAL 3001\n";
echo "===========================================\n\n";

// 1. Verificar status do servidor 3001
echo "🔍 VERIFICANDO STATUS DO SERVIDOR 3001:\n";
$vps_ip = '212.85.11.238';

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "http://{$vps_ip}:3001/status");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

if ($http_code !== 200) {
    echo "❌ Erro ao acessar servidor 3001\n";
    echo "   HTTP Code: $http_code\n";
    echo "   Erro: $error\n";
    exit(1);
}

echo "✅ Servidor 3001 está funcionando!\n";

// 2. Decodificar resposta JSON
$data = json_decode($response, true);
if (!$data) {
    echo "❌ Erro ao decodificar resposta JSON\n";
    echo "   Resposta: $response\n";
    exit(1);
}

echo "   Status: " . ($data['ready'] ? 'Conectado' : 'Aguardando QR') . "\n";
echo "   Mensagem: " . ($data['message'] ?? 'N/A') . "\n";

// 3. Verificar se há número conectado
if (isset($data['clients_status']['default']['number'])) {
    $numero_whatsapp = $data['clients_status']['default']['number'];
    echo "   Número conectado: $numero_whatsapp\n";
    
    // Configurar identificador
    $identificador = $numero_whatsapp . '@c.us';
    
    echo "\n🔧 CONFIGURANDO IDENTIFICADOR:\n";
    echo "   Número: $numero_whatsapp\n";
    echo "   Identificador: $identificador\n";
    
    // Atualizar canal no banco
    $update = $mysqli->query("UPDATE canais_comunicacao SET identificador = '$identificador', status = 'conectado', data_conexao = NOW() WHERE porta = 3001");
    
    if ($update) {
        echo "✅ Identificador configurado com sucesso!\n";
        echo "✅ Status atualizado para 'conectado'\n";
    } else {
        echo "❌ Erro ao configurar identificador: " . $mysqli->error . "\n";
        exit(1);
    }
    
} else {
    echo "⚠️ WhatsApp conectado mas número não disponível\n";
    echo "   Tentando obter número via endpoint /info...\n";
    
    // Tentar obter informações via endpoint /info
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "http://{$vps_ip}:3001/info");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $info_response = curl_exec($ch);
    $info_http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($info_http_code === 200) {
        $info_data = json_decode($info_response, true);
        if ($info_data && isset($info_data['number'])) {
            $numero_whatsapp = $info_data['number'];
            $identificador = $numero_whatsapp . '@c.us';
            
            echo "   Número obtido via /info: $numero_whatsapp\n";
            
            $update = $mysqli->query("UPDATE canais_comunicacao SET identificador = '$identificador', status = 'conectado', data_conexao = NOW() WHERE porta = 3001");
            
            if ($update) {
                echo "✅ Identificador configurado via /info!\n";
            } else {
                echo "❌ Erro ao configurar identificador: " . $mysqli->error . "\n";
            }
        } else {
            echo "❌ Número não encontrado na resposta /info\n";
            echo "   Resposta: $info_response\n";
        }
    } else {
        echo "❌ Endpoint /info não disponível (HTTP $info_http_code)\n";
    }
}

// 4. Verificar configuração final
echo "\n📊 CONFIGURAÇÃO FINAL DO CANAL 3001:\n";
$canal_3001 = $mysqli->query("SELECT * FROM canais_comunicacao WHERE porta = 3001")->fetch_assoc();

if ($canal_3001) {
    $status_icon = $canal_3001['status'] === 'conectado' ? '🟢' : '🔴';
    echo "   {$status_icon} {$canal_3001['nome_exibicao']} (ID: {$canal_3001['id']})\n";
    echo "      Porta: {$canal_3001['porta']} | Status: {$canal_3001['status']}\n";
    echo "      Identificador: " . ($canal_3001['identificador'] ?: 'Não definido') . "\n";
    echo "      Data Conexão: " . ($canal_3001['data_conexao'] ?: 'Não conectado') . "\n";
    
    if ($canal_3001['status'] === 'conectado' && !empty($canal_3001['identificador'])) {
        echo "\n✅ CANAL 3001 CONFIGURADO COM SUCESSO!\n";
        echo "🎯 Próximos passos:\n";
        echo "   1. Teste enviar uma mensagem para o número {$canal_3001['identificador']}\n";
        echo "   2. Verifique se a mensagem aparece no chat do sistema\n";
        echo "   3. Confirme que está associada ao canal Comercial (ID 37)\n";
    } else {
        echo "\n⚠️ Canal ainda não está totalmente configurado\n";
    }
} else {
    echo "❌ Canal 3001 não encontrado no banco de dados\n";
}

echo "\n🎯 CONFIGURAÇÃO CONCLUÍDA!\n";
?> 