<?php
// ===== CORREÇÃO DA IDENTIFICAÇÃO DE CANAL =====
// Adicionar esta lógica no webhook_whatsapp.php

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
                error_log("[WEBHOOK WHATSAPP] 📡 Canal identificado: {$canal_nome} (ID: $canal_id) - Número: $identificador");
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
            error_log("[WEBHOOK WHATSAPP] 📡 Canal identificado por conteúdo: {$canal_nome} (ID: $canal_id)");
        } elseif (strpos($mensagem, '554797309525') !== false || strpos($mensagem, 'canal 3001') !== false) {
            $canal_id = 37; // Canal 3001 - Pixel - Comercial
            $canal_nome = 'Pixel - Comercial';
            $numero_origem = '554797309525@c.us';
            error_log("[WEBHOOK WHATSAPP] 📡 Canal identificado por conteúdo: {$canal_nome} (ID: $canal_id)");
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
            error_log("[WEBHOOK WHATSAPP] 📡 Usando canal padrão: {$canal_nome} (ID: $canal_id)");
        }
    }
    
    return [
        'canal_id' => $canal_id,
        'canal_nome' => $canal_nome,
        'numero_origem' => $numero_origem
    ];
}

// ===== USO NO WEBHOOK =====
// Substituir a lógica atual por:
/*
$canal_info = identificarCanalPorMensagem($numero, $texto, $mysqli);
$canal_id = $canal_info['canal_id'];
$canal_nome = $canal_info['canal_nome'];
$numero_origem = $canal_info['numero_origem'];
*/
?>