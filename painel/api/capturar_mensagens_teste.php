<?php
/**
 * CAPTURADOR DE MENSAGENS DE TESTE
 * Número de teste: 47 9961-6469 (Charles Dietrich)
 * Número conectado: 47 9714-6908
 */

header('Content-Type: application/json');
require_once __DIR__ . '/../../config.php';
require_once '../db.php';

class CapturadorMensagensTeste {
    private $mysqli;
    private $log_file;
    private $meu_numero = '4797146908'; // Número conectado no sistema
    private $numero_teste = '47996164699'; // Número de teste
    private $cliente_id = 4296; // Charles Dietrich
    private $cliente_numero = '554796164699';
    
    public function __construct($mysqli) {
        $this->mysqli = $mysqli;
        $this->log_file = '../logs/captura_teste_' . date('Y-m-d') . '.log';
    }
    
    public function log($message) {
        $timestamp = date('Y-m-d H:i:s');
        $log_entry = "[$timestamp] $message" . PHP_EOL;
        file_put_contents($this->log_file, $log_entry, FILE_APPEND);
    }
    
    /**
     * Captura mensagens enviadas pelo número de teste
     */
    public function capturarMensagensTeste() {
        try {
            $this->log("🚀 Iniciando captura de mensagens de teste...");
            
            // Mensagens enviadas pelo número de teste (47 9961-6469)
            $mensagens_teste = [
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
            
            foreach ($mensagens_teste as $msg) {
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
                                          '{$this->cliente_numero}', '{$this->numero_teste}')";
                    
                    if ($this->mysqli->query($sql_insert)) {
                        $mensagem_id = $this->mysqli->insert_id;
                        $this->log("✅ Mensagem de teste capturada - ID: $mensagem_id - Texto: {$msg['texto']} - Hora: {$msg['data_hora']}");
                        $mensagens_capturadas++;
                    } else {
                        $this->log("❌ Erro ao salvar mensagem de teste: " . $this->mysqli->error);
                    }
                } else {
                    $this->log("ℹ️ Mensagem de teste já existe: {$msg['texto']} - {$msg['data_hora']}");
                    $mensagens_ja_existentes++;
                }
            }
            
            $this->log("✅ Captura de teste concluída: $mensagens_capturadas novas, $mensagens_ja_existentes já existentes");
            
            return [
                'success' => true,
                'mensagens_capturadas' => $mensagens_capturadas,
                'mensagens_ja_existentes' => $mensagens_ja_existentes,
                'total_verificadas' => count($mensagens_teste),
                'numero_teste' => $this->numero_teste,
                'numero_sistema' => $this->meu_numero
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
     * Adiciona uma nova mensagem de teste
     */
    public function adicionarMensagemTeste($texto, $data_hora = null) {
        if (!$data_hora) {
            $data_hora = date('Y-m-d H:i:s');
        }
        
        $sql = "INSERT INTO mensagens_comunicacao 
                (canal_id, cliente_id, mensagem, tipo, data_hora, direcao, status, numero_whatsapp, numero_remetente) 
                VALUES (36, {$this->cliente_id}, '" . $this->mysqli->real_escape_string($texto) . "', 
                        'texto', '$data_hora', 'enviado', 'enviado', 
                        '{$this->cliente_numero}', '{$this->numero_teste}')";
        
        if ($this->mysqli->query($sql)) {
            $mensagem_id = $this->mysqli->insert_id;
            $this->log("✅ Nova mensagem de teste adicionada - ID: $mensagem_id - Texto: $texto");
            return $mensagem_id;
        } else {
            $this->log("❌ Erro ao adicionar mensagem de teste: " . $this->mysqli->error);
            return false;
        }
    }
}

// Executar captura
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $capturador = new CapturadorMensagensTeste($mysqli);
    
    // Se foi enviada uma nova mensagem
    if (isset($_POST['nova_mensagem'])) {
        $texto = $_POST['nova_mensagem'];
        $data_hora = $_POST['data_hora'] ?? date('Y-m-d H:i:s');
        
        $resultado = $capturador->adicionarMensagemTeste($texto, $data_hora);
        
        echo json_encode([
            'success' => $resultado !== false,
            'mensagem_id' => $resultado,
            'texto' => $texto,
            'data_hora' => $data_hora,
            'numero_teste' => '47996164699'
        ]);
    } else {
        // Capturar todas as mensagens de teste
        $resultado = $capturador->capturarMensagensTeste();
        echo json_encode($resultado);
    }
} else {
    echo json_encode([
        'success' => false,
        'error' => 'Método não permitido'
    ]);
}
?> 