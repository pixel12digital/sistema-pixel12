<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Centro de Controle</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        
        .card-hover {
            transition: all 0.3s ease;
        }
        
        .card-hover:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        }
        
        .gradient-text {
            background: linear-gradient(45deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
    </style>
</head>
<body>
    <div class="min-h-screen py-12 px-4">
        <div class="max-w-7xl mx-auto">
            <!-- Header -->
            <div class="text-center mb-12">
                <h1 class="text-4xl font-bold text-white mb-4">
                    🛠️ Centro de Controle Admin
                </h1>
                <p class="text-xl text-white opacity-90">
                    Ferramentas avançadas para gerenciamento e monitoramento do sistema
                </p>
            </div>

            <!-- Dashboard de Status -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-12">
                <div class="bg-white rounded-lg shadow-lg p-6 text-center">
                    <div class="text-3xl mb-2">🌐</div>
                    <h3 class="font-semibold text-gray-800">Sistema</h3>
                    <p class="text-green-600 font-bold" id="status-sistema">Online</p>
                </div>
                
                <div class="bg-white rounded-lg shadow-lg p-6 text-center">
                    <div class="text-3xl mb-2">📱</div>
                    <h3 class="font-semibold text-gray-800">WhatsApp</h3>
                    <p class="text-blue-600 font-bold" id="status-whatsapp">Verificando...</p>
                </div>
                
                <div class="bg-white rounded-lg shadow-lg p-6 text-center">
                    <div class="text-3xl mb-2">🗄️</div>
                    <h3 class="font-semibold text-gray-800">Banco</h3>
                    <p class="text-green-600 font-bold" id="status-banco">Conectado</p>
                </div>
                
                <div class="bg-white rounded-lg shadow-lg p-6 text-center">
                    <div class="text-3xl mb-2">🔗</div>
                    <h3 class="font-semibold text-gray-800">Webhook</h3>
                    <p class="text-yellow-600 font-bold" id="status-webhook">Verificando...</p>
                </div>
            </div>

            <!-- Ferramentas Principais -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                
                <!-- Centro de Testes de Webhook -->
                <div class="bg-white rounded-lg shadow-lg p-6 card-hover">
                    <div class="flex items-center mb-4">
                        <div class="w-12 h-12 bg-gradient-to-r from-purple-500 to-pink-500 rounded-lg flex items-center justify-center text-white text-2xl mr-4">
                            🧪
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-gray-800">Centro de Testes</h3>
                            <p class="text-gray-600">Webhook & Integrações</p>
                        </div>
                    </div>
                    
                    <p class="text-gray-700 mb-4">
                        Interface completa para testar webhooks, conectividade e integrações do sistema.
                    </p>
                    
                    <div class="space-y-2 mb-4">
                        <div class="flex items-center text-sm text-gray-600">
                            <span class="w-2 h-2 bg-green-500 rounded-full mr-2"></span>
                            Testes automatizados
                        </div>
                        <div class="flex items-center text-sm text-gray-600">
                            <span class="w-2 h-2 bg-blue-500 rounded-full mr-2"></span>
                            Logs em tempo real
                        </div>
                        <div class="flex items-center text-sm text-gray-600">
                            <span class="w-2 h-2 bg-purple-500 rounded-full mr-2"></span>
                            Futuras integrações
                        </div>
                    </div>
                    
                    <a href="webhook-test.php" class="w-full bg-gradient-to-r from-purple-500 to-pink-500 text-white px-4 py-2 rounded-lg hover:from-purple-600 hover:to-pink-600 transition-all duration-300 inline-block text-center">
                        🚀 Abrir Centro de Testes
                    </a>
                </div>

                <!-- Monitoramento de Sistema -->
                <div class="bg-white rounded-lg shadow-lg p-6 card-hover">
                    <div class="flex items-center mb-4">
                        <div class="w-12 h-12 bg-gradient-to-r from-blue-500 to-cyan-500 rounded-lg flex items-center justify-center text-white text-2xl mr-4">
                            📊
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-gray-800">Monitoramento</h3>
                            <p class="text-gray-600">Sistema & Performance</p>
                        </div>
                    </div>
                    
                    <p class="text-gray-700 mb-4">
                        Monitore logs, performance e status dos serviços em tempo real.
                    </p>
                    
                    <div class="space-y-2 mb-4">
                        <div class="flex items-center text-sm text-gray-600">
                            <span class="w-2 h-2 bg-green-500 rounded-full mr-2"></span>
                            Logs do sistema
                        </div>
                        <div class="flex items-center text-sm text-gray-600">
                            <span class="w-2 h-2 bg-blue-500 rounded-full mr-2"></span>
                            Métricas de performance
                        </div>
                        <div class="flex items-center text-sm text-yellow-600">
                            <span class="w-2 h-2 bg-yellow-500 rounded-full mr-2"></span>
                            Em desenvolvimento
                        </div>
                    </div>
                    
                    <button disabled class="w-full bg-gray-300 text-gray-500 px-4 py-2 rounded-lg cursor-not-allowed">
                        🔧 Em Breve
                    </button>
                </div>

                <!-- Configurações Avançadas -->
                <div class="bg-white rounded-lg shadow-lg p-6 card-hover">
                    <div class="flex items-center mb-4">
                        <div class="w-12 h-12 bg-gradient-to-r from-green-500 to-emerald-500 rounded-lg flex items-center justify-center text-white text-2xl mr-4">
                            ⚙️
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-gray-800">Configurações</h3>
                            <p class="text-gray-600">Sistema & Integrações</p>
                        </div>
                    </div>
                    
                    <p class="text-gray-700 mb-4">
                        Configure APIs, webhooks e parâmetros avançados do sistema.
                    </p>
                    
                    <div class="space-y-2 mb-4">
                        <div class="flex items-center text-sm text-gray-600">
                            <span class="w-2 h-2 bg-green-500 rounded-full mr-2"></span>
                            APIs externas
                        </div>
                        <div class="flex items-center text-sm text-gray-600">
                            <span class="w-2 h-2 bg-blue-500 rounded-full mr-2"></span>
                            Webhooks
                        </div>
                        <div class="flex items-center text-sm text-yellow-600">
                            <span class="w-2 h-2 bg-yellow-500 rounded-full mr-2"></span>
                            Em desenvolvimento
                        </div>
                    </div>
                    
                    <button disabled class="w-full bg-gray-300 text-gray-500 px-4 py-2 rounded-lg cursor-not-allowed">
                        🔧 Em Breve
                    </button>
                </div>

                <!-- Backup & Restauração -->
                <div class="bg-white rounded-lg shadow-lg p-6 card-hover">
                    <div class="flex items-center mb-4">
                        <div class="w-12 h-12 bg-gradient-to-r from-red-500 to-pink-500 rounded-lg flex items-center justify-center text-white text-2xl mr-4">
                            💾
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-gray-800">Backup</h3>
                            <p class="text-gray-600">Dados & Configurações</p>
                        </div>
                    </div>
                    
                    <p class="text-gray-700 mb-4">
                        Gerencie backups automáticos e restauração de dados do sistema.
                    </p>
                    
                    <div class="space-y-2 mb-4">
                        <div class="flex items-center text-sm text-gray-600">
                            <span class="w-2 h-2 bg-green-500 rounded-full mr-2"></span>
                            Backup automático
                        </div>
                        <div class="flex items-center text-sm text-gray-600">
                            <span class="w-2 h-2 bg-blue-500 rounded-full mr-2"></span>
                            Restauração rápida
                        </div>
                        <div class="flex items-center text-sm text-yellow-600">
                            <span class="w-2 h-2 bg-yellow-500 rounded-full mr-2"></span>
                            Em desenvolvimento
                        </div>
                    </div>
                    
                    <button disabled class="w-full bg-gray-300 text-gray-500 px-4 py-2 rounded-lg cursor-not-allowed">
                        🔧 Em Breve
                    </button>
                </div>

                <!-- Segurança -->
                <div class="bg-white rounded-lg shadow-lg p-6 card-hover">
                    <div class="flex items-center mb-4">
                        <div class="w-12 h-12 bg-gradient-to-r from-yellow-500 to-orange-500 rounded-lg flex items-center justify-center text-white text-2xl mr-4">
                            🔒
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-gray-800">Segurança</h3>
                            <p class="text-gray-600">Logs & Acessos</p>
                        </div>
                    </div>
                    
                    <p class="text-gray-700 mb-4">
                        Monitore acessos, logs de segurança e tentativas de invasão.
                    </p>
                    
                    <div class="space-y-2 mb-4">
                        <div class="flex items-center text-sm text-gray-600">
                            <span class="w-2 h-2 bg-green-500 rounded-full mr-2"></span>
                            Logs de acesso
                        </div>
                        <div class="flex items-center text-sm text-gray-600">
                            <span class="w-2 h-2 bg-blue-500 rounded-full mr-2"></span>
                            Firewall logs
                        </div>
                        <div class="flex items-center text-sm text-yellow-600">
                            <span class="w-2 h-2 bg-yellow-500 rounded-full mr-2"></span>
                            Em desenvolvimento
                        </div>
                    </div>
                    
                    <button disabled class="w-full bg-gray-300 text-gray-500 px-4 py-2 rounded-lg cursor-not-allowed">
                        🔧 Em Breve
                    </button>
                </div>

                <!-- Relatórios -->
                <div class="bg-white rounded-lg shadow-lg p-6 card-hover">
                    <div class="flex items-center mb-4">
                        <div class="w-12 h-12 bg-gradient-to-r from-indigo-500 to-purple-500 rounded-lg flex items-center justify-center text-white text-2xl mr-4">
                            📈
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-gray-800">Relatórios</h3>
                            <p class="text-gray-600">Analytics & Insights</p>
                        </div>
                    </div>
                    
                    <p class="text-gray-700 mb-4">
                        Gere relatórios detalhados de uso, performance e analytics.
                    </p>
                    
                    <div class="space-y-2 mb-4">
                        <div class="flex items-center text-sm text-gray-600">
                            <span class="w-2 h-2 bg-green-500 rounded-full mr-2"></span>
                            Relatórios de uso
                        </div>
                        <div class="flex items-center text-sm text-gray-600">
                            <span class="w-2 h-2 bg-blue-500 rounded-full mr-2"></span>
                            Analytics avançadas
                        </div>
                        <div class="flex items-center text-sm text-yellow-600">
                            <span class="w-2 h-2 bg-yellow-500 rounded-full mr-2"></span>
                            Em desenvolvimento
                        </div>
                    </div>
                    
                    <button disabled class="w-full bg-gray-300 text-gray-500 px-4 py-2 rounded-lg cursor-not-allowed">
                        🔧 Em Breve
                    </button>
                </div>

            </div>

            <!-- Links Rápidos -->
            <div class="mt-12 bg-white rounded-lg shadow-lg p-6">
                <h2 class="text-2xl font-bold text-gray-800 mb-6">🔗 Links Rápidos</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <a href="../painel/" class="flex items-center p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                        <span class="text-2xl mr-3">🏠</span>
                        <div>
                            <div class="font-semibold text-gray-800">Painel Principal</div>
                            <div class="text-sm text-gray-600">Dashboard</div>
                        </div>
                    </a>
                    
                    <a href="../painel/chat.php" class="flex items-center p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                        <span class="text-2xl mr-3">💬</span>
                        <div>
                            <div class="font-semibold text-gray-800">Chat</div>
                            <div class="text-sm text-gray-600">Mensagens</div>
                        </div>
                    </a>
                    
                    <a href="../painel/faturas.php" class="flex items-center p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                        <span class="text-2xl mr-3">💰</span>
                        <div>
                            <div class="font-semibold text-gray-800">Faturas</div>
                            <div class="text-sm text-gray-600">Financeiro</div>
                        </div>
                    </a>
                    
                    <a href="../painel/comunicacao.php" class="flex items-center p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                        <span class="text-2xl mr-3">📱</span>
                        <div>
                            <div class="font-semibold text-gray-800">WhatsApp</div>
                            <div class="text-sm text-gray-600">Comunicação</div>
                        </div>
                    </a>
                </div>
            </div>

            <!-- Footer -->
            <div class="text-center mt-12">
                <p class="text-white opacity-75">
                    Centro de Controle Admin - Sistema de Gestão Integrada
                </p>
                <p class="text-white opacity-50 text-sm mt-2">
                    Versão 1.0.0 - <?php echo date('Y'); ?>
                </p>
            </div>
        </div>
    </div>

    <script>
        // Verificar status dos sistemas
        document.addEventListener('DOMContentLoaded', function() {
            verificarStatusSistemas();
            
            // Atualizar status a cada 30 segundos
            setInterval(verificarStatusSistemas, 30000);
        });

        async function verificarStatusSistemas() {
            // Verificar WhatsApp
            try {
                const response = await fetch('test-database.php');
                const data = await response.json();
                
                if (data.success) {
                    document.getElementById('status-banco').textContent = 'Conectado';
                    document.getElementById('status-banco').className = 'text-green-600 font-bold';
                } else {
                    document.getElementById('status-banco').textContent = 'Erro';
                    document.getElementById('status-banco').className = 'text-red-600 font-bold';
                }
            } catch (error) {
                document.getElementById('status-banco').textContent = 'Offline';
                document.getElementById('status-banco').className = 'text-red-600 font-bold';
            }

            // Verificar Webhook
            try {
                const response = await fetch('http://212.85.11.238:3000/status');
                if (response.ok) {
                    document.getElementById('status-webhook').textContent = 'Online';
                    document.getElementById('status-webhook').className = 'text-green-600 font-bold';
                    
                    // Se VPS está online, verificar WhatsApp
                    const data = await response.json();
                    if (data.ready) {
                        document.getElementById('status-whatsapp').textContent = 'Conectado';
                        document.getElementById('status-whatsapp').className = 'text-green-600 font-bold';
                    } else {
                        document.getElementById('status-whatsapp').textContent = 'Desconectado';
                        document.getElementById('status-whatsapp').className = 'text-yellow-600 font-bold';
                    }
                } else {
                    document.getElementById('status-webhook').textContent = 'Offline';
                    document.getElementById('status-webhook').className = 'text-red-600 font-bold';
                    document.getElementById('status-whatsapp').textContent = 'N/A';
                    document.getElementById('status-whatsapp').className = 'text-gray-600 font-bold';
                }
            } catch (error) {
                document.getElementById('status-webhook').textContent = 'Offline';
                document.getElementById('status-webhook').className = 'text-red-600 font-bold';
                document.getElementById('status-whatsapp').textContent = 'N/A';
                document.getElementById('status-whatsapp').className = 'text-gray-600 font-bold';
            }
        }
    </script>
</body>
</html> 