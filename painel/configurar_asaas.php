<?php
/**
 * Configuração da API Asaas
 * Interface para configurar a chave da API de forma segura
 */

require_once __DIR__ . '/../config.php';
require_once 'db.php';

// Verificar se é uma requisição POST para salvar
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['asaas_api_key'])) {
    $api_key = trim($_POST['asaas_api_key']);
    
    if (!empty($api_key)) {
        // Testar a chave antes de salvar
        $ch = curl_init("https://www.asaas.com/api/v3/customers");
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'access_token: ' . $api_key,
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($http_code === 200) {
            // Chave válida, salvar no banco
            $api_key_escaped = $mysqli->real_escape_string($api_key);
            $sql = "INSERT INTO configuracoes (chave, valor, descricao) VALUES ('asaas_api_key', '$api_key_escaped', 'Chave da API do Asaas') 
                    ON DUPLICATE KEY UPDATE valor = '$api_key_escaped', data_atualizacao = NOW()";
            
            if ($mysqli->query($sql)) {
                $mensagem = "✅ Chave da API Asaas configurada com sucesso!";
                $tipo_mensagem = "success";
            } else {
                $mensagem = "❌ Erro ao salvar chave: " . $mysqli->error;
                $tipo_mensagem = "error";
            }
        } else {
            $mensagem = "❌ Chave da API inválida (HTTP $http_code)";
            $tipo_mensagem = "error";
        }
    } else {
        $mensagem = "❌ Chave da API não pode estar vazia";
        $tipo_mensagem = "error";
    }
}

// Buscar chave atual
$chave_atual = '';
$result = $mysqli->query("SELECT valor FROM configuracoes WHERE chave = 'asaas_api_key' LIMIT 1");
if ($result && $row = $result->fetch_assoc()) {
    $chave_atual = $row['valor'];
}

echo "<h1>⚙️ Configuração da API Asaas</h1>";
echo "<style>
    body{font-family:Arial,sans-serif;margin:20px;background:#f5f5f5;}
    .container{max-width:600px;margin:0 auto;background:white;padding:20px;border-radius:10px;box-shadow:0 2px 10px rgba(0,0,0,0.1);}
    .form-group{margin:15px 0;}
    .form-group label{display:block;margin-bottom:5px;font-weight:bold;}
    .form-group input{width:100%;padding:10px;border:1px solid #ddd;border-radius:5px;font-size:14px;}
    .btn{background:#007bff;color:white;padding:10px 20px;border:none;border-radius:5px;cursor:pointer;font-size:14px;}
    .btn:hover{background:#0056b3;}
    .success{background:#d4edda;color:#155724;padding:15px;border-radius:5px;margin:15px 0;}
    .error{background:#f8d7da;color:#721c24;padding:15px;border-radius:5px;margin:15px 0;}
    .info{background:#d1ecf1;color:#0c5460;padding:15px;border-radius:5px;margin:15px 0;}
    .chave-mascarada{font-family:monospace;background:#f8f9fa;padding:5px;border-radius:3px;}
</style>";

echo "<div class='container'>";

if (isset($mensagem)) {
    $classe = $tipo_mensagem === 'success' ? 'success' : 'error';
    echo "<div class='$classe'>$mensagem</div>";
}

echo "<div class='info'>";
echo "<h3>📋 Informações sobre a API Asaas</h3>";
echo "<p><strong>O que é:</strong> A API Asaas é o gateway de pagamentos usado pelo sistema.</p>";
echo "<p><strong>Onde encontrar:</strong> Acesse sua conta no Asaas > Configurações > API.</p>";
echo "<p><strong>Ambiente:</strong> Use a chave do ambiente <strong>sandbox</strong> para testes.</p>";
echo "</div>";

echo "<form method='POST'>";
echo "<div class='form-group'>";
echo "<label for='asaas_api_key'>🔑 Chave da API Asaas:</label>";

if (!empty($chave_atual)) {
    $chave_mascarada = substr($chave_atual, 0, 8) . '...' . substr($chave_atual, -4);
    echo "<p class='chave-mascarada'>Chave atual: $chave_mascarada</p>";
    echo "<p><small>Digite uma nova chave para substituir a atual</small></p>";
}

echo "<input type='text' id='asaas_api_key' name='asaas_api_key' placeholder='Cole sua chave da API Asaas aqui' required>";
echo "</div>";

echo "<div class='form-group'>";
echo "<button type='submit' class='btn'>💾 Salvar e Testar Chave</button>";
echo "</div>";
echo "</form>";

echo "<div class='info'>";
echo "<h3>🔍 Como Obter a Chave da API</h3>";
echo "<ol>";
echo "<li>Acesse <a href='https://www.asaas.com' target='_blank'>www.asaas.com</a></li>";
echo "<li>Faça login na sua conta</li>";
echo "<li>Vá em <strong>Configurações</strong> > <strong>API</strong></li>";
echo "<li>Copie a <strong>Chave de Acesso</strong></li>";
echo "<li>Cole no campo acima e clique em Salvar</li>";
echo "</ol>";
echo "</div>";

echo "<div class='info'>";
echo "<h3>🧪 Teste da Configuração</h3>";
echo "<p>Após configurar a chave, você pode testar o sistema completo:</p>";
echo "<p><a href='teste_producao_simples.php' class='btn'>🧪 Executar Teste Completo</a></p>";
echo "</div>";

echo "</div>";
?> 