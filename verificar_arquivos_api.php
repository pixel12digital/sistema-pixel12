<?php
/**
 * Verificação de Arquivos da API para Upload
 */

echo "<h1>📁 Verificação de Arquivos da API</h1>";

echo "<h2>Arquivos Necessários para o Sistema de Chave API</h2>";

$arquivosNecessarios = [
    'painel/api/get_asaas_config.php' => 'Obtém a chave atual da API',
    'painel/api/test_asaas_key.php' => 'Testa chaves da API (GET e POST)',
    'painel/api/update_asaas_key.php' => 'Atualiza a chave da API',
    'painel/config.php' => 'Arquivo de configuração principal'
];

echo "<div style='background:#f0f9ff;border:1px solid #0ea5e9;padding:15px;border-radius:8px;'>";
echo "<h3>📋 Lista de Arquivos:</h3>";
echo "<table style='width:100%;border-collapse:collapse;'>";
echo "<tr style='background:#e0f2fe;'>";
echo "<th style='padding:10px;border:1px solid #0ea5e9;text-align:left;'>Arquivo</th>";
echo "<th style='padding:10px;border:1px solid #0ea5e9;text-align:left;'>Status</th>";
echo "<th style='padding:10px;border:1px solid #0ea5e9;text-align:left;'>Descrição</th>";
echo "</tr>";

foreach ($arquivosNecessarios as $arquivo => $descricao) {
    $existe = file_exists($arquivo);
    $status = $existe ? '✅ Existe' : '❌ Não encontrado';
    $cor = $existe ? '#d4edda' : '#f8d7da';
    
    echo "<tr style='background:$cor;'>";
    echo "<td style='padding:10px;border:1px solid #0ea5e9;font-family:monospace;'>$arquivo</td>";
    echo "<td style='padding:10px;border:1px solid #0ea5e9;'>$status</td>";
    echo "<td style='padding:10px;border:1px solid #0ea5e9;'>$descricao</td>";
    echo "</tr>";
}

echo "</table>";
echo "</div>";

echo "<h2>Conteúdo dos Arquivos</h2>";

foreach ($arquivosNecessarios as $arquivo => $descricao) {
    if (file_exists($arquivo)) {
        echo "<h3>📄 $arquivo</h3>";
        echo "<div style='background:#f8f9fa;border:1px solid #e9ecef;padding:15px;border-radius:8px;margin-bottom:20px;'>";
        echo "<p><strong>Descrição:</strong> $descricao</p>";
        echo "<p><strong>Tamanho:</strong> " . filesize($arquivo) . " bytes</p>";
        echo "<p><strong>Última modificação:</strong> " . date('d/m/Y H:i:s', filemtime($arquivo)) . "</p>";
        
        // Mostrar primeiras linhas do arquivo
        $conteudo = file_get_contents($arquivo);
        $linhas = explode("\n", $conteudo);
        $primeirasLinhas = array_slice($linhas, 0, 10);
        
        echo "<p><strong>Primeiras 10 linhas:</strong></p>";
        echo "<pre style='background:#f3f4f6;padding:10px;border-radius:5px;max-height:200px;overflow:auto;font-size:12px;'>";
        echo htmlspecialchars(implode("\n", $primeirasLinhas));
        if (count($linhas) > 10) {
            echo "\n... (" . (count($linhas) - 10) . " linhas restantes)";
        }
        echo "</pre>";
        echo "</div>";
    }
}

echo "<h2>Instruções para Upload</h2>";
echo "<div style='background:#fef3c7;border:1px solid #f59e0b;padding:15px;border-radius:8px;'>";
echo "<h3>🚀 Como Enviar para o Servidor:</h3>";
echo "<ol>";
echo "<li><strong>Via FTP/File Manager:</strong>";
echo "<ul>";
echo "<li>Acesse o painel de controle do seu hosting</li>";
echo "<li>Vá em File Manager ou use um cliente FTP</li>";
echo "<li>Navegue até a pasta <code>public_html/painel/api/</code></li>";
echo "<li>Faça upload dos arquivos listados acima</li>";
echo "</ul>";
echo "</li>";
echo "<li><strong>Verificação:</strong>";
echo "<ul>";
echo "<li>Após o upload, acesse: <code>https://app.pixel12digital.com.br/painel/api/test_asaas_key.php</code></li>";
echo "<li>Deve retornar JSON, não erro 404</li>";
echo "</ul>";
echo "</li>";
echo "</ol>";
echo "</div>";

echo "<h2>Teste de Endpoints</h2>";
echo "<div style='background:#f0fdf4;border:1px solid #bbf7d0;padding:15px;border-radius:8px;'>";
echo "<h3>🔗 URLs para Testar:</h3>";
echo "<ul>";
echo "<li><strong>Teste da chave atual:</strong> <code>https://app.pixel12digital.com.br/painel/api/test_asaas_key.php</code></li>";
echo "<li><strong>Obter configuração:</strong> <code>https://app.pixel12digital.com.br/painel/api/get_asaas_config.php</code></li>";
echo "<li><strong>Teste com nova chave:</strong> <code>https://app.pixel12digital.com.br/painel/api/test_asaas_key.php</code> (POST)</li>";
echo "<li><strong>Atualizar chave:</strong> <code>https://app.pixel12digital.com.br/painel/api/update_asaas_key.php</code> (POST)</li>";
echo "</ul>";
echo "</div>";

echo "<h2>Arquivos para Download</h2>";
echo "<div style='background:#f8fafc;border:1px solid #e2e8f0;padding:15px;border-radius:8px;'>";
echo "<h3>📦 Arquivos Prontos:</h3>";
echo "<p>Clique nos links abaixo para baixar os arquivos necessários:</p>";
echo "<ul>";
foreach ($arquivosNecessarios as $arquivo => $descricao) {
    if (file_exists($arquivo)) {
        $nomeArquivo = basename($arquivo);
        echo "<li><a href='$arquivo' download style='color:#7c3aed;text-decoration:none;'>📄 $nomeArquivo</a> - $descricao</li>";
    }
}
echo "</ul>";
echo "</div>";

echo "<p><a href='painel/faturas.php' style='background:#7c3aed;color:white;padding:10px 20px;text-decoration:none;border-radius:6px;display:inline-block;'>🔑 Voltar para Faturas</a></p>";
?> 