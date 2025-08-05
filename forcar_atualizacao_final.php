<?php
// Script para forçar atualização das mensagens
require_once 'config.php';

$cliente_id = 4296;

// Limpar cache
$cache_dir = __DIR__ . '/cache/';
if (is_dir($cache_dir)) {
    $files = glob($cache_dir . '*.cache');
    foreach ($files as $file) {
        unlink($file);
    }
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