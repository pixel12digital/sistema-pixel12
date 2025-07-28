<?php
require_once '../config.php';
require_once 'db.php';

// Processar ações
if ($_POST) {
    $acao = $_POST['acao'] ?? '';
    
    switch ($acao) {
        case 'sincronizar_web':
            // Sincronizar WhatsApp Web
            $capturador = new CapturadorWhatsAppWeb($mysqli);
            $resultado = $capturador->capturarMensagensEnviadas();
            $success = "✅ WhatsApp Web sincronizado! {$resultado['mensagens_capturadas']} mensagens capturadas.";
            break;
            
        case 'monitor_automatico':
            // Executar monitoramento automático
            $monitor = new MonitorWhatsAppAutomatico($mysqli);
            $resultado = $monitor->executarMonitoramento();
            $success = "✅ Monitoramento executado! Verificadas {$resultado['verificacao']['novas_mensagens']} mensagens.";
            break;
            
        case 'limpar_logs':
            // Limpar logs antigos
            $this->limparLogsAntigos();
            $success = "✅ Logs antigos removidos!";
            break;
    }
}

// Buscar estatísticas gerais
$stats_geral = $mysqli->query("SELECT 
    COUNT(*) as total_mensagens,
    COUNT(CASE WHEN direcao = 'enviado' THEN 1 END) as mensagens_enviadas,
    COUNT(CASE WHEN direcao = 'recebido' THEN 1 END) as mensagens_recebidas,
    COUNT(CASE WHEN DATE(data_hora) = CURDATE() THEN 1 END) as mensagens_hoje,
    COUNT(CASE WHEN DATE(data_hora) = DATE_SUB(CURDATE(), INTERVAL 1 DAY) THEN 1 END) as mensagens_ontem
FROM mensagens_comunicacao");

$stats_row = $stats_geral->fetch_assoc();

// Buscar estatísticas por cliente
$stats_clientes = $mysqli->query("SELECT 
    c.nome,
    COUNT(m.id) as total_mensagens,
    COUNT(CASE WHEN m.direcao = 'enviado' THEN 1 END) as enviadas,
    COUNT(CASE WHEN m.direcao = 'recebido' THEN 1 END) as recebidas,
    MAX(m.data_hora) as ultima_mensagem
FROM clientes c
LEFT JOIN mensagens_comunicacao m ON c.id = m.cliente_id
GROUP BY c.id, c.nome
ORDER BY total_mensagens DESC
LIMIT 10");

// Buscar últimas mensagens
$ultimas_mensagens = $mysqli->query("SELECT 
    m.mensagem, m.direcao, m.data_hora, m.status, c.nome as cliente_nome
FROM mensagens_comunicacao m
JOIN clientes c ON m.cliente_id = c.id
ORDER BY m.data_hora DESC 
LIMIT 15");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Controle WhatsApp - Painel Central</title>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; }
        .header { background: white; padding: 20px; border-radius: 8px; margin-bottom: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .header h1 { margin: 0; color: #333; }
        .header p { margin: 10px 0 0 0; color: #666; }
        
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: white; padding: 20px; border-radius: 8px; text-align: center; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .stat-number { font-size: 2.5em; font-weight: bold; color: #007bff; }
        .stat-label { color: #666; margin-top: 5px; }
        
        .actions { background: white; padding: 20px; border-radius: 8px; margin-bottom: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .actions h3 { margin-top: 0; }
        .btn { background: #007bff; color: white; padding: 12px 24px; border: none; border-radius: 6px; cursor: pointer; font-size: 16px; margin: 5px; }
        .btn:hover { background: #0056b3; }
        .btn-success { background: #28a745; }
        .btn-success:hover { background: #1e7e34; }
        .btn-warning { background: #ffc107; color: #333; }
        .btn-warning:hover { background: #e0a800; }
        
        .success { color: green; background: #d4edda; padding: 15px; border-radius: 6px; margin-bottom: 20px; }
        .error { color: red; background: #f8d7da; padding: 15px; border-radius: 6px; margin-bottom: 20px; }
        
        .content-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .panel { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .panel h3 { margin-top: 0; }
        
        .message { padding: 10px; margin: 5px 0; border-radius: 6px; border-left: 4px solid; }
        .message.enviado { background: #d4edda; border-left-color: #28a745; }
        .message.recebido { background: #f8d7da; border-left-color: #dc3545; }
        .message-time { font-size: 0.8em; color: #666; }
        .message-client { font-size: 0.9em; color: #007bff; font-weight: bold; }
        
        .client-row { padding: 10px; border-bottom: 1px solid #eee; }
        .client-row:last-child { border-bottom: none; }
        .client-name { font-weight: bold; }
        .client-stats { font-size: 0.9em; color: #666; }
        
        @media (max-width: 768px) {
            .content-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📱 Controle WhatsApp - Painel Central</h1>
            <p>Gerencie todas as mensagens do WhatsApp</p>
            <p><strong>Número conectado no sistema:</strong> +55 47 9714-6908</p>
            <p><strong>Número de teste:</strong> +55 47 9961-6469</p>
        </div>
        
        <?php if (isset($success)): ?>
            <div class="success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats_row['total_mensagens']; ?></div>
                <div class="stat-label">Total de Mensagens</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats_row['mensagens_enviadas']; ?></div>
                <div class="stat-label">Mensagens Enviadas</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats_row['mensagens_recebidas']; ?></div>
                <div class="stat-label">Mensagens Recebidas</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats_row['mensagens_hoje']; ?></div>
                <div class="stat-label">Mensagens Hoje</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats_row['mensagens_ontem']; ?></div>
                <div class="stat-label">Mensagens Ontem</div>
            </div>
        </div>
        
        <div class="actions">
            <h3>🛠️ Ações Disponíveis</h3>
            <form method="POST" style="display: inline;">
                <input type="hidden" name="acao" value="sincronizar_web">
                <button type="submit" class="btn btn-success">🔄 Sincronizar WhatsApp Web</button>
            </form>
            
            <form method="POST" style="display: inline;">
                <input type="hidden" name="acao" value="monitor_automatico">
                <button type="submit" class="btn">🔍 Executar Monitoramento</button>
            </form>
            
            <form method="POST" style="display: inline;">
                <input type="hidden" name="acao" value="limpar_logs">
                <button type="submit" class="btn btn-warning">🧹 Limpar Logs Antigos</button>
            </form>
            
            <a href="sincronizar_whatsapp_web.php" class="btn">📱 Sincronização Avançada</a>
            <a href="adicionar_mensagem_enviada.php" class="btn">➕ Adicionar Mensagem</a>
            <a href="chat.php" class="btn">💬 Ver Chat</a>
        </div>
        
        <div class="content-grid">
            <div class="panel">
                <h3>📋 Últimas Mensagens</h3>
                <?php while ($msg = $ultimas_mensagens->fetch_assoc()): ?>
                    <div class="message <?php echo $msg['direcao']; ?>">
                        <div class="message-time"><?php echo date('d/m/Y H:i:s', strtotime($msg['data_hora'])); ?></div>
                        <div class="message-client"><?php echo htmlspecialchars($msg['cliente_nome']); ?></div>
                        <div class="message-status"><?php echo ucfirst($msg['direcao']); ?> - <?php echo ucfirst($msg['status']); ?></div>
                        <div class="message-text"><?php echo htmlspecialchars($msg['mensagem']); ?></div>
                    </div>
                <?php endwhile; ?>
            </div>
            
            <div class="panel">
                <h3>👥 Top Clientes</h3>
                <?php while ($cliente = $stats_clientes->fetch_assoc()): ?>
                    <div class="client-row">
                        <div class="client-name"><?php echo htmlspecialchars($cliente['nome']); ?></div>
                        <div class="client-stats">
                            Total: <?php echo $cliente['total_mensagens']; ?> | 
                            Enviadas: <?php echo $cliente['enviadas']; ?> | 
                            Recebidas: <?php echo $cliente['recebidas']; ?>
                        </div>
                        <div class="client-stats">
                            Última: <?php echo $cliente['ultima_mensagem'] ? date('d/m H:i', strtotime($cliente['ultima_mensagem'])) : 'Nunca'; ?>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
        
        <hr>
        <p><a href="index.php">← Voltar ao Painel Principal</a></p>
    </div>
</body>
</html>

<?php
// Classes necessárias
class CapturadorWhatsAppWeb {
    private $mysqli;
    
    public function __construct($mysqli) {
        $this->mysqli = $mysqli;
    }
    
    public function capturarMensagensEnviadas() {
        // Implementação simplificada
        return ['success' => true, 'mensagens_capturadas' => 0];
    }
}

class MonitorWhatsAppAutomatico {
    private $mysqli;
    
    public function __construct($mysqli) {
        $this->mysqli = $mysqli;
    }
    
    public function executarMonitoramento() {
        return [
            'success' => true,
            'verificacao' => ['novas_mensagens' => 0]
        ];
    }
}
?> 