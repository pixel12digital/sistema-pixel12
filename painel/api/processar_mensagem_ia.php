<?php
/**
 * PROCESSADOR DE MENSAGENS COM IA BÁSICA
 * 
 * Sistema de inteligência de atendimento para WhatsApp
 */

header('Content-Type: application/json');
require_once __DIR__ . '/../../config.php';
require_once '../db.php';

// Receber dados da mensagem
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Dados inválidos']);
    exit;
}

$numero = $data['from'] ?? '';
$texto = strtolower(trim($data['message'] ?? ''));
$tipo = $data['type'] ?? 'text';

// Buscar cliente
$cliente_id = null;
$cliente = null;

if ($numero) {
    $numero_limpo = preg_replace('/\D/', '', $numero);
    
    // Busca inteligente por número (com variações)
    $cliente = buscarClientePorNumero($numero_limpo, $mysqli);
    if ($cliente) {
        $cliente_id = $cliente['id'];
    }
}

// Sistema de IA básico - Análise de intenção
$intencao = 'geral';
$resposta = '';
$metodo = 'ia_basica';
$tipo_resposta = 'texto';

// Palavras-chave para identificar intenções
$palavras_chave = [
    'fatura' => ['fatura', 'boleto', 'conta', 'pagamento', 'vencimento', 'pagar', 'consulta', 'consultas'],
    'plano' => ['plano', 'pacote', 'serviço', 'assinatura', 'mensalidade'],
    'suporte' => ['suporte', 'ajuda', 'problema', 'erro', 'não funciona', 'bug'],
    'comercial' => ['comercial', 'venda', 'preço', 'orçamento', 'proposta', 'site'],
    'cpf' => ['cpf', 'documento', 'identificação', 'cadastro', 'cnpj'],
    'saudacao' => ['oi', 'olá', 'ola', 'bom dia', 'boa tarde', 'boa noite', 'hello', 'hi', 'oie']
];

// Identificar intenção
foreach ($palavras_chave as $intencao_tipo => $palavras) {
    foreach ($palavras as $palavra) {
        if (strpos($texto, $palavra) !== false) {
            $intencao = $intencao_tipo;
            break 2;
        }
    }
}

// Gerar resposta baseada na intenção
switch ($intencao) {
    case 'fatura':
        if ($cliente_id) {
            // Cliente já identificado - enviar faturas com sincronização
            $resposta = buscarFaturasCliente($cliente_id, $mysqli);
        } else {
            // Cliente não encontrado - pedir CPF/CNPJ
            $resposta = "Olá! Para verificar suas faturas, preciso localizar seu cadastro.\n\n";
            $resposta .= "📋 *Por favor, informe:*\n";
            $resposta .= "• Seu CPF ou CNPJ (apenas números, sem espaços)\n\n";
            $resposta .= "Assim posso buscar suas informações e repassar o status das faturas! 😊";
        }
        break;
        
    case 'plano':
        if ($cliente_id) {
            $resposta = "Olá! Vejo que você tem dúvidas sobre seu plano. 📊\n\n";
            $resposta .= "Para verificar os detalhes do seu plano, preciso do seu CPF. ";
            $resposta .= "Pode me informar o número do seu CPF?";
        } else {
            $resposta = "Olá! Para verificar informações sobre planos, preciso do seu CPF. ";
            $resposta .= "Pode me informar o número do seu CPF?";
        }
        break;
        
    case 'suporte':
        $resposta = "Olá! Vejo que você precisa de suporte técnico. 🔧\n\n";
        $resposta .= "Para suporte técnico, entre em contato através do número: *47 997309525*\n\n";
        $resposta .= "Nossa equipe técnica está pronta para ajudá-lo!";
        break;
        
    case 'comercial':
        $resposta = "Olá! Vejo que você tem interesse em nossos serviços comerciais. 💼\n\n";
        $resposta .= "Para atendimento comercial, entre em contato através do número: *47 997309525*\n\n";
        $resposta .= "Nossa equipe comercial ficará feliz em atendê-lo!";
        break;
        
    case 'cpf':
        // Verificar se é um CPF/CNPJ válido
        $cpf_limpo = preg_replace('/\D/', '', $texto);
        if (strlen($cpf_limpo) >= 11 && strlen($cpf_limpo) <= 14) {
            // Buscar cliente pelo CPF/CNPJ
            $cliente_cpf = buscarClientePorCPF($cpf_limpo, $mysqli);
            
            if ($cliente_cpf) {
                $cliente_id = $cliente_cpf['id'];
                $resposta = "Olá {$cliente_cpf['contact_name']}! 👋\n\n";
                $resposta .= "✅ Encontrei seu cadastro! Como posso ajudá-lo hoje?\n\n";
                $resposta .= "📋 *Opções disponíveis:*\n";
                $resposta .= "• Verificar faturas (digite 'faturas' ou 'consulta')\n";
                $resposta .= "• Informações do plano\n";
                $resposta .= "• Suporte técnico\n";
                $resposta .= "• Atendimento comercial";
            } else {
                $resposta = "❌ CPF/CNPJ não encontrado em nossa base de dados.\n\n";
                $resposta .= "📞 Para atendimento personalizado, entre em contato: *47 997309525*\n\n";
                $resposta .= "Nossa equipe ficará feliz em ajudá-lo! 😊";
            }
        } else {
            $resposta = "Por favor, informe um CPF (11 dígitos) ou CNPJ (14 dígitos) válido, apenas números.";
        }
        break;
        
    case 'saudacao':
        if ($cliente_id) {
            $nome_cliente = $cliente['contact_name'] ?: $cliente['nome'];
            $resposta = "Olá $nome_cliente! 👋\n\n";
            $resposta .= "🤖 *Este é um atendimento automático* do canal exclusivo da *Pixel12Digital* para assuntos financeiros.\n\n";
            $resposta .= "📞 *Para outras informações ou falar com nossa equipe:*\n";
            $resposta .= "Entre em contato: *47 997309525*\n\n";
            $resposta .= "💰 *Para assuntos financeiros:*\n";
            $resposta .= "• Digite 'faturas' para consultar suas faturas em aberto\n";
            $resposta .= "• Verificar status de pagamentos\n";
            $resposta .= "• Informações sobre planos\n\n";
            $resposta .= "Como posso ajudá-lo hoje? 😊";
        } else {
            $resposta = "Olá! 👋\n\n";
            $resposta .= "🤖 *Este é um atendimento automático* do canal exclusivo da *Pixel12Digital* para assuntos financeiros.\n\n";
            $resposta .= "📞 *Para outras informações ou falar com nossa equipe:*\n";
            $resposta .= "Entre em contato: *47 997309525*\n\n";
            $resposta .= "💰 *Para assuntos financeiros:*\n";
            $resposta .= "• Digite 'faturas' para consultar suas faturas em aberto\n";
            $resposta .= "• Verificar status de pagamentos\n";
            $resposta .= "• Informações sobre planos\n\n";
            $resposta .= "Se não encontrar seu cadastro, informe seu CPF ou CNPJ (apenas números).";
        }
        break;
        
    default:
        if ($cliente_id) {
            $nome_cliente = $cliente['contact_name'] ?: $cliente['nome'];
            $resposta = "Olá $nome_cliente! 👋\n\n";
            $resposta .= "🤖 *Este é um atendimento automático* do canal exclusivo da *Pixel12Digital* para assuntos financeiros.\n\n";
            $resposta .= "📞 *Para outras informações ou falar com nossa equipe:*\n";
            $resposta .= "Entre em contato: *47 997309525*\n\n";
            $resposta .= "💰 *Para assuntos financeiros:*\n";
            $resposta .= "• Digite 'faturas' para consultar suas faturas em aberto\n";
            $resposta .= "• Verificar status de pagamentos\n";
            $resposta .= "• Informações sobre planos\n\n";
            $resposta .= "Como posso ajudá-lo hoje? 😊";
        } else {
            $resposta = "Olá! 👋\n\n";
            $resposta .= "🤖 *Este é um atendimento automático* do canal exclusivo da *Pixel12Digital* para assuntos financeiros.\n\n";
            $resposta .= "📞 *Para outras informações ou falar com nossa equipe:*\n";
            $resposta .= "Entre em contato: *47 997309525*\n\n";
            $resposta .= "💰 *Para assuntos financeiros:*\n";
            $resposta .= "• Digite 'faturas' para consultar suas faturas em aberto\n";
            $resposta .= "• Verificar status de pagamentos\n";
            $resposta .= "• Informações sobre planos\n\n";
            $resposta .= "Se não encontrar seu cadastro, informe seu CPF ou CNPJ (apenas números).";
        }
        break;
}

// Responder com a análise da IA
echo json_encode([
    'success' => true,
    'resposta' => $resposta,
    'intencao' => $intencao,
    'metodo' => $metodo,
    'tipo' => $tipo_resposta,
    'cliente_id' => $cliente_id,
    'cliente_nome' => $cliente ? ($cliente['contact_name'] ?: $cliente['nome']) : null
]);

/**
 * Busca cliente por número com variações inteligentes
 */
function buscarClientePorNumero($numero_limpo, $mysqli) {
    // Tentar diferentes variações do número
    $variacoes = [
        $numero_limpo,
        substr($numero_limpo, -8), // Últimos 8 dígitos
        substr($numero_limpo, -9), // Últimos 9 dígitos
        substr($numero_limpo, -10), // Últimos 10 dígitos
        substr($numero_limpo, -11)  // Últimos 11 dígitos
    ];
    
    foreach ($variacoes as $variacao) {
        $sql = "SELECT id, nome, contact_name, celular, telefone, asaas_id FROM clientes 
                WHERE REPLACE(REPLACE(REPLACE(REPLACE(celular, '(', ''), ')', ''), '-', ''), ' ', '') LIKE '%$variacao%' 
                OR REPLACE(REPLACE(REPLACE(REPLACE(telefone, '(', ''), ')', ''), '-', ''), ' ', '') LIKE '%$variacao%'
                LIMIT 1";
        
        $result = $mysqli->query($sql);
        if ($result && $result->num_rows > 0) {
            return $result->fetch_assoc();
        }
    }
    
    return null;
}

/**
 * Busca cliente por CPF/CNPJ
 */
function buscarClientePorCPF($cpf_limpo, $mysqli) {
    $sql = "SELECT id, nome, contact_name, cpf_cnpj FROM clientes WHERE cpf_cnpj = '$cpf_limpo' LIMIT 1";
    $result = $mysqli->query($sql);
    
    if ($result && $result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    
    return null;
}

/**
 * Busca faturas do cliente (apenas vencidas e a próxima a vencer)
 * COM SINCRONIZAÇÃO INDIVIDUAL COM ASAAS
 */
function buscarFaturasCliente($cliente_id, $mysqli) {
    // 1. SINCRONIZAÇÃO INDIVIDUAL COM ASAAS
    $sincronizacao = sincronizarFaturasClienteAsaas($cliente_id, $mysqli);
    
    // 2. Buscar faturas vencidas (OVERDUE) - após sincronização
    $sql_vencidas = "SELECT 
                        cob.id,
                        cob.valor,
                        cob.status,
                        DATE_FORMAT(cob.vencimento, '%d/%m/%Y') as vencimento_formatado,
                        cob.url_fatura,
                        DATEDIFF(CURDATE(), cob.vencimento) as dias_vencido
                    FROM cobrancas cob
                    WHERE cob.cliente_id = $cliente_id
                    AND cob.status = 'OVERDUE'
                    ORDER BY cob.vencimento ASC";
    
    $result_vencidas = $mysqli->query($sql_vencidas);
    
    // 3. Buscar apenas a PRÓXIMA fatura a vencer (PENDING) - a mais próxima
    $sql_proxima_vencer = "SELECT 
                        cob.id,
                        cob.valor,
                        cob.status,
                        DATE_FORMAT(cob.vencimento, '%d/%m/%Y') as vencimento_formatado,
                        cob.url_fatura,
                        DATEDIFF(cob.vencimento, CURDATE()) as dias_para_vencer
                    FROM cobrancas cob
                    WHERE cob.cliente_id = $cliente_id
                    AND cob.status = 'PENDING'
                    ORDER BY cob.vencimento ASC
                    LIMIT 1";
    
    $result_proxima_vencer = $mysqli->query($sql_proxima_vencer);
    
    // Verificar se há faturas
    $total_vencidas = $result_vencidas ? $result_vencidas->num_rows : 0;
    $tem_proxima_vencer = $result_proxima_vencer ? $result_proxima_vencer->num_rows : 0;
    
    if ($total_vencidas == 0 && $tem_proxima_vencer == 0) {
        return "🎉 Ótima notícia! Você não possui faturas vencidas ou a vencer no momento.\n\nTudo em dia! 😊\n\n🤖 *Esta é uma mensagem automática*\n📞 Para atendimento personalizado, entre em contato: *47 997309525*";
    }
    
    // Buscar nome do cliente
    $sql_cliente = "SELECT contact_name, nome FROM clientes WHERE id = $cliente_id LIMIT 1";
    $result_cliente = $mysqli->query($sql_cliente);
    $cliente = $result_cliente->fetch_assoc();
    $nome_cliente = $cliente['contact_name'] ?: $cliente['nome'];
    
    $resposta = "Olá $nome_cliente! 👋\n\n";
    $resposta .= "📋 Aqui está o resumo das suas faturas:\n\n";
    
    // Seção de faturas vencidas
    if ($total_vencidas > 0) {
        $resposta .= "🔴 *Faturas Vencidas:*\n";
        $valor_total_vencidas = 0;
        
        while ($fatura = $result_vencidas->fetch_assoc()) {
            $valor = number_format($fatura['valor'], 2, ',', '.');
            $dias_vencido = $fatura['dias_vencido'];
            $valor_total_vencidas += $fatura['valor'];
            
            $resposta .= "• Fatura #{$fatura['id']} - R$ $valor\n";
            $resposta .= "  Venceu em {$fatura['vencimento_formatado']} ({$dias_vencido} dias atrás)\n";
            
            if ($fatura['url_fatura']) {
                $resposta .= "  💳 Pagar: {$fatura['url_fatura']}\n";
            }
            $resposta .= "\n";
        }
        
        $valor_total_vencidas_formatado = number_format($valor_total_vencidas, 2, ',', '.');
        $resposta .= "💰 *Total vencido: R$ $valor_total_vencidas_formatado*\n\n";
    }
    
    // Seção da PRÓXIMA fatura a vencer (apenas uma)
    if ($tem_proxima_vencer > 0) {
        $resposta .= "🟡 *Próxima Fatura a Vencer:*\n";
        
        $fatura = $result_proxima_vencer->fetch_assoc();
        $valor = number_format($fatura['valor'], 2, ',', '.');
        $dias_para_vencer = $fatura['dias_para_vencer'];
        
        $resposta .= "• Fatura #{$fatura['id']} - R$ $valor\n";
        $resposta .= "  Vence em {$fatura['vencimento_formatado']} (em {$dias_para_vencer} dias)\n";
        
        if ($fatura['url_fatura']) {
            $resposta .= "  💳 Pagar: {$fatura['url_fatura']}\n";
        }
        $resposta .= "\n";
    }
    
    // Resumo final - APENAS faturas vencidas no total em aberto
    if ($total_vencidas > 0) {
        $valor_total_vencidas_formatado = number_format($valor_total_vencidas, 2, ',', '.');
        $resposta .= "📊 *Resumo Geral:*\n";
        $resposta .= "💰 Valor total em aberto: R$ $valor_total_vencidas_formatado\n\n";
    }
    
    // Mensagem final simpática
    if ($total_vencidas > 0) {
        $resposta .= "⚠️ *Atenção:* Você tem faturas vencidas. Para evitar juros e multas, recomendamos o pagamento o quanto antes.\n\n";
    }
    
    $resposta .= "💡 *Dica:* Mantenha suas faturas em dia para aproveitar todos os nossos serviços sem interrupções!\n\n";
    $resposta .= "🤖 *Esta é uma mensagem automática*\n";
    $resposta .= "📞 Para conversar com nossa equipe, entre em contato: *47 997309525*";
    
    return $resposta;
}

/**
 * SINCRONIZAÇÃO INDIVIDUAL: Verifica e atualiza faturas do cliente com Asaas
 */
function sincronizarFaturasClienteAsaas($cliente_id, $mysqli) {
    try {
        // 1. Buscar dados do cliente (incluindo asaas_id)
        $sql_cliente = "SELECT asaas_id, nome FROM clientes WHERE id = $cliente_id LIMIT 1";
        $result_cliente = $mysqli->query($sql_cliente);
        
        if (!$result_cliente || $result_cliente->num_rows == 0) {
            return ['success' => false, 'message' => 'Cliente não encontrado'];
        }
        
        $cliente = $result_cliente->fetch_assoc();
        $asaas_customer_id = $cliente['asaas_id'];
        
        if (!$asaas_customer_id) {
            return ['success' => false, 'message' => 'Cliente sem ID do Asaas'];
        }
        
        // 2. Buscar faturas no banco local
        $sql_local = "SELECT 
                        asaas_payment_id,
                        valor,
                        status,
                        vencimento,
                        url_fatura
                    FROM cobrancas 
                    WHERE cliente_id = $cliente_id 
                    AND status IN ('PENDING', 'OVERDUE')
                    ORDER BY vencimento ASC";
        
        $result_local = $mysqli->query($sql_local);
        $faturas_locais = [];
        
        if ($result_local) {
            while ($row = $result_local->fetch_assoc()) {
                $faturas_locais[$row['asaas_payment_id']] = $row;
            }
        }
        
        // 3. Buscar faturas no Asaas
        $faturas_asaas = buscarFaturasAsaas($asaas_customer_id);
        
        if (!$faturas_asaas['success']) {
            return $faturas_asaas; // Retorna erro da API
        }
        
        $faturas_asaas_data = $faturas_asaas['data'];
        
        // 4. Comparar e sincronizar
        $atualizacoes = 0;
        $novas_faturas = 0;
        
        foreach ($faturas_asaas_data as $fatura_asaas) {
            $asaas_payment_id = $fatura_asaas['id'];
            $status_asaas = $fatura_asaas['status'];
            $valor_asaas = $fatura_asaas['value'];
            $vencimento_asaas = $fatura_asaas['dueDate'];
            $url_asaas = $fatura_asaas['invoiceUrl'] ?? '';
            
            // Verificar se fatura existe localmente
            if (isset($faturas_locais[$asaas_payment_id])) {
                $fatura_local = $faturas_locais[$asaas_payment_id];
                
                // Verificar se precisa atualizar
                if ($fatura_local['status'] !== $status_asaas || 
                    $fatura_local['valor'] != $valor_asaas ||
                    $fatura_local['vencimento'] !== $vencimento_asaas ||
                    $fatura_local['url_fatura'] !== $url_asaas) {
                    
                    // Atualizar fatura local
                    $sql_update = "UPDATE cobrancas SET 
                                    status = '" . $mysqli->real_escape_string($status_asaas) . "',
                                    valor = " . floatval($valor_asaas) . ",
                                    vencimento = '" . $mysqli->real_escape_string($vencimento_asaas) . "',
                                    url_fatura = '" . $mysqli->real_escape_string($url_asaas) . "',
                                    data_atualizacao = NOW()
                                  WHERE asaas_payment_id = '" . $mysqli->real_escape_string($asaas_payment_id) . "'";
                    
                    if ($mysqli->query($sql_update)) {
                        $atualizacoes++;
                    }
                }
            } else {
                // Nova fatura - inserir no banco local
                $sql_insert = "INSERT INTO cobrancas (
                                asaas_payment_id, 
                                cliente_id, 
                                valor, 
                                status, 
                                vencimento, 
                                url_fatura, 
                                data_criacao, 
                                data_atualizacao
                              ) VALUES (
                                '" . $mysqli->real_escape_string($asaas_payment_id) . "',
                                $cliente_id,
                                " . floatval($valor_asaas) . ",
                                '" . $mysqli->real_escape_string($status_asaas) . "',
                                '" . $mysqli->real_escape_string($vencimento_asaas) . "',
                                '" . $mysqli->real_escape_string($url_asaas) . "',
                                NOW(),
                                NOW()
                              )";
                
                if ($mysqli->query($sql_insert)) {
                    $novas_faturas++;
                }
            }
        }
        
        return [
            'success' => true,
            'message' => "Sincronização concluída: $atualizacoes atualizações, $novas_faturas novas faturas",
            'atualizacoes' => $atualizacoes,
            'novas_faturas' => $novas_faturas
        ];
        
    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => 'Erro na sincronização: ' . $e->getMessage()
        ];
    }
}

/**
 * Busca faturas de um cliente específico no Asaas
 */
function buscarFaturasAsaas($asaas_customer_id) {
    try {
        // Buscar chave da API
        global $mysqli;
        $config = $mysqli->query("SELECT valor FROM configuracoes WHERE chave = 'asaas_api_key' LIMIT 1")->fetch_assoc();
        $api_key = $config ? $config['valor'] : ASAAS_API_KEY;
        
        // Fazer requisição para API do Asaas
        $ch = curl_init();
        $url = ASAAS_API_URL . "/payments?customer=" . urlencode($asaas_customer_id) . "&limit=100";
        
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'access_token: ' . $api_key
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            return [
                'success' => false,
                'message' => 'Erro de conexão: ' . $error
            ];
        }
        
        if ($http_code !== 200) {
            return [
                'success' => false,
                'message' => "Erro HTTP $http_code: $response"
            ];
        }
        
        $data = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return [
                'success' => false,
                'message' => 'Erro ao decodificar resposta: ' . json_last_error_msg()
            ];
        }
        
        return [
            'success' => true,
            'data' => $data['data'] ?? []
        ];
        
    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => 'Erro na requisição: ' . $e->getMessage()
        ];
    }
}

/**
 * Traduz status da fatura
 */
function traduzirStatus($status) {
    $statusMap = [
        'PENDING' => 'Aguardando pagamento',
        'OVERDUE' => 'Vencida',
        'RECEIVED' => 'Paga',
        'CONFIRMED' => 'Confirmada',
        'CANCELLED' => 'Cancelada'
    ];
    return $statusMap[$status] ?? $status;
}
?> 