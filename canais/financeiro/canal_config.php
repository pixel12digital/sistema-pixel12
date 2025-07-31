<?php
/**
 * CONFIGURAÇÃO ESPECÍFICA - CANAL FINANCEIRO
 * 
 * Este arquivo contém as configurações específicas do canal financeiro
 * que utiliza o banco de dados principal da aplicação
 */

// ===== CONFIGURAÇÕES DO CANAL =====
define('CANAL_ID', 3000);                    // ID interno do canal
define('CANAL_NUMERO', 36);                  // Número do canal no sistema
define('CANAL_NOME', 'Financeiro');          // Nome do canal
define('CANAL_TIPO', 'whatsapp');            // Tipo de canal
define('CANAL_DESCRICAO', 'Canal exclusivo para cobranças automatizadas');

// ===== CONFIGURAÇÕES DO WHATSAPP =====
define('CANAL_WHATSAPP_NUMERO', '47997309525');  // Número do WhatsApp (sem código do país)
define('CANAL_WHATSAPP_COMPLETO', '5547997309525@c.us'); // Número completo para API

// ===== CONFIGURAÇÕES DE BANCO DE DADOS =====
// O canal financeiro utiliza o banco principal da aplicação
define('CANAL_USAR_BANCO_PRINCIPAL', true);  // Usa o banco principal
define('CANAL_BANCO_NOME', 'pixel12digital'); // Nome do banco principal

// ===== CONFIGURAÇÕES DE AUTOMAÇÃO =====
define('CANAL_AUTOMACAO_ATIVA', true);       // Automação ativa
define('CANAL_RESPOSTA_PADRAO', true);       // Sempre responder
define('CANAL_DIRECIONAR_CONTATO', true);    // Direcionar para contato direto

// ===== CONFIGURAÇÕES DE MENSAGENS =====
define('CANAL_CONTATO_DIRETO', '47 997309525'); // Número para contato direto
define('CANAL_PALAVRA_CHAVE_FATURAS', 'faturas'); // Palavra-chave para consulta de faturas

// ===== CONFIGURAÇÕES DE LOG =====
define('CANAL_LOG_ATIVO', true);             // Logs ativos
define('CANAL_LOG_PREFIXO', '[CANAL_FINANCEIRO]'); // Prefixo dos logs

// ===== CONFIGURAÇÕES DE WEBHOOK =====
define('CANAL_WEBHOOK_URL', '/api/webhook_whatsapp.php'); // URL do webhook atual
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
?> 