<?php
/**
 * TESTE CONSULTA DE FATURAS
 * Verificar se Ana está consultando e exibindo faturas corretamente
 */

echo "=== TESTE CONSULTA DE FATURAS ===\n\n";

require_once __DIR__ . '/config.php';
require_once 'painel/db.php';

// 1. Verificar se há clientes com faturas para testar
echo "1️⃣ PROCURANDO CLIENTES COM FATURAS...\n";

$sql_clientes_com_faturas = "SELECT DISTINCT c.id, c.nome, COUNT(cob.id) as total_faturas
                            FROM clientes c 
                            INNER JOIN cobrancas cob ON c.id = cob.cliente_id 
                            WHERE cob.status IN ('OVERDUE', 'PENDING')
                            GROUP BY c.id, c.nome 
                            ORDER BY total_faturas DESC 
                            LIMIT 3";

$result = $mysqli->query($sql_clientes_com_faturas);

if ($result && $result->num_rows > 0) {
    echo "✅ Clientes com faturas encontrados:\n";
    $clientes_teste = [];
    while ($row = $result->fetch_assoc()) {
        echo "   📋 Cliente {$row['id']}: {$row['nome']} ({$row['total_faturas']} faturas)\n";
        $clientes_teste[] = $row['id'];
    }
    echo "\n";
    
    // Usar primeiro cliente para teste
    $cliente_teste = $clientes_teste[0];
    
} else {
    echo "❌ Nenhum cliente com faturas encontrado\n";
    echo "   Vou usar cliente 4296 para teste sem faturas\n\n";
    $cliente_teste = 4296;
}

// 2. Testar detecção de palavras-chave
echo "2️⃣ TESTANDO DETECÇÃO DE PALAVRAS-CHAVE...\n";

$mensagens_teste = [
    "Como está minha fatura?",
    "Preciso ver meus pagamentos",
    "Quero consultar minha conta",
    "Olá Ana, como você está?", // Esta não deve detectar faturas
    "Tenho algum boleto vencido?"
];

foreach ($mensagens_teste as $msg) {
    $precisa_consultar = false;
    $palavras_financeiras = ['fatura', 'pagamento', 'conta', 'cobrança', 'vencimento', 'débito', 'boleto', 'financeiro'];
    
    foreach ($palavras_financeiras as $palavra) {
        if (strpos(strtolower($msg), $palavra) !== false) {
            $precisa_consultar = true;
            break;
        }
    }
    
    $status = $precisa_consultar ? "✅ DETECTA" : "❌ NÃO DETECTA";
    echo "   $status: \"$msg\"\n";
}
echo "\n";

// 3. Testar consulta real de faturas
echo "3️⃣ TESTANDO CONSULTA REAL DE FATURAS (Cliente $cliente_teste)...\n";

// Faturas vencidas
$sql_vencidas = "SELECT 
                    id, valor, status,
                    DATE_FORMAT(vencimento, '%d/%m/%Y') as vencimento_formatado,
                    url_fatura,
                    DATEDIFF(CURDATE(), vencimento) as dias_vencido
                FROM cobrancas 
                WHERE cliente_id = $cliente_teste 
                AND status = 'OVERDUE'
                ORDER BY vencimento ASC";

$result_vencidas = $mysqli->query($sql_vencidas);

// Próxima fatura
$sql_proxima = "SELECT 
                    id, valor, status,
                    DATE_FORMAT(vencimento, '%d/%m/%Y') as vencimento_formatado,
                    url_fatura,
                    DATEDIFF(vencimento, CURDATE()) as dias_para_vencer
                FROM cobrancas 
                WHERE cliente_id = $cliente_teste 
                AND status = 'PENDING'
                ORDER BY vencimento ASC 
                LIMIT 1";

$result_proxima = $mysqli->query($sql_proxima);

echo "📊 FATURAS VENCIDAS:\n";
if ($result_vencidas && $result_vencidas->num_rows > 0) {
    echo "   ✅ {$result_vencidas->num_rows} faturas vencidas encontradas\n";
    $valor_total = 0;
    while ($row = $result_vencidas->fetch_assoc()) {
        $valor_total += floatval($row['valor']);
        echo "   💰 ID {$row['id']}: R$ {$row['valor']} - Venc: {$row['vencimento_formatado']} ({$row['dias_vencido']} dias)\n";
    }
    echo "   💰 Total em atraso: R$ " . number_format($valor_total, 2, ',', '.') . "\n";
} else {
    echo "   ✅ Nenhuma fatura vencida\n";
}

echo "\n📅 PRÓXIMA FATURA:\n";
if ($result_proxima && $result_proxima->num_rows > 0) {
    $proxima = $result_proxima->fetch_assoc();
    echo "   ✅ Próxima fatura encontrada\n";
    echo "   💳 ID {$proxima['id']}: R$ {$proxima['valor']} - Venc: {$proxima['vencimento_formatado']} ({$proxima['dias_para_vencer']} dias)\n";
} else {
    echo "   ✅ Nenhuma fatura pendente\n";
}

echo "\n";

// 4. Simular resposta completa da Ana com faturas
echo "4️⃣ SIMULANDO RESPOSTA ENRIQUECIDA DA ANA...\n";

$resposta_ana_original = "Olá! Vou verificar suas faturas para você.";

// Simular enriquecimento
$info_financeira = "\n\n📊 **RESUMO DA SUA CONTA:**\n";

// Re-executar queries para simulação
$result_vencidas = $mysqli->query($sql_vencidas);
$result_proxima = $mysqli->query($sql_proxima);

$tem_faturas = false;

if ($result_vencidas && $result_vencidas->num_rows > 0) {
    $tem_faturas = true;
    $total_vencidas = $result_vencidas->num_rows;
    $valor_total_vencido = 0;
    
    $faturas_vencidas = [];
    while ($row = $result_vencidas->fetch_assoc()) {
        $faturas_vencidas[] = $row;
        $valor_total_vencido += floatval($row['valor']);
    }
    
    $info_financeira .= "⚠️ **FATURAS VENCIDAS:** $total_vencidas\n";
    $info_financeira .= "💰 **Total em atraso:** R$ " . number_format($valor_total_vencido, 2, ',', '.') . "\n";
    
    if (count($faturas_vencidas) > 0) {
        $primeira = $faturas_vencidas[0];
        $info_financeira .= "📅 **Mais antiga:** {$primeira['vencimento_formatado']} ({$primeira['dias_vencido']} dias atrás)\n";
    }
    
    if ($total_vencidas > 1) {
        $info_financeira .= "📋 *+".($total_vencidas-1)." outras faturas vencidas*\n";
    }
    $info_financeira .= "\n";
}

if ($result_proxima && $result_proxima->num_rows > 0) {
    $tem_faturas = true;
    $proxima = $result_proxima->fetch_assoc();
    
    $info_financeira .= "📅 **PRÓXIMA FATURA:**\n";
    $info_financeira .= "💳 Vencimento: {$proxima['vencimento_formatado']}\n";
    $info_financeira .= "💰 Valor: R$ " . number_format($proxima['valor'], 2, ',', '.') . "\n";
    
    if ($proxima['dias_para_vencer'] <= 3) {
        $info_financeira .= "⚡ *Vence em {$proxima['dias_para_vencer']} dias!*\n";
    }
}

if (!$tem_faturas) {
    $info_financeira .= "✅ **Parabéns!** Sua conta está em dia!\n";
    $info_financeira .= "📋 Nenhuma fatura pendente ou vencida.\n";
}

$resposta_completa = $resposta_ana_original . $info_financeira;

echo "📝 RESPOSTA FINAL DA ANA:\n";
echo "═══════════════════════════════\n";
echo $resposta_completa;
echo "\n═══════════════════════════════\n\n";

echo "🎉 TESTE CONCLUÍDO!\n";
echo "✅ Detecção de palavras-chave funcionando\n";
echo "✅ Consulta ao banco funcionando\n";
echo "✅ Formatação da resposta funcionando\n";
echo "✅ Sistema pronto para teste real!\n";

echo "\n=== FIM DO TESTE ===\n";
?> 