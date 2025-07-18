<?php
/**
 * Script para corrigir a chave da API do Asaas
 * Acesse: http://localhost/loja-virtual-revenda/painel/corrigir_chave_asaas.php
 */

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>🔑 Correção da Chave da API do Asaas</h1>";

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

// 1. Verificar configuração atual
echo "<h2>1. Configuração Atual</h2>";
require_once 'config.php';

$ambiente_atual = strpos($_SERVER['SERVER_NAME'] ?? '', 'localhost') !== false ? 'LOCAL' : 'PRODUÇÃO';
logCorrecao("Ambiente detectado: $ambiente_atual", 'info');

if (defined('ASAAS_API_KEY')) {
    $chave_atual = ASAAS_API_KEY;
    $tipo_chave = strpos($chave_atual, '_test_') !== false ? 'TESTE' : 'PRODUÇÃO';
    logCorrecao("Chave atual: $tipo_chave", 'info');
    logCorrecao("Primeiros 20 caracteres: " . substr($chave_atual, 0, 20) . "...", 'info');
} else {
    logCorrecao("❌ Chave da API não está definida", 'error');
}

// 2. Testar conexão com a chave atual
echo "<h2>2. Teste da Chave Atual</h2>";
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
    logCorrecao("❌ Erro de conexão: $curlError", 'error');
} elseif ($httpCode == 401) {
    logCorrecao("❌ Erro 401: Chave da API inválida", 'error');
    $response = json_decode($result, true);
    if ($response && isset($response['errors'][0]['description'])) {
        logCorrecao("Detalhes: " . $response['errors'][0]['description'], 'error');
    }
} elseif ($httpCode == 200) {
    logCorrecao("✅ Chave da API válida!", 'success');
} else {
    logCorrecao("⚠️ Erro HTTP $httpCode", 'warning');
}

// 3. Sugestões de correção
echo "<h2>3. Sugestões de Correção</h2>";

if ($ambiente_atual === 'LOCAL') {
    echo "<div style='background: #f0f9ff; border: 1px solid #0ea5e9; padding: 15px; border-radius: 8px; margin: 10px 0;'>";
    echo "<h3>🔧 Para Ambiente Local (XAMPP)</h3>";
    echo "<p><strong>Opção 1:</strong> Usar chave de teste do Asaas</p>";
    echo "<p><strong>Opção 2:</strong> Configurar variável de ambiente</p>";
    echo "<p><strong>Opção 3:</strong> Usar chave de produção (se disponível)</p>";
    echo "</div>";
    
    echo "<h4>📝 Como configurar:</h4>";
    echo "<ol>";
    echo "<li><strong>Chave de Teste:</strong> Acesse o painel do Asaas → Configurações → API → Copie a chave de teste</li>";
    echo "<li><strong>Variável de Ambiente:</strong> Crie um arquivo .env ou configure no sistema</li>";
    echo "<li><strong>Chave de Produção:</strong> Use apenas se tiver certeza de que é segura</li>";
    echo "</ol>";
} else {
    echo "<div style='background: #fef2f2; border: 1px solid #fecaca; padding: 15px; border-radius: 8px; margin: 10px 0;'>";
    echo "<h3>🚨 Para Ambiente de Produção</h3>";
    echo "<p><strong>IMPORTANTE:</strong> A chave de produção deve ser válida e ativa</p>";
    echo "<p>Verifique no painel do Asaas se a chave está correta e ativa.</p>";
    echo "</div>";
}

// 4. Formulário para atualizar a chave
echo "<h2>4. Atualizar Chave da API</h2>";
echo "<form method='POST' style='background: #f8fafc; padding: 20px; border-radius: 10px; border: 1px solid #e2e8f0;'>";
echo "<div style='margin-bottom: 15px;'>";
echo "<label for='nova_chave' style='display: block; margin-bottom: 5px; font-weight: 600;'>Nova Chave da API:</label>";
echo "<input type='text' id='nova_chave' name='nova_chave' style='width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; font-family: monospace;' placeholder='$aact_...' required>";
echo "</div>";
echo "<div style='margin-bottom: 15px;'>";
echo "<label for='tipo_chave' style='display: block; margin-bottom: 5px; font-weight: 600;'>Tipo de Chave:</label>";
echo "<select id='tipo_chave' name='tipo_chave' style='width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px;'>";
echo "<option value='test'>Teste (Sandbox)</option>";
echo "<option value='prod'>Produção</option>";
echo "</select>";
echo "</div>";
echo "<button type='submit' style='background: #7c3aed; color: white; padding: 12px 24px; border: none; border-radius: 6px; cursor: pointer; font-weight: 600;'>Atualizar Chave</button>";
echo "</form>";

// 5. Processar atualização da chave
if ($_POST && isset($_POST['nova_chave']) && isset($_POST['tipo_chave'])) {
    echo "<h2>5. Processando Atualização</h2>";
    
    $nova_chave = trim($_POST['nova_chave']);
    $tipo_chave = $_POST['tipo_chave'];
    
    if (empty($nova_chave)) {
        logCorrecao("❌ Chave não pode estar vazia", 'error');
    } elseif (!preg_match('/^\$aact_/', $nova_chave)) {
        logCorrecao("❌ Formato de chave inválido. Deve começar com '$aact_'", 'error');
    } else {
        // Testar a nova chave
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, ASAAS_API_URL . '/customers?limit=1');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'access_token: ' . $nova_chave
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        
        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode == 200) {
            logCorrecao("✅ Nova chave testada com sucesso!", 'success');
            
            // Atualizar o arquivo de configuração
            $config_file = __DIR__ . '/config.php';
            $config_content = file_get_contents($config_file);
            
            if ($tipo_chave === 'test') {
                // Atualizar chave de teste
                $pattern = "/define\('ASAAS_API_KEY',\s*getenv\('ASAAS_API_KEY'\)\s*\?:\s*'[^']*'\);/";
                $replacement = "define('ASAAS_API_KEY', getenv('ASAAS_API_KEY') ?: '$nova_chave');";
            } else {
                // Atualizar chave de produção
                $pattern = "/define\('ASAAS_API_KEY',\s*'[^']*'\);/";
                $replacement = "define('ASAAS_API_KEY', '$nova_chave');";
            }
            
            $novo_conteudo = preg_replace($pattern, $replacement, $config_content);
            
            if ($novo_conteudo !== $config_content) {
                if (file_put_contents($config_file, $novo_conteudo)) {
                    logCorrecao("✅ Arquivo de configuração atualizado com sucesso!", 'success');
                    logCorrecao("🔄 Recarregue a página para aplicar as mudanças", 'info');
                } else {
                    logCorrecao("❌ Erro ao salvar arquivo de configuração", 'error');
                }
            } else {
                logCorrecao("⚠️ Nenhuma alteração foi feita no arquivo", 'warning');
            }
        } else {
            logCorrecao("❌ Nova chave inválida (HTTP $httpCode)", 'error');
            $response = json_decode($result, true);
            if ($response && isset($response['errors'][0]['description'])) {
                logCorrecao("Detalhes: " . $response['errors'][0]['description'], 'error');
            }
        }
    }
}

// 6. Links úteis
echo "<h2>6. Links Úteis</h2>";
echo "<div style='background: #f0f9ff; padding: 15px; border-radius: 8px; border-left: 4px solid #0ea5e9;'>";
echo "<h3>🔗 Recursos do Asaas</h3>";
echo "<ul>";
echo "<li><a href='https://www.asaas.com/api-docs/' target='_blank'>📚 Documentação da API</a></li>";
echo "<li><a href='https://www.asaas.com/api-docs/#section/Autenticacao' target='_blank'>🔐 Guia de Autenticação</a></li>";
echo "<li><a href='https://www.asaas.com/api-docs/#section/Ambiente-de-Teste' target='_blank'>🧪 Ambiente de Teste</a></li>";
echo "</ul>";
echo "</div>";

// 7. Teste final
echo "<h2>7. Teste Final</h2>";
echo "<p><a href='teste_sincronizacao_simples.php' style='background: #7c3aed; color: white; padding: 10px 20px; text-decoration: none; border-radius: 6px; display: inline-block;'>🧪 Testar Sincronização</a></p>";
echo "<p><a href='faturas.php' style='background: #059669; color: white; padding: 10px 20px; text-decoration: none; border-radius: 6px; display: inline-block;'>📄 Ir para Faturas</a></p>";

echo "<p><strong>Última atualização:</strong> " . date('Y-m-d H:i:s') . "</p>";
?> 