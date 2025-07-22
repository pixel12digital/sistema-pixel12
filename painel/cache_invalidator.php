<?php
function invalidate_message_cache($cliente_id, $data = []) {
    $cache_dir = sys_get_temp_dir() . '/loja_virtual_cache/';
    $historico_cache = $cache_dir . 'historico_html_' . $cliente_id . '.cache';
    if (file_exists($historico_cache)) {
        unlink($historico_cache);
    }
    // Adicione aqui outros caches relacionados, se necessário
} 