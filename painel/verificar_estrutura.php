<?php
/**
 * Script para verificar estrutura de arquivos no servidor
 * Acesse: https://app.pixel12digital.com.br/painel/verificar_estrutura.php
 */

echo "<h1>🔍 Verificação da Estrutura do Servidor</h1>";
echo "<style>
    body{font-family:Arial,sans-serif;margin:20px;background:#f5f5f5;}
    .container{max-width:800px;margin:0 auto;background:white;padding:20px;border-radius:10px;box-shadow:0 2px 10px rgba(0,0,0,0.1);}
    .info{background:#e3f2fd;padding:15px;margin:10px 0;border-radius:8px;border-left:4px solid #2196f3;}
    .success{background:#e8f5e8;padding:15px;margin:10px 0;border-radius:8px;border-left:4px solid #4caf50;}
    .error{background:#ffebee;padding:15px;margin:10px 0;border-radius:8px;border-left:4px solid #f44336;}
    .file{background:#f5f5f5;padding:8px;margin:5px 0;border-radius:5px;font-family:monospace;}
</style>";

echo "<div class='container'>";

// Informações do servidor
echo "<div class='info'>";
echo "<h3>📋 Informações do Servidor</h3>";
echo "<p><strong>Document Root:</strong> " . $_SERVER['DOCUMENT_ROOT'] . "</p>";
echo "<p><strong>Script Path:</strong> " . __FILE__ . "</p>";
echo "<p><strong>Current Directory:</strong> " . getcwd() . "</p>";
echo "<p><strong>Server Name:</strong> " . $_SERVER['SERVER_NAME'] . "</p>";
echo "</div>";

// Verificar se este arquivo existe
echo "<div class='success'>";
echo "<h3>✅ Este arquivo existe</h3>";
echo "<p>Se você está vendo esta mensagem, o arquivo <code>verificar_estrutura.php</code> está funcionando.</p>";
echo "</div>";

// Listar arquivos na pasta atual
echo "<div class='info'>";
echo "<h3>📁 Arquivos na pasta atual (painel/)</h3>";
$files = scandir('.');
$php_files = [];
$other_files = [];

foreach ($files as $file) {
    if ($file != '.' && $file != '..') {
        if (pathinfo($file, PATHINFO_EXTENSION) === 'php') {
            $php_files[] = $file;
        } else {
            $other_files[] = $file;
        }
    }
}

echo "<h4>📄 Arquivos PHP:</h4>";
if (count($php_files) > 0) {
    foreach ($php_files as $file) {
        echo "<div class='file'>✅ $file</div>";
    }
} else {
    echo "<div class='error'>❌ Nenhum arquivo PHP encontrado</div>";
}

echo "<h4>📁 Outros arquivos:</h4>";
if (count($other_files) > 0) {
    foreach ($other_files as $file) {
        echo "<div class='file'>📁 $file</div>";
    }
} else {
    echo "<div class='file'>📁 Nenhum outro arquivo encontrado</div>";
}
echo "</div>";

// Verificar se o arquivo de teste existe
echo "<div class='info'>";
echo "<h3>🧪 Verificação do Arquivo de Teste</h3>";
$test_file = 'teste_producao_completo.php';
if (file_exists($test_file)) {
    echo "<div class='success'>✅ Arquivo <code>$test_file</code> encontrado!</div>";
    echo "<p><strong>Tamanho:</strong> " . filesize($test_file) . " bytes</p>";
    echo "<p><strong>Última modificação:</strong> " . date('d/m/Y H:i:s', filemtime($test_file)) . "</p>";
} else {
    echo "<div class='error'>❌ Arquivo <code>$test_file</code> NÃO encontrado</div>";
    echo "<p>O arquivo precisa ser enviado para o servidor.</p>";
}
echo "</div>";

// Verificar pastas importantes
echo "<div class='info'>";
echo "<h3>📂 Verificação de Pastas</h3>";
$pastas_importantes = ['api', 'sql', 'cron', 'assets', 'logs'];

foreach ($pastas_importantes as $pasta) {
    if (is_dir($pasta)) {
        echo "<div class='success'>✅ Pasta <code>$pasta/</code> existe</div>";
    } else {
        echo "<div class='error'>❌ Pasta <code>$pasta/</code> não encontrada</div>";
    }
}
echo "</div>";

// Verificar arquivos de configuração
echo "<div class='info'>";
echo "<h3>⚙️ Arquivos de Configuração</h3>";
$config_files = ['config.php', 'db.php'];

foreach ($config_files as $file) {
    if (file_exists($file)) {
        echo "<div class='success'>✅ <code>$file</code> encontrado</div>";
    } else {
        echo "<div class='error'>❌ <code>$file</code> não encontrado</div>";
    }
}
echo "</div>";

// Instruções para upload
echo "<div class='info'>";
echo "<h3>📤 Como Fazer Upload do Arquivo de Teste</h3>";
echo "<ol>";
echo "<li>Acesse o painel de controle do seu hosting</li>";
echo "<li>Vá para o <strong>File Manager</strong></li>";
echo "<li>Navegue até a pasta <code>painel/</code></li>";
echo "<li>Faça upload do arquivo <code>teste_producao_completo.php</code></li>";
echo "<li>Acesse: <code>https://app.pixel12digital.com.br/painel/teste_producao_completo.php</code></li>";
echo "</ol>";
echo "</div>";

echo "</div>";
?> 