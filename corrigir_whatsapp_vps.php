<?php
/**
 * CORREÇÃO COMPLETA DO WHATSAPP VPS
 * Script para corrigir problemas de conectividade dos canais WhatsApp
 * VPS: 212.85.11.238
 */

echo "=== 🔧 CORREÇÃO COMPLETA DO WHATSAPP VPS ===\n";
echo "Data/Hora: " . date('Y-m-d H:i:s') . "\n";
echo "VPS: 212.85.11.238\n\n";

// Configurações
$vps_ip = '212.85.11.238';
$portas = [3000, 3001];
$timeout = 10;

// Função para fazer requisição HTTP
function fazerRequisicao($url, $timeout = 10) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_USERAGENT, 'WhatsApp-Correction/1.0');
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    $curl_info = curl_getinfo($ch);
    curl_close($ch);
    
    return [
        'response' => $response,
        'http_code' => $http_code,
        'curl_error' => $curl_error,
        'curl_info' => $curl_info
    ];
}

// Função para executar comando SSH no VPS
function executarComandoSSH($comando) {
    $ssh_command = "ssh -o ConnectTimeout=10 -o StrictHostKeyChecking=no root@212.85.11.238 '$comando'";
    $output = shell_exec($ssh_command . ' 2>&1');
    return $output;
}

// ===== 1. VERIFICAÇÃO INICIAL DOS SERVIÇOS =====
echo "1. 🔍 VERIFICAÇÃO INICIAL DOS SERVIÇOS:\n";

foreach ($portas as $porta) {
    $url = "http://{$vps_ip}:{$porta}/status";
    echo "   📡 Testando porta $porta ($url)...\n";
    
    $resultado = fazerRequisicao($url, $timeout);
    
    if ($resultado['http_code'] == 200) {
        echo "   ✅ Porta $porta: Respondendo (HTTP {$resultado['http_code']})\n";
        
        // Tentar fazer parse da resposta
        $data = json_decode($resultado['response'], true);
        if ($data) {
            $ready = isset($data['ready']) ? $data['ready'] : 'N/A';
            $status = isset($data['status']) ? $data['status'] : 'N/A';
            echo "   📊 Ready: " . ($ready ? 'true' : 'false') . "\n";
            echo "   📊 Status: $status\n";
            
            if (isset($data['clients_status'])) {
                $sessions = array_keys($data['clients_status']);
                echo "   📱 Sessões: " . (empty($sessions) ? 'nenhuma' : implode(', ', $sessions)) . "\n";
            }
        } else {
            echo "   ⚠️  Resposta não é JSON válido\n";
        }
    } else {
        echo "   ❌ Porta $porta: Não respondendo (HTTP {$resultado['http_code']})\n";
        if ($resultado['curl_error']) {
            echo "   🔍 Erro: {$resultado['curl_error']}\n";
        }
    }
    echo "\n";
}

// ===== 2. VERIFICAÇÃO DO PM2 NO VPS =====
echo "2. 🔧 VERIFICAÇÃO DO PM2 NO VPS:\n";

echo "   📋 Verificando processos PM2...\n";
$pm2_status = executarComandoSSH('pm2 list');
echo $pm2_status . "\n";

// Verificar se o processo whatsapp-multi-session está rodando
if (strpos($pm2_status, 'whatsapp-multi-session') !== false) {
    echo "   ✅ Processo whatsapp-multi-session encontrado\n";
} else {
    echo "   ❌ Processo whatsapp-multi-session não encontrado\n";
}

// ===== 3. VERIFICAÇÃO DE RECURSOS DO VPS =====
echo "3. 💻 VERIFICAÇÃO DE RECURSOS DO VPS:\n";

echo "   📊 CPU e Memória:\n";
$top_output = executarComandoSSH('top -bn1 | head -20');
echo $top_output . "\n";

echo "   💾 Uso de memória:\n";
$memory_output = executarComandoSSH('free -h');
echo $memory_output . "\n";

echo "   💿 Espaço em disco:\n";
$disk_output = executarComandoSSH('df -h');
echo $disk_output . "\n";

// ===== 4. REINICIALIZAÇÃO DOS SERVIÇOS =====
echo "4. 🔄 REINICIALIZAÇÃO DOS SERVIÇOS:\n";

echo "   🔄 Reiniciando PM2 whatsapp-multi-session...\n";
$restart_output = executarComandoSSH('pm2 restart whatsapp-multi-session');
echo $restart_output . "\n";

echo "   ⏳ Aguardando 5 segundos para estabilização...\n";
sleep(5);

// ===== 5. VERIFICAÇÃO PÓS-REINICIALIZAÇÃO =====
echo "5. ✅ VERIFICAÇÃO PÓS-REINICIALIZAÇÃO:\n";

foreach ($portas as $porta) {
    $url = "http://{$vps_ip}:{$porta}/status";
    echo "   📡 Testando porta $porta novamente...\n";
    
    $resultado = fazerRequisicao($url, $timeout);
    
    if ($resultado['http_code'] == 200) {
        echo "   ✅ Porta $porta: Funcionando (HTTP {$resultado['http_code']})\n";
        
        $data = json_decode($resultado['response'], true);
        if ($data && isset($data['ready'])) {
            if ($data['ready']) {
                echo "   🎉 Serviço pronto e funcionando!\n";
            } else {
                echo "   ⚠️  Serviço respondendo mas não está pronto\n";
            }
        }
    } else {
        echo "   ❌ Porta $porta: Ainda não respondendo (HTTP {$resultado['http_code']})\n";
    }
    echo "\n";
}

// ===== 6. ATUALIZAÇÃO DO BANCO DE DADOS =====
echo "6. 🗄️ ATUALIZAÇÃO DO BANCO DE DADOS:\n";

// Conectar ao banco de dados
require_once 'config.php';

try {
    // Verificar canais existentes
    $canais = $mysqli->query("SELECT id, porta, status, nome_exibicao FROM canais_comunicacao WHERE tipo = 'whatsapp'");
    
    if ($canais && $canais->num_rows > 0) {
        echo "   📋 Canais encontrados:\n";
        
        while ($canal = $canais->fetch_assoc()) {
            $porta = $canal['porta'];
            $canal_id = $canal['id'];
            $nome = $canal['nome_exibicao'];
            
            echo "   🔍 Verificando canal $nome (porta $porta)...\n";
            
            // Testar conectividade
            $url = "http://{$vps_ip}:{$porta}/status";
            $resultado = fazerRequisicao($url, 5);
            
            if ($resultado['http_code'] == 200) {
                $data = json_decode($resultado['response'], true);
                $is_ready = ($data && isset($data['ready']) && $data['ready']);
                
                if ($is_ready) {
                    // Atualizar status para conectado
                    $mysqli->query("UPDATE canais_comunicacao SET status = 'conectado', data_conexao = NOW() WHERE id = $canal_id");
                    echo "   ✅ Canal $nome: Atualizado para CONECTADO\n";
                } else {
                    // Atualizar status para pendente
                    $mysqli->query("UPDATE canais_comunicacao SET status = 'pendente' WHERE id = $canal_id");
                    echo "   ⚠️  Canal $nome: Atualizado para PENDENTE\n";
                }
            } else {
                // Atualizar status para desconectado
                $mysqli->query("UPDATE canais_comunicacao SET status = 'desconectado' WHERE id = $canal_id");
                echo "   ❌ Canal $nome: Atualizado para DESCONECTADO\n";
            }
        }
    } else {
        echo "   ℹ️  Nenhum canal WhatsApp encontrado no banco\n";
    }
    
} catch (Exception $e) {
    echo "   ❌ Erro ao acessar banco de dados: " . $e->getMessage() . "\n";
}

// ===== 7. TESTE FINAL DE CONECTIVIDADE =====
echo "7. 🧪 TESTE FINAL DE CONECTIVIDADE:\n";

foreach ($portas as $porta) {
    $url = "http://{$vps_ip}:{$porta}/status";
    echo "   📡 Teste final porta $porta...\n";
    
    $resultado = fazerRequisicao($url, $timeout);
    
    if ($resultado['http_code'] == 200) {
        echo "   ✅ SUCESSO: Porta $porta funcionando corretamente\n";
        
        // Teste adicional de QR code
        $qr_url = "http://{$vps_ip}:{$porta}/session/default/qr";
        $qr_resultado = fazerRequisicao($qr_url, 5);
        
        if ($qr_resultado['http_code'] == 200) {
            echo "   📱 QR Code disponível na porta $porta\n";
        } else {
            echo "   ⚠️  QR Code não disponível na porta $porta\n";
        }
    } else {
        echo "   ❌ FALHA: Porta $porta ainda não está funcionando\n";
    }
    echo "\n";
}

// ===== 8. INSTRUÇÕES FINAIS =====
echo "8. 📋 INSTRUÇÕES FINAIS:\n";
echo "   ✅ Correção concluída!\n\n";
echo "   🔧 Se ainda houver problemas:\n";
echo "   1. Acesse o VPS: ssh root@212.85.11.238\n";
echo "   2. Verifique logs: pm2 logs whatsapp-multi-session\n";
echo "   3. Reinicie manualmente: pm2 restart whatsapp-multi-session\n";
echo "   4. Verifique recursos: top, free -h\n";
echo "   5. Teste: curl http://localhost:3000/status\n\n";

echo "   📱 Para conectar novos canais WhatsApp:\n";
echo "   1. Acesse o painel de comunicação\n";
echo "   2. Clique em 'Cadastrar Canal'\n";
echo "   3. Configure a porta (3000 ou 3001)\n";
echo "   4. Clique em 'Conectar' e escaneie o QR Code\n\n";

echo "=== ✅ CORREÇÃO CONCLUÍDA ===\n";
echo "Data/Hora: " . date('Y-m-d H:i:s') . "\n";
?> 