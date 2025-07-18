<?php
/**
 * 🚀 Teste Completo de Produção
 * Verifica todos os componentes do sistema em produção
 */

echo "<h1>🚀 Teste Completo de Produção</h1>";
echo "<style>
    body{font-family:Arial,sans-serif;margin:20px;background:#f5f5f5;}
    .container{max-width:1000px;margin:0 auto;background:white;padding:20px;border-radius:10px;box-shadow:0 2px 10px rgba(0,0,0,0.1);}
    .success{color:#28a745;background:#d4edda;padding:10px;border-radius:5px;margin:10px 0;}
    .error{color:#dc3545;background:#f8d7da;padding:10px;border-radius:5px;margin:10px 0;}
    .info{color:#17a2b8;background:#d1ecf1;padding:10px;border-radius:5px;margin:10px 0;}
    .warning{color:#856404;background:#fff3cd;padding:10px;border-radius:5px;margin:10px 0;}
    .code{background:#f8f9fa;padding:10px;border-radius:5px;font-family:monospace;margin:10px 0;font-size:12px;}
    .test-section{background:#f8f9fa;padding:15px;margin:15px 0;border-radius:8px;border-left:4px solid #007bff;}
    .test-success{border-left-color:#28a745;}
    .test-error{border-left-color:#dc3545;}
    .progress{background:#e9ecef;height:20px;border-radius:10px;overflow:hidden;margin:10px 0;}
    .progress-bar{background:#007bff;height:100%;transition:width 0.3s;}
    .progress-success{background:#28a745;}
    .progress-error{background:#dc3545;}
</style>";

echo "<div class='container'>";

require_once 'config.php';
require_once 'db.php';

$inicio_teste = microtime(true);
$total_testes = 0;
$testes_sucesso = 0;
$testes_falha = 0;

function executarTeste($nome, $funcao) {
    global $total_testes, $testes_sucesso, $testes_falha;
    
    $total_testes++;
    echo "<div class='test-section'>";
    echo "<h3>🔍 $nome</h3>";
    
    try {
        $resultado = $funcao();
        if ($resultado['success']) {
            echo "<div class='success'>✅ " . $resultado['message'] . "</div>";
            $testes_sucesso++;
        } else {
            echo "<div class='error'>❌ " . $resultado['message'] . "</div>";
            $testes_falha++;
        }
        
        if (isset($resultado['details'])) {
            echo "<div class='code'>" . $resultado['details'] . "</div>";
        }
    } catch (Exception $e) {
        echo "<div class='error'>❌ Erro: " . $e->getMessage() . "</div>";
        $testes_falha++;
    }
    
    echo "</div>";
}

// 1. Teste de Configuração
executarTeste("Configuração do Sistema", function() {
    $configs = [
        'DB_HOST' => defined('DB_HOST') ? DB_HOST : 'NÃO DEFINIDO',
        'DB_NAME' => defined('DB_NAME') ? DB_NAME : 'NÃO DEFINIDO',
        'WHATSAPP_ROBOT_URL' => defined('WHATSAPP_ROBOT_URL') ? WHATSAPP_ROBOT_URL : 'NÃO DEFINIDO',
        'ASAAS_API_KEY' => defined('ASAAS_API_KEY') ? (strlen(ASAAS_API_KEY) > 20 ? 'CONFIGURADO' : 'INCOMPLETO') : 'NÃO DEFINIDO'
    ];
    
    $todas_configuradas = true;
    $details = "Configurações encontradas:\n";
    
    foreach ($configs as $key => $value) {
        $details .= "• $key: $value\n";
        if ($value === 'NÃO DEFINIDO' || $value === 'INCOMPLETO') {
            $todas_configuradas = false;
        }
    }
    
    return [
        'success' => $todas_configuradas,
        'message' => $todas_configuradas ? 'Todas as configurações estão definidas' : 'Algumas configurações estão faltando',
        'details' => $details
    ];
});

// 2. Teste de Conexão com Banco
executarTeste("Conexão com Banco de Dados", function() {
    global $mysqli;
    
    if (!$mysqli) {
        return ['success' => false, 'message' => 'Conexão com banco não estabelecida'];
    }
    
    $result = $mysqli->query("SELECT 1 as teste");
    if (!$result) {
        return ['success' => false, 'message' => 'Erro ao executar query de teste: ' . $mysqli->error];
    }
    
    $row = $result->fetch_assoc();
    if ($row['teste'] != 1) {
        return ['success' => false, 'message' => 'Query de teste retornou valor incorreto'];
    }
    
    return [
        'success' => true,
        'message' => 'Conexão com banco de dados funcionando',
        'details' => "Host: " . DB_HOST . "\nDatabase: " . DB_NAME
    ];
});

// 3. Teste de Tabelas Essenciais
executarTeste("Tabelas Essenciais", function() {
    global $mysqli;
    
    $tabelas_essenciais = [
        'clientes',
        'cobrancas',
        'configuracoes',
        'mensagens_comunicacao',
        'mensagens_agendadas',
        'canais_comunicacao'
    ];
    
    $tabelas_ok = [];
    $tabelas_faltando = [];
    
    foreach ($tabelas_essenciais as $tabela) {
        $result = $mysqli->query("SHOW TABLES LIKE '$tabela'");
        if ($result && $result->num_rows > 0) {
            $tabelas_ok[] = $tabela;
        } else {
            $tabelas_faltando[] = $tabela;
        }
    }
    
    $todas_existem = empty($tabelas_faltando);
    
    $details = "Tabelas encontradas: " . implode(', ', $tabelas_ok) . "\n";
    if (!empty($tabelas_faltando)) {
        $details .= "Tabelas faltando: " . implode(', ', $tabelas_faltando);
    }
    
    return [
        'success' => $todas_existem,
        'message' => $todas_existem ? 'Todas as tabelas essenciais existem' : 'Algumas tabelas estão faltando',
        'details' => $details
    ];
});

// 4. Teste de Configurações do Banco
executarTeste("Configurações do Banco", function() {
    global $mysqli;
    
    $configs_essenciais = [
        'asaas_api_key' => 'Chave da API Asaas',
        'whatsapp_vps_url' => 'URL do VPS WhatsApp',
        'sistema_status' => 'Status do Sistema'
    ];
    
    $configs_ok = [];
    $configs_faltando = [];
    
    foreach ($configs_essenciais as $chave => $descricao) {
        $result = $mysqli->query("SELECT valor FROM configuracoes WHERE chave = '$chave' LIMIT 1");
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $configs_ok[] = "$descricao: " . (strlen($row['valor']) > 10 ? 'CONFIGURADO' : 'INCOMPLETO');
        } else {
            $configs_faltando[] = $descricao;
        }
    }
    
    $todas_configuradas = empty($configs_faltando);
    
    $details = "Configurações encontradas:\n• " . implode("\n• ", $configs_ok) . "\n";
    if (!empty($configs_faltando)) {
        $details .= "Configurações faltando:\n• " . implode("\n• ", $configs_faltando);
    }
    
    return [
        'success' => $todas_configuradas,
        'message' => $todas_configuradas ? 'Todas as configurações estão no banco' : 'Algumas configurações estão faltando',
        'details' => $details
    ];
});

// 5. Teste de Conectividade WhatsApp VPS
executarTeste("Conectividade WhatsApp VPS", function() {
    $vps_url = WHATSAPP_ROBOT_URL;
    
    $ch = curl_init($vps_url . "/status");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        return ['success' => false, 'message' => 'Erro de conexão: ' . $error];
    }
    
    if ($http_code !== 200) {
        return ['success' => false, 'message' => "VPS retornou HTTP $http_code"];
    }
    
    $data = json_decode($response, true);
    $details = "URL: $vps_url\nHTTP Code: $http_code\n";
    if ($data) {
        $details .= "Resposta: " . json_encode($data, JSON_PRETTY_PRINT);
    }
    
    return [
        'success' => true,
        'message' => 'VPS WhatsApp acessível e respondendo',
        'details' => $details
    ];
});

// 6. Teste de Endpoint de Envio WhatsApp
executarTeste("Endpoint de Envio WhatsApp", function() {
    $vps_url = WHATSAPP_ROBOT_URL;
    $numero_teste = "5547996164699";
    $mensagem_teste = "🧪 Teste de produção - " . date('Y-m-d H:i:s');
    
    $payload = [
        'sessionName' => 'default',
        'number' => $numero_teste,
        'message' => $mensagem_teste
    ];
    
    $ch = curl_init($vps_url . "/send/text");
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        return ['success' => false, 'message' => 'Erro de conexão: ' . $error];
    }
    
    if ($http_code !== 200) {
        return ['success' => false, 'message' => "Endpoint retornou HTTP $http_code"];
    }
    
    $data = json_decode($response, true);
    $details = "Payload: " . json_encode($payload, JSON_PRETTY_PRINT) . "\n";
    $details .= "HTTP Code: $http_code\n";
    if ($data) {
        $details .= "Resposta: " . json_encode($data, JSON_PRETTY_PRINT);
    }
    
    $sucesso = $data && isset($data['success']) && $data['success'];
    
    return [
        'success' => $sucesso,
        'message' => $sucesso ? 'Mensagem enviada com sucesso' : 'Falha no envio da mensagem',
        'details' => $details
    ];
});

// 7. Teste de API Asaas
executarTeste("API Asaas", function() {
    global $mysqli;
    
    $config = $mysqli->query("SELECT valor FROM configuracoes WHERE chave = 'asaas_api_key' LIMIT 1")->fetch_assoc();
    
    if (!$config || !$config['valor']) {
        return ['success' => false, 'message' => 'Chave da API Asaas não configurada'];
    }
    
    $api_key = $config['valor'];
    
    $ch = curl_init("https://www.asaas.com/api/v3/customers");
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'access_token: ' . $api_key,
        'Content-Type: application/json'
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code === 401) {
        return ['success' => false, 'message' => 'Chave da API Asaas inválida'];
    }
    
    if ($http_code !== 200) {
        return ['success' => false, 'message' => "API Asaas retornou HTTP $http_code"];
    }
    
    return [
        'success' => true,
        'message' => 'API Asaas funcionando corretamente',
        'details' => "HTTP Code: $http_code\nChave configurada: " . substr($api_key, 0, 20) . "..."
    ];
});

// 8. Teste de Clientes no Sistema
executarTeste("Clientes no Sistema", function() {
    global $mysqli;
    
    $result = $mysqli->query("SELECT COUNT(*) as total FROM clientes");
    $total_clientes = $result->fetch_assoc()['total'];
    
    $result = $mysqli->query("SELECT COUNT(*) as total FROM clientes WHERE celular IS NOT NULL AND celular != ''");
    $clientes_com_celular = $result->fetch_assoc()['total'];
    
    $result = $mysqli->query("SELECT COUNT(*) as total FROM cobrancas");
    $total_cobrancas = $result->fetch_assoc()['total'];
    
    $details = "Total de clientes: $total_clientes\n";
    $details .= "Clientes com celular: $clientes_com_celular\n";
    $details .= "Total de cobranças: $total_cobrancas";
    
    return [
        'success' => $total_clientes > 0,
        'message' => $total_clientes > 0 ? "Sistema possui $total_clientes clientes" : 'Nenhum cliente cadastrado',
        'details' => $details
    ];
});

// 9. Teste de Mensagens Agendadas
executarTeste("Mensagens Agendadas", function() {
    global $mysqli;
    
    $result = $mysqli->query("SELECT COUNT(*) as total FROM mensagens_agendadas WHERE status = 'agendada'");
    $mensagens_agendadas = $result->fetch_assoc()['total'];
    
    $result = $mysqli->query("SELECT COUNT(*) as total FROM mensagens_agendadas WHERE status = 'enviada'");
    $mensagens_enviadas = $result->fetch_assoc()['total'];
    
    $details = "Mensagens agendadas: $mensagens_agendadas\n";
    $details .= "Mensagens enviadas: $mensagens_enviadas";
    
    return [
        'success' => true,
        'message' => "Sistema de mensagens agendadas funcionando",
        'details' => $details
    ];
});

// 10. Teste de Performance
executarTeste("Performance do Sistema", function() {
    global $inicio_teste;
    
    $tempo_execucao = round((microtime(true) - $inicio_teste) * 1000, 2);
    $memoria_uso = round(memory_get_usage() / 1024 / 1024, 2);
    $memoria_peak = round(memory_get_peak_usage() / 1024 / 1024, 2);
    
    $details = "Tempo de execução: {$tempo_execucao}ms\n";
    $details .= "Memória atual: {$memoria_uso}MB\n";
    $details .= "Memória pico: {$memoria_peak}MB";
    
    $performance_ok = $tempo_execucao < 5000 && $memoria_peak < 50;
    
    return [
        'success' => $performance_ok,
        'message' => $performance_ok ? 'Performance dentro do esperado' : 'Performance pode ser melhorada',
        'details' => $details
    ];
});

// Resumo Final
$fim_teste = microtime(true);
$tempo_total = round(($fim_teste - $inicio_teste) * 1000, 2);
$taxa_sucesso = $total_testes > 0 ? round(($testes_sucesso / $total_testes) * 100, 1) : 0;

echo "<h2>📊 Resumo Final do Teste</h2>";
echo "<div class='test-section'>";

echo "<div class='progress'>";
echo "<div class='progress-bar " . ($taxa_sucesso >= 80 ? 'progress-success' : ($taxa_sucesso >= 60 ? '' : 'progress-error')) . "' style='width: {$taxa_sucesso}%'></div>";
echo "</div>";

echo "<div class='info'>";
echo "<strong>📈 Resultados:</strong><br>";
echo "• Total de testes: $total_testes<br>";
echo "• Sucessos: $testes_sucesso<br>";
echo "• Falhas: $testes_falha<br>";
echo "• Taxa de sucesso: {$taxa_sucesso}%<br>";
echo "• Tempo total: {$tempo_total}ms";
echo "</div>";

if ($taxa_sucesso >= 90) {
    echo "<div class='success'>";
    echo "<strong>🎉 SISTEMA PRONTO PARA PRODUÇÃO!</strong><br>";
    echo "Todos os componentes principais estão funcionando corretamente.";
    echo "</div>";
} elseif ($taxa_sucesso >= 70) {
    echo "<div class='warning'>";
    echo "<strong>⚠️ SISTEMA PARCIALMENTE FUNCIONAL</strong><br>";
    echo "Alguns componentes precisam de atenção antes da produção.";
    echo "</div>";
} else {
    echo "<div class='error'>";
    echo "<strong>❌ SISTEMA COM PROBLEMAS</strong><br>";
    echo "Vários componentes precisam ser corrigidos antes da produção.";
    echo "</div>";
}

echo "</div>";

echo "<h2>🚀 Próximos Passos</h2>";
echo "<div class='test-section'>";

echo "<div class='info'>";
if ($taxa_sucesso >= 90) {
    echo "<strong>✅ Sistema pronto para uso em produção!</strong><br><br>";
    echo "1. <strong>Testar Chat:</strong> Enviar mensagem real via chat<br>";
    echo "2. <strong>Testar Agendamento:</strong> Criar mensagem agendada<br>";
    echo "3. <strong>Monitorar Logs:</strong> Acompanhar funcionamento<br>";
    echo "4. <strong>Configurar Cron:</strong> Ativar processamento automático";
} else {
    echo "<strong>🔧 Correções necessárias antes da produção:</strong><br><br>";
    echo "1. <strong>Verificar Falhas:</strong> Analisar testes que falharam<br>";
    echo "2. <strong>Corrigir Configurações:</strong> Ajustar parâmetros incorretos<br>";
    echo "3. <strong>Testar Novamente:</strong> Executar testes após correções<br>";
    echo "4. <strong>Validar Funcionamento:</strong> Confirmar que tudo está OK";
}
echo "</div>";

echo "</div>";

echo "</div>";
?> 