<?php
/**
 * MONITOR AUTOMÁTICO DE WHATSAPP
 * Verifica periodicamente se há novas mensagens para capturar
 */

header('Content-Type: application/json');
require_once '../config.php';
require_once 'db.php';

class MonitorWhatsAppAutomatico {
    private $mysqli;
    private $log_file;
    private $meu_numero = '4797146908'; // Número conectado no sistema
    
    public function __construct($mysqli) {
        $this->mysqli = $mysqli;
        $this->log_file = '../logs/monitor_automatico_' . date('Y-m-d') . '.log';
    }
    
    public function log($message) {
        $timestamp = date('Y-m-d H:i:s');
        $log_entry = "[$timestamp] $message" . PHP_EOL;
        file_put_contents($this->log_file, $log_entry, FILE_APPEND);
    }
    
    /**
     * Verifica se há novas mensagens para capturar
     */
    public function verificarNovasMensagens() {
        try {
            $this->log("🔍 Verificando novas mensagens...");
            
            // Verificar última sincronização
            $ultima_sincronizacao = $this->getUltimaSincronizacao();
            
            // Buscar mensagens mais recentes que a última sincronização
            $sql = "SELECT id, cliente_id, mensagem, data_hora, direcao, status 
                    FROM mensagens_comunicacao 
                    WHERE data_hora > ? 
                    AND numero_remetente = ? 
                    ORDER BY data_hora DESC";
            
            $stmt = $this->mysqli->prepare($sql);
            $stmt->bind_param('ss', $ultima_sincronizacao, $this->meu_numero);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $novas_mensagens = [];
            while ($row = $result->fetch_assoc()) {
                $novas_mensagens[] = $row;
            }
            
            $this->log("📊 Encontradas " . count($novas_mensagens) . " novas mensagens");
            
            // Atualizar última sincronização
            $this->atualizarUltimaSincronizacao();
            
            return [
                'success' => true,
                'novas_mensagens' => count($novas_mensagens),
                'mensagens' => $novas_mensagens,
                'ultima_sincronizacao' => $ultima_sincronizacao
            ];
            
        } catch (Exception $e) {
            $this->log("❌ Erro ao verificar mensagens: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Obtém a última sincronização
     */
    private function getUltimaSincronizacao() {
        $sql = "SELECT valor FROM configuracoes WHERE chave = 'ultima_sincronizacao_whatsapp'";
        $result = $this->mysqli->query($sql);
        
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            return $row['valor'];
        }
        
        // Se não existe, usar 1 hora atrás
        return date('Y-m-d H:i:s', strtotime('-1 hour'));
    }
    
    /**
     * Atualiza a última sincronização
     */
    private function atualizarUltimaSincronizacao() {
        $agora = date('Y-m-d H:i:s');
        
        $sql = "INSERT INTO configuracoes (chave, valor, data_atualizacao) 
                VALUES ('ultima_sincronizacao_whatsapp', ?, NOW()) 
                ON DUPLICATE KEY UPDATE valor = ?, data_atualizacao = NOW()";
        
        $stmt = $this->mysqli->prepare($sql);
        $stmt->bind_param('ss', $agora, $agora);
        $stmt->execute();
        
        $this->log("✅ Última sincronização atualizada: $agora");
    }
    
    /**
     * Captura mensagens pendentes do WhatsApp Web
     */
    public function capturarMensagensPendentes() {
        try {
            $this->log("📥 Capturando mensagens pendentes...");
            
            // Aqui você pode implementar a lógica para capturar mensagens
            // que foram enviadas pelo WhatsApp Web mas não estão no banco
            
            // Por enquanto, vamos simular algumas mensagens
            $mensagens_pendentes = [
                [
                    'cliente_id' => 4296,
                    'texto' => 'Nova mensagem teste ' . date('H:i:s'),
                    'data_hora' => date('Y-m-d H:i:s'),
                    'numero_whatsapp' => '554796164699'
                ]
            ];
            
            $capturadas = 0;
            foreach ($mensagens_pendentes as $msg) {
                $sql = "INSERT INTO mensagens_comunicacao 
                        (canal_id, cliente_id, mensagem, tipo, data_hora, direcao, status, numero_whatsapp, numero_remetente) 
                        VALUES (36, ?, ?, 'texto', ?, 'enviado', 'enviado', ?, ?)";
                
                $stmt = $this->mysqli->prepare($sql);
                $stmt->bind_param('issss', $msg['cliente_id'], $msg['texto'], $msg['data_hora'], $msg['numero_whatsapp'], $this->meu_numero);
                
                if ($stmt->execute()) {
                    $capturadas++;
                    $this->log("✅ Mensagem capturada: {$msg['texto']}");
                }
            }
            
            return [
                'success' => true,
                'mensagens_capturadas' => $capturadas
            ];
            
        } catch (Exception $e) {
            $this->log("❌ Erro ao capturar mensagens: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Executa monitoramento completo
     */
    public function executarMonitoramento() {
        $this->log("🚀 Iniciando monitoramento automático...");
        
        // Verificar novas mensagens
        $resultado_verificacao = $this->verificarNovasMensagens();
        
        // Capturar mensagens pendentes
        $resultado_captura = $this->capturarMensagensPendentes();
        
        $this->log("✅ Monitoramento concluído");
        
        return [
            'success' => true,
            'verificacao' => $resultado_verificacao,
            'captura' => $resultado_captura
        ];
    }
}

// Executar monitoramento
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $monitor = new MonitorWhatsAppAutomatico($mysqli);
    $resultado = $monitor->executarMonitoramento();
    
    echo json_encode($resultado);
} else {
    echo json_encode([
        'success' => false,
        'error' => 'Método não permitido'
    ]);
}
?> 