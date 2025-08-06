<?php
/**
 * 🔧 CORRIGIR MENSAGENS SEM CLIENTE_ID
 * 
 * Este script corrige mensagens que foram salvas sem cliente_id
 */

echo "🔧 CORRIGINDO MENSAGENS SEM CLIENTE_ID\n";
echo "=====================================\n\n";

require_once 'config.php';
require_once 'painel/db.php';

// ===== 1. VERIFICAR MENSAGENS SEM CLIENTE_ID =====
echo "1️⃣ VERIFICANDO MENSAGENS SEM CLIENTE_ID:\n";
echo "=========================================\n";

$sql_sem_cliente = "SELECT id, numero_whatsapp, mensagem, data_hora, direcao 
                    FROM mensagens_comunicacao 
                    WHERE cliente_id IS NULL OR cliente_id = 0 
                    ORDER BY id DESC 
                    LIMIT 10";

$result_sem_cliente = $mysqli->query($sql_sem_cliente);

if ($result_sem_cliente && $result_sem_cliente->num_rows > 0) {
    echo "✅ Mensagens sem cliente_id encontradas:\n";
    $mensagens_para_corrigir = [];
    
    while ($row = $result_sem_cliente->fetch_assoc()) {
        echo "   - ID: {$row['id']} | Número: {$row['numero_whatsapp']} | Direção: {$row['direcao']} | Data: {$row['data_hora']}\n";
        echo "     Mensagem: " . substr($row['mensagem'], 0, 50) . "...\n";
        $mensagens_para_corrigir[] = $row;
    }
    
    // ===== 2. CORRIGIR MENSAGENS =====
    echo "\n2️⃣ CORRIGINDO MENSAGENS:\n";
    echo "=========================\n";
    
    $corrigidas = 0;
    $erros = 0;
    
    foreach ($mensagens_para_corrigir as $mensagem) {
        $numero = $mensagem['numero_whatsapp'];
        
        if (empty($numero)) {
            echo "   ⚠️ Mensagem ID {$mensagem['id']} sem número - pulando\n";
            continue;
        }
        
        // Limpar número
        $numero_limpo = preg_replace('/\D/', '', $numero);
        
        if (empty($numero_limpo)) {
            echo "   ⚠️ Mensagem ID {$mensagem['id']} número inválido - pulando\n";
            continue;
        }
        
        // Buscar cliente pelo número
        $sql_buscar = "SELECT id, nome FROM clientes WHERE celular LIKE '%$numero_limpo%' OR telefone LIKE '%$numero_limpo%' LIMIT 1";
        $result_buscar = $mysqli->query($sql_buscar);
        
        if ($result_buscar && $result_buscar->num_rows > 0) {
            $cliente = $result_buscar->fetch_assoc();
            $cliente_id = $cliente['id'];
            
            // Atualizar mensagem
            $sql_update = "UPDATE mensagens_comunicacao SET cliente_id = $cliente_id WHERE id = {$mensagem['id']}";
            
            if ($mysqli->query($sql_update)) {
                echo "   ✅ Mensagem ID {$mensagem['id']} corrigida - Cliente: {$cliente['nome']} (ID: $cliente_id)\n";
                $corrigidas++;
            } else {
                echo "   ❌ Erro ao corrigir mensagem ID {$mensagem['id']}: " . $mysqli->error . "\n";
                $erros++;
            }
        } else {
            // Criar cliente automaticamente
            $nome_cliente = "Cliente WhatsApp (" . $numero_limpo . ")";
            $data_criacao = date("Y-m-d H:i:s");
            $asaas_id = "whatsapp_" . $numero_limpo . "_" . time();
            
            $sql_criar = "INSERT INTO clientes (nome, celular, data_criacao, data_atualizacao, asaas_id) 
                          VALUES (\"" . $mysqli->real_escape_string($nome_cliente) . "\", 
                                  \"" . $mysqli->real_escape_string($numero_limpo) . "\", 
                                  \"$data_criacao\", \"$data_criacao\", \"" . $mysqli->real_escape_string($asaas_id) . "\")";
            
            if ($mysqli->query($sql_criar)) {
                $novo_cliente_id = $mysqli->insert_id;
                
                // Atualizar mensagem
                $sql_update = "UPDATE mensagens_comunicacao SET cliente_id = $novo_cliente_id WHERE id = {$mensagem['id']}";
                
                if ($mysqli->query($sql_update)) {
                    echo "   ✅ Mensagem ID {$mensagem['id']} corrigida - Cliente criado: $nome_cliente (ID: $novo_cliente_id)\n";
                    $corrigidas++;
                } else {
                    echo "   ❌ Erro ao corrigir mensagem ID {$mensagem['id']}: " . $mysqli->error . "\n";
                    $erros++;
                }
            } else {
                echo "   ❌ Erro ao criar cliente para mensagem ID {$mensagem['id']}: " . $mysqli->error . "\n";
                $erros++;
            }
        }
    }
    
    echo "\n📊 RESUMO DA CORREÇÃO:\n";
    echo "   ✅ Mensagens corrigidas: $corrigidas\n";
    echo "   ❌ Erros: $erros\n";
    
} else {
    echo "✅ Nenhuma mensagem sem cliente_id encontrada\n";
}

// ===== 3. VERIFICAR MENSAGENS SEM NÚMERO =====
echo "\n3️⃣ VERIFICANDO MENSAGENS SEM NÚMERO:\n";
echo "=====================================\n";

$sql_sem_numero = "SELECT COUNT(*) as total FROM mensagens_comunicacao WHERE numero_whatsapp IS NULL OR numero_whatsapp = ''";
$result_sem_numero = $mysqli->query($sql_sem_numero);

if ($result_sem_numero) {
    $sem_numero = $result_sem_numero->fetch_assoc();
    echo "📊 Mensagens sem número: {$sem_numero['total']}\n";
    
    if ($sem_numero['total'] > 0) {
        echo "🔧 Para corrigir mensagens sem número, é necessário verificar o webhook\n";
        echo "🔧 As mensagens devem ser salvas com o número do WhatsApp\n";
    }
}

// ===== 4. VERIFICAR MENSAGENS DUPLICADAS =====
echo "\n4️⃣ VERIFICANDO MENSAGENS DUPLICADAS:\n";
echo "=====================================\n";

$sql_duplicadas = "SELECT mensagem, data_hora, COUNT(*) as count 
                   FROM mensagens_comunicacao 
                   GROUP BY mensagem, data_hora 
                   HAVING COUNT(*) > 1
                   ORDER BY count DESC 
                   LIMIT 5";

$result_duplicadas = $mysqli->query($sql_duplicadas);

if ($result_duplicadas && $result_duplicadas->num_rows > 0) {
    echo "📊 Mensagens duplicadas encontradas:\n";
    while ($row = $result_duplicadas->fetch_assoc()) {
        echo "   - Mensagem: " . substr($row['mensagem'], 0, 50) . "... | Data: {$row['data_hora']} | Contagem: {$row['count']}\n";
    }
    
    echo "🔧 Para corrigir duplicatas, execute manualmente:\n";
    echo "   DELETE m1 FROM mensagens_comunicacao m1\n";
    echo "   INNER JOIN mensagens_comunicacao m2\n";
    echo "   WHERE m1.id > m2.id AND m1.mensagem = m2.mensagem AND m1.data_hora = m2.data_hora;\n";
} else {
    echo "✅ Nenhuma mensagem duplicada encontrada\n";
}

// ===== 5. VERIFICAÇÃO FINAL =====
echo "\n5️⃣ VERIFICAÇÃO FINAL:\n";
echo "=====================\n";

$sql_final = "SELECT 
                COUNT(*) as total_mensagens,
                COUNT(CASE WHEN cliente_id IS NULL OR cliente_id = 0 THEN 1 END) as sem_cliente,
                COUNT(CASE WHEN numero_whatsapp IS NULL OR numero_whatsapp = '' THEN 1 END) as sem_numero
              FROM mensagens_comunicacao";

$result_final = $mysqli->query($sql_final);

if ($result_final) {
    $final = $result_final->fetch_assoc();
    echo "📊 ESTATÍSTICAS FINAIS:\n";
    echo "   📄 Total de mensagens: {$final['total_mensagens']}\n";
    echo "   ⚠️ Sem cliente_id: {$final['sem_cliente']}\n";
    echo "   ⚠️ Sem número: {$final['sem_numero']}\n";
    
    if ($final['sem_cliente'] == 0 && $final['sem_numero'] == 0) {
        echo "✅ Todas as mensagens estão corretas!\n";
    } else {
        echo "⚠️ Ainda há mensagens para corrigir\n";
    }
}

echo "\n🎯 CORREÇÃO CONCLUÍDA!\n";
echo "======================\n";
echo "As mensagens agora devem aparecer corretamente no chat.\n";
echo "Se ainda houver problemas, verificar:\n";
echo "1. Se o JavaScript está carregando as mensagens\n";
echo "2. Se há problemas de cache no navegador\n";
echo "3. Se há erros no console do navegador\n";
?> 