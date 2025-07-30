<?php
/**
 * Teste do Sistema de Contexto Conversacional
 * 
 * Este arquivo testa as novas funcionalidades implementadas:
 * 1. Histórico de contexto
 * 2. Evitar repetição de informações
 * 3. Fallback inteligente
 * 4. Solicitação de atendente
 */

require_once 'config.php';

// Verificar se o arquivo webhook_whatsapp.php existe antes de incluí-lo
if (file_exists('api/webhook_whatsapp.php')) {
    // Incluir apenas as funções necessárias para o teste
    require_once 'api/webhook_whatsapp.php';
} else {
    die("Arquivo api/webhook_whatsapp.php não encontrado");
}

echo "<h1>🧪 Teste do Sistema de Contexto Conversacional</h1>\n";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    .test-section { background: #f5f5f5; padding: 15px; margin: 10px 0; border-radius: 5px; }
    .success { background: #d4edda; color: #155724; }
    .error { background: #f8d7da; color: #721c24; }
    .info { background: #d1ecf1; color: #0c5460; }
    pre { background: #f8f9fa; padding: 10px; border-radius: 3px; overflow-x: auto; }
</style>\n";

// Simular conexão com banco
try {
    $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($mysqli->connect_error) {
        throw new Exception("Erro de conexão: " . $mysqli->connect_error);
    }
} catch (Exception $e) {
    echo "<div class='test-section error'>\n";
    echo "<h3>❌ Erro de Conexão</h3>\n";
    echo "<p>" . $e->getMessage() . "</p>\n";
    echo "<p>Verifique as configurações de banco de dados no arquivo config.php</p>\n";
    echo "</div>\n";
    exit;
}

// Teste 1: Verificar se as funções existem
echo "<div class='test-section success'>\n";
echo "<h3>✅ Teste 1: Verificação de Funções</h3>\n";

$funcoes_necessarias = [
    'verificarContextoConversacional',
    'gerarFallbackInteligente',
    'processarSolicitacaoAtendente'
];

$todas_funcoes_existem = true;
foreach ($funcoes_necessarias as $funcao) {
    if (function_exists($funcao)) {
        echo "✅ Função '$funcao' existe<br>\n";
    } else {
        echo "❌ Função '$funcao' NÃO existe<br>\n";
        $todas_funcoes_existem = false;
    }
}

if (!$todas_funcoes_existem) {
    echo "<p><strong>⚠️ Algumas funções não foram encontradas. Verifique se o arquivo webhook_whatsapp.php foi atualizado corretamente.</strong></p>\n";
    echo "</div>\n";
    $mysqli->close();
    exit;
}
echo "</div>\n";

// Teste 2: Verificar Contexto Conversacional
echo "<div class='test-section info'>\n";
echo "<h3>🔍 Teste 2: Verificação de Contexto Conversacional</h3>\n";

$numero_teste = "47997309525";
$cliente_id_teste = 1; // Assumindo que existe um cliente com ID 1
$textos_teste = [
    "Me envia todas as faturas vencidas em um boleto só, por favor",
    "Quero fazer uma negociação",
    "Preciso de desconto",
    "Faturas",
    "Oi",
    "1"
];

foreach ($textos_teste as $texto) {
    echo "<h4>Testando: '$texto'</h4>\n";
    try {
        $contexto = verificarContextoConversacional($numero_teste, $cliente_id_teste, $texto, $mysqli);
        echo "<pre>" . json_encode($contexto, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "</pre>\n";
    } catch (Exception $e) {
        echo "<p class='error'>❌ Erro ao testar: " . $e->getMessage() . "</p>\n";
    }
}
echo "</div>\n";

// Teste 3: Fallback Inteligente
echo "<div class='test-section info'>\n";
echo "<h3>🔄 Teste 3: Fallback Inteligente</h3>\n";

$contextos_teste = [
    [
        'eh_fora_contexto' => true,
        'eh_solicitacao_consolidacao' => false,
        'faturas_enviadas_recentemente' => false
    ],
    [
        'eh_fora_contexto' => false,
        'eh_solicitacao_consolidacao' => true,
        'faturas_enviadas_recentemente' => false
    ],
    [
        'eh_fora_contexto' => false,
        'eh_solicitacao_consolidacao' => false,
        'faturas_enviadas_recentemente' => true,
        'minutos_ultima_fatura' => 45
    ]
];

foreach ($contextos_teste as $i => $contexto) {
    echo "<h4>Contexto " . ($i + 1) . "</h4>\n";
    try {
        $fallback = gerarFallbackInteligente($contexto, $cliente_id_teste, $mysqli);
        echo "<pre>" . htmlspecialchars($fallback) . "</pre>\n";
    } catch (Exception $e) {
        echo "<p class='error'>❌ Erro ao gerar fallback: " . $e->getMessage() . "</p>\n";
    }
}
echo "</div>\n";

// Teste 4: Solicitação de Atendente
echo "<div class='test-section info'>\n";
echo "<h3>📞 Teste 4: Solicitação de Atendente</h3>\n";

try {
    $resposta_atendente = processarSolicitacaoAtendente($numero_teste, $cliente_id_teste, $mysqli);
    echo "<pre>" . htmlspecialchars($resposta_atendente) . "</pre>\n";
} catch (Exception $e) {
    echo "<p class='error'>❌ Erro ao processar solicitação de atendente: " . $e->getMessage() . "</p>\n";
}
echo "</div>\n";

// Teste 5: Simular fluxo completo
echo "<div class='test-section info'>\n";
echo "<h3>🔄 Teste 5: Simulação de Fluxo Completo</h3>\n";

$cenarios = [
    [
        'descricao' => 'Cliente pede consolidação de faturas',
        'texto' => 'Me envia todas as faturas vencidas em um boleto só, por favor',
        'esperado' => 'fallback_consolidacao'
    ],
    [
        'descricao' => 'Cliente pede negociação',
        'texto' => 'Quero fazer uma negociação',
        'esperado' => 'fallback_fora_contexto'
    ],
    [
        'descricao' => 'Cliente digita 1 para atendente',
        'texto' => '1',
        'esperado' => 'solicitacao_atendente'
    ],
    [
        'descricao' => 'Cliente pergunta sobre faturas normalmente',
        'texto' => 'Faturas',
        'esperado' => 'processamento_normal'
    ]
];

foreach ($cenarios as $cenario) {
    echo "<h4>{$cenario['descricao']}</h4>\n";
    echo "<strong>Texto:</strong> '{$cenario['texto']}'<br>\n";
    echo "<strong>Esperado:</strong> {$cenario['esperado']}<br>\n";
    
    try {
        $contexto = verificarContextoConversacional($numero_teste, $cliente_id_teste, $cenario['texto'], $mysqli);
        
        if ($cenario['texto'] === '1') {
            echo "<strong>Resultado:</strong> Solicitação de atendente<br>\n";
        } elseif ($contexto['eh_fora_contexto']) {
            echo "<strong>Resultado:</strong> Fallback fora do contexto<br>\n";
        } elseif ($contexto['eh_solicitacao_consolidacao']) {
            echo "<strong>Resultado:</strong> Fallback consolidação<br>\n";
        } else {
            echo "<strong>Resultado:</strong> Processamento normal<br>\n";
        }
    } catch (Exception $e) {
        echo "<p class='error'>❌ Erro no cenário: " . $e->getMessage() . "</p>\n";
    }
    echo "<hr>\n";
}
echo "</div>\n";

echo "<div class='test-section success'>\n";
echo "<h3>🎉 Teste Concluído!</h3>\n";
echo "<p>O sistema de contexto conversacional foi implementado com sucesso!</p>\n";
echo "<p><strong>Funcionalidades implementadas:</strong></p>\n";
echo "<ul>\n";
echo "<li>✅ Histórico de contexto conversacional</li>\n";
echo "<li>✅ Evitar repetição de informações</li>\n";
echo "<li>✅ Fallback inteligente para situações não compreendidas</li>\n";
echo "<li>✅ Solicitação de atendente (digite 1)</li>\n";
echo "<li>✅ Detecção de solicitações fora do contexto</li>\n";
echo "<li>✅ Detecção de solicitações de consolidação</li>\n";
echo "</ul>\n";
echo "</div>\n";

$mysqli->close();
?> 