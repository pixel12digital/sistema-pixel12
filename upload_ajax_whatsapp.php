<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json; charset=utf-8');

// Verificar se é uma requisição POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Método não permitido']);
    exit;
}

// Verificar se os dados necessários foram enviados
if (!isset($_POST['arquivo']) || !isset($_POST['nome_arquivo'])) {
    echo json_encode(['success' => false, 'error' => 'Dados incompletos']);
    exit;
}

try {
    // Decodificar o arquivo
    $conteudo = base64_decode($_POST['arquivo']);
    $nome_arquivo = $_POST['nome_arquivo'];
    
    // Verificar se o conteúdo é válido
    if (empty($conteudo)) {
        echo json_encode(['success' => false, 'error' => 'Conteúdo do arquivo inválido']);
        exit;
    }
    
    // Caminho do arquivo
    $caminho_arquivo = __DIR__ . '/painel/' . $nome_arquivo;
    
    // Fazer backup do arquivo atual
    if (file_exists($caminho_arquivo)) {
        $backup_path = $caminho_arquivo . '.backup.' . date('Y-m-d_H-i-s');
        copy($caminho_arquivo, $backup_path);
    }
    
    // Salvar o novo arquivo
    $resultado = file_put_contents($caminho_arquivo, $conteudo);
    
    if ($resultado === false) {
        echo json_encode(['success' => false, 'error' => 'Erro ao salvar arquivo']);
        exit;
    }
    
    // Verificar se o arquivo foi salvo corretamente
    $conteudo_salvo = file_get_contents($caminho_arquivo);
    if (strpos($conteudo_salvo, '$status_endpoint = "/status"') === false) {
        echo json_encode(['success' => false, 'error' => 'Arquivo não foi atualizado corretamente']);
        exit;
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Arquivo atualizado com sucesso',
        'arquivo' => $nome_arquivo,
        'tamanho' => strlen($conteudo),
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Erro: ' . $e->getMessage()]);
}
?> 