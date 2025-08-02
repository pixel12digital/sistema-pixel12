<?php
echo "🔍 PROCURANDO PAINEL DO WHATSAPP API\n";
echo "=====================================\n\n";

$base_ip = '212.85.11.238';
$possiveis_urls = [
    "http://$base_ip:3000",
    "http://$base_ip:3001", 
    "http://$base_ip:8080",
    "http://$base_ip:9000",
    "http://$base_ip:5000",
    "http://$base_ip/admin",
    "http://$base_ip/dashboard",
    "http://$base_ip/manager",
    "http://$base_ip/panel",
    "http://$base_ip:3000/admin",
    "http://$base_ip:3000/dashboard",
    "http://$base_ip:3000/manager"
];

foreach ($possiveis_urls as $url) {
    echo "🌐 Testando: $url\n";
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_NOBODY, true);
    curl_setopt($ch, CURLOPT_HEADER, true);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code == 200) {
        echo "   ✅ ENCONTRADO! HTTP $http_code\n";
        echo "   🎯 ACESSE: $url\n\n";
    } elseif ($http_code > 0) {
        echo "   ⚠️  HTTP $http_code (pode ser login/auth)\n";
    } else {
        echo "   ❌ Sem resposta\n";
    }
}

echo "\n📋 INSTRUÇÕES:\n";
echo "1. Acesse as URLs marcadas com ✅\n";
echo "2. Procure por 'Webhook', 'API Settings', 'Configurações'\n";
echo "3. Configure: https://app.pixel12digital.com.br/painel/receber_mensagem_ana_local.php\n\n";

echo "🔑 CREDENCIAIS COMUNS:\n";
echo "- admin / admin\n";
echo "- admin / password\n";
echo "- whatsapp / whatsapp\n";
echo "- root / root\n\n";

echo "📞 SE NÃO ENCONTRAR, o painel pode estar em:\n";
echo "- Outro servidor/IP\n";
echo "- Porta diferente\n";
echo "- Configuração via arquivo de config\n";
?> 