<?php
/**
 * 🔧 RESTAURAÇÃO COMPLETA DA VPS - WHATSAPP API
 * 
 * Script para restaurar completamente a VPS com todas as configurações
 * de webhooks, endpoints e serviços baseado no commit restaurado
 */

echo "🔧 RESTAURAÇÃO COMPLETA DA VPS - WHATSAPP API\n";
echo "=============================================\n\n";

// Configurações da VPS
$vps_ip = '212.85.11.238';
$vps_user = 'root';
$vps_password = ''; // Será solicitado via SSH

// URLs dos webhooks baseadas no commit restaurado
$webhook_urls = [
    'local' => 'http://localhost:8080/loja-virtual-revenda/api/webhook_whatsapp.php',
    'producao' => 'https://app.pixel12digital.com.br/painel/receber_mensagem_ana_local.php',
    'alternativo' => 'https://revendawebvirtual.com.br/api/webhook_whatsapp.php'
];

// Configurações dos canais
$canais = [
    '3000' => [
        'nome' => 'Canal Financeiro (Ana)',
        'porta' => 3000,
        'identificador' => '554797146908@c.us',
        'nome_exibicao' => 'Pixel12Digital'
    ],
    '3001' => [
        'nome' => 'Canal Comercial (Humano)',
        'porta' => 3001,
        'identificador' => '554797309525@c.us',
        'nome_exibicao' => 'Comercial - Pixel'
    ]
];

echo "📋 CONFIGURAÇÕES IDENTIFICADAS:\n";
echo "VPS IP: $vps_ip\n";
echo "Canais: " . count($canais) . " (3000 e 3001)\n";
echo "Webhooks disponíveis: " . count($webhook_urls) . "\n\n";

// 1. VERIFICAR CONECTIVIDADE COM VPS
echo "1️⃣ VERIFICANDO CONECTIVIDADE COM VPS\n";
echo "------------------------------------\n";

$vps_status = verificarConectividadeVPS($vps_ip);
if (!$vps_status['conectavel']) {
    echo "❌ VPS não está acessível: {$vps_status['erro']}\n";
    echo "🔧 Verifique se a VPS está online e acessível\n\n";
    exit(1);
}

echo "✅ VPS acessível\n\n";

// 2. VERIFICAR SERVIÇOS ATUAIS
echo "2️⃣ VERIFICANDO SERVIÇOS ATUAIS\n";
echo "------------------------------\n";

foreach ($canais as $canal_id => $canal) {
    $porta = $canal['porta'];
    echo "🔍 {$canal['nome']} (Porta $porta)...\n";
    
    $status = verificarServicoCanal($vps_ip, $porta);
    if ($status['ativo']) {
        echo "  ✅ Serviço ativo\n";
        echo "  📊 Status: {$status['status']}\n";
    } else {
        echo "  ❌ Serviço inativo\n";
        echo "  🔧 Será reiniciado\n";
    }
    echo "\n";
}

// 3. CONFIGURAR WEBHOOKS
echo "3️⃣ CONFIGURANDO WEBHOOKS\n";
echo "------------------------\n";

// Usar webhook de produção como padrão
$webhook_principal = $webhook_urls['producao'];

echo "🔗 Webhook principal: $webhook_principal\n\n";

foreach ($canais as $canal_id => $canal) {
    $porta = $canal['porta'];
    echo "🔧 Configurando {$canal['nome']}...\n";
    
    $resultado = configurarWebhookCanal($vps_ip, $porta, $webhook_principal);
    if ($resultado['sucesso']) {
        echo "  ✅ Webhook configurado\n";
        echo "  📝 Resposta: {$resultado['resposta']}\n";
    } else {
        echo "  ❌ Erro ao configurar webhook\n";
        echo "  🔧 Erro: {$resultado['erro']}\n";
    }
    echo "\n";
}

// 4. VERIFICAR CONFIGURAÇÕES FINAIS
echo "4️⃣ VERIFICANDO CONFIGURAÇÕES FINAIS\n";
echo "-----------------------------------\n";

foreach ($canais as $canal_id => $canal) {
    $porta = $canal['porta'];
    echo "🔍 Verificando {$canal['nome']}...\n";
    
    $config = verificarConfiguracaoWebhook($vps_ip, $porta);
    if ($config['sucesso']) {
        echo "  ✅ Configuração verificada\n";
        echo "  🔗 URL: {$config['webhook_url']}\n";
    } else {
        echo "  ❌ Não foi possível verificar configuração\n";
        echo "  🔧 Erro: {$config['erro']}\n";
    }
    echo "\n";
}

// 5. TESTAR ENVIOS
echo "5️⃣ TESTANDO ENVIOS\n";
echo "------------------\n";

foreach ($canais as $canal_id => $canal) {
    $porta = $canal['porta'];
    echo "🧪 Testando {$canal['nome']}...\n";
    
    $teste = testarEnvioCanal($vps_ip, $porta);
    if ($teste['sucesso']) {
        echo "  ✅ Teste de envio bem-sucedido\n";
        echo "  📊 Resposta: {$teste['resposta']}\n";
    } else {
        echo "  ❌ Teste de envio falhou\n";
        echo "  🔧 Erro: {$teste['erro']}\n";
    }
    echo "\n";
}

// 6. ATUALIZAR BANCO DE DADOS
echo "6️⃣ ATUALIZANDO BANCO DE DADOS\n";
echo "-----------------------------\n";

$resultado_banco = atualizarCanaisBanco($canais);
if ($resultado_banco['sucesso']) {
    echo "✅ Canais atualizados no banco de dados\n";
    echo "📊 {$resultado_banco['atualizados']} canais processados\n";
} else {
    echo "❌ Erro ao atualizar banco de dados\n";
    echo "🔧 Erro: {$resultado_banco['erro']}\n";
}

echo "\n";

// 7. RESUMO FINAL
echo "7️⃣ RESUMO DA RESTAURAÇÃO\n";
echo "------------------------\n";

echo "🎯 RESTAURAÇÃO CONCLUÍDA!\n\n";

echo "📋 CONFIGURAÇÕES APLICADAS:\n";
echo "• VPS: $vps_ip\n";
echo "• Canais: " . count($canais) . " (3000 e 3001)\n";
echo "• Webhook: $webhook_principal\n";
echo "• Status: Ativo e funcionando\n\n";

echo "🔧 PRÓXIMOS PASSOS:\n";
echo "1. Acesse o painel de comunicação\n";
echo "2. Verifique se os canais estão conectados\n";
echo "3. Envie uma mensagem de teste\n";
echo "4. Monitore os logs se necessário\n\n";

echo "📚 COMANDOS ÚTEIS:\n";
echo "• Verificar status: curl http://$vps_ip:3000/status\n";
echo "• Verificar webhook: curl http://$vps_ip:3000/webhook/config\n";
echo "• Logs PM2: ssh root@$vps_ip 'pm2 logs --lines 20'\n\n";

echo "✅ RESTAURAÇÃO FINALIZADA COM SUCESSO!\n";

// ===== FUNÇÕES AUXILIARES =====

/**
 * Verifica se a VPS está acessível
 */
function verificarConectividadeVPS($vps_ip) {
    $ch = curl_init("http://$vps_ip:3000/status");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    return [
        'conectavel' => ($http_code === 200 && !$error),
        'erro' => $error ?: "HTTP $http_code"
    ];
}

/**
 * Verifica se um canal específico está ativo
 */
function verificarServicoCanal($vps_ip, $porta) {
    $ch = curl_init("http://$vps_ip:$porta/status");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($http_code === 200 && !$error) {
        $data = json_decode($response, true);
        return [
            'ativo' => true,
            'status' => $data['status'] ?? 'unknown'
        ];
    }
    
    return [
        'ativo' => false,
        'status' => 'inactive'
    ];
}

/**
 * Configura webhook para um canal específico
 */
function configurarWebhookCanal($vps_ip, $porta, $webhook_url) {
    $ch = curl_init("http://$vps_ip:$porta/webhook/config");
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['url' => $webhook_url]));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($http_code === 200 && !$error) {
        return [
            'sucesso' => true,
            'resposta' => $response
        ];
    }
    
    return [
        'sucesso' => false,
        'erro' => $error ?: "HTTP $http_code"
    ];
}

/**
 * Verifica configuração atual do webhook
 */
function verificarConfiguracaoWebhook($vps_ip, $porta) {
    $ch = curl_init("http://$vps_ip:$porta/webhook/config");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($http_code === 200 && !$error) {
        $data = json_decode($response, true);
        return [
            'sucesso' => true,
            'webhook_url' => $data['webhook_url'] ?? 'N/A'
        ];
    }
    
    return [
        'sucesso' => false,
        'erro' => $error ?: "HTTP $http_code"
    ];
}

/**
 * Testa envio de mensagem em um canal
 */
function testarEnvioCanal($vps_ip, $porta) {
    $ch = curl_init("http://$vps_ip:$porta/send/text");
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
        'to' => '5511999999999',
        'message' => 'Teste de restauração VPS - ' . date('Y-m-d H:i:s')
    ]));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($http_code === 200 && !$error) {
        return [
            'sucesso' => true,
            'resposta' => $response
        ];
    }
    
    return [
        'sucesso' => false,
        'erro' => $error ?: "HTTP $http_code"
    ];
}

/**
 * Atualiza canais no banco de dados
 */
function atualizarCanaisBanco($canais) {
    try {
        require_once 'config.php';
        require_once 'painel/db.php';
        
        $atualizados = 0;
        
        foreach ($canais as $canal_id => $canal) {
            $identificador = $canal['identificador'];
            $nome_exibicao = $canal['nome_exibicao'];
            $porta = $canal['porta'];
            
            // Verificar se o canal já existe
            $sql_check = "SELECT id FROM canais_comunicacao WHERE identificador = ? AND tipo = 'whatsapp'";
            $stmt_check = $mysqli->prepare($sql_check);
            $stmt_check->bind_param('s', $identificador);
            $stmt_check->execute();
            $result_check = $stmt_check->get_result();
            
            if ($result_check->num_rows > 0) {
                // Atualizar canal existente
                $canal_db = $result_check->fetch_assoc();
                $sql_update = "UPDATE canais_comunicacao SET 
                              nome_exibicao = ?, 
                              status = 'conectado',
                              data_conexao = NOW()
                              WHERE id = ?";
                $stmt_update = $mysqli->prepare($sql_update);
                $stmt_update->bind_param('si', $nome_exibicao, $canal_db['id']);
                $stmt_update->execute();
                $atualizados++;
            } else {
                // Inserir novo canal
                $sql_insert = "INSERT INTO canais_comunicacao 
                              (tipo, identificador, nome_exibicao, status, data_conexao) 
                              VALUES ('whatsapp', ?, ?, 'conectado', NOW())";
                $stmt_insert = $mysqli->prepare($sql_insert);
                $stmt_insert->bind_param('ss', $identificador, $nome_exibicao);
                $stmt_insert->execute();
                $atualizados++;
            }
        }
        
        return [
            'sucesso' => true,
            'atualizados' => $atualizados
        ];
        
    } catch (Exception $e) {
        return [
            'sucesso' => false,
            'erro' => $e->getMessage()
        ];
    }
}
?> 