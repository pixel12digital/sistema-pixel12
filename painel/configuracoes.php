<?php
// Headers para evitar cache
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

session_start();
if (!isset($_SESSION['logado']) || !$_SESSION['logado']) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title>Painel - Configurações</title>
    <link rel="stylesheet" href="assets/style.css?v=<?php echo time(); ?>">
    <style>
        .acoes-rapidas {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        
        .acao-card {
            background: white;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }
        
        .acao-card:hover {
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
            transform: translateY(-2px);
        }
        
        .acao-titulo {
            font-size: 18px;
            font-weight: bold;
            color: #333;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .acao-descricao {
            color: #666;
            margin-bottom: 15px;
            font-size: 14px;
        }
        
        .acao-botao {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
            transition: all 0.3s ease;
            width: 100%;
        }
        
        .acao-botao:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }
        
        .acao-botao:disabled {
            background: #ccc;
            cursor: not-allowed;
            transform: none;
        }
        
        .acao-resultado {
            margin-top: 15px;
            padding: 10px;
            border-radius: 4px;
            font-size: 14px;
            display: none;
        }
        
        .resultado-sucesso {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .resultado-erro {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .resultado-info {
            background: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }
        
        .loading {
            display: inline-block;
            width: 16px;
            height: 16px;
            border: 2px solid #f3f3f3;
            border-top: 2px solid #667eea;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-right: 8px;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .status-indicador {
            display: inline-block;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            margin-right: 8px;
        }
        
        .status-online {
            background: #28a745;
        }
        
        .status-offline {
            background: #dc3545;
        }
        
        .status-warning {
            background: #ffc107;
        }
        
        .monitor-container {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 15px;
            margin-top: 15px;
            display: none;
        }
        
        .monitor-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            padding: 5px 0;
            border-bottom: 1px solid #eee;
        }
        
        .monitor-item:last-child {
            border-bottom: none;
        }
        
        .monitor-label {
            font-weight: bold;
            color: #495057;
        }
        
        .monitor-valor {
            color: #6c757d;
        }
    </style>
</head>
<body>
<?php include 'menu_lateral.php'; ?>
<div class="main-content">
    <div class="topbar">
        <span class="topbar-title">Painel Administrativo - Pixel 12 Digital <span style='color:#a259e6;font-weight:bold;'>| Configurações</span></span>
    </div>
    <div class="config-container">
        <div class="config-header">
            <h2>🔧 Ações Rápidas do Sistema</h2>
            <p>Gerencie e monitore o sistema WhatsApp de forma rápida e eficiente</p>
        </div>
        
        <div class="acoes-rapidas">
            <!-- Testar Webhook -->
            <div class="acao-card">
                <div class="acao-titulo">
                    <span class="status-indicador status-online"></span>
                    🧪 Testar Webhook
                </div>
                <div class="acao-descricao">
                    Envia uma mensagem de teste para verificar se o webhook está funcionando corretamente
                </div>
                <button class="acao-botao" onclick="executarAcao('testar_webhook')">
                    Testar Agora
                </button>
                <div class="acao-resultado" id="resultado-testar_webhook"></div>
            </div>
            
            <!-- Verificar Status -->
            <div class="acao-card">
                <div class="acao-titulo">
                    <span class="status-indicador status-online"></span>
                    📊 Verificar Status
                </div>
                <div class="acao-descricao">
                    Verifica o status geral do sistema, conexões e mensagens
                </div>
                <button class="acao-botao" onclick="executarAcao('verificar_status')">
                    Verificar
                </button>
                <div class="acao-resultado" id="resultado-verificar_status"></div>
            </div>
            
            <!-- Limpar Logs -->
            <div class="acao-card">
                <div class="acao-titulo">
                    <span class="status-indicador status-warning"></span>
                    🧹 Limpar Logs
                </div>
                <div class="acao-descricao">
                    Remove logs antigos para liberar espaço e melhorar performance
                </div>
                <button class="acao-botao" onclick="executarAcao('limpar_logs')">
                    Limpar
                </button>
                <div class="acao-resultado" id="resultado-limpar_logs"></div>
            </div>
            
            <!-- Monitor em Tempo Real -->
            <div class="acao-card">
                <div class="acao-titulo">
                    <span class="status-indicator status-online"></span>
                    📡 Monitor Tempo Real
                </div>
                <div class="acao-descricao">
                    Ativa monitoramento em tempo real do sistema
                </div>
                <button class="acao-botao" onclick="toggleMonitor()" id="botao-monitor">
                    Iniciar Monitor
                </button>
                <div class="monitor-container" id="monitor-container">
                    <div class="monitor-item">
                        <span class="monitor-label">Mensagens Hoje:</span>
                        <span class="monitor-valor" id="monitor-msgs-hoje">-</span>
                    </div>
                    <div class="monitor-item">
                        <span class="monitor-label">Última Mensagem:</span>
                        <span class="monitor-valor" id="monitor-ultima-msg">-</span>
                    </div>
                    <div class="monitor-item">
                        <span class="monitor-label">Status Webhook:</span>
                        <span class="monitor-valor" id="monitor-status">-</span>
                    </div>
                    <div class="monitor-item">
                        <span class="monitor-label">Tamanho Log:</span>
                        <span class="monitor-valor" id="monitor-log-size">-</span>
                    </div>
                </div>
            </div>
            
            <!-- Otimizar Sistema -->
            <div class="acao-card">
                <div class="acao-titulo">
                    <span class="status-indicador status-warning"></span>
                    ⚡ Otimizar Sistema
                </div>
                <div class="acao-descricao">
                    Executa otimizações automáticas para melhorar performance
                </div>
                <button class="acao-botao" onclick="executarAcao('otimizar_sistema')">
                    Otimizar
                </button>
                <div class="acao-resultado" id="resultado-otimizar_sistema"></div>
            </div>
            
            <!-- Backup Rápido -->
            <div class="acao-card">
                <div class="acao-titulo">
                    <span class="status-indicador status-online"></span>
                    💾 Backup Rápido
                </div>
                <div class="acao-descricao">
                    Cria backup rápido das configurações e dados importantes
                </div>
                <button class="acao-botao" onclick="executarAcao('backup_rapido')">
                    Fazer Backup
                </button>
                <div class="acao-resultado" id="resultado-backup_rapido"></div>
            </div>
        </div>
    </div>
</div>

<script>
let monitorAtivo = false;
let monitorInterval = null;

// Configurações de polling OTIMIZADAS para economizar conexões
const POLLING_INTERVAL = 300000; // 5 minutos (era 5 segundos)
const CACHE_TTL = 1800; // 30 minutos de cache

function executarAcao(acao) {
    const botao = event.target;
    const resultadoDiv = document.getElementById(`resultado-${acao}`);
    
    // Desabilita botão e mostra loading
    botao.disabled = true;
    botao.innerHTML = '<span class="loading"></span>Executando...';
    resultadoDiv.style.display = 'none';
    
    // Faz requisição AJAX
    fetch('acoes_rapidas.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `acao=${acao}`
    })
    .then(response => response.json())
    .then(data => {
        resultadoDiv.innerHTML = data.mensagem;
        resultadoDiv.className = `acao-resultado resultado-${data.tipo}`;
        resultadoDiv.style.display = 'block';
    })
    .catch(error => {
        resultadoDiv.innerHTML = 'Erro ao executar ação: ' + error.message;
        resultadoDiv.className = 'acao-resultado resultado-erro';
        resultadoDiv.style.display = 'block';
    })
    .finally(() => {
        // Reabilita botão
        botao.disabled = false;
        botao.innerHTML = getBotaoTexto(acao);
    });
}

function getBotaoTexto(acao) {
    const textos = {
        'testar_webhook': 'Testar Agora',
        'verificar_status': 'Verificar',
        'limpar_logs': 'Limpar',
        'otimizar_sistema': 'Otimizar',
        'backup_rapido': 'Fazer Backup'
    };
    return textos[acao] || 'Executar';
}

function toggleMonitor() {
    const botao = document.getElementById('botao-monitor');
    const container = document.getElementById('monitor-container');
    
    if (!monitorAtivo) {
        // Inicia monitor
        monitorAtivo = true;
        botao.innerHTML = 'Parar Monitor';
        container.style.display = 'block';
        
        // Primeira atualização
        atualizarMonitor();
        
        // Atualiza a cada 5 segundos
        monitorInterval = setInterval(atualizarMonitor, POLLING_INTERVAL); // Aumentado para 60s
    } else {
        // Para monitor
        monitorAtivo = false;
        botao.innerHTML = 'Iniciar Monitor';
        container.style.display = 'none';
        
        if (monitorInterval) {
            clearInterval(monitorInterval);
            monitorInterval = null;
        }
    }
}

function atualizarMonitor() {
    fetch('acoes_rapidas.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'acao=monitor_tempo_real'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('monitor-msgs-hoje').textContent = data.dados.mensagens_hoje;
            document.getElementById('monitor-ultima-msg').textContent = data.dados.ultima_mensagem;
            document.getElementById('monitor-status').textContent = data.dados.status_webhook;
            document.getElementById('monitor-log-size').textContent = data.dados.tamanho_log;
        }
    })
    .catch(error => {
        console.error('Erro ao atualizar monitor:', error);
    });
}
</script>
</body>
</html> 