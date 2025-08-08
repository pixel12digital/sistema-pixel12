<?php
$page = 'fatura_detalhes.php';
session_start();
if (!isset($_SESSION['logado']) || !$_SESSION['logado']) {
    header('Location: index.php');
    exit;
}

require_once __DIR__ . '/../config.php';
require_once 'db.php';

$fatura_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$fatura_id) {
    header('Location: faturas.php');
    exit;
}

// Buscar fatura com dados do cliente
$fatura = fetchOne("
    SELECT f.*, c.nome as cliente_nome, c.email as cliente_email, c.celular as cliente_celular
    FROM faturas f 
    LEFT JOIN clientes c ON f.cliente_id = c.id 
    WHERE f.id = ?
", [$fatura_id], 'i');

if (!$fatura) {
    header('Location: faturas.php');
    exit;
}

$page_title = 'Detalhes da Fatura #' . $fatura_id;
$custom_header = '
  <div class="flex gap-4 items-center">
    <a href="faturas.php" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-md text-decoration-none">← Voltar</a>
    <button class="bg-purple-600 hover:bg-purple-800 transition-colors px-4 py-2 rounded-md" onclick="sincronizarFatura(<?= $fatura_id ?>)">Sincronizar</button>
  </div>
';

function render_content() {
    global $fatura;
    ?>
    <style>
    .detalhes-container {
        background: white;
        padding: 2rem;
        border-radius: 8px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        margin-bottom: 1rem;
    }
    .detalhes-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 2rem;
        margin-top: 1rem;
    }
    .detalhes-section {
        background: #f9fafb;
        padding: 1.5rem;
        border-radius: 8px;
    }
    .detalhes-section h3 {
        color: #374151;
        font-size: 1.1rem;
        font-weight: 600;
        margin-bottom: 1rem;
        border-bottom: 2px solid #e5e7eb;
        padding-bottom: 0.5rem;
    }
    .detalhes-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.75rem 0;
        border-bottom: 1px solid #e5e7eb;
    }
    .detalhes-item:last-child {
        border-bottom: none;
    }
    .detalhes-label {
        font-weight: 500;
        color: #374151;
    }
    .detalhes-value {
        color: #6b7280;
    }
    .status-badge {
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 0.75rem;
        font-weight: 500;
    }
    .status-pending { background-color: #fef3c7; color: #92400e; }
    .status-received { background-color: #d1fae5; color: #065f46; }
    .status-overdue { background-color: #fee2e2; color: #991b1b; }
    .status-cancelled { background-color: #f3f4f6; color: #374151; }
    .acoes-container {
        background: white;
        padding: 1.5rem;
        border-radius: 8px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        margin-top: 1rem;
    }
    .acoes-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
    }
    .acao-btn {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.75rem 1rem;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-weight: 500;
        text-decoration: none;
        text-align: center;
        transition: all 0.2s;
    }
    .acao-btn.primary {
        background: #7c3aed;
        color: white;
    }
    .acao-btn.primary:hover {
        background: #6d28d9;
    }
    .acao-btn.secondary {
        background: #f3f4f6;
        color: #374151;
    }
    .acao-btn.secondary:hover {
        background: #e5e7eb;
    }
    .acao-btn.danger {
        background: #ef4444;
        color: white;
    }
    .acao-btn.danger:hover {
        background: #dc2626;
    }
    </style>

    <div class="detalhes-container">
        <h2 class="text-2xl font-bold text-gray-800 mb-4">Fatura #<?= htmlspecialchars($fatura['id']) ?></h2>
        
        <div class="detalhes-grid">
            <div class="detalhes-section">
                <h3>Informações da Fatura</h3>
                <div class="detalhes-item">
                    <span class="detalhes-label">ID:</span>
                    <span class="detalhes-value"><?= htmlspecialchars($fatura['id']) ?></span>
                </div>
                <div class="detalhes-item">
                    <span class="detalhes-label">Valor:</span>
                    <span class="detalhes-value font-medium">R$ <?= number_format($fatura['valor'], 2, ',', '.') ?></span>
                </div>
                <div class="detalhes-item">
                    <span class="detalhes-label">Status:</span>
                    <span class="status-badge status-<?= strtolower($fatura['status']) ?>">
                        <?= htmlspecialchars($fatura['status']) ?>
                    </span>
                </div>
                <div class="detalhes-item">
                    <span class="detalhes-label">Vencimento:</span>
                    <span class="detalhes-value"><?= $fatura['due_date'] ? date('d/m/Y', strtotime($fatura['due_date'])) : '-' ?></span>
                </div>
                <div class="detalhes-item">
                    <span class="detalhes-label">Data Criação:</span>
                    <span class="detalhes-value"><?= $fatura['created_at'] ? date('d/m/Y H:i', strtotime($fatura['created_at'])) : '-' ?></span>
                </div>
                <div class="detalhes-item">
                    <span class="detalhes-label">Última Atualização:</span>
                    <span class="detalhes-value"><?= $fatura['updated_at'] ? date('d/m/Y H:i', strtotime($fatura['updated_at'])) : '-' ?></span>
                </div>
            </div>

            <div class="detalhes-section">
                <h3>Informações do Cliente</h3>
                <div class="detalhes-item">
                    <span class="detalhes-label">Nome:</span>
                    <span class="detalhes-value"><?= htmlspecialchars($fatura['cliente_nome'] ?? 'Cliente #' . $fatura['cliente_id']) ?></span>
                </div>
                <div class="detalhes-item">
                    <span class="detalhes-label">E-mail:</span>
                    <span class="detalhes-value"><?= htmlspecialchars($fatura['cliente_email'] ?? '-') ?></span>
                </div>
                <div class="detalhes-item">
                    <span class="detalhes-label">Celular:</span>
                    <span class="detalhes-value"><?= htmlspecialchars($fatura['cliente_celular'] ?? '-') ?></span>
                </div>
                <div class="detalhes-item">
                    <span class="detalhes-label">ID do Cliente:</span>
                    <span class="detalhes-value"><?= htmlspecialchars($fatura['cliente_id']) ?></span>
                </div>
            </div>
        </div>

        <?php if (!empty($fatura['asaas_id'])): ?>
        <div class="detalhes-section mt-4">
            <h3>Integração Asaas</h3>
            <div class="detalhes-item">
                <span class="detalhes-label">ID Asaas:</span>
                <span class="detalhes-value"><?= htmlspecialchars($fatura['asaas_id']) ?></span>
            </div>
            <?php if (!empty($fatura['invoice_url'])): ?>
            <div class="detalhes-item">
                <span class="detalhes-label">URL da Fatura:</span>
                <span class="detalhes-value">
                    <a href="<?= htmlspecialchars($fatura['invoice_url']) ?>" target="_blank" class="text-blue-600 hover:text-blue-800">
                        Abrir no Asaas
                    </a>
                </span>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>

    <div class="acoes-container">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Ações</h3>
        <div class="acoes-grid">
            <?php if (!empty($fatura['invoice_url'])): ?>
            <a href="<?= htmlspecialchars($fatura['invoice_url']) ?>" target="_blank" class="acao-btn primary">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                </svg>
                Pagar Fatura
            </a>
            <?php endif; ?>
            
            <button onclick="sincronizarFatura(<?= $fatura['id'] ?>)" class="acao-btn secondary">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M21 2v6h-6M3 12a9 9 0 0 1 15-6.7L21 8M3 22v-6h6M21 12a9 9 0 0 1-15 6.7L3 16"/>
                </svg>
                Sincronizar
            </button>
            
            <a href="faturas.php?cliente_id=<?= $fatura['cliente_id'] ?>" class="acao-btn secondary">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                    <circle cx="9" cy="7" r="4"/>
                </svg>
                Ver Cliente
            </a>
            
            <button onclick="editarFatura(<?= $fatura['id'] ?>)" class="acao-btn secondary">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                    <path d="m18.5 2.5 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                </svg>
                Editar
            </button>
        </div>
    </div>

    <script>
    function sincronizarFatura(faturaId) {
        if (confirm('Deseja sincronizar esta fatura com o Asaas?')) {
            fetch('api/sincronizar_fatura.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ fatura_id: faturaId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Fatura sincronizada com sucesso!');
                    location.reload();
                } else {
                    alert('Erro ao sincronizar fatura: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                alert('Erro ao sincronizar fatura');
            });
        }
    }

    function editarFatura(faturaId) {
        // Implementar edição da fatura
        alert('Funcionalidade de edição será implementada em breve');
    }
    </script>
    <?php
}

include 'template.php';
?> 