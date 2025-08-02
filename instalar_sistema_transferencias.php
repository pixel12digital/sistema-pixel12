<?php
/**
 * 🚀 INSTALADOR SISTEMA DE TRANSFERÊNCIAS - PIXEL12DIGITAL
 * 
 * Script que instala e configura completamente o sistema de transferências
 */

require_once 'config.php';
require_once 'painel/db.php';

header('Content-Type: text/html; charset=utf-8');

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🚀 Instalador Sistema de Transferências</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; margin: 0; padding: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #333; text-align: center; margin-bottom: 30px; }
        .step { margin: 20px 0; padding: 15px; border-left: 4px solid #007bff; background: #f8f9fa; }
        .success { border-left-color: #28a745; background: #d4edda; }
        .error { border-left-color: #dc3545; background: #f8d7da; }
        .warning { border-left-color: #ffc107; background: #fff3cd; }
        .btn { display: inline-block; padding: 12px 24px; background: #007bff; color: white; text-decoration: none; border-radius: 5px; margin: 10px 5px; }
        .btn:hover { background: #0056b3; }
        pre { background: #f8f9fa; padding: 15px; border-radius: 5px; overflow-x: auto; }
        .progress { width: 100%; height: 20px; background: #e9ecef; border-radius: 10px; overflow: hidden; margin: 15px 0; }
        .progress-bar { height: 100%; background: linear-gradient(90deg, #007bff, #28a745); transition: width 0.3s; }
    </style>
</head>
<body>

<div class="container">
    <h1>🚀 Sistema de Transferências Pixel12Digital</h1>
    
    <?php
    
    $etapas_concluidas = 0;
    $total_etapas = 8;
    
    function atualizar_progresso() {
        global $etapas_concluidas, $total_etapas;
        $percentual = ($etapas_concluidas / $total_etapas) * 100;
        echo "<div class='progress'><div class='progress-bar' style='width: {$percentual}%'></div></div>";
        echo "<p><strong>Progresso:</strong> {$etapas_concluidas}/{$total_etapas} etapas concluídas ({$percentual}%)</p>";
    }
    
    // ETAPA 1: Verificar conexão com banco
    echo "<div class='step'>";
    echo "<h3>🔌 Etapa 1: Verificando Conexão com Banco</h3>";
    
    if ($mysqli->connect_error) {
        echo "<div class='error'>❌ Erro de conexão: " . $mysqli->connect_error . "</div>";
        exit;
    } else {
        echo "<div class='success'>✅ Conexão com banco estabelecida com sucesso!</div>";
        $etapas_concluidas++;
    }
    echo "</div>";
    atualizar_progresso();
    
    // ETAPA 2: Executar SQL das tabelas
    echo "<div class='step'>";
    echo "<h3>🗃️ Etapa 2: Criando/Atualizando Tabelas</h3>";
    
    $sql_script = file_get_contents('painel/sql/criar_tabelas_transferencias.sql');
    $sqls = explode(';', $sql_script);
    $tabelas_criadas = 0;
    $erros_sql = [];
    
    foreach ($sqls as $sql) {
        $sql = trim($sql);
        if (empty($sql) || strpos($sql, '--') === 0 || strpos($sql, 'DELIMITER') !== false) continue;
        
        if ($mysqli->query($sql)) {
            $tabelas_criadas++;
        } else {
            $erros_sql[] = "SQL: " . substr($sql, 0, 50) . "... | Erro: " . $mysqli->error;
        }
    }
    
    if (empty($erros_sql)) {
        echo "<div class='success'>✅ Tabelas criadas/atualizadas com sucesso! ($tabelas_criadas comandos executados)</div>";
        $etapas_concluidas++;
    } else {
        echo "<div class='error'>❌ Erros na criação de tabelas:</div>";
        foreach ($erros_sql as $erro) {
            echo "<div class='error'>• $erro</div>";
        }
    }
    echo "</div>";
    atualizar_progresso();
    
    // ETAPA 3: Verificar tabelas críticas
    echo "<div class='step'>";
    echo "<h3>🔍 Etapa 3: Verificando Estrutura das Tabelas</h3>";
    
    $tabelas_necessarias = [
        'transferencias_rafael',
        'transferencias_humano', 
        'bloqueios_ana',
        'agentes_notificacao',
        'logs_integracao_ana'
    ];
    
    $tabelas_ok = 0;
    foreach ($tabelas_necessarias as $tabela) {
        $result = $mysqli->query("SHOW TABLES LIKE '$tabela'");
        if ($result && $result->num_rows > 0) {
            echo "<div class='success'>✅ Tabela '$tabela' existe</div>";
            $tabelas_ok++;
        } else {
            echo "<div class='error'>❌ Tabela '$tabela' não encontrada</div>";
        }
    }
    
    if ($tabelas_ok === count($tabelas_necessarias)) {
        $etapas_concluidas++;
    }
    echo "</div>";
    atualizar_progresso();
    
    // ETAPA 4: Verificar arquivos do sistema
    echo "<div class='step'>";
    echo "<h3>📁 Etapa 4: Verificando Arquivos do Sistema</h3>";
    
    $arquivos_necessarios = [
        'painel/api/executar_transferencias.php',
        'painel/api/integrador_ana_local.php',
        'painel/receber_mensagem_ana_local.php',
        'painel/gestao_transferencias.php',
        'painel/cron/processar_transferencias_automatico.php'
    ];
    
    $arquivos_ok = 0;
    foreach ($arquivos_necessarios as $arquivo) {
        if (file_exists($arquivo)) {
            echo "<div class='success'>✅ Arquivo '$arquivo' existe</div>";
            $arquivos_ok++;
        } else {
            echo "<div class='error'>❌ Arquivo '$arquivo' não encontrado</div>";
        }
    }
    
    if ($arquivos_ok === count($arquivos_necessarios)) {
        $etapas_concluidas++;
    }
    echo "</div>";
    atualizar_progresso();
    
    // ETAPA 5: Testar integrador Ana
    echo "<div class='step'>";
    echo "<h3>🤖 Etapa 5: Testando Integrador Ana</h3>";
    
    try {
        require_once 'painel/api/integrador_ana_local.php';
        $integrador = new IntegradorAnaLocal($mysqli);
        
        // Teste básico
        $teste_dados = [
            'from' => '5547999999999',
            'body' => 'Olá, preciso de um site para minha empresa'
        ];
        
        $resultado = $integrador->processarMensagem($teste_dados);
        
        if ($resultado['success']) {
            echo "<div class='success'>✅ Integrador Ana funcionando! Resposta: " . substr($resultado['resposta_ana'], 0, 100) . "...</div>";
            if ($resultado['transfer_para_rafael']) {
                echo "<div class='success'>✅ Detecção de transferência para Rafael funcionando!</div>";
            }
            $etapas_concluidas++;
        } else {
            echo "<div class='error'>❌ Erro no integrador: " . implode(', ', $resultado['debug']) . "</div>";
        }
        
    } catch (Exception $e) {
        echo "<div class='error'>❌ Erro ao testar integrador: " . $e->getMessage() . "</div>";
    }
    echo "</div>";
    atualizar_progresso();
    
    // ETAPA 6: Testar executor de transferências
    echo "<div class='step'>";
    echo "<h3>⚡ Etapa 6: Testando Executor de Transferências</h3>";
    
    try {
        require_once 'painel/api/executar_transferencias.php';
        $executor = new ExecutorTransferencias($mysqli);
        
        // Verificar se há transferências para processar
        $pendentes_rafael = $mysqli->query("SELECT COUNT(*) as total FROM transferencias_rafael WHERE status = 'pendente'")->fetch_assoc()['total'];
        $pendentes_humanos = $mysqli->query("SELECT COUNT(*) as total FROM transferencias_humano WHERE status = 'pendente'")->fetch_assoc()['total'];
        
        echo "<div class='success'>✅ Executor carregado com sucesso!</div>";
        echo "<div class='success'>📊 Transferências pendentes: $pendentes_rafael para Rafael, $pendentes_humanos para humanos</div>";
        
        if ($pendentes_rafael > 0 || $pendentes_humanos > 0) {
            echo "<div class='warning'>⚠️ Há transferências pendentes. Execute manualmente se necessário.</div>";
        }
        
        $etapas_concluidas++;
        
    } catch (Exception $e) {
        echo "<div class='error'>❌ Erro ao testar executor: " . $e->getMessage() . "</div>";
    }
    echo "</div>";
    atualizar_progresso();
    
    // ETAPA 7: Verificar configuração WhatsApp
    echo "<div class='step'>";
    echo "<h3>📱 Etapa 7: Verificando Configuração WhatsApp</h3>";
    
    if (defined('WHATSAPP_ROBOT_URL')) {
        echo "<div class='success'>✅ URL WhatsApp configurada: " . WHATSAPP_ROBOT_URL . "</div>";
        
        // Testar conectividade básica
        $test_url = str_replace('/send/text', '/sessions', WHATSAPP_ROBOT_URL);
        $ch = curl_init($test_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($http_code === 200) {
            echo "<div class='success'>✅ API WhatsApp respondendo (Canal 3000)</div>";
        } else {
            echo "<div class='warning'>⚠️ API WhatsApp não respondeu (HTTP: $http_code)</div>";
        }
        
        // Testar Canal 3001
        $test_url_3001 = str_replace(':3000', ':3001', $test_url);
        $ch = curl_init($test_url_3001);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($http_code === 200) {
            echo "<div class='success'>✅ API WhatsApp respondendo (Canal 3001)</div>";
        } else {
            echo "<div class='warning'>⚠️ API WhatsApp Canal 3001 não respondeu (HTTP: $http_code)</div>";
        }
        
        $etapas_concluidas++;
        
    } else {
        echo "<div class='error'>❌ WHATSAPP_ROBOT_URL não configurada no config.php</div>";
    }
    echo "</div>";
    atualizar_progresso();
    
    // ETAPA 8: Relatório final
    echo "<div class='step'>";
    echo "<h3>🎯 Etapa 8: Relatório Final</h3>";
    
    if ($etapas_concluidas >= 6) {
        echo "<div class='success'>🎉 <strong>SISTEMA DE TRANSFERÊNCIAS INSTALADO COM SUCESSO!</strong></div>";
        echo "<div class='success'>✅ O sistema está pronto para usar</div>";
        
        echo "<h4>🚀 Próximos Passos:</h4>";
        echo "<ul>";
        echo "<li>✅ Configurar webhook do WhatsApp Canal 3000 para: <code>" . (isset($_SERVER['HTTP_HOST']) ? "https://{$_SERVER['HTTP_HOST']}" : "https://seu-dominio.com") . "/painel/receber_mensagem_ana_local.php</code></li>";
        echo "<li>✅ Acessar gestão de transferências: <a href='painel/gestao_transferencias.php' class='btn'>🎛️ Painel de Gestão</a></li>";
        echo "<li>✅ Configurar cron para automação: <code>* * * * * /usr/bin/php " . realpath('painel/cron/processar_transferencias_automatico.php') . "</code></li>";
        echo "<li>✅ Testar com mensagem real mencionando 'site' ou 'ecommerce'</li>";
        echo "</ul>";
        
        $etapas_concluidas++;
        
    } else {
        echo "<div class='error'>❌ <strong>INSTALAÇÃO INCOMPLETA</strong></div>";
        echo "<div class='error'>⚠️ Algumas etapas falharam. Verifique os erros acima.</div>";
    }
    echo "</div>";
    atualizar_progresso();
    
    // Estatísticas finais
    echo "<div class='step'>";
    echo "<h3>📊 Estatísticas do Sistema</h3>";
    
    $stats = [
        'rafael_total' => $mysqli->query("SELECT COUNT(*) as total FROM transferencias_rafael")->fetch_assoc()['total'],
        'humanos_total' => $mysqli->query("SELECT COUNT(*) as total FROM transferencias_humano")->fetch_assoc()['total'],
        'bloqueios_ativos' => $mysqli->query("SELECT COUNT(*) as total FROM bloqueios_ana WHERE ativo = 1")->fetch_assoc()['total'],
        'agentes_cadastrados' => $mysqli->query("SELECT COUNT(*) as total FROM agentes_notificacao WHERE ativo = 1")->fetch_assoc()['total']
    ];
    
    echo "<div class='success'>";
    echo "<p><strong>📈 Transferências Rafael:</strong> {$stats['rafael_total']}</p>";
    echo "<p><strong>👥 Transferências Humanos:</strong> {$stats['humanos_total']}</p>";
    echo "<p><strong>🚫 Clientes Bloqueados:</strong> {$stats['bloqueios_ativos']}</p>";
    echo "<p><strong>👨‍💼 Agentes Cadastrados:</strong> {$stats['agentes_cadastrados']}</p>";
    echo "</div>";
    echo "</div>";
    
    ?>
    
    <div style="text-align: center; margin-top: 30px;">
        <a href="painel/gestao_transferencias.php" class="btn">🎛️ Acessar Gestão de Transferências</a>
        <a href="painel/" class="btn">🏠 Voltar ao Painel</a>
    </div>
    
    <div style="margin-top: 30px; padding: 20px; background: #f8f9fa; border-radius: 5px;">
        <h4>ℹ️ Informações Importantes:</h4>
        <ul>
            <li><strong>Webhook URL:</strong> Configure no WhatsApp para <code><?= isset($_SERVER['HTTP_HOST']) ? "https://{$_SERVER['HTTP_HOST']}" : "https://seu-dominio.com" ?>/painel/receber_mensagem_ana_local.php</code></li>
            <li><strong>Cron Job:</strong> Execute <code>* * * * * /usr/bin/php <?= realpath('painel/cron/processar_transferencias_automatico.php') ?></code></li>
            <li><strong>Rafael WhatsApp:</strong> 5547973095525 (configurado para receber notificações)</li>
            <li><strong>Canal 3000:</strong> Ana IA (Pixel12Digital)</li>
            <li><strong>Canal 3001:</strong> Atendimento Humano (Comercial)</li>
        </ul>
    </div>
</div>

</body>
</html>

<?php
$mysqli->close();
?> 