<?php
/**
 * Endpoint Bridge: Sistema Financeiro → IA → Robô
 * Integra o sistema atual com o painel de IA
 */

header('Content-Type: application/json');
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../db.php';

// Receber dados da mensagem
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['from']) || !isset($input['message'])) {
    echo json_encode(['success' => false, 'error' => 'Dados incompletos']);
    exit;
}

$numero_cliente = $input['from'];
$mensagem = trim($input['message']);
$tipo_mensagem = $input['type'] ?? 'text';

try {
    // Extrair número do cliente (remover @c.us)
    $numero_limpo = str_replace('@c.us', '', $numero_cliente);
    $numero_limpo = str_replace('55', '', $numero_limpo);
    
    // Buscar cliente pelo número
    $sql = "SELECT id, nome, celular FROM clientes WHERE celular LIKE '%$numero_limpo%' LIMIT 1";
    $result = $mysqli->query($sql);
    
    if (!$result || $result->num_rows === 0) {
        // Cliente não encontrado - resposta padrão
        $resposta = gerarRespostaPadrao();
        echo json_encode([
            'success' => true,
            'resposta' => $resposta,
            'tipo' => 'cliente_nao_encontrado',
            'metodo' => 'padrao'
        ]);
        exit;
    }
    
    $cliente = $result->fetch_assoc();
    $cliente_id = $cliente['id'];
    
    // Verificar se cliente está sendo monitorado
    $monitorado = $mysqli->query("SELECT monitorado FROM clientes_monitoramento WHERE cliente_id = $cliente_id LIMIT 1")->fetch_assoc();
    
    if (!$monitorado || $monitorado['monitorado'] != 1) {
        // Cliente não monitorado - resposta padrão
        $resposta = gerarRespostaPadrao();
        echo json_encode([
            'success' => true,
            'resposta' => $resposta,
            'tipo' => 'cliente_nao_monitorado',
            'metodo' => 'padrao'
        ]);
        exit;
    }
    
    // Buscar dados do cliente para contexto
    $dados_contexto = buscarContextoCliente($cliente_id, $mysqli);
    
    // Verificar se IA está configurada
    $config_ia = verificarConfiguracaoIA();
    
    if ($config_ia['ativa']) {
        // Processar com IA
        $resposta_ia = processarComIA($cliente_id, $mensagem, $dados_contexto, $config_ia);
        
        if ($resposta_ia['success']) {
            // Salvar mensagem recebida
            salvarMensagem($cliente_id, $mensagem, 'recebido', $tipo_mensagem, $mysqli);
            
            echo json_encode([
                'success' => true,
                'resposta' => $resposta_ia['resposta'],
                'cliente_id' => $cliente_id,
                'tipo' => 'resposta_ia',
                'metodo' => 'ia',
                'dados_ia' => $resposta_ia['dados'] ?? null
            ]);
        } else {
            // Fallback para robô tradicional
            $resposta = processarComRoboTradicional($cliente_id, $mensagem, $mysqli);
            echo json_encode([
                'success' => true,
                'resposta' => $resposta,
                'cliente_id' => $cliente_id,
                'tipo' => 'resposta_fallback',
                'metodo' => 'robo_tradicional'
            ]);
        }
    } else {
        // Usar robô tradicional
        $resposta = processarComRoboTradicional($cliente_id, $mensagem, $mysqli);
        echo json_encode([
            'success' => true,
            'resposta' => $resposta,
            'cliente_id' => $cliente_id,
            'tipo' => 'resposta_robo',
            'metodo' => 'robo_tradicional'
        ]);
    }

} catch (Exception $e) {
    error_log("Erro ao processar mensagem com IA: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'metodo' => 'erro'
    ]);
}

/**
 * Gera resposta padrão para clientes não encontrados/monitorados
 */
function gerarRespostaPadrao() {
    return "Olá! Este é o contato financeiro da Pixel12 Digital.\n\n" .
           "Para consultar suas faturas, digite \"faturas\" ou \"consulta\"\n" .
           "Para links de pagamento, digite \"pagar\" ou \"pagamento\"\n" .
           "Para abrir um ticket de atendimento, digite \"atendente\"\n\n" .
           "Atenciosamente,\nEquipe Financeira Pixel12 Digital";
}

/**
 * Busca contexto completo do cliente para a IA
 */
function buscarContextoCliente($cliente_id, $mysqli) {
    // Dados básicos do cliente
    $cliente = $mysqli->query("SELECT * FROM clientes WHERE id = $cliente_id LIMIT 1")->fetch_assoc();
    
    // Cobranças do cliente
    $cobrancas = [];
    $result = $mysqli->query("SELECT * FROM cobrancas WHERE cliente_id = $cliente_id ORDER BY vencimento DESC LIMIT 10");
    while ($row = $result->fetch_assoc()) {
        $cobrancas[] = $row;
    }
    
    // Últimas mensagens
    $historico = [];
    $result = $mysqli->query("SELECT * FROM mensagens_comunicacao WHERE cliente_id = $cliente_id ORDER BY data_hora DESC LIMIT 5");
    while ($row = $result->fetch_assoc()) {
        $historico[] = $row;
    }
    
    return [
        'cliente' => $cliente,
        'cobrancas' => $cobrancas,
        'historico' => $historico,
        'timestamp' => date('Y-m-d H:i:s')
    ];
}

/**
 * Verifica se a IA está configurada e ativa
 */
function verificarConfiguracaoIA() {
    // Por enquanto, verificar arquivo de configuração
    // Futuramente: buscar do banco de dados
    $config_file = __DIR__ . '/../config_ia.json';
    
    if (file_exists($config_file)) {
        $config = json_decode(file_get_contents($config_file), true);
        return [
            'ativa' => $config['ativa'] ?? false,
            'url_api' => $config['url_api'] ?? '',
            'api_key' => $config['api_key'] ?? '',
            'modelo' => $config['modelo'] ?? 'assistente_financeiro'
        ];
    }
    
    return ['ativa' => false];
}

/**
 * Processa mensagem com IA
 */
function processarComIA($cliente_id, $mensagem, $contexto, $config) {
    $url_ia = $config['url_api'];
    $api_key = $config['api_key'];
    
    // Payload para a IA
    $payload = [
        'mensagem' => $mensagem,
        'contexto' => $contexto,
        'tipo' => 'financeiro',
        'cliente_id' => $cliente_id,
        'timestamp' => time()
    ];
    
    $ch = curl_init($url_ia);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $api_key
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($response && $http_code === 200) {
        $resultado = json_decode($response, true);
        if ($resultado && isset($resultado['resposta'])) {
            return [
                'success' => true,
                'resposta' => $resultado['resposta'],
                'dados' => $resultado
            ];
        }
    }
    
    return ['success' => false, 'error' => 'Falha na comunicação com IA'];
}

/**
 * Processa com robô tradicional (fallback)
 */
function processarComRoboTradicional($cliente_id, $mensagem, $mysqli) {
    $mensagem_lower = strtolower($mensagem);
    
    // Verificar palavras-chave tradicionais
    if (strpos($mensagem_lower, 'fatura') !== false || 
        strpos($mensagem_lower, 'consulta') !== false || 
        strpos($mensagem_lower, 'faturas') !== false) {
        
        return buscarFaturasCliente($cliente_id, $mysqli);
        
    } elseif (strpos($mensagem_lower, 'pagar') !== false || 
              strpos($mensagem_lower, 'pagamento') !== false) {
        
        return buscarLinksPagamento($cliente_id, $mysqli);
        
    } elseif (strpos($mensagem_lower, 'atendente') !== false || 
              strpos($mensagem_lower, 'humano') !== false ||
              strpos($mensagem_lower, 'ticket') !== false) {
        
        return abrirTicketAtendimento($cliente_id, $mensagem, $mysqli);
        
    } else {
        return "Olá! Como posso ajudá-lo?\n\n" .
               "Para consultar suas faturas, digite \"faturas\" ou \"consulta\"\n" .
               "Para links de pagamento, digite \"pagar\" ou \"pagamento\"\n" .
               "Para abrir um ticket de atendimento, digite \"atendente\"\n\n" .
               "Atenciosamente,\nEquipe Financeira Pixel12 Digital";
    }
}

/**
 * Salva mensagem no banco
 */
function salvarMensagem($cliente_id, $mensagem, $direcao, $tipo, $mysqli) {
    $mensagem_escaped = $mysqli->real_escape_string($mensagem);
    $data_hora = date('Y-m-d H:i:s');
    
    $sql = "INSERT INTO mensagens_comunicacao (canal_id, cliente_id, mensagem, tipo, data_hora, direcao, status) 
            VALUES (1, $cliente_id, '$mensagem_escaped', '$tipo', '$data_hora', '$direcao', 'recebido')";
    
    $mysqli->query($sql);
}

/**
 * Busca faturas do cliente (função do sistema atual)
 */
function buscarFaturasCliente($cliente_id, $mysqli) {
    // Implementar busca de faturas...
    return "Consultando suas faturas... (implementar busca real)";
}

/**
 * Busca links de pagamento (função do sistema atual) 
 */
function buscarLinksPagamento($cliente_id, $mysqli) {
    // Implementar busca de links...
    return "Aqui estão seus links de pagamento... (implementar busca real)";
}

/**
 * Abre ticket de atendimento (função do sistema atual)
 */
function abrirTicketAtendimento($cliente_id, $mensagem, $mysqli) {
    // Implementar abertura de ticket...
    return "Ticket aberto com sucesso... (implementar ticket real)";
}
?> 