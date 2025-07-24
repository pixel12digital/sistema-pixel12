<?php
/**
 * 🔧 Configurar API Asaas no Banco
 * Insere/atualiza a chave da API Asaas no banco de dados
 */

echo "<h1>🔧 Configurar API Asaas no Banco</h1>";
echo "<style>
    body{font-family:Arial,sans-serif;margin:20px;background:#f5f5f5;}
    .container{max-width:800px;margin:0 auto;background:white;padding:20px;border-radius:10px;box-shadow:0 2px 10px rgba(0,0,0,0.1);}
    .success{color:#28a745;background:#d4edda;padding:10px;border-radius:5px;margin:10px 0;}
    .error{color:#dc3545;background:#f8d7da;padding:10px;border-radius:5px;margin:10px 0;}
    .info{color:#17a2b8;background:#d1ecf1;padding:10px;border-radius:5px;margin:10px 0;}
    .warning{color:#856404;background:#fff3cd;padding:10px;border-radius:5px;margin:10px 0;}
    .code{background:#f8f9fa;padding:10px;border-radius:5px;font-family:monospace;margin:10px 0;}
    .form-group{margin:15px 0;}
    .form-group label{display:block;margin-bottom:5px;font-weight:bold;}
    .form-group input{width:100%;padding:10px;border:1px solid #ddd;border-radius:5px;font-size:14px;}
    .btn{background:#007bff;color:white;padding:10px 20px;border:none;border-radius:5px;cursor:pointer;font-size:16px;}
    .btn:hover{background:#0056b3;}
</style>";

echo "<div class='container'>";

require_once __DIR__ . '/config.php';
require_once 'db.php';

// Chave da API Asaas do config.php
$asaas_key_config = defined('ASAAS_API_KEY') ? ASAAS_API_KEY : '';

echo "<div class='info'>";
echo "<strong>🔍 Status Atual:</strong><br>";
echo "• Chave no config.php: " . (strlen($asaas_key_config) > 20 ? 'CONFIGURADA' : 'NÃO CONFIGURADA') . "<br>";
echo "• Chave no banco: Verificando...";
echo "</div>";

// Verificar se já existe no banco
$result = $mysqli->query("SELECT valor FROM configuracoes WHERE chave = 'asaas_api_key' LIMIT 1");
$config_existe = $result && $result->num_rows > 0;
$config_atual = $config_existe ? $result->fetch_assoc()['valor'] : '';

echo "<div class='info'>";
echo "<strong>📋 Configuração no Banco:</strong><br>";
if ($config_existe) {
    echo "• Status: CONFIGURADA<br>";
    echo "• Valor: " . (strlen($config_atual) > 20 ? substr($config_atual, 0, 20) . '...' : 'INCOMPLETA');
} else {
    echo "• Status: NÃO CONFIGURADA";
}
echo "</div>";

// Processar formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nova_chave = trim($_POST['asaas_key'] ?? '');
    
    if (empty($nova_chave)) {
        echo "<div class='error'>❌ Chave da API não pode estar vazia</div>";
    } elseif (strlen($nova_chave) < 20) {
        echo "<div class='error'>❌ Chave da API parece estar incompleta</div>";
    } else {
        try {
            if ($config_existe) {
                // Atualizar configuração existente
                $chave_escaped = $mysqli->real_escape_string($nova_chave);
                $sql = "UPDATE configuracoes SET valor = '$chave_escaped', data_atualizacao = NOW() WHERE chave = 'asaas_api_key'";
                $result = $mysqli->query($sql);
                
                if ($result) {
                    echo "<div class='success'>✅ Chave da API Asaas atualizada com sucesso!</div>";
                } else {
                    throw new Exception("Erro ao atualizar: " . $mysqli->error);
                }
            } else {
                // Inserir nova configuração
                $chave_escaped = $mysqli->real_escape_string($nova_chave);
                $sql = "INSERT INTO configuracoes (chave, valor, descricao, data_criacao, data_atualizacao) 
                        VALUES ('asaas_api_key', '$chave_escaped', 'Chave da API Asaas para integração financeira', NOW(), NOW())";
                $result = $mysqli->query($sql);
                
                if ($result) {
                    echo "<div class='success'>✅ Chave da API Asaas inserida com sucesso!</div>";
                } else {
                    throw new Exception("Erro ao inserir: " . $mysqli->error);
                }
            }
            
            // Testar a nova configuração
            echo "<div class='info'>🧪 Testando nova configuração...</div>";
            
            $ch = curl_init("https://www.asaas.com/api/v3/customers");
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'access_token: ' . $nova_chave,
                'Content-Type: application/json'
            ]);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($http_code === 200) {
                echo "<div class='success'>✅ API Asaas testada com sucesso! (HTTP $http_code)</div>";
            } elseif ($http_code === 401) {
                echo "<div class='warning'>⚠️ Chave da API pode estar inválida (HTTP $http_code)</div>";
            } else {
                echo "<div class='warning'>⚠️ API retornou código $http_code</div>";
            }
            
        } catch (Exception $e) {
            echo "<div class='error'>❌ Erro: " . $e->getMessage() . "</div>";
        }
    }
}

// Formulário para configurar
echo "<h2>🔧 Configurar Chave da API Asaas</h2>";

echo "<form method='POST'>";
echo "<div class='form-group'>";
echo "<label for='asaas_key'>Chave da API Asaas:</label>";
echo "<input type='text' id='asaas_key' name='asaas_key' value='" . htmlspecialchars($asaas_key_config) . "' placeholder='Cole aqui a chave da API Asaas' required>";
echo "</div>";

echo "<div class='form-group'>";
echo "<button type='submit' class='btn'>💾 Salvar Configuração</button>";
echo "</div>";
echo "</form>";

// Adicionar outras configurações necessárias
echo "<h2>⚙️ Outras Configurações Necessárias</h2>";

// Verificar e adicionar configurações faltantes
$configuracoes_necessarias = [
    'sistema_status' => 'Sistema ativo e funcionando',
    'whatsapp_vps_url' => WHATSAPP_ROBOT_URL,
    'sistema_versao' => '2.0',
    'ultima_atualizacao' => date('Y-m-d H:i:s')
];

foreach ($configuracoes_necessarias as $chave => $valor) {
    $result = $mysqli->query("SELECT valor FROM configuracoes WHERE chave = '$chave' LIMIT 1");
    
    if (!$result || $result->num_rows === 0) {
        $valor_escaped = $mysqli->real_escape_string($valor);
        $sql = "INSERT INTO configuracoes (chave, valor, descricao, data_criacao, data_atualizacao) 
                VALUES ('$chave', '$valor_escaped', 'Configuração automática do sistema', NOW(), NOW())";
        $mysqli->query($sql);
        echo "<div class='info'>✅ Configuração '$chave' adicionada automaticamente</div>";
    } else {
        echo "<div class='success'>✅ Configuração '$chave' já existe</div>";
    }
}

echo "<h2>✅ Verificação Final</h2>";

// Verificar todas as configurações
$result = $mysqli->query("SELECT chave, valor FROM configuracoes ORDER BY chave");
$configs = [];
while ($row = $result->fetch_assoc()) {
    $configs[$row['chave']] = $row['valor'];
}

echo "<div class='code'>";
echo "<strong>Configurações no Banco:</strong>\n";
foreach ($configs as $chave => $valor) {
    $valor_mascarado = strlen($valor) > 20 ? substr($valor, 0, 20) . '...' : $valor;
    echo "• $chave: $valor_mascarado\n";
}
echo "</div>";

echo "<div class='success'>";
echo "<strong>🎯 Próximo Passo:</strong><br>";
echo "Execute novamente o teste de produção para verificar se todas as configurações estão funcionando!";
echo "</div>";

echo "</div>";
?> 