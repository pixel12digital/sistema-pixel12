<?php
// Incluir configurações globais
require_once __DIR__ . '/../config.php';

// Conexão MySQL otimizada com pooling para evitar limite de conexões
static $mysqli = null;

if ($mysqli === null) {
    // Usar conexão persistente se configurado
    $host = DB_PERSISTENT ? 'p:' . DB_HOST : DB_HOST;
    
    $mysqli = new mysqli($host, DB_USER, DB_PASS, DB_NAME);
    if ($mysqli->connect_errno) {
        die('Erro ao conectar ao MySQL: ' . $mysqli->connect_error);
    }
    $mysqli->set_charset('utf8mb4');
    
    // Configurar timeout otimizado para evitar conexões órfãs
    $timeout = defined('DB_TIMEOUT') ? DB_TIMEOUT : 10;
    $mysqli->query("SET SESSION wait_timeout=$timeout");
    $mysqli->query("SET SESSION interactive_timeout=$timeout");
    
    // Configurações adicionais para otimização
    $mysqli->query("SET SESSION sql_mode='STRICT_TRANS_TABLES,NO_ZERO_DATE,NO_ZERO_IN_DATE,ERROR_FOR_DIVISION_BY_ZERO'");
    $mysqli->query("SET SESSION autocommit=1");
}

// Verificar se a conexão ainda está ativa com retry limitado
if (!$mysqli->ping()) {
    $max_retries = defined('DB_MAX_RETRIES') ? DB_MAX_RETRIES : 3;
    $retry_count = 0;
    
    while ($retry_count < $max_retries) {
        $mysqli->close();
        $host = DB_PERSISTENT ? 'p:' . DB_HOST : DB_HOST;
        
        $mysqli = new mysqli($host, DB_USER, DB_PASS, DB_NAME);
        if (!$mysqli->connect_errno && $mysqli->ping()) {
            $mysqli->set_charset('utf8mb4');
            $timeout = defined('DB_TIMEOUT') ? DB_TIMEOUT : 10;
            $mysqli->query("SET SESSION wait_timeout=$timeout");
            $mysqli->query("SET SESSION interactive_timeout=$timeout");
            break;
        }
        $retry_count++;
        if ($retry_count < $max_retries) {
            sleep(1); // Aguardar 1 segundo antes de tentar novamente
        }
    }
    
    if ($retry_count >= $max_retries) {
        die('Erro ao reconectar ao MySQL após ' . $max_retries . ' tentativas');
    }
} 