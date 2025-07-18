<?php
/**
 * Script para verificar logs de debug da API do Asaas
 */

echo "<h1>🔍 Verificação de Logs de Debug</h1>";

// Verificar logs de teste
$logTestFile = __DIR__ . '/logs/asaas_test_debug.log';
echo "<h2>1. Logs de Teste da API</h2>";

if (file_exists($logTestFile)) {
    $logs = file_get_contents($logTestFile);
    if (!empty($logs)) {
        echo "<pre style='background:#f3f4f6;padding:15px;border-radius:8px;max-height:400px;overflow:auto;'>";
        echo htmlspecialchars($logs);
        echo "</pre>";
    } else {
        echo "<p>Nenhum log encontrado.</p>";
    }
} else {
    echo "<p>Arquivo de log não encontrado: $logTestFile</p>";
}

// Verificar logs de atualização de chaves
$logUpdateFile = __DIR__ . '/logs/asaas_key_updates.log';
echo "<h2>2. Logs de Atualização de Chaves</h2>";

if (file_exists($logUpdateFile)) {
    $logs = file_get_contents($logUpdateFile);
    if (!empty($logs)) {
        echo "<pre style='background:#f3f4f6;padding:15px;border-radius:8px;max-height:400px;overflow:auto;'>";
        echo htmlspecialchars($logs);
        echo "</pre>";
    } else {
        echo "<p>Nenhum log encontrado.</p>";
    }
} else {
    echo "<p>Arquivo de log não encontrado: $logUpdateFile</p>";
}

// Verificar logs de sincronização
$logSyncFile = __DIR__ . '/logs/sincroniza_asaas_debug.log';
echo "<h2>3. Logs de Sincronização</h2>";

if (file_exists($logSyncFile)) {
    $logs = file_get_contents($logSyncFile);
    if (!empty($logs)) {
        // Mostrar apenas as últimas 50 linhas
        $lines = explode("\n", $logs);
        $lastLines = array_slice($lines, -50);
        echo "<pre style='background:#f3f4f6;padding:15px;border-radius:8px;max-height:400px;overflow:auto;'>";
        echo htmlspecialchars(implode("\n", $lastLines));
        echo "</pre>";
    } else {
        echo "<p>Nenhum log encontrado.</p>";
    }
} else {
    echo "<p>Arquivo de log não encontrado: $logSyncFile</p>";
}

// Verificar configuração atual
echo "<h2>4. Configuração Atual</h2>";
require_once 'painel/config.php';

if (defined('ASAAS_API_KEY')) {
    $chave = ASAAS_API_KEY;
    $tipo = strpos($chave, '_test_') !== false ? 'TESTE' : 'PRODUÇÃO';
    echo "<p><strong>Chave Atual:</strong> " . substr($chave, 0, 20) . "...</p>";
    echo "<p><strong>Tipo:</strong> $tipo</p>";
    echo "<p><strong>URL da API:</strong> " . ASAAS_API_URL . "</p>";
} else {
    echo "<p><strong>❌ Chave da API não está definida</strong></p>";
}

// Teste rápido da chave atual
echo "<h2>5. Teste Rápido da Chave Atual</h2>";
if (defined('ASAAS_API_KEY')) {
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
    
    echo "<p><strong>Código HTTP:</strong> $httpCode</p>";
    echo "<p><strong>Erro cURL:</strong> " . ($curlError ?: 'Nenhum') . "</p>";
    
    if ($httpCode == 200) {
        echo "<div style='background:#f0fdf4;border:1px solid #bbf7d0;padding:15px;border-radius:8px;color:#059669;'>";
        echo "<strong>✅ Chave válida!</strong>";
        echo "</div>";
    } elseif ($httpCode == 401) {
        echo "<div style='background:#fef2f2;border:1px solid #fecaca;padding:15px;border-radius:8px;color:#dc2626;'>";
        echo "<strong>❌ Chave inválida (401)</strong>";
        $response = json_decode($result, true);
        if ($response && isset($response['errors'][0]['description'])) {
            echo "<br>Detalhes: " . $response['errors'][0]['description'];
        }
        echo "</div>";
    } else {
        echo "<div style='background:#fef2f2;border:1px solid #fecaca;padding:15px;border-radius:8px;color:#dc2626;'>";
        echo "<strong>⚠️ Erro HTTP $httpCode</strong>";
        if ($curlError) {
            echo "<br>Erro de conexão: $curlError";
        }
        echo "</div>";
    }
} else {
    echo "<p><strong>❌ Não é possível testar - chave não definida</strong></p>";
}

echo "<h2>6. Links Úteis</h2>";
echo "<p><a href='teste_chave_direto.php' style='background:#7c3aed;color:white;padding:10px 20px;text-decoration:none;border-radius:6px;display:inline-block;margin-right:10px;'>🔑 Teste Detalhado da Chave</a>";
echo "<a href='painel/faturas.php' style='background:#059669;color:white;padding:10px 20px;text-decoration:none;border-radius:6px;display:inline-block;'>📄 Voltar para Faturas</a></p>";
?> 