<?php
require_once 'config.php';
require_once 'painel/db.php';

echo "📊 VERIFICANDO MENSAGENS DE TESTE\n";
echo "================================\n\n";

$sql = "SELECT mc.*, c.nome as cliente_nome, cc.nome_exibicao as canal_nome
        FROM mensagens_comunicacao mc
        LEFT JOIN clientes c ON mc.cliente_id = c.id
        LEFT JOIN canais_comunicacao cc ON mc.canal_id = cc.id
        WHERE mc.data_hora >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
        ORDER BY mc.data_hora DESC
        LIMIT 10";

$result = $mysqli->query($sql);

if ($result && $result->num_rows > 0) {
    echo "Mensagens salvas nas últimas 24 horas:\n\n";
    while ($msg = $result->fetch_assoc()) {
        $cliente = $msg['cliente_nome'] ?: 'Cliente não identificado';
        $canal = $msg['canal_nome'] ?: 'Canal ' . $msg['canal_id'];
        $hora = date('H:i:s', strtotime($msg['data_hora']));
        $direcao = $msg['direcao'] === 'recebido' ? '📥' : '📤';
        
        echo "   $direcao [$hora] $cliente ($canal)\n";
        echo "      ID: {$msg['id']} | Status: {$msg['status']}\n";
        echo "      Mensagem: " . substr($msg['mensagem'], 0, 80) . "...\n\n";
    }
} else {
    echo "Nenhuma mensagem encontrada nas últimas 24 horas.\n";
}

echo "✅ Verificação concluída!\n";
?> 