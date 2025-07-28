<?php
/**
 * SISTEMA DE RETRY PARA WEBHOOK
 * 
 * Verifica e reprocessa mensagens perdidas
 */

require_once 'config.php';
require_once 'painel/db.php';

class WebhookRetrySystem {
    private $mysqli;
    private $log_file;
    
    public function __construct($mysqli) {
        $this->mysqli = $mysqli;
        $this->log_file = 'logs/webhook_retry_' . date('Y-m-d') . '.log';
    }
    
    public function log($message) {
        $timestamp = date('Y-m-d H:i:s');
        $log_entry = "[$timestamp] $message" . PHP_EOL;
        file_put_contents($this->log_file, $log_entry, FILE_APPEND);
    }
    
    public function checkForMissingMessages() {
        // Verificar mensagens dos últimos 30 minutos que podem ter sido perdidas
        $sql = "SELECT mc.*, c.nome as cliente_nome, c.celular
                FROM mensagens_comunicacao mc
                LEFT JOIN clientes c ON mc.cliente_id = c.id
                WHERE mc.data_hora >= DATE_SUB(NOW(), INTERVAL 30 MINUTE)
                AND mc.numero_whatsapp IS NOT NULL
                ORDER BY mc.data_hora DESC";
        
        $result = $this->mysqli->query($sql);
        $messages = [];
        
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $messages[] = $row;
            }
        }
        
        $this->log("Verificadas " . count($messages) . " mensagens dos últimos 30 minutos");
        return $messages;
    }
    
    public function reprocessMessage($message) {
        // Simular reprocessamento da mensagem
        $numero = $message['numero_whatsapp'];
        $texto = $message['mensagem'];
        $cliente_id = $message['cliente_id'];
        
        $this->log("Reprocessando mensagem: $numero - $texto");
        
        // Aqui você pode adicionar lógica específica de reprocessamento
        // Por exemplo, reenviar resposta automática se necessário
        
        return true;
    }
    
    public function run() {
        $this->log("Iniciando verificação de mensagens perdidas");
        
        $messages = $this->checkForMissingMessages();
        
        foreach ($messages as $message) {
            // Verificar se a mensagem precisa de reprocessamento
            if ($this->needsReprocessing($message)) {
                $this->reprocessMessage($message);
            }
        }
        
        $this->log("Verificação concluída");
    }
    
    private function needsReprocessing($message) {
        // Lógica para determinar se uma mensagem precisa de reprocessamento
        // Por exemplo, mensagens sem resposta automática
        return false; // Implementar conforme necessário
    }
}

// Executar se chamado diretamente
if (basename(__FILE__) == basename($_SERVER['SCRIPT_NAME'])) {
    $retry = new WebhookRetrySystem($mysqli);
    $retry->run();
}
?>