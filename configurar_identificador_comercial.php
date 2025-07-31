<?php
require_once 'config.php';
require_once 'painel/db.php';

echo "🔧 Configurando identificador do canal comercial...\n\n";

// 1. Verificar status atual
echo "📊 STATUS ATUAL DOS CANAIS:\n";
$result = $mysqli->query("SELECT id, nome_exibicao, porta, status, identificador FROM canais_comunicacao ORDER BY porta, id");
if ($result && $result->num_rows > 0) {
    while ($canal = $result->fetch_assoc()) {
        $status_icon = $canal['status'] === 'conectado' ? '🟢' : '🔴';
        echo "   {$status_icon} {$canal['nome_exibicao']} (ID: {$canal['id']})\n";
        echo "      Porta: {$canal['porta']}\n";
        echo "      Status DB: {$canal['status']}\n";
        echo "      Identificador: " . ($canal['identificador'] ?: 'Não definido') . "\n\n";
    }
}

// 2. Verificar status real do servidor
echo "🔍 VERIFICANDO STATUS REAL DO SERVIDOR:\n";
$vps_ip = '212.85.11.238';
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "http://{$vps_ip}:3000/status");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code === 200) {
    $data = json_decode($response, true);
    if ($data) {
        echo "✅ Servidor WhatsApp está funcionando\n";
        echo "   Status: " . ($data['ready'] ? 'Conectado' : 'Aguardando QR') . "\n";
        echo "   Mensagem: " . ($data['message'] ?? 'N/A') . "\n";
        
        // Verificar se há número conectado
        if (isset($data['clients_status']['default']['number'])) {
            $numero_conectado = $data['clients_status']['default']['number'];
            echo "   Número conectado: $numero_conectado\n";
            
            // Perguntar se quer usar este número para o canal comercial
            echo "\n❓ Deseja usar o número $numero_conectado para o canal comercial? (s/n): ";
            $handle = fopen("php://stdin", "r");
            $resposta = trim(fgets($handle));
            fclose($handle);
            
            if (strtolower($resposta) === 's') {
                $update = $mysqli->query("UPDATE canais_comunicacao SET identificador = '$numero_conectado' WHERE nome_exibicao LIKE '%Comercial%'");
                if ($update) {
                    echo "✅ Identificador do canal comercial configurado: $numero_conectado\n";
                } else {
                    echo "❌ Erro ao configurar identificador: " . $mysqli->error . "\n";
                }
            }
        } else {
            echo "   Nenhum número específico detectado\n";
        }
    }
} else {
    echo "❌ Servidor não está respondendo\n";
}

// 3. Explicar como funciona o sistema
echo "\n📋 COMO FUNCIONA O SISTEMA:\n";
echo "   🔄 A interface mostra o status REAL do servidor (tempo real)\n";
echo "   💾 O banco de dados armazena a configuração dos canais\n";
echo "   🔗 Ambos os canais usam a mesma porta 3000 (mesmo servidor)\n";
echo "   📱 O servidor pode ter apenas UMA sessão WhatsApp ativa\n";
echo "   🎯 O identificador serve para identificar qual número está conectado\n";

// 4. Verificar se há conflito
echo "\n⚠️ POSSÍVEL CONFLITO IDENTIFICADO:\n";
echo "   - Ambos os canais estão configurados para porta 3000\n";
echo "   - O servidor só pode ter UMA sessão WhatsApp ativa\n";
echo "   - Isso significa que apenas UM canal pode estar realmente conectado\n";
echo "   - O outro canal vai mostrar status incorreto\n";

// 5. Sugerir soluções
echo "\n🔧 SOLUÇÕES POSSÍVEIS:\n";
echo "   OPÇÃO 1: Usar apenas um canal (recomendado)\n";
echo "   - Manter apenas o canal Financeiro\n";
echo "   - Remover o canal Comercial\n";
echo "   - Usar o mesmo número para ambos os departamentos\n\n";
echo "   OPÇÃO 2: Configurar porta separada\n";
echo "   - Configurar servidor na porta 3001\n";
echo "   - Cada canal usa uma porta diferente\n";
echo "   - Requer configuração adicional na VPS\n\n";
echo "   OPÇÃO 3: Usar sessões múltiplas\n";
echo "   - Configurar servidor para suportar múltiplas sessões\n";
echo "   - Requer modificação do servidor WhatsApp\n";

// 6. Perguntar qual opção preferir
echo "\n❓ Qual opção você prefere?\n";
echo "   1 - Usar apenas um canal (Financeiro)\n";
echo "   2 - Configurar porta separada (3001)\n";
echo "   3 - Manter como está (pode causar conflitos)\n";
echo "   Digite 1, 2 ou 3: ";

$handle = fopen("php://stdin", "r");
$opcao = trim(fgets($handle));
fclose($handle);

if ($opcao === '1') {
    echo "\n🔧 Removendo canal comercial...\n";
    $delete = $mysqli->query("DELETE FROM canais_comunicacao WHERE nome_exibicao LIKE '%Comercial%'");
    if ($delete) {
        echo "✅ Canal comercial removido\n";
        echo "✅ Agora apenas o canal Financeiro está ativo\n";
        echo "✅ Use o número 554797146908 para ambos os departamentos\n";
    }
} elseif ($opcao === '2') {
    echo "\n🔧 Configurando porta separada...\n";
    echo "   Esta opção requer acesso à VPS para configurar porta 3001\n";
    echo "   Por enquanto, mantenha apenas o canal Financeiro\n";
} else {
    echo "\n⚠️ Mantendo configuração atual\n";
    echo "   ATENÇÃO: Pode haver conflitos entre os canais\n";
    echo "   Recomenda-se usar apenas um canal por vez\n";
}

echo "\n✅ Configuração concluída!\n";
?> 