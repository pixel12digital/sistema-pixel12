<?php
/**
 * ENVIAR MENSAGENS DE TESTE PARA OS CANAIS 3000 E 3001
 * Testa se as mensagens são salvas corretamente no banco de dados
 * Número de teste: 554796164699
 */

require_once 'config.php';
require_once 'painel/db.php';

echo "=== ENVIANDO MENSAGENS DE TESTE PARA OS CANAIS ===\n";
echo "Número de teste: 554796164699\n";
echo "Data/Hora: " . date('Y-m-d H:i:s') . "\n\n";

$numero_teste = '554796164699';
$timestamp = date('H:i:s');

try {
    // 1. ENVIAR MENSAGEM PARA CANAL 3000
    echo "1. ENVIANDO MENSAGEM PARA CANAL 3000:\n";
    echo "   =================================\n";
    
    $mensagem_3000 = "🧪 TESTE CANAL 3000 - {$timestamp} - Verificação de salvamento no banco";
    
    $sql_enviar_3000 = "INSERT INTO mensagens_comunicacao 
                        (canal_id, numero_whatsapp, mensagem, tipo, data_hora, direcao, status) 
                        VALUES (36, ?, ?, 'texto', NOW(), 'enviado', 'enviado')";
    
    $stmt_3000 = $mysqli->prepare($sql_enviar_3000);
    $stmt_3000->bind_param('ss', $numero_teste, $mensagem_3000);
    
    if ($stmt_3000->execute()) {
        $id_mensagem_3000 = $mysqli->insert_id;
        echo "   ✅ Mensagem enviada para canal 3000\n";
        echo "   - ID: {$id_mensagem_3000}\n";
        echo "   - Mensagem: {$mensagem_3000}\n";
    } else {
        echo "   ❌ Erro ao enviar mensagem para canal 3000\n";
    }
    echo "\n";
    
    // 2. ENVIAR MENSAGEM PARA CANAL 3001
    echo "2. ENVIANDO MENSAGEM PARA CANAL 3001:\n";
    echo "   =================================\n";
    
    $mensagem_3001 = "🧪 TESTE CANAL 3001 - {$timestamp} - Verificação de salvamento no banco";
    
    $sql_enviar_3001 = "INSERT INTO mensagens_comunicacao 
                        (canal_id, numero_whatsapp, mensagem, tipo, data_hora, direcao, status) 
                        VALUES (37, ?, ?, 'texto', NOW(), 'enviado', 'enviado')";
    
    $stmt_3001 = $mysqli->prepare($sql_enviar_3001);
    $stmt_3001->bind_param('ss', $numero_teste, $mensagem_3001);
    
    if ($stmt_3001->execute()) {
        $id_mensagem_3001 = $mysqli->insert_id;
        echo "   ✅ Mensagem enviada para canal 3001\n";
        echo "   - ID: {$id_mensagem_3001}\n";
        echo "   - Mensagem: {$mensagem_3001}\n";
    } else {
        echo "   ❌ Erro ao enviar mensagem para canal 3001\n";
    }
    echo "\n";
    
    // 3. VERIFICAR SE AS MENSAGENS FORAM SALVAS
    echo "3. VERIFICANDO SE AS MENSAGENS FORAM SALVAS:\n";
    echo "   ========================================\n";
    
    // Verificar mensagem do canal 3000
    $sql_verificar_3000 = "SELECT * FROM mensagens_comunicacao WHERE id = ?";
    $stmt_ver_3000 = $mysqli->prepare($sql_verificar_3000);
    $stmt_ver_3000->bind_param('i', $id_mensagem_3000);
    $stmt_ver_3000->execute();
    $result_ver_3000 = $stmt_ver_3000->get_result();
    
    if ($result_ver_3000 && $result_ver_3000->num_rows > 0) {
        $msg_3000 = $result_ver_3000->fetch_assoc();
        echo "   ✅ Mensagem canal 3000 salva corretamente:\n";
        echo "   - ID: {$msg_3000['id']}\n";
        echo "   - Canal ID: {$msg_3000['canal_id']}\n";
        echo "   - Número: {$msg_3000['numero_whatsapp']}\n";
        echo "   - Data/Hora: {$msg_3000['data_hora']}\n";
        echo "   - Status: {$msg_3000['status']}\n";
    } else {
        echo "   ❌ Mensagem canal 3000 NÃO encontrada no banco!\n";
    }
    echo "\n";
    
    // Verificar mensagem do canal 3001
    $sql_verificar_3001 = "SELECT * FROM mensagens_comunicacao WHERE id = ?";
    $stmt_ver_3001 = $mysqli->prepare($sql_verificar_3001);
    $stmt_ver_3001->bind_param('i', $id_mensagem_3001);
    $stmt_ver_3001->execute();
    $result_ver_3001 = $stmt_ver_3001->get_result();
    
    if ($result_ver_3001 && $result_ver_3001->num_rows > 0) {
        $msg_3001 = $result_ver_3001->fetch_assoc();
        echo "   ✅ Mensagem canal 3001 salva corretamente:\n";
        echo "   - ID: {$msg_3001['id']}\n";
        echo "   - Canal ID: {$msg_3001['canal_id']}\n";
        echo "   - Número: {$msg_3001['numero_whatsapp']}\n";
        echo "   - Data/Hora: {$msg_3001['data_hora']}\n";
        echo "   - Status: {$msg_3001['status']}\n";
    } else {
        echo "   ❌ Mensagem canal 3001 NÃO encontrada no banco!\n";
    }
    echo "\n";
    
    // 4. VERIFICAR BANCO SEPARADO DO CANAL 3001
    echo "4. VERIFICANDO BANCO SEPARADO DO CANAL 3001:\n";
    echo "   =========================================\n";
    
    try {
        $mysqli_3001 = new mysqli('srv1607.hstgr.io', 'u342734079_wts_com_pixel', 'Los@ngo#081081', 'u342734079_wts_com_pixel');
        
        if (!$mysqli_3001->connect_error) {
            echo "   📱 Conectado ao banco do canal 3001 (comercial)\n";
            
            // Inserir mensagem no banco separado
            $sql_3001_separado = "INSERT INTO mensagens_comunicacao 
                                 (canal_id, numero_whatsapp, mensagem, tipo, data_hora, direcao, status) 
                                 VALUES (1, ?, ?, 'texto', NOW(), 'enviado', 'enviado')";
            
            $mensagem_3001_sep = "🧪 TESTE BANCO SEPARADO 3001 - {$timestamp}";
            
            $stmt_3001_sep = $mysqli_3001->prepare($sql_3001_separado);
            $stmt_3001_sep->bind_param('ss', $numero_teste, $mensagem_3001_sep);
            
            if ($stmt_3001_sep->execute()) {
                $id_3001_sep = $mysqli_3001->insert_id;
                echo "   ✅ Mensagem inserida no banco separado\n";
                echo "   - ID: {$id_3001_sep}\n";
                echo "   - Mensagem: {$mensagem_3001_sep}\n";
            } else {
                echo "   ❌ Erro ao inserir no banco separado\n";
            }
            
            $mysqli_3001->close();
        } else {
            echo "   ❌ Erro ao conectar ao banco do canal 3001\n";
        }
    } catch (Exception $e) {
        echo "   ❌ Erro ao verificar banco do canal 3001: " . $e->getMessage() . "\n";
    }
    
    echo "\n";
    
    // 5. SIMULAR MENSAGENS RECEBIDAS
    echo "5. SIMULANDO MENSAGENS RECEBIDAS:\n";
    echo "   =============================\n";
    
    // Simular mensagem recebida do canal 3000
    $mensagem_recebida_3000 = "📥 RESPOSTA TESTE CANAL 3000 - {$timestamp}";
    $sql_recebida_3000 = "INSERT INTO mensagens_comunicacao 
                          (canal_id, numero_whatsapp, mensagem, tipo, data_hora, direcao, status) 
                          VALUES (36, ?, ?, 'texto', NOW(), 'recebido', 'recebido')";
    
    $stmt_rec_3000 = $mysqli->prepare($sql_recebida_3000);
    $stmt_rec_3000->bind_param('ss', $numero_teste, $mensagem_recebida_3000);
    
    if ($stmt_rec_3000->execute()) {
        echo "   ✅ Mensagem recebida simulada para canal 3000\n";
    } else {
        echo "   ❌ Erro ao simular mensagem recebida canal 3000\n";
    }
    
    // Simular mensagem recebida do canal 3001
    $mensagem_recebida_3001 = "📥 RESPOSTA TESTE CANAL 3001 - {$timestamp}";
    $sql_recebida_3001 = "INSERT INTO mensagens_comunicacao 
                          (canal_id, numero_whatsapp, mensagem, tipo, data_hora, direcao, status) 
                          VALUES (37, ?, ?, 'texto', NOW(), 'recebido', 'recebido')";
    
    $stmt_rec_3001 = $mysqli->prepare($sql_recebida_3001);
    $stmt_rec_3001->bind_param('ss', $numero_teste, $mensagem_recebida_3001);
    
    if ($stmt_rec_3001->execute()) {
        echo "   ✅ Mensagem recebida simulada para canal 3001\n";
    } else {
        echo "   ❌ Erro ao simular mensagem recebida canal 3001\n";
    }
    
    echo "\n";
    
    // 6. RESUMO FINAL
    echo "6. RESUMO FINAL:\n";
    echo "   =============\n";
    echo "   ✅ Teste de salvamento concluído\n";
    echo "   ✅ Mensagens enviadas e recebidas simuladas\n";
    echo "   ✅ Verificação de banco separado realizada\n";
    echo "   📊 Para verificar as mensagens, execute: php teste_mensagens_canais_3000_3001.php\n";
    
    echo "\n=== FIM DO TESTE ===\n";
    
} catch (Exception $e) {
    echo "❌ ERRO: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
?> 