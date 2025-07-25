<?php
/**
 * Script para corrigir problemas na configuração da API do Asaas
 */

require_once __DIR__ . '/../config.php';

echo "<h2>🔧 Correção da Configuração da API do Asaas</h2>";

// 1. Verificar se a chave atual está funcionando
echo "<h3>1. Verificação da Chave Atual</h3>";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, ASAAS_API_URL . '/customers?limit=1');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'access_token: ' . ASAAS_API_KEY
]);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);

$result = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "<p><strong>Status da API:</strong> ";
if ($http_code === 200) {
    echo "✅ Funcionando (HTTP $http_code)";
} else {
    echo "❌ Problema (HTTP $http_code)";
    if ($error) {
        echo " - Erro: $error";
    }
}
echo "</p>";

// 2. Verificar estrutura do config.php
echo "<h3>2. Análise do config.php</h3>";

$config_content = file_get_contents('config.php');
$lines = explode("\n", $config_content);

$asaas_lines = [];
foreach ($lines as $line_num => $line) {
    if (strpos($line, 'ASAAS_API_KEY') !== false) {
        $asaas_lines[] = [
            'line' => $line_num + 1,
            'content' => trim($line)
        ];
    }
}

echo "<p><strong>Linhas encontradas com ASAAS_API_KEY:</strong> " . count($asaas_lines) . "</p>";

foreach ($asaas_lines as $line_info) {
    echo "<p><strong>Linha {$line_info['line']}:</strong> <code>" . htmlspecialchars($line_info['content']) . "</code></p>";
}

// 3. Verificar se há problemas de sintaxe
echo "<h3>3. Verificação de Sintaxe</h3>";

$syntax_ok = true;
foreach ($asaas_lines as $line_info) {
    $line = $line_info['content'];
    
    // Verificar se a linha tem a sintaxe correta
    if (!preg_match('/^define\s*\(\s*[\'"]ASAAS_API_KEY[\'"]\s*,\s*[\'"][^\'"]*[\'"]\s*\)\s*;?\s*$/', $line)) {
        echo "<p>❌ <strong>Linha {$line_info['line']}:</strong> Sintaxe incorreta</p>";
        $syntax_ok = false;
    } else {
        echo "<p>✅ <strong>Linha {$line_info['line']}:</strong> Sintaxe correta</p>";
    }
}

// 4. Corrigir problemas se necessário
echo "<h3>4. Correção Automática</h3>";

if (!$syntax_ok || $http_code !== 200) {
    echo "<p>🔧 Iniciando correção automática...</p>";
    
    // Fazer backup
    $backup_file = 'config.php.backup.' . date('Y-m-d_H-i-s');
    file_put_contents($backup_file, $config_content);
    echo "<p>✅ Backup criado: $backup_file</p>";
    
    // Corrigir a estrutura
    $nova_chave = ASAAS_API_KEY; // Manter a chave atual se estiver funcionando
    
    // Padrão para encontrar e substituir
    $pattern = "/define\s*\(\s*['\"]ASAAS_API_KEY['\"]\s*,\s*['\"][^'\"]*['\"]\s*\)\s*;?\s*$/m";
    $replacement = "define('ASAAS_API_KEY', '$nova_chave');";
    
    $new_content = preg_replace($pattern, $replacement, $config_content);
    
    if ($new_content !== $config_content) {
        if (file_put_contents('config.php', $new_content)) {
            echo "<p>✅ Arquivo config.php corrigido com sucesso!</p>";
            
            // Testar novamente
            echo "<p>🔄 Testando conexão após correção...</p>";
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, ASAAS_API_URL . '/customers?limit=1');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'access_token: ' . $nova_chave
            ]);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            
            $result = curl_exec($ch);
            $http_code_after = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($http_code_after === 200) {
                echo "<p>✅ Correção bem-sucedida! API funcionando (HTTP $http_code_after)</p>";
            } else {
                echo "<p>⚠️ API ainda com problemas (HTTP $http_code_after)</p>";
            }
        } else {
            echo "<p>❌ Erro ao salvar o arquivo config.php</p>";
        }
    } else {
        echo "<p>⚠️ Nenhuma alteração necessária no arquivo</p>";
    }
} else {
    echo "<p>✅ Configuração está correta, nenhuma correção necessária</p>";
}

// 5. Verificar permissões
echo "<h3>5. Verificação de Permissões</h3>";

$config_file = 'config.php';
if (file_exists($config_file)) {
    $perms = fileperms($config_file);
    $perms_octal = substr(sprintf('%o', $perms), -4);
    echo "<p><strong>Permissões:</strong> $perms_octal</p>";
    echo "<p><strong>Legível:</strong> " . (is_readable($config_file) ? "✅ Sim" : "❌ Não") . "</p>";
    echo "<p><strong>Gravável:</strong> " . (is_writable($config_file) ? "✅ Sim" : "❌ Não") . "</p>";
    
    if (!is_writable($config_file)) {
        echo "<p>⚠️ <strong>Atenção:</strong> O arquivo config.php não é gravável. Isso pode causar problemas na atualização da chave.</p>";
    }
}

echo "<hr>";
echo "<p><em>Correção concluída em " . date('Y-m-d H:i:s') . "</em></p>";
echo "<p><a href='faturas.php'>← Voltar para Faturas</a></p>";
?> 