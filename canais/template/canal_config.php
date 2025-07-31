<?php
/**
 * TEMPLATE DE CONFIGURAÇÃO - NOVO CANAL
 * 
 * Este arquivo é um template para criar novos canais
 * Copie este arquivo e renomeie para o novo canal
 */

// ===== CONFIGURAÇÕES DO CANAL =====
define('CANAL_ID', 3001);                    // ID interno do canal (incrementar)
define('CANAL_NUMERO', 37);                  // Número do canal no sistema (incrementar)
define('CANAL_NOME', 'Comercial');           // Nome do canal
define('CANAL_TIPO', 'whatsapp');            // Tipo de canal
define('CANAL_DESCRICAO', 'Canal para atendimento comercial');

// ===== CONFIGURAÇÕES DO WHATSAPP =====
define('CANAL_WHATSAPP_NUMERO', '47999999999');  // Número do WhatsApp (sem código do país)
define('CANAL_WHATSAPP_COMPLETO', '5547999999999@c.us'); // Número completo para API

// ===== CONFIGURAÇÕES DE BANCO DE DADOS =====
// Novos canais usam bancos separados para otimizar performance
define('CANAL_USAR_BANCO_PRINCIPAL', false); // Usa banco separado
define('CANAL_BANCO_NOME', 'pixel12digital_comercial'); // Nome do banco específico
define('CANAL_BANCO_HOST', 'localhost');     // Host do banco
define('CANAL_BANCO_USER', 'pixel12digital'); // Usuário do banco
define('CANAL_BANCO_PASS', 'SUA_SENHA_AQUI'); // Senha do banco

// ===== CONFIGURAÇÕES DE AUTOMAÇÃO =====
define('CANAL_AUTOMACAO_ATIVA', true);       // Automação ativa
define('CANAL_RESPOSTA_PADRAO', true);       // Sempre responder
define('CANAL_DIRECIONAR_CONTATO', true);    // Direcionar para contato direto

// ===== CONFIGURAÇÕES DE MENSAGENS =====
define('CANAL_CONTATO_DIRETO', '47 999999999'); // Número para contato direto
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
?> 