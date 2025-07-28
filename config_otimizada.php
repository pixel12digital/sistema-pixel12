<?php
/**
 * CONFIGURAÇÃO OTIMIZADA PARA REDUZIR CONEXÕES
 */

// Configurações de polling otimizadas
define("POLLING_CONFIGURACOES", 60000);    // 60 segundos
define("POLLING_WHATSAPP", 30000);         // 30 segundos
define("POLLING_MONITORAMENTO", 60000);    // 60 segundos
define("POLLING_CHAT", 60000);             // 60 segundos
define("POLLING_COMUNICACAO", 120000);     // 2 minutos

// Configurações de cache
define("CACHE_ENABLED", true);
define("CACHE_TTL", 300);                  // 5 minutos
define("CACHE_MAX_SIZE", "50MB");

// Configurações de conexão
define("DB_PERSISTENT", true);
define("DB_TIMEOUT", 10);
define("DB_MAX_RETRIES", 3);

echo "✅ Configuração otimizada carregada\n";
?>