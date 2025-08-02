<?php
echo "🔍 VERIFICANDO STATUS DOS CANAIS ANTES DE CONFIGURAR\n";
echo "===================================================\n\n";

$vps_ip = '212.85.11.238';

echo "📡 VPS: $vps_ip\n\n";

// 1. Verificar status dos canais
echo "🔍 VERIFICANDO CANAIS:\n";
echo "----------------------\n";

$canais = [
    ['nome' => 'Canal 3000 (Default)', 'porta' => '3000'],
    ['nome' => 'Canal 3001 (Comercial)', 'porta' => '3001']
];

$status_canais = [];

foreach ($canais as $canal) {
    echo "📱 {$canal['nome']}:\n";
    
    // Verificar status
    $status_check = curl_init("http://$vps_ip:{$canal['porta']}/status");
    curl_setopt($status_check, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($status_check, CURLOPT_TIMEOUT, 5);
    
    $status_response = curl_exec($status_check);
    $status_code = curl_getinfo($status_check, CURLINFO_HTTP_CODE);
    curl_close($status_check);
    
    if ($status_code === 200) {
        echo "   ✅ Online (HTTP $status_code)\n";
        
        // Verificar sessões
        $sessions_check = curl_init("http://$vps_ip:{$canal['porta']}/sessions");
        curl_setopt($sessions_check, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($sessions_check, CURLOPT_TIMEOUT, 5);
        
        $sessions_response = curl_exec($sessions_check);
        $sessions_code = curl_getinfo($sessions_check, CURLINFO_HTTP_CODE);
        curl_close($sessions_check);
        
        if ($sessions_code === 200) {
            $sessions_data = json_decode($sessions_response, true);
            if (is_array($sessions_data) && count($sessions_data) > 0) {
                $conectadas = 0;
                foreach ($sessions_data as $session) {
                    if (isset($session['hasClient']) && $session['hasClient']) {
                        $conectadas++;
                    }
                }
                echo "   📱 Sessões: " . count($sessions_data) . " total, $conectadas conectadas\n";
                $status_canais[$canal['porta']] = 'ok';
            } else {
                echo "   ⚠️ Sem sessões ativas\n";
                $status_canais[$canal['porta']] = 'sem_sessoes';
            }
        } else {
            echo "   ❌ Erro ao verificar sessões\n";
            $status_canais[$canal['porta']] = 'erro_sessoes';
        }
    } else {
        echo "   ❌ Offline (HTTP $status_code)\n";
        $status_canais[$canal['porta']] = 'offline';
    }
    
    echo "\n";
}

// 2. Verificar webhook atual
echo "🔍 VERIFICANDO WEBHOOK ATUAL:\n";
echo "-----------------------------\n";

$webhook_check = curl_init("http://$vps_ip:3000/webhook/status");
curl_setopt($webhook_check, CURLOPT_RETURNTRANSFER, true);
curl_setopt($webhook_check, CURLOPT_TIMEOUT, 5);

$webhook_response = curl_exec($webhook_check);
$webhook_code = curl_getinfo($webhook_check, CURLINFO_HTTP_CODE);
curl_close($webhook_check);

if ($webhook_code === 200) {
    echo "✅ Webhook status disponível\n";
    echo "Configuração atual: " . substr($webhook_response, 0, 200) . "\n\n";
} else {
    echo "⚠️ Não conseguiu verificar webhook atual\n\n";
}

// 3. Resumo e recomendações
echo "📊 RESUMO DO STATUS:\n";
echo "====================\n";

$canais_ok = 0;
foreach ($status_canais as $porta => $status) {
    if ($status === 'ok') {
        $canais_ok++;
        echo "✅ Canal $porta: Funcionando perfeitamente\n";
    } else {
        echo "⚠️ Canal $porta: $status\n";
    }
}

echo "\n🎯 RECOMENDAÇÃO:\n";
if ($canais_ok >= 1) {
    echo "✅ SEGURO PROSSEGUIR\n";
    echo "• Pelo menos $canais_ok canal(is) funcionando\n";
    echo "• Configuração de webhook é reversível\n";
    echo "• Não afeta funcionamento dos canais\n\n";
    
    echo "🔧 OPÇÕES SEGURAS:\n";
    echo "1. Configurar webhook agora (recomendado)\n";
    echo "2. Fazer backup da configuração atual primeiro\n";
    echo "3. Testar em horário de menor movimento\n\n";
    
    echo "Deseja prosseguir? (y/n): ";
    
} else {
    echo "⚠️ AGUARDAR\n";
    echo "• Nenhum canal totalmente funcional\n";
    echo "• Recomendo verificar canais primeiro\n";
    echo "• Depois configurar webhook\n\n";
    
    echo "💡 PASSOS RECOMENDADOS:\n";
    echo "1. Verificar PM2: pm2 status\n";
    echo "2. Reiniciar se necessário: pm2 restart all\n";
    echo "3. Aguardar canais conectarem\n";
    echo "4. Então configurar webhook\n";
}
?> 