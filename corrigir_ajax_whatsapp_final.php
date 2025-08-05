<?php
echo "🔧 CORRIGINDO AJAX WHATSAPP - ENDPOINT QR\n";
echo "==========================================\n\n";

$arquivo = 'painel/ajax_whatsapp.php';
$conteudo = file_get_contents($arquivo);

if (!$conteudo) {
    echo "❌ ERRO: Não foi possível ler o arquivo $arquivo\n";
    exit;
}

echo "📖 Lendo arquivo: $arquivo\n";
echo "📏 Tamanho: " . strlen($conteudo) . " caracteres\n\n";

// CORREÇÃO 1: Substituir o endpoint QR incorreto
$padrao_antigo = '$qr_endpoint = "/session/default/qr";';
$padrao_novo = '$qr_endpoint = "/qr?session=" . $sessionName;';

if (strpos($conteudo, $padrao_antigo) !== false) {
    $conteudo = str_replace($padrao_antigo, $padrao_novo, $conteudo);
    echo "✅ CORREÇÃO 1: Endpoint QR corrigido\n";
    echo "   Antes: $padrao_antigo\n";
    echo "   Depois: $padrao_novo\n\n";
} else {
    echo "⚠️  CORREÇÃO 1: Padrão não encontrado, verificando alternativas...\n";
    
    // Verificar se já está correto
    if (strpos($conteudo, '/qr?session=') !== false) {
        echo "✅ Endpoint QR já está correto!\n\n";
    } else {
        echo "❌ Endpoint QR não encontrado no formato esperado\n\n";
    }
}

// CORREÇÃO 2: Melhorar a lógica de validação do QR
$padrao_validacao = 'if (!str_starts_with($qrData, \'undefined\') && 
                        !str_starts_with($qrData, \'simulate-qr\') && 
                        !str_starts_with($qrData, \'test-\') &&
                        !str_starts_with($qrData, \'mock-\') &&
                        strlen($qrData) > 10) {';

$validacao_melhorada = 'if (!str_starts_with($qrData, \'undefined\') && 
                        !str_starts_with($qrData, \'simulate-qr\') && 
                        !str_starts_with($qrData, \'test-\') &&
                        !str_starts_with($qrData, \'mock-\') &&
                        !str_starts_with($qrData, \'2@qJaXRo\') && // Rejeitar QR específico problemático
                        strlen($qrData) > 50) { // Aumentar tamanho mínimo para QR válido';

if (strpos($conteudo, $padrao_validacao) !== false) {
    $conteudo = str_replace($padrao_validacao, $validacao_melhorada, $conteudo);
    echo "✅ CORREÇÃO 2: Validação de QR melhorada\n";
    echo "   - Adicionado filtro para QR problemático\n";
    echo "   - Aumentado tamanho mínimo para 50 caracteres\n\n";
} else {
    echo "⚠️  CORREÇÃO 2: Padrão de validação não encontrado\n\n";
}

// CORREÇÃO 3: Adicionar fallback para buscar QR no clients_status
$padrao_fallback = '// CORREÇÃO: Verificar também no clients_status se não encontrou no nível principal
                if (!$qrValid && isset($data[\'clients_status\'][\'default\'][\'qr\'])) {
                    $qrData = $data[\'clients_status\'][\'default\'][\'qr\'];
                    
                    if (!str_starts_with($qrData, \'undefined\') && 
                        !str_starts_with($qrData, \'simulate-qr\') && 
                        !str_starts_with($qrData, \'test-\') &&
                        !str_starts_with($qrData, \'mock-\') &&
                        strlen($qrData) > 10) {
                        
                        $qr = $qrData;
                        $qrValid = true;
                        error_log("[WhatsApp QR Valid] QR Code válido encontrado via clients_status: " . substr($qr, 0, 20) . "...");
                    }
                }';

$fallback_melhorado = '// CORREÇÃO: Verificar também no clients_status se não encontrou no nível principal
                if (!$qrValid && isset($data[\'clients_status\'][$sessionName][\'qr\'])) {
                    $qrData = $data[\'clients_status\'][$sessionName][\'qr\'];
                    
                    if (!str_starts_with($qrData, \'undefined\') && 
                        !str_starts_with($qrData, \'simulate-qr\') && 
                        !str_starts_with($qrData, \'test-\') &&
                        !str_starts_with($qrData, \'mock-\') &&
                        !str_starts_with($qrData, \'2@qJaXRo\') && // Rejeitar QR específico problemático
                        strlen($qrData) > 50) { // Aumentar tamanho mínimo
                        
                        $qr = $qrData;
                        $qrValid = true;
                        error_log("[WhatsApp QR Valid] QR Code válido encontrado via clients_status: " . substr($qr, 0, 20) . "...");
                    }
                }';

if (strpos($conteudo, $padrao_fallback) !== false) {
    $conteudo = str_replace($padrao_fallback, $fallback_melhorado, $conteudo);
    echo "✅ CORREÇÃO 3: Fallback de clients_status melhorado\n";
    echo "   - Usa \$sessionName dinâmico em vez de 'default' fixo\n";
    echo "   - Aplicado mesmo filtro de validação\n\n";
} else {
    echo "⚠️  CORREÇÃO 3: Padrão de fallback não encontrado\n\n";
}

// Salvar o arquivo corrigido
if (file_put_contents($arquivo, $conteudo)) {
    echo "✅ ARQUIVO SALVO COM SUCESSO!\n";
    echo "📁 Arquivo: $arquivo\n";
    echo "📏 Novo tamanho: " . strlen($conteudo) . " caracteres\n\n";
} else {
    echo "❌ ERRO: Não foi possível salvar o arquivo\n";
    exit;
}

// Testar se as correções foram aplicadas
echo "🔍 VERIFICANDO CORREÇÕES APLICADAS:\n";
echo "====================================\n";

$conteudo_atual = file_get_contents($arquivo);

// Verificar endpoint QR
if (strpos($conteudo_atual, '/qr?session=') !== false) {
    echo "✅ Endpoint QR corrigido: /qr?session=\$sessionName\n";
} else {
    echo "❌ Endpoint QR ainda incorreto\n";
}

// Verificar validação melhorada
if (strpos($conteudo_atual, '2@qJaXRo') !== false) {
    echo "✅ Filtro para QR problemático adicionado\n";
} else {
    echo "❌ Filtro para QR problemático não encontrado\n";
}

// Verificar tamanho mínimo
if (strpos($conteudo_atual, 'strlen($qrData) > 50') !== false) {
    echo "✅ Tamanho mínimo de QR aumentado para 50 caracteres\n";
} else {
    echo "❌ Tamanho mínimo de QR não foi atualizado\n";
}

echo "\n🎯 PRÓXIMOS PASSOS:\n";
echo "===================\n";
echo "1. Recarregue a página do painel\n";
echo "2. Teste a conexão do WhatsApp\n";
echo "3. Os QR codes devem aparecer corretamente agora!\n\n";

echo "🔗 URL do painel: https://app.pixel12digital.com.br/painel/comunicacao.php\n";
echo "📱 Teste os endpoints:\n";
echo "   - http://212.85.11.238:3000/qr?session=default\n";
echo "   - http://212.85.11.238:3001/qr?session=comercial\n\n";

echo "✅ CORREÇÃO CONCLUÍDA!\n";
?> 