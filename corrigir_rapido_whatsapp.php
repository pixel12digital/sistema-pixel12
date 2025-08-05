<?php
/**
 * CORREÇÃO RÁPIDA DO WHATSAPP
 * Script simples para corrigir problemas de conectividade
 */

echo "🔧 CORREÇÃO RÁPIDA DO WHATSAPP\n";
echo "==============================\n\n";

// Configurações
$vps_ip = '212.85.11.238';
$portas = [3000, 3001];

// Função para testar conectividade
function testarConectividade($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return ['http_code' => $http_code, 'response' => $response];
}

// 1. Testar conectividade atual
echo "1. 🔍 TESTANDO CONECTIVIDADE ATUAL:\n";
$problemas = [];

foreach ($portas as $porta) {
    $url = "http://{$vps_ip}:{$porta}/status";
    $resultado = testarConectividade($url);
    
    if ($resultado['http_code'] == 200) {
        echo "   ✅ Porta $porta: OK\n";
    } else {
        echo "   ❌ Porta $porta: Problema (HTTP {$resultado['http_code']})\n";
        $problemas[] = $porta;
    }
}

// 2. Se há problemas, tentar reiniciar via SSH
if (!empty($problemas)) {
    echo "\n2. 🔄 TENTANDO REINICIAR SERVIÇOS:\n";
    
    // Comando SSH para reiniciar
    $ssh_command = "ssh -o ConnectTimeout=10 root@212.85.11.238 'pm2 restart whatsapp-multi-session'";
    $output = shell_exec($ssh_command . ' 2>&1');
    
    if ($output) {
        echo "   📋 Saída do comando:\n";
        echo "   " . str_replace("\n", "\n   ", $output) . "\n";
    } else {
        echo "   ⚠️  Não foi possível executar o comando SSH\n";
    }
    
    echo "   ⏳ Aguardando 10 segundos...\n";
    sleep(10);
    
    // 3. Testar novamente
    echo "\n3. 🔍 TESTANDO APÓS REINICIALIZAÇÃO:\n";
    foreach ($portas as $porta) {
        $url = "http://{$vps_ip}:{$porta}/status";
        $resultado = testarConectividade($url);
        
        if ($resultado['http_code'] == 200) {
            echo "   ✅ Porta $porta: Funcionando!\n";
        } else {
            echo "   ❌ Porta $porta: Ainda com problema\n";
        }
    }
} else {
    echo "\n✅ Todos os serviços estão funcionando!\n";
}

// 4. Atualizar banco de dados
echo "\n4. 🗄️ ATUALIZANDO BANCO DE DADOS:\n";

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
    
    $canais = $mysqli->query("SELECT id, porta, nome_exibicao FROM canais_comunicacao WHERE tipo = 'whatsapp'");
    
    if ($canais && $canais->num_rows > 0) {
        while ($canal = $canais->fetch_assoc()) {
            $porta = $canal['porta'];
            $canal_id = $canal['id'];
            $nome = $canal['nome_exibicao'];
            
            $url = "http://{$vps_ip}:{$porta}/status";
            $resultado = testarConectividade($url);
            
            if ($resultado['http_code'] == 200) {
                $data = json_decode($resultado['response'], true);
                $is_ready = ($data && isset($data['ready']) && $data['ready']);
                
                if ($is_ready) {
                    $mysqli->query("UPDATE canais_comunicacao SET status = 'conectado', data_conexao = NOW() WHERE id = $canal_id");
                    echo "   ✅ $nome: Conectado\n";
                } else {
                    $mysqli->query("UPDATE canais_comunicacao SET status = 'pendente' WHERE id = $canal_id");
                    echo "   ⚠️  $nome: Pendente\n";
                }
            } else {
                $mysqli->query("UPDATE canais_comunicacao SET status = 'desconectado' WHERE id = $canal_id");
                echo "   ❌ $nome: Desconectado\n";
            }
        }
    } else {
        echo "   ℹ️  Nenhum canal WhatsApp encontrado no banco\n";
    }
    
} catch (Exception $e) {
    echo "   ❌ Erro no banco: " . $e->getMessage() . "\n";
    echo "   ℹ️  Continuando sem atualizar banco...\n";
}

// 5. Instruções finais
echo "\n5. 📋 PRÓXIMOS PASSOS:\n";
echo "   • Acesse o painel de comunicação\n";
echo "   • Verifique se os status foram atualizados\n";
echo "   • Se ainda houver 'Verificando...', aguarde 2-3 minutos\n";
echo "   • Para conectar novos canais, use o botão 'Conectar'\n\n";

echo "✅ CORREÇÃO CONCLUÍDA!\n";
?> 