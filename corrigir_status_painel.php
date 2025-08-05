<?php
/**
 * CORREÇÃO DO STATUS NO PAINEL
 * Script para atualizar status dos canais no banco e forçar atualização
 */

echo "🔧 CORREÇÃO DO STATUS NO PAINEL\n";
echo "===============================\n\n";

// Incluir configuração do banco
try {
    if (file_exists('config.php')) {
        require_once 'config.php';
    } elseif (file_exists('painel/db.php')) {
        require_once 'painel/db.php';
    } else {
        throw new Exception('Arquivo de configuração do banco não encontrado');
    }
    
    if (!isset($mysqli) || !$mysqli) {
        throw new Exception('Conexão com banco de dados não estabelecida');
    }
    
    echo "✅ Conexão com banco estabelecida\n\n";
    
    // Função para testar status VPS
    function testarStatusVPS($porta) {
        $url = "http://212.85.11.238:{$porta}/status";
        $context = stream_context_create([
            'http' => [
                'timeout' => 5,
                'user_agent' => 'WhatsApp-Status-Fix/1.0'
            ]
        ]);
        
        $response = @file_get_contents($url, false, $context);
        if ($response === false) {
            return ['funcionando' => false, 'ready' => false];
        }
        
        $data = json_decode($response, true);
        if (!$data) {
            return ['funcionando' => false, 'ready' => false];
        }
        
        return [
            'funcionando' => true,
            'ready' => isset($data['ready']) ? $data['ready'] : false,
            'status' => isset($data['status']) ? $data['status'] : 'unknown',
            'data' => $data
        ];
    }
    
    echo "1. 📊 TESTANDO STATUS ATUAL DO VPS:\n";
    
    $statusPortas = [];
    foreach ([3000, 3001] as $porta) {
        echo "   📡 Testando porta $porta...\n";
        $status = testarStatusVPS($porta);
        $statusPortas[$porta] = $status;
        
        if ($status['funcionando']) {
            $ready = $status['ready'] ? 'SIM' : 'NÃO';
            echo "   ✅ Porta $porta: FUNCIONANDO - Ready: $ready\n";
        } else {
            echo "   ❌ Porta $porta: NÃO FUNCIONANDO\n";
        }
    }
    echo "\n";
    
    echo "2. 🗄️ ATUALIZANDO BANCO DE DADOS:\n";
    
    // Buscar canais existentes
    $canais = $mysqli->query("SELECT id, porta, nome_exibicao, status, identificador FROM canais_comunicacao WHERE tipo = 'whatsapp'");
    
    if ($canais && $canais->num_rows > 0) {
        while ($canal = $canais->fetch_assoc()) {
            $porta = $canal['porta'];
            $canal_id = $canal['id'];
            $nome = $canal['nome_exibicao'];
            $status_atual = $canal['status'];
            
            echo "   🔍 Canal: $nome (ID: $canal_id, Porta: $porta)\n";
            echo "      Status atual: $status_atual\n";
            
            if (isset($statusPortas[$porta]) && $statusPortas[$porta]['funcionando']) {
                if ($statusPortas[$porta]['ready']) {
                    // Canal conectado
                    $novo_status = 'conectado';
                    $mysqli->query("UPDATE canais_comunicacao SET status = 'conectado', data_conexao = NOW() WHERE id = $canal_id");
                    echo "      ✅ Atualizado para: CONECTADO\n";
                } else {
                    // Canal aguardando QR code
                    $novo_status = 'pendente';
                    $mysqli->query("UPDATE canais_comunicacao SET status = 'pendente' WHERE id = $canal_id");
                    echo "      ⚠️  Atualizado para: PENDENTE (aguardando QR)\n";
                }
            } else {
                // Canal desconectado
                $novo_status = 'desconectado';
                $mysqli->query("UPDATE canais_comunicacao SET status = 'desconectado' WHERE id = $canal_id");
                echo "      ❌ Atualizado para: DESCONECTADO\n";
            }
            echo "\n";
        }
    } else {
        echo "   ℹ️  Nenhum canal WhatsApp encontrado\n";
    }
    
    echo "3. 🔄 LIMPANDO CACHE DO SISTEMA:\n";
    
    // Limpar possíveis arquivos de cache
    $cacheFiles = [
        'cache/status_canais.json',
        'cache/whatsapp_status.cache',
        'painel/cache/status.cache'
    ];
    
    foreach ($cacheFiles as $file) {
        if (file_exists($file)) {
            unlink($file);
            echo "   🗑️  Cache removido: $file\n";
        }
    }
    
    echo "   ✅ Cache limpo\n\n";
    
    echo "4. 📱 FORÇANDO ATUALIZAÇÃO DO PAINEL:\n";
    
    // Criar arquivo de sinal para forçar atualização
    file_put_contents('painel/cache/force_refresh.flag', time());
    echo "   🚀 Sinal de atualização criado\n";
    
    // Verificar status final
    echo "\n5. 📊 RESUMO FINAL:\n";
    $resumo = $mysqli->query("SELECT status, COUNT(*) as total FROM canais_comunicacao WHERE tipo = 'whatsapp' GROUP BY status");
    
    while ($row = $resumo->fetch_assoc()) {
        $status = $row['status'];
        $total = $row['total'];
        $icon = '';
        
        switch ($status) {
            case 'conectado':
                $icon = '✅';
                break;
            case 'pendente':
                $icon = '⚠️';
                break;
            case 'desconectado':
                $icon = '❌';
                break;
            default:
                $icon = '❓';
        }
        
        echo "   $icon $status: $total canais\n";
    }
    
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
}

echo "\n6. 📋 PRÓXIMOS PASSOS:\n";
echo "   1. Recarregue a página do painel (F5)\n";
echo "   2. Aguarde 30 segundos para atualização automática\n";
echo "   3. Se ainda mostrar 'Verificando...', execute este script novamente\n";
echo "   4. Para canais PENDENTE, clique em 'Conectar' para gerar QR Code\n\n";

echo "✅ CORREÇÃO CONCLUÍDA!\n";
echo "Data/Hora: " . date('Y-m-d H:i:s') . "\n";
?> 