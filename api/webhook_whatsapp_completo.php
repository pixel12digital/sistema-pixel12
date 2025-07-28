<?php
/**
 * WEBHOOK COMPLETO PARA CAPTURAR TODAS AS MENSAGENS
 * Captura mensagens recebidas E enviadas para o número 47 996164699
 */

header('Content-Type: application/json');
require_once '../config.php';
require_once '../painel/db.php';

class WebhookCompleto {
    private $mysqli;
    private $log_file;
    private $meu_numero = '4797146908'; // Número conectado no sistema
    private $meu_numero_completo = '554797146908'; // Número completo com código do país
    
    public function __construct($mysqli) {
        $this->mysqli = $mysqli;
        $this->log_file = '../logs/webhook_completo_' . date('Y-m-d') . '.log';
    }
    
    public function log($message) {
        $timestamp = date('Y-m-d H:i:s');
        $log_entry = "[$timestamp] $message" . PHP_EOL;
        file_put_contents($this->log_file, $log_entry, FILE_APPEND);
    }
    
    /**
     * Processa mensagem recebida via webhook
     */
    public function processarMensagemRecebida($data) {
        try {
            $this->log("📥 Processando mensagem recebida...");
            
            // Extrair dados da mensagem
            $numero_remetente = $data['entry'][0]['changes'][0]['value']['messages'][0]['from'] ?? '';
            $texto = $data['entry'][0]['changes'][0]['value']['messages'][0]['text']['body'] ?? '';
            $timestamp = $data['entry'][0]['changes'][0]['value']['messages'][0]['timestamp'] ?? time();
            $message_id = $data['entry'][0]['changes'][0]['value']['messages'][0]['id'] ?? '';
            
            // Converter timestamp para data/hora
            $data_hora = date('Y-m-d H:i:s', $timestamp);
            
            // Buscar cliente pelo número
            $cliente_id = $this->buscarClientePorNumero($numero_remetente);
            
            if (!$cliente_id) {
                // Criar cliente se não existir
                $cliente_id = $this->criarCliente($numero_remetente);
            }
            
            // Salvar mensagem recebida
            $sql = "INSERT INTO mensagens_comunicacao 
                    (canal_id, cliente_id, mensagem, tipo, data_hora, direcao, status, numero_whatsapp, numero_remetente, message_id) 
                    VALUES (36, ?, ?, 'texto', ?, 'recebido', 'recebido', ?, ?, ?)";
            
            $stmt = $this->mysqli->prepare($sql);
            $stmt->bind_param('isssss', $cliente_id, $texto, $data_hora, $numero_remetente, $this->meu_numero_completo, $message_id);
            
            if ($stmt->execute()) {
                $mensagem_id = $this->mysqli->insert_id;
                $this->log("✅ Mensagem recebida salva - ID: $mensagem_id - De: $numero_remetente - Texto: $texto");
                
                // Processar resposta automática se necessário
                $this->processarRespostaAutomatica($cliente_id, $texto);
                
                return $mensagem_id;
            } else {
                $this->log("❌ Erro ao salvar mensagem recebida: " . $this->mysqli->error);
                return false;
            }
            
        } catch (Exception $e) {
            $this->log("❌ Erro ao processar mensagem recebida: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Processa mensagem enviada (status update)
     */
    public function processarMensagemEnviada($data) {
        try {
            $this->log("📤 Processando status de mensagem enviada...");
            
            $status = $data['entry'][0]['changes'][0]['value']['statuses'][0]['status'] ?? '';
            $message_id = $data['entry'][0]['changes'][0]['value']['statuses'][0]['id'] ?? '';
            $timestamp = $data['entry'][0]['changes'][0]['value']['statuses'][0]['timestamp'] ?? time();
            
            // Atualizar status da mensagem
            $sql = "UPDATE mensagens_comunicacao 
                    SET status = ? 
                    WHERE message_id = ? AND direcao = 'enviado'";
            
            $stmt = $this->mysqli->prepare($sql);
            $stmt->bind_param('ss', $status, $message_id);
            
            if ($stmt->execute()) {
                $this->log("✅ Status atualizado - Message ID: $message_id - Status: $status");
                return true;
            } else {
                $this->log("❌ Erro ao atualizar status: " . $this->mysqli->error);
                return false;
            }
            
        } catch (Exception $e) {
            $this->log("❌ Erro ao processar status: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Busca cliente pelo número de telefone
     */
    private function buscarClientePorNumero($numero) {
        // Remover código do país se presente
        $numero_limpo = preg_replace('/^55/', '', $numero);
        
        $sql = "SELECT id FROM clientes WHERE numero_whatsapp LIKE ? OR numero_whatsapp LIKE ?";
        $stmt = $this->mysqli->prepare($sql);
        $numero_completo = "55$numero_limpo";
        $stmt->bind_param('ss', $numero_limpo, $numero_completo);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            return $row['id'];
        }
        
        return false;
    }
    
    /**
     * Cria novo cliente se não existir
     */
    private function criarCliente($numero) {
        $numero_limpo = preg_replace('/^55/', '', $numero);
        $nome = "Cliente $numero_limpo";
        
        $sql = "INSERT INTO clientes (nome, numero_whatsapp, data_cadastro) VALUES (?, ?, NOW())";
        $stmt = $this->mysqli->prepare($sql);
        $numero_completo = "55$numero_limpo";
        $stmt->bind_param('ss', $nome, $numero_completo);
        
        if ($stmt->execute()) {
            $cliente_id = $this->mysqli->insert_id;
            $this->log("✅ Novo cliente criado - ID: $cliente_id - Número: $numero_completo");
            return $cliente_id;
        } else {
            $this->log("❌ Erro ao criar cliente: " . $this->mysqli->error);
            return false;
        }
    }
    
    /**
     * Processa resposta automática
     */
    private function processarRespostaAutomatica($cliente_id, $mensagem_recebida) {
        // Aqui você pode implementar lógica de IA ou respostas automáticas
        $this->log("🤖 Processando resposta automática para cliente $cliente_id");
    }
    
    /**
     * Processa webhook completo
     */
    public function processarWebhook($data) {
        $this->log("🔄 Processando webhook...");
        
        try {
            $object = $data['object'] ?? '';
            $entry = $data['entry'] ?? [];
            
            if ($object !== 'whatsapp_business_account') {
                $this->log("❌ Objeto inválido: $object");
                return false;
            }
            
            foreach ($entry as $item) {
                $changes = $item['changes'] ?? [];
                
                foreach ($changes as $change) {
                    $value = $change['value'] ?? [];
                    
                    // Verificar se é mensagem recebida
                    if (isset($value['messages'])) {
                        $this->processarMensagemRecebida($data);
                    }
                    
                    // Verificar se é status de mensagem enviada
                    if (isset($value['statuses'])) {
                        $this->processarMensagemEnviada($data);
                    }
                }
            }
            
            $this->log("✅ Webhook processado com sucesso");
            return true;
            
        } catch (Exception $e) {
            $this->log("❌ Erro ao processar webhook: " . $e->getMessage());
            return false;
        }
    }
}

// Processar webhook
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    $webhook = new WebhookCompleto($mysqli);
    $resultado = $webhook->processarWebhook($data);
    
    if ($resultado) {
        http_response_code(200);
        echo json_encode(['status' => 'success']);
    } else {
        http_response_code(500);
        echo json_encode(['status' => 'error']);
    }
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
}
?> 