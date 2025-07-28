<?php
/**
 * ATUALIZAR MENSAGENS EXISTENTES
 * 
 * Script para atualizar mensagens existentes com o número WhatsApp correto
 */

require_once 'config.php';
require_once 'painel/db.php';

echo "🔄 ATUALIZANDO MENSAGENS EXISTENTES\n";
echo "===================================\n\n";

// 1. Atualizar mensagens que têm cliente_id mas não têm numero_whatsapp
echo "1️⃣ ATUALIZANDO MENSAGENS COM CLIENTE_ID\n";
echo "========================================\n\n";

$sql_update = "UPDATE mensagens_comunicacao mc
               INNER JOIN clientes c ON mc.cliente_id = c.id
               SET mc.numero_whatsapp = c.celular
               WHERE mc.numero_whatsapp IS NULL 
               AND mc.cliente_id IS NOT NULL
               AND c.celular IS NOT NULL 
               AND c.celular != ''";

if ($mysqli->query($sql_update)) {
    $linhas_afetadas = $mysqli->affected_rows;
    echo "✅ Atualizadas $linhas_afetadas mensagens com número do cliente\n";
} else {
    echo "❌ Erro ao atualizar: " . $mysqli->error . "\n";
}

// 2. Verificar mensagens que ainda não têm numero_whatsapp
echo "\n2️⃣ VERIFICANDO MENSAGENS SEM NÚMERO\n";
echo "=====================================\n\n";

$sql_sem_numero = "SELECT COUNT(*) as total
                   FROM mensagens_comunicacao 
                   WHERE numero_whatsapp IS NULL 
                   OR numero_whatsapp = ''";

$result_sem_numero = $mysqli->query($sql_sem_numero);
$sem_numero = $result_sem_numero->fetch_assoc();

echo "Mensagens sem número WhatsApp: {$sem_numero['total']}\n";

if ($sem_numero['total'] > 0) {
    echo "\nDetalhes das mensagens sem número:\n";
    
    $sql_detalhes = "SELECT mc.*, c.nome as cliente_nome, c.celular
                     FROM mensagens_comunicacao mc
                     LEFT JOIN clientes c ON mc.cliente_id = c.id
                     WHERE mc.numero_whatsapp IS NULL 
                     OR mc.numero_whatsapp = ''
                     ORDER BY mc.data_hora DESC
                     LIMIT 10";
    
    $result_detalhes = $mysqli->query($sql_detalhes);
    while ($msg = $result_detalhes->fetch_assoc()) {
        $cliente = $msg['cliente_nome'] ?: 'Sem cliente';
        $celular = $msg['celular'] ?: 'Sem celular';
        $hora = date('H:i:s', strtotime($msg['data_hora']));
        
        echo "   📥 [$hora] $cliente ($celular): " . substr($msg['mensagem'], 0, 50) . "...\n";
    }
}

// 3. Estatísticas finais
echo "\n3️⃣ ESTATÍSTICAS FINAIS\n";
echo "=======================\n\n";

$sql_stats = "SELECT 
               COUNT(*) as total_mensagens,
               COUNT(CASE WHEN numero_whatsapp IS NOT NULL AND numero_whatsapp != '' THEN 1 END) as com_numero,
               COUNT(CASE WHEN numero_whatsapp IS NULL OR numero_whatsapp = '' THEN 1 END) as sem_numero,
               COUNT(DISTINCT numero_whatsapp) as numeros_unicos
              FROM mensagens_comunicacao";

$result_stats = $mysqli->query($sql_stats);
$stats = $result_stats->fetch_assoc();

echo "📊 Estatísticas:\n";
echo "   Total mensagens: {$stats['total_mensagens']}\n";
echo "   Com número WhatsApp: {$stats['com_numero']}\n";
echo "   Sem número WhatsApp: {$stats['sem_numero']}\n";
echo "   Números únicos: {$stats['numeros_unicos']}\n";

// 4. Verificar conversas por número
echo "\n4️⃣ CONVERSAS POR NÚMERO\n";
echo "========================\n\n";

$sql_conversas = "SELECT 
                   numero_whatsapp,
                   COUNT(*) as total_mensagens,
                   COUNT(CASE WHEN direcao = 'recebido' THEN 1 END) as recebidas,
                   COUNT(CASE WHEN direcao = 'enviado' THEN 1 END) as enviadas,
                   MIN(data_hora) as primeira,
                   MAX(data_hora) as ultima
                  FROM mensagens_comunicacao 
                  WHERE numero_whatsapp IS NOT NULL 
                  AND numero_whatsapp != ''
                  GROUP BY numero_whatsapp 
                  ORDER BY total_mensagens DESC
                  LIMIT 10";

$result_conversas = $mysqli->query($sql_conversas);

if ($result_conversas && $result_conversas->num_rows > 0) {
    echo "Top 10 conversas por número:\n\n";
    while ($conversa = $result_conversas->fetch_assoc()) {
        echo "📱 {$conversa['numero_whatsapp']}\n";
        echo "   Total: {$conversa['total_mensagens']} (📥{$conversa['recebidas']} 📤{$conversa['enviadas']})\n";
        echo "   Período: {$conversa['primeira']} até {$conversa['ultima']}\n";
        echo "   " . str_repeat("-", 30) . "\n";
    }
} else {
    echo "Nenhuma conversa encontrada.\n";
}

echo "\n✅ Atualização concluída!\n";
?> 