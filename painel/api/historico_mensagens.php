<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../db.php';
require_once '../cache_manager.php';

header('Content-Type: text/html; charset=utf-8');
header('Cache-Control: private, max-age=10'); // Cache HTTP de 10 segundos

$cliente_id = isset($_GET['cliente_id']) ? intval($_GET['cliente_id']) : 0;

if (!$cliente_id) {
    echo '<div class="text-center text-gray-400">Cliente não encontrado.</div>';
    exit;
}

// Cache para mensagens com invalidação inteligente
echo cache_remember("historico_html_{$cliente_id}", function() use ($cliente_id, $mysqli) {
    $cliente = cache_cliente($cliente_id, $mysqli);
    
    if (!$cliente) {
        return '<div class="text-center text-gray-400">Cliente não encontrado.</div>';
    }
    
    // Marcar mensagens como lidas (operação que não deve ser cached)
    $mysqli->query("UPDATE mensagens_comunicacao SET status = 'lido' WHERE cliente_id = $cliente_id AND direcao = 'recebido'");
    
    // Buscar mensagens com cache específico
    $mensagens = cache_remember("mensagens_{$cliente_id}", function() use ($cliente_id, $mysqli) {
        $sql = "SELECT m.*, c.nome_exibicao as canal_nome, c.porta as canal_porta
                FROM mensagens_comunicacao m
                LEFT JOIN canais_comunicacao c ON m.canal_id = c.id
                WHERE m.cliente_id = ?
                ORDER BY m.data_hora ASC";
        
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param('i', $cliente_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $mensagens = [];
        while ($msg = $result->fetch_assoc()) {
            $mensagens[] = $msg;
        }
        $stmt->close();
        
        return $mensagens;
    }, 20); // Cache de 20 segundos para mensagens
    
    // Renderizar apenas as mensagens
    ob_start();
    
    if (count($mensagens) > 0) {
        foreach ($mensagens as $msg) {
            $is_received = $msg['direcao'] === 'recebido';
            $message_class = $is_received ? 'received' : 'sent';
            $status_icon = '';
            
            if (!$is_received) {
                if ($msg['status'] === 'lido') {
                    $status_icon = '<span style="color:#4f46e5;font-size:1em;vertical-align:middle;">✔✔</span>';
                } elseif ($msg['status'] === 'entregue') {
                    $status_icon = '<span style="color:#888;font-size:1em;vertical-align:middle;">✔✔</span>';
                } elseif ($msg['status'] === 'enviado') {
                    $status_icon = '<span style="color:#888;font-size:1em;vertical-align:middle;">✔</span>';
                }
            }
            
            // Determinar o nome do canal para exibição
            $canal_nome = $msg['canal_nome'] ?? 'WhatsApp';
            $canal_porta = $msg['canal_porta'] ?? 0;
            
            // Formatar nome do canal baseado na porta
            if ($canal_porta === 3001) {
                $canal_nome = "Comercial - Pixel";
            } elseif ($canal_porta === 3000) {
                $canal_nome = "Financeiro - Pixel";
            }
            
            $conteudo = '';
            if (!empty($msg['anexo'])) {
                // Filtrar apenas anexos reais, não respostas da API
                $anexo = $msg['anexo'];
                
                // Remover informações de API response do anexo
                if (strpos($anexo, '|api_response:') !== false) {
                    $anexo = explode('|api_response:', $anexo)[0];
                }
                if (strpos($anexo, '|api_id:') !== false) {
                    $anexo = explode('|api_id:', $anexo)[0];
                }
                
                // Só processar se ainda houver conteúdo após remover respostas da API
                if (!empty($anexo) && !preg_match('/^[a-zA-Z0-9\/\-_\.]+$/', $anexo)) {
                    $ext = strtolower(pathinfo($anexo, PATHINFO_EXTENSION));
                    if (in_array($ext, ['jpg','jpeg','png','gif','bmp','webp'])) {
                        $conteudo .= '<a href="' . htmlspecialchars($anexo) . '" target="_blank"><img src="' . htmlspecialchars($anexo) . '" alt="anexo" style="max-width:140px;max-height:90px;border-radius:8px;box-shadow:0 1px 4px #0001;margin-bottom:4px;"></a><br>';
                    } else {
                        $nome_arquivo = basename($anexo);
                        $conteudo .= '<a href="' . htmlspecialchars($anexo) . '" target="_blank" style="color:#7c2ae8;text-decoration:underline;"><span style="color:#7c2ae8;">📎</span> ' . htmlspecialchars($nome_arquivo) . '</a><br>';
                    }
                }
            }
            $conteudo .= htmlspecialchars($msg['mensagem']);
            
            echo '<div class="message ' . $message_class . '">';
            
            // Adicionar informação do canal para mensagens recebidas
            if ($is_received && $canal_nome !== 'WhatsApp') {
                echo '<div class="message-contact-info">';
                echo '<span class="contact-name">' . strtoupper($canal_nome) . '</span>';
                echo '<span class="channel-info">via ' . htmlspecialchars($canal_nome) . '</span>';
                echo '</div>';
            }
            
            echo '<div class="message-bubble">';
            echo $conteudo;
            echo '<div class="message-time">' . date('H:i', strtotime($msg['data_hora'])) . ' ' . $status_icon . '</div>';
            echo '</div>';
            echo '</div>';
        }
    } else {
        echo '<div class="text-center text-gray-400">Nenhuma mensagem nesta conversa.</div>';
    }
    
    return ob_get_clean();
}, 10); // Cache de 10 segundos para o HTML completo
?> 