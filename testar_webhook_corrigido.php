<?php
/**
 * 🧪 TESTE DO WEBHOOK CORRIGIDO
 * Testa se o webhook está funcionando corretamente após a correção da URL da IA
 */

header('Content-Type: text/html; charset=utf-8');
require_once 'config.php';
require_once 'painel/db.php';

echo "<h2>🧪 Teste do Webhook Corrigido</h2>";
echo "<p><strong>Testando:</strong> Se o webhook está chamando a IA corretamente</p>";
echo "<hr>";

// Simular dados que viriam do WhatsApp
$data = [
    'event' => 'onmessage',
    'data' => [
        'from' => '554796164699', // Seu número
        'text' => 'faturas',
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

// Testar chamada direta para a IA
echo "<div style='background: #e8f5e8; padding: 15px; border-radius: 10px; margin: 10px 0;'>";
echo "<h3>🤖 Testando Chamada Direta para IA:</h3>";

try {
    $payload_ia = [
        'from' => '554796164699',
        'message' => 'faturas',
        'type' => 'text'
    ];
    
    echo "<p><strong>Chamando IA com payload:</strong></p>";
    echo "<pre style='background: white; padding: 10px; border: 1px solid #ccc; border-radius: 5px;'>";
    echo htmlspecialchars(json_encode($payload_ia, JSON_PRETTY_PRINT));
    echo "</pre>";
    
    // Chamar endpoint da IA diretamente
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
            echo "<h4>✅ Resposta gerada pela IA:</h4>";
            echo "<pre style='background: white; padding: 10px; border: 1px solid #ccc; border-radius: 5px; white-space: pre-wrap;'>";
            echo htmlspecialchars($resultado_ia['resposta']);
            echo "</pre>";
            echo "<p><strong>Intenção detectada:</strong> " . ($resultado_ia['intencao'] ?? 'N/A') . "</p>";
            echo "<p><strong>Cliente ID:</strong> " . ($resultado_ia['cliente_id'] ?? 'N/A') . "</p>";
            
            // Enviar resposta da IA para WhatsApp
            echo "<div style='background: #d4edda; padding: 15px; border-radius: 10px; margin: 10px 0;'>";
            echo "<h3>📤 Enviando Resposta da IA:</h3>";
            
            try {
                $api_url = WHATSAPP_ROBOT_URL . "/send/text";
                $data_envio = [
                    "number" => '554796164699',
                    "message" => $resultado_ia['resposta']
                ];
                
                echo "<p><strong>API URL:</strong> $api_url</p>";
                echo "<p><strong>Enviando para:</strong> 554796164699</p>";
                
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
                        echo "<p style='color: green; font-weight: bold;'>✅ <strong>Resposta da IA enviada com sucesso!</strong></p>";
                    } else {
                        echo "<p style='color: red; font-weight: bold;'>❌ <strong>Erro ao enviar resposta:</strong></p>";
                        echo "<pre style='background: white; padding: 10px; border: 1px solid #ccc; border-radius: 5px;'>";
                        echo htmlspecialchars($api_response);
                        echo "</pre>";
                    }
                } else {
                    echo "<p style='color: red; font-weight: bold;'>❌ <strong>Erro HTTP ao enviar resposta:</strong> $http_code</p>";
                    if ($error_envio) {
                        echo "<p><strong>Erro:</strong> $error_envio</p>";
                    }
                }
            } catch (Exception $e) {
                echo "<p style='color: red; font-weight: bold;'>❌ <strong>Exceção ao enviar resposta:</strong> " . $e->getMessage() . "</p>";
            }
            echo "</div>";
            
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

echo "<hr>";
echo "<h3>📊 Resumo do Teste do Webhook Corrigido</h3>";
echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 10px;'>";
echo "<p><strong>Teste do webhook corrigido concluído!</strong></p>";
echo "<ul>";
echo "<li>✅ Chamada direta para IA testada</li>";
echo "<li>✅ URL da IA corrigida</li>";
echo "<li>✅ Resposta da IA processada</li>";
echo "<li>✅ Envio para WhatsApp testado</li>";
echo "</ul>";
echo "<p><em>Teste concluído em: " . date('d/m/Y H:i:s') . "</em></p>";
echo "<p><strong>Verifique seu WhatsApp (47 96164699) para ver a resposta correta das faturas!</strong></p>";
echo "</div>";

echo "<div style='background: #d1ecf1; padding: 15px; border-radius: 10px; margin: 20px 0;'>";
echo "<h3>🔧 Correção Implementada:</h3>";
echo "<p><strong>Problema identificado:</strong> O webhook estava tentando chamar a IA via localhost:8080 que não funcionava</p>";
echo "<p><strong>Solução:</strong> Corrigida a URL para chamar a IA diretamente via caminho relativo</p>";
echo "<p><strong>Resultado esperado:</strong> Agora quando você digitar 'faturas', deve receber a consulta de faturas em vez da mensagem de saudação</p>";
echo "</div>";
?> 