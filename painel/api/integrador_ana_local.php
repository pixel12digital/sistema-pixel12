<?php
/**
 * 🔗 INTEGRADOR ANA LOCAL - PIXEL12DIGITAL
 * 
 * Conecta com Ana via HTTP de forma eficiente
 * Corrigido para usar o sistema que funciona
 */

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../db.php';

header('Content-Type: application/json');

class IntegradorAnaLocal {
    
    private $mysqli_loja;
    private $ana_api_url = 'https://agentes.pixel12digital.com.br/ai-agents/api/chat/agent_chat.php';
    private $ana_agent_id = '3';
    
    public function __construct($mysqli_loja) {
        $this->mysqli_loja = $mysqli_loja;
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
            
            // 2. Chamar Ana via HTTP
            $resposta_ana = $this->chamarAnaHTTP($mensagem);
            
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
     * Chamar Ana via HTTP
     */
    private function chamarAnaHTTP($mensagem) {
        try {
            $payload = json_encode([
                'question' => $mensagem,
                'agent_id' => $this->ana_agent_id
            ]);
            
            $ch = curl_init($this->ana_api_url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Content-Length: ' . strlen($payload)
            ]);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 15);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            
            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($http_code === 200 && $response) {
                $data = json_decode($response, true);
                
                if (isset($data['success']) && $data['success'] && !empty($data['response'])) {
                    return ['success' => true, 'response' => $data['response']];
                }
            }
            
            return ['success' => false, 'error' => "HTTP $http_code - " . substr($response, 0, 100)];
            
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
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
            strpos($resposta_lower, 'desenvolvimento web') !== false ||
            strpos($resposta_lower, 'especialista em desenvolvimento web') !== false) {
            
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
        elseif (strpos($resposta_lower, 'financeira') !== false || strpos($resposta_lower, 'assistente financeira') !== false) {
            $analise['acao'] = 'departamento_identificado';
            $analise['departamento'] = 'FIN';
        }
        elseif (strpos($resposta_lower, 'suporte técnico') !== false || strpos($resposta_lower, 'assistente de suporte') !== false) {
            $analise['acao'] = 'departamento_identificado';
            $analise['departamento'] = 'SUP';
        }
        elseif (strpos($resposta_lower, 'comercial') !== false || strpos($resposta_lower, 'assistente comercial') !== false) {
            $analise['acao'] = 'departamento_identificado';
            $analise['departamento'] = 'COM';
        }
        elseif (strpos($resposta_lower, 'administrativa') !== false || strpos($resposta_lower, 'assistente administrativa') !== false) {
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
                // NOVO: Executar transferência automaticamente
                $this->executarTransferenciaImediata('rafael', $numero);
                break;
                
            case 'transfer_humano':
                $this->registrarTransferenciaHumano($numero, $mensagem, $analise['departamento']);
                // NOVO: Executar transferência automaticamente
                $this->executarTransferenciaImediata('humano', $numero);
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
        
        $stmt = $this->mysqli_loja->prepare($sql);
        if ($stmt) {
            $stmt->bind_param('ss', $numero, $mensagem);
            $stmt->execute();
            $stmt->close();
        }
        
        error_log("[INTEGRADOR_LOCAL] Transferência para Rafael registrada: $numero");
    }
    
    /**
     * Registrar transferência para humano
     */
    private function registrarTransferenciaHumano($numero, $mensagem, $departamento) {
        $sql = "INSERT INTO transferencias_humano (numero_cliente, mensagem_original, departamento, data_transferencia, status) 
                VALUES (?, ?, ?, NOW(), 'pendente')";
        
        $stmt = $this->mysqli_loja->prepare($sql);
        if ($stmt) {
            $stmt->bind_param('sss', $numero, $mensagem, $departamento);
            $stmt->execute();
            $stmt->close();
        }
        
        error_log("[INTEGRADOR_LOCAL] Transferência para humano registrada: $numero → $departamento");
    }
    
    /**
     * Registrar atendimento por departamento
     */
    private function registrarAtendimentoDepartamento($numero, $mensagem, $departamento) {
        $sql = "INSERT INTO atendimentos_ana (numero_cliente, mensagem, departamento, data_atendimento) 
                VALUES (?, ?, ?, NOW())";
        
        $stmt = $this->mysqli_loja->prepare($sql);
        if ($stmt) {
            $stmt->bind_param('sss', $numero, $mensagem, $departamento);
            $stmt->execute();
            $stmt->close();
        }
        
        error_log("[INTEGRADOR_LOCAL] Atendimento Ana registrado: $numero → $departamento");
    }
    
    /**
     * Fallback usando roteador local
     */
    private function fallbackRoteadorLocal($mensagem) {
        // Resposta simples de fallback
        $palavras_chave = [
            'site' => 'Para desenvolvimento de sites, vou transferir você para nosso especialista Rafael.',
            'financeiro' => 'Para questões financeiras, entre em contato: 47 97309525',
            'suporte' => 'Para suporte técnico, entre em contato: 47 97309525',
            'comercial' => 'Para questões comerciais, entre em contato: 47 97309525',
            'problema' => 'Para problemas técnicos, entre em contato: 47 97309525'
        ];
        
        $mensagem_lower = strtolower($mensagem);
        
        foreach ($palavras_chave as $palavra => $resposta) {
            if (strpos($mensagem_lower, $palavra) !== false) {
                return $resposta;
            }
        }
        
        return "Olá! Sou a Ana da Pixel12Digital. Como posso ajudá-lo hoje? 😊";
    }

    /**
     * NOVO: Executar transferência imediatamente
     */
    private function executarTransferenciaImediata($tipo, $numero_cliente) {
        try {
            error_log("[INTEGRADOR_LOCAL] Executando transferência imediata - Tipo: $tipo, Cliente: $numero_cliente");
            
            // Chamar executor de transferências
            require_once __DIR__ . '/executar_transferencias.php';
            
            $executor = new ExecutorTransferencias($this->mysqli_loja);
            $resultado = $executor->processarTransferenciasPendentes();
            
            if ($resultado['success']) {
                error_log("[INTEGRADOR_LOCAL] Transferência executada com sucesso");
                return true;
            } else {
                error_log("[INTEGRADOR_LOCAL] Erro na execução da transferência: " . json_encode($resultado['erros']));
                return false;
            }
            
        } catch (Exception $e) {
            error_log("[INTEGRADOR_LOCAL] Erro ao executar transferência: " . $e->getMessage());
            return false;
        }
    }
}

// ===== PROCESSAMENTO DA REQUISIÇÃO =====
// Este código só executa quando o arquivo é chamado diretamente via HTTP

if (basename(__FILE__) === basename($_SERVER['SCRIPT_NAME'] ?? '')) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $input = file_get_contents('php://input');
        $dados = json_decode($input, true);
        
        if ($dados) {
            $integrador = new IntegradorAnaLocal($mysqli);
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
            'tipo' => 'integração_http',
            'ana_agent_id' => '3',
            'versao' => '2.0 - Integração HTTP Ana + Sistema',
            'vantagens' => [
                'Usa sistema Ana que funciona',
                'Transferências automáticas',
                'Fallbacks inteligentes',
                'Controle total do sistema'
            ]
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        
    } else {
        echo json_encode(['success' => false, 'error' => 'Método não permitido']);
    }

    if (isset($mysqli)) {
        $mysqli->close();
    }
}
?> 