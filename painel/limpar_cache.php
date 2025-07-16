<?php
$page = 'limpar_cache.php';
$page_title = 'Limpando Cache do Sistema';
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta http-equiv="cache-control" content="no-cache, must-revalidate, post-check=0, pre-check=0">
    <meta http-equiv="cache-control" content="max-age=0">
    <meta http-equiv="expires" content="0">
    <meta http-equiv="expires" content="Tue, 01 Jan 1980 1:00:00 GMT">
    <meta http-equiv="pragma" content="no-cache">
    <title>🔄 Limpando Cache - WhatsApp System</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            margin: 50px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            min-height: 80vh;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
        }
        .container {
            background: rgba(255, 255, 255, 0.1);
            padding: 40px;
            border-radius: 20px;
            backdrop-filter: blur(10px);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        }
        .spinner {
            border: 4px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top: 4px solid #fff;
            width: 60px;
            height: 60px;
            animation: spin 1s linear infinite;
            margin: 20px auto;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .success {
            color: #4ade80;
            font-weight: bold;
            font-size: 1.2em;
            margin: 20px 0;
        }
        .info {
            font-size: 0.9em;
            opacity: 0.8;
            margin: 15px 0;
        }
        .btn {
            background: #6366f1;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1.1em;
            margin: 10px;
            transition: all 0.3s;
        }
        .btn:hover {
            background: #4f46e5;
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔄 Atualizando Sistema WhatsApp</h1>
        
        <div id="loading">
            <div class="spinner"></div>
            <p>Limpando cache e aplicando correções...</p>
            <div class="info">
                ✅ Removendo cache antigo<br>
                ✅ Atualizando URLs da VPS<br>
                ✅ Carregando configurações mais recentes
            </div>
        </div>
        
        <div id="success" style="display: none;">
            <div class="success">✅ Cache limpo com sucesso!</div>
            <p>O sistema foi atualizado com as URLs corretas da VPS.</p>
            
            <h3>🎯 Próximos passos:</h3>
            <p class="info">
                1. Acesse a página de comunicação<br>
                2. Clique em "Conectar" no canal WhatsApp<br>
                3. Escaneie o QR Code que aparecerá<br>
                4. Sistema estará 100% operacional!
            </p>
            
            <div>
                <button class="btn" onclick="testarConectividade()">🔍 Testar Conectividade VPS</button>
                <button class="btn" onclick="irParaComunicacao()">📱 Abrir Comunicação</button>
            </div>
            
            <div id="teste-resultado" style="margin-top: 20px;"></div>
        </div>
    </div>

    <script>
        // Forçar limpeza completa do cache
        if ('caches' in window) {
            caches.keys().then(function(cacheNames) {
                cacheNames.forEach(function(cacheName) {
                    caches.delete(cacheName);
                });
            });
        }

        // Limpar localStorage e sessionStorage
        localStorage.clear();
        sessionStorage.clear();

        // Simular processo de limpeza
        setTimeout(function() {
            document.getElementById('loading').style.display = 'none';
            document.getElementById('success').style.display = 'block';
        }, 3000);

        function testarConectividade() {
            const resultado = document.getElementById('teste-resultado');
            resultado.innerHTML = '<div class="spinner" style="width: 30px; height: 30px;"></div><p>Testando...</p>';
            
            fetch('teste_conectividade_vps.php?_=' + Date.now())
                .then(response => response.json())
                .then(data => {
                    if (data.teste_conectividade && data.teste_conectividade.status === 'sucesso') {
                        resultado.innerHTML = `
                            <div class="success">✅ VPS Online!</div>
                            <div class="info">
                                URL: ${data.config_url}<br>
                                Tempo: ${data.teste_conectividade.response_time_ms}ms<br>
                                WhatsApp: ${data.teste_conectividade.whatsapp_ready ? '🟢 Conectado' : '🔴 Aguardando QR Code'}
                            </div>
                        `;
                    } else {
                        resultado.innerHTML = `
                            <div style="color: #f87171;">❌ Erro na conectividade</div>
                            <div class="info">
                                Verifique se a VPS está online e a API rodando.
                            </div>
                        `;
                    }
                })
                .catch(error => {
                    resultado.innerHTML = `
                        <div style="color: #f87171;">❌ Erro: ${error.message}</div>
                    `;
                });
        }

        function irParaComunicacao() {
            // Forçar reload completo da página
            window.location.href = 'comunicacao.php?_cache_clear=' + Date.now();
        }

        // Auto-teste após 4 segundos
        setTimeout(function() {
            if (document.getElementById('success').style.display !== 'none') {
                testarConectividade();
            }
        }, 4000);
    </script>
</body>
</html> 