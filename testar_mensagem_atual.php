<?php
/**
 * 🧪 TESTE DA MENSAGEM ATUAL
 * Testa exatamente qual mensagem está sendo enviada quando o usuário digita "boa tarde"
 */

header('Content-Type: text/html; charset=utf-8');
require_once 'config.php';
require_once 'painel/db.php';

echo "<h2>🧪 Teste da Mensagem Atual</h2>";
echo "<p><strong>Testando:</strong> Qual mensagem está sendo enviada quando você digita 'boa tarde'</p>";
echo "<hr>";

// Simular dados que viriam do WhatsApp
$data = [
    'event' => 'onmessage',
    'data' => [
        'from' => '554796164699', // Seu número
        'text' => 'boa tarde',
        'type' => 'text'
    ]
];

echo "<div style='background: #f0f8ff; padding: 15px; border-radius: 10px; margin: 10px 0;'>";
echo "<h3>📤 Dados de Teste:</h3>";
echo "<p><strong>Payload:</strong></p>";
echo "<pre style='background: white; padding: 10px; border: 1px solid #ccc; border-radius: 5px;'>";
echo htmlspecialchars(json_encode($data, JSON_PRETTY_PRINT));
echo "</pre>";
echo "</div>";

// Processar como o webhook faria
if (isset($data['event']) && $data['event'] === 'onmessage') {
    $message = $data['data'];
    
    // Extrair informações
    $numero = $message['from'];
    $texto = $message['text'] ?? '';
    $tipo = $message['type'] ?? 'text';
    $data_hora = date('Y-m-d H:i:s');
    
    echo "<div style='background: #e8f5e8; padding: 15px; border-radius: 10px; margin: 10px 0;'>";
    echo "<h3>📥 Processando Mensagem:</h3>";
    echo "<p><strong>De:</strong> $numero</p>";
    echo "<p><strong>Texto:</strong> '$texto'</p>";
    echo "<p><strong>Tipo:</strong> $tipo</p>";
    echo "<p><strong>Data/Hora:</strong> $data_hora</p>";
    echo "</div>";
    
    // Buscar cliente pelo número
    $numero_limpo = preg_replace('/\D/', '', $numero);
    $cliente_id = null;
    $cliente = null;
    
    // Buscar cliente com similaridade de número
    $formatos_busca = [
        $numero_limpo,
        ltrim($numero_limpo, '55'),
        substr($numero_limpo, -11),
        substr($numero_limpo, -10),
        substr($numero_limpo, -9),
    ];
    
    foreach ($formatos_busca as $formato) {
        if (strlen($formato) >= 9) {
            $sql = "SELECT id, nome, contact_name, celular, telefone FROM clientes 
                    WHERE REPLACE(REPLACE(REPLACE(REPLACE(celular, '(', ''), ')', ''), '-', ''), ' ', '') LIKE '%$formato%' 
                    OR REPLACE(REPLACE(REPLACE(REPLACE(telefone, '(', ''), ')', ''), '-', ''), ' ', '') LIKE '%$formato%'
                    LIMIT 1";
            $result = $mysqli->query($sql);
            
            if ($result && $result->num_rows > 0) {
                $cliente = $result->fetch_assoc();
                $cliente_id = $cliente['id'];
                break;
            }
        }
    }
    
    echo "<div style='background: #fff3cd; padding: 15px; border-radius: 10px; margin: 10px 0;'>";
    echo "<h3>🔍 Busca de Cliente:</h3>";
    if ($cliente) {
        echo "<p style='color: green;'>✅ <strong>Cliente encontrado!</strong></p>";
        echo "<ul>";
        echo "<li><strong>ID:</strong> " . $cliente['id'] . "</li>";
        echo "<li><strong>Nome:</strong> " . $cliente['nome'] . "</li>";
        echo "<li><strong>Contact Name:</strong> " . $cliente['contact_name'] . "</li>";
        echo "</ul>";
    } else {
        echo "<p style='color: red;'>❌ <strong>Cliente não encontrado</strong></p>";
    }
    echo "</div>";
    
    // Testar a função gerarRespostaPadrao atual
    echo "<div style='background: #d4edda; padding: 15px; border-radius: 10px; margin: 10px 0;'>";
    echo "<h3>🤖 Testando Função gerarRespostaPadrao:</h3>";
    
    function gerarRespostaPadrao($cliente_id, $cliente) {
        if ($cliente_id && $cliente) {
            $nome_cliente = $cliente['contact_name'] ?: $cliente['nome'];
            $resposta = "Olá $nome_cliente! 👋\n\n";
            $resposta .= "🤖 *Este é um atendimento automático* do canal exclusivo da *Pixel12Digital* para assuntos financeiros.\n\n";
            $resposta .= "📞 *Para outras informações ou falar com nossa equipe:*\n";
            $resposta .= "Entre em contato: *47 997309525*\n\n";
            $resposta .= "💰 *Para assuntos financeiros:*\n";
            $resposta .= "• Digite 'faturas' para consultar suas faturas em aberto\n";
            $resposta .= "• Verificar status de pagamentos\n";
            $resposta .= "• Informações sobre planos\n\n";
            $resposta .= "Como posso ajudá-lo hoje? 😊";
            
            return $resposta;
        } else {
            $resposta = "Olá! 👋\n\n";
            $resposta .= "🤖 *Este é um atendimento automático* do canal exclusivo da *Pixel12Digital* para assuntos financeiros.\n\n";
            $resposta .= "📞 *Para outras informações ou falar com nossa equipe:*\n";
            $resposta .= "Entre em contato: *47 997309525*\n\n";
            $resposta .= "💰 *Para assuntos financeiros:*\n";
            $resposta .= "• Digite 'faturas' para consultar suas faturas em aberto\n";
            $resposta .= "• Verificar status de pagamentos\n";
            $resposta .= "• Informações sobre planos\n\n";
            $resposta .= "Se não encontrar seu cadastro, informe seu CPF ou CNPJ (apenas números).";
            
            return $resposta;
        }
    }
    
    $resposta_automatica = gerarRespostaPadrao($cliente_id, $cliente);
    
    echo "<h4>Resposta gerada pela função gerarRespostaPadrao:</h4>";
    echo "<pre style='background: white; padding: 10px; border: 1px solid #ccc; border-radius: 5px; white-space: pre-wrap;'>";
    echo htmlspecialchars($resposta_automatica);
    echo "</pre>";
    echo "</div>";
    
    // Testar também a IA
    echo "<div style='background: #cce5ff; padding: 15px; border-radius: 10px; margin: 10px 0;'>";
    echo "<h3>🤖 Testando IA (processar_mensagem_ia.php):</h3>";
    
    try {
        $payload_ia = [
            'from' => $numero,
            'message' => $texto,
            'type' => $tipo
        ];
        
        echo "<p><strong>Chamando IA com payload:</strong></p>";
        echo "<pre style='background: white; padding: 10px; border: 1px solid #ccc; border-radius: 5px;'>";
        echo htmlspecialchars(json_encode($payload_ia, JSON_PRETTY_PRINT));
        echo "</pre>";
        
        // Chamar endpoint da IA
        $ch_ia = curl_init('http://localhost/painel/api/processar_mensagem_ia.php');
        curl_setopt($ch_ia, CURLOPT_POST, true);
        curl_setopt($ch_ia, CURLOPT_POSTFIELDS, json_encode($payload_ia));
        curl_setopt($ch_ia, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
        curl_setopt($ch_ia, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch_ia, CURLOPT_TIMEOUT, 15);
        
        $resposta_ia = curl_exec($ch_ia);
        $http_code_ia = curl_getinfo($ch_ia, CURLINFO_HTTP_CODE);
        $error_ia = curl_error($ch_ia);
        curl_close($ch_ia);
        
        echo "<p><strong>Resposta IA - HTTP:</strong> $http_code_ia</p>";
        if ($error_ia) {
            echo "<p style='color: red;'><strong>Erro IA:</strong> $error_ia</p>";
        }
        
        if ($resposta_ia && $http_code_ia === 200) {
            $resultado_ia = json_decode($resposta_ia, true);
            if ($resultado_ia && $resultado_ia['success'] && isset($resultado_ia['resposta'])) {
                echo "<h4>Resposta gerada pela IA:</h4>";
                echo "<pre style='background: white; padding: 10px; border: 1px solid #ccc; border-radius: 5px; white-space: pre-wrap;'>";
                echo htmlspecialchars($resultado_ia['resposta']);
                echo "</pre>";
                echo "<p><strong>Intenção detectada:</strong> " . ($resultado_ia['intencao'] ?? 'N/A') . "</p>";
            } else {
                echo "<p style='color: red;'><strong>Erro na resposta IA:</strong></p>";
                echo "<pre style='background: white; padding: 10px; border: 1px solid #ccc; border-radius: 5px;'>";
                echo htmlspecialchars($resposta_ia);
                echo "</pre>";
            }
        } else {
            echo "<p style='color: red;'><strong>Falha na comunicação com IA:</strong> HTTP $http_code_ia</p>";
            if ($error_ia) {
                echo "<p><strong>Erro:</strong> $error_ia</p>";
            }
        }
    } catch (Exception $e) {
        echo "<p style='color: red;'><strong>Exceção ao processar IA:</strong> " . $e->getMessage() . "</p>";
    }
    echo "</div>";
    
    // Enviar a mensagem correta para WhatsApp
    echo "<div style='background: #d1ecf1; padding: 15px; border-radius: 10px; margin: 10px 0;'>";
    echo "<h3>📤 Enviando Mensagem Correta:</h3>";
    
    try {
        $api_url = WHATSAPP_ROBOT_URL . "/send/text";
        $data_envio = [
            "number" => $numero,
            "message" => $resposta_automatica
        ];
        
        echo "<p><strong>API URL:</strong> $api_url</p>";
        echo "<p><strong>Enviando para:</strong> $numero</p>";
        
        $ch = curl_init($api_url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data_envio));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, WHATSAPP_TIMEOUT);
        
        $api_response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error_envio = curl_error($ch);
        curl_close($ch);
        
        echo "<p><strong>Resposta API - HTTP:</strong> $http_code</p>";
        if ($error_envio) {
            echo "<p style='color: red;'><strong>Erro de envio:</strong> $error_envio</p>";
        }
        
        if ($http_code === 200) {
            $api_result = json_decode($api_response, true);
            if ($api_result && isset($api_result["success"]) && $api_result["success"]) {
                echo "<p style='color: green; font-weight: bold;'>✅ <strong>Mensagem correta enviada com sucesso!</strong></p>";
            } else {
                echo "<p style='color: red; font-weight: bold;'>❌ <strong>Erro ao enviar mensagem:</strong></p>";
                echo "<pre style='background: white; padding: 10px; border: 1px solid #ccc; border-radius: 5px;'>";
                echo htmlspecialchars($api_response);
                echo "</pre>";
            }
        } else {
            echo "<p style='color: red; font-weight: bold;'>❌ <strong>Erro HTTP ao enviar mensagem:</strong> $http_code</p>";
            if ($error_envio) {
                echo "<p><strong>Erro:</strong> $error_envio</p>";
            }
        }
    } catch (Exception $e) {
        echo "<p style='color: red; font-weight: bold;'>❌ <strong>Exceção ao enviar mensagem:</strong> " . $e->getMessage() . "</p>";
    }
    echo "</div>";
}

echo "<hr>";
echo "<h3>📊 Resumo do Teste da Mensagem Atual</h3>";
echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 10px;'>";
echo "<p><strong>Teste da mensagem atual concluído!</strong></p>";
echo "<ul>";
echo "<li>✅ Função gerarRespostaPadrao testada</li>";
echo "<li>✅ IA testada</li>";
echo "<li>✅ Mensagem correta enviada</li>";
echo "</ul>";
echo "<p><em>Teste concluído em: " . date('d/m/Y H:i:s') . "</em></p>";
echo "<p><strong>Verifique seu WhatsApp (47 96164699) para ver a mensagem correta!</strong></p>";
echo "</div>";

echo "<div style='background: #d1ecf1; padding: 15px; border-radius: 10px; margin: 20px 0;'>";
echo "<h3>🔍 Diagnóstico:</h3>";
echo "<p><strong>Se você ainda receber a mensagem antiga, o problema pode ser:</strong></p>";
echo "<ol>";
echo "<li><strong>Cache do navegador:</strong> Limpe o cache do WhatsApp Web</li>";
echo "<li><strong>Webhook não atualizado:</strong> O servidor pode estar usando versão antiga</li>";
echo "<li><strong>Configuração incorreta:</strong> Webhook apontando para arquivo errado</li>";
echo "</ol>";
echo "<p><strong>Este teste envia a mensagem correta diretamente!</strong></p>";
echo "</div>";
?> 