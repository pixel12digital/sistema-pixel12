<?php
/**
 * Sistema de invalidação de cache centralizado
 */

class CacheInvalidator {
    
    /**
     * Invalida cache quando nova mensagem é recebida
     */
    public function onNewMessage($canal_id, $cliente_numero = null) {
        if ($cliente_numero) {
            invalidate_message_cache($cliente_numero);
        }
        
        // Invalidar caches do canal
        if (function_exists('cache_forget')) {
            cache_forget("canal_mensagens_{$canal_id}");
            cache_forget("conversas_recentes");
        }
        
        error_log("[CACHE_INVALIDATOR] Cache invalidado para nova mensagem - Canal: $canal_id");
    }
    
    /**
     * Invalida cache quando cliente é atualizado
     */
    public function onClientUpdate($cliente_id) {
        invalidate_client_cache($cliente_id);
    }
    
    /**
     * Invalida cache de conversas
     */
    public function onConversationUpdate() {
        invalidate_conversations_cache();
    }
    
    /**
     * Limpa todos os caches
     */
    public function clearAll() {
        clear_all_cache();
    }
}

/**
 * Invalida cache de mensagens de um cliente específico
 */
function invalidate_message_cache($cliente_id) {
    if (function_exists('cache_forget')) {
        // Invalidar caches específicos do cliente
        cache_forget("mensagens_{$cliente_id}");
        cache_forget("historico_html_{$cliente_id}");
        cache_forget("mensagens_html_{$cliente_id}");
        cache_forget("conv_nao_lidas_{$cliente_id}");
        
        // Invalidar cache global de conversas para atualizar lista
        cache_forget("conversas_recentes");
        cache_forget("conversas_nao_lidas");
        cache_forget("total_mensagens_nao_lidas");
        
        // Log para debug
        error_log("[CACHE] Cache invalidado para cliente {$cliente_id} e lista de conversas");
    }
}

/**
 * Invalida cache quando cliente é atualizado
 */
function invalidate_client_cache($cliente_id) {
    if (function_exists('cache_forget')) {
        cache_forget("cliente_{$cliente_id}");
        cache_forget("conversas_recentes");
        
        error_log("[CACHE] Cache invalidado para dados do cliente {$cliente_id}");
    }
}

/**
 * Invalida cache global de conversas
 */
function invalidate_conversations_cache() {
    if (function_exists('cache_forget')) {
        cache_forget("conversas_recentes");
        cache_forget("conversas_nao_lidas");
        cache_forget("total_mensagens_nao_lidas");
        
        error_log("[CACHE] Cache global de conversas invalidado");
    }
}

/**
 * Limpa todos os caches (usar com moderação)
 */
function clear_all_cache() {
    if (function_exists('cache_clear')) {
        cache_clear();
        error_log("[CACHE] Todos os caches foram limpos");
    }
}
?> 