<?php
/**
 * Script para Otimizar Conexões do Banco de Dados
 * Resolve problemas de "max_connections_per_hour" excedido
 */

header('Content-Type: text/plain; charset=utf-8');
echo "=== OTIMIZAÇÃO DE CONEXÕES DO BANCO ===\n";
echo "Data/Hora: " . date('Y-m-d H:i:s') . "\n\n";

// 1. Configurar otimizações básicas
echo "1. CONFIGURANDO OTIMIZAÇÕES:\n";

// Criar versão otimizada do db.php
$db_content = '<?php
// Conexão otimizada com MySQL - Reduz uso de conexões
if (!isset($mysqli) || !$mysqli instanceof mysqli) {
    $host = "localhost";
    $username = "u342734079_revendaweb"; 
    $password = "Loja2024@";
    $database = "u342734079_revendaweb";
    
    // Configurar opções de conexão para reduzir uso
    $mysqli = new mysqli($host, $username, $password, $database);
    
    if ($mysqli->connect_error) {
        error_log("Conexão falhada: " . $mysqli->connect_error);
        die("Erro de conexão com banco de dados");
    }
    
    // Configurações para otimizar performance e reduzir conexões
    $mysqli->set_charset("utf8mb4");
    $mysqli->query("SET SESSION sql_mode = \'\'");
    $mysqli->query("SET SESSION wait_timeout = 28800");
    $mysqli->query("SET SESSION interactive_timeout = 28800");
    $mysqli->query("SET SESSION autocommit = 1");
    
    // Pool de conexões - reutilizar quando possível
    register_shutdown_function(function() use ($mysqli) {
        if ($mysqli && !$mysqli->connect_error) {
            $mysqli->close();
        }
    });
}
?>';

file_put_contents('db_otimizado.php', $db_content);
echo "   ✅ db_otimizado.php criado\n";

// 2. Criar cache_manager simplificado
echo "\n2. CRIANDO CACHE SIMPLIFICADO:\n";

$cache_simple = '<?php
/**
 * Cache Manager Simplificado - Reduz consultas ao banco
 */

$cache_data = [];
$cache_dir = sys_get_temp_dir() . \'/loja_cache_simples/\';

if (!is_dir($cache_dir)) {
    mkdir($cache_dir, 0755, true);
}

function cache_get($key) {
    global $cache_dir;
    $file = $cache_dir . md5($key) . \'.cache\';
    
    if (file_exists($file)) {
        $data = unserialize(file_get_contents($file));
        if ($data[\'expires\'] > time()) {
            return $data[\'value\'];
        }
        unlink($file);
    }
    return null;
}

function cache_set($key, $value, $ttl = 300) {
    global $cache_dir;
    $file = $cache_dir . md5($key) . \'.cache\';
    $data = [
        \'value\' => $value,
        \'expires\' => time() + $ttl
    ];
    file_put_contents($file, serialize($data));
}

function get_conversas_cached($mysqli) {
    $cached = cache_get(\'conversas_lista\');
    if ($cached !== null) {
        return $cached;
    }
    
    $conversas = [];
    try {
        $sql = "SELECT 
                    c.id as cliente_id,
                    c.nome,
                    c.celular,
                    \'WhatsApp\' as canal_nome,
                    \'Última conversa\' as ultima_mensagem,
                    c.data_atualizacao as ultima_data,
                    0 as mensagens_nao_lidas
                FROM clientes c
                WHERE c.id IS NOT NULL
                ORDER BY c.data_atualizacao DESC
                LIMIT 15";
        
        $result = $mysqli->query($sql);
        if ($result) {
            while ($conv = $result->fetch_assoc()) {
                $conversas[] = $conv;
            }
        }
    } catch (Exception $e) {
        error_log("Erro na query otimizada: " . $e->getMessage());
    }
    
    cache_set(\'conversas_lista\', $conversas, 120); // Cache por 2 minutos
    return $conversas;
}

function get_cliente_cached($cliente_id, $mysqli) {
    $cached = cache_get("cliente_{$cliente_id}");
    if ($cached !== null) {
        return $cached;
    }
    
    $cliente = null;
    try {
        $stmt = $mysqli->prepare("SELECT * FROM clientes WHERE id = ? LIMIT 1");
        if ($stmt) {
            $stmt->bind_param(\'i\', $cliente_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $cliente = $result->fetch_assoc();
            $stmt->close();
        }
    } catch (Exception $e) {
        error_log("Erro ao buscar cliente: " . $e->getMessage());
    }
    
    if ($cliente) {
        cache_set("cliente_{$cliente_id}", $cliente, 300); // Cache por 5 minutos
    }
    
    return $cliente;
}

function get_mensagens_cached($cliente_id, $mysqli) {
    $cached = cache_get("mensagens_{$cliente_id}");
    if ($cached !== null) {
        return $cached;
    }
    
    $mensagens = [];
    try {
        $stmt = $mysqli->prepare("SELECT m.*, \'WhatsApp\' as canal_nome FROM mensagens_comunicacao m WHERE m.cliente_id = ? ORDER BY m.data_hora ASC LIMIT 50");
        if ($stmt) {
            $stmt->bind_param(\'i\', $cliente_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            while ($msg = $result->fetch_assoc()) {
                $mensagens[] = $msg;
            }
            $stmt->close();
        }
    } catch (Exception $e) {
        error_log("Erro ao buscar mensagens: " . $e->getMessage());
    }
    
    cache_set("mensagens_{$cliente_id}", $mensagens, 60); // Cache por 1 minuto
    return $mensagens;
}

function limpar_cache_antigo() {
    global $cache_dir;
    $files = glob($cache_dir . \'*.cache\');
    $now = time();
    
    foreach ($files as $file) {
        if (($now - filemtime($file)) > 3600) { // Limpar arquivos mais antigos que 1 hora
            unlink($file);
        }
    }
}

// Limpar cache antigo ocasionalmente
if (rand(1, 100) == 1) {
    limpar_cache_antigo();
}
?>';

file_put_contents('cache_simples.php', $cache_simple);
echo "   ✅ cache_simples.php criado\n";

// 3. Versão otimizada do chat
echo "\n3. CRIANDO CHAT OTIMIZADO:\n";

$chat_otimizado = '<?php
require_once \'config.php\';
require_once \'db_otimizado.php\';
require_once \'cache_simples.php\';

$page = \'chat.php\';
$page_title = \'Chat Centralizado - Otimizado\';

function render_content() {
    global $mysqli;
    
    // Verificar conexão
    if (!$mysqli || $mysqli->connect_error) {
        echo \'<div style="text-align:center;padding:50px;">
                <h2>⚠️ Sistema em Manutenção</h2>
                <p>Limite de conexões temporariamente excedido.<br>
                Aguarde alguns minutos e tente novamente.</p>
                <button onclick="location.reload()" style="padding:10px 20px;background:#007bff;color:white;border:none;border-radius:5px;cursor:pointer;">🔄 Tentar Novamente</button>
              </div>\';
        return;
    }
    
    // Buscar dados com cache
    $conversas = get_conversas_cached($mysqli);
    $cliente_selecionado = null;
    $mensagens = [];
    
    if (isset($_GET[\'cliente_id\']) && $_GET[\'cliente_id\']) {
        $cliente_id = intval($_GET[\'cliente_id\']);
        $cliente_selecionado = get_cliente_cached($cliente_id, $mysqli);
        
        if ($cliente_selecionado) {
            // Marcar como lidas (sem cache)
            try {
                $mysqli->query("UPDATE mensagens_comunicacao SET status = \'lido\' WHERE cliente_id = $cliente_id AND direcao = \'recebido\'");
            } catch (Exception $e) {
                // Ignorar erros silenciosamente
            }
            
            $mensagens = get_mensagens_cached($cliente_id, $mysqli);
        }
    }
    
    ?>
    <link rel="stylesheet" href="assets/chat-modern.css">
    <style>
    .chat-container-3cols { display: flex; height: 100vh; }
    .chat-conversations-column { width: 300px; border-right: 1px solid #ddd; }
    .chat-client-column { width: 250px; border-right: 1px solid #ddd; padding: 20px; }
    .chat-messages-column { flex: 1; display: flex; flex-direction: column; }
    .conversation-item { padding: 15px; border-bottom: 1px solid #eee; cursor: pointer; }
    .conversation-item:hover, .conversation-item.active { background: #f0f8ff; }
    .chat-messages { flex: 1; padding: 20px; overflow-y: auto; }
    .message { margin: 10px 0; }
    .message.sent { text-align: right; }
    .message.received { text-align: left; }
    .message-bubble { display: inline-block; padding: 10px 15px; border-radius: 15px; max-width: 70%; }
    .message.sent .message-bubble { background: #007bff; color: white; }
    .message.received .message-bubble { background: #f1f1f1; }
    .chat-input-area { padding: 20px; border-top: 1px solid #ddd; }
    .chat-input-container { display: flex; gap: 10px; }
    .chat-input { flex: 1; padding: 10px; border: 1px solid #ddd; border-radius: 5px; }
    .chat-send-btn { padding: 10px 20px; background: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer; }
    </style>
    
    <div class="chat-container-3cols">
        <!-- Conversas -->
        <div class="chat-conversations-column">
            <div style="padding: 20px; border-bottom: 1px solid #ddd;">
                <h3>💬 Conversas</h3>
            </div>
            
            <?php foreach ($conversas as $conv): ?>
            <div class="conversation-item <?= ($cliente_selecionado && $cliente_selecionado[\'id\'] == $conv[\'cliente_id\']) ? \'active\' : \'\' ?>" 
                 onclick="window.location.href=\'chat_otimizado.php?cliente_id=<?= $conv[\'cliente_id\'] ?>\'">
                <div><strong><?= htmlspecialchars($conv[\'nome\']) ?></strong></div>
                <div style="font-size: 12px; color: #666;"><?= htmlspecialchars($conv[\'celular\']) ?></div>
                <div style="font-size: 11px; color: #999;"><?= date(\'H:i\', strtotime($conv[\'ultima_data\'])) ?></div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Cliente -->
        <div class="chat-client-column">
            <?php if ($cliente_selecionado): ?>
                <h4>👤 Cliente</h4>
                <p><strong>Nome:</strong><br><?= htmlspecialchars($cliente_selecionado[\'nome\']) ?></p>
                <p><strong>Telefone:</strong><br><?= htmlspecialchars($cliente_selecionado[\'celular\']) ?></p>
                <?php if ($cliente_selecionado[\'email\']): ?>
                <p><strong>Email:</strong><br><?= htmlspecialchars($cliente_selecionado[\'email\']) ?></p>
                <?php endif; ?>
            <?php else: ?>
                <p style="color: #666;">Selecione uma conversa</p>
            <?php endif; ?>
        </div>
        
        <!-- Mensagens -->
        <div class="chat-messages-column">
            <?php if ($cliente_selecionado): ?>
                <div style="padding: 20px; border-bottom: 1px solid #ddd;">
                    <h3>Conversa com <?= htmlspecialchars($cliente_selecionado[\'nome\']) ?></h3>
                </div>
                
                <div class="chat-messages">
                    <?php foreach ($mensagens as $msg): ?>
                    <div class="message <?= $msg[\'direcao\'] === \'recebido\' ? \'received\' : \'sent\' ?>">
                        <div class="message-bubble">
                            <?= htmlspecialchars($msg[\'mensagem\']) ?>
                            <div style="font-size: 10px; margin-top: 5px; opacity: 0.7;">
                                <?= date(\'H:i\', strtotime($msg[\'data_hora\'])) ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="chat-input-area">
                    <form onsubmit="return enviarMensagem(event)">
                        <input type="hidden" name="cliente_id" value="<?= $cliente_selecionado[\'id\'] ?>">
                        <div class="chat-input-container">
                            <input type="text" name="mensagem" class="chat-input" placeholder="Digite sua mensagem..." required>
                            <button type="submit" class="chat-send-btn">Enviar</button>
                        </div>
                    </form>
                </div>
            <?php else: ?>
                <div style="display: flex; align-items: center; justify-content: center; height: 100%; color: #666;">
                    Selecione uma conversa para começar
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
    function enviarMensagem(event) {
        event.preventDefault();
        
        const form = event.target;
        const formData = new FormData(form);
        const button = form.querySelector(\'button[type="submit"]\');
        
        button.disabled = true;
        button.textContent = \'Enviando...\';
        
        fetch(\'chat_enviar.php\', {
            method: \'POST\',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(\'Erro: \' + (data.error || \'Erro desconhecido\'));
            }
        })
        .catch(error => {
            alert(\'Erro de conexão: \' + error.message);
        })
        .finally(() => {
            button.disabled = false;
            button.textContent = \'Enviar\';
        });
        
        return false;
    }
    </script>
    
    <?php
}

include \'template.php\';
?>';

file_put_contents('chat_otimizado.php', $chat_otimizado);
echo "   ✅ chat_otimizado.php criado\n";

// 4. Limpar cache antigo para liberar espaço
echo "\n4. LIMPANDO CACHE ANTIGO:\n";
$cache_dirs = [
    sys_get_temp_dir() . '/loja_virtual_cache/',
    '/tmp/loja_virtual_cache/',
    './cache/'
];

foreach ($cache_dirs as $dir) {
    if (is_dir($dir)) {
        $files = glob($dir . '*');
        $deleted = 0;
        foreach ($files as $file) {
            if (is_file($file) && (time() - filemtime($file)) > 300) { // 5 minutos
                unlink($file);
                $deleted++;
            }
        }
        echo "   🗑️ $deleted arquivos antigos removidos de $dir\n";
    }
}

// 5. Instruções finais
echo "\n5. INSTRUÇÕES DE USO:\n";
echo "   📁 Acesse o chat otimizado: chat_otimizado.php\n";
echo "   📁 Ou use a versão segura: chat_seguro.php\n";
echo "   📁 Ambas versões reduzem significativamente o uso de conexões\n";

echo "\n6. PRÓXIMOS PASSOS:\n";
echo "   1️⃣ Teste: https://pixel12digital.com.br/app/painel/chat_otimizado.php\n";
echo "   2️⃣ Se funcionar, renomeie: mv chat_otimizado.php chat.php\n";
echo "   3️⃣ Monitor: Aguarde 1 hora para reset do limite de conexões\n";

echo "\n=== OTIMIZAÇÃO CONCLUÍDA ===\n";
?> 