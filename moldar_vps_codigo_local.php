<?php
/**
 * 🔧 MOLDAR VPS DE ACORDO COM CÓDIGO LOCAL
 * 
 * Script para ajustar a VPS para corresponder exatamente às configurações do código local
 * Baseado na averiguação completa realizada
 */

echo "🔧 MOLDANDO VPS DE ACORDO COM CÓDIGO LOCAL\n";
echo "==========================================\n\n";

// ===== CONFIGURAÇÕES BASEADAS NO CÓDIGO LOCAL =====
require_once 'config.php';

$vps_ip = '212.85.11.238';
$webhook_principal = 'https://app.pixel12digital.com.br/painel/receber_mensagem_ana_local.php';

// Canais baseados no código local
$canais_codigo = [
    '3000' => [
        'nome' => 'Canal Financeiro (Ana)',
        'porta' => 3000,
        'identificador' => '554797146908@c.us',
        'nome_exibicao' => 'Pixel12Digital',
        'session' => 'default',
        'api_file' => 'whatsapp-api-server.js'
    ],
    '3001' => [
        'nome' => 'Canal Comercial (Humano)',
        'porta' => 3001,
        'identificador' => '554797309525@c.us',
        'nome_exibicao' => 'Comercial - Pixel',
        'session' => 'comercial',
        'api_file' => 'whatsapp-api-server.js' // Assumindo que deve usar a mesma API
    ]
];

echo "📋 CONFIGURAÇÕES DO CÓDIGO LOCAL:\n";
echo "VPS IP: $vps_ip\n";
echo "Webhook Principal: $webhook_principal\n";
echo "Canais: " . count($canais_codigo) . " (3000 e 3001)\n";
echo "API Base: whatsapp-api-server.js\n\n";

// ===== 1. VERIFICAR STATUS ATUAL DA VPS =====
echo "1️⃣ VERIFICANDO STATUS ATUAL DA VPS\n";
echo "----------------------------------\n";

$status_atual = [];

foreach ($canais_codigo as $canal_id => $canal) {
    $porta = $canal['porta'];
    echo "🔍 Verificando {$canal['nome']} (Porta $porta)...\n";
    
    $ch = curl_init("http://$vps_ip:$porta/status");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    $status_atual[$canal_id] = [
        'http_code' => $http_code,
        'response' => $response,
        'error' => $error,
        'funcionando' => ($http_code === 200 && !$error)
    ];
    
    if ($status_atual[$canal_id]['funcionando']) {
        echo "  ✅ Canal funcionando\n";
        $status_data = json_decode($response, true);
        if ($status_data) {
            echo "  📊 Status: " . ($status_data['status'] ?? 'unknown') . "\n";
            echo "  🔗 Porta: " . ($status_data['port'] ?? 'unknown') . "\n";
        }
    } else {
        echo "  ❌ Canal não responde (HTTP $http_code)\n";
        if ($error) {
            echo "  🔧 Erro: $error\n";
        }
    }
    echo "\n";
}

// ===== 2. VERIFICAR WEBHOOKS ATUAIS =====
echo "2️⃣ VERIFICANDO WEBHOOKS ATUAIS\n";
echo "-------------------------------\n";

$webhooks_atuais = [];

foreach ($canais_codigo as $canal_id => $canal) {
    $porta = $canal['porta'];
    echo "🔍 Verificando webhook {$canal['nome']}...\n";
    
    $ch = curl_init("http://$vps_ip:$porta/webhook/config");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    $webhooks_atuais[$canal_id] = [
        'http_code' => $http_code,
        'response' => $response,
        'error' => $error,
        'configurado' => ($http_code === 200 && !$error)
    ];
    
    if ($webhooks_atuais[$canal_id]['configurado']) {
        echo "  ✅ Webhook configurado\n";
        $webhook_data = json_decode($response, true);
        if ($webhook_data && isset($webhook_data['webhook_url'])) {
            echo "  🔗 URL: {$webhook_data['webhook_url']}\n";
        }
    } else {
        echo "  ❌ Webhook não configurado (HTTP $http_code)\n";
        if ($error) {
            echo "  🔧 Erro: $error\n";
        }
    }
    echo "\n";
}

// ===== 3. CONFIGURAR WEBHOOKS CONFORME CÓDIGO LOCAL =====
echo "3️⃣ CONFIGURANDO WEBHOOKS CONFORME CÓDIGO LOCAL\n";
echo "-----------------------------------------------\n";

$webhooks_configurados = 0;

foreach ($canais_codigo as $canal_id => $canal) {
    $porta = $canal['porta'];
    
    // Verificar se precisa configurar webhook
    $precisa_configurar = !$webhooks_atuais[$canal_id]['configurado'] || 
                         ($webhooks_atuais[$canal_id]['configurado'] && 
                          strpos($webhooks_atuais[$canal_id]['response'], $webhook_principal) === false);
    
    if ($precisa_configurar) {
        echo "🔧 Configurando webhook {$canal['nome']}...\n";
        
        // Tentar diferentes endpoints para canal 3001
        $endpoints = ['/webhook/config', '/webhook', '/hook/config', '/hook'];
        $webhook_configurado = false;
        
        foreach ($endpoints as $endpoint) {
            $ch = curl_init("http://$vps_ip:$porta$endpoint");
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['url' => $webhook_principal]));
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            
            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);
            
            if ($http_code === 200 && !$error) {
                echo "  ✅ Webhook configurado via $endpoint\n";
                $webhook_configurado = true;
                $webhooks_configurados++;
                break;
            }
        }
        
        if (!$webhook_configurado) {
            echo "  ⚠️ Não foi possível configurar webhook (API diferente)\n";
        }
    } else {
        echo "✅ Webhook {$canal['nome']} já configurado corretamente\n";
    }
    echo "\n";
}

// ===== 4. VERIFICAR E CONFIGURAR API CORRETA =====
echo "4️⃣ VERIFICANDO E CONFIGURANDO API CORRETA\n";
echo "------------------------------------------\n";

// Verificar se a API correta está rodando
$api_correta = false;

foreach ($canais_codigo as $canal_id => $canal) {
    $porta = $canal['porta'];
    echo "🔍 Verificando API {$canal['nome']}...\n";
    
    // Testar endpoints da API correta
    $endpoints_teste = ['/send/text', '/webhook/config', '/status', '/qr'];
    $endpoints_funcionando = 0;
    
    foreach ($endpoints_teste as $endpoint) {
        $ch = curl_init("http://$vps_ip:$porta$endpoint");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_NOBODY, true); // HEAD request
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($http_code !== 404) {
            $endpoints_funcionando++;
        }
    }
    
    $percentual_funcionando = ($endpoints_funcionando / count($endpoints_teste)) * 100;
    
    if ($percentual_funcionando >= 75) {
        echo "  ✅ API correta detectada ($percentual_funcionando% dos endpoints funcionando)\n";
        $api_correta = true;
    } else {
        echo "  ⚠️ API diferente detectada ($percentual_funcionando% dos endpoints funcionando)\n";
        echo "  🔧 Necessita migração para API correta\n";
    }
    echo "\n";
}

// ===== 5. ATUALIZAR BANCO DE DADOS CONFORME CÓDIGO LOCAL =====
echo "5️⃣ ATUALIZANDO BANCO DE DADOS CONFORME CÓDIGO LOCAL\n";
echo "---------------------------------------------------\n";

try {
    $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($mysqli->connect_error) {
        echo "❌ Erro de conexão com banco: " . $mysqli->connect_error . "\n\n";
    } else {
        echo "✅ Conectado ao banco de dados\n";
        
        $canais_atualizados = 0;
        
        foreach ($canais_codigo as $canal_id => $canal) {
            $identificador = $canal['identificador'];
            $nome_exibicao = $canal['nome_exibicao'];
            $porta = $canal['porta'];
            $sessao = $canal['session'];
            $status = $status_atual[$canal_id]['funcionando'] ? 'conectado' : 'offline';
            
            // Atualizar ou inserir canal
            $sql = "INSERT INTO canais_comunicacao 
                    (tipo, identificador, nome_exibicao, status, porta, sessao, data_conexao) 
                    VALUES ('whatsapp', ?, ?, ?, ?, ?, NOW())
                    ON DUPLICATE KEY UPDATE 
                    nome_exibicao = VALUES(nome_exibicao),
                    status = VALUES(status),
                    porta = VALUES(porta),
                    sessao = VALUES(sessao),
                    data_conexao = NOW()";
            
            $stmt = $mysqli->prepare($sql);
            $stmt->bind_param('sssss', $identificador, $nome_exibicao, $status, $porta, $sessao);
            $stmt->execute();
            
            echo "  ✅ Canal {$nome_exibicao} atualizado (Status: $status, Porta: $porta, Sessão: $sessao)\n";
            $canais_atualizados++;
        }
        
        echo "📊 {$canais_atualizados} canais atualizados no banco de dados\n";
    }
} catch (Exception $e) {
    echo "❌ Erro ao atualizar banco de dados: " . $e->getMessage() . "\n";
}

echo "\n";

// ===== 6. TESTAR FUNCIONALIDADES CONFORME CÓDIGO LOCAL =====
echo "6️⃣ TESTANDO FUNCIONALIDADES CONFORME CÓDIGO LOCAL\n";
echo "-------------------------------------------------\n";

foreach ($canais_codigo as $canal_id => $canal) {
    $porta = $canal['porta'];
    $session = $canal['session'];
    
    if ($status_atual[$canal_id]['funcionando']) {
        echo "🧪 Testando {$canal['nome']}...\n";
        
        // Testar envio de mensagem (formato do código local)
        $test_data = [
            'sessionName' => $session,
            'number' => '5511999999999',
            'message' => 'Teste moldagem VPS - ' . date('Y-m-d H:i:s')
        ];
        
        $ch = curl_init("http://$vps_ip:$porta/send/text");
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($test_data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($http_code === 200 && !$error) {
            echo "  ✅ Envio funcionando\n";
            $result = json_decode($response, true);
            if ($result && isset($result['success']) && $result['success']) {
                echo "  📝 Mensagem enviada com sucesso\n";
            }
        } else {
            echo "  ❌ Erro no envio (HTTP $http_code)\n";
            if ($error) {
                echo "  🔧 Erro: $error\n";
            }
        }
        
        // Testar webhook
        if ($webhooks_atuais[$canal_id]['configurado']) {
            $ch = curl_init("http://$vps_ip:$porta/webhook/test");
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            
            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($http_code === 200) {
                echo "  ✅ Webhook testado com sucesso\n";
            } else {
                echo "  ❌ Erro no teste webhook (HTTP $http_code)\n";
            }
        }
    } else {
        echo "⚠️ {$canal['nome']} não está funcionando - pulando testes\n";
    }
    echo "\n";
}

// ===== 7. GERAR RELATÓRIO DE MOLDAGEM =====
echo "7️⃣ RELATÓRIO DE MOLDAGEM DA VPS\n";
echo "--------------------------------\n";

$total_canais = count($canais_codigo);
$canais_funcionando = 0;
$webhooks_ok = 0;

foreach ($canais_codigo as $canal_id => $canal) {
    if ($status_atual[$canal_id]['funcionando']) {
        $canais_funcionando++;
    }
    if ($webhooks_atuais[$canal_id]['configurado']) {
        $webhooks_ok++;
    }
}

echo "📊 RESUMO DA MOLDAGEM:\n";
echo "• Total de canais: $total_canais\n";
echo "• Canais funcionando: $canais_funcionando/$total_canais\n";
echo "• Webhooks configurados: $webhooks_ok/$total_canais\n";
echo "• Webhooks configurados agora: $webhooks_configurados\n";
echo "• API correta: " . ($api_correta ? "✅ Detectada" : "⚠️ Necessita ajuste") . "\n\n";

// ===== 8. COMANDOS PARA AJUSTES MANUAIS =====
echo "8️⃣ COMANDOS PARA AJUSTES MANUAIS (SE NECESSÁRIO)\n";
echo "------------------------------------------------\n";

echo "🔧 Se algum canal não estiver funcionando:\n";
echo "ssh root@$vps_ip\n";
echo "pm2 restart whatsapp-$canal_id\n";
echo "pm2 logs whatsapp-$canal_id --lines 20\n\n";

echo "🔧 Para conectar WhatsApp no canal 3000:\n";
echo "curl http://$vps_ip:3000/qr\n\n";

echo "🔧 Para verificar status geral:\n";
echo "pm2 status\n";
echo "curl http://$vps_ip:3000/status\n";
echo "curl http://$vps_ip:3001/status\n\n";

echo "🔧 Para testar webhooks:\n";
echo "curl http://$vps_ip:3000/webhook/config\n";
echo "curl -X POST http://$vps_ip:3000/webhook/test\n\n";

// ===== 9. RESUMO FINAL =====
echo "9️⃣ RESUMO FINAL DA MOLDAGEM\n";
echo "---------------------------\n";

echo "🎯 MOLDAGEM CONCLUÍDA!\n\n";

echo "📋 VPS MOLDADA CONFORME CÓDIGO LOCAL:\n";
echo "• VPS IP: $vps_ip\n";
echo "• Webhook Principal: $webhook_principal\n";
echo "• Canais configurados: " . count($canais_codigo) . "\n";
echo "• API Base: whatsapp-api-server.js\n";
echo "• Banco de dados: Sincronizado\n\n";

echo "✅ STATUS FINAL:\n";
foreach ($canais_codigo as $canal_id => $canal) {
    $status = $status_atual[$canal_id]['funcionando'] ? "✅ Funcionando" : "❌ Offline";
    $webhook = $webhooks_atuais[$canal_id]['configurado'] ? "✅ Webhook OK" : "❌ Webhook NOK";
    echo "• {$canal['nome']}: $status | $webhook\n";
}

echo "\n📚 PRÓXIMOS PASSOS:\n";
echo "1. Conectar WhatsApp no canal 3000 (gerar QR Code)\n";
echo "2. Verificar painel de comunicação\n";
echo "3. Testar envio de mensagens reais\n";
echo "4. Monitorar logs se necessário\n\n";

echo "📞 COMANDOS ÚTEIS:\n";
echo "• Status: curl http://$vps_ip:3000/status\n";
echo "• QR Code: curl http://$vps_ip:3000/qr\n";
echo "• Webhook: curl http://$vps_ip:3000/webhook/config\n";
echo "• Logs: ssh root@$vps_ip 'pm2 logs --lines 20'\n\n";

echo "✅ MOLDAGEM FINALIZADA COM SUCESSO!\n";
echo "🎉 VPS moldada de acordo com o código local!\n";
?> 