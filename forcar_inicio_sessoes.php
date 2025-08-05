<?php
/**
 * 🚀 FORÇAR INÍCIO DAS SESSÕES NA VPS
 */

echo "🚀 FORÇANDO INÍCIO DAS SESSÕES\n";
echo "=============================\n\n";

$endpoints = [
    ['porta' => 3000, 'sessao' => 'default'],
    ['porta' => 3001, 'sessao' => 'comercial']
];

foreach ($endpoints as $canal) {
    $porta = $canal['porta'];
    $sessao = $canal['sessao'];
    
    echo "🔄 CANAL $porta (sessão: $sessao)\n";
    echo "--------------------------------\n";
    
    // Tentar diferentes formas de iniciar
    $urls_inicio = [
        "http://212.85.11.238:$porta/session/start/$sessao",
        "http://212.85.11.238:$porta/start/$sessao",
        "http://212.85.11.238:$porta/init/$sessao"
    ];
    
    foreach ($urls_inicio as $url) {
        echo "Tentando: $url\n";
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        echo "  → Código: $http_code\n";
        echo "  → Resposta: $response\n";
        
        if ($http_code === 200) {
            echo "  ✅ Sucesso!\n";
            break;
        }
    }
    
    echo "\n";
}

echo "🎯 AGORA TESTE O QR CODE NO PAINEL!\n";
?> 