<?php
/**
 * CONFIGURAÇÃO ESPECÍFICA - CANAL COMERCIAL
 * Porta: 3001 | Canal ID: 37
 */

// ===== CONFIGURAÇÕES DO CANAL =====
define('CANAL_ID', 3001);                    // ID do canal
define('CANAL_NUMERO', 37);                  // Número do canal
define('CANAL_NOME', 'Comercial');           // Nome do canal
define('CANAL_TIPO', 'whatsapp');            // Tipo de canal
define('CANAL_DESCRICAO', 'Canal para atendimento comercial');

// ===== CONFIGURAÇÕES DO WHATSAPP =====
define('CANAL_WHATSAPP_NUMERO', '4797309525');  // Número do WhatsApp
define('CANAL_WHATSAPP_COMPLETO', '4797309525@c.us'); // Formato para API

// ===== CONFIGURAÇÕES DE BANCO DE DADOS =====
define('CANAL_USAR_BANCO_PRINCIPAL', false); // Usa banco separado
define('CANAL_BANCO_NOME', 'u342734079_wts_com_pixel'); // Nome do banco correto
define('CANAL_BANCO_HOST', 'srv1607.hstgr.io');     // Host do banco
define('CANAL_BANCO_USER', 'u342734079_wts_com_pixel'); // Usuário correto
define('CANAL_BANCO_PASS', 'Los@ngo#081081'); // Senha do banco

// ===== CONFIGURAÇÕES DE AUTOMAÇÃO =====
define('CANAL_AUTOMACAO_ATIVA', true);       // Automação ativa
define('CANAL_RESPOSTA_PADRAO', true);       // Sempre responder
define('CANAL_DIRECIONAR_CONTATO', true);    // Direcionar para contato direto

// ===== CONFIGURAÇÕES DE MENSAGENS =====
define('CANAL_CONTATO_DIRETO', '47 97309525'); // Número para contato direto
define('CANAL_PALAVRA_CHAVE_PRINCIPAL', 'ajuda'); // Palavra-chave principal

// ===== CONFIGURAÇÕES DE LOG =====
define('CANAL_LOG_ATIVO', true);             // Logs ativos
define('CANAL_LOG_PREFIXO', '[CANAL_COMERCIAL]'); // Prefixo dos logs

// ===== CONFIGURAÇÕES DE WEBHOOK =====
define('CANAL_WEBHOOK_URL', '/api/webhook_canal_37.php'); // URL do webhook específico
define('CANAL_WEBHOOK_ATIVO', true);         // Webhook ativo

// ===== CONFIGURAÇÕES DE NOTIFICAÇÃO =====
define('CANAL_NOTIFICACAO_PUSH', true);      // Notificações push ativas
define('CANAL_NOTIFICACAO_EMAIL', false);    // Notificações por email

// ===== CONFIGURAÇÕES DE SEGURANÇA =====
define('CANAL_VERIFICAR_CLIENTE', true);     // Verificar se cliente existe
define('CANAL_LIMITE_MENSAGENS', 50);        // Limite de mensagens por hora
define('CANAL_BLOQUEAR_SPAM', true);         // Bloquear spam

// ===== CONFIGURAÇÕES DE BACKUP =====
define('CANAL_BACKUP_ATIVO', true);          // Backup automático
define('CANAL_BACKUP_FREQUENCIA', 'daily');  // Frequência do backup

// ===== INFORMAÇÕES DE MANUTENÇÃO =====
define('CANAL_CRIADO_EM', '2025-07-31');     // Data de criação
define('CANAL_ULTIMA_ATUALIZACAO', '2025-07-31'); // Última atualização
define('CANAL_VERSIONE', '1.0.0');           // Versão do canal
define('CANAL_RESPONSAVEL', 'Pixel12Digital'); // Responsável pelo canal

// ===== CONFIGURAÇÕES DE DESENVOLVIMENTO =====
define('CANAL_DEBUG_MODE', false);           // Modo debug
define('CANAL_TESTE_MODE', false);           // Modo teste
define('CANAL_DEV_MODE', false);             // Modo desenvolvimento

/**
 * Função para obter configuração do canal
 */
function getCanalConfig($chave) {
    return defined($chave) ? constant($chave) : null;
}

/**
 * Função para verificar se canal está ativo
 */
function isCanalAtivo() {
    return CANAL_WEBHOOK_ATIVO && CANAL_AUTOMACAO_ATIVA;
}

/**
 * Função para obter informações do canal
 */
function getCanalInfo() {
    return [
        'id' => CANAL_ID,
        'numero' => CANAL_NUMERO,
        'nome' => CANAL_NOME,
        'tipo' => CANAL_TIPO,
        'whatsapp' => CANAL_WHATSAPP_NUMERO,
        'ativo' => isCanalAtivo(),
        'versao' => CANAL_VERSIONE,
        'responsavel' => CANAL_RESPONSAVEL
    ];
}

/**
 * Função para conectar ao banco específico do canal
 */
function conectarBancoCanal() {
    if (CANAL_USAR_BANCO_PRINCIPAL) {
        // Usar conexão principal (como o canal financeiro)
        require_once __DIR__ . '/../../config.php';
        return $mysqli;
    } else {
        // Conectar ao banco específico do canal
        $mysqli = new mysqli(
            CANAL_BANCO_HOST,
            CANAL_BANCO_USER,
            CANAL_BANCO_PASS,
            CANAL_BANCO_NOME
        );
        
        if ($mysqli->connect_error) {
            error_log(CANAL_LOG_PREFIXO . " ❌ Erro ao conectar ao banco: " . $mysqli->connect_error);
            return null;
        }
        
        return $mysqli;
    }
}

/**
 * Função para salvar mensagem no banco do canal
 */
function salvarMensagemCanal($dados) {
    $mysqli = conectarBancoCanal();
    if (!$mysqli) {
        error_log(CANAL_LOG_PREFIXO . " ❌ Não foi possível conectar ao banco");
        return false;
    }
    
    $from = $mysqli->real_escape_string($dados['from']);
    $body = $mysqli->real_escape_string($dados['body']);
    $timestamp = isset($dados['timestamp']) ? intval($dados['timestamp']) : time();
    $data_hora = date('Y-m-d H:i:s', $timestamp);
    
    // Buscar cliente pelo número
    $numero_limpo = preg_replace('/\D/', '', $from);
    $cliente_id = null;
    
    // Tentar diferentes formatos do número
    $formatos_numero = [
        $numero_limpo,
        substr($numero_limpo, 2),
        substr($numero_limpo, 0, 2) . '9' . substr($numero_limpo, 2),
        substr($numero_limpo, 2, 2) . '9' . substr($numero_limpo, 4),
        substr($numero_limpo, -10)
    ];
    
    foreach ($formatos_numero as $formato) {
        $formato_escaped = $mysqli->real_escape_string($formato);
        $result = $mysqli->query("SELECT id FROM clientes WHERE celular LIKE '%$formato_escaped%' OR telefone LIKE '%$formato_escaped%' LIMIT 1");
        
        if ($result && $result->num_rows > 0) {
            $cliente = $result->fetch_assoc();
            $cliente_id = intval($cliente['id']);
            break;
        }
    }
    
    if ($cliente_id) {
        // Cliente existe, salvar normalmente
        $sql = "INSERT INTO mensagens_comunicacao (canal_id, cliente_id, mensagem, tipo, data_hora, direcao, status) 
                VALUES (" . CANAL_NUMERO . ", $cliente_id, '$body', 'texto', '$data_hora', 'recebido', 'recebido')";
    } else {
        // Cliente não existe, salvar em pendentes
        $sql = "INSERT INTO mensagens_pendentes (canal_id, numero, mensagem, tipo, data_hora) 
                VALUES (" . CANAL_NUMERO . ", '$numero_limpo', '$body', 'texto', '$data_hora')";
    }
    
    $result = $mysqli->query($sql);
    $mysqli->close();
    
    if ($result) {
        error_log(CANAL_LOG_PREFIXO . " ✅ Mensagem salva com sucesso");
        return true;
    } else {
        error_log(CANAL_LOG_PREFIXO . " ❌ Erro ao salvar mensagem: " . $mysqli->error);
        return false;
    }
}
?> 