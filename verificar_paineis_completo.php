<?php
echo "🔍 VERIFICAÇÃO COMPLETA DOS PAINÉIS\n";
echo "====================================\n\n";

$urls_promissoras = [
    "http://212.85.11.238:8080",
    "http://212.85.11.238/admin", 
    "http://212.85.11.238/dashboard"
];

foreach ($urls_promissoras as $url) {
    echo "🌐 Analisando: $url\n";
    echo "----------------------------\n";
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $redirect_url = curl_getinfo($ch, CURLINFO_REDIRECT_URL);
    $effective_url = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
    curl_close($ch);
    
    echo "HTTP Code: $http_code\n";
    if ($redirect_url) echo "Redirect: $redirect_url\n";
    if ($effective_url && $effective_url != $url) echo "URL Final: $effective_url\n";
    
    if ($response) {
        $content = strtolower($response);
        
        // Procurar por palavras-chave de painéis admin
        $admin_keywords = ['login', 'dashboard', 'admin', 'whatsapp', 'webhook', 'api', 'settings', 'configuration'];
        $found_keywords = [];
        
        foreach ($admin_keywords as $keyword) {
            if (strpos($content, $keyword) !== false) {
                $found_keywords[] = $keyword;
            }
        }
        
        if (!empty($found_keywords)) {
            echo "✅ PALAVRAS-CHAVE ENCONTRADAS: " . implode(', ', $found_keywords) . "\n";
            echo "🎯 ESTE PARECE SER UM PAINEL ADMINISTRATIVO!\n";
        }
        
        // Procurar por título da página
        if (preg_match('/<title[^>]*>(.*?)<\/title>/i', $response, $matches)) {
            echo "📄 Título: " . trim($matches[1]) . "\n";
        }
        
        echo "📏 Tamanho da resposta: " . strlen($response) . " bytes\n";
    }
    
    echo "\n";
}

echo "🎯 PRÓXIMOS PASSOS:\n";
echo "===================\n\n";

echo "1. 🌐 TESTE MANUALMENTE NO BROWSER:\n";
echo "   • http://212.85.11.238:8080\n";
echo "   • http://212.85.11.238/admin\n";
echo "   • http://212.85.11.238/dashboard\n\n";

echo "2. 🔑 SE PEDIR LOGIN, TENTE:\n";
echo "   • admin / admin\n";
echo "   • admin / password\n";
echo "   • whatsapp / whatsapp\n\n";

echo "3. 📱 PROCURE POR SEÇÕES:\n";
echo "   • 'Webhook'\n";
echo "   • 'API Settings'\n";
echo "   • 'Configurações'\n";
echo "   • 'Sessões'\n\n";

echo "4. ⚙️ CONFIGURE O WEBHOOK:\n";
echo "   URL: https://app.pixel12digital.com.br/painel/receber_mensagem_ana_local.php\n";
echo "   Método: POST\n";
echo "   Content-Type: application/json\n\n";

echo "💡 DICA: Se não encontrar painel web, o sistema pode usar:\n";
echo "   • Configuração via arquivo .env\n";
echo "   • API direta (sem interface web)\n";
echo "   • Painel em outro servidor\n";
?> 