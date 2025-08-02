<?php
/**
 * 🚀 EXECUTOR DE TRANSFERÊNCIAS - PIXEL12DIGITAL
 * 
 * Sistema que efetivamente executa as transferências detectadas pela Ana
 * - Notifica Rafael para sites/ecommerce
 * - Transfere conversas para Canal 3001 (humanos)
 * - Gerencia status e follow-up
 */

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../db.php';

header('Content-Type: application/json');

class ExecutorTransferencias {
    
    private $mysqli;
    private $whatsapp_api_base = 'http://212.85.11.238';
    private $rafael_numero = '5547973095525'; // Número do Rafael
    
    public function __construct($mysqli) {
        $this->mysqli = $mysqli;
    }
    
    /**
     * Processar todas as transferências pendentes
     */
    public function processarTransferenciasPendentes() {
        $resultado = [
            'success' => true,
            'transferencias_rafael' => 0,
            'transferencias_humanas' => 0,
            'transferencias_suporte' => 0, // NOVO
            'erros' => [],
            'detalhes' => []
        ];
        
        try {
            // 1. Processar transferências para Rafael
            $resultado_rafael = $this->processarTransferenciasRafael();
            $resultado['transferencias_rafael'] = $resultado_rafael['processadas'];
            $resultado['detalhes']['rafael'] = $resultado_rafael['detalhes'];
            
            // 2. Processar transferências para humanos
            $resultado_humanos = $this->processarTransferenciasHumanas();
            $resultado['transferencias_humanas'] = $resultado_humanos['processadas'];
            $resultado['detalhes']['humanos'] = $resultado_humanos['detalhes'];
            
            // 3. NOVO: Processar transferências de suporte específicas
            $resultado_suporte = $this->processarTransferenciasSuporte();
            $resultado['transferencias_suporte'] = $resultado_suporte['processadas'];
            $resultado['detalhes']['suporte'] = $resultado_suporte['detalhes'];
            
            // 4. Consolidar erros
            $resultado['erros'] = array_merge(
                $resultado_rafael['erros'] ?? [],
                $resultado_humanos['erros'] ?? [],
                $resultado_suporte['erros'] ?? [] // NOVO
            );
            
        } catch (Exception $e) {
            $resultado['success'] = false;
            $resultado['erros'][] = "Erro crítico: " . $e->getMessage();
        }
        
        return $resultado;
    }
    
    /**
     * Processar transferências para Rafael
     */
    private function processarTransferenciasRafael() {
        $resultado = [
            'processadas' => 0,
            'erros' => [],
            'detalhes' => []
        ];
        
        // Buscar transferências pendentes para Rafael
        $sql = "SELECT * FROM transferencias_rafael WHERE status = 'pendente' ORDER BY data_transferencia ASC LIMIT 10";
        $result = $this->mysqli->query($sql);
        
        while ($transferencia = $result->fetch_assoc()) {
            try {
                $sucesso = $this->notificarRafael($transferencia);
                
                if ($sucesso) {
                    // Marcar como processada
                    $this->mysqli->query("UPDATE transferencias_rafael SET status = 'notificado', data_processamento = NOW() WHERE id = " . $transferencia['id']);
                    
                    $resultado['processadas']++;
                    $resultado['detalhes'][] = [
                        'id' => $transferencia['id'],
                        'cliente' => $transferencia['numero_cliente'],
                        'status' => 'notificado',
                        'acao' => 'Rafael notificado via WhatsApp'
                    ];
                    
                    error_log("[TRANSFERENCIAS] Rafael notificado para cliente: " . $transferencia['numero_cliente']);
                    
                } else {
                    $resultado['erros'][] = "Falha ao notificar Rafael para transferência ID: " . $transferencia['id'];
                }
                
            } catch (Exception $e) {
                $resultado['erros'][] = "Erro na transferência Rafael ID " . $transferencia['id'] . ": " . $e->getMessage();
            }
        }
        
        return $resultado;
    }
    
    /**
     * Processar transferências para humanos (Canal 3001)
     */
    private function processarTransferenciasHumanas() {
        $resultado = [
            'processadas' => 0,
            'erros' => [],
            'detalhes' => []
        ];
        
        // Buscar transferências pendentes para humanos (EXCETO suporte técnico específico)
        $sql = "SELECT * FROM transferencias_humano 
                WHERE status = 'pendente' 
                AND (departamento != 'SUP' OR departamento IS NULL)
                ORDER BY data_transferencia ASC LIMIT 10";
        $result = $this->mysqli->query($sql);
        
        while ($transferencia = $result->fetch_assoc()) {
            try {
                $sucesso = $this->transferirParaHumanos($transferencia);
                
                if ($sucesso) {
                    // Marcar como processada
                    $this->mysqli->query("UPDATE transferencias_humano SET status = 'transferido', data_processamento = NOW() WHERE id = " . $transferencia['id']);
                    
                    $resultado['processadas']++;
                    $resultado['detalhes'][] = [
                        'id' => $transferencia['id'],
                        'cliente' => $transferencia['numero_cliente'],
                        'departamento' => $transferencia['departamento'],
                        'status' => 'transferido',
                        'acao' => 'Conversa transferida para Canal 3001'
                    ];
                    
                    error_log("[TRANSFERENCIAS] Cliente transferido para humanos: " . $transferencia['numero_cliente'] . " -> " . $transferencia['departamento']);
                    
                } else {
                    $resultado['erros'][] = "Falha ao transferir para humanos - ID: " . $transferencia['id'];
                }
                
            } catch (Exception $e) {
                $resultado['erros'][] = "Erro na transferência humana ID " . $transferencia['id'] . ": " . $e->getMessage();
            }
        }
        
        return $resultado;
    }
    
    /**
     * 🆕 NOVO: Processar transferências específicas de suporte técnico
     */
    private function processarTransferenciasSuporte() {
        $resultado = [
            'processadas' => 0,
            'erros' => [],
            'detalhes' => []
        ];
        
        // Buscar transferências pendentes para suporte técnico específico
        $sql = "SELECT * FROM transferencias_humano 
                WHERE status = 'pendente' 
                AND departamento = 'SUP'
                ORDER BY data_transferencia ASC LIMIT 10";
        $result = $this->mysqli->query($sql);
        
        while ($transferencia = $result->fetch_assoc()) {
            try {
                $sucesso = $this->transferirParaSuporte($transferencia);
                
                if ($sucesso) {
                    // Marcar como processada
                    $this->mysqli->query("UPDATE transferencias_humano SET status = 'transferido', data_processamento = NOW() WHERE id = " . $transferencia['id']);
                    
                    $resultado['processadas']++;
                    $resultado['detalhes'][] = [
                        'id' => $transferencia['id'],
                        'cliente' => $transferencia['numero_cliente'],
                        'departamento' => 'SUP',
                        'status' => 'transferido',
                        'acao' => 'Transferido para Suporte Técnico'
                    ];
                    
                    error_log("[TRANSFERENCIAS] Cliente transferido para suporte técnico: " . $transferencia['numero_cliente']);
                    
                } else {
                    $resultado['erros'][] = "Falha ao transferir para suporte - ID: " . $transferencia['id'];
                }
                
            } catch (Exception $e) {
                $resultado['erros'][] = "Erro na transferência suporte ID " . $transferencia['id'] . ": " . $e->getMessage();
            }
        }
        
        return $resultado;
    }
    
    /**
     * Notificar Rafael sobre cliente interessado em sites
     */
    private function notificarRafael($transferencia) {
        try {
            $numero_cliente = $transferencia['numero_cliente'];
            $mensagem_original = $transferencia['mensagem_original'];
            $data_transferencia = $transferencia['data_transferencia'];
            
            // Buscar dados do cliente se existir
            $cliente_info = $this->buscarInfoCliente($numero_cliente);
            $nome_cliente = $cliente_info ? $cliente_info['nome'] : 'Cliente não identificado';
            
            // Montar mensagem para Rafael
            $mensagem_rafael = "🌐 *NOVO CLIENTE SITES/ECOMMERCE*\n\n";
            $mensagem_rafael .= "👤 *Cliente:* $nome_cliente\n";
            $mensagem_rafael .= "📱 *WhatsApp:* $numero_cliente\n";
            $mensagem_rafael .= "🕐 *Quando:* " . date('d/m/Y H:i', strtotime($data_transferencia)) . "\n\n";
            $mensagem_rafael .= "💬 *Mensagem original:*\n";
            $mensagem_rafael .= "\"" . substr($mensagem_original, 0, 200) . (strlen($mensagem_original) > 200 ? '...' : '') . "\"\n\n";
            $mensagem_rafael .= "🎯 *Ana detectou interesse em desenvolvimento web/ecommerce*\n\n";
            $mensagem_rafael .= "📋 *Próximos passos:*\n";
            $mensagem_rafael .= "• Entre em contato via Canal Comercial\n";
            $mensagem_rafael .= "• Cliente já foi informado que você é o especialista\n";
            $mensagem_rafael .= "• Contexto: Sites e Ecommerce\n\n";
            $mensagem_rafael .= "🚀 *Sucesso nos negócios!*\n";
            $mensagem_rafael .= "_Ana - Pixel12Digital_";
            
            // Enviar via WhatsApp Canal Comercial (3001)
            return $this->enviarWhatsApp($this->rafael_numero, $mensagem_rafael, 'comercial', 3001);
            
        } catch (Exception $e) {
            error_log("[TRANSFERENCIAS] Erro ao notificar Rafael: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Transferir conversa para Canal 3001 (humanos)
     */
    private function transferirParaHumanos($transferencia) {
        try {
            $numero_cliente = $transferencia['numero_cliente'];
            $mensagem_original = $transferencia['mensagem_original'];
            $departamento = $transferencia['departamento'];
            $data_transferencia = $transferencia['data_transferencia'];
            
            // 1. Buscar dados do cliente
            $cliente_info = $this->buscarInfoCliente($numero_cliente);
            $nome_cliente = $cliente_info ? $cliente_info['nome'] : 'Cliente não identificado';
            
            // 2. Criar registro de transferência no Canal 3001
            $this->criarRegistroTransferenciaCanal3001($transferencia, $cliente_info);
            
            // 3. Enviar notificação para agentes do Canal 3001
            $sucesso_notificacao = $this->notificarAgentesCanal3001($transferencia, $cliente_info);
            
            // 4. Bloquear Ana para este cliente no Canal 3000
            $this->bloquearAnaParaCliente($numero_cliente);
            
            // 5. Enviar mensagem de boas-vindas ao cliente no Canal 3001
            $this->enviarBoasVindasCanal3001($numero_cliente, $departamento, $nome_cliente);
            
            return $sucesso_notificacao;
            
        } catch (Exception $e) {
            error_log("[TRANSFERENCIAS] Erro ao transferir para humanos: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * 🆕 NOVO: Transferir conversa para Suporte Técnico específico
     */
    private function transferirParaSuporte($transferencia) {
        try {
            $numero_cliente = $transferencia['numero_cliente'];
            $mensagem_original = $transferencia['mensagem_original'];
            $data_transferencia = $transferencia['data_transferencia'];
            
            // 1. Buscar dados do cliente
            $cliente_info = $this->buscarInfoCliente($numero_cliente);
            $nome_cliente = $cliente_info ? $cliente_info['nome'] : 'Cliente não identificado';
            
            // 2. Criar registro específico de suporte técnico
            $this->criarRegistroSuporteTecnico($transferencia, $cliente_info);
            
            // 3. Notificar equipe de suporte técnico
            $sucesso_notificacao = $this->notificarSuporteTecnico($transferencia, $cliente_info);
            
            // 4. Bloquear Ana para este cliente
            $this->bloquearAnaParaCliente($numero_cliente);
            
            // 5. Enviar mensagem específica de suporte
            $this->enviarBoasVindasSuporteTecnico($numero_cliente, $nome_cliente);
            
            return $sucesso_notificacao;
            
        } catch (Exception $e) {
            error_log("[TRANSFERENCIAS] Erro ao transferir para suporte técnico: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Criar registro da transferência no Canal 3001
     */
    private function criarRegistroTransferenciaCanal3001($transferencia, $cliente_info) {
        $numero_cliente = $transferencia['numero_cliente'];
        $mensagem_original = $transferencia['mensagem_original'];
        $departamento = $transferencia['departamento'];
        
        // Inserir mensagem de contexto no Canal 3001
        $contexto = "🔄 *TRANSFERÊNCIA DO CANAL IA*\n\n";
        $contexto .= "👤 Cliente: " . ($cliente_info ? $cliente_info['nome'] : 'Não identificado') . "\n";
        $contexto .= "🏢 Departamento: " . $this->getNomeDepartamento($departamento) . "\n";
        $contexto .= "💬 Mensagem original: \"$mensagem_original\"\n\n";
        $contexto .= "ℹ️ Cliente solicitou atendimento humano via Ana";
        
        $sql = "INSERT INTO mensagens_comunicacao 
                (canal_id, numero_whatsapp, mensagem, tipo, data_hora, direcao, status, observacoes) 
                VALUES (37, ?, ?, 'transferencia', NOW(), 'sistema', 'entregue', 'Transferência automática da Ana')";
        
        $stmt = $this->mysqli->prepare($sql);
        $stmt->bind_param('ss', $numero_cliente, $contexto);
        $stmt->execute();
        $stmt->close();
    }
    
    /**
     * Notificar agentes do Canal 3001 sobre nova transferência
     */
    private function notificarAgentesCanal3001($transferencia, $cliente_info) {
        try {
            $numero_cliente = $transferencia['numero_cliente'];
            $departamento = $transferencia['departamento'];
            $nome_departamento = $this->getNomeDepartamento($departamento);
            $nome_cliente = $cliente_info ? $cliente_info['nome'] : 'Cliente não identificado';
            
            // Mensagem para agentes
            $mensagem_agentes = "🔔 *NOVA TRANSFERÊNCIA - $nome_departamento*\n\n";
            $mensagem_agentes .= "👤 *Cliente:* $nome_cliente\n";
            $mensagem_agentes .= "📱 *WhatsApp:* $numero_cliente\n";
            $mensagem_agentes .= "🕐 *Transferido:* " . date('d/m/Y H:i') . "\n";
            $mensagem_agentes .= "🤖 *Origem:* Ana (Canal IA)\n\n";
            $mensagem_agentes .= "💬 *Mensagem:* \"" . substr($transferencia['mensagem_original'], 0, 150) . "\"\n\n";
            $mensagem_agentes .= "🎯 *Cliente solicitou atendimento humano*\n";
            $mensagem_agentes .= "📋 Acesse o painel para atender\n\n";
            $mensagem_agentes .= "_Sistema de Transferências - Pixel12Digital_";
            
            // Buscar números dos agentes (você pode configurar uma tabela de agentes)
            $numeros_agentes = [
                '5547973095525' // Adicione outros números de agentes aqui
            ];
            
            $sucessos = 0;
            foreach ($numeros_agentes as $numero_agente) {
                if ($this->enviarWhatsApp($numero_agente, $mensagem_agentes, 'comercial', 3001)) {
                    $sucessos++;
                }
            }
            
            return $sucessos > 0;
            
        } catch (Exception $e) {
            error_log("[TRANSFERENCIAS] Erro ao notificar agentes: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Bloquear Ana para cliente específico
     */
    private function bloquearAnaParaCliente($numero_cliente) {
        // Criar/atualizar registro de bloqueio
        $sql = "INSERT INTO bloqueios_ana (numero_cliente, motivo, data_bloqueio, ativo) 
                VALUES (?, 'transferencia_humano', NOW(), 1)
                ON DUPLICATE KEY UPDATE 
                motivo = 'transferencia_humano', 
                data_bloqueio = NOW(), 
                ativo = 1";
        
        $stmt = $this->mysqli->prepare($sql);
        $stmt->bind_param('s', $numero_cliente);
        $stmt->execute();
        $stmt->close();
        
        error_log("[TRANSFERENCIAS] Ana bloqueada para cliente: $numero_cliente");
    }
    
    /**
     * Enviar boas-vindas ao cliente no Canal 3001
     */
    private function enviarBoasVindasCanal3001($numero_cliente, $departamento, $nome_cliente) {
        $nome_departamento = $this->getNomeDepartamento($departamento);
        
        $mensagem = "👋 Olá" . ($nome_cliente !== 'Cliente não identificado' ? ", $nome_cliente" : '') . "!\n\n";
        $mensagem .= "🤝 Você foi transferido para nosso atendimento humano - $nome_departamento.\n\n";
        $mensagem .= "👨‍💼 Em breve um de nossos especialistas entrará em contato.\n\n";
        $mensagem .= "⏰ Horário de atendimento: Segunda a Sexta, 8h às 18h\n\n";
        $mensagem .= "Obrigado por escolher a Pixel12Digital! 🚀";
        
        return $this->enviarWhatsApp($numero_cliente, $mensagem, 'comercial', 3001);
    }
    
    /**
     * 🆕 NOVO: Criar registro específico de suporte técnico
     */
    private function criarRegistroSuporteTecnico($transferencia, $cliente_info) {
        $numero_cliente = $transferencia['numero_cliente'];
        $mensagem_original = $transferencia['mensagem_original'];
        
        // Inserir mensagem de contexto específica para suporte técnico
        $contexto = "🔧 *TRANSFERÊNCIA PARA SUPORTE TÉCNICO*\n\n";
        $contexto .= "👤 Cliente: " . ($cliente_info ? $cliente_info['nome'] : 'Não identificado') . "\n";
        $contexto .= "🛠️ Departamento: Suporte Técnico\n";
        $contexto .= "💬 Problema relatado: \"$mensagem_original\"\n\n";
        $contexto .= "⚠️ Cliente precisa de assistência técnica via Ana";
        
        $sql = "INSERT INTO mensagens_comunicacao 
                (canal_id, numero_whatsapp, mensagem, tipo, data_hora, direcao, status, observacoes) 
                VALUES (37, ?, ?, 'transferencia_suporte', NOW(), 'sistema', 'entregue', 'Transferência automática - Suporte Técnico')";
        
        $stmt = $this->mysqli->prepare($sql);
        $stmt->bind_param('ss', $numero_cliente, $contexto);
        $stmt->execute();
        $stmt->close();
    }
    
    /**
     * 🆕 NOVO: Notificar equipe de suporte técnico
     */
    private function notificarSuporteTecnico($transferencia, $cliente_info) {
        try {
            $numero_cliente = $transferencia['numero_cliente'];
            $nome_cliente = $cliente_info ? $cliente_info['nome'] : 'Cliente não identificado';
            
            // Mensagem específica para suporte técnico
            $mensagem_suporte = "🔧 *NOVO CHAMADO TÉCNICO*\n\n";
            $mensagem_suporte .= "👤 *Cliente:* $nome_cliente\n";
            $mensagem_suporte .= "📱 *WhatsApp:* $numero_cliente\n";
            $mensagem_suporte .= "🕐 *Transferido:* " . date('d/m/Y H:i') . "\n";
            $mensagem_suporte .= "🤖 *Origem:* Ana (Canal IA)\n\n";
            $mensagem_suporte .= "🛠️ *Problema:* \"" . substr($transferencia['mensagem_original'], 0, 150) . "\"\n\n";
            $mensagem_suporte .= "⚠️ *Cliente precisa de suporte técnico especializado*\n";
            $mensagem_suporte .= "📋 Prioridade: NORMAL\n\n";
            $mensagem_suporte .= "_Sistema de Transferências - Pixel12Digital_";
            
            // Números específicos da equipe de suporte (configure conforme necessário)
            $numeros_suporte = [
                '5547973095525' // Adicione outros técnicos aqui
            ];
            
            $sucessos = 0;
            foreach ($numeros_suporte as $numero_tecnico) {
                if ($this->enviarWhatsApp($numero_tecnico, $mensagem_suporte, 'comercial', 3001)) {
                    $sucessos++;
                }
            }
            
            return $sucessos > 0;
            
        } catch (Exception $e) {
            error_log("[TRANSFERENCIAS] Erro ao notificar suporte técnico: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * 🆕 NOVO: Enviar boas-vindas específicas para suporte técnico
     */
    private function enviarBoasVindasSuporteTecnico($numero_cliente, $nome_cliente) {
        $mensagem = "🔧 Olá" . ($nome_cliente !== 'Cliente não identificado' ? ", $nome_cliente" : '') . "!\n\n";
        $mensagem .= "🛠️ Você foi transferido para nossa **Equipe de Suporte Técnico**.\n\n";
        $mensagem .= "👨‍💻 Nossos especialistas irão analisar seu problema técnico e retornar em breve.\n\n";
        $mensagem .= "⏰ **Horário de atendimento técnico:**\n";
        $mensagem .= "   • Segunda a Sexta: 8h às 18h\n";
        $mensagem .= "   • Sábado: 8h às 12h\n\n";
        $mensagem .= "🚨 **Para emergências:** 47 97309525\n\n";
        $mensagem .= "Obrigado por confiar em nosso suporte! 🚀";
        
        return $this->enviarWhatsApp($numero_cliente, $mensagem, 'comercial', 3001);
    }
    
    /**
     * Buscar informações do cliente
     */
    private function buscarInfoCliente($numero) {
        $numero_limpo = preg_replace('/\D/', '', $numero);
        
        $sql = "SELECT nome, email, documento FROM clientes 
                WHERE REPLACE(REPLACE(REPLACE(celular, ' ', ''), '-', ''), '(', '') LIKE '%$numero_limpo%' 
                LIMIT 1";
        
        $result = $this->mysqli->query($sql);
        return $result ? $result->fetch_assoc() : null;
    }
    
    /**
     * Enviar mensagem via WhatsApp
     */
    private function enviarWhatsApp($numero, $mensagem, $session = 'default', $porta = 3000) {
        try {
            $api_url = $this->whatsapp_api_base . ":$porta/send/text";
            
            $payload = [
                'sessionName' => $session,
                'number' => $numero,
                'message' => $mensagem
            ];
            
            $ch = curl_init($api_url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            
            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($http_code === 200 && $response) {
                $data = json_decode($response, true);
                return isset($data['success']) && $data['success'];
            }
            
            return false;
            
        } catch (Exception $e) {
            error_log("[TRANSFERENCIAS] Erro ao enviar WhatsApp: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obter nome completo do departamento
     */
    private function getNomeDepartamento($codigo) {
        $departamentos = [
            'FIN' => 'Financeiro',
            'SUP' => 'Suporte Técnico', // ATUALIZADO
            'COM' => 'Comercial',
            'ADM' => 'Administrativo',
            'SITES' => 'Sites e Ecommerce'
        ];
        
        return $departamentos[$codigo] ?? $codigo;
    }
}

// ===== PROCESSAMENTO DA REQUISIÇÃO =====

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $executor = new ExecutorTransferencias($mysqli);
    $resultado = $executor->processarTransferenciasPendentes();
    echo json_encode($resultado, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Status do executor
    echo json_encode([
        'success' => true,
        'status' => 'ativo',
        'sistema' => 'Executor de Transferências',
        'versao' => '2.0', // ATUALIZADO
        'funcionalidades' => [
            'Notificação automática para Rafael (Comercial)',
            'Transferência inteligente para Suporte Técnico', // NOVO
            'Transferência geral para Canal 3001 (Humanos)',
            'Bloqueio da Ana pós-transferência',
            'Detecção por frases de ativação da Ana', // NOVO
            'Sistema de fallback com detecção inteligente', // NOVO
            'Monitoramento completo'
        ],
        'tipos_transferencia' => [
            'RAFAEL' => 'Comercial - Sites/Ecommerce',
            'SUPORTE' => 'Suporte Técnico - Problemas', // NOVO
            'HUMANOS' => 'Atendimento Geral'
        ]
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
}

$mysqli->close();
?> 