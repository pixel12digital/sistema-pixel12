<?php
require_once 'config.php';
require_once 'painel/db.php';

echo "<h2>🔧 Correção de Mensagens Órfãs</h2>\n";
echo "<hr>\n";

// Verificar mensagens órfãs
$sql_orfas = "SELECT mc.id, mc.cliente_id, mc.mensagem, mc.data_hora, mc.telefone_origem
              FROM mensagens_comunicacao mc
              LEFT JOIN clientes c ON mc.cliente_id = c.id
              WHERE mc.direcao = 'recebido' 
              AND mc.status != 'lido'
              AND mc.data_hora >= DATE_SUB(NOW(), INTERVAL 7 DAY)
              AND c.id IS NULL";

$result_orfas = $mysqli->query($sql_orfas);
$total_orfas = $result_orfas->num_rows;

echo "<h3>📊 Situação atual:</h3>\n";
echo "<p><strong>Mensagens órfãs encontradas:</strong> {$total_orfas}</p>\n";

if ($total_orfas > 0) {
    echo "<h3>🔍 Detalhes das mensagens órfãs:</h3>\n";
    echo "<table border='1' style='border-collapse: collapse; margin: 10px 0; width: 100%;'>\n";
    echo "<tr><th>ID</th><th>Cliente ID</th><th>Telefone Origem</th><th>Mensagem</th><th>Data/Hora</th></tr>\n";
    
    $mensagens_orfas = [];
    while ($row = $result_orfas->fetch_assoc()) {
        $mensagens_orfas[] = $row;
        echo "<tr>";
        echo "<td>{$row['id']}</td>";
        echo "<td>" . ($row['cliente_id'] ?: 'NULL') . "</td>";
        echo "<td>" . ($row['telefone_origem'] ?: 'N/A') . "</td>";
        echo "<td>" . substr($row['mensagem'], 0, 50) . "...</td>";
        echo "<td>{$row['data_hora']}</td>";
        echo "</tr>\n";
    }
    echo "</table>\n";

    // Verificar se é possível recuperar clientes pelo telefone
    echo "<h3>🔍 Tentativa de recuperação por telefone:</h3>\n";
    $recuperadas = 0;
    $nao_recuperadas = 0;
    
    foreach ($mensagens_orfas as $msg) {
        if (!empty($msg['telefone_origem'])) {
            // Tentar encontrar cliente pelo telefone
            $telefone_limpo = preg_replace('/[^0-9]/', '', $msg['telefone_origem']);
            
            $sql_cliente = "SELECT id, nome FROM clientes 
                           WHERE REPLACE(REPLACE(REPLACE(REPLACE(celular, '(', ''), ')', ''), '-', ''), ' ', '') LIKE '%{$telefone_limpo}%'
                           OR REPLACE(REPLACE(REPLACE(REPLACE(telefone, '(', ''), ')', ''), '-', ''), ' ', '') LIKE '%{$telefone_limpo}%'
                           LIMIT 1";
            
            $result_cliente = $mysqli->query($sql_cliente);
            if ($result_cliente && $result_cliente->num_rows > 0) {
                $cliente = $result_cliente->fetch_assoc();
                echo "<p>✅ Mensagem {$msg['id']} pode ser associada ao cliente {$cliente['id']} ({$cliente['nome']})</p>\n";
                $recuperadas++;
            } else {
                echo "<p>❌ Mensagem {$msg['id']} - telefone {$msg['telefone_origem']} não encontrado</p>\n";
                $nao_recuperadas++;
            }
        } else {
            $nao_recuperadas++;
        }
    }

    echo "<p><strong>Resumo:</strong> {$recuperadas} podem ser recuperadas, {$nao_recuperadas} precisam ser removidas</p>\n";

    // Opções de correção
    echo "<h3>🛠️ Opções de correção:</h3>\n";
    echo "<div style='margin: 20px 0; padding: 15px; background: #f5f5f5; border: 1px solid #ddd;'>\n";
    echo "<h4>Opção 1: Marcar mensagens órfãs como lidas (Recomendado)</h4>\n";
    echo "<p>Marca todas as mensagens órfãs como 'lidas' para corrigir o contador sem perder dados.</p>\n";
    echo "<button onclick=\"executarCorrecao('marcar_lidas')\" style='padding: 10px 20px; background: #28a745; color: white; border: none; border-radius: 4px;'>Executar Opção 1</button>\n";
    echo "</div>\n";

    echo "<div style='margin: 20px 0; padding: 15px; background: #fff3cd; border: 1px solid #ffeaa7;'>\n";
    echo "<h4>Opção 2: Remover mensagens órfãs (Cuidado)</h4>\n";
    echo "<p>Remove permanentemente as mensagens que não podem ser associadas a clientes.</p>\n";
    echo "<button onclick=\"executarCorrecao('remover')\" style='padding: 10px 20px; background: #dc3545; color: white; border: none; border-radius: 4px;'>Executar Opção 2</button>\n";
    echo "</div>\n";

    echo "<div style='margin: 20px 0; padding: 15px; background: #d4edda; border: 1px solid #c3e6cb;'>\n";
    echo "<h4>Opção 3: Tentar recuperar por telefone (Automático)</h4>\n";
    echo "<p>Tenta associar mensagens aos clientes usando o número de telefone automaticamente.</p>\n";
    echo "<button onclick=\"executarCorrecao('recuperar')\" style='padding: 10px 20px; background: #007bff; color: white; border: none; border-radius: 4px;'>Executar Opção 3</button>\n";
    echo "</div>\n";

    // JavaScript para executar correções
    echo "<script>\n";
    echo "function executarCorrecao(tipo) {\n";
    echo "  if (confirm('Tem certeza que deseja executar esta correção?')) {\n";
    echo "    window.location.href = 'executar_correcao_orfas.php?acao=' + tipo;\n";
    echo "  }\n";
    echo "}\n";
    echo "</script>\n";

} else {
    echo "<p>✅ <strong>Nenhuma mensagem órfã encontrada!</strong> O sistema está consistente.</p>\n";
}

// Testar a API corrigida
echo "<h3>🧪 Teste da API corrigida:</h3>\n";
echo "<iframe src='api/conversas_nao_lidas.php' style='width: 100%; height: 200px; border: 1px solid #ddd;'></iframe>\n";

$mysqli->close();
?> 