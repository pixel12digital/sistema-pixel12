<?php
header('Content-Type: text/html; charset=utf-8');
require_once(__DIR__ . '/../config.php');
require_once('db.php');

echo "<h1>🔍 Verificação do Chat Centralizado</h1>";
echo "<h2>Cliente: JP TRASLADOS LTDA | Nicelio Salustiano dos santos (ID: 145)</h2>";

// 1. Verificar se o cliente aparece na consulta de conversas
echo "<h3>1. Consulta de Conversas (cache_conversas)</h3>";
$sql_conversas = "SELECT DISTINCT 
                    c.id as cliente_id,
                    c.nome,
                    c.celular,
                    'WhatsApp' as canal_nome,
                    m.ultima_mensagem,
                    m.ultima_data,
                    m.mensagens_nao_lidas
                  FROM clientes c
                  INNER JOIN (
                      SELECT 
                          cliente_id,
                          MAX(data_hora) as ultima_data,
                          MAX(CASE WHEN direcao = 'recebido' THEN mensagem END) as ultima_mensagem,
                          COUNT(CASE WHEN direcao = 'recebido' AND status != 'lido' THEN 1 END) as mensagens_nao_lidas
                      FROM mensagens_comunicacao 
                      WHERE data_hora >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                      GROUP BY cliente_id
                  ) m ON c.id = m.cliente_id
                  WHERE c.id = 145
                  ORDER BY m.ultima_data DESC";

$result_conversas = $mysqli->query($sql_conversas);

if ($result_conversas && $result_conversas->num_rows > 0) {
    $conversa = $result_conversas->fetch_assoc();
    echo "<div style='background: #d4edda; padding: 15px; border-radius: 8px;'>";
    echo "<p><strong>✅ Cliente encontrado na consulta de conversas!</strong></p>";
    echo "<p><strong>Cliente ID:</strong> {$conversa['cliente_id']}</p>";
    echo "<p><strong>Nome:</strong> {$conversa['nome']}</p>";
    echo "<p><strong>Última Data:</strong> {$conversa['ultima_data']}</p>";
    echo "<p><strong>Última Mensagem:</strong> " . htmlspecialchars(substr($conversa['ultima_mensagem'], 0, 50)) . "...</p>";
    echo "<p><strong>Mensagens não lidas:</strong> {$conversa['mensagens_nao_lidas']}</p>";
    echo "</div>";
} else {
    echo "<p style='color: red;'>❌ Cliente NÃO encontrado na consulta de conversas!</p>";
}

// 2. Verificar mensagens dos últimos 30 dias
echo "<h3>2. Mensagens dos Últimos 30 Dias</h3>";
$sql_30dias = "SELECT COUNT(*) as total FROM mensagens_comunicacao 
               WHERE cliente_id = 145 
               AND data_hora >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
$result_30dias = $mysqli->query($sql_30dias);
$total_30dias = $result_30dias->fetch_assoc()['total'];

echo "<p><strong>Total de mensagens nos últimos 30 dias:</strong> $total_30dias</p>";

if ($total_30dias == 0) {
    echo "<p style='color: red;'>❌ PROBLEMA IDENTIFICADO: Nenhuma mensagem nos últimos 30 dias!</p>";
    echo "<p>A consulta de conversas filtra apenas mensagens dos últimos 30 dias.</p>";
}

// 3. Verificar todas as mensagens do cliente
echo "<h3>3. Todas as Mensagens do Cliente</h3>";
$sql_todas = "SELECT * FROM mensagens_comunicacao 
              WHERE cliente_id = 145 
              ORDER BY data_hora DESC";
$result_todas = $mysqli->query($sql_todas);

if ($result_todas && $result_todas->num_rows > 0) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>ID</th><th>Data/Hora</th><th>Direção</th><th>Tipo</th><th>Status</th><th>Mensagem (primeiros 50 chars)</th></tr>";
    
    while ($msg = $result_todas->fetch_assoc()) {
        $cor_fundo = '';
        if (strtotime($msg['data_hora']) < strtotime('-30 days')) {
            $cor_fundo = 'background-color: #ffe6e6;'; // Vermelho claro para mensagens antigas
        }
        
        echo "<tr style='$cor_fundo'>";
        echo "<td>{$msg['id']}</td>";
        echo "<td>{$msg['data_hora']}</td>";
        echo "<td>{$msg['direcao']}</td>";
        echo "<td>{$msg['tipo']}</td>";
        echo "<td>{$msg['status']}</td>";
        echo "<td>" . htmlspecialchars(substr($msg['mensagem'], 0, 50)) . "...</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: red;'>❌ Nenhuma mensagem encontrada!</p>";
}

// 4. Verificar cache
echo "<h3>4. Status do Cache</h3>";
$cache_file = '../cache/conversas_recentes.cache';
if (file_exists($cache_file)) {
    $cache_time = filemtime($cache_file);
    $cache_age = time() - $cache_time;
    $cache_age_minutes = round($cache_age / 60, 1);
    
    echo "<p><strong>Arquivo de cache:</strong> $cache_file</p>";
    echo "<p><strong>Última atualização:</strong> " . date('Y-m-d H:i:s', $cache_time) . "</p>";
    echo "<p><strong>Idade do cache:</strong> $cache_age_minutes minutos</p>";
    
    if ($cache_age < 1800) { // 30 minutos
        echo "<p style='color: orange;'>⚠️ Cache ainda válido. Pode estar usando dados antigos.</p>";
    } else {
        echo "<p style='color: green;'>✅ Cache expirado. Será recriado na próxima consulta.</p>";
    }
} else {
    echo "<p style='color: orange;'>⚠️ Arquivo de cache não encontrado.</p>";
}

// 5. Testar consulta sem filtro de 30 dias
echo "<h3>5. Teste: Consulta Sem Filtro de 30 Dias</h3>";
$sql_sem_filtro = "SELECT DISTINCT 
                     c.id as cliente_id,
                     c.nome,
                     c.celular,
                     'WhatsApp' as canal_nome,
                     m.ultima_mensagem,
                     m.ultima_data,
                     m.mensagens_nao_lidas
                   FROM clientes c
                   INNER JOIN (
                       SELECT 
                           cliente_id,
                           MAX(data_hora) as ultima_data,
                           MAX(CASE WHEN direcao = 'recebido' THEN mensagem END) as ultima_mensagem,
                           COUNT(CASE WHEN direcao = 'recebido' AND status != 'lido' THEN 1 END) as mensagens_nao_lidas
                       FROM mensagens_comunicacao 
                       GROUP BY cliente_id
                   ) m ON c.id = m.cliente_id
                   WHERE c.id = 145";

$result_sem_filtro = $mysqli->query($sql_sem_filtro);

if ($result_sem_filtro && $result_sem_filtro->num_rows > 0) {
    $conversa_sem_filtro = $result_sem_filtro->fetch_assoc();
    echo "<div style='background: #fff3cd; padding: 15px; border-radius: 8px;'>";
    echo "<p><strong>✅ Cliente encontrado SEM filtro de 30 dias!</strong></p>";
    echo "<p><strong>Última Data:</strong> {$conversa_sem_filtro['ultima_data']}</p>";
    echo "<p><strong>Última Mensagem:</strong> " . htmlspecialchars(substr($conversa_sem_filtro['ultima_mensagem'], 0, 50)) . "...</p>";
    echo "</div>";
} else {
    echo "<p style='color: red;'>❌ Cliente NÃO encontrado mesmo sem filtro!</p>";
}

// 6. Solução: Limpar cache
echo "<h3>6. Solução: Limpar Cache</h3>";
if (file_exists($cache_file)) {
    if (unlink($cache_file)) {
        echo "<p style='color: green;'>✅ Cache limpo com sucesso!</p>";
        echo "<p>Agora o chat centralizado deve recarregar os dados do banco.</p>";
    } else {
        echo "<p style='color: red;'>❌ Erro ao limpar cache</p>";
    }
} else {
    echo "<p style='color: orange;'>⚠️ Cache não existe, não foi necessário limpar.</p>";
}

echo "<hr>";
echo "<h3>🎯 Análise do Problema</h3>";

if ($total_30dias == 0) {
    echo "<p style='color: red;'><strong>PROBLEMA IDENTIFICADO:</strong> A mensagem do Nicelio foi enviada, mas não está aparecendo no chat porque:</p>";
    echo "<ul>";
    echo "<li>A consulta de conversas filtra apenas mensagens dos últimos 30 dias</li>";
    echo "<li>A mensagem foi enviada hoje, mas pode estar com data incorreta</li>";
    echo "<li>O cache pode estar usando dados antigos</li>";
    echo "</ul>";
    echo "<p><strong>SOLUÇÃO:</strong> Cache foi limpo. Recarregue a página do chat centralizado.</p>";
} else {
    echo "<p style='color: green;'><strong>STATUS:</strong> Mensagens encontradas nos últimos 30 dias.</p>";
    echo "<p><strong>PRÓXIMO PASSO:</strong> Recarregue a página do chat centralizado para ver as mudanças.</p>";
}

echo "<p><strong>Timestamp:</strong> " . date('Y-m-d H:i:s') . "</p>";
?> 