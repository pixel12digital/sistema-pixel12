<?php
require_once 'config.php';
require_once 'painel/db.php';

echo "🔧 CONFIGURANDO CANAL COMERCIAL SEPARADO\n";
echo "========================================\n\n";

echo "📋 OBJETIVO:\n";
echo "   ✅ Manter canal Financeiro na porta 3000 (554797146908)\n";
echo "   ✅ Configurar canal Comercial na porta 3001 (número diferente)\n";
echo "   ✅ Ambos funcionando independentemente\n\n";

// 1. Verificar status atual
echo "📊 STATUS ATUAL DOS CANAIS:\n";
$result = $mysqli->query("SELECT id, nome_exibicao, porta, status, identificador FROM canais_comunicacao ORDER BY porta, id");
if ($result && $result->num_rows > 0) {
    while ($canal = $result->fetch_assoc()) {
        $status_icon = $canal['status'] === 'conectado' ? '🟢' : '🔴';
        echo "   {$status_icon} {$canal['nome_exibicao']} (ID: {$canal['id']})\n";
        echo "      Porta: {$canal['porta']} | Status: {$canal['status']}\n";
        echo "      Identificador: " . ($canal['identificador'] ?: 'Não definido') . "\n\n";
    }
}

// 2. Verificar se porta 3001 está disponível
echo "🔍 VERIFICANDO PORTA 3001:\n";
$vps_ip = '212.85.11.238';

// Teste de conectividade TCP
$conexao_tcp = @fsockopen($vps_ip, 3001, $errno, $errstr, 3);
if ($conexao_tcp) {
    fclose($conexao_tcp);
    echo "⚠️ Porta 3001 está aberta (pode estar em uso)\n";
} else {
    echo "✅ Porta 3001 está livre\n";
}

// Teste HTTP
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
    echo "✅ Servidor WhatsApp já está rodando na porta 3001!\n";
    $data = json_decode($response, true);
    if ($data) {
        echo "   Status: " . ($data['ready'] ? 'Conectado' : 'Aguardando QR') . "\n";
        if (isset($data['clients_status']['default']['number'])) {
            echo "   Número: " . $data['clients_status']['default']['number'] . "\n";
        }
    }
} else {
    echo "❌ Servidor não está rodando na porta 3001\n";
    echo "   HTTP Code: $http_code\n";
    echo "   Erro: $error\n";
}

// 3. Configurar canal comercial para porta 3001
echo "\n🔧 CONFIGURANDO CANAL COMERCIAL:\n";

// Verificar se canal comercial existe
$canal_comercial = $mysqli->query("SELECT * FROM canais_comunicacao WHERE nome_exibicao LIKE '%Comercial%'")->fetch_assoc();

if ($canal_comercial) {
    echo "📱 Canal comercial encontrado (ID: {$canal_comercial['id']})\n";
    
    // Atualizar para porta 3001
    $update = $mysqli->query("UPDATE canais_comunicacao SET porta = 3001, status = 'pendente', data_conexao = NULL WHERE id = {$canal_comercial['id']}");
    if ($update) {
        echo "✅ Canal comercial configurado para porta 3001\n";
    } else {
        echo "❌ Erro ao configurar canal: " . $mysqli->error . "\n";
    }
} else {
    echo "📱 Criando novo canal comercial...\n";
    $insert = $mysqli->query("INSERT INTO canais_comunicacao (tipo, identificador, nome_exibicao, status, porta) VALUES ('whatsapp', '', 'Comercial - Pixel', 'pendente', 3001)");
    if ($insert) {
        echo "✅ Canal comercial criado na porta 3001\n";
    } else {
        echo "❌ Erro ao criar canal: " . $mysqli->error . "\n";
    }
}

// 4. Verificar configuração final
echo "\n📊 CONFIGURAÇÃO FINAL:\n";
$result_final = $mysqli->query("SELECT id, nome_exibicao, porta, status, identificador FROM canais_comunicacao ORDER BY porta, id");
if ($result_final && $result_final->num_rows > 0) {
    while ($canal = $result_final->fetch_assoc()) {
        $status_icon = $canal['status'] === 'conectado' ? '🟢' : '🔴';
        echo "   {$status_icon} {$canal['nome_exibicao']} (ID: {$canal['id']})\n";
        echo "      Porta: {$canal['porta']} | Status: {$canal['status']}\n";
        echo "      Identificador: " . ($canal['identificador'] ?: 'Não definido') . "\n\n";
    }
}

// 5. Instruções para configurar servidor na VPS
echo "🚨 AÇÃO NECESSÁRIA NA VPS:\n";
echo "   Para que o canal comercial funcione, você precisa:\n\n";
echo "   1. Acessar a VPS: ssh root@212.85.11.238\n";
echo "   2. Verificar se porta 3001 está livre: netstat -tulpn | grep :3001\n";
echo "   3. Se estiver livre, configurar servidor WhatsApp na porta 3001\n";
echo "   4. Ou usar uma porta diferente disponível\n\n";

echo "🔧 COMANDOS SUGERIDOS NA VPS:\n";
echo "   # Verificar portas em uso\n";
echo "   netstat -tulpn | grep :300\n\n";
echo "   # Verificar processos Node.js\n";
echo "   ps aux | grep node\n\n";
echo "   # Se porta 3001 estiver livre, configurar servidor\n";
echo "   # (depende da sua configuração atual do servidor)\n\n";

// 6. Perguntar qual número usar para comercial
echo "❓ QUAL NÚMERO USAR PARA O CANAL COMERCIAL?\n";
echo "   Digite o número (apenas números, sem formatação): ";
$handle = fopen("php://stdin", "r");
$numero_comercial = trim(fgets($handle));
fclose($handle);

if (!empty($numero_comercial)) {
    $update_numero = $mysqli->query("UPDATE canais_comunicacao SET identificador = '$numero_comercial' WHERE nome_exibicao LIKE '%Comercial%'");
    if ($update_numero) {
        echo "✅ Número comercial configurado: $numero_comercial\n";
    } else {
        echo "❌ Erro ao configurar número: " . $mysqli->error . "\n";
    }
}

// 7. Resumo final
echo "\n🎯 CONFIGURAÇÃO CONCLUÍDA!\n\n";
echo "📱 CANAIS CONFIGURADOS:\n";
echo "   🟢 Financeiro: Porta 3000 - 554797146908\n";
echo "   🔴 Comercial: Porta 3001 - $numero_comercial\n\n";

echo "🔧 PRÓXIMOS PASSOS:\n";
echo "   1. Configurar servidor na porta 3001 (VPS)\n";
echo "   2. Acessar: http://localhost:8080/loja-virtual-revenda/painel/comunicacao.php\n";
echo "   3. Clique em 'Atualizar Status (CORS-FREE)'\n";
echo "   4. Canal comercial deve aparecer como 'Desconectado'\n";
echo "   5. Clique em 'Conectar' para gerar QR code\n";
echo "   6. Escaneie com o WhatsApp do número $numero_comercial\n\n";

echo "✅ CONFIGURAÇÃO APLICADA!\n";
echo "Agora configure o servidor na porta 3001 da VPS.\n";
?> 