<?php
require_once 'config.php';
require_once 'painel/db.php';

echo "=== VERIFICAÇÃO DO CLIENTE KLYSMAN ===\n\n";

// Verificar dados do cliente
$res = $mysqli->query("SELECT id, nome, celular, celular_editado_manual, data_ultima_edicao_manual FROM clientes WHERE id = 264");
if ($res && $row = $res->fetch_assoc()) {
    echo "Cliente Klysman:\n";
    echo "ID: " . $row['id'] . "\n";
    echo "Nome: " . $row['nome'] . "\n";
    echo "Celular: " . $row['celular'] . "\n";
    echo "Editado manualmente: " . $row['celular_editado_manual'] . "\n";
    echo "Data última edição: " . $row['data_ultima_edicao_manual'] . "\n\n";
    
    // Testar formatação do número
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
    
    $numero_formatado = ajustarNumeroWhatsapp($row['celular']);
    echo "Número formatado para WhatsApp: " . $numero_formatado . "\n\n";
    
    // Verificar última mensagem enviada
    $res_msg = $mysqli->query("SELECT id, mensagem, data_hora, status, numero_whatsapp FROM mensagens_comunicacao WHERE cliente_id = 264 ORDER BY data_hora DESC LIMIT 5");
    if ($res_msg && $res_msg->num_rows > 0) {
        echo "Últimas mensagens enviadas:\n";
        while ($msg = $res_msg->fetch_assoc()) {
            echo "- ID: " . $msg['id'] . " | Data: " . $msg['data_hora'] . " | Status: " . $msg['status'] . " | Número usado: " . $msg['numero_whatsapp'] . "\n";
            echo "  Mensagem: " . substr($msg['mensagem'], 0, 50) . "...\n\n";
        }
    } else {
        echo "Nenhuma mensagem encontrada para este cliente.\n\n";
    }
    
} else {
    echo "Cliente não encontrado!\n";
}

echo "=== FIM DA VERIFICAÇÃO ===\n";
?> 