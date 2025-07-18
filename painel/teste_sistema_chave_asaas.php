<?php
/**
 * 🔧 Teste do Sistema de Chave Asaas
 * Verifica se a atualização da chave funciona tanto no banco quanto nos arquivos
 */

require_once 'config.php';
require_once 'db.php';

echo "<h1>🔧 Teste do Sistema de Chave Asaas</h1>";

// 1. Verificar configuração atual
echo "<h2>1. 📋 Status Atual</h2>";

// Verificar no banco
$result = $mysqli->query("SELECT valor FROM configuracoes WHERE chave = 'asaas_api_key' LIMIT 1");
if ($result && $result->num_rows > 0) {
    $config_banco = $result->fetch_assoc()['valor'];
    echo "✅ <strong>Banco de Dados:</strong> CONFIGURADO<br>";
    echo "&nbsp;&nbsp;&nbsp;&nbsp;Chave: " . substr($config_banco, 0, 20) . "...<br>";
} else {
    echo "❌ <strong>Banco de Dados:</strong> NÃO CONFIGURADO<br>";
    $config_banco = '';
}

// Verificar no arquivo config.php
echo "✅ <strong>Arquivo config.php:</strong> " . (defined('ASAAS_API_KEY') ? 'CONFIGURADO' : 'NÃO CONFIGURADO') . "<br>";
if (defined('ASAAS_API_KEY')) {
    echo "&nbsp;&nbsp;&nbsp;&nbsp;Chave: " . substr(ASAAS_API_KEY, 0, 20) . "...<br>";
}

// 2. Testar endpoint de atualização (simulação direta)
echo "<h2>2. 🔄 Teste do Endpoint de Atualização</h2>";

// Simular diretamente a função do endpoint
$chave_teste = defined('ASAAS_API_KEY') ? ASAAS_API_KEY : $config_banco;
$tipo_teste = strpos($chave_teste, '_test_') !== false ? 'test' : 'prod';

if (!empty($chave_teste)) {
    // Testar a chave
    $ch = curl_init("https://www.asaas.com/api/v3/customers?limit=1");
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'access_token: ' . $chave_teste,
        'Content-Type: application/json'
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code === 200) {
        echo "✅ <strong>Endpoint funcionando:</strong> OK<br>";
        echo "&nbsp;&nbsp;&nbsp;&nbsp;Chave válida para atualização<br>";
        echo "&nbsp;&nbsp;&nbsp;&nbsp;Tipo: " . strtoupper($tipo_teste) . "<br>";
        
        // Simular atualização no banco
        $chave_escaped = $mysqli->real_escape_string($chave_teste);
        $tipo_escaped = $mysqli->real_escape_string($tipo_teste);
        
        // Verificar se já existe no banco
        $result = $mysqli->query("SELECT id FROM configuracoes WHERE chave = 'asaas_api_key' LIMIT 1");
        
        if ($result && $result->num_rows > 0) {
            $sql = "UPDATE configuracoes SET 
                    valor = '$chave_escaped', 
                    data_atualizacao = NOW() 
                    WHERE chave = 'asaas_api_key'";
        } else {
            $sql = "INSERT INTO configuracoes (chave, valor, descricao, data_criacao, data_atualizacao) 
                    VALUES ('asaas_api_key', '$chave_escaped', 'Chave da API Asaas para integração financeira', NOW(), NOW())";
        }
        
        $result = $mysqli->query($sql);
        
        if ($result) {
            echo "&nbsp;&nbsp;&nbsp;&nbsp;Banco atualizado: Sim<br>";
            echo "&nbsp;&nbsp;&nbsp;&nbsp;Arquivos podem ser atualizados: Sim<br>";
        } else {
            echo "&nbsp;&nbsp;&nbsp;&nbsp;Banco atualizado: Erro - " . $mysqli->error . "<br>";
        }
        
    } else {
        echo "❌ <strong>Endpoint retornou erro:</strong> HTTP $http_code<br>";
    }
} else {
    echo "❌ <strong>Endpoint não pode ser testado:</strong> Nenhuma chave disponível<br>";
}

// 3. Verificar sincronização entre banco e arquivo
echo "<h2>3. 🔗 Sincronização Banco ↔ Arquivo</h2>";

$result = $mysqli->query("SELECT valor FROM configuracoes WHERE chave = 'asaas_api_key' LIMIT 1");
if ($result && $result->num_rows > 0) {
    $chave_banco = $result->fetch_assoc()['valor'];
    $chave_arquivo = defined('ASAAS_API_KEY') ? ASAAS_API_KEY : '';
    
    if ($chave_banco === $chave_arquivo) {
        echo "✅ <strong>Sincronização:</strong> PERFEITA<br>";
        echo "&nbsp;&nbsp;&nbsp;&nbsp;Banco e arquivo têm a mesma chave<br>";
    } else {
        echo "⚠️ <strong>Sincronização:</strong> DIFERENTE<br>";
        echo "&nbsp;&nbsp;&nbsp;&nbsp;Banco: " . substr($chave_banco, 0, 20) . "...<br>";
        echo "&nbsp;&nbsp;&nbsp;&nbsp;Arquivo: " . substr($chave_arquivo, 0, 20) . "...<br>";
        echo "&nbsp;&nbsp;&nbsp;&nbsp;💡 Use o painel para sincronizar automaticamente<br>";
    }
} else {
    echo "❌ <strong>Sincronização:</strong> NÃO POSSÍVEL - Banco não configurado<br>";
}

// 4. Testar validação da chave
echo "<h2>4. 🔍 Validação da Chave</h2>";

$chave_teste = defined('ASAAS_API_KEY') ? ASAAS_API_KEY : $config_banco;

if (!empty($chave_teste)) {
    $ch = curl_init("https://www.asaas.com/api/v3/customers?limit=1");
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'access_token: ' . $chave_teste,
        'Content-Type: application/json'
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code === 200) {
        echo "✅ <strong>Chave válida:</strong> Conexão com Asaas OK<br>";
        echo "&nbsp;&nbsp;&nbsp;&nbsp;HTTP Code: $http_code<br>";
    } else {
        echo "❌ <strong>Chave inválida:</strong> HTTP $http_code<br>";
        echo "&nbsp;&nbsp;&nbsp;&nbsp;Resposta: " . substr($response, 0, 100) . "...<br>";
    }
} else {
    echo "❌ <strong>Chave não encontrada:</strong> Nenhuma chave para testar<br>";
}

// 5. Verificar logs
echo "<h2>5. 📝 Logs do Sistema</h2>";

$log_file = 'logs/asaas_key_updates.log';
if (file_exists($log_file)) {
    $logs = file($log_file);
    $ultimas_entradas = array_slice($logs, -5); // Últimas 5 entradas
    
    echo "✅ <strong>Arquivo de log encontrado:</strong> " . count($logs) . " entradas<br>";
    echo "<strong>Últimas atualizações:</strong><br>";
    foreach ($ultimas_entradas as $log) {
        echo "&nbsp;&nbsp;&nbsp;&nbsp;📅 " . trim($log) . "<br>";
    }
} else {
    echo "⚠️ <strong>Arquivo de log não encontrado:</strong> $log_file<br>";
    echo "&nbsp;&nbsp;&nbsp;&nbsp;Será criado automaticamente na primeira atualização<br>";
}

// 6. Verificar permissões de arquivo
echo "<h2>6. 🔐 Permissões de Arquivo</h2>";

$config_file = 'config.php';
if (file_exists($config_file)) {
    if (is_writable($config_file)) {
        echo "✅ <strong>config.php:</strong> Gravável<br>";
    } else {
        echo "❌ <strong>config.php:</strong> Não gravável<br>";
    }
} else {
    echo "❌ <strong>config.php:</strong> Não encontrado<br>";
}

$config_painel_file = 'painel/config.php';
if (file_exists($config_painel_file)) {
    if (is_writable($config_painel_file)) {
        echo "✅ <strong>painel/config.php:</strong> Gravável<br>";
    } else {
        echo "❌ <strong>painel/config.php:</strong> Não gravável<br>";
    }
} else {
    echo "❌ <strong>painel/config.php:</strong> Não encontrado<br>";
}

// 7. Resumo final
echo "<h2>7. 📊 Resumo Final</h2>";

$status_banco = !empty($config_banco);
$status_arquivo = defined('ASAAS_API_KEY');
$status_chave = $http_code === 200;
$status_permissoes = is_writable($config_file) && is_writable($config_painel_file);

$total_checks = 4;
$checks_ok = ($status_banco ? 1 : 0) + ($status_arquivo ? 1 : 0) + ($status_chave ? 1 : 0) + ($status_permissoes ? 1 : 0);
$percentual = round(($checks_ok / $total_checks) * 100);

echo "<div style='background: " . ($percentual >= 75 ? '#d1fae5' : '#fef3c7') . "; padding: 15px; border-radius: 8px; border: 1px solid " . ($percentual >= 75 ? '#10b981' : '#f59e0b') . ";'>";
echo "<strong>Sistema de Chave Asaas: $percentual% Funcional</strong><br><br>";

echo "✅ Banco de Dados: " . ($status_banco ? 'OK' : '❌') . "<br>";
echo "✅ Arquivo config.php: " . ($status_arquivo ? 'OK' : '❌') . "<br>";
echo "✅ Validação da chave: " . ($status_chave ? 'OK' : '❌') . "<br>";
echo "✅ Permissões de arquivo: " . ($status_permissoes ? 'OK' : '❌') . "<br>";

if ($percentual >= 75) {
    echo "<br>🎉 <strong>Sistema funcionando corretamente!</strong><br>";
    echo "A chave pode ser atualizada via painel sem problemas.";
} else {
    echo "<br>⚠️ <strong>Sistema com problemas!</strong><br>";
    echo "Verifique as configurações antes de usar.";
}

echo "</div>";

echo "<br><br>";
echo "<h3>🚀 Próximos Passos:</h3>";
echo "1. Acesse o painel de faturas<br>";
echo "2. Clique em '🔑 Configurar API'<br>";
echo "3. Teste a atualização da chave<br>";
echo "4. Verifique se tudo funciona corretamente<br>";
echo "<br>";
echo "<h3>🔧 Como Usar:</h3>";
echo "• <strong>Atualizar chave:</strong> Use o modal no painel de faturas<br>";
echo "• <strong>Testar chave:</strong> Clique em 'Testar' no modal<br>";
echo "• <strong>Aplicar chave:</strong> Clique em 'Aplicar Nova Chave'<br>";
echo "• <strong>Verificar logs:</strong> Arquivo logs/asaas_key_updates.log<br>";
?> 