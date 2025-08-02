<?php
/**
 * 🚀 GESTÃO DE TRANSFERÊNCIAS - PIXEL12DIGITAL
 * 
 * Interface web para monitorar e gerenciar transferências da Ana
 */

require_once 'db.php';
require_once 'template.php';

// Processar ações
if ($_POST) {
    if ($_POST['acao'] === 'executar_transferencias') {
        require_once 'api/executar_transferencias.php';
        $executor = new ExecutorTransferencias($mysqli);
        $resultado = $executor->processarTransferenciasPendentes();
        
        $mensagem = $resultado['success'] ? 
            "✅ Transferências processadas: {$resultado['transferencias_rafael']} para Rafael, {$resultado['transferencias_humanas']} para humanos" : 
            "❌ Erro ao processar transferências: " . implode(', ', $resultado['erros']);
    }
    
    if ($_POST['acao'] === 'desbloquear_ana') {
        $numero = $_POST['numero_cliente'];
        $sql = "UPDATE bloqueios_ana SET ativo = 0, data_desbloqueio = NOW() WHERE numero_cliente = ?";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param('s', $numero);
        $stmt->execute();
        $mensagem = "✅ Ana desbloqueada para cliente $numero";
    }
}

// Buscar estatísticas
$stats = [];

// Transferências pendentes
$stats['rafael_pendentes'] = $mysqli->query("SELECT COUNT(*) as total FROM transferencias_rafael WHERE status = 'pendente'")->fetch_assoc()['total'];
$stats['humanos_pendentes'] = $mysqli->query("SELECT COUNT(*) as total FROM transferencias_humano WHERE status = 'pendente'")->fetch_assoc()['total'];

// Transferências hoje
$stats['rafael_hoje'] = $mysqli->query("SELECT COUNT(*) as total FROM transferencias_rafael WHERE DATE(data_transferencia) = CURDATE()")->fetch_assoc()['total'];
$stats['humanos_hoje'] = $mysqli->query("SELECT COUNT(*) as total FROM transferencias_humano WHERE DATE(data_transferencia) = CURDATE()")->fetch_assoc()['total'];

// Clientes bloqueados
$stats['bloqueados'] = $mysqli->query("SELECT COUNT(*) as total FROM bloqueios_ana WHERE ativo = 1")->fetch_assoc()['total'];

// Últimas transferências Rafael
$transferencias_rafael = $mysqli->query("
    SELECT *, 
           CASE 
               WHEN status = 'pendente' THEN '🟡'
               WHEN status = 'notificado' THEN '🟢'
               ELSE '🔴'
           END as status_icon
    FROM transferencias_rafael 
    ORDER BY data_transferencia DESC 
    LIMIT 10
")->fetch_all(MYSQLI_ASSOC);

// Últimas transferências humanos
$transferencias_humanos = $mysqli->query("
    SELECT *, 
           CASE 
               WHEN status = 'pendente' THEN '🟡'
               WHEN status = 'transferido' THEN '🟢'
               ELSE '🔴'
           END as status_icon
    FROM transferencias_humano 
    ORDER BY data_transferencia DESC 
    LIMIT 10
")->fetch_all(MYSQLI_ASSOC);

// Clientes bloqueados
$clientes_bloqueados = $mysqli->query("
    SELECT * FROM bloqueios_ana 
    WHERE ativo = 1 
    ORDER BY data_bloqueio DESC 
    LIMIT 10
")->fetch_all(MYSQLI_ASSOC);

renderizar_template('Gestão de Transferências', function() use ($stats, $transferencias_rafael, $transferencias_humanos, $clientes_bloqueados, $mensagem) {
?>

<style>
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.stat-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 20px;
    border-radius: 10px;
    text-align: center;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
}

.stat-number {
    font-size: 2.5em;
    font-weight: bold;
    margin-bottom: 5px;
}

.stat-label {
    font-size: 0.9em;
    opacity: 0.9;
}

.transferencias-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 30px;
    margin-bottom: 30px;
}

.transferencia-card {
    background: white;
    border-radius: 10px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    overflow: hidden;
}

.card-header {
    background: #f8f9fa;
    padding: 15px 20px;
    border-bottom: 1px solid #dee2e6;
    font-weight: bold;
    display: flex;
    justify-content: between;
    align-items: center;
}

.card-body {
    padding: 20px;
}

.transferencia-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 0;
    border-bottom: 1px solid #eee;
}

.transferencia-item:last-child {
    border-bottom: none;
}

.cliente-info {
    flex: 1;
}

.cliente-numero {
    font-weight: bold;
    color: #333;
}

.cliente-mensagem {
    font-size: 0.85em;
    color: #666;
    margin-top: 3px;
}

.transferencia-status {
    text-align: right;
}

.status-badge {
    display: inline-block;
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 0.8em;
    font-weight: bold;
}

.status-pendente { background: #fff3cd; color: #856404; }
.status-notificado { background: #d4edda; color: #155724; }
.status-transferido { background: #d1ecf1; color: #0c5460; }

.btn-grupo {
    display: flex;
    gap: 10px;
    margin-bottom: 20px;
}

.btn {
    padding: 12px 24px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-weight: bold;
    text-decoration: none;
    display: inline-block;
    text-align: center;
}

.btn-primary {
    background: #007bff;
    color: white;
}

.btn-success {
    background: #28a745;
    color: white;
}

.btn-warning {
    background: #ffc107;
    color: #212529;
}

.btn-danger {
    background: #dc3545;
    color: white;
}

.btn:hover {
    opacity: 0.9;
    transform: translateY(-1px);
}

.alert {
    padding: 15px;
    margin-bottom: 20px;
    border: 1px solid transparent;
    border-radius: 4px;
}

.alert-success {
    color: #155724;
    background-color: #d4edda;
    border-color: #c3e6cb;
}

.alert-danger {
    color: #721c24;
    background-color: #f8d7da;
    border-color: #f5c6cb;
}

@media (max-width: 768px) {
    .transferencias-grid {
        grid-template-columns: 1fr;
    }
    
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}
</style>

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>🚀 Gestão de Transferências</h2>
        <div class="btn-grupo">
            <form method="post" style="display: inline;">
                <input type="hidden" name="acao" value="executar_transferencias">
                <button type="submit" class="btn btn-primary">▶️ Executar Pendentes</button>
            </form>
            <a href="?refresh=1" class="btn btn-secondary">🔄 Atualizar</a>
        </div>
    </div>

    <?php if (isset($mensagem)): ?>
        <div class="alert <?= strpos($mensagem, '✅') !== false ? 'alert-success' : 'alert-danger' ?>">
            <?= $mensagem ?>
        </div>
    <?php endif; ?>

    <!-- Estatísticas -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-number"><?= $stats['rafael_pendentes'] ?></div>
            <div class="stat-label">📋 Rafael Pendentes</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?= $stats['humanos_pendentes'] ?></div>
            <div class="stat-label">👥 Humanos Pendentes</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?= $stats['rafael_hoje'] ?></div>
            <div class="stat-label">🌐 Rafael Hoje</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?= $stats['humanos_hoje'] ?></div>
            <div class="stat-label">🤝 Humanos Hoje</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?= $stats['bloqueados'] ?></div>
            <div class="stat-label">🚫 Ana Bloqueada</div>
        </div>
    </div>

    <!-- Transferências -->
    <div class="transferencias-grid">
        <!-- Transferências Rafael -->
        <div class="transferencia-card">
            <div class="card-header">
                <span>🌐 Transferências para Rafael</span>
                <span class="badge bg-primary"><?= count($transferencias_rafael) ?> recentes</span>
            </div>
            <div class="card-body">
                <?php if (empty($transferencias_rafael)): ?>
                    <p class="text-muted text-center">Nenhuma transferência registrada</p>
                <?php else: ?>
                    <?php foreach ($transferencias_rafael as $t): ?>
                        <div class="transferencia-item">
                            <div class="cliente-info">
                                <div class="cliente-numero"><?= $t['status_icon'] ?> <?= $t['numero_cliente'] ?></div>
                                <div class="cliente-mensagem"><?= substr($t['mensagem_original'], 0, 60) ?>...</div>
                                <small class="text-muted"><?= date('d/m/Y H:i', strtotime($t['data_transferencia'])) ?></small>
                            </div>
                            <div class="transferencia-status">
                                <span class="status-badge status-<?= $t['status'] ?>"><?= ucfirst($t['status']) ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Transferências Humanos -->
        <div class="transferencia-card">
            <div class="card-header">
                <span>👥 Transferências para Humanos</span>
                <span class="badge bg-success"><?= count($transferencias_humanos) ?> recentes</span>
            </div>
            <div class="card-body">
                <?php if (empty($transferencias_humanos)): ?>
                    <p class="text-muted text-center">Nenhuma transferência registrada</p>
                <?php else: ?>
                    <?php foreach ($transferencias_humanos as $t): ?>
                        <div class="transferencia-item">
                            <div class="cliente-info">
                                <div class="cliente-numero"><?= $t['status_icon'] ?> <?= $t['numero_cliente'] ?></div>
                                <div class="cliente-mensagem">
                                    <strong><?= $t['departamento'] ?>:</strong> 
                                    <?= substr($t['mensagem_original'], 0, 50) ?>...
                                </div>
                                <small class="text-muted"><?= date('d/m/Y H:i', strtotime($t['data_transferencia'])) ?></small>
                            </div>
                            <div class="transferencia-status">
                                <span class="status-badge status-<?= $t['status'] ?>"><?= ucfirst($t['status']) ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Clientes Bloqueados -->
    <div class="transferencia-card">
        <div class="card-header">
            <span>🚫 Clientes com Ana Bloqueada</span>
            <span class="badge bg-warning"><?= count($clientes_bloqueados) ?> ativos</span>
        </div>
        <div class="card-body">
            <?php if (empty($clientes_bloqueados)): ?>
                <p class="text-muted text-center">Nenhum cliente com Ana bloqueada</p>
            <?php else: ?>
                <?php foreach ($clientes_bloqueados as $b): ?>
                    <div class="transferencia-item">
                        <div class="cliente-info">
                            <div class="cliente-numero">🚫 <?= $b['numero_cliente'] ?></div>
                            <div class="cliente-mensagem">
                                <strong>Motivo:</strong> <?= ucfirst(str_replace('_', ' ', $b['motivo'])) ?>
                            </div>
                            <small class="text-muted">Bloqueado em: <?= date('d/m/Y H:i', strtotime($b['data_bloqueio'])) ?></small>
                        </div>
                        <div class="transferencia-status">
                            <form method="post" style="display: inline;">
                                <input type="hidden" name="acao" value="desbloquear_ana">
                                <input type="hidden" name="numero_cliente" value="<?= $b['numero_cliente'] ?>">
                                <button type="submit" class="btn btn-sm btn-warning" onclick="return confirm('Desbloquear Ana para este cliente?')">
                                    🔓 Desbloquear
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Informações -->
    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5>📊 Como Funciona</h5>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled">
                        <li><strong>🌐 Rafael:</strong> Sites/Ecommerce detectados automaticamente</li>
                        <li><strong>👥 Humanos:</strong> Cliente solicita atendimento humano</li>
                        <li><strong>🚫 Bloqueio:</strong> Ana para de atender após transferência</li>
                        <li><strong>📱 Notificação:</strong> WhatsApp automático para agentes</li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5>⚙️ Status do Sistema</h5>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled">
                        <li>✅ <strong>Ana:</strong> Ativa e detectando</li>
                        <li>✅ <strong>Executor:</strong> Processando automaticamente</li>
                        <li>✅ <strong>WhatsApp:</strong> Canais 3000 e 3001 funcionando</li>
                        <li>✅ <strong>Logs:</strong> Monitoramento completo ativo</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Auto-atualizar página a cada 2 minutos
setTimeout(() => {
    window.location.reload();
}, 120000);

// Confirmar ações críticas
document.querySelectorAll('form[method="post"]').forEach(form => {
    form.addEventListener('submit', function(e) {
        if (this.querySelector('input[name="acao"][value="executar_transferencias"]')) {
            if (!confirm('Executar todas as transferências pendentes?')) {
                e.preventDefault();
            }
        }
    });
});
</script>

<?php
});
?> 