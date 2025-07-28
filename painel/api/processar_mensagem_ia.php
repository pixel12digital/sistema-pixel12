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
    
    // Buscar cliente pelo número
    $sql = "SELECT id, nome, contact_name, celular, telefone FROM clientes 
            WHERE REPLACE(REPLACE(REPLACE(REPLACE(celular, '(', ''), ')', ''), '-', ''), ' ', '') LIKE '%$numero_limpo%' 
            OR REPLACE(REPLACE(REPLACE(REPLACE(telefone, '(', ''), ')', ''), '-', ''), ' ', '') LIKE '%$numero_limpo%'
            LIMIT 1";
    
    $result = $mysqli->query($sql);
    if ($result && $result->num_rows > 0) {
        $cliente = $result->fetch_assoc();
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
    'fatura' => ['fatura', 'boleto', 'conta', 'pagamento', 'vencimento', 'pagar'],
    'plano' => ['plano', 'pacote', 'serviço', 'assinatura', 'mensalidade'],
    'suporte' => ['suporte', 'ajuda', 'problema', 'erro', 'não funciona', 'bug'],
    'comercial' => ['comercial', 'venda', 'preço', 'orçamento', 'proposta', 'site'],
    'cpf' => ['cpf', 'documento', 'identificação', 'cadastro'],
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
            $resposta = "Olá! Vejo que você tem dúvidas sobre faturas. 📋\n\n";
            $resposta .= "Para verificar suas faturas, preciso do seu CPF. ";
            $resposta .= "Pode me informar o número do seu CPF?";
        } else {
            $resposta = "Olá! Para verificar suas faturas, preciso do seu CPF. ";
            $resposta .= "Pode me informar o número do seu CPF?";
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
        // Verificar se é um CPF válido
        $cpf_limpo = preg_replace('/\D/', '', $texto);
        if (strlen($cpf_limpo) === 11) {
            // Buscar cliente pelo CPF
            $sql_cpf = "SELECT id, nome, contact_name FROM clientes WHERE cpf_cnpj = '$cpf_limpo' LIMIT 1";
            $result_cpf = $mysqli->query($sql_cpf);
            
            if ($result_cpf && $result_cpf->num_rows > 0) {
                $cliente_cpf = $result_cpf->fetch_assoc();
                $resposta = "Olá {$cliente_cpf['contact_name']}! 👋\n\n";
                $resposta .= "Encontrei seu cadastro! Como posso ajudá-lo hoje?\n\n";
                $resposta .= "📋 *Opções disponíveis:*\n";
                $resposta .= "• Verificar faturas\n";
                $resposta .= "• Informações do plano\n";
                $resposta .= "• Suporte técnico\n";
                $resposta .= "• Atendimento comercial";
            } else {
                $resposta = "CPF não encontrado em nossa base de dados. 😕\n\n";
                $resposta .= "Para atendimento, entre em contato através do número: *47 997309525*";
            }
        } else {
            $resposta = "Por favor, informe um CPF válido (11 dígitos).";
        }
        break;
        
    case 'saudacao':
        if ($cliente_id) {
            $nome_cliente = $cliente['contact_name'] ?: $cliente['nome'];
            $resposta = "Olá $nome_cliente! 👋\n\n";
            $resposta .= "Como posso ajudá-lo hoje?\n\n";
            $resposta .= "📋 *Opções disponíveis:*\n";
            $resposta .= "• Verificar faturas\n";
            $resposta .= "• Informações do plano\n";
            $resposta .= "• Suporte técnico\n";
            $resposta .= "• Atendimento comercial";
        } else {
            $resposta = "Olá! 👋\n\n";
            $resposta .= "Este é o canal da *Pixel12Digital* exclusivo para tratar de assuntos financeiros.\n\n";
            $resposta .= "📞 *Para atendimento comercial ou suporte técnico:*\n";
            $resposta .= "Entre em contato através do número: *47 997309525*\n\n";
            $resposta .= "📋 *Para informações sobre seu plano, faturas, etc.:*\n";
            $resposta .= "Por favor, digite seu *CPF* para localizar seu cadastro.";
        }
        break;
        
    default:
        if ($cliente_id) {
            $nome_cliente = $cliente['contact_name'] ?: $cliente['nome'];
            $resposta = "Olá $nome_cliente! 👋\n\n";
            $resposta .= "Como posso ajudá-lo hoje?\n\n";
            $resposta .= "📋 *Opções disponíveis:*\n";
            $resposta .= "• Verificar faturas\n";
            $resposta .= "• Informações do plano\n";
            $resposta .= "• Suporte técnico\n";
            $resposta .= "• Atendimento comercial";
        } else {
            $resposta = "Olá! 👋\n\n";
            $resposta .= "Este é o canal da *Pixel12Digital* exclusivo para tratar de assuntos financeiros.\n\n";
            $resposta .= "📞 *Para atendimento comercial ou suporte técnico:*\n";
            $resposta .= "Entre em contato através do número: *47 997309525*\n\n";
            $resposta .= "📋 *Para informações sobre seu plano, faturas, etc.:*\n";
            $resposta .= "Por favor, digite seu *CPF* para localizar seu cadastro.";
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
?> 