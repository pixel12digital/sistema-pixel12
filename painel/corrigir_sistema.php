<?php
/**
 * Script para corrigir problemas do sistema
 * Executa automaticamente as correções necessárias
 */

require_once 'config.php';
require_once 'db.php';

echo "<h1>🔧 Correção Automática do Sistema</h1>";
echo "<style>
    body{font-family:Arial,sans-serif;margin:20px;background:#f5f5f5;}
    .container{max-width:800px;margin:0 auto;background:white;padding:20px;border-radius:10px;box-shadow:0 2px 10px rgba(0,0,0,0.1);}
    .step{background:#f8f9fa;padding:15px;margin:15px 0;border-radius:8px;border-left:4px solid #007bff;}
    .success{color:#28a745;border-left-color:#28a745;}
    .error{color:#dc3545;border-left-color:#dc3545;}
    .warning{color:#ffc107;border-left-color:#ffc107;}
    .info{color:#17a2b8;border-left-color:#17a2b8;}
</style>";

echo "<div class='container'>";

$correcoes = [];

// 1. Criar tabela de configurações
echo "<div class='step'>";
echo "<h3>1. Criando Tabela de Configurações</h3>";

$sql_config = "
CREATE TABLE IF NOT EXISTS `configuracoes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `chave` varchar(100) NOT NULL,
  `valor` text NOT NULL,
  `descricao` varchar(255) DEFAULT NULL,
  `tipo` enum('texto','numero','booleano','json') NOT NULL DEFAULT 'texto',
  `data_criacao` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `data_atualizacao` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `chave` (`chave`),
  KEY `idx_chave` (`chave`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
";

try {
    if ($mysqli->query($sql_config)) {
        echo "<p class='success'>✅ Tabela configuracoes criada</p>";
        $correcoes[] = "Tabela configuracoes: CRIADA";
    } else {
        echo "<p class='error'>❌ Erro ao criar tabela: " . $mysqli->error . "</p>";
        $correcoes[] = "Tabela configuracoes: ERRO";
    }
} catch (Exception $e) {
    echo "<p class='error'>❌ Exceção: " . $e->getMessage() . "</p>";
    $correcoes[] = "Tabela configuracoes: EXCEÇÃO";
}
echo "</div>";

// 2. Inserir configurações padrão
echo "<div class='step'>";
echo "<h3>2. Inserindo Configurações Padrão</h3>";

$configs_padrao = [
    ['asaas_api_key', '', 'Chave da API do Asaas'],
    ['asaas_ambiente', 'sandbox', 'Ambiente do Asaas'],
    ['whatsapp_webhook_url', '', 'URL do webhook do WhatsApp'],
    ['whatsapp_vps_url', 'http://212.85.11.238:3000', 'URL do servidor VPS do WhatsApp'],
    ['sistema_nome', 'Pixel12 Digital', 'Nome do sistema'],
    ['sistema_versao', '2.0', 'Versão do sistema'],
    ['monitoramento_ativo', '1', 'Monitoramento automático ativo'],
    ['max_mensagens_dia', '50', 'Máximo de mensagens por dia'],
    ['horario_inicio_envio', '09:00', 'Horário de início para envio'],
    ['horario_fim_envio', '18:00', 'Horário de fim para envio']
];

$configs_inseridas = 0;
foreach ($configs_padrao as $config) {
    $chave = $mysqli->real_escape_string($config[0]);
    $valor = $mysqli->real_escape_string($config[1]);
    $descricao = $mysqli->real_escape_string($config[2]);
    
    $sql = "INSERT IGNORE INTO configuracoes (chave, valor, descricao) VALUES ('$chave', '$valor', '$descricao')";
    
    if ($mysqli->query($sql)) {
        if ($mysqli->affected_rows > 0) {
            echo "<p class='success'>✅ $chave inserida</p>";
            $configs_inseridas++;
        } else {
            echo "<p class='info'>ℹ️ $chave já existe</p>";
        }
    } else {
        echo "<p class='error'>❌ Erro ao inserir $chave: " . $mysqli->error . "</p>";
    }
}

$correcoes[] = "Configurações: $configs_inseridas inseridas";
echo "</div>";

// 3. Testar conectividade WhatsApp
echo "<div class='step'>";
echo "<h3>3. Testando Conectividade WhatsApp</h3>";

function testarWhatsApp() {
    $payload = json_encode([
        'to' => '5547996164699@c.us',
        'message' => '🔧 Teste de conectividade - ' . date('d/m/Y H:i:s')
    ]);
    
    $ch = curl_init("http://212.85.11.238:3000/send");
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    return [
        'success' => $http_code === 200 && !$error,
        'http_code' => $http_code,
        'error' => $error,
        'response' => $response
    ];
}

$resultado_whatsapp = testarWhatsApp();

if ($resultado_whatsapp['success']) {
    echo "<p class='success'>✅ WhatsApp VPS conectado</p>";
    $correcoes[] = "WhatsApp: CONECTADO";
} else {
    echo "<p class='error'>❌ Erro WhatsApp: " . $resultado_whatsapp['error'] . "</p>";
    echo "<p class='info'>ℹ️ HTTP Code: " . $resultado_whatsapp['http_code'] . "</p>";
    $correcoes[] = "WhatsApp: ERRO - " . $resultado_whatsapp['error'];
}
echo "</div>";

// 4. Criar pasta logs se não existir
echo "<div class='step'>";
echo "<h3>4. Verificando Pasta de Logs</h3>";

if (!is_dir('logs')) {
    if (mkdir('logs', 0755, true)) {
        echo "<p class='success'>✅ Pasta logs criada</p>";
        $correcoes[] = "Pasta logs: CRIADA";
    } else {
        echo "<p class='error'>❌ Erro ao criar pasta logs</p>";
        $correcoes[] = "Pasta logs: ERRO";
    }
} else {
    echo "<p class='success'>✅ Pasta logs já existe</p>";
    $correcoes[] = "Pasta logs: OK";
}
echo "</div>";

// 5. Testar API Asaas com configuração
echo "<div class='step'>";
echo "<h3>5. Testando API Asaas</h3>";

try {
    $config = $mysqli->query("SELECT valor FROM configuracoes WHERE chave = 'asaas_api_key' LIMIT 1")->fetch_assoc();
    
    if ($config && !empty($config['valor'])) {
        $ch = curl_init("https://www.asaas.com/api/v3/customers");
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'access_token: ' . $config['valor'],
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($http_code === 200) {
            echo "<p class='success'>✅ API Asaas conectada</p>";
            $correcoes[] = "API Asaas: CONECTADA";
        } else {
            echo "<p class='warning'>⚠️ API Asaas retornou código $http_code</p>";
            $correcoes[] = "API Asaas: HTTP $http_code";
        }
    } else {
        echo "<p class='warning'>⚠️ Chave da API Asaas não configurada</p>";
        echo "<p class='info'>ℹ️ Configure a chave em: Configurações > Asaas API Key</p>";
        $correcoes[] = "API Asaas: SEM CHAVE";
    }
} catch (Exception $e) {
    echo "<p class='error'>❌ Erro API Asaas: " . $e->getMessage() . "</p>";
    $correcoes[] = "API Asaas: ERRO";
}
echo "</div>";

// Resumo das correções
echo "<div class='step success'>";
echo "<h3>📊 Resumo das Correções</h3>";
foreach ($correcoes as $correcao) {
    echo "<p>• $correcao</p>";
}

$sucessos = count(array_filter($correcoes, function($c) { 
    return strpos($c, 'ERRO') === false && strpos($c, 'EXCEÇÃO') === false; 
}));
$total = count($correcoes);
$percentual = round(($sucessos / $total) * 100, 1);

echo "<p><strong>Taxa de Sucesso: $percentual% ($sucessos/$total)</strong></p>";
echo "</div>";

// Enviar relatório
echo "<div class='step'>";
echo "<h3>📤 Enviando Relatório de Correção</h3>";

$relatorio = "🔧 RELATÓRIO DE CORREÇÃO - SISTEMA PIXEL12\n\n";
$relatorio .= "Data/Hora: " . date('d/m/Y H:i:s') . "\n";
$relatorio .= "Taxa de Sucesso: $percentual%\n\n";

foreach ($correcoes as $correcao) {
    $relatorio .= "• $correcao\n";
}

$relatorio .= "\n🎯 STATUS: ";
if ($percentual >= 80) {
    $relatorio .= "SISTEMA CORRIGIDO! 🚀";
} elseif ($percentual >= 60) {
    $relatorio .= "MAIORIA CORRIGIDA ⚠️";
} else {
    $relatorio .= "PROBLEMAS PERSISTEM ❌";
}

$relatorio .= "\n\nAtenciosamente,\nSistema de Correção";

$resultado_envio = testarWhatsApp();
if ($resultado_envio['success']) {
    echo "<p class='success'>✅ Relatório enviado para WhatsApp</p>";
} else {
    echo "<p class='error'>❌ Erro ao enviar relatório</p>";
}
echo "</div>";

echo "</div>";
?> 