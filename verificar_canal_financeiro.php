<?php
/**
 * VERIFICAR E CONFIGURAR CANAL FINANCEIRO
 * 
 * Script para verificar se o canal financeiro existe e configurá-lo se necessário
 */

require_once 'config.php';
require_once 'painel/db.php';

echo "🔍 VERIFICANDO CANAL FINANCEIRO\n";
echo "==============================\n\n";

// Verificar se o canal financeiro existe
$sql = "SELECT id, nome_exibicao, identificador, status, data_conexao 
        FROM canais_comunicacao 
        WHERE tipo = 'whatsapp' AND (id = 36 OR nome_exibicao LIKE '%financeiro%')
        ORDER BY id";

$result = $mysqli->query($sql);

if ($result && $result->num_rows > 0) {
    echo "✅ Canais WhatsApp financeiros encontrados:\n\n";
    while ($canal = $result->fetch_assoc()) {
        echo "   ID: {$canal['id']}\n";
        echo "   Nome: {$canal['nome_exibicao']}\n";
        echo "   Identificador: {$canal['identificador']}\n";
        echo "   Status: {$canal['status']}\n";
        echo "   Última conexão: {$canal['data_conexao']}\n";
        echo "   " . str_repeat("-", 30) . "\n";
    }
} else {
    echo "❌ Nenhum canal financeiro encontrado.\n";
    echo "Criando canal financeiro...\n\n";
    
    $sql_criar = "INSERT INTO canais_comunicacao (tipo, identificador, nome_exibicao, status, data_conexao) 
                  VALUES ('whatsapp', 'financeiro', 'WhatsApp Financeiro', 'conectado', NOW())";
    
    if ($mysqli->query($sql_criar)) {
        $canal_id = $mysqli->insert_id;
        echo "✅ Canal financeiro criado com sucesso!\n";
        echo "   ID: $canal_id\n";
        echo "   Nome: WhatsApp Financeiro\n";
        echo "   Identificador: financeiro\n";
        echo "   Status: conectado\n";
    } else {
        echo "❌ Erro ao criar canal: " . $mysqli->error . "\n";
    }
}

echo "\n📊 ESTATÍSTICAS DOS CANAIS\n";
echo "==========================\n\n";

// Contar mensagens por canal
$sql_stats = "SELECT 
                cc.id,
                cc.nome_exibicao,
                COUNT(mc.id) as total_mensagens,
                COUNT(CASE WHEN mc.direcao = 'recebido' THEN 1 END) as mensagens_recebidas,
                COUNT(CASE WHEN mc.direcao = 'enviado' THEN 1 END) as mensagens_enviadas,
                MAX(mc.data_hora) as ultima_mensagem
              FROM canais_comunicacao cc
              LEFT JOIN mensagens_comunicacao mc ON cc.id = mc.canal_id
              WHERE cc.tipo = 'whatsapp'
              GROUP BY cc.id, cc.nome_exibicao
              ORDER BY cc.id";

$result_stats = $mysqli->query($sql_stats);

if ($result_stats && $result_stats->num_rows > 0) {
    while ($stats = $result_stats->fetch_assoc()) {
        echo "📡 Canal: {$stats['nome_exibicao']} (ID: {$stats['id']})\n";
        echo "   Total de mensagens: {$stats['total_mensagens']}\n";
        echo "   Recebidas: {$stats['mensagens_recebidas']}\n";
        echo "   Enviadas: {$stats['mensagens_enviadas']}\n";
        echo "   Última mensagem: " . ($stats['ultima_mensagem'] ?: 'N/A') . "\n";
        echo "   " . str_repeat("-", 30) . "\n";
    }
} else {
    echo "Nenhuma mensagem encontrada nos canais WhatsApp.\n";
}

echo "\n🔧 CONFIGURAÇÃO DO WEBHOOK\n";
echo "==========================\n\n";

// Verificar configuração do webhook
$webhook_url = $is_local ? 'http://localhost:8080/loja-virtual-revenda/api/webhook_whatsapp.php' : 'https://revendawebvirtual.com.br/api/webhook_whatsapp.php';

echo "URL do Webhook: $webhook_url\n";
echo "Ambiente: " . ($is_local ? 'LOCAL' : 'PRODUÇÃO') . "\n";

// Testar se o webhook está acessível
$ch = curl_init($webhook_url);
curl_setopt($ch, CURLOPT_NOBODY, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$result = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "Status do Webhook: ";
if ($http_code == 200) {
    echo "✅ Acessível (HTTP $http_code)\n";
} else {
    echo "❌ Problema (HTTP $http_code)\n";
}

echo "\n✅ Verificação concluída!\n";
?> 