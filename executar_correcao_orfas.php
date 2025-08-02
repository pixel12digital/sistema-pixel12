<?php
require_once 'config.php';
require_once 'painel/db.php';

$acao = $_GET['acao'] ?? '';

echo "<h2>🔧 Executando Correção de Mensagens Órfãs</h2>\n";
echo "<hr>\n";

if (empty($acao)) {
    echo "<p>❌ Ação não especificada.</p>\n";
    echo "<p><a href='corrigir_mensagens_orfas.php'>← Voltar</a></p>\n";
    exit;
}

try {
    switch ($acao) {
        case 'marcar_lidas':
            echo "<h3>🔄 Marcando mensagens órfãs como lidas...</h3>\n";
            
            $sql = "UPDATE mensagens_comunicacao mc
                    LEFT JOIN clientes c ON mc.cliente_id = c.id
                    SET mc.status = 'lido'
                    WHERE mc.direcao = 'recebido' 
                    AND mc.status != 'lido'
                    AND mc.data_hora >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                    AND c.id IS NULL";
            
            $result = $mysqli->query($sql);
            $affected = $mysqli->affected_rows;
            
            if ($result) {
                echo "<p>✅ <strong>{$affected} mensagens órfãs foram marcadas como lidas.</strong></p>\n";
                echo "<p>O contador de mensagens não lidas agora deve estar consistente.</p>\n";
            } else {
                echo "<p>❌ Erro ao executar atualização: " . $mysqli->error . "</p>\n";
            }
            break;

        case 'remover':
            echo "<h3>🗑️ Removendo mensagens órfãs...</h3>\n";
            
            $sql = "DELETE mc FROM mensagens_comunicacao mc
                    LEFT JOIN clientes c ON mc.cliente_id = c.id
                    WHERE mc.direcao = 'recebido' 
                    AND mc.status != 'lido'
                    AND mc.data_hora >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                    AND c.id IS NULL";
            
            $result = $mysqli->query($sql);
            $affected = $mysqli->affected_rows;
            
            if ($result) {
                echo "<p>✅ <strong>{$affected} mensagens órfãs foram removidas permanentemente.</strong></p>\n";
                echo "<p>O contador de mensagens não lidas agora deve estar consistente.</p>\n";
            } else {
                echo "<p>❌ Erro ao executar remoção: " . $mysqli->error . "</p>\n";
            }
            break;

        case 'recuperar':
            echo "<h3>🔄 Tentando recuperar mensagens por telefone...</h3>\n";
            
            // Buscar mensagens órfãs com telefone
            $sql_orfas = "SELECT mc.id, mc.telefone_origem
                          FROM mensagens_comunicacao mc
                          LEFT JOIN clientes c ON mc.cliente_id = c.id
                          WHERE mc.direcao = 'recebido' 
                          AND mc.status != 'lido'
                          AND mc.data_hora >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                          AND c.id IS NULL
                          AND mc.telefone_origem IS NOT NULL
                          AND mc.telefone_origem != ''";
            
            $result_orfas = $mysqli->query($sql_orfas);
            $recuperadas = 0;
            $nao_recuperadas = 0;
            
            if ($result_orfas) {
                while ($msg = $result_orfas->fetch_assoc()) {
                    $telefone_limpo = preg_replace('/[^0-9]/', '', $msg['telefone_origem']);
                    
                    if (strlen($telefone_limpo) >= 8) {
                        $sql_cliente = "SELECT id FROM clientes 
                                       WHERE REPLACE(REPLACE(REPLACE(REPLACE(celular, '(', ''), ')', ''), '-', ''), ' ', '') LIKE '%{$telefone_limpo}%'
                                       OR REPLACE(REPLACE(REPLACE(REPLACE(telefone, '(', ''), ')', ''), '-', ''), ' ', '') LIKE '%{$telefone_limpo}%'
                                       LIMIT 1";
                        
                        $result_cliente = $mysqli->query($sql_cliente);
                        if ($result_cliente && $result_cliente->num_rows > 0) {
                            $cliente = $result_cliente->fetch_assoc();
                            
                            // Atualizar mensagem com o cliente encontrado
                            $sql_update = "UPDATE mensagens_comunicacao 
                                          SET cliente_id = {$cliente['id']} 
                                          WHERE id = {$msg['id']}";
                            
                            if ($mysqli->query($sql_update)) {
                                echo "<p>✅ Mensagem {$msg['id']} associada ao cliente {$cliente['id']}</p>\n";
                                $recuperadas++;
                            }
                        } else {
                            $nao_recuperadas++;
                        }
                    } else {
                        $nao_recuperadas++;
                    }
                }
                
                echo "<p><strong>Resultado:</strong> {$recuperadas} mensagens recuperadas, {$nao_recuperadas} não puderam ser associadas.</p>\n";
                
                if ($nao_recuperadas > 0) {
                    echo "<p>⚠️ Ainda há {$nao_recuperadas} mensagens que não puderam ser recuperadas. Considere marcá-las como lidas.</p>\n";
                }
            }
            break;

        default:
            echo "<p>❌ Ação inválida: {$acao}</p>\n";
            break;
    }

    // Invalidar caches após qualquer correção
    if (function_exists('cache_forget')) {
        cache_forget("conversas_nao_lidas");
        cache_forget("total_mensagens_nao_lidas");
        echo "<p>🗑️ Cache limpo.</p>\n";
    }

    // Verificar estado após correção
    echo "<h3>📊 Estado após correção:</h3>\n";
    
    // Contador atualizado
    $sql_total = "SELECT COUNT(DISTINCT c.id) as total 
                  FROM mensagens_comunicacao mc
                  INNER JOIN clientes c ON mc.cliente_id = c.id
                  WHERE mc.direcao = 'recebido' 
                  AND mc.status != 'lido'
                  AND mc.data_hora >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
    
    $result_total = $mysqli->query($sql_total);
    $row_total = $result_total->fetch_assoc();
    
    // Conversas disponíveis
    $sql_conversas = "SELECT COUNT(DISTINCT c.id) as total
                      FROM mensagens_comunicacao mc
                      INNER JOIN clientes c ON mc.cliente_id = c.id
                      WHERE mc.direcao = 'recebido' 
                      AND mc.status != 'lido'
                      AND mc.data_hora >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
    
    $result_conversas = $mysqli->query($sql_conversas);
    $row_conversas = $result_conversas->fetch_assoc();
    
    echo "<p><strong>Contador atual:</strong> {$row_total['total']}</p>\n";
    echo "<p><strong>Conversas disponíveis:</strong> {$row_conversas['total']}</p>\n";
    
    if ($row_total['total'] == $row_conversas['total']) {
        echo "<p>✅ <strong>Sistema agora está consistente!</strong></p>\n";
    } else {
        echo "<p>⚠️ Ainda há inconsistência. Verifique se existem outras mensagens órfãs.</p>\n";
    }

} catch (Exception $e) {
    echo "<p>❌ Erro durante execução: " . $e->getMessage() . "</p>\n";
}

echo "<hr>\n";
echo "<p><a href='corrigir_mensagens_orfas.php'>← Voltar para diagnóstico</a></p>\n";
echo "<p><a href='painel/chat.php'>🔗 Testar chat corrigido</a></p>\n";

$mysqli->close();
?> 