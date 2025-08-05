<?php
/**
 * CONFIGURAÇÃO AJUSTADA PARA VPS ATUAL
 * Gerado automaticamente em 2025-08-04 21:37:22
 */

// Configurações da VPS baseadas no estado atual
define('VPS_3000_FUNCIONANDO', true);
define('VPS_3001_FUNCIONANDO', true);
define('VPS_3000_READY', false);
define('VPS_3001_READY', true);

// URLs das VPS
define('VPS_3000_URL', 'http://212.85.11.238:3000');
define('VPS_3001_URL', 'http://212.85.11.238:3001');

// Endpoints funcionais
$ENDPOINTS_FUNCIONAIS = [
    '3000' => [
        '/status' => true,
        '/webhook/config' => true
    ],
    '3001' => [
        '/status' => true
    ]
];

// Função para obter URL da VPS baseada na porta
function getVpsUrl($porta) {
    if ($porta == '3000' || $porta == 3000) {
        return VPS_3000_FUNCIONANDO ? VPS_3000_URL : null;
    } elseif ($porta == '3001' || $porta == 3001) {
        return VPS_3001_FUNCIONANDO ? VPS_3001_URL : null;
    }
    return null;
}

// Função para verificar se endpoint funciona
function endpointFunciona($porta, $endpoint) {
    global $ENDPOINTS_FUNCIONAIS;
    return isset($ENDPOINTS_FUNCIONAIS[$porta][$endpoint]) && $ENDPOINTS_FUNCIONAIS[$porta][$endpoint];
}

// Função para obter VPS de fallback
function getVpsFallback() {
    if (VPS_3001_FUNCIONANDO) {
        return VPS_3001_URL;
    } elseif (VPS_3000_FUNCIONANDO) {
        return VPS_3000_URL;
    }
    return null;
}
?>