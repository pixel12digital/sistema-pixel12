<?php
/**
 * LIMPAR CONVERSAS DUPLICADAS
 * 
 * Script para identificar e limpar conversas duplicadas do mesmo número
 */

require_once 'config.php';
require_once 'painel/db.php';

echo "🧹 LIMPANDO CONVERSAS DUPLICADAS\n";
echo "================================\n\n";

// 1. Identificar números com múltiplas conversas
echo "1️⃣ IDENTIFICANDO NÚMEROS COM MÚLTIPLAS CONVERSAS\n";
echo "================================================\n\n";

$sql_duplicados = "SELECT 
                    numero_whatsapp,
                    COUNT(DISTINCT cliente_id) as total_clientes,
                    GROUP_CONCAT(DISTINCT cliente_id ORDER BY cliente_id) as clientes_ids,
                    COUNT(*) as total_mensagens,
                    MIN(data_hora) as primeira_mensagem,
                    MAX(data_hora) as ultima_mensagem
                   FROM mensagens_comunicacao 
                   WHERE numero_whatsapp IS NOT NULL 
                   AND numero_whatsapp != ''
                   GROUP BY numero_whatsapp 
                   HAVING COUNT(DISTINCT cliente_id) > 1
                   ORDER BY total_mensagens DESC";

$result_duplicados = $mysqli->query($sql_duplicados);

if ($result_duplicados && $result_duplicados->num_rows > 0) {
    echo "Encontrados números com múltiplas conversas:\n\n";
    
    while ($duplicado = $result_duplicados->fetch_assoc()) {
        echo "📱 Número: {$duplicado['numero_whatsapp']}\n";
        echo "   Clientes: {$duplicado['clientes_ids']}\n";
        echo "   Total mensagens: {$duplicado['total_mensagens']}\n";
        echo "   Período: {$duplicado['primeira_mensagem']} até {$duplicado['ultima_mensagem']}\n";
        echo "   " . str_repeat("-", 40) . "\n";
    }
    
    echo "\n2️⃣ CONSOLIDANDO CONVERSAS\n";
    echo "==========================\n\n";
    
    // Resetar o resultado para processar novamente
    $result_duplicados = $mysqli->query($sql_duplicados);
    
    while ($duplicado = $result_duplicados->fetch_assoc()) {
        $numero = $duplicado['numero_whatsapp'];
        $clientes_ids = explode(',', $duplicado['clientes_ids']);
        
        // Escolher o cliente principal (primeiro da lista)
        $cliente_principal = $clientes_ids[0];
        $clientes_secundarios = array_slice($clientes_ids, 1);
        
        echo "📱 Processando número: $numero\n";
        echo "   Cliente principal: $cliente_principal\n";
        echo "   Clientes secundários: " . implode(', ', $clientes_secundarios) . "\n";
        
        // Atualizar mensagens dos clientes secundários para o cliente principal
        foreach ($clientes_secundarios as $cliente_secundario) {
            $sql_update = "UPDATE mensagens_comunicacao 
                          SET cliente_id = $cliente_principal 
                          WHERE numero_whatsapp = '$numero' 
                          AND cliente_id = $cliente_secundario";
            
            if ($mysqli->query($sql_update)) {
                $linhas_afetadas = $mysqli->affected_rows;
                echo "   ✅ Atualizadas $linhas_afetadas mensagens do cliente $cliente_secundario\n";
            } else {
                echo "   ❌ Erro ao atualizar cliente $cliente_secundario: " . $mysqli->error . "\n";
            }
        }
        
        echo "   " . str_repeat("-", 40) . "\n";
    }
    
} else {
    echo "✅ Nenhuma conversa duplicada encontrada!\n";
}

echo "\n3️⃣ VERIFICANDO RESULTADO\n";
echo "=========================\n\n";

// Verificar se ainda há duplicatas
$sql_verificar = "SELECT 
                   numero_whatsapp,
                   COUNT(DISTINCT cliente_id) as total_clientes,
                   COUNT(*) as total_mensagens
                  FROM mensagens_comunicacao 
                  WHERE numero_whatsapp IS NOT NULL 
                  AND numero_whatsapp != ''
                  GROUP BY numero_whatsapp 
                  HAVING COUNT(DISTINCT cliente_id) > 1
                  ORDER BY total_mensagens DESC";

$result_verificar = $mysqli->query($sql_verificar);

if ($result_verificar && $result_verificar->num_rows > 0) {
    echo "❌ Ainda existem conversas duplicadas:\n\n";
    while ($duplicado = $result_verificar->fetch_assoc()) {
        echo "   📱 {$duplicado['numero_whatsapp']}: {$duplicado['total_clientes']} clientes, {$duplicado['total_mensagens']} mensagens\n";
    }
} else {
    echo "✅ Todas as conversas foram consolidadas com sucesso!\n";
}

echo "\n4️⃣ ESTATÍSTICAS FINAIS\n";
echo "=======================\n\n";

// Estatísticas gerais
$sql_stats = "SELECT 
               COUNT(DISTINCT numero_whatsapp) as total_numeros,
               COUNT(*) as total_mensagens,
               COUNT(DISTINCT cliente_id) as total_clientes_unicos
              FROM mensagens_comunicacao 
              WHERE numero_whatsapp IS NOT NULL 
              AND numero_whatsapp != ''";

$result_stats = $mysqli->query($sql_stats);
$stats = $result_stats->fetch_assoc();

echo "📊 Estatísticas:\n";
echo "   Números únicos: {$stats['total_numeros']}\n";
echo "   Total mensagens: {$stats['total_mensagens']}\n";
echo "   Clientes únicos: {$stats['total_clientes_unicos']}\n";

echo "\n✅ Limpeza concluída!\n";
?> 