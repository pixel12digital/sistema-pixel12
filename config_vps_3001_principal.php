<?php
/**
 * CONFIGURAÇÃO OTIMIZADA - VPS 3001 FUNCIONANDO
 * Gerado automaticamente em 2025-08-04 21:41:32
 * 
 * Esta configuração usa a VPS 3001 como principal, que está funcionando perfeitamente
 */

// VPS Principal (funcionando)
define('VPS_PRINCIPAL_URL', 'http://212.85.11.238:3001');
define('VPS_PRINCIPAL_READY', true);
define('VPS_PRINCIPAL_PORT', '3001');

// VPS Secundária (com problemas)
define('VPS_SECUNDARIA_URL', 'http://212.85.11.238:3000');
define('VPS_SECUNDARIA_READY', false);
define('VPS_SECUNDARIA_PORT', '3000');

// Endpoints funcionais na VPS 3001
$ENDPOINTS_VPS_3001 = [
    '/status' => true
];

// Função para obter URL da VPS principal
function getVpsPrincipal() {
    return VPS_PRINCIPAL_URL;
}

// Função para obter URL da VPS secundária (fallback)
function getVpsSecundaria() {
    return VPS_SECUNDARIA_URL;
}

// Função para verificar se endpoint funciona na VPS principal
function endpointFuncionaVps3001($endpoint) {
    global $ENDPOINTS_VPS_3001;
    return isset($ENDPOINTS_VPS_3001[$endpoint]) && $ENDPOINTS_VPS_3001[$endpoint];
}

// Função para obter URL baseada na porta (com fallback)
function getVpsUrl($porta) {
    if ($porta == '3001' || $porta == 3001) {
        return VPS_PRINCIPAL_URL;
    } elseif ($porta == '3000' || $porta == 3000) {
        // Se VPS 3000 não estiver funcionando, usar 3001
        return VPS_PRINCIPAL_URL;
    }
    return VPS_PRINCIPAL_URL; // Padrão para VPS principal
}

// Função para obter VPS de fallback
function getVpsFallback() {
    return VPS_PRINCIPAL_URL;
}

// Configurações específicas para o sistema
define('WHATSAPP_ROBOT_URL', VPS_PRINCIPAL_URL);
define('WHATSAPP_TIMEOUT', 10);

// Status das VPS
define('VPS_3000_FUNCIONANDO', false);
define('VPS_3001_FUNCIONANDO', true);
define('VPS_3000_READY', false);
define('VPS_3001_READY', true);
?>