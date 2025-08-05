<?php
/**
 * ATUALIZAÇÃO IMEDIATA DO STATUS
 * Execute: php atualizar_agora.php
 */

echo "🔧 ATUALIZANDO STATUS AGORA\n";
echo "==========================\n\n";

// Conectar ao banco - usando configuração de produção
$host = "srv1607.hstgr.io";
$user = "u342734079_revendaweb";
$pass = "Los@ngo#081081";
$db = "u342734079_revendaweb";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ Conectado ao banco\n\n";
    
    // Função para testar VPS
    function testarVPS($porta) {
        $url = "http://212.85.11.238:{$porta}/status";
        $context = stream_context_create([
            'http' => [
                'timeout' => 3,
                'user_agent' => 'Status-Update/1.0'
            ]
        ]);
        
        $response = @file_get_contents($url, false, $context);
        if ($response === false) return null; // VPS não responde
        
        $data = json_decode($response, true);
        if (!$data) return null;
        
        return isset($data['ready']) ? $data['ready'] : false;
    }
    
    // Buscar canais WhatsApp
    $stmt = $pdo->query("SELECT id, porta, nome_exibicao FROM canais_comunicacao WHERE tipo = 'whatsapp'");
    $canais = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($canais)) {
        echo "❌ Nenhum canal encontrado\n";
        exit(1);
    }
    
    echo "📋 Canais encontrados: " . count($canais) . "\n\n";
    
    foreach ($canais as $canal) {
        $id = $canal['id'];
        $porta = $canal['porta'];
        $nome = $canal['nome_exibicao'];
        
        echo "🔍 Testando $nome (porta $porta)...\n";
        
        $isReady = testarVPS($porta);
        
        if ($isReady === true) {
            // Conectado
            $pdo->exec("UPDATE canais_comunicacao SET status = 'conectado', data_conexao = NOW() WHERE id = $id");
            echo "   ✅ CONECTADO\n";
        } elseif ($isReady === false) {
            // Pendente (VPS responde mas não ready)
            $pdo->exec("UPDATE canais_comunicacao SET status = 'pendente' WHERE id = $id");
            echo "   ⚠️  PENDENTE\n";
        } else {
            // Desconectado (VPS não responde)
            $pdo->exec("UPDATE canais_comunicacao SET status = 'desconectado' WHERE id = $id");
            echo "   ❌ DESCONECTADO\n";
        }
    }
    
    echo "\n📊 RESUMO FINAL:\n";
    
    $stmt = $pdo->query("SELECT status, COUNT(*) as total FROM canais_comunicacao WHERE tipo = 'whatsapp' GROUP BY status");
    $resumo = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($resumo as $row) {
        $status = $row['status'];
        $total = $row['total'];
        
        $icon = '';
        switch ($status) {
            case 'conectado': $icon = '✅'; break;
            case 'pendente': $icon = '⚠️'; break;
            case 'desconectado': $icon = '❌'; break;
            default: $icon = '❓';
        }
        
        echo "   $icon $status: $total canais\n";
    }
    
    echo "\n✅ ATUALIZAÇÃO CONCLUÍDA!\n";
    echo "📱 Recarregue o painel para ver as mudanças\n";
    
} catch (Exception $e) {
    echo "❌ ERRO: " . $e->getMessage() . "\n";
    exit(1);
}
?> 