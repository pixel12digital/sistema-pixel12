<?php
/**
 * Verificar qual ambiente está sendo detectado
 */

echo "<h1>🔍 Verificação de Ambiente</h1>";

// Simular a detecção de ambiente
$is_local = false;

// Verificar se está rodando via CLI
if (php_sapi_name() === 'cli') {
    echo "<p><strong>Executando via:</strong> CLI</p>";
    $document_root = $_SERVER['DOCUMENT_ROOT'] ?? '';
    $is_local = (
        strpos($document_root, 'xampp') !== false ||
        strpos(getcwd(), 'xampp') !== false ||
        strpos(__DIR__, 'xampp') !== false
    );
    echo "<p><strong>Document Root:</strong> $document_root</p>";
    echo "<p><strong>Current Directory:</strong> " . getcwd() . "</p>";
    echo "<p><strong>__DIR__:</strong> " . __DIR__ . "</p>";
} else {
    echo "<p><strong>Executando via:</strong> Web</p>";
    echo "<p><strong>SERVER_NAME:</strong> " . ($_SERVER['SERVER_NAME'] ?? 'NÃO DEFINIDO') . "</p>";
    echo "<p><strong>HTTP_HOST:</strong> " . ($_SERVER['HTTP_HOST'] ?? 'NÃO DEFINIDO') . "</p>";
    echo "<p><strong>DOCUMENT_ROOT:</strong> " . ($_SERVER['DOCUMENT_ROOT'] ?? 'NÃO DEFINIDO') . "</p>";
    echo "<p><strong>XAMPP_ROOT:</strong> " . ($_SERVER['XAMPP_ROOT'] ?? 'NÃO DEFINIDO') . "</p>";
    
    $is_local = (
        $_SERVER['SERVER_NAME'] === 'localhost' || 
        strpos($_SERVER['SERVER_NAME'], '127.0.0.1') !== false ||
        strpos($_SERVER['SERVER_NAME'], '.local') !== false ||
        !empty($_SERVER['XAMPP_ROOT']) ||
        !empty($_SERVER['DOCUMENT_ROOT']) && strpos($_SERVER['DOCUMENT_ROOT'], 'xampp') !== false
    );
}

echo "<h2>🔧 Resultado da Detecção:</h2>";
echo "<p><strong>Ambiente Detectado:</strong> " . ($is_local ? 'LOCAL' : 'PRODUÇÃO') . "</p>";

// Verificar se a detecção está correta
$expected_local = (
    strpos($_SERVER['HTTP_HOST'] ?? '', 'localhost') !== false ||
    strpos($_SERVER['HTTP_HOST'] ?? '', '127.0.0.1') !== false ||
    strpos($_SERVER['DOCUMENT_ROOT'] ?? '', 'xampp') !== false
);

echo "<p><strong>Esperado:</strong> " . ($expected_local ? 'LOCAL' : 'PRODUÇÃO') . "</p>";
echo "<p><strong>Detecção Correta:</strong> " . ($is_local === $expected_local ? '✅ SIM' : '❌ NÃO') . "</p>";

// Mostrar configurações que seriam aplicadas
echo "<h2>⚙️ Configurações que Seriam Aplicadas:</h2>";
if ($is_local) {
    echo "<p><strong>DEBUG_MODE:</strong> true</p>";
    echo "<p><strong>ENABLE_CACHE:</strong> false</p>";
    echo "<p><strong>LOCAL_BASE_URL:</strong> http://localhost:8080</p>";
} else {
    echo "<p><strong>DEBUG_MODE:</strong> false</p>";
    echo "<p><strong>ENABLE_CACHE:</strong> true</p>";
    echo "<p><strong>LOCAL_BASE_URL:</strong> null</p>";
}

// Sugerir correção se necessário
if (!$is_local && $expected_local) {
    echo "<h2>🔧 Correção Necessária:</h2>";
    echo "<p>O sistema está detectando PRODUÇÃO mas deveria detectar LOCAL.</p>";
    echo "<p>Isso pode estar causando problemas com cache e configurações.</p>";
    
    echo "<h3>💡 Solução:</h3>";
    echo "<p>Adicionar verificação adicional para HTTP_HOST:</p>";
    echo "<pre>";
    echo "// Adicionar esta verificação na detecção de ambiente:\n";
    echo "strpos(\$_SERVER['HTTP_HOST'] ?? '', 'localhost') !== false ||\n";
    echo "strpos(\$_SERVER['HTTP_HOST'] ?? '', '127.0.0.1') !== false";
    echo "</pre>";
}
?> 