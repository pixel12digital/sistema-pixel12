<?php
/**
 * MONITOR AUTOMÁTICO DE MENSAGENS ENVIADAS PELO WHATSAPP WEB
 * 
 * Este script executa periodicamente para capturar mensagens que foram
 * enviadas pelo WhatsApp Web mas não estão registradas no sistema
 */

require_once '../config.php';
require_once 'db.php';
require_once 'capturar_mensagens_whatsapp_web.php';

class MonitorAutomaticoMensagensEnviadas {
    private $mysqli;
    private $log_file;
    private $capturador;
    
    public function __construct($mysqli) {
        $this->mysqli = $mysqli;
        $this->log_file = '../logs/monitor_automatico_mensagens_' . date('Y-m-d') . '.log';
        $this->capturador = new CapturadorMensagensWhatsAppWeb($mysqli);
    }
    
    public function log($message) {
        $timestamp = date('Y-m-d H:i:s');
        $log_entry = "[$timestamp] $message" . PHP_EOL;
        file_put_contents($this->log_file, $log_entry, FILE_APPEND);
    }
    
    /**
     * Executa o monitoramento automático
     */
    public function executar() {
        try {
            $this->log("🚀 Iniciando monitoramento automático de mensagens enviadas...");
            
            // Verificar se o sistema está ativo
            if (!$this->verificarSistemaAtivo()) {
                $this->log("⚠️ Sistema inativo - pulando monitoramento");
                return [
                    'success' => false,
                    'error' => 'Sistema inativo'
                ];
            }
            
            // Capturar mensagens enviadas
            $resultado_captura = $this->capturador->capturarMensagensEnviadas();
            
            // Sincronizar em tempo real
            $resultado_sincronizacao = $this->capturador->sincronizarTempoReal();
            
            // Verificar mensagens perdidas
            $resultado_verificacao = $this->verificarMensagensPerdidas();
            
            // Atualizar estatísticas
            $this->atualizarEstatisticas($resultado_captura, $resultado_sincronizacao, $resultado_verificacao);
            
            $this->log("✅ Monitoramento concluído com sucesso");
            
            return [
                'success' => true,
                'captura' => $resultado_captura,
                'sincronizacao' => $resultado_sincronizacao,
                'verificacao' => $resultado_verificacao
            ];
            
        } catch (Exception $e) {
            $this->log("❌ Erro no monitoramento: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Verifica se o sistema está ativo
     */
    private function verificarSistemaAtivo() {
        // Verificar se há conexão com o banco
        if (!$this->mysqli->ping()) {
            $this->log("❌ Sem conexão com o banco de dados");
            return false;
        }
        
        // Verificar se o WhatsApp está conectado
        $sql = "SELECT valor FROM configuracoes WHERE chave = 'whatsapp_status'";
        $result = $this->mysqli->query($sql);
        
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            if ($row['valor'] !== 'connected') {
                $this->log("⚠️ WhatsApp não está conectado: " . $row['valor']);
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Verifica mensagens perdidas
     */
    private function verificarMensagensPerdidas() {
        try {
            $this->log("🔍 Verificando mensagens perdidas...");
            
            // Buscar mensagens recebidas sem resposta nas últimas 24 horas
            $sql = "SELECT COUNT(*) as total_sem_resposta
                    FROM mensagens_comunicacao m1
                    WHERE m1.direcao = 'recebido'
                    AND m1.data_hora >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
                    AND NOT EXISTS (
                        SELECT 1 FROM mensagens_comunicacao m2
                        WHERE m2.cliente_id = m1.cliente_id
                        AND m2.direcao = 'enviado'
                        AND m2.data_hora > m1.data_hora
                        AND m2.data_hora <= DATE_ADD(m1.data_hora, INTERVAL 2 HOUR)
                    )";
            
            $result = $this->mysqli->query($sql);
            $row = $result->fetch_assoc();
            $mensagens_sem_resposta = $row['total_sem_resposta'];
            
            $this->log("📊 Encontradas $mensagens_sem_resposta mensagens sem resposta nas últimas 24h");
            
            return [
                'success' => true,
                'mensagens_sem_resposta' => $mensagens_sem_resposta
            ];
            
        } catch (Exception $e) {
            $this->log("❌ Erro ao verificar mensagens perdidas: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Atualiza estatísticas do monitoramento
     */
    private function atualizarEstatisticas($captura, $sincronizacao, $verificacao) {
        try {
            $agora = date('Y-m-d H:i:s');
            
            // Salvar estatísticas
            $sql = "INSERT INTO configuracoes (chave, valor, data_atualizacao) VALUES 
                    ('ultima_execucao_monitor_mensagens', '$agora', NOW()),
                    ('mensagens_capturadas_hoje', '" . ($captura['mensagens_capturadas'] ?? 0) . "', NOW()),
                    ('mensagens_sincronizadas_hoje', '" . ($sincronizacao['novas_mensagens'] ?? 0) . "', NOW()),
                    ('mensagens_sem_resposta_24h', '" . ($verificacao['mensagens_sem_resposta'] ?? 0) . "', NOW())
                    ON DUPLICATE KEY UPDATE 
                    valor = VALUES(valor), 
                    data_atualizacao = NOW()";
            
            $this->mysqli->query($sql);
            
            $this->log("📈 Estatísticas atualizadas");
            
        } catch (Exception $e) {
            $this->log("❌ Erro ao atualizar estatísticas: " . $e->getMessage());
        }
    }
    
    /**
     * Executa monitoramento em loop (para uso via cron)
     */
    public function executarLoop($intervalo_segundos = 300) { // 5 minutos por padrão
        $this->log("🔄 Iniciando monitoramento em loop (intervalo: {$intervalo_segundos}s)");
        
        while (true) {
            try {
                $resultado = $this->executar();
                
                if ($resultado['success']) {
                    $this->log("✅ Ciclo executado com sucesso");
                } else {
                    $this->log("❌ Erro no ciclo: " . ($resultado['error'] ?? 'Erro desconhecido'));
                }
                
            } catch (Exception $e) {
                $this->log("❌ Exceção no ciclo: " . $e->getMessage());
            }
            
            // Aguardar próximo ciclo
            sleep($intervalo_segundos);
        }
    }
}

// Executar monitoramento
if (php_sapi_name() === 'cli') {
    // Execução via linha de comando
    $monitor = new MonitorAutomaticoMensagensEnviadas($mysqli);
    
    if (isset($argv[1]) && $argv[1] === 'loop') {
        $intervalo = isset($argv[2]) ? intval($argv[2]) : 300;
        $monitor->executarLoop($intervalo);
    } else {
        $resultado = $monitor->executar();
        echo json_encode($resultado, JSON_PRETTY_PRINT) . "\n";
    }
} else {
    // Execução via web
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $monitor = new MonitorAutomaticoMensagensEnviadas($mysqli);
        $resultado = $monitor->executar();
        
        header('Content-Type: application/json');
        echo json_encode($resultado);
    } else {
        echo json_encode([
            'success' => false,
            'error' => 'Método não permitido'
        ]);
    }
}
?> 