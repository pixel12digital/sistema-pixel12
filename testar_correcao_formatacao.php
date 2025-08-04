<?php
/**
 * 🧪 TESTE DA CORREÇÃO DE FORMATAÇÃO
 */

echo "🧪 TESTE DA CORREÇÃO DE FORMATAÇÃO\n";
echo "==================================\n\n";

// Simular a lógica corrigida
function formatarNumeroCorrigido($numero) {
    $numero_limpo = preg_replace('/\D/', '', $numero);
    
    // Verificar se já tem código 55 no início
    if (strpos($numero_limpo, '55') === 0) {
        // Se já tem 55, usar como está
        return $numero_limpo . '@c.us';
    } else {
        // Se não tem 55, adicionar
        return '55' . $numero_limpo . '@c.us';
    }
}

// Testar com o número do Charles
$numero_charles = '554796164699';
echo "Número original: $numero_charles\n";
echo "Formatado (corrigido): " . formatarNumeroCorrigido($numero_charles) . "\n\n";

// Testar com diferentes formatos
$testes = [
    '554796164699',    // Com 55
    '4796164699',      // Sem 55
    '+554796164699',   // Com + e 55
    '(47) 96164-6999', // Com formatação
];

foreach ($testes as $teste) {
    echo "Original: $teste → Formatado: " . formatarNumeroCorrigido($teste) . "\n";
}

echo "\n✅ TESTE CONCLUÍDO!\n";
?> 