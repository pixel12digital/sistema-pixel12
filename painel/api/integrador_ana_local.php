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
    private $ana_api_url = 'https://agentes.pixel12digital.com.br/api/chat/agent_chat.php'; // URL CORRIGIDA
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
                
                // FORMATO CORRETO DA ANA: {"status": "ok", "response": "..."}
                if (isset($data['status']) && $data['status'] === 'ok' && !empty($data['response'])) {
                    return ['success' => true, 'response' => $data['response']];
                }
                // Fallback para formato antigo
                elseif (isset($data['success']) && $data['success'] && !empty($data['response'])) {
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
        
        // 🎯 NOVO: Detectar frases de ativação específicas da Ana
        if (strpos($resposta_lower, 'ativar_transferencia_rafael') !== false) {
            $analise['acao'] = 'transfer_rafael';
            $analise['transfer_rafael'] = true;
            $analise['departamento'] = 'SITES';
            error_log("[INTEGRADOR] Ana ativou transferência para Rafael via frase específica");
        }
        elseif (strpos($resposta_lower, 'ativar_transferencia_suporte') !== false) {
            $analise['acao'] = 'transfer_suporte'; // CORRIGIDO
            $analise['transfer_humano'] = true; // Ainda usa o mesmo campo para processamento
            $analise['departamento'] = 'SUP';
            error_log("[INTEGRADOR] Ana ativou transferência para Suporte via frase específica");
        }
        elseif (strpos($resposta_lower, 'ativar_transferencia_humano') !== false) {
            $analise['acao'] = 'transfer_humano';
            $analise['transfer_humano'] = true;
            $analise['departamento'] = 'COM';
            error_log("[INTEGRADOR] Ana ativou transferência para Humano via frase específica");
        }
        
        // 🧠 FALLBACK: Detecção inteligente baseada na mensagem original (caso Ana não use as frases)
        elseif ($this->detectarIntencaoInteligente($mensagem_original, $resposta_ana)) {
            $intencao = $this->detectarIntencaoInteligente($mensagem_original, $resposta_ana);
            
            if ($intencao['acao'] === 'transfer_rafael') {
                $analise['acao'] = 'transfer_rafael';
                $analise['transfer_rafael'] = true;
                $analise['departamento'] = 'SITES';
                error_log("[INTEGRADOR] Detecção inteligente: Transferência para Rafael (confiança: {$intencao['confianca']})");
            }
            elseif ($intencao['acao'] === 'transfer_suporte') {
                $analise['acao'] = 'transfer_suporte'; // CORRIGIDO
                $analise['transfer_humano'] = true; // Usa mesmo campo para processamento
                $analise['departamento'] = 'SUP';
                error_log("[INTEGRADOR] Detecção inteligente: Transferência para Suporte (confiança: {$intencao['confianca']})");
            }
        }
        
        // 📞 DETECÇÃO LEGACY: Manter compatibilidade com detecções antigas
        elseif (strpos($resposta_lower, 'rafael') !== false || 
            strpos($resposta_lower, 'transferir você para o rafael') !== false ||
            strpos($resposta_lower, 'desenvolvimento web') !== false ||
            strpos($resposta_lower, 'especialista em desenvolvimento web') !== false) {
            
            $analise['acao'] = 'transfer_rafael';
            $analise['transfer_rafael'] = true;
            $analise['departamento'] = 'SITES';
            error_log("[INTEGRADOR] Detecção legacy: Ana mencionou Rafael");
        }
        
        // 👥 Detectar transferência para humanos (método legacy)
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
            else $analise['departamento'] = 'COM'; // Padrão
            
            error_log("[INTEGRADOR] Detecção legacy: Transferência para humanos");
        }
        
        return $analise;
    }
    
    /**
     * 🧠 Detecção inteligente de intenção baseada na mensagem original
     */
    private function detectarIntencaoInteligente($mensagem_original, $resposta_ana) {
        $mensagem = strtolower($mensagem_original);
        $resposta = strtolower($resposta_ana);
        
        // ====== PALAVRAS-CHAVE COMERCIAIS ======
        $palavras_comercial = [
            'quero um site', 'preciso de um site', 'criar um site',
            'fazer um site', 'desenvolver um site', 'site novo',
            'loja virtual', 'ecommerce', 'e-commerce', 'loja online',
            'quanto custa', 'preço', 'orçamento', 'cotação',
            'contratar', 'comprar', 'adquirir'
        ];
        
        // ====== PALAVRAS-CHAVE SUPORTE ======
        $palavras_suporte = [
            'meu site está', 'site fora do ar', 'site não funciona',
            'problema no site', 'erro no site', 'bug', 'falha',
            'não consigo acessar', 'site lento', 'site quebrado',
            'não está funcionando', 'deu erro', 'travou',
            'site parou', 'caiu', 'indisponível'
        ];
        
        // ====== ANÁLISE DE INTENÇÃO ======
        $score_comercial = 0;
        $score_suporte = 0;
        
        // Verificar palavras comerciais
        foreach ($palavras_comercial as $palavra) {
            if (strpos($mensagem, $palavra) !== false) {
                $score_comercial += 2;
            }
        }
        
        // Verificar palavras de suporte
        foreach ($palavras_suporte as $palavra) {
            if (strpos($mensagem, $palavra) !== false) {
                $score_suporte += 3; // Peso maior para suporte
            }
        }
        
        // ====== ANÁLISE CONTEXTUAL ======
        
        // Cliente já tem site? (indica suporte)
        if (strpos($mensagem, 'meu site') !== false || 
            strpos($mensagem, 'nosso site') !== false) {
            $score_suporte += 2;
        }
        
        // Cliente não tem site? (indica comercial)
        if (strpos($mensagem, 'não tenho') !== false || 
            strpos($mensagem, 'quero ter') !== false) {
            $score_comercial += 2;
        }
        
        // ====== DECISÃO FINAL ======
        if ($score_suporte > $score_comercial && $score_suporte >= 3) {
            return [
                'acao' => 'transfer_suporte',
                'departamento' => 'SUP',
                'confianca' => $score_suporte,
                'motivo' => 'Cliente tem problema técnico'
            ];
        }
        elseif ($score_comercial > $score_suporte && $score_comercial >= 2) {
            return [
                'acao' => 'transfer_rafael', 
                'departamento' => 'SITES',
                'confianca' => $score_comercial,
                'motivo' => 'Cliente quer criar/comprar site'
            ];
        }
        
        return false; // Nenhuma intenção detectada
    }
    
    /**
     * Executar ações específicas do sistema
     */
    private function executarAcaoSistema($analise, $numero, $mensagem) {
        switch ($analise['acao']) {
            case 'transfer_rafael':
                $this->registrarTransferenciaRafael($numero, $mensagem);
                $this->executarTransferenciaImediata('rafael', $numero);
                break;
                
            case 'transfer_humano':
                $this->registrarTransferenciaHumano($numero, $mensagem, $analise['departamento']);
                $this->executarTransferenciaImediata('humano', $numero);
                break;
                
            case 'transfer_suporte': // NOVO
                $this->registrarTransferenciaHumano($numero, $mensagem, 'SUP'); // Registra como humano mas com departamento SUP
                $this->executarTransferenciaImediata('suporte', $numero); // NOVO tipo
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
     * NOVO: Executar transferência imediatamente após detecção
     */
    private function executarTransferenciaImediata($tipo, $numero) {
        try {
            require_once __DIR__ . '/executar_transferencias.php';
            $executor = new ExecutorTransferencias($this->mysqli_loja);
            
            if ($tipo === 'rafael') {
                $resultado = $executor->processarTransferenciasRafael();
                error_log("[INTEGRADOR] Transferência Rafael executada imediatamente para $numero");
            } 
            elseif ($tipo === 'humano') {
                $resultado = $executor->processarTransferenciasHumanas();
                error_log("[INTEGRADOR] Transferência Humanos executada imediatamente para $numero");
            }
            elseif ($tipo === 'suporte') { // NOVO
                $resultado = $executor->processarTransferenciasSuporte();
                error_log("[INTEGRADOR] Transferência Suporte executada imediatamente para $numero");
            }
            
        } catch (Exception $e) {
            error_log("[INTEGRADOR] Erro ao executar transferência imediata ($tipo): " . $e->getMessage());
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
            'ana_api_url' => 'https://agentes.pixel12digital.com.br/api/chat/agent_chat.php', // URL CORRIGIDA
            'versao' => '2.1 - URL Corrigida Ana + Sistema',
            'vantagens' => [
                'Usa URL correta da Ana que funciona',
                'Formato de resposta corrigido',
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