<?php
/**
 * CORREÇÃO DO STATUS VIA WEB
 * Acesse via: http://localhost:8080/loja-virtual-revenda/painel/corrigir_status_web.php
 */

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Correção Status WhatsApp</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; }
        .status { padding: 10px; margin: 10px 0; border-radius: 4px; }
        .success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .warning { background: #fff3cd; color: #856404; border: 1px solid #ffeaa7; }
        .info { background: #d1ecf1; color: #0c5460; border: 1px solid #bee5eb; }
        pre { background: #f8f9fa; padding: 10px; border-radius: 4px; overflow-x: auto; }
        .btn { padding: 10px 20px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; }
        .btn:hover { background: #0056b3; }
    </style>
</head>
<body>

<div class="container">
    <h1>🔧 Correção Status WhatsApp</h1>
    
    <?php
    try {
        // Incluir configuração do banco
        require_once 'db.php';
        
        echo '<div class="status success">✅ Conexão com banco estabelecida</div>';
        
        // Função para testar VPS
        function testarVPS($porta) {
            $url = "http://212.85.11.238:{$porta}/status";
            $context = stream_context_create([
                'http' => [
                    'timeout' => 5,
                    'user_agent' => 'WhatsApp-Fix/1.0'
                ]
            ]);
            
            $response = @file_get_contents($url, false, $context);
            if ($response === false) {
                return ['funcionando' => false, 'ready' => false];
            }
            
            $data = json_decode($response, true);
            if (!$data) {
                return ['funcionando' => false, 'ready' => false];
            }
            
            return [
                'funcionando' => true,
                'ready' => isset($data['ready']) ? $data['ready'] : false,
                'status' => isset($data['status']) ? $data['status'] : 'unknown'
            ];
        }
        
        echo '<h2>1. 📊 Testando VPS</h2>';
        
        $statusPortas = [];
        foreach ([3000, 3001] as $porta) {
            $status = testarVPS($porta);
            $statusPortas[$porta] = $status;
            
            if ($status['funcionando']) {
                $ready = $status['ready'] ? 'SIM' : 'NÃO';
                echo '<div class="status success">✅ Porta ' . $porta . ': FUNCIONANDO - Ready: ' . $ready . '</div>';
            } else {
                echo '<div class="status error">❌ Porta ' . $porta . ': NÃO FUNCIONANDO</div>';
            }
        }
        
        echo '<h2>2. 🗄️ Atualizando Banco</h2>';
        
        // Obter conexão
        $db = DatabaseManager::getInstance();
        $mysqli = $db->getConnection();
        
        if (!$mysqli) {
            throw new Exception('Não foi possível conectar ao banco');
        }
        
        // Buscar canais
        $result = $mysqli->query("SELECT id, porta, nome_exibicao, status FROM canais_comunicacao WHERE tipo = 'whatsapp'");
        
        if ($result && $result->num_rows > 0) {
            while ($canal = $result->fetch_assoc()) {
                $porta = $canal['porta'];
                $canal_id = $canal['id'];
                $nome = $canal['nome_exibicao'];
                $status_atual = $canal['status'];
                
                echo '<div class="status info">🔍 Canal: ' . htmlspecialchars($nome) . ' (Porta: ' . $porta . ')</div>';
                
                if (isset($statusPortas[$porta]) && $statusPortas[$porta]['funcionando']) {
                    if ($statusPortas[$porta]['ready']) {
                        // Conectado
                        $mysqli->query("UPDATE canais_comunicacao SET status = 'conectado', data_conexao = NOW() WHERE id = $canal_id");
                        echo '<div class="status success">✅ Atualizado para: CONECTADO</div>';
                    } else {
                        // Pendente
                        $mysqli->query("UPDATE canais_comunicacao SET status = 'pendente' WHERE id = $canal_id");
                        echo '<div class="status warning">⚠️ Atualizado para: PENDENTE (aguardando QR)</div>';
                    }
                } else {
                    // Desconectado
                    $mysqli->query("UPDATE canais_comunicacao SET status = 'desconectado' WHERE id = $canal_id");
                    echo '<div class="status error">❌ Atualizado para: DESCONECTADO</div>';
                }
            }
        } else {
            echo '<div class="status warning">ℹ️ Nenhum canal WhatsApp encontrado</div>';
        }
        
        echo '<h2>3. 📊 Status Final</h2>';
        
        $resumo = $mysqli->query("SELECT status, COUNT(*) as total FROM canais_comunicacao WHERE tipo = 'whatsapp' GROUP BY status");
        
        while ($row = $resumo->fetch_assoc()) {
            $status = $row['status'];
            $total = $row['total'];
            
            $class = '';
            $icon = '';
            switch ($status) {
                case 'conectado':
                    $class = 'success';
                    $icon = '✅';
                    break;
                case 'pendente':
                    $class = 'warning';
                    $icon = '⚠️';
                    break;
                case 'desconectado':
                    $class = 'error';
                    $icon = '❌';
                    break;
                default:
                    $class = 'info';
                    $icon = '❓';
            }
            
            echo '<div class="status ' . $class . '">' . $icon . ' ' . $status . ': ' . $total . ' canais</div>';
        }
        
        echo '<div class="status success">✅ Correção concluída com sucesso!</div>';
        
    } catch (Exception $e) {
        echo '<div class="status error">❌ Erro: ' . htmlspecialchars($e->getMessage()) . '</div>';
    }
    ?>
    
    <h2>📋 Próximos Passos</h2>
    <div class="status info">
        <strong>1.</strong> Recarregue o painel de comunicação (F5)<br>
        <strong>2.</strong> Aguarde 30 segundos para atualização automática<br>
        <strong>3.</strong> Para canais PENDENTE, clique em "Conectar" para gerar QR Code<br>
        <strong>4.</strong> Para canais CONECTADO, você já pode usar normalmente
    </div>
    
    <br>
    <button class="btn" onclick="location.href='comunicacao.php'">🔙 Voltar ao Painel</button>
    <button class="btn" onclick="location.reload()">🔄 Executar Novamente</button>
    
</div>

</body>
</html> 