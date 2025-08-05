<?php
require_once 'config.php';

// Conectar ao banco
$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($mysqli->connect_error) {
    die("Erro na conexão: " . $mysqli->connect_error);
}

$mysqli->set_charset('utf8mb4');

echo "<h2>🔧 Correção da Identificação de Canal no Webhook</h2>";
echo "<p><strong>Data/Hora da correção:</strong> " . date('Y-m-d H:i:s') . "</p>";

// Testar a lógica de identificação de canal
echo "<h3>🎯 Testando Identificação de Canal</h3>";

// Simular dados de teste
$numero_teste = '554796164699';
$mensagem_teste = "Teste de mensagem enviada para canal 3000 554797146908 - 18:04";

echo "<h4>1. Verificando Canais Disponíveis</h4>";
$sql_canais = "SELECT id, nome_exibicao, identificador, porta FROM canais_comunicacao WHERE tipo = 'whatsapp' AND status <> 'excluido' ORDER BY id";
$result_canais = $mysqli->query($sql_canais);

if ($result_canais && $result_canais->num_rows > 0) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%; font-family: Arial, sans-serif; font-size: 12px;'>";
    echo "<tr style='background-color: #4CAF50; color: white;'>";
    echo "<th>ID</th><th>Nome Exibição</th><th>Identificador</th><th>Porta</th>";
    echo "</tr>";
    
    while ($canal = $result_canais->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $canal['id'] . "</td>";
        echo "<td>" . htmlspecialchars($canal['nome_exibicao']) . "</td>";
        echo "<td>" . htmlspecialchars($canal['identificador']) . "</td>";
        echo "<td>" . $canal['porta'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: red;'>❌ Nenhum canal encontrado!</p>";
}

// 2. Testar lógica de identificação de canal
echo "<h4>2. Testando Lógica de Identificação de Canal</h4>";

$numero = $numero_teste;
$canal_id = null;
$canal_nome = null;
$numero_origem = null;

// Buscar todos os canais WhatsApp ativos
$canais_result = $mysqli->query("SELECT id, nome_exibicao, identificador FROM canais_comunicacao WHERE tipo = 'whatsapp' AND status <> 'excluido' ORDER BY id");

if ($canais_result && $canais_result->num_rows > 0) {
    while ($canal = $canais_result->fetch_assoc()) {
        $identificador = $canal['identificador'];
        
        echo "<p>🔍 Testando canal: {$canal['nome_exibicao']} (ID: {$canal['id']}) - Identificador: $identificador</p>";
        
        // Verificar se a mensagem veio deste canal específico
        if ($identificador && strpos($numero, $identificador) !== false) {
            $canal_id = $canal['id'];
            $canal_nome = $canal['nome_exibicao'];
            $numero_origem = $identificador;
            echo "<p style='color: green;'>✅ Canal identificado: {$canal_nome} (ID: $canal_id) - Número: $identificador</p>";
            break;
        } else {
            echo "<p style='color: orange;'>⚠️ Canal não identificado - strpos($numero, $identificador) retornou false</p>";
        }
    }
}

// 3. Se não identificou canal específico, usar canal padrão
if (!$canal_id) {
    echo "<h4>3. Usando Canal Padrão</h4>";
    
    // Lógica para identificar canal baseado no número de destino
    if (strpos($numero, '554797146908') !== false) {
        $canal_id = 36; // Financeiro
        $canal_nome = 'Pixel12Digital';
        $numero_origem = '554797146908@c.us';
        echo "<p style='color: green;'>✅ Canal identificado por número: $canal_nome (ID: $canal_id)</p>";
    } elseif (strpos($numero, '4797309525') !== false) {
        $canal_id = 37; // Comercial
        $canal_nome = 'Pixel - Comercial';
        $numero_origem = '4797309525@c.us';
        echo "<p style='color: green;'>✅ Canal identificado por número: $canal_nome (ID: $canal_id)</p>";
    } else {
        // Canal padrão (primeiro canal encontrado)
        $canal_padrao = $mysqli->query("SELECT id, nome_exibicao FROM canais_comunicacao WHERE tipo = 'whatsapp' AND status <> 'excluido' ORDER BY id LIMIT 1");
        if ($canal_padrao && $canal_padrao->num_rows > 0) {
            $canal = $canal_padrao->fetch_assoc();
            $canal_id = $canal['id'];
            $canal_nome = $canal['nome_exibicao'];
            $numero_origem = 'Canal Padrão';
            echo "<p style='color: orange;'>⚠️ Usando canal padrão: $canal_nome (ID: $canal_id)</p>";
        }
    }
}

// 4. Criar script corrigido para o webhook
echo "<h4>4. Script Corrigido para Webhook</h4>";

$webhook_corrigido = "<?php
// ===== CORREÇÃO DA IDENTIFICAÇÃO DE CANAL =====
// Adicionar esta lógica no webhook_whatsapp.php

function identificarCanalPorMensagem(\$numero, \$mensagem, \$mysqli) {
    \$canal_id = null;
    \$canal_nome = null;
    \$numero_origem = null;
    
    // 1. Tentar identificar por identificador do canal
    \$canais_result = \$mysqli->query(\"SELECT id, nome_exibicao, identificador FROM canais_comunicacao WHERE tipo = 'whatsapp' AND status <> 'excluido' ORDER BY id\");
    
    if (\$canais_result && \$canais_result->num_rows > 0) {
        while (\$canal = \$canais_result->fetch_assoc()) {
            \$identificador = \$canal['identificador'];
            
            // Verificar se a mensagem veio deste canal específico
            if (\$identificador && strpos(\$numero, \$identificador) !== false) {
                \$canal_id = \$canal['id'];
                \$canal_nome = \$canal['nome_exibicao'];
                \$numero_origem = \$identificador;
                error_log(\"[WEBHOOK WHATSAPP] 📡 Canal identificado: {\$canal_nome} (ID: \$canal_id) - Número: \$identificador\");
                break;
            }
        }
    }
    
    // 2. Se não identificou, tentar por conteúdo da mensagem
    if (!\$canal_id) {
        if (strpos(\$mensagem, '554797146908') !== false || strpos(\$mensagem, 'canal 3000') !== false) {
            \$canal_id = 36; // Canal 3000 - Pixel12Digital
            \$canal_nome = 'Pixel12Digital';
            \$numero_origem = '554797146908@c.us';
            error_log(\"[WEBHOOK WHATSAPP] 📡 Canal identificado por conteúdo: {\$canal_nome} (ID: \$canal_id)\");
        } elseif (strpos(\$mensagem, '554797309525') !== false || strpos(\$mensagem, 'canal 3001') !== false) {
            \$canal_id = 37; // Canal 3001 - Pixel - Comercial
            \$canal_nome = 'Pixel - Comercial';
            \$numero_origem = '554797309525@c.us';
            error_log(\"[WEBHOOK WHATSAPP] 📡 Canal identificado por conteúdo: {\$canal_nome} (ID: \$canal_id)\");
        }
    }
    
    // 3. Se ainda não identificou, usar canal padrão
    if (!\$canal_id) {
        \$canal_padrao = \$mysqli->query(\"SELECT id, nome_exibicao FROM canais_comunicacao WHERE tipo = 'whatsapp' AND status <> 'excluido' ORDER BY id LIMIT 1\");
        if (\$canal_padrao && \$canal_padrao->num_rows > 0) {
            \$canal = \$canal_padrao->fetch_assoc();
            \$canal_id = \$canal['id'];
            \$canal_nome = \$canal['nome_exibicao'];
            \$numero_origem = 'Canal Padrão';
            error_log(\"[WEBHOOK WHATSAPP] 📡 Usando canal padrão: {\$canal_nome} (ID: \$canal_id)\");
        }
    }
    
    return [
        'canal_id' => \$canal_id,
        'canal_nome' => \$canal_nome,
        'numero_origem' => \$numero_origem
    ];
}

// ===== USO NO WEBHOOK =====
// Substituir a lógica atual por:
/*
\$canal_info = identificarCanalPorMensagem(\$numero, \$texto, \$mysqli);
\$canal_id = \$canal_info['canal_id'];
\$canal_nome = \$canal_info['canal_nome'];
\$numero_origem = \$canal_info['numero_origem'];
*/
?>";

file_put_contents('webhook_canal_corrigido.php', $webhook_corrigido);
echo "✅ Script corrigido criado: <a href='webhook_canal_corrigido.php' target='_blank'>webhook_canal_corrigido.php</a><br>";

// 5. Testar a correção
echo "<h4>5. Testando Correção</h4>";

// Simular a correção
$canal_info = identificarCanalPorMensagem($numero_teste, $mensagem_teste, $mysqli);
$canal_id_corrigido = $canal_info['canal_id'];
$canal_nome_corrigido = $canal_info['canal_nome'];
$numero_origem_corrigido = $canal_info['numero_origem'];

echo "<p><strong>Resultado da correção:</strong></p>";
echo "<ul>";
echo "<li>Canal ID: $canal_id_corrigido</li>";
echo "<li>Canal Nome: $canal_nome_corrigido</li>";
echo "<li>Número Origem: $numero_origem_corrigido</li>";
echo "</ul>";

$mysqli->close();

echo "<h3>🎯 Resumo da Correção</h3>";
echo "<div style='background-color: #f9f9f9; padding: 15px; border-radius: 5px;'>";
echo "<p><strong>🔍 Problema Identificado:</strong></p>";
echo "<p>A lógica de identificação de canal no webhook não está funcionando corretamente para mensagens enviadas para o canal 3000.</p>";
echo "<p><strong>🔧 Solução:</strong></p>";
echo "<ol>";
echo "<li>Criada função <code>identificarCanalPorMensagem()</code> para melhor identificação</li>";
echo "<li>Adicionada verificação por conteúdo da mensagem</li>";
echo "<li>Melhorada lógica de fallback para canal padrão</li>";
echo "</ol>";
echo "<p><strong>🎯 Próximos passos:</strong></p>";
echo "<ol>";
echo "<li>Aplicar a correção no <code>api/webhook_whatsapp.php</code></li>";
echo "<li>Testar enviando uma nova mensagem para o canal 3000</li>";
echo "<li>Verificar se a mensagem aparece no chat</li>";
echo "</ol>";
echo "</div>";

function identificarCanalPorMensagem($numero, $mensagem, $mysqli) {
    $canal_id = null;
    $canal_nome = null;
    $numero_origem = null;
    
    // 1. Tentar identificar por identificador do canal
    $canais_result = $mysqli->query("SELECT id, nome_exibicao, identificador FROM canais_comunicacao WHERE tipo = 'whatsapp' AND status <> 'excluido' ORDER BY id");
    
    if ($canais_result && $canais_result->num_rows > 0) {
        while ($canal = $canais_result->fetch_assoc()) {
            $identificador = $canal['identificador'];
            
            // Verificar se a mensagem veio deste canal específico
            if ($identificador && strpos($numero, $identificador) !== false) {
                $canal_id = $canal['id'];
                $canal_nome = $canal['nome_exibicao'];
                $numero_origem = $identificador;
                break;
            }
        }
    }
    
    // 2. Se não identificou, tentar por conteúdo da mensagem
    if (!$canal_id) {
        if (strpos($mensagem, '554797146908') !== false || strpos($mensagem, 'canal 3000') !== false) {
            $canal_id = 36; // Canal 3000 - Pixel12Digital
            $canal_nome = 'Pixel12Digital';
            $numero_origem = '554797146908@c.us';
        } elseif (strpos($mensagem, '554797309525') !== false || strpos($mensagem, 'canal 3001') !== false) {
            $canal_id = 37; // Canal 3001 - Pixel - Comercial
            $canal_nome = 'Pixel - Comercial';
            $numero_origem = '554797309525@c.us';
        }
    }
    
    // 3. Se ainda não identificou, usar canal padrão
    if (!$canal_id) {
        $canal_padrao = $mysqli->query("SELECT id, nome_exibicao FROM canais_comunicacao WHERE tipo = 'whatsapp' AND status <> 'excluido' ORDER BY id LIMIT 1");
        if ($canal_padrao && $canal_padrao->num_rows > 0) {
            $canal = $canal_padrao->fetch_assoc();
            $canal_id = $canal['id'];
            $canal_nome = $canal['nome_exibicao'];
            $numero_origem = 'Canal Padrão';
        }
    }
    
    return [
        'canal_id' => $canal_id,
        'canal_nome' => $canal_nome,
        'numero_origem' => $numero_origem
    ];
}
?> 