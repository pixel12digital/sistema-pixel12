<?php
/**
 * 🔗 INTEGRADOR ANA LOCAL - PIXEL12DIGITAL
 * 
 * Conecta com o sistema de agentes localmente (sem HTTP)
 * Muito mais eficiente e fácil de gerenciar
 */

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../db.php';

// Incluir sistema de agentes local
require_once __DIR__ . '/../../agentes/config/database.php';
require_once __DIR__ . '/../../agentes/api/ai/openai_client.php';

header('Content-Type: application/json');

class IntegradorAnaLocal {
    
    private $mysqli_loja;      // Banco da loja
    private $pdo_agentes;      // Banco dos agentes (PDO)
    private $ana_agent_id = '3';
    
    public function __construct($mysqli_loja) {
        $this->mysqli_loja = $mysqli_loja;
        
        // Conectar com banco dos agentes
        global $pdo;
        if (!$pdo) {
            throw new Exception('Erro ao conectar com banco de agentes');
        }
        $this->pdo_agentes = $pdo;
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
            
            // 2. Chamar Ana LOCALMENTE
            $resposta_ana = $this->chamarAnaLocal($mensagem);
            
            if ($resposta_ana['success']) {
                $resultado['success'] = true;
                $resultado['resposta_ana'] = $resposta_ana['response'];
                $resultado['debug'][] = "Ana local respondeu com sucesso";
                
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
     * Chamar Ana usando sistema local (sem HTTP)
     */
    private function chamarAnaLocal($mensagem) {
        try {
            // 1. Buscar dados da Ana no banco de agentes (incluindo use_custom_prompt)
            $stmt = $this->pdo_agentes->prepare("SELECT * FROM agents WHERE id = ? AND status = 'ativo'");
            $stmt->execute([$this->ana_agent_id]);
            $agent = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$agent) {
                return ['success' => false, 'error' => 'Ana não encontrada ou inativa'];
            }
            
            // 2. Garantir que use_custom_prompt está definido
            if (!isset($agent['use_custom_prompt'])) {
                $agent['use_custom_prompt'] = '1'; // Forçar uso do prompt personalizado
            }
            
            // 3. Usar cliente OpenAI diretamente
            $openai_client = new OpenAIClient();
            
            // 4. Gerar resposta (sem salvar no banco de agentes - só processar)
            $response = $openai_client->generateResponse($mensagem, $agent, []);
            
            return ['success' => true, 'response' => $response];
            
        } catch (Exception $e) {
            error_log("[INTEGRADOR_LOCAL] Erro ao chamar Ana: " . $e->getMessage());
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
        require_once __DIR__ . '/roteador_departamentos.php';
        
        $roteador = new RoteadorDepartamentos($this->mysqli_loja);
        $resultado = $roteador->processarMensagem(['body' => $mensagem, 'from' => 'fallback']);
        
        return $resultado['resposta_sugerida'] ?? 
               "Olá! Sou a Ana da Pixel12Digital. Como posso ajudá-lo hoje? 😊";
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
            'tipo' => 'integração_local',
            'ana_agent_id' => '3',
            'versao' => '2.0 - Integração Local Ana + Sistema',
            'vantagens' => [
                'Sem chamadas HTTP externas',
                'Muito mais rápido',
                'Fácil de gerenciar',
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