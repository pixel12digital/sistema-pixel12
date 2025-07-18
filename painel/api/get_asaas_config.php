<?php
/**
 * Endpoint para obter a configuração atual da API do Asaas
 */

header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');

require_once '../config.php';

try {
    if (defined('ASAAS_API_KEY')) {
        $chave = ASAAS_API_KEY;
        $tipo = strpos($chave, '_test_') !== false ? 'test' : 'prod';
        
        echo json_encode([
            'success' => true,
            'chave' => $chave,
            'tipo' => $tipo,
            'url' => ASAAS_API_URL
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'error' => 'Chave da API não está definida'
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Erro ao obter configuração: ' . $e->getMessage()
    ]);
}
?> 