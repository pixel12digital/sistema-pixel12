<?php
/**
 * POLLING TEMPORÁRIO - Buscar mensagens recebidas do VPS
 * Solução temporária enquanto webhook não funciona para INCOMING
 */

// Verificar se função já foi declarada
if (!function_exists('processarWebhookLocal')) {
    /**
     * Processar webhook localmente (simular chamada do VPS)
     */
    function processarWebhookLocal($dados, $mysqli) {
        // Simular o que o webhook faria
        $numero_remetente = str_replace('@c.us', '', $dados['from'] ?? '');
        $mensagem = $dados['body'] ?? $dados['message'] ?? '';
        $canal_id = 36; // Default para Ana
        
        // Identificar canal pelo 'to'
        $numero_destino = str_replace('@c.us', '', $dados['to'] ?? '');
        if ($numero_destino === '554797309525') {
            $canal_id = 37; // Canal Humano
        }
        
        echo "    Processando: Canal $canal_id, De: $numero_remetente\n";
        
        // Encontrar ou criar cliente
        $cliente_id = null;
        $numero_limpo = preg_replace('/[^0-9]/', '', $numero_remetente);
        
        $stmt = $mysqli->prepare("SELECT id FROM clientes WHERE celular = ? OR celular = ? LIMIT 1");
        if ($stmt) {
            $numero_formatado = "+$numero_limpo";
            $stmt->bind_param('ss', $numero_limpo, $numero_formatado);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($row = $result->fetch_assoc()) {
                $cliente_id = $row['id'];
            } else {
                // Criar cliente
                $stmt_create = $mysqli->prepare("INSERT INTO clientes (nome, celular, data_criacao) VALUES (?, ?, NOW())");
                if ($stmt_create) {
                    $nome_temp = "WhatsApp " . substr($numero_limpo, -4);
                    $stmt_create->bind_param('ss', $nome_temp, $numero_limpo);
                    if ($stmt_create->execute()) {
                        $cliente_id = $mysqli->insert_id;
                    }
                    $stmt_create->close();
                }
            }
            $stmt->close();
        }
        
        if ($cliente_id) {
            // Salvar mensagem
            $stmt = $mysqli->prepare("INSERT INTO mensagens_comunicacao (canal_id, cliente_id, numero_whatsapp, mensagem, direcao, data_hora, tipo) VALUES (?, ?, ?, ?, 'recebido', NOW(), 'text')");
            if ($stmt) {
                $stmt->bind_param('iiss', $canal_id, $cliente_id, $numero_limpo, $mensagem);
                if ($stmt->execute()) {
                    $message_id = $mysqli->insert_id;
                    echo "    ✅ Mensagem salva: ID $message_id\n";
                    
                    // Processar com Ana se canal 36
                    if ($canal_id == 36) {
                        try {
                            require_once __DIR__ . '/painel/api/integrador_ana_local.php';
                            $integrador = new IntegradorAnaLocal($mysqli);
                            $resultado = $integrador->processarMensagem($dados);
                            
                            if ($resultado['success'] && !empty($resultado['resposta_ana'])) {
                                // Salvar resposta da Ana
                                $stmt_ana = $mysqli->prepare("INSERT INTO mensagens_comunicacao (canal_id, cliente_id, numero_whatsapp, mensagem, direcao, data_hora, tipo) VALUES (?, ?, ?, ?, 'enviado', NOW(), 'text')");
                                if ($stmt_ana) {
                                    $stmt_ana->bind_param('iiss', $canal_id, $cliente_id, $numero_limpo, $resultado['resposta_ana']);
                                    $stmt_ana->execute();
                                    echo "    🤖 Ana respondeu\n";
                                    $stmt_ana->close();
                                }
                            }
                        } catch (Exception $e) {
                            echo "    ⚠️ Erro ao processar Ana: " . $e->getMessage() . "\n";
                        }
                    }
                    
                    // Invalidar cache
                    $cache_file = __DIR__ . '/painel/cache/conversas_recentes.cache';
                    if (file_exists($cache_file)) {
                        unlink($cache_file);
                    }
                    
                }
                $stmt->close();
            }
        }
    }
}

// Evitar execução simultânea
$lock_file = __DIR__ . '/polling_whatsapp.lock';
if (file_exists($lock_file)) {
    $lock_time = filemtime($lock_file);
    if (time() - $lock_time < 30) { // Se lock tem menos de 30s, pular
        exit("Polling já em execução\n");
    }
    unlink($lock_file); // Lock muito antigo, remover
}
file_put_contents($lock_file, time());

// Função de cleanup
register_shutdown_function(function() use ($lock_file) {
    if (file_exists($lock_file)) {
        unlink($lock_file);
    }
});

require_once __DIR__ . '/config.php';

echo "[" . date('Y-m-d H:i:s') . "] Iniciando polling WhatsApp...\n";

try {
    $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($mysqli->connect_error) {
        throw new Exception("Erro conexão BD: " . $mysqli->connect_error);
    }
    
    // VPS URLs e configurações
    $vps_configs = [
        [
            'nome' => 'Canal Ana',
            'url' => 'http://212.85.11.238:3000',
            'session' => 'default',
            'canal_id' => 36,
            'numero_canal' => '554797146908'
        ],
        [
            'nome' => 'Canal Humano', 
            'url' => 'http://212.85.11.238:3001',
            'session' => 'comercial',
            'canal_id' => 37,
            'numero_canal' => '554797309525'
        ]
    ];
    
    foreach ($vps_configs as $config) {
        echo "Verificando {$config['nome']}...\n";
        
        // 1. Buscar mensagens recentes do VPS (se API suportar)
        $endpoints_to_try = [
            '/messages/recent',
            '/messages?limit=10',
            '/chat/messages',
            '/sessions/' . $config['session'] . '/messages',
            '/chats/messages'
        ];
        
        $mensagens_vps = [];
        foreach ($endpoints_to_try as $endpoint) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $config['url'] . $endpoint);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            
            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($http_code == 200) {
                $data = json_decode($response, true);
                if ($data && is_array($data)) {
                    $mensagens_vps = $data;
                    echo "  Encontrou endpoint: $endpoint\n";
                    break;
                }
            }
        }
        
        // 2. Se não encontrou endpoint de mensagens, pular por enquanto
        if (empty($mensagens_vps)) {
            echo "  Nenhum endpoint de mensagens encontrado, continuando...\n";
            continue;
        }
        
        // 3. Processar mensagens encontradas
        foreach ($mensagens_vps as $msg_vps) {
            // Verificar se é mensagem recebida (não enviada por nós)
            $from = $msg_vps['from'] ?? '';
            $body = $msg_vps['body'] ?? $msg_vps['message'] ?? '';
            $timestamp = $msg_vps['timestamp'] ?? $msg_vps['time'] ?? time();
            
            // Pular se vazio ou se é do nosso próprio número
            if (empty($body) || empty($from)) continue;
            if (strpos($from, $config['numero_canal']) !== false) continue;
            
            // Verificar se já existe no banco
            $numero_limpo = preg_replace('/[^0-9]/', '', $from);
            $stmt = $mysqli->prepare("
                SELECT id FROM mensagens_comunicacao 
                WHERE numero_whatsapp = ? AND mensagem = ? AND data_hora >= DATE_SUB(NOW(), INTERVAL 1 HOUR)
            ");
            $stmt->bind_param('ss', $numero_limpo, $body);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows == 0) {
                // Mensagem nova! Processar
                echo "  💬 Nova mensagem de $numero_limpo: " . substr($body, 0, 30) . "...\n";
                
                // Simular webhook call
                $webhook_data = [
                    'from' => $from,
                    'to' => $config['numero_canal'] . '@c.us',
                    'body' => $body,
                    'message' => $body,
                    'timestamp' => $timestamp
                ];
                
                // Chamar webhook localmente
                processarWebhookLocal($webhook_data, $mysqli);
            }
            $stmt->close();
        }
    }
    
    echo "Polling concluído.\n";
    
} catch (Exception $e) {
    echo "ERRO: " . $e->getMessage() . "\n";
} finally {
    // Fechar conexão se ainda estiver aberta
    if (isset($mysqli) && $mysqli instanceof mysqli && !$mysqli->connect_error) {
        $mysqli->close();
    }
    
    // Remover lock
    if (file_exists($lock_file)) {
        unlink($lock_file);
    }
}
?> 