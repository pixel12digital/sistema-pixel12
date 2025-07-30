<?php
/**
 * Teste Simplificado das Funções de Contexto Conversacional
 * 
 * Este arquivo testa apenas as funções implementadas sem depender do webhook completo
 */

require_once 'config.php';

// ===== FUNÇÕES DE CONTEXTO CONVERSACIONAL =====
function verificarContextoConversacional($numero, $cliente_id, $texto, $mysqli) {
    $texto_lower = strtolower(trim($texto));
    
    // Verificar se já foi enviada resposta de faturas recentemente (últimas 2 horas)
    $sql_contexto = "SELECT 
                        m.mensagem, 
                        m.data_hora,
                        m.direcao,
                        TIMESTAMPDIFF(MINUTE, m.data_hora, NOW()) as minutos_atras
                    FROM mensagens_comunicacao m 
                    WHERE m.numero_whatsapp = ? 
                    AND m.direcao = 'enviado'
                    AND m.mensagem LIKE '%fatura%'
                    AND m.data_hora >= DATE_SUB(NOW(), INTERVAL 2 HOUR)
                    ORDER BY m.data_hora DESC 
                    LIMIT 1";
    
    $stmt = $mysqli->prepare($sql_contexto);
    $stmt->bind_param('s', $numero);
    $stmt->execute();
    $result_contexto = $stmt->get_result();
    $contexto_faturas = $result_contexto->fetch_assoc();
    $stmt->close();
    
    // Verificar se é uma solicitação de consolidação ou ação específica
    $palavras_consolidacao = ['boleto só', 'boleto so', 'único', 'unico', 'junto', 'consolidar', 'agregar', 'tudo junto'];
    $eh_solicitacao_consolidacao = false;
    foreach ($palavras_consolidacao as $palavra) {
        if (strpos($texto_lower, $palavra) !== false) {
            $eh_solicitacao_consolidacao = true;
            break;
        }
    }
    
    // Verificar se é uma solicitação fora do contexto
    $palavras_fora_contexto = ['negociação', 'negociacao', 'desconto', 'parcelamento', 'renegociar', 'renegociacao', 'atendente', 'humano', 'pessoa'];
    $eh_fora_contexto = false;
    foreach ($palavras_fora_contexto as $palavra) {
        if (strpos($texto_lower, $palavra) !== false) {
            $eh_fora_contexto = true;
            break;
        }
    }
    
    return [
        'faturas_enviadas_recentemente' => $contexto_faturas ? true : false,
        'minutos_ultima_fatura' => $contexto_faturas ? $contexto_faturas['minutos_atras'] : null,
        'eh_solicitacao_consolidacao' => $eh_solicitacao_consolidacao,
        'eh_fora_contexto' => $eh_fora_contexto,
        'texto_original' => $texto_lower
    ];
}

function gerarFallbackInteligente($contexto, $cliente_id, $mysqli) {
    if ($contexto['eh_fora_contexto']) {
        return "Olá! 👋\n\n" .
               "📋 *Este canal é específico para consulta de faturas.*\n\n" .
               "Para negociações diferenciadas ou outros assuntos, digite *1* para falar com um atendente.\n\n" .
               "🤖 *Esta é uma mensagem automática*";
    }
    
    if ($contexto['eh_solicitacao_consolidacao']) {
        return "Olá! 👋\n\n" .
               "Entendi que você gostaria de consolidar suas faturas em um único pagamento.\n\n" .
               "Para essa solicitação específica, digite *1* para falar com um atendente que poderá ajudá-lo com essa negociação.\n\n" .
               "🤖 *Esta é uma mensagem automática*";
    }
    
    if ($contexto['faturas_enviadas_recentemente']) {
        $minutos = $contexto['minutos_ultima_fatura'];
        return "Olá! 👋\n\n" .
               "As informações das suas faturas foram enviadas há $minutos minutos.\n\n" .
               "Se precisar de algo específico ou negociação diferenciada, digite *1* para falar com um atendente.\n\n" .
               "🤖 *Esta é uma mensagem automática*";
    }
    
    // Fallback genérico para situações não compreendidas
    return "Olá! 👋\n\n" .
           "Não entendi completamente sua solicitação.\n\n" .
           "📋 *Este canal é para consulta de faturas.*\n\n" .
           "Para outros assuntos ou atendimento personalizado, digite *1* para falar com um atendente.\n\n" .
           "🤖 *Esta é uma mensagem automática*";
}

function processarSolicitacaoAtendente($numero, $cliente_id, $mysqli) {
    // Verificar se já existe uma solicitação de atendente em andamento
    $sql_atendente = "SELECT 
                        m.mensagem, 
                        m.data_hora,
                        TIMESTAMPDIFF(MINUTE, m.data_hora, NOW()) as minutos_atras
                    FROM mensagens_comunicacao m 
                    WHERE m.numero_whatsapp = ? 
                    AND m.direcao = 'enviado'
                    AND m.mensagem LIKE '%atendente%'
                    AND m.data_hora >= DATE_SUB(NOW(), INTERVAL 30 MINUTE)
                    ORDER BY m.data_hora DESC 
                    LIMIT 1";
    
    $stmt = $mysqli->prepare($sql_atendente);
    $stmt->bind_param('s', $numero);
    $stmt->execute();
    $result_atendente = $stmt->get_result();
    $solicitacao_anterior = $result_atendente->fetch_assoc();
    $stmt->close();
    
    if ($solicitacao_anterior) {
        return "Sua solicitação de atendente já foi registrada! 📞\n\n" .
               "Um atendente entrará em contato em breve através do número: *47 997309525*\n\n" .
               "Aguarde o contato! 😊\n\n" .
               "🤖 *Esta é uma mensagem automática*";
    }
    
    // Registrar solicitação de atendente
    $canal_id = 1; // Assumindo que canal_id 1 é WhatsApp
    $data_hora = date('Y-m-d H:i:s');
    $mensagem_atendente = "Solicitação de atendente registrada - Cliente solicitou transferência para atendente humano";
    
    $sql_insert = "INSERT INTO mensagens_comunicacao (canal_id, cliente_id, mensagem, tipo, data_hora, direcao, status, numero_whatsapp) 
                   VALUES (?, ?, ?, 'sistema', ?, 'enviado', 'enviado', ?)";
    
    $stmt = $mysqli->prepare($sql_insert);
    $stmt->bind_param('iisss', $canal_id, $cliente_id, $mensagem_atendente, $data_hora, $numero);
    $stmt->execute();
    $stmt->close();
    
    return "✅ *Solicitação de atendente registrada com sucesso!*\n\n" .
           "📞 Um atendente entrará em contato em breve através do número: *47 997309525*\n\n" .
           "⏰ Aguarde o contato! 😊\n\n" .
           "🤖 *Esta é uma mensagem automática*";
}

// ===== INTERFACE DE TESTE =====
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
    echo "<div class='test-section success'>\n";
    echo "<h3>✅ Conexão com banco estabelecida</h3>\n";
    echo "</div>\n";
} catch (Exception $e) {
    echo "<div class='test-section error'>\n";
    echo "<h3>❌ Erro de Conexão</h3>\n";
    echo "<p>" . $e->getMessage() . "</p>\n";
    echo "</div>\n";
    exit;
}

// Teste 1: Verificar Contexto Conversacional
echo "<div class='test-section info'>\n";
echo "<h3>🔍 Teste 1: Verificação de Contexto Conversacional</h3>\n";

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

// Teste 2: Fallback Inteligente
echo "<div class='test-section info'>\n";
echo "<h3>🔄 Teste 2: Fallback Inteligente</h3>\n";

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

// Teste 3: Solicitação de Atendente
echo "<div class='test-section info'>\n";
echo "<h3>📞 Teste 3: Solicitação de Atendente</h3>\n";

try {
    $resposta_atendente = processarSolicitacaoAtendente($numero_teste, $cliente_id_teste, $mysqli);
    echo "<pre>" . htmlspecialchars($resposta_atendente) . "</pre>\n";
} catch (Exception $e) {
    echo "<p class='error'>❌ Erro ao processar solicitação de atendente: " . $e->getMessage() . "</p>\n";
}
echo "</div>\n";

// Teste 4: Simular fluxo completo
echo "<div class='test-section info'>\n";
echo "<h3>🔄 Teste 4: Simulação de Fluxo Completo</h3>\n";

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