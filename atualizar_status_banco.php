<?php
/**
 * ATUALIZAR STATUS DO BANCO - WHATSAPP
 * Script para atualizar o status dos canais no banco de dados
 */

echo "🗄️ ATUALIZANDO STATUS DO BANCO - WHATSAPP\n";
echo "==========================================\n\n";

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
    
    echo "✅ Conexão com banco estabelecida\n\n";
    
    // Buscar canais WhatsApp
    $canais = $mysqli->query("SELECT id, porta, nome_exibicao, status FROM canais_comunicacao WHERE tipo = 'whatsapp'");
    
    if ($canais && $canais->num_rows > 0) {
        echo "📋 Canais encontrados:\n";
        echo "=====================\n\n";
        
        while ($canal = $canais->fetch_assoc()) {
            $porta = $canal['porta'];
            $canal_id = $canal['id'];
            $nome = $canal['nome_exibicao'];
            $status_atual = $canal['status'];
            
            echo "🔍 Canal: $nome (Porta: $porta)\n";
            echo "   Status atual: $status_atual\n";
            
            // Testar conectividade da porta
            $url = "http://212.85.11.238:{$porta}/status";
            
            $context = stream_context_create([
                'http' => [
                    'timeout' => 5,
                    'user_agent' => 'WhatsApp-Status/1.0'
                ]
            ]);
            
            $response = @file_get_contents($url, false, $context);
            
            if ($response !== false) {
                $data = json_decode($response, true);
                $is_ready = ($data && isset($data['ready']) && $data['ready']);
                
                if ($is_ready) {
                    // Atualizar para conectado
                    $mysqli->query("UPDATE canais_comunicacao SET status = 'conectado', data_conexao = NOW() WHERE id = $canal_id");
                    echo "   ✅ Atualizado para: CONECTADO\n";
                } else {
                    // Atualizar para pendente (aguardando QR code)
                    $mysqli->query("UPDATE canais_comunicacao SET status = 'pendente' WHERE id = $canal_id");
                    echo "   ⚠️  Atualizado para: PENDENTE (aguardando QR code)\n";
                }
            } else {
                // Atualizar para desconectado
                $mysqli->query("UPDATE canais_comunicacao SET status = 'desconectado' WHERE id = $canal_id");
                echo "   ❌ Atualizado para: DESCONECTADO\n";
            }
            echo "\n";
        }
        
        echo "✅ Atualização concluída!\n\n";
        
        // Mostrar resumo final
        echo "📊 RESUMO FINAL:\n";
        echo "================\n";
        
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
        
    } else {
        echo "ℹ️  Nenhum canal WhatsApp encontrado no banco\n";
    }
    
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
}

echo "\n📋 PRÓXIMOS PASSOS:\n";
echo "==================\n";
echo "1. Acesse o painel de comunicação\n";
echo "2. Verifique se os status foram atualizados\n";
echo "3. Para canais 'PENDENTE', clique em 'Conectar' e escaneie o QR Code\n";
echo "4. Para canais 'DESCONECTADO', verifique o VPS primeiro\n\n";

echo "✅ PROCESSO CONCLUÍDO!\n";
echo "Data/Hora: " . date('Y-m-d H:i:s') . "\n";
?> 