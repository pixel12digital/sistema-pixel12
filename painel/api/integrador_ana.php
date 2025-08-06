<?php
/**
 * 🔗 INTEGRADOR ANA - PIXEL12DIGITAL
 * 
 * Conecta mensagens do WhatsApp Canal 3000 com Ana (agentes.pixel12digital.com.br)
 * e processa respostas inteligentemente
 */

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../db.php';

header('Content-Type: application/json');

class IntegradorAna {
    
    private $mysqli;
    private $ana_api_url = 'https://agentes.pixel12digital.com.br/api/chat/agent_chat.php';
    private $ana_agent_id = '3';
    
    public function __construct($mysqli) {
        $this->mysqli = $mysqli;
    }
    
    /**
     * Processar mensagem recebida do WhatsApp
     */
    public function processarMensagem($dados_whatsapp) {
        $resultado = [
            'success' => false,
            'resposta_ana' => '',
            'acao_sistema' => 'nenhuma',
            'departamento_detectado' => null,
            'transfer_para_rafael' => false,
            'transfer_para_humano' => false,
            'debug' => []
        ];
        
        try {
            // 1. Extrair dados da mensagem
            $mensagem = $dados_whatsapp['body'] ?? $dados_whatsapp['message'] ?? '';
            $numero = $dados_whatsapp['from'] ?? '';
            
            $resultado['debug'][] = "Mensagem recebida: " . substr($mensagem, 0, 50);
            
            // 2. Chamar Ana para obter resposta
            $resposta_ana = $this->chamarAna($mensagem);
            
            if ($resposta_ana['success']) {
                $resultado['success'] = true;
                $resultado['resposta_ana'] = $resposta_ana['response'];
                $resultado['debug'][] = "Ana respondeu com sucesso";
                
                // 3. Analisar resposta da Ana para detectar ações especiais
                $analise = $this->analisarRespostaAna($resposta_ana['response'], $mensagem);
                
                $resultado['acao_sistema'] = $analise['acao'];
                $resultado['departamento_detectado'] = $analise['departamento'];
                $resultado['transfer_para_rafael'] = $analise['transfer_rafael'];
                $resultado['transfer_para_humano'] = $analise['transfer_humano'];
                
                // 4. Executar ações específicas do sistema
                if ($analise['acao'] !== 'nenhuma') {
                    $this->executarAcaoSistema($analise, $numero, $mensagem);
                }
                
            } else {
                $resultado['debug'][] = "Erro ao chamar Ana: " . ($resposta_ana['error'] ?? 'Erro desconhecido');
                
                // Fallback - usar roteador local
                $resultado['resposta_ana'] = $this->fallbackRoteadorLocal($mensagem);
                $resultado['success'] = true;
                $resultado['acao_sistema'] = 'fallback_local';
            }
            
        } catch (Exception $e) {
            $resultado['debug'][] = "Erro no integrador: " . $e->getMessage();
            
            // Fallback de emergência
            $resultado['resposta_ana'] = "Olá! Sou a Ana da Pixel12Digital. No momento estou com uma instabilidade, mas em breve retorno. Para urgências, contate 47 97309525. 😊";
            $resultado['success'] = true;
            $resultado['acao_sistema'] = 'fallback_emergencia';
        }
        
        return $resultado;
    }
    
    /**
     * Chamar API da Ana
     */
    private function chamarAna($mensagem) {
        $payload = [
            'question' => $mensagem,
            'agent_id' => $this->ana_agent_id
        ];
        
        $ch = curl_init($this->ana_api_url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_error = curl_error($ch);
        curl_close($ch);
        
        if ($curl_error) {
            return ['success' => false, 'error' => $curl_error];
        }
        
        if ($http_code !== 200) {
            return ['success' => false, 'error' => "HTTP Code: $http_code"];
        }
        
        $data = json_decode($response, true);
        
        if (!$data || !isset($data['response'])) {
            return ['success' => false, 'error' => 'Resposta inválida da Ana'];
        }
        
        return ['success' => true, 'response' => $data['response']];
    }
    
    /**
     * Analisar resposta da Ana para detectar ações do sistema
     */
    private function analisarRespostaAna($resposta_ana, $mensagem_original) {
        $analise = [
            'acao' => 'nenhuma',
            'departamento' => null,
            'transfer_rafael' => false,
            'transfer_humano' => false
        ];
        
        $resposta_lower = strtolower($resposta_ana);
        
        // Detectar transferência para Rafael (sites/ecommerce)
        if (strpos($resposta_lower, 'rafael') !== false || 
            strpos($resposta_lower, 'transferir você para o rafael') !== false ||
            strpos($resposta_lower, 'desenvolvimento web') !== false) {
            
            $analise['acao'] = 'transfer_rafael';
            $analise['transfer_rafael'] = true;
            $analise['departamento'] = 'SITES';
        }
        
        // Detectar transferência para humanos
        elseif (strpos($resposta_lower, '47 97309525') !== false ||
                strpos($resposta_lower, 'equipe humana') !== false ||
                strpos($resposta_lower, 'atendimento humano') !== false) {
            
            $analise['acao'] = 'transfer_humano';
            $analise['transfer_humano'] = true;
            
            // Detectar departamento da transferência
            if (strpos($resposta_lower, 'financeira') !== false) $analise['departamento'] = 'FIN';
            elseif (strpos($resposta_lower, 'suporte') !== false) $analise['departamento'] = 'SUP';
            elseif (strpos($resposta_lower, 'comercial') !== false) $analise['departamento'] = 'COM';
            elseif (strpos($resposta_lower, 'administrativa') !== false) $analise['departamento'] = 'ADM';
        }
        
        // Detectar departamento sem transferência
        elseif (strpos($resposta_lower, 'financeira') !== false) {
            $analise['acao'] = 'departamento_identificado';
            $analise['departamento'] = 'FIN';
        }
        elseif (strpos($resposta_lower, 'suporte técnico') !== false) {
            $analise['acao'] = 'departamento_identificado';
            $analise['departamento'] = 'SUP';
        }
        elseif (strpos($resposta_lower, 'comercial') !== false) {
            $analise['acao'] = 'departamento_identificado';
            $analise['departamento'] = 'COM';
        }
        elseif (strpos($resposta_lower, 'administrativa') !== false) {
            $analise['acao'] = 'departamento_identificado';
            $analise['departamento'] = 'ADM';
        }
        
        return $analise;
    }
    
    /**
     * Executar ações específicas do sistema
     */
    private function executarAcaoSistema($analise, $numero, $mensagem) {
        switch ($analise['acao']) {
            case 'transfer_rafael':
                $this->registrarTransferenciaRafael($numero, $mensagem);
                break;
                
            case 'transfer_humano':
                $this->registrarTransferenciaHumano($numero, $mensagem, $analise['departamento']);
                break;
                
            case 'departamento_identificado':
                $this->registrarAtendimentoDepartamento($numero, $mensagem, $analise['departamento']);
                break;
        }
    }
    
    /**
     * Registrar transferência para Rafael
     */
    private function registrarTransferenciaRafael($numero, $mensagem) {
        $sql = "INSERT INTO transferencias_rafael (numero_cliente, mensagem_original, data_transferencia, status) 
                VALUES (?, ?, NOW(), 'pendente')";
        
        $stmt = $this->mysqli->prepare($sql);
        if ($stmt) {
            $stmt->bind_param('ss', $numero, $mensagem);
            $stmt->execute();
            $stmt->close();
        }
        
        error_log("[INTEGRADOR] Transferência para Rafael registrada: $numero");
    }
    
    /**
     * Registrar transferência para humano
     */
    private function registrarTransferenciaHumano($numero, $mensagem, $departamento) {
        $sql = "INSERT INTO transferencias_humano (numero_cliente, mensagem_original, departamento, data_transferencia, status) 
                VALUES (?, ?, ?, NOW(), 'pendente')";
        
        $stmt = $this->mysqli->prepare($sql);
        if ($stmt) {
            $stmt->bind_param('sss', $numero, $mensagem, $departamento);
            $stmt->execute();
            $stmt->close();
        }
        
        error_log("[INTEGRADOR] Transferência para humano registrada: $numero → $departamento");
    }
    
    /**
     * Registrar atendimento por departamento
     */
    private function registrarAtendimentoDepartamento($numero, $mensagem, $departamento) {
        $sql = "INSERT INTO atendimentos_ana (numero_cliente, mensagem, departamento, data_atendimento) 
                VALUES (?, ?, ?, NOW())";
        
        $stmt = $this->mysqli->prepare($sql);
        if ($stmt) {
            $stmt->bind_param('sss', $numero, $mensagem, $departamento);
            $stmt->execute();
            $stmt->close();
        }
        
        error_log("[INTEGRADOR] Atendimento Ana registrado: $numero → $departamento");
    }
    
    /**
     * Fallback usando roteador local
     */
    private function fallbackRoteadorLocal($mensagem) {
        require_once __DIR__ . '/roteador_departamentos.php';
        
        $roteador = new RoteadorDepartamentos($this->mysqli);
        $resultado = $roteador->processarMensagem(['body' => $mensagem, 'from' => 'fallback']);
        
        return $resultado['resposta_sugerida'] ?? 
               "Olá! Sou a Ana da Pixel12Digital. Como posso ajudá-lo hoje? 😊";
    }
}

// ===== PROCESSAMENTO DA REQUISIÇÃO =====

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = file_get_contents('php://input');
    $dados = json_decode($input, true);
    
    if ($dados) {
        $integrador = new IntegradorAna($mysqli);
        $resultado = $integrador->processarMensagem($dados);
        
        echo json_encode($resultado, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    } else {
        echo json_encode(['success' => false, 'error' => 'Dados inválidos']);
    }
    
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Status do integrador
    echo json_encode([
        'success' => true,
        'status' => 'ativo',
        'ana_api' => 'https://agentes.pixel12digital.com.br/api/chat/agent_chat.php',
        'agent_id' => '3',
        'versao' => '1.0 - Integração Ana + Sistema'
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    
} else {
    echo json_encode(['success' => false, 'error' => 'Método não permitido']);
}

$mysqli->close();
?> 