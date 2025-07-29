<?php
// Incluir configurações globais
require_once __DIR__ . '/../config.php';

/**
 * Gerenciador de Conexões MySQL com Pool e Controle de Limite
 * Evita exceder max_connections_per_hour
 */
class DatabaseManager {
    private static $instance = null;
    private static $connections = [];
    private static $connectionCount = 0;
    private static $maxConnections;
    private static $lastConnectionTime = 0;
    private static $connectionInterval;
    
    private function __construct() {
        // Usar configurações do config.php
        self::$maxConnections = defined('DB_MAX_CONNECTIONS') ? DB_MAX_CONNECTIONS : 8;
        self::$connectionInterval = defined('DB_CONNECTION_INTERVAL') ? DB_CONNECTION_INTERVAL : 2;
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        // Verificar se já temos uma conexão ativa
        foreach (self::$connections as $conn) {
            if ($conn && $conn->ping()) {
                return $conn;
            }
        }
        
        // Limpar conexões mortas
        self::$connections = array_filter(self::$connections, function($conn) {
            return $conn && $conn->ping();
        });
        
        // Verificar limite de conexões simultâneas
        if (count(self::$connections) >= self::$maxConnections) {
            // Aguardar uma conexão ficar disponível
            $timeout = 30; // 30 segundos de timeout
            $start = time();
            
            while (count(self::$connections) >= self::$maxConnections && (time() - $start) < $timeout) {
                usleep(100000); // 0.1 segundo
                
                // Limpar conexões mortas novamente
                self::$connections = array_filter(self::$connections, function($conn) {
                    return $conn && $conn->ping();
                });
            }
            
            if (count(self::$connections) >= self::$maxConnections) {
                throw new Exception('Limite de conexões simultâneas atingido');
            }
        }
        
        // Controle de taxa de conexões (evitar muitas conexões por segundo)
        $now = time();
        if ($now - self::$lastConnectionTime < self::$connectionInterval) {
            sleep(self::$connectionInterval - ($now - self::$lastConnectionTime));
        }
        self::$lastConnectionTime = $now;
        
        // Criar nova conexão
        try {
            $host = DB_PERSISTENT ? 'p:' . DB_HOST : DB_HOST;
            $mysqli = new mysqli($host, DB_USER, DB_PASS, DB_NAME);
            
            if ($mysqli->connect_errno) {
                throw new Exception('Erro ao conectar ao MySQL: ' . $mysqli->connect_error);
            }
            
            // Configurações otimizadas
            $mysqli->set_charset('utf8mb4');
            $timeout = defined('DB_TIMEOUT') ? DB_TIMEOUT : 30;
            $mysqli->query("SET SESSION wait_timeout=$timeout");
            $mysqli->query("SET SESSION interactive_timeout=$timeout");
            $mysqli->query("SET SESSION sql_mode='STRICT_TRANS_TABLES,NO_ZERO_DATE,NO_ZERO_IN_DATE,ERROR_FOR_DIVISION_BY_ZERO'");
            $mysqli->query("SET SESSION autocommit=1");
            
            // Adicionar à pool
            self::$connections[] = $mysqli;
            self::$connectionCount++;
            
            return $mysqli;
            
        } catch (Exception $e) {
            error_log("Erro ao criar conexão MySQL: " . $e->getMessage());
            throw $e;
        }
    }
    
    public function closeConnection($mysqli) {
        // Remover da pool
        $key = array_search($mysqli, self::$connections);
        if ($key !== false) {
            unset(self::$connections[$key]);
            self::$connections = array_values(self::$connections); // Reindexar
        }
        
        // Fechar conexão
        if ($mysqli && $mysqli->ping()) {
            $mysqli->close();
        }
    }
    
    public function getStats() {
        return [
            'active_connections' => count(self::$connections),
            'total_connections' => self::$connectionCount,
            'max_connections' => self::$maxConnections,
            'connection_interval' => self::$connectionInterval
        ];
    }
}

// Inicializar gerenciador
$dbManager = DatabaseManager::getInstance();

// Obter conexão
try {
    $mysqli = $dbManager->getConnection();
} catch (Exception $e) {
    // Em caso de erro de limite de conexões, mostrar página de manutenção
    if (strpos($e->getMessage(), 'max_connections_per_hour') !== false) {
        http_response_code(503);
        echo '<!DOCTYPE html>
        <html>
        <head>
            <title>Sistema Temporariamente Indisponível</title>
            <meta charset="utf-8">
            <style>
                body { font-family: Arial, sans-serif; text-align: center; padding: 50px; background: #f5f5f5; }
                .container { background: white; padding: 40px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); max-width: 500px; margin: 0 auto; }
                .icon { font-size: 48px; margin-bottom: 20px; }
                h1 { color: #e74c3c; margin-bottom: 20px; }
                p { color: #666; line-height: 1.6; }
                .retry { background: #3498db; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; margin-top: 20px; }
                .retry:hover { background: #2980b9; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="icon">⚠️</div>
                <h1>Sistema Temporariamente Indisponível</h1>
                <p>O sistema está temporariamente indisponível devido ao alto volume de acessos.</p>
                <p>Por favor, aguarde alguns minutos e tente novamente.</p>
                <button class="retry" onclick="window.location.reload()">🔄 Tentar Novamente</button>
            </div>
            <script>
                // Tentar recarregar automaticamente após 2 minutos
                setTimeout(() => window.location.reload(), 120000);
            </script>
        </body>
        </html>';
        exit;
    } else {
        // Outros erros
        error_log("Erro de conexão MySQL: " . $e->getMessage());
        die('Erro de conexão com o banco de dados. Tente novamente em alguns minutos.');
    }
}

// Função para fechar conexão quando necessário
function closeDatabaseConnection() {
    global $mysqli, $dbManager;
    if ($mysqli && $dbManager) {
        $dbManager->closeConnection($mysqli);
    }
}

// Registrar função para fechar conexão ao finalizar script
register_shutdown_function('closeDatabaseConnection');
?> 