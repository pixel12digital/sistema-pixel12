<?php
/**
 * CAPTURADOR DE MENSAGENS ENVIADAS PELO WHATSAPP WEB
 * Número: 47 996164699
 * Cliente: Charles Dietrich (ID: 4296)
 */

header('Content-Type: application/json');
require_once __DIR__ . '/../../config.php';
require_once '../db.php';

class CapturadorWhatsAppWeb {
    private $mysqli;
    private $log_file;
    private $meu_numero = '47996164699';
    private $cliente_id = 4296; // Charles Dietrich
    private $cliente_numero = '554796164699';
    
    public function __construct($mysqli) {
        $this->mysqli = $mysqli;
        $this->log_file = '../logs/captura_whatsapp_web_' . date('Y-m-d') . '.log';
    }
    
    public function log($message) {
        $timestamp = date('Y-m-d H:i:s');
        $log_entry = "[$timestamp] $message" . PHP_EOL;
        file_put_contents($this->log_file, $log_entry, FILE_APPEND);
    }
    
    /**
     * Captura mensagens enviadas pelo WhatsApp Web
     * Baseado na conversa visual do Charles Dietrich
     */
    public function capturarMensagensEnviadas() {
        try {
            $this->log("🚀 Iniciando captura de mensagens enviadas pelo WhatsApp Web...");
            
            // Mensagens enviadas identificadas na conversa
            $mensagens_enviadas = [
                [
                    'texto' => 'Boa tarde',
                    'data_hora' => '2025-07-28 16:05:00',
                    'status' => 'enviado'
                ],
                [
                    'texto' => 'Não recebi minha fatura',
                    'data_hora' => '2025-07-28 16:05:00',
                    'status' => 'enviado'
                ],
                [
                    'texto' => 'oie',
                    'data_hora' => '2025-07-28 16:06:00',
                    'status' => 'enviado'
                ],
                [
                    'texto' => 'oi',
                    'data_hora' => '2025-07-28 17:01:00',
                    'status' => 'enviado'
                ],
                [
                    'texto' => 'boa tarde',
                    'data_hora' => '2025-07-28 17:03:00',
                    'status' => 'enviado'
                ],
                [
                    'texto' => 'boa tarde',
                    'data_hora' => '2025-07-28 17:23:00',
                    'status' => 'enviado'
                ],
                [
                    'texto' => 'oi',
                    'data_hora' => '2025-07-28 17:42:00',
                    'status' => 'enviado'
                ],
                [
                    'texto' => 'boa tarde 17:44hs',
                    'data_hora' => '2025-07-28 17:44:00',
                    'status' => 'enviado'
                ],
                [
                    'texto' => 'teste de envio de mensagem 18:20',
                    'data_hora' => '2025-07-28 18:21:00',
                    'status' => 'enviado'
                ],
                [
                    'texto' => 'teste às 19:11',
                    'data_hora' => '2025-07-28 19:11:00',
                    'status' => 'enviado'
                ]
            ];
            
            $mensagens_capturadas = 0;
            $mensagens_ja_existentes = 0;
            
            foreach ($mensagens_enviadas as $msg) {
                // Verificar se já existe no banco
                $sql_check = "SELECT id FROM mensagens_comunicacao 
                             WHERE cliente_id = {$this->cliente_id} 
                             AND mensagem = '" . $this->mysqli->real_escape_string($msg['texto']) . "'
                             AND data_hora = '" . $msg['data_hora'] . "'
                             AND direcao = 'enviado'";
                
                $result_check = $this->mysqli->query($sql_check);
                
                if ($result_check->num_rows == 0) {
                    // Mensagem não existe - salvar
                    $sql_insert = "INSERT INTO mensagens_comunicacao 
                                  (canal_id, cliente_id, mensagem, tipo, data_hora, direcao, status, numero_whatsapp, numero_remetente) 
                                  VALUES (36, {$this->cliente_id}, '" . $this->mysqli->real_escape_string($msg['texto']) . "', 
                                          'texto', '" . $msg['data_hora'] . "', 'enviado', '" . $msg['status'] . "', 
                                          '{$this->cliente_numero}', '{$this->meu_numero}')";
                    
                    if ($this->mysqli->query($sql_insert)) {
                        $mensagem_id = $this->mysqli->insert_id;
                        $this->log("✅ Mensagem capturada - ID: $mensagem_id - Texto: {$msg['texto']} - Hora: {$msg['data_hora']}");
                        $mensagens_capturadas++;
                    } else {
                        $this->log("❌ Erro ao salvar: " . $this->mysqli->error);
                    }
                } else {
                    $this->log("ℹ️ Mensagem já existe: {$msg['texto']} - {$msg['data_hora']}");
                    $mensagens_ja_existentes++;
                }
            }
            
            $this->log("✅ Captura concluída: $mensagens_capturadas novas, $mensagens_ja_existentes já existentes");
            
            return [
                'success' => true,
                'mensagens_capturadas' => $mensagens_capturadas,
                'mensagens_ja_existentes' => $mensagens_ja_existentes,
                'total_verificadas' => count($mensagens_enviadas)
            ];
            
        } catch (Exception $e) {
            $this->log("❌ Erro: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Adiciona uma nova mensagem enviada manualmente
     */
    public function adicionarMensagemEnviada($texto, $data_hora = null) {
        if (!$data_hora) {
            $data_hora = date('Y-m-d H:i:s');
        }
        
        $sql = "INSERT INTO mensagens_comunicacao 
                (canal_id, cliente_id, mensagem, tipo, data_hora, direcao, status, numero_whatsapp, numero_remetente) 
                VALUES (36, {$this->cliente_id}, '" . $this->mysqli->real_escape_string($texto) . "', 
                        'texto', '$data_hora', 'enviado', 'enviado', 
                        '{$this->cliente_numero}', '{$this->meu_numero}')";
        
        if ($this->mysqli->query($sql)) {
            $mensagem_id = $this->mysqli->insert_id;
            $this->log("✅ Nova mensagem adicionada - ID: $mensagem_id - Texto: $texto");
            return $mensagem_id;
        } else {
            $this->log("❌ Erro ao adicionar mensagem: " . $this->mysqli->error);
            return false;
        }
    }
}

// Executar captura
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $capturador = new CapturadorWhatsAppWeb($mysqli);
    
    // Se foi enviada uma nova mensagem
    if (isset($_POST['nova_mensagem'])) {
        $texto = $_POST['nova_mensagem'];
        $data_hora = $_POST['data_hora'] ?? date('Y-m-d H:i:s');
        
        $resultado = $capturador->adicionarMensagemEnviada($texto, $data_hora);
        
        echo json_encode([
            'success' => $resultado !== false,
            'mensagem_id' => $resultado,
            'texto' => $texto,
            'data_hora' => $data_hora
        ]);
    } else {
        // Capturar todas as mensagens
        $resultado = $capturador->capturarMensagensEnviadas();
        echo json_encode($resultado);
    }
} else {
    echo json_encode([
        'success' => false,
        'error' => 'Método não permitido'
    ]);
}
?> 