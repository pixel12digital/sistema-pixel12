<?php
/**
 * CORREÇÃO AUTOMÁTICA DE MENSAGENS
 * 
 * Script para corrigir problemas de recebimento
 */

echo "🔧 CORRIGINDO PROBLEMAS DE MENSAGENS\n";
echo "====================================\n\n";

// 1. Limpar cache
echo "1. Limpando cache...\n";
$cache_dir = "painel/cache/";
if (is_dir($cache_dir)) {
    $files = glob($cache_dir . "*.cache");
    foreach ($files as $file) {
        unlink($file);
    }
    echo "   ✅ Cache limpo\n";
}

// 2. Verificar mensagens pendentes
echo "2. Verificando mensagens pendentes...\n";
try {
    require_once "painel/db_emergency.php";
    
    $sql = "SELECT * FROM mensagens_pendentes WHERE DATE(data_hora) = CURDATE()";
    $result = $mysqli->query($sql);
    
    if ($result && $result->num_rows > 0) {
        echo "   📬 Encontradas " . $result->num_rows . " mensagens pendentes\n";
        
        while ($row = $result->fetch_assoc()) {
            // Tentar processar mensagem pendente
            $numero = $row["numero"];
            $mensagem = $row["mensagem"];
            
            // Buscar cliente pelo número
            $numero_limpo = preg_replace("/\D/", "", $numero);
            $sql_cliente = "SELECT id FROM clientes WHERE celular LIKE '%$numero_limpo%' LIMIT 1";
            $result_cliente = $mysqli->query($sql_cliente);
            
            if ($result_cliente && $result_cliente->num_rows > 0) {
                $cliente = $result_cliente->fetch_assoc();
                $cliente_id = $cliente["id"];
                
                // Mover para tabela principal
                $sql_move = "INSERT INTO mensagens_comunicacao (canal_id, cliente_id, mensagem, tipo, data_hora, direcao, status) 
                            VALUES (" . $row["canal_id"] . ", $cliente_id, '" . $mysqli->real_escape_string($mensagem) . "', 'texto', '" . $row["data_hora"] . "', 'recebido', 'recebido')";
                
                if ($mysqli->query($sql_move)) {
                    // Remover da tabela pendente
                    $mysqli->query("DELETE FROM mensagens_pendentes WHERE id = " . $row["id"]);
                    echo "   ✅ Mensagem processada: $numero\n";
                }
            }
        }
    } else {
        echo "   ✅ Nenhuma mensagem pendente\n";
    }
    
} catch (Exception $e) {
    echo "   ❌ Erro: " . $e->getMessage() . "\n";
}

// 3. Verificar integridade das mensagens
echo "3. Verificando integridade...\n";
try {
    $sql = "SELECT COUNT(*) as total FROM mensagens_comunicacao WHERE DATE(data_hora) = CURDATE()";
    $result = $mysqli->query($sql);
    
    if ($result) {
        $row = $result->fetch_assoc();
        echo "   📊 Total de mensagens hoje: " . $row["total"] . "\n";
    }
    
} catch (Exception $e) {
    echo "   ❌ Erro: " . $e->getMessage() . "\n";
}

// 4. Verificar logs de webhook
echo "4. Verificando logs de webhook...\n";
$log_file = 'logs/webhook_whatsapp_' . date('Y-m-d') . '.log';
if (file_exists($log_file)) {
    $logs = file($log_file);
    $mensagens_recentes = 0;
    foreach ($logs as $linha) {
        if (strpos($linha, '"event":"onmessage"') !== false) {
            $mensagens_recentes++;
        }
    }
    echo "   📄 Mensagens no log hoje: $mensagens_recentes\n";
} else {
    echo "   ❌ Arquivo de log não encontrado\n";
}

// 5. Testar webhook
echo "5. Testando webhook...\n";
$webhook_url = "https://pixel12digital.com.br/app/api/webhook_whatsapp.php";
$ch = curl_init($webhook_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_NOBODY, true);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "   📡 Status do webhook: HTTP $http_code\n";

echo "\n🎉 CORREÇÃO CONCLUÍDA!\n";
echo "======================\n\n";

echo "📋 PRÓXIMOS PASSOS:\n";
echo "   1. Teste o chat novamente\n";
echo "   2. Envie uma mensagem de teste\n";
echo "   3. Verifique se aparece no painel\n";
echo "   4. Se ainda não funcionar, aguarde 1 hora\n\n";

echo "🔧 CAUSA PROVÁVEL DO PROBLEMA:\n";
echo "   - Limite de conexões do banco excedido (500/hora)\n";
echo "   - Aguarde 1 hora para resetar o limite\n";
echo "   - Ou contate o provedor para aumentar o limite\n";
?> 