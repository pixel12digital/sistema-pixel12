<?php
require_once 'config.php';
require_once 'painel/db.php';

echo "=== ATUALIZAÇÃO DEFINITIVA DO NÚMERO KLYSMAN ===\n\n";

// Verificar número atual
$res = $mysqli->query("SELECT celular FROM clientes WHERE id = 264");
if ($res && $row = $res->fetch_assoc()) {
    echo "Número atual: " . $row['celular'] . "\n";
}

// Atualizar para o número correto
$novo_numero = '4797146908';
$sql = "UPDATE clientes SET 
        celular = '$novo_numero',
        celular_editado_manual = 1,
        data_ultima_edicao_manual = NOW()
        WHERE id = 264";

if ($mysqli->query($sql)) {
    echo "✅ Número atualizado com sucesso!\n";
    echo "Novo número: $novo_numero\n";
    echo "Editado manualmente: 1\n";
    echo "Data da edição: " . date('Y-m-d H:i:s') . "\n\n";
    
    // Testar formatação
    function ajustarNumeroWhatsapp($numero) {
        $numero = preg_replace('/\D/', '', $numero);
        if (strpos($numero, '55') === 0) {
            $numero = substr($numero, 2);
        }
        if (strlen($numero) < 10) {
            return null;
        }
        $ddd = substr($numero, 0, 2);
        $telefone = substr($numero, 2);
        if (strlen($telefone) === 9 && substr($telefone, 0, 1) === '9') {
            // Manter como está
        } elseif (strlen($telefone) === 8) {
            $telefone = '9' . $telefone;
        } elseif (strlen($telefone) === 7) {
            $telefone = '9' . $telefone;
        } elseif (strlen($telefone) > 9) {
            $telefone = substr($telefone, -9);
        }
        if (strlen($telefone) !== 9) {
            return null;
        }
        return '55' . $ddd . $telefone;
    }
    
    $numero_formatado = ajustarNumeroWhatsapp($novo_numero);
    echo "Número formatado para WhatsApp: $numero_formatado\n";
    
} else {
    echo "❌ Erro ao atualizar: " . $mysqli->error . "\n";
}

echo "\n=== FIM DA ATUALIZAÇÃO ===\n";
?> 