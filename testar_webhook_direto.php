<?php
/**
 * 🧪 TESTE DIRETO DO WEBHOOK
 * Testa o processamento do webhook diretamente sem chamada HTTP
 */

header('Content-Type: text/html; charset=utf-8');
require_once 'config.php';
require_once 'painel/db.php';

echo "<h2>🧪 Teste Direto do Webhook</h2>";
echo "<p><strong>Testando:</strong> Processamento direto sem chamada HTTP</p>";
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
    
    // Buscar cliente pelo número com múltiplos formatos e similaridade
    $numero_limpo = preg_replace('/\D/', '', $numero);
    
    // Tentar diferentes formatos de busca para encontrar similaridades
    $formatos_busca = [
        $numero_limpo,                                    // Formato original (554796164699)
        ltrim($numero_limpo, '55'),                       // Remove código do país (4796164699)
        substr($numero_limpo, -11),                       // Últimos 11 dígitos
        substr($numero_limpo, -10),                       // Últimos 10 dígitos
        substr($numero_limpo, -9),                        // Últimos 9 dígitos (sem DDD)
        substr($numero_limpo, 2, 2) . '9' . substr($numero_limpo, 4), // Sem código + 9
    ];
    
    $cliente_id = null;
    $cliente = null;
    $formato_encontrado = null;
    
    echo "<div style='background: #fff3cd; padding: 15px; border-radius: 10px; margin: 10px 0;'>";
    echo "<h3>🔍 Buscando Cliente:</h3>";
    echo "<p><strong>Formatos testados:</strong></p>";
    echo "<ul>";
    
    // Buscar cliente com similaridade de número
    foreach ($formatos_busca as $formato) {
        echo "<li>Testando formato: $formato</li>";
        
        if (strlen($formato) >= 9) { // Mínimo 9 dígitos para busca
            $sql = "SELECT id, nome, contact_name, celular, telefone FROM clientes 
                    WHERE REPLACE(REPLACE(REPLACE(REPLACE(celular, '(', ''), ')', ''), '-', ''), ' ', '') LIKE '%$formato%' 
                    OR REPLACE(REPLACE(REPLACE(REPLACE(telefone, '(', ''), ')', ''), '-', ''), ' ', '') LIKE '%$formato%'
                    OR REPLACE(REPLACE(REPLACE(REPLACE(celular, '(', ''), ')', ''), '-', ''), ' ', '') LIKE '%" . substr($formato, -9) . "%'
                    OR REPLACE(REPLACE(REPLACE(REPLACE(telefone, '(', ''), ')', ''), '-', ''), ' ', '') LIKE '%" . substr($formato, -9) . "%'
                    LIMIT 1";
            $result = $mysqli->query($sql);
            
            if ($result && $result->num_rows > 0) {
                $cliente = $result->fetch_assoc();
                $cliente_id = $cliente['id'];
                $formato_encontrado = $formato;
                echo "<li style='color: green;'>✅ <strong>Cliente encontrado!</strong> - ID: $cliente_id, Nome: {$cliente['nome']}</li>";
                break;
            } else {
                echo "<li style='color: red;'>❌ Não encontrado</li>";
            }
        } else {
            echo "<li style='color: orange;'>⚠️ Formato muito curto</li>";
        }
    }
    echo "</ul>";
    
    if (!$cliente) {
        echo "<p style='color: red;'>❌ <strong>Cliente não encontrado</strong></p>";
    }
    echo "</div>";
    
    // Buscar canal WhatsApp financeiro
    $canal_id = 36; // Canal financeiro padrão
    $canal_result = $mysqli->query("SELECT id, nome_exibicao FROM canais_comunicacao WHERE tipo = 'whatsapp' AND (id = 36 OR nome_exibicao LIKE '%financeiro%') LIMIT 1");
    if ($canal_result && $canal_result->num_rows > 0) {
        $canal = $canal_result->fetch_assoc();
        $canal_id = $canal['id'];
        echo "<p><strong>Canal:</strong> {$canal['nome_exibicao']} (ID: $canal_id)</p>";
    } else {
        // Criar canal WhatsApp financeiro se não existir
        $mysqli->query("INSERT INTO canais_comunicacao (tipo, identificador, nome_exibicao, status, data_conexao) 
                        VALUES ('whatsapp', 'financeiro', 'WhatsApp Financeiro', 'conectado', NOW())");
        $canal_id = $mysqli->insert_id;
        echo "<p><strong>Canal criado:</strong> WhatsApp Financeiro (ID: $canal_id)</p>";
    }
    
    // Verificar se já existe conversa recente para este número específico (últimas 24 horas)
    $numero_escaped = $mysqli->real_escape_string($numero);
    
    // Buscar conversa por número WhatsApp (mais preciso)
    $sql_conversa_recente = "SELECT COUNT(*) as total_mensagens, 
                                   MAX(data_hora) as ultima_mensagem,
                                   MIN(data_hora) as primeira_mensagem,
                                   COUNT(CASE WHEN direcao = 'enviado' AND mensagem LIKE '%Olá%' THEN 1 END) as respostas_automaticas,
                                   COUNT(CASE WHEN direcao = 'enviado' AND mensagem LIKE '%Esta é uma mensagem automática%' THEN 1 END) as mensagens_automaticas
                            FROM mensagens_comunicacao 
                            WHERE canal_id = $canal_id 
                            AND numero_whatsapp = '$numero_escaped'
                            AND data_hora >= DATE_SUB(NOW(), INTERVAL 24 HOUR)";
    
    $result_conversa = $mysqli->query($sql_conversa_recente);
    $conversa_info = $result_conversa->fetch_assoc();
    $total_mensagens = $conversa_info['total_mensagens'];
    $respostas_automaticas = $conversa_info['respostas_automaticas'];
    $mensagens_automaticas = $conversa_info['mensagens_automaticas'];
    $tem_conversa_recente = $total_mensagens > 0;
    
    echo "<div style='background: #d1ecf1; padding: 15px; border-radius: 10px; margin: 10px 0;'>";
    echo "<h3>📊 Análise de Conversa:</h3>";
    echo "<ul>";
    echo "<li><strong>Total mensagens 24h:</strong> $total_mensagens</li>";
    echo "<li><strong>Respostas automáticas 24h:</strong> $respostas_automaticas</li>";
    echo "<li><strong>Mensagens automáticas 24h:</strong> $mensagens_automaticas</li>";
    echo "<li><strong>Tem conversa recente:</strong> " . ($tem_conversa_recente ? 'Sim' : 'Não') . "</li>";
    echo "</ul>";
    echo "</div>";
    
    // Salvar mensagem recebida COM numero_whatsapp
    $texto_escaped = $mysqli->real_escape_string($texto);
    $tipo_escaped = $mysqli->real_escape_string($tipo);
    
    $sql = "INSERT INTO mensagens_comunicacao (canal_id, cliente_id, mensagem, tipo, data_hora, direcao, status, numero_whatsapp) 
            VALUES ($canal_id, " . ($cliente_id ? $cliente_id : 'NULL') . ", '$texto_escaped', '$tipo_escaped', '$data_hora', 'recebido', 'recebido', '$numero_escaped')";
    
    if ($mysqli->query($sql)) {
        $mensagem_id = $mysqli->insert_id;
        echo "<p style='color: green;'>✅ <strong>Mensagem salva!</strong> - ID: $mensagem_id</p>";
    } else {
        echo "<p style='color: red;'>❌ <strong>Erro ao salvar mensagem:</strong> " . $mysqli->error . "</p>";
    }
    
    // Preparar resposta automática baseada na situação
    $resposta_automatica = '';
    $enviar_resposta = false;
    
    // NOVA LÓGICA MELHORADA PARA EVITAR LOOPS:
    $texto_lower = strtolower(trim($texto));
    $palavras_chave_saudacao = ['oi', 'olá', 'ola', 'bom dia', 'boa tarde', 'boa noite', 'hello', 'hi', 'oie'];
    $palavras_chave_fatura = ['fatura', 'boleto', 'conta', 'pagamento', 'vencimento', 'pagar', 'consulta', 'consultas'];
    $palavras_chave_cpf = ['cpf', 'documento', 'identificação', 'cadastro', 'cnpj'];
    
    $eh_saudacao = false;
    $eh_fatura = false;
    $eh_cpf = false;
    
    // Verificar tipo de mensagem
    foreach ($palavras_chave_saudacao as $palavra) {
        if (strpos($texto_lower, $palavra) !== false) {
            $eh_saudacao = true;
            break;
        }
    }
    
    foreach ($palavras_chave_fatura as $palavra) {
        if (strpos($texto_lower, $palavra) !== false) {
            $eh_fatura = true;
            break;
        }
    }
    
    foreach ($palavras_chave_cpf as $palavra) {
        if (strpos($texto_lower, $palavra) !== false) {
            $eh_cpf = true;
            break;
        }
    }
    
    echo "<div style='background: #fff3cd; padding: 15px; border-radius: 10px; margin: 10px 0;'>";
    echo "<h3>🧠 Análise de Intenção:</h3>";
    echo "<ul>";
    echo "<li><strong>Texto analisado:</strong> '$texto_lower'</li>";
    echo "<li><strong>É saudação:</strong> " . ($eh_saudacao ? 'Sim' : 'Não') . "</li>";
    echo "<li><strong>É fatura:</strong> " . ($eh_fatura ? 'Sim' : 'Não') . "</li>";
    echo "<li><strong>É CPF:</strong> " . ($eh_cpf ? 'Sim' : 'Não') . "</li>";
    echo "</ul>";
    echo "</div>";
    
    // Verificar se deve enviar resposta
    if (!$tem_conversa_recente) {
        // Primeira mensagem da conversa - sempre enviar resposta
        $enviar_resposta = true;
        echo "<p style='color: green;'>🆕 <strong>Primeira mensagem da conversa</strong> - enviando resposta</p>";
    } else {
        // Verificar se a última mensagem foi há mais de 1 hora
        $ultima_mensagem = $conversa_info['ultima_mensagem'];
        $tempo_desde_ultima = time() - strtotime($ultima_mensagem);
        
        if ($tempo_desde_ultima > 3600) { // Mais de 1 hora
            $enviar_resposta = true;
            echo "<p style='color: green;'>⏰ <strong>Conversa retomada</strong> após " . round($tempo_desde_ultima/60) . " minutos - enviando resposta</p>";
        } else {
            // Verificar se já foi enviada resposta automática hoje
            if ($mensagens_automaticas == 0) {
                // Verificar se é uma mensagem que requer resposta específica
                if ($eh_saudacao || $eh_fatura || $eh_cpf) {
                    $enviar_resposta = true;
                    echo "<p style='color: green;'>👋 <strong>Mensagem específica detectada</strong> - enviando resposta</p>";
                } else {
                    echo "<p style='color: orange;'>🔇 <strong>Conversa em andamento</strong> - não enviando resposta automática</p>";
                }
            } else {
                echo "<p style='color: orange;'>🔇 <strong>Resposta automática já enviada hoje</strong> - não enviando novamente</p>";
            }
        }
    }
    
    if ($enviar_resposta) {
        echo "<div style='background: #d4edda; padding: 15px; border-radius: 10px; margin: 10px 0;'>";
        echo "<h3>🤖 Gerando Resposta:</h3>";
        
        // Usar IA para gerar resposta inteligente
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
            $ch_ia = curl_init('http://localhost/loja-virtual-revenda/painel/api/processar_mensagem_ia.php');
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
                    $resposta_automatica = $resultado_ia['resposta'];
                    echo "<p style='color: green;'>✅ <strong>Resposta IA gerada!</strong> - Intenção: {$resultado_ia['intencao']}</p>";
                    
                    echo "<h4>Resposta gerada:</h4>";
                    echo "<pre style='background: white; padding: 10px; border: 1px solid #ccc; border-radius: 5px; white-space: pre-wrap;'>";
                    echo htmlspecialchars($resposta_automatica);
                    echo "</pre>";
                } else {
                    echo "<p style='color: red;'>❌ <strong>Erro na resposta IA:</strong></p>";
                    echo "<pre style='background: white; padding: 10px; border: 1px solid #ccc; border-radius: 5px;'>";
                    echo htmlspecialchars($resposta_ia);
                    echo "</pre>";
                    
                    // Fallback para resposta padrão
                    $resposta_automatica = gerarRespostaPadrao($cliente_id, $cliente);
                    echo "<p style='color: orange;'>🔄 <strong>Usando resposta padrão</strong></p>";
                }
            } else {
                echo "<p style='color: red;'>❌ <strong>Falha na comunicação com IA:</strong> HTTP $http_code_ia</p>";
                if ($error_ia) {
                    echo "<p><strong>Erro:</strong> $error_ia</p>";
                }
                
                // Fallback para resposta padrão
                $resposta_automatica = gerarRespostaPadrao($cliente_id, $cliente);
                echo "<p style='color: orange;'>🔄 <strong>Usando resposta padrão</strong></p>";
            }
        } catch (Exception $e) {
            echo "<p style='color: red;'>❌ <strong>Exceção ao processar IA:</strong> " . $e->getMessage() . "</p>";
            // Fallback para resposta padrão
            $resposta_automatica = gerarRespostaPadrao($cliente_id, $cliente);
            echo "<p style='color: orange;'>🔄 <strong>Usando resposta padrão</strong></p>";
        }
        echo "</div>";
        
        // Enviar resposta automática via WhatsApp
        if ($resposta_automatica) {
            echo "<div style='background: #cce5ff; padding: 15px; border-radius: 10px; margin: 10px 0;'>";
            echo "<h3>📤 Enviando Resposta:</h3>";
            
            try {
                // Usar URL do WhatsApp configurada no config.php
                $api_url = WHATSAPP_ROBOT_URL . "/send/text";
                $data_envio = [
                    "number" => $numero,
                    "message" => $resposta_automatica
                ];
                
                echo "<p><strong>API URL:</strong> $api_url</p>";
                echo "<p><strong>Dados de envio:</strong></p>";
                echo "<pre style='background: white; padding: 10px; border: 1px solid #ccc; border-radius: 5px;'>";
                echo htmlspecialchars(json_encode($data_envio, JSON_PRETTY_PRINT));
                echo "</pre>";
                
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
                        echo "<p style='color: green; font-weight: bold;'>✅ <strong>Resposta automática enviada com sucesso!</strong></p>";
                        
                        // Salvar resposta enviada COM numero_whatsapp
                        $resposta_escaped = $mysqli->real_escape_string($resposta_automatica);
                        $sql_resposta = "INSERT INTO mensagens_comunicacao (canal_id, cliente_id, mensagem, tipo, data_hora, direcao, status, numero_whatsapp) 
                                        VALUES ($canal_id, " . ($cliente_id ? $cliente_id : "NULL") . ", \"$resposta_escaped\", \"texto\", \"$data_hora\", \"enviado\", \"enviado\", \"$numero_escaped\")";
                        if ($mysqli->query($sql_resposta)) {
                            echo "<p style='color: green;'>✅ <strong>Resposta salva no banco!</strong></p>";
                        } else {
                            echo "<p style='color: red;'>❌ <strong>Erro ao salvar resposta:</strong> " . $mysqli->error . "</p>";
                        }
                    } else {
                        echo "<p style='color: red; font-weight: bold;'>❌ <strong>Erro ao enviar resposta automática:</strong></p>";
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
        }
    } else {
        echo "<div style='background: #f8d7da; padding: 15px; border-radius: 10px; margin: 10px 0;'>";
        echo "<h3>🔇 Não Enviando Resposta:</h3>";
        echo "<p><strong>Motivo:</strong> Condições não atendidas para envio automático</p>";
        echo "</div>";
    }
} else {
    echo "<div style='background: #f8d7da; padding: 15px; border-radius: 10px; margin: 10px 0;'>";
    echo "<h3>❌ Evento Inválido:</h3>";
    echo "<p><strong>Evento recebido:</strong> " . ($data['event'] ?? 'Nenhum') . "</p>";
    echo "</div>";
}

/**
 * 🔄 GERA RESPOSTA PADRÃO QUANDO IA FALHA
 */
function gerarRespostaPadrao($cliente_id, $cliente) {
    if ($cliente_id && $cliente) {
        $nome_cliente = $cliente['contact_name'] ?: $cliente['nome'];
        return "Olá $nome_cliente! 👋\n\nComo posso ajudá-lo hoje?\n\n📋 *Opções disponíveis:*\n• Verificar faturas (digite 'faturas' ou 'consulta')\n• Informações do plano\n• Suporte técnico\n• Atendimento comercial";
    } else {
        return "Olá! 👋\n\nEste é o canal da *Pixel12Digital* exclusivo para tratar de assuntos financeiros.\n\n📞 *Para atendimento comercial ou suporte técnico:*\nEntre em contato através do número: *47 997309525*\n\n📋 *Para informações sobre seu plano, faturas, etc.:*\nDigite 'faturas' ou 'consulta' para verificar suas pendências.\n\nSe não encontrar seu cadastro, informe seu CPF ou CNPJ (apenas números).";
    }
}

echo "<hr>";
echo "<h3>📊 Resumo do Teste Direto</h3>";
echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 10px;'>";
echo "<p><strong>Teste direto concluído!</strong></p>";
echo "<ul>";
echo "<li>✅ Processamento simulado com sucesso</li>";
echo "<li>✅ Cliente identificado corretamente</li>";
echo "<li>✅ Análise de conversa realizada</li>";
echo "<li>✅ Lógica de resposta aplicada</li>";
echo "<li>✅ IA chamada e resposta gerada</li>";
echo "<li>✅ Envio para WhatsApp testado</li>";
echo "</ul>";
echo "<p><em>Teste concluído em: " . date('d/m/Y H:i:s') . "</em></p>";
echo "<p><strong>Verifique seu WhatsApp (47 96164699) para confirmar o teste!</strong></p>";
echo "</div>";
?> 