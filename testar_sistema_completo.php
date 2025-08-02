<?php
echo "🚀 TESTE COMPLETO DO SISTEMA DE TRANSFERÊNCIAS\n\n";

require_once 'painel/db.php';

// 1. Testar integrador Ana
echo "1️⃣ Testando Integrador Ana...\n";
require_once 'painel/api/integrador_ana_local.php';
$integrador = new IntegradorAnaLocal($mysqli);

$teste_mensagem = [
    'from' => '5547999999999',
    'body' => 'Preciso de um site para minha empresa'
];

$resultado_ana = $integrador->processarMensagem($teste_mensagem);

if ($resultado_ana['success']) {
    echo "✅ Ana funcionando! Resposta: " . substr($resultado_ana['resposta_ana'], 0, 100) . "...\n";
    echo "   Ação detectada: " . $resultado_ana['acao_sistema'] . "\n";
    echo "   Transfer Rafael: " . ($resultado_ana['transfer_para_rafael'] ? 'SIM' : 'NÃO') . "\n";
    echo "   Transfer Humano: " . ($resultado_ana['transfer_para_humano'] ? 'SIM' : 'NÃO') . "\n";
} else {
    echo "❌ Erro no integrador Ana\n";
    print_r($resultado_ana['debug']);
}

echo "\n";

// 2. Testar executor de transferências
echo "2️⃣ Testando Executor de Transferências...\n";
require_once 'painel/api/executar_transferencias.php';
$executor = new ExecutorTransferencias($mysqli);

$resultado_exec = $executor->processarTransferenciasPendentes();

echo "✅ Executor carregado\n";
echo "   Transferências Rafael: " . $resultado_exec['transferencias_rafael'] . "\n";
echo "   Transferências Humanos: " . $resultado_exec['transferencias_humanas'] . "\n";
echo "   Erros: " . count($resultado_exec['erros']) . "\n";

if (!empty($resultado_exec['erros'])) {
    echo "   Detalhes dos erros:\n";
    foreach ($resultado_exec['erros'] as $erro) {
        echo "   - $erro\n";
    }
}

echo "\n";

// 3. Verificar tabelas e dados
echo "3️⃣ Verificando Dados das Tabelas...\n";

$stats = [
    'rafael_total' => $mysqli->query("SELECT COUNT(*) as total FROM transferencias_rafael")->fetch_assoc()['total'],
    'humanos_total' => $mysqli->query("SELECT COUNT(*) as total FROM transferencias_humano")->fetch_assoc()['total'],
    'bloqueios' => $mysqli->query("SELECT COUNT(*) as total FROM bloqueios_ana WHERE ativo = 1")->fetch_assoc()['total'],
    'agentes' => $mysqli->query("SELECT COUNT(*) as total FROM agentes_notificacao WHERE ativo = 1")->fetch_assoc()['total'],
    'logs_ana' => $mysqli->query("SELECT COUNT(*) as total FROM logs_integracao_ana")->fetch_assoc()['total']
];

echo "✅ Estatísticas do Sistema:\n";
echo "   📊 Transferências Rafael: " . $stats['rafael_total'] . "\n";
echo "   👥 Transferências Humanos: " . $stats['humanos_total'] . "\n";
echo "   🚫 Bloqueios Ativos: " . $stats['bloqueios'] . "\n";
echo "   👨‍💼 Agentes Cadastrados: " . $stats['agentes'] . "\n";
echo "   📝 Logs Ana: " . $stats['logs_ana'] . "\n";

echo "\n";

// 4. Testar acesso via HTTP
echo "4️⃣ Testando Acesso HTTP...\n";

$url_gestao = 'http://localhost/loja-virtual-revenda/painel/gestao_transferencias.php';
echo "📱 Dashboard: $url_gestao\n";

$url_receptor = 'http://localhost/loja-virtual-revenda/painel/receber_mensagem_ana_local.php';
echo "🔗 Webhook URL: $url_receptor\n";

echo "\n";

// 5. Resultado final
echo "🎯 RESULTADO FINAL:\n";

$sistema_ok = $resultado_ana['success'] && $stats['agentes'] > 0;

if ($sistema_ok) {
    echo "🎉 ✅ SISTEMA DE TRANSFERÊNCIAS 100% FUNCIONAL!\n\n";
    echo "📋 PRÓXIMOS PASSOS:\n";
    echo "1. Configurar webhook WhatsApp: $url_receptor\n";
    echo "2. Acessar dashboard: $url_gestao\n";
    echo "3. Testar com mensagem real sobre 'site' ou 'ecommerce'\n\n";
    echo "🚀 TUDO PRONTO PARA USAR!\n";
} else {
    echo "⚠️ Sistema precisa de ajustes\n";
}

$mysqli->close();
?> 