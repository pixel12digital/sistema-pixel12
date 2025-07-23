<?php
$page = 'configuracao_ia.php';
$page_title = '🤖 Configuração da IA';
$custom_header = '<a href="faturas.php" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-md flex items-center gap-2"><span>← Voltar</span></a>';

require_once 'config.php';

// Verificar se é administrador ou tem permissão
session_start();

$config_file = __DIR__ . '/config_ia.json';
$config_atual = [];

if (file_exists($config_file)) {
    $config_atual = json_decode(file_get_contents($config_file), true) ?: [];
}

// Processar formulário
if ($_POST) {
    $nova_config = [
        'ativa' => isset($_POST['ativa']) ? true : false,
        'url_api' => trim($_POST['url_api'] ?? ''),
        'api_key' => trim($_POST['api_key'] ?? ''),
        'modelo' => trim($_POST['modelo'] ?? 'assistente_financeiro'),
        'configuracao' => [
            'nome' => trim($_POST['nome'] ?? 'Assistente Financeiro Pixel12'),
            'personalidade' => $_POST['personalidade'] ?? 'profissional_amigavel',
            'timeout' => intval($_POST['timeout'] ?? 10),
            'fallback_ativo' => isset($_POST['fallback_ativo']) ? true : false,
            'log_conversas' => isset($_POST['log_conversas']) ? true : false,
            'versao' => '1.0'
        ],
        'ultima_atualizacao' => date('Y-m-d H:i:s'),
        'status' => isset($_POST['ativa']) ? 'ativada' : 'desativada'
    ];
    
    if (file_put_contents($config_file, json_encode($nova_config, JSON_PRETTY_PRINT))) {
        $mensagem = "Configuração da IA salva com sucesso!";
        $tipo_mensagem = "success";
        $config_atual = $nova_config;
    } else {
        $mensagem = "Erro ao salvar configuração.";
        $tipo_mensagem = "error";
    }
}

// Testar conexão com IA
if (isset($_POST['testar_conexao'])) {
    $url_teste = trim($_POST['url_api'] ?? '');
    $api_key_teste = trim($_POST['api_key'] ?? '');
    
    if ($url_teste && $api_key_teste) {
        $payload_teste = [
            'mensagem' => 'teste de conexão',
            'tipo' => 'teste'
        ];
        
        $ch = curl_init($url_teste);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload_teste));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $api_key_teste
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($response && $http_code === 200) {
            $mensagem = "✅ Conexão com IA funcionando!";
            $tipo_mensagem = "success";
        } else {
            $mensagem = "❌ Falha na conexão. Código HTTP: $http_code";
            $tipo_mensagem = "error";
        }
    } else {
        $mensagem = "Preencha URL e API Key para testar.";
        $tipo_mensagem = "warning";
    }
}

function render_content() {
    global $mensagem, $tipo_mensagem, $config_atual;
?>

<!-- Mensagem de feedback -->
<?php if (isset($mensagem)): ?>
<div class="mb-6 p-4 rounded-lg <?= $tipo_mensagem === 'success' ? 'bg-green-100 text-green-800 border border-green-300' : ($tipo_mensagem === 'error' ? 'bg-red-100 text-red-800 border border-red-300' : 'bg-yellow-100 text-yellow-800 border border-yellow-300') ?>">
    <?= htmlspecialchars($mensagem) ?>
</div>
<?php endif; ?>

<!-- Status Atual -->
<div class="painel-card">
    <h2 class="text-xl font-semibold mb-4">📊 Status Atual</h2>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-gray-50 p-4 rounded-lg">
            <div class="text-sm text-gray-600">Status da IA</div>
            <div class="text-lg font-semibold <?= ($config_atual['ativa'] ?? false) ? 'text-green-600' : 'text-red-600' ?>">
                <?= ($config_atual['ativa'] ?? false) ? '✅ Ativada' : '❌ Desativada' ?>
            </div>
        </div>
        <div class="bg-gray-50 p-4 rounded-lg">
            <div class="text-sm text-gray-600">Modelo</div>
            <div class="text-lg font-semibold"><?= htmlspecialchars($config_atual['modelo'] ?? 'Não configurado') ?></div>
        </div>
        <div class="bg-gray-50 p-4 rounded-lg">
            <div class="text-sm text-gray-600">Última Atualização</div>
            <div class="text-lg font-semibold"><?= $config_atual['ultima_atualizacao'] ?? 'Nunca' ?></div>
        </div>
    </div>
</div>

<!-- Formulário de Configuração -->
<form method="POST" class="space-y-6">
    <!-- Configuração Básica -->
    <div class="painel-card">
        <h2 class="text-xl font-semibold mb-4">⚙️ Configuração Básica</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="flex items-center space-x-3 mb-4">
                    <input type="checkbox" name="ativa" <?= ($config_atual['ativa'] ?? false) ? 'checked' : '' ?> 
                           class="w-5 h-5 text-purple-600 rounded">
                    <span class="text-lg font-semibold">Ativar IA</span>
                </label>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">URL da API</label>
                    <input type="url" name="url_api" 
                           value="<?= htmlspecialchars($config_atual['url_api'] ?? '') ?>"
                           placeholder="https://sua-ia.com/api/chat"
                           class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-purple-500 focus:border-purple-500">
                    <small class="text-gray-500">URL do endpoint da sua IA</small>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">API Key</label>
                    <input type="password" name="api_key" 
                           value="<?= htmlspecialchars($config_atual['api_key'] ?? '') ?>"
                           placeholder="sua_api_key_aqui"
                           class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-purple-500 focus:border-purple-500">
                    <small class="text-gray-500">Chave da API gerada no seu painel</small>
                </div>
            </div>

            <div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Nome do Assistente</label>
                    <input type="text" name="nome" 
                           value="<?= htmlspecialchars($config_atual['configuracao']['nome'] ?? 'Assistente Financeiro Pixel12') ?>"
                           class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-purple-500 focus:border-purple-500">
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Modelo</label>
                    <select name="modelo" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-purple-500 focus:border-purple-500">
                        <option value="assistente_financeiro" <?= ($config_atual['modelo'] ?? '') === 'assistente_financeiro' ? 'selected' : '' ?>>Assistente Financeiro</option>
                        <option value="atendimento_geral" <?= ($config_atual['modelo'] ?? '') === 'atendimento_geral' ? 'selected' : '' ?>>Atendimento Geral</option>
                        <option value="personalizado" <?= ($config_atual['modelo'] ?? '') === 'personalizado' ? 'selected' : '' ?>>Personalizado</option>
                    </select>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Personalidade</label>
                    <select name="personalidade" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-purple-500 focus:border-purple-500">
                        <option value="profissional_amigavel" <?= ($config_atual['configuracao']['personalidade'] ?? '') === 'profissional_amigavel' ? 'selected' : '' ?>>Profissional e Amigável</option>
                        <option value="formal" <?= ($config_atual['configuracao']['personalidade'] ?? '') === 'formal' ? 'selected' : '' ?>>Formal</option>
                        <option value="casual" <?= ($config_atual['configuracao']['personalidade'] ?? '') === 'casual' ? 'selected' : '' ?>>Casual</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Configurações Avançadas -->
    <div class="painel-card">
        <h2 class="text-xl font-semibold mb-4">🔧 Configurações Avançadas</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Timeout (segundos)</label>
                    <input type="number" name="timeout" min="5" max="30"
                           value="<?= $config_atual['configuracao']['timeout'] ?? 10 ?>"
                           class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-purple-500 focus:border-purple-500">
                    <small class="text-gray-500">Tempo limite para resposta da IA</small>
                </div>
            </div>

            <div>
                <div class="space-y-3">
                    <label class="flex items-center space-x-3">
                        <input type="checkbox" name="fallback_ativo" <?= ($config_atual['configuracao']['fallback_ativo'] ?? true) ? 'checked' : '' ?> 
                               class="w-4 h-4 text-purple-600 rounded">
                        <span>Fallback para robô tradicional se IA falhar</span>
                    </label>
                    
                    <label class="flex items-center space-x-3">
                        <input type="checkbox" name="log_conversas" <?= ($config_atual['configuracao']['log_conversas'] ?? true) ? 'checked' : '' ?> 
                               class="w-4 h-4 text-purple-600 rounded">
                        <span>Registrar conversas nos logs</span>
                    </label>
                </div>
            </div>
        </div>
    </div>

    <!-- Botões de Ação -->
    <div class="flex gap-4">
        <button type="submit" 
                class="bg-purple-600 hover:bg-purple-700 text-white px-6 py-3 rounded-lg font-semibold transition-colors">
            💾 Salvar Configuração
        </button>
        
        <button type="submit" name="testar_conexao"
                class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-semibold transition-colors">
            🧪 Testar Conexão
        </button>
    </div>
</form>

<!-- Instruções -->
<div class="painel-card mt-8">
    <h2 class="text-xl font-semibold mb-4">📚 Como Configurar</h2>
    <div class="prose prose-sm max-w-none">
        <ol class="list-decimal list-inside space-y-2 text-gray-700">
            <li><strong>Acesse seu painel de IA</strong> e crie um novo agente do tipo "Assistente Financeiro"</li>
            <li><strong>Faça o treinamento</strong> usando a documentação fornecida</li>
            <li><strong>Gere uma API Key</strong> no painel da IA</li>
            <li><strong>Copie a URL da API</strong> e a chave gerada</li>
            <li><strong>Cole aqui</strong> e teste a conexão</li>
            <li><strong>Ative a IA</strong> e salve as configurações</li>
        </ol>
        
        <div class="mt-4 p-4 bg-blue-50 rounded-lg border border-blue-200">
            <p class="text-blue-800"><strong>💡 Dica:</strong> Mantenha o fallback ativo para garantir que o sistema continue funcionando mesmo se a IA estiver indisponível.</p>
        </div>
    </div>
</div>

<?php
}

include 'template.php';
?> 