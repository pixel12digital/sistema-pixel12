<?php
// Script para forçar atualização das mensagens
require_once 'config.php';

$cliente_id = 4296;

// Limpar cache
$cache_file = __DIR__ . '/cache/' . md5("mensagens_{$cliente_id}") . '.cache';
if (file_exists($cache_file)) {
    unlink($cache_file);
    echo "Cache removido<br>";
}

// Forçar atualização
echo "<script>
    if (typeof carregarMensagensCliente === 'function') {
        carregarMensagensCliente(4296, true);
        console.log('Mensagens recarregadas forçadamente');
    } else {
        console.log('Função carregarMensagensCliente não encontrada');
    }
</script>";

echo "✅ Força atualização aplicada para cliente ID: $cliente_id<br>";
?>