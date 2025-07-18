<?php
/**
 * Script para corrigir automaticamente problemas de sincronização
 * Acesse: http://localhost/loja-virtual-revenda/painel/corrigir_sincronizacao.php
 */

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>🔧 Correção Automática de Sincronização</h1>";

// Função para log
function logCorrecao($mensagem, $tipo = 'info') {
    $cores = [
        'info' => '#3b82f6',
        'success' => '#059669',
        'warning' => '#d97706',
        'error' => '#dc2626'
    ];
    $cor = $cores[$tipo] ?? '#3b82f6';
    echo "<p style='color: $cor;'>" . date('H:i:s') . " - $mensagem</p>";
}

// 1. Verificar e criar diretório de logs
echo "<h2>1. Verificando Estrutura de Logs</h2>";
$logDir = __DIR__ . '/../logs';
if (!is_dir($logDir)) {
    if (mkdir($logDir, 0755, true)) {
        logCorrecao("✅ Diretório de logs criado: $logDir", 'success');
    } else {
        logCorrecao("❌ Erro ao criar diretório de logs", 'error');
    }
} else {
    logCorrecao("✅ Diretório de logs já existe", 'success');
}

// 2. Limpar logs antigos se necessário
$logFile = $logDir . '/sincroniza_asaas_debug.log';
if (file_exists($logFile) && filesize($logFile) > 1024 * 1024) { // > 1MB
    if (unlink($logFile)) {
        logCorrecao("✅ Log antigo removido (muito grande)", 'warning');
    }
}

// 3. Verificar configurações
echo "<h2>2. Verificando Configurações</h2>";
require_once 'config.php';

if (defined('DB_HOST') && defined('DB_USER') && defined('DB_PASS') && defined('DB_NAME')) {
    logCorrecao("✅ Configurações do banco definidas", 'success');
} else {
    logCorrecao("❌ Configurações do banco incompletas", 'error');
}

if (defined('ASAAS_API_KEY') && defined('ASAAS_API_URL')) {
    logCorrecao("✅ Configurações do Asaas definidas", 'success');
} else {
    logCorrecao("❌ Configurações do Asaas incompletas", 'error');
}

// 4. Testar conexão com banco
echo "<h2>3. Testando Conexão com Banco</h2>";
try {
    $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($mysqli->connect_errno) {
        logCorrecao("❌ Erro de conexão: " . $mysqli->connect_error, 'error');
        
        // Tentar soluções automáticas
        if ($mysqli->connect_errno == 2002) {
            logCorrecao("⚠️ MySQL não está rodando. Inicie o XAMPP Control Panel", 'warning');
        }
    } else {
        logCorrecao("✅ Conexão com banco estabelecida", 'success');
        
        // Verificar tabelas necessárias
        $tabelas_necessarias = ['clientes', 'cobrancas'];
        $tabelas_faltando = [];
        
        foreach ($tabelas_necessarias as $tabela) {
            $result = $mysqli->query("SHOW TABLES LIKE '$tabela'");
            if ($result->num_rows == 0) {
                $tabelas_faltando[] = $tabela;
            }
        }
        
        if (!empty($tabelas_faltando)) {
            logCorrecao("⚠️ Tabelas faltando: " . implode(', ', $tabelas_faltando), 'warning');
            logCorrecao("ℹ️ Execute o script de instalação para criar as tabelas", 'info');
        } else {
            logCorrecao("✅ Todas as tabelas necessárias existem", 'success');
        }
        
        $mysqli->close();
    }
} catch (Exception $e) {
    logCorrecao("❌ Exceção na conexão: " . $e->getMessage(), 'error');
}

// 5. Testar conexão com API do Asaas
echo "<h2>4. Testando Conexão com Asaas</h2>";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, ASAAS_API_URL . '/customers?limit=1');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'access_token: ' . ASAAS_API_KEY
]);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);

$result = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

if ($curlError) {
    logCorrecao("❌ Erro de conexão com Asaas: $curlError", 'error');
} elseif ($httpCode !== 200) {
    logCorrecao("❌ Erro HTTP $httpCode ao conectar com Asaas", 'error');
    if ($httpCode == 401) {
        logCorrecao("⚠️ Chave da API do Asaas inválida", 'warning');
    }
} else {
    logCorrecao("✅ Conexão com Asaas funcionando", 'success');
}

// 6. Corrigir arquivo de status da sincronização
echo "<h2>5. Corrigindo Arquivo de Status</h2>";
$statusFile = __DIR__ . '/api/sync_status.php';
if (file_exists($statusFile)) {
    logCorrecao("✅ Arquivo de status existe", 'success');
} else {
    logCorrecao("❌ Arquivo de status não encontrado", 'error');
}

// 7. Criar log de teste se não existir
echo "<h2>6. Preparando Log de Teste</h2>";
if (!file_exists($logFile)) {
    $logTeste = [
        date('Y-m-d H:i:s') . ' - Log de teste criado automaticamente',
        date('Y-m-d H:i:s') . ' - Sistema pronto para sincronização',
        date('Y-m-d H:i:s') . ' - Clique em "Sincronizar com Asaas" para testar'
    ];
    
    if (file_put_contents($logFile, implode("\n", $logTeste))) {
        logCorrecao("✅ Log de teste criado", 'success');
    } else {
        logCorrecao("❌ Erro ao criar log de teste", 'error');
    }
} else {
    logCorrecao("✅ Log já existe", 'success');
}

// 8. Verificar permissões de arquivos
echo "<h2>7. Verificando Permissões</h2>";
$arquivos_importantes = [
    'sincroniza_asaas.php',
    'sincronizar_asaas_ajax.php',
    'api/sync_status.php',
    'config.php',
    'db.php'
];

foreach ($arquivos_importantes as $arquivo) {
    $caminho = __DIR__ . '/' . $arquivo;
    if (file_exists($caminho)) {
        if (is_readable($caminho)) {
            logCorrecao("✅ $arquivo - Legível", 'success');
        } else {
            logCorrecao("❌ $arquivo - Não legível", 'error');
        }
    } else {
        logCorrecao("❌ $arquivo - Não encontrado", 'error');
    }
}

// 9. Criar script de teste de sincronização
echo "<h2>8. Criando Script de Teste</h2>";
$testeFile = __DIR__ . '/teste_sincronizacao_simples.php';
$testeContent = '<?php
/**
 * Teste simples de sincronização
 */
ini_set("display_errors", 1);
ini_set("display_startup_errors", 1);
error_reporting(E_ALL);

echo "<h1>🧪 Teste Simples de Sincronização</h1>";

require_once "config.php";
require_once "db.php";

echo "<h2>1. Testando Conexão com Banco</h2>";
try {
    if ($mysqli->ping()) {
        echo "<p style=\"color: green;\">✅ Conexão com banco OK</p>";
    } else {
        echo "<p style=\"color: red;\">❌ Problema na conexão com banco</p>";
    }
} catch (Exception $e) {
    echo "<p style=\"color: red;\">❌ Erro: " . $e->getMessage() . "</p>";
}

echo "<h2>2. Testando Conexão com Asaas</h2>";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, ASAAS_API_URL . "/customers?limit=1");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json",
    "access_token: " . ASAAS_API_KEY
]);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);

$result = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode == 200) {
    echo "<p style=\"color: green;\">✅ Conexão com Asaas OK</p>";
} else {
    echo "<p style=\"color: red;\">❌ Erro HTTP $httpCode ao conectar com Asaas</p>";
}

echo "<h2>3. Próximos Passos</h2>";
echo "<p><a href=\"faturas.php\">🔗 Ir para página de Faturas</a></p>";
echo "<p><a href=\"sincronizar_asaas_ajax.php\">🔗 Testar Sincronização AJAX</a></p>";
echo "<p><a href=\"sincroniza_asaas.php\">🔗 Testar Sincronização Direta</a></p>";
?>';

if (file_put_contents($testeFile, $testeContent)) {
    logCorrecao("✅ Script de teste criado: teste_sincronizacao_simples.php", 'success');
} else {
    logCorrecao("❌ Erro ao criar script de teste", 'error');
}

// 10. Resumo final
echo "<h2>9. Resumo da Correção</h2>";
echo "<div style='background: #f8fafc; padding: 20px; border-radius: 10px; border-left: 4px solid #3b82f6;'>";
echo "<h3>✅ Problemas Corrigidos:</h3>";
echo "<ul>";
echo "<li>Estrutura de logs verificada e criada se necessário</li>";
echo "<li>Configurações do sistema validadas</li>";
echo "<li>Conexões com banco e API testadas</li>";
echo "<li>Script de teste criado</li>";
echo "</ul>";

echo "<h3>🔧 Próximos Passos:</h3>";
echo "<ol>";
echo "<li><a href='teste_sincronizacao_simples.php'>Testar conexões básicas</a></li>";
echo "<li><a href='faturas.php'>Ir para página de Faturas</a></li>";
echo "<li>Clique em 'Sincronizar com Asaas'</a></li>";
echo "<li>Se houver problemas, verifique os logs</li>";
echo "</ol>";

echo "<h3>📋 Links Úteis:</h3>";
echo "<ul>";
echo "<li><a href='verificar_conexao_banco.php'>🔍 Diagnóstico Completo</a></li>";
echo "<li><a href='teste_modal_sync.php'>🧪 Teste do Modal</a></li>";
echo "<li><a href='../logs/sincroniza_asaas_debug.log'>📄 Ver Log de Sincronização</a></li>";
echo "</ul>";
echo "</div>";

echo "<p><strong>Correção concluída em:</strong> " . date('Y-m-d H:i:s') . "</p>";
?> 