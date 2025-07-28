<?php
/**
 * VERIFICAR MENSAGEM "OIE" DE 16:06
 * 
 * Script para verificar se a mensagem específica foi recebida
 */

require_once 'config.php';
require_once 'painel/db.php';

echo "🔍 VERIFICANDO MENSAGEM 'OIE' DE 16:06\n";
echo "=======================================\n\n";

// Verificar mensagens recebidas hoje
$sql = "SELECT mc.*, c.nome as cliente_nome, c.celular, cc.nome_exibicao as canal_nome
        FROM mensagens_comunicacao mc
        LEFT JOIN clientes c ON mc.cliente_id = c.id
        LEFT JOIN canais_comunicacao cc ON mc.canal_id = cc.id
        WHERE mc.data_hora >= '2025-07-28 16:00:00'
        AND mc.data_hora <= '2025-07-28 16:10:00'
        ORDER BY mc.data_hora DESC";

$result = $mysqli->query($sql);

if ($result && $result->num_rows > 0) {
    echo "📥 Mensagens recebidas entre 16:00 e 16:10:\n\n";
    while ($msg = $result->fetch_assoc()) {
        $hora = date('H:i:s', strtotime($msg['data_hora']));
        $cliente = $msg['cliente_nome'] ?: 'Sem cliente';
        $canal = $msg['canal_nome'] ?: 'Canal ' . $msg['canal_id'];
        $direcao = $msg['direcao'] === 'recebido' ? '📥' : '📤';
        
        echo "   $direcao [$hora] $cliente ($canal)\n";
        echo "      Mensagem: " . substr($msg['mensagem'], 0, 100) . "...\n";
        echo "      Número WhatsApp: " . ($msg['numero_whatsapp'] ?: 'N/A') . "\n";
        echo "      " . str_repeat("-", 40) . "\n";
    }
} else {
    echo "❌ Nenhuma mensagem encontrada entre 16:00 e 16:10\n";
}

echo "\n🔍 VERIFICANDO MENSAGENS COM 'OIE':\n";
echo "=====================================\n\n";

// Buscar especificamente por "oie"
$sql_oie = "SELECT mc.*, c.nome as cliente_nome, c.celular
            FROM mensagens_comunicacao mc
            LEFT JOIN clientes c ON mc.cliente_id = c.id
            WHERE LOWER(mc.mensagem) LIKE '%oie%'
            AND mc.data_hora >= '2025-07-28 00:00:00'
            ORDER BY mc.data_hora DESC";

$result_oie = $mysqli->query($sql_oie);

if ($result_oie && $result_oie->num_rows > 0) {
    echo "📥 Mensagens com 'oie' encontradas:\n\n";
    while ($msg = $result_oie->fetch_assoc()) {
        $hora = date('H:i:s', strtotime($msg['data_hora']));
        $cliente = $msg['cliente_nome'] ?: 'Sem cliente';
        $direcao = $msg['direcao'] === 'recebido' ? '📥' : '📤';
        
        echo "   $direcao [$hora] $cliente\n";
        echo "      Mensagem: {$msg['mensagem']}\n";
        echo "      Número WhatsApp: " . ($msg['numero_whatsapp'] ?: 'N/A') . "\n";
        echo "      " . str_repeat("-", 30) . "\n";
    }
} else {
    echo "❌ Nenhuma mensagem com 'oie' encontrada hoje\n";
}

echo "\n🔍 VERIFICANDO ÚLTIMAS MENSAGENS DO CHARLES:\n";
echo "=============================================\n\n";

// Buscar últimas mensagens do Charles (554796164699)
$sql_charles = "SELECT mc.*, c.nome as cliente_nome, c.celular
                FROM mensagens_comunicacao mc
                LEFT JOIN clientes c ON mc.cliente_id = c.id
                WHERE mc.numero_whatsapp = '554796164699'
                OR mc.numero_whatsapp = '4796164699'
                OR c.celular = '554796164699'
                OR c.celular = '4796164699'
                ORDER BY mc.data_hora DESC
                LIMIT 10";

$result_charles = $mysqli->query($sql_charles);

if ($result_charles && $result_charles->num_rows > 0) {
    echo "📥 Últimas mensagens do Charles:\n\n";
    while ($msg = $result_charles->fetch_assoc()) {
        $hora = date('H:i:s', strtotime($msg['data_hora']));
        $cliente = $msg['cliente_nome'] ?: 'Sem cliente';
        $direcao = $msg['direcao'] === 'recebido' ? '📥' : '📤';
        
        echo "   $direcao [$hora] $cliente\n";
        echo "      Mensagem: " . substr($msg['mensagem'], 0, 100) . "...\n";
        echo "      Número WhatsApp: " . ($msg['numero_whatsapp'] ?: 'N/A') . "\n";
        echo "      " . str_repeat("-", 30) . "\n";
    }
} else {
    echo "❌ Nenhuma mensagem do Charles encontrada\n";
}

echo "\n🔍 VERIFICANDO CONVERSAS DUPLICADAS:\n";
echo "=====================================\n\n";

// Verificar se ainda há conversas duplicadas
$sql_duplicadas = "SELECT 
                    numero_whatsapp,
                    COUNT(DISTINCT cliente_id) as total_clientes,
                    GROUP_CONCAT(DISTINCT cliente_id ORDER BY cliente_id) as clientes_ids,
                    COUNT(*) as total_mensagens
                   FROM mensagens_comunicacao 
                   WHERE numero_whatsapp IS NOT NULL 
                   AND numero_whatsapp != ''
                   GROUP BY numero_whatsapp 
                   HAVING COUNT(DISTINCT cliente_id) > 1
                   ORDER BY total_mensagens DESC";

$result_duplicadas = $mysqli->query($sql_duplicadas);

if ($result_duplicadas && $result_duplicadas->num_rows > 0) {
    echo "❌ CONVERSAS DUPLICADAS ENCONTRADAS:\n\n";
    while ($duplicada = $result_duplicadas->fetch_assoc()) {
        echo "📱 Número: {$duplicada['numero_whatsapp']}\n";
        echo "   Clientes: {$duplicada['clientes_ids']}\n";
        echo "   Total mensagens: {$duplicada['total_mensagens']}\n";
        echo "   " . str_repeat("-", 30) . "\n";
    }
} else {
    echo "✅ Nenhuma conversa duplicada encontrada\n";
}

echo "\n✅ Verificação concluída!\n";
?> 