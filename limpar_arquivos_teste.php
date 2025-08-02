<?php
/**
 * 🧹 LIMPEZA DE ARQUIVOS DE TESTE
 * Remove arquivos temporários criados durante desenvolvimento
 */

echo "🧹 LIMPANDO ARQUIVOS DE TESTE\n";
echo "============================\n\n";

$arquivos_teste = [
    // Testes principais
    'teste_completo_pre_producao.php',
    'teste_producao_robusto.php', 
    'teste_final_pre_producao.php',
    'teste_fluxo_completo_whatsapp.php',
    'teste_cron_e_bloqueios.php',
    
    // Scripts de configuração
    'configurar_webhook_vps.php',
    'configurar_webhook_seguro.php',
    'configurar_webhook_fallback.php',
    'ativar_webhook_raiz.php',
    'teste_webhook_simples.php',
    'teste_webhook_integrado.php',
    'teste_webhook_fisico_especifico.php',
    'configurar_vps_direto.php',
    'verificar_status_antes_configurar.php',
    'testar_webhook_local.php',
    'verificar_webhook_funcionando.php',
    
    // Diagnósticos
    'diagnosticar_webhook.php',
    'verificar_logs_webhook.php',
    
    // Instalação e correção
    'instalar_sistema_transferencias.php',
    'corrigir_campo_transferencia.php',
    'testar_sistema_completo.php',
    
    // Webhooks alternativos 
    'webhook_ana.php',
    'painel/webhook_teste.php',
    'painel/webhook_fallback.php',
    
    // Scripts SSH
    'configurar_webhook_ssh.sh',
    'script_ssh_configurar_webhook.sh',
    
    // Arquivos de comandos
    'comandos_ssh_corretos_final.txt',
    'comandos_ssh_manual.md',
    'comandos_ssh_rapido.txt',
    'comandos_ssh_manual.md',
    
    // Documentação temporária
    'SISTEMA_TRANSFERENCIAS_INTELIGENTE_FINAL.md',
    'CONFIGURAR_WEBHOOK_AGORA.md',
    
    // Script de limpeza (este arquivo)
    'limpar_arquivos_teste.php'
];

$removidos = 0;
$nao_encontrados = 0;

echo "📋 ARQUIVOS PARA REMOVER:\n";
echo "-------------------------\n";

foreach ($arquivos_teste as $arquivo) {
    if (file_exists($arquivo)) {
        if (unlink($arquivo)) {
            echo "✅ Removido: $arquivo\n";
            $removidos++;
        } else {
            echo "❌ Erro ao remover: $arquivo\n";
        }
    } else {
        echo "⚠️ Não encontrado: $arquivo\n";
        $nao_encontrados++;
    }
}

echo "\n";

// Limpar diretório de logs de teste
$log_test_files = glob('painel/logs/*teste*');
if (!empty($log_test_files)) {
    echo "📝 LIMPANDO LOGS DE TESTE:\n";
    echo "-------------------------\n";
    
    foreach ($log_test_files as $log_file) {
        if (unlink($log_file)) {
            echo "✅ Log removido: $log_file\n";
            $removidos++;
        }
    }
    echo "\n";
}

// Resumo
echo "📊 RESUMO DA LIMPEZA:\n";
echo "====================\n";
echo "• Arquivos removidos: $removidos\n";
echo "• Arquivos não encontrados: $nao_encontrados\n";
echo "• Total processado: " . count($arquivos_teste) . "\n\n";

if ($removidos > 0) {
    echo "✅ LIMPEZA CONCLUÍDA!\n";
    echo "🎯 Sistema pronto para produção - arquivos de teste removidos\n";
} else {
    echo "ℹ️ Nenhum arquivo de teste encontrado para remover\n";
}

echo "\n📁 ARQUIVOS MANTIDOS (SISTEMA PRINCIPAL):\n";
echo "=========================================\n";
echo "✅ config.php - Configuração principal\n";
echo "✅ index.php - Router\n";
echo "✅ webhook.php - Webhook backup\n";
echo "✅ painel/receber_mensagem_ana_local.php - Webhook principal\n";
echo "✅ painel/api/integrador_ana_local.php - Integrador Ana\n";
echo "✅ painel/api/executar_transferencias.php - Executor transferências\n";
echo "✅ painel/cron/processar_transferencias_automatico.php - Cron job\n";
echo "✅ painel/gestao_transferencias.php - Dashboard\n";
echo "✅ README.md - Documentação principal\n";
echo "✅ RELATORIO_TESTES_DESENVOLVIMENTO.md - Relatório final\n";

echo "\n🎊 SISTEMA LIMPO E PRONTO PARA PRODUÇÃO!\n";
?> 