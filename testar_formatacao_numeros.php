<?php
/**
 * 🧪 TESTE DE FORMATAÇÃO DE NÚMEROS
 * 
 * Testa se a formatação dos números está correta para envio via WhatsApp
 */

echo "🧪 TESTE DE FORMATAÇÃO DE NÚMEROS\n";
echo "==================================\n\n";

require_once 'config.php';
require_once 'painel/db.php';

// Função para formatar número WhatsApp (cópia da função corrigida)
function ajustarNumeroWhatsapp($numero) {
    // Remover todos os caracteres não numéricos
    $numero = preg_replace('/\D/', '', $numero);
    
    // Se já tem código do país (55), remover para processar
    if (strpos($numero, '55') === 0) {
        $numero = substr($numero, 2);
    }
    
    // Para números muito longos, pegar apenas os últimos 11 dígitos (DDD + telefone)
    if (strlen($numero) > 11) {
        $numero = substr($numero, -11);
    }
    
    // Verificar se tem pelo menos DDD (2 dígitos) + número (mínimo 7 dígitos)
    if (strlen($numero) < 9) {
        return null; // Número muito curto
    }
    
    // Extrair DDD e número
    $ddd = substr($numero, 0, 2);
    $telefone = substr($numero, 2);
    
    // Verificar se o DDD é válido (deve ser um DDD brasileiro válido)
    $ddds_validos = ['11','12','13','14','15','16','17','18','19','21','22','24','27','28','31','32','33','34','35','37','38','41','42','43','44','45','46','47','48','49','51','53','54','55','61','62','63','64','65','66','67','68','69','71','73','74','75','77','79','81','82','83','84','85','86','87','88','89','91','92','93','94','95','96','97','98','99'];
    
    if (!in_array($ddd, $ddds_validos)) {
        return null; // DDD inválido
    }
    
    // NUNCA ADICIONAR 9 - usar exatamente o número como está no banco
    // Verificar se o número final é válido (deve ter 7, 8 ou 9 dígitos)
    if (strlen($telefone) < 7 || strlen($telefone) > 9) {
        return null; // Número inválido
    }
    
    // GARANTIR SEMPRE o código +55 do Brasil + DDD + número + @c.us
    return '55' . $ddd . $telefone . '@c.us';
}

// 1. TESTAR NÚMERO DO CHARLES DIETRICH
echo "📱 TESTE DO NÚMERO DO CHARLES DIETRICH\n";
echo "=====================================\n";

$numero_charles = '554796164699'; // Número do Charles (do print)
echo "Número original: $numero_charles\n";

$numero_formatado = ajustarNumeroWhatsapp($numero_charles);
echo "Número formatado: " . ($numero_formatado ?: 'ERRO - Número inválido') . "\n\n";

// 2. TESTAR NÚMERO DO CANAL 3001
echo "📱 TESTE DO NÚMERO DO CANAL 3001\n";
echo "================================\n";

$numero_canal = '554797309525'; // Número do canal 3001
echo "Número original: $numero_canal\n";

$numero_formatado_canal = ajustarNumeroWhatsapp($numero_canal);
echo "Número formatado: " . ($numero_formatado_canal ?: 'ERRO - Número inválido') . "\n\n";

// 3. TESTAR DIFERENTES FORMATOS
echo "📱 TESTE DE DIFERENTES FORMATOS\n";
echo "===============================\n";

$testes = [
    '4796164699',           // Sem 55
    '554796164699',         // Com 55
    '+554796164699',        // Com +
    '(47) 96164-6999',      // Com formatação
    '47 96164 6999',        // Com espaços
    '4797309525',           // Canal sem 55
    '554797309525',         // Canal com 55
];

foreach ($testes as $teste) {
    $formatado = ajustarNumeroWhatsapp($teste);
    echo "Original: $teste → Formatado: " . ($formatado ?: 'ERRO') . "\n";
}

echo "\n";

// 4. VERIFICAR CLIENTES NO BANCO
echo "📊 VERIFICAR CLIENTES NO BANCO\n";
echo "==============================\n";

$clientes = $mysqli->query("SELECT id, nome, celular FROM clientes WHERE celular LIKE '%4796164699%' OR nome LIKE '%Charles%' LIMIT 5");

if ($clientes && $clientes->num_rows > 0) {
    while ($cliente = $clientes->fetch_assoc()) {
        echo "Cliente: {$cliente['nome']} (ID: {$cliente['id']})\n";
        echo "Celular: {$cliente['celular']}\n";
        $formatado = ajustarNumeroWhatsapp($cliente['celular']);
        echo "Formatado: " . ($formatado ?: 'ERRO') . "\n";
        echo "---\n";
    }
} else {
    echo "Nenhum cliente encontrado com esse número.\n";
}

echo "\n";

// 5. VERIFICAR CANAIS
echo "📊 VERIFICAR CANAIS\n";
echo "===================\n";

$canais = $mysqli->query("SELECT id, nome_exibicao, identificador, porta FROM canais_comunicacao WHERE status = 'conectado'");

if ($canais && $canais->num_rows > 0) {
    while ($canal = $canais->fetch_assoc()) {
        echo "Canal: {$canal['nome_exibicao']} (ID: {$canal['id']})\n";
        echo "Identificador: {$canal['identificador']}\n";
        echo "Porta: {$canal['porta']}\n";
        if ($canal['identificador']) {
            // Extrair número do identificador (remover @c.us)
            $numero_limpo = str_replace('@c.us', '', $canal['identificador']);
            $formatado = ajustarNumeroWhatsapp($numero_limpo);
            echo "Formatado: " . ($formatado ?: 'ERRO') . "\n";
        }
        echo "---\n";
    }
} else {
    echo "Nenhum canal conectado encontrado.\n";
}

echo "\n✅ TESTE CONCLUÍDO!\n";
?> 