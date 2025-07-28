<?php
/**
 * CONEXÃO DE EMERGÊNCIA
 * 
 * Usado quando o banco principal atinge limite de conexões
 */

// Incluir configurações globais
require_once __DIR__ . '/../config.php';

// Configurações locais de emergência
$emergency_config = [
    'host' => 'localhost',
    'user' => 'root',
    'pass' => '',
    'db' => 'loja_virtual_revenda'
];

// Tentar conexão local primeiro
try {
    $mysqli = new mysqli(
        $emergency_config['host'],
        $emergency_config['user'],
        $emergency_config['pass'],
        $emergency_config['db']
    );
    
    if ($mysqli->connect_errno) {
        throw new Exception("Erro na conexão local: " . $mysqli->connect_error);
    }
    
    $mysqli->set_charset('utf8mb4');
    echo "✅ Conexão local estabelecida\n";
    
} catch (Exception $e) {
    // Se falhar, tentar conexão remota com retry
    echo "⚠️ Tentando conexão remota com retry...\n";
    
    $retry_count = 0;
    $max_retries = 3;
    
    while ($retry_count < $max_retries) {
        try {
            $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
            
            if (!$mysqli->connect_errno) {
                $mysqli->set_charset('utf8mb4');
                echo "✅ Conexão remota estabelecida na tentativa " . ($retry_count + 1) . "\n";
                break;
            }
            
        } catch (Exception $e) {
            $retry_count++;
            if ($retry_count < $max_retries) {
                sleep(2); // Aguardar 2 segundos antes de tentar novamente
            }
        }
    }
    
    if ($retry_count >= $max_retries) {
        die("❌ Não foi possível estabelecer conexão com o banco de dados após $max_retries tentativas");
    }
}

// Configurar timeout para evitar conexões órfãs
$mysqli->query("SET SESSION wait_timeout=300");
$mysqli->query("SET SESSION interactive_timeout=300");

// Função para verificar se a conexão ainda está ativa
function check_connection($mysqli) {
    if (!$mysqli->ping()) {
        echo "⚠️ Reconectando ao banco...\n";
        $mysqli->close();
        
        // Tentar reconectar
        $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if ($mysqli->connect_errno) {
            die("❌ Erro ao reconectar: " . $mysqli->connect_error);
        }
        $mysqli->set_charset('utf8mb4');
    }
    return $mysqli;
}

// Verificar conexão inicial
$mysqli = check_connection($mysqli);
?> 