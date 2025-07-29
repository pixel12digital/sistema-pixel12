<?php
/**
 * CONFIGURAÇÃO OTIMIZADA PARA REDUZIR CONEXÕES
 * 
 * Configurações de emergência para resolver problema de conexões excessivas
 */

// Reduzir drasticamente o polling
if (!defined("POLLING_CONFIGURACOES")) define("POLLING_CONFIGURACOES", 300000);   // 5 minutos
if (!defined("POLLING_WHATSAPP")) define("POLLING_WHATSAPP", 300000);             // 5 minutos  
if (!defined("POLLING_MONITORAMENTO")) define("POLLING_MONITORAMENTO", 600000);   // 10 minutos
if (!defined("POLLING_CHAT")) define("POLLING_CHAT", 300000);                     // 5 minutos
if (!defined("POLLING_COMUNICACAO")) define("POLLING_COMUNICACAO", 600000);       // 10 minutos

// Configurações de cache agressivo
if (!defined("CACHE_TTL_DEFAULT")) define("CACHE_TTL_DEFAULT", 1800); // 30 minutos
if (!defined("ENABLE_CACHE")) define("ENABLE_CACHE", true);

// Timeout de conexão reduzido
if (!defined("DB_CONNECT_TIMEOUT")) define("DB_CONNECT_TIMEOUT", 5);
if (!defined("DB_READ_TIMEOUT")) define("DB_READ_TIMEOUT", 10);
?>