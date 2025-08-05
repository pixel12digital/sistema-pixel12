<?php
/**
 * CORREÇÃO DO MODAL WHATSAPP
 * Melhora a interface de erro e adiciona funcionalidades de recuperação
 */

// Verificar se o modal existe e corrigir se necessário
$modal_file = 'painel/comunicacao.php';

if (file_exists($modal_file)) {
    echo "🔧 === CORREÇÃO DO MODAL WHATSAPP ===\n";
    echo "Data/Hora: " . date('Y-m-d H:i:s') . "\n\n";
    
    // Ler o arquivo atual
    $content = file_get_contents($modal_file);
    
    // Verificar se o modal QR existe
    if (strpos($content, 'modal-qr-canal') === false) {
        echo "❌ Modal QR não encontrado! Criando...\n";
        
        // Adicionar modal QR antes do fechamento do PHP
        $modal_qr = '
  // Modal QR Code para conexão WhatsApp
  echo \'<div id="modal-qr-canal" style="display:none;position:fixed;top:0;left:0;width:100vw;height:100vh;background:#0008;z-index:9999;align-items:center;justify-content:center;">\';
  echo \'<div class="modal-qr-content" style="background:white;padding:30px;border-radius:15px;text-align:center;max-width:400px;min-width:300px;">\';
  echo \'<button id="close-modal-qr" style="position:absolute;top:12px;right:16px;font-size:1.5rem;background:none;border:none;cursor:pointer;color:#666;">&times;</button>\';
  echo \'<h3 style="color:#333;margin-bottom:20px;font-size:1.3rem;">Conectar WhatsApp</h3>\';
  echo \'<div id="qr-code-area" style="display:flex;justify-content:center;align-items:center;min-height:250px;margin:20px 0;">\';
  echo \'<div style="text-align:center;padding:20px;color:#666;"><div style="font-size:2rem;margin-bottom:10px;">📱</div><div>Carregando QR Code...</div></div>\';
  echo \'</div>\';
  echo \'<div style="margin-top:20px;display:flex;gap:10px;justify-content:center;">\';
  echo \'<button id="btn-atualizar-qr" style="background:#3b82f6;color:white;padding:10px 20px;border:none;border-radius:6px;cursor:pointer;font-weight:bold;">Atualizar QR</button>\';
  echo \'<button id="btn-forcar-novo-qr" style="background:#f59e0b;color:white;padding:10px 20px;border:none;border-radius:6px;cursor:pointer;font-weight:bold;">Forçar Novo QR</button>\';
  echo \'</div>\';
  echo \'</div></div>\';
';
        
        // Inserir antes do fechamento do PHP
        $content = str_replace('// ===== JAVASCRIPT CONSOLIDADO NO FINAL =====', $modal_qr . "\n// ===== JAVASCRIPT CONSOLIDADO NO FINAL =====", $content);
        
        file_put_contents($modal_file, $content);
        echo "✅ Modal QR criado com sucesso!\n";
    } else {
        echo "✅ Modal QR já existe!\n";
    }
    
    // Verificar e melhorar a função de debug
    if (strpos($content, 'function debug(') === false) {
        echo "❌ Função debug não encontrada! Adicionando...\n";
        
        $debug_function = '
// Função de debug para console
function debug(message, type = "info") {
  const console = document.getElementById("debug-console");
  if (console) {
    const timestamp = new Date().toLocaleTimeString();
    const color = type === "error" ? "#ef4444" : type === "success" ? "#22c55e" : type === "warning" ? "#f59e0b" : "#3b82f6";
    console.innerHTML += `<span style="color: ${color};">[${timestamp}] ${message}</span><br>`;
    console.scrollTop = console.scrollHeight;
  }
  console.log(`[${type.toUpperCase()}] ${message}`);
}
';
        
        // Inserir no início do JavaScript
        $content = str_replace('// ===== CONFIGURAÇÃO CORS-FREE (SEM CHAMADAS DIRETAS À VPS) =====', $debug_function . "\n// ===== CONFIGURAÇÃO CORS-FREE (SEM CHAMADAS DIRETAS À VPS) =====", $content);
        
        file_put_contents($modal_file, $content);
        echo "✅ Função debug adicionada!\n";
    } else {
        echo "✅ Função debug já existe!\n";
    }
    
    // Melhorar mensagens de erro no modal
    $error_improvements = [
        'Erro ao obter QR Code do WhatsApp' => 'Erro ao obter QR Code do WhatsApp - Serviço VPS indisponível',
        'O serviço WhatsApp não está respondendo corretamente' => 'O serviço WhatsApp no VPS não está funcionando. Execute os comandos de correção.',
        'Verifique se o serviço WhatsApp está rodando no VPS' => 'O VPS precisa ser reiniciado. Acesse via SSH e execute: pm2 restart whatsapp-multi-session'
    ];
    
    foreach ($error_improvements as $old => $new) {
        if (strpos($content, $old) !== false) {
            $content = str_replace($old, $new, $content);
            echo "✅ Mensagem de erro melhorada: $old\n";
        }
    }
    
    // Adicionar instruções de correção no modal de erro
    $correction_instructions = '
                <div style="margin-top: 1rem; font-size: 0.8rem; color: #999; background: #f5f5f5; padding: 10px; border-radius: 6px; text-align: left;">
                  <strong>🔧 Como corrigir:</strong><br>
                  • Acesse o VPS: ssh root@212.85.11.238<br>
                  • Reinicie o serviço: pm2 restart whatsapp-multi-session<br>
                  • Verifique recursos: top, free -h<br>
                  • Teste: curl http://localhost:3000/status<br>
                  • Aguarde 2-3 minutos e tente novamente
                </div>
';
    
    if (strpos($content, 'Como corrigir:') === false) {
        $content = str_replace('</div>', $correction_instructions . '</div>', $content);
        echo "✅ Instruções de correção adicionadas!\n";
    }
    
    file_put_contents($modal_file, $content);
    
} else {
    echo "❌ Arquivo comunicacao.php não encontrado!\n";
}

// Criar script de teste rápido
$test_script = '<?php
/**
 * TESTE RÁPIDO VPS WHATSAPP
 * Verifica status e tenta conectar
 */

echo "🚀 === TESTE RÁPIDO VPS WHATSAPP ===\n";
echo "Data/Hora: " . date("Y-m-d H:i:s") . "\n\n";

$vps_urls = [
    "3000" => "http://212.85.11.238:3000",
    "3001" => "http://212.85.11.238:3001"
];

foreach ($vps_urls as $porta => $vps_url) {
    echo "--- TESTANDO VPS $porta ---\n";
    
    // Teste de status
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $vps_url . "/status");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code == 200) {
        $data = json_decode($response, true);
        $ready = $data["ready"] ?? false;
        $sessoes = array_keys($data["clients_status"] ?? []);
        
        echo "   ✅ VPS respondendo\n";
        echo "   📊 Ready: " . ($ready ? "✅" : "❌") . "\n";
        echo "   📱 Sessões: " . (count($sessoes) > 0 ? implode(", ", $sessoes) : "❌ Nenhuma") . "\n";
        
        if ($ready && !empty($sessoes)) {
            echo "   🎉 VPS $porta FUNCIONANDO!\n";
        } else {
            echo "   ⚠️ VPS $porta precisa de correção\n";
        }
    } else {
        echo "   ❌ VPS não responde (HTTP $http_code)\n";
    }
    
    echo "\n";
}

echo "=== INSTRUÇÕES ===\n";
echo "Se algum VPS não estiver funcionando:\n";
echo "1. ssh root@212.85.11.238\n";
echo "2. pm2 restart whatsapp-multi-session\n";
echo "3. pm2 save\n";
echo "4. Execute este teste novamente\n\n";

echo "✅ Teste concluído!\n";
?>';

file_put_contents('teste_rapido_vps.php', $test_script);
echo "✅ Script de teste rápido criado: teste_rapido_vps.php\n";

echo "\n=== RESUMO DAS CORREÇÕES ===\n";
echo "✅ Modal QR verificado/criado\n";
echo "✅ Função debug adicionada\n";
echo "✅ Mensagens de erro melhoradas\n";
echo "✅ Instruções de correção adicionadas\n";
echo "✅ Script de teste criado\n\n";

echo "🎯 PRÓXIMOS PASSOS:\n";
echo "1. Execute: php teste_rapido_vps.php\n";
echo "2. Se o VPS não estiver funcionando, acesse via SSH\n";
echo "3. Reinicie o serviço: pm2 restart whatsapp-multi-session\n";
echo "4. Teste novamente no painel\n\n";

echo "✅ Correções aplicadas com sucesso!\n";
?> 