<?php
/**
 * CORREÇÃO SEM SSH - WHATSAPP
 * Script para corrigir problemas sem acesso SSH
 */

echo "🔧 CORREÇÃO WHATSAPP SEM SSH\n";
echo "============================\n\n";

// Configurações
$vps_ip = '212.85.11.238';
$portas = [3000, 3001];

// Função para testar conectividade sem curl
function testarConectividade($url) {
    $context = stream_context_create([
        'http' => [
            'timeout' => 5,
            'user_agent' => 'WhatsApp-Correction/1.0',
            'ignore_errors' => true
        ]
    ]);
    
    $response = @file_get_contents($url, false, $context);
    
    // Extrair código HTTP dos headers
    $http_code = 0;
    if (isset($http_response_header)) {
        foreach ($http_response_header as $header) {
            if (preg_match('/HTTP\/\d\.\d\s+(\d+)/', $header, $matches)) {
                $http_code = intval($matches[1]);
                break;
            }
        }
    }
    
    return [
        'http_code' => $http_code,
        'response' => $response,
        'success' => $response !== false
    ];
}

// 1. Testar conectividade atual
echo "1. 🔍 TESTANDO CONECTIVIDADE ATUAL:\n";
$status_portas = [];

foreach ($portas as $porta) {
    $url = "http://{$vps_ip}:{$porta}/status";
    echo "   📡 Testando porta $porta...\n";
    
    $resultado = testarConectividade($url);
    
    if ($resultado['success'] && $resultado['http_code'] == 200) {
        echo "   ✅ Porta $porta: Respondendo\n";
        
        // Tentar decodificar JSON
        $data = json_decode($resultado['response'], true);
        if ($data) {
            $ready = isset($data['ready']) ? ($data['ready'] ? 'true' : 'false') : 'N/A';
            $status = isset($data['status']) ? $data['status'] : 'N/A';
            echo "   📊 Ready: $ready\n";
            echo "   📊 Status: $status\n";
            
            $status_portas[$porta] = [
                'funcionando' => true,
                'ready' => isset($data['ready']) ? $data['ready'] : false,
                'status' => $status
            ];
        } else {
            echo "   ⚠️  Resposta não é JSON válido\n";
            $status_portas[$porta] = ['funcionando' => true, 'ready' => false];
        }
    } else {
        echo "   ❌ Porta $porta: Não respondendo (HTTP {$resultado['http_code']})\n";
        $status_portas[$porta] = ['funcionando' => false, 'ready' => false];
    }
    echo "\n";
}

// 2. Atualizar banco de dados
echo "2. 🗄️ ATUALIZANDO BANCO DE DADOS:\n";

try {
    // Incluir configuração do banco
    if (file_exists('config.php')) {
        require_once 'config.php';
    } elseif (file_exists('painel/db.php')) {
        require_once 'painel/db.php';
    } else {
        throw new Exception('Arquivo de configuração do banco não encontrado');
    }
    
    // Verificar se a conexão foi estabelecida
    if (!isset($mysqli) || !$mysqli) {
        throw new Exception('Conexão com banco de dados não estabelecida');
    }
    
    $canais = $mysqli->query("SELECT id, porta, nome_exibicao, status FROM canais_comunicacao WHERE tipo = 'whatsapp'");
    
    if ($canais && $canais->num_rows > 0) {
        echo "   📋 Canais encontrados:\n";
        
        while ($canal = $canais->fetch_assoc()) {
            $porta = $canal['porta'];
            $canal_id = $canal['id'];
            $nome = $canal['nome_exibicao'];
            $status_atual = $canal['status'];
            
            echo "   🔍 Verificando canal $nome (porta $porta)...\n";
            
            // Verificar se a porta está funcionando
            if (isset($status_portas[$porta]) && $status_portas[$porta]['funcionando']) {
                if ($status_portas[$porta]['ready']) {
                    // Atualizar status para conectado
                    $mysqli->query("UPDATE canais_comunicacao SET status = 'conectado', data_conexao = NOW() WHERE id = $canal_id");
                    echo "   ✅ $nome: Atualizado para CONECTADO\n";
                } else {
                    // Atualizar status para pendente
                    $mysqli->query("UPDATE canais_comunicacao SET status = 'pendente' WHERE id = $canal_id");
                    echo "   ⚠️  $nome: Atualizado para PENDENTE\n";
                }
            } else {
                // Atualizar status para desconectado
                $mysqli->query("UPDATE canais_comunicacao SET status = 'desconectado' WHERE id = $canal_id");
                echo "   ❌ $nome: Atualizado para DESCONECTADO\n";
            }
        }
    } else {
        echo "   ℹ️  Nenhum canal WhatsApp encontrado no banco\n";
    }
    
} catch (Exception $e) {
    echo "   ❌ Erro no banco: " . $e->getMessage() . "\n";
    echo "   ℹ️  Continuando sem atualizar banco...\n";
}

// 3. Resumo do status
echo "\n3. 📊 RESUMO DO STATUS:\n";
foreach ($portas as $porta) {
    if (isset($status_portas[$porta])) {
        $status = $status_portas[$porta];
        if ($status['funcionando']) {
            if ($status['ready']) {
                echo "   ✅ Porta $porta: FUNCIONANDO E PRONTA\n";
            } else {
                echo "   ⚠️  Porta $porta: FUNCIONANDO MAS NÃO PRONTA\n";
            }
        } else {
            echo "   ❌ Porta $porta: NÃO FUNCIONANDO\n";
        }
    }
}

// 4. Instruções para correção manual
echo "\n4. 🔧 INSTRUÇÕES PARA CORREÇÃO MANUAL:\n";
echo "   Se as portas não estão funcionando:\n\n";
echo "   1. Acesse o VPS via SSH:\n";
echo "      ssh root@212.85.11.238\n\n";
echo "   2. Verifique se o processo está rodando:\n";
echo "      pm2 list\n\n";
echo "   3. Reinicie o serviço:\n";
echo "      pm2 restart whatsapp-multi-session\n\n";
echo "   4. Verifique recursos:\n";
echo "      top\n";
echo "      free -h\n\n";
echo "   5. Teste localmente:\n";
echo "      curl http://localhost:3000/status\n";
echo "      curl http://localhost:3001/status\n\n";

// 5. Próximos passos
echo "5. 📋 PRÓXIMOS PASSOS:\n";
echo "   • Acesse o painel de comunicação\n";
echo "   • Verifique se os status foram atualizados\n";
echo "   • Se ainda houver 'Verificando...', aguarde 2-3 minutos\n";
echo "   • Para conectar novos canais, use o botão 'Conectar'\n\n";

echo "✅ CORREÇÃO CONCLUÍDA!\n";
echo "Data/Hora: " . date('Y-m-d H:i:s') . "\n";
?> 