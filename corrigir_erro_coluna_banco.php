<?php
/**
 * 🔧 CORRIGIR ERRO DE COLUNA - BANCO DE DADOS (VERSÃO PRODUÇÃO)
 * 
 * Corrige o erro "Unknown column 'telefone_origem' in 'INSERT INTO'"
 * Inclui backup automático e transações para segurança
 */

// Verificar se config.php está no diretório correto
$config_path = __DIR__ . '/config.php';
if (!file_exists($config_path)) {
    die("❌ ERRO: config.php não encontrado em " . __DIR__ . "\n");
}

require_once $config_path;

echo "=== 🔧 CORREÇÃO DE ERRO DE COLUNA (PRODUÇÃO) ===\n";
echo "Data/Hora: " . date('Y-m-d H:i:s') . "\n";
echo "Diretório: " . __DIR__ . "\n\n";

try {
    $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($mysqli->connect_error) {
        die("❌ Erro de conexão: " . $mysqli->connect_error . "\n");
    }
    
    echo "✅ Conectado ao banco de dados: " . DB_NAME . "\n\n";
    
    // ===== 1. VERIFICAR ESTRUTURA ATUAL =====
    echo "1. 📋 VERIFICANDO ESTRUTURA DA TABELA:\n";
    
    $result = $mysqli->query("DESCRIBE mensagens_comunicacao");
    if (!$result) {
        throw new Exception("Erro ao verificar estrutura: " . $mysqli->error);
    }
    
    $colunas = [];
    while ($row = $result->fetch_assoc()) {
        $colunas[] = $row['Field'];
        echo "   • {$row['Field']} ({$row['Type']})\n";
    }
    
    // Verificar se a coluna existe
    if (in_array('telefone_origem', $colunas)) {
        echo "\n   ✅ Coluna 'telefone_origem' já existe!\n";
        $coluna_existe = true;
    } else {
        echo "\n   ❌ Coluna 'telefone_origem' não encontrada\n";
        $coluna_existe = false;
    }
    
    echo "\n";
    
    // ===== 2. BACKUP AUTOMÁTICO (se necessário alterar) =====
    if (!$coluna_existe) {
        echo "2. 💾 CRIANDO BACKUP AUTOMÁTICO:\n";
        
        $backup_table = 'mensagens_comunicacao_backup_' . date('Ymd_His');
        $sql_backup = "CREATE TABLE $backup_table AS SELECT * FROM mensagens_comunicacao";
        
        if ($mysqli->query($sql_backup)) {
            // Verificar quantos registros foram copiados
            $count_result = $mysqli->query("SELECT COUNT(*) as total FROM $backup_table");
            $count = $count_result->fetch_assoc()['total'];
            echo "   ✅ Backup criado: $backup_table ($count registros)\n";
        } else {
            throw new Exception("Erro ao criar backup: " . $mysqli->error);
        }
        
        echo "\n";
    }
    
    // ===== 3. ADICIONAR COLUNA COM TRANSAÇÃO =====
    if (!$coluna_existe) {
        echo "3. 🔧 ADICIONANDO COLUNA 'telefone_origem' (COM TRANSAÇÃO):\n";
        
        // Iniciar transação
        $mysqli->begin_transaction();
        
        try {
            $sql = "ALTER TABLE mensagens_comunicacao ADD COLUMN telefone_origem VARCHAR(20) AFTER numero_whatsapp";
            
            if ($mysqli->query($sql)) {
                echo "   ✅ Coluna 'telefone_origem' adicionada com sucesso!\n";
                
                // Verificar se foi realmente adicionada
                $check_result = $mysqli->query("SHOW COLUMNS FROM mensagens_comunicacao LIKE 'telefone_origem'");
                if ($check_result && $check_result->num_rows > 0) {
                    echo "   ✅ Verificação: Coluna confirmada no banco\n";
                } else {
                    throw new Exception("Coluna não foi adicionada corretamente");
                }
                
                // Commit da transação
                $mysqli->commit();
                echo "   ✅ Transação confirmada\n";
                
            } else {
                throw new Exception("Erro ao adicionar coluna: " . $mysqli->error);
            }
            
        } catch (Exception $e) {
            // Rollback em caso de erro
            $mysqli->rollback();
            throw new Exception("Erro na transação: " . $e->getMessage());
        }
        
        echo "\n";
    }
    
    // ===== 4. VERIFICAR OUTRAS COLUNAS RELACIONADAS =====
    echo "4. 🔍 VERIFICANDO OUTRAS COLUNAS RELACIONADAS:\n";
    
    $colunas_whatsapp = [
        'numero_whatsapp',
        'telefone_origem', 
        'whatsapp_message_id',
        'from_number',
        'to_number',
        'sender',
        'recipient'
    ];
    
    foreach ($colunas_whatsapp as $coluna) {
        $result = $mysqli->query("SHOW COLUMNS FROM mensagens_comunicacao LIKE '$coluna'");
        if ($result && $result->num_rows > 0) {
            echo "   ✅ $coluna: Existe\n";
        } else {
            echo "   ❌ $coluna: Não existe\n";
        }
    }
    
    echo "\n";
    
    // ===== 5. VERIFICAR CÓDIGO QUE USA A COLUNA =====
    echo "5. 🔍 VERIFICANDO CÓDIGO QUE USA 'telefone_origem':\n";
    
    $arquivos_para_verificar = [
        'painel/receber_mensagem_ana_local.php',
        'painel/receber_mensagem_ana.php',
        'painel/receber_mensagem.php'
    ];
    
    $arquivos_com_erro = [];
    
    foreach ($arquivos_para_verificar as $arquivo) {
        if (file_exists($arquivo)) {
            $conteudo = file_get_contents($arquivo);
            if (strpos($conteudo, 'telefone_origem') !== false) {
                echo "   📄 $arquivo: Usa 'telefone_origem'\n";
                $arquivos_com_erro[] = $arquivo;
                
                // Mostrar linha específica
                $linhas = explode("\n", $conteudo);
                foreach ($linhas as $num => $linha) {
                    if (strpos($linha, 'telefone_origem') !== false) {
                        echo "      Linha " . ($num + 1) . ": " . trim($linha) . "\n";
                    }
                }
            } else {
                echo "   📄 $arquivo: Não usa 'telefone_origem'\n";
            }
        } else {
            echo "   📄 $arquivo: Arquivo não encontrado\n";
        }
    }
    
    echo "\n";
    
    // ===== 6. TESTAR INSERÇÃO COM TRANSAÇÃO =====
    echo "6. 🧪 TESTANDO INSERÇÃO COM NOVA COLUNA:\n";
    
    $mysqli->begin_transaction();
    
    try {
        $sql_teste = "INSERT INTO mensagens_comunicacao 
                      (canal_id, numero_whatsapp, telefone_origem, mensagem, tipo, data_hora, direcao, status) 
                      VALUES (36, '554796164699', '554796164699', 'Teste de correção - " . date('Y-m-d H:i:s') . "', 'texto', NOW(), 'recebido', 'teste')";
        
        if ($mysqli->query($sql_teste)) {
            $id_teste = $mysqli->insert_id;
            echo "   ✅ Inserção de teste realizada (ID: $id_teste)\n";
            
            // Verificar se foi inserido corretamente
            $check_result = $mysqli->query("SELECT id, numero_whatsapp, telefone_origem FROM mensagens_comunicacao WHERE id = $id_teste");
            if ($check_result && $check_result->num_rows > 0) {
                $row = $check_result->fetch_assoc();
                echo "   ✅ Verificação: Registro encontrado (ID: {$row['id']}, Num: {$row['numero_whatsapp']}, Origem: {$row['telefone_origem']})\n";
            }
            
            // Limpar registro de teste
            $mysqli->query("DELETE FROM mensagens_comunicacao WHERE id = $id_teste");
            echo "   🧹 Registro de teste removido\n";
            
            $mysqli->commit();
            echo "   ✅ Teste de inserção confirmado\n";
            
        } else {
            throw new Exception("Erro na inserção de teste: " . $mysqli->error);
        }
        
    } catch (Exception $e) {
        $mysqli->rollback();
        throw new Exception("Erro no teste de inserção: " . $e->getMessage());
    }
    
    echo "\n";
    
    // ===== 7. VERIFICAR ESTRUTURA FINAL =====
    echo "7. 📋 ESTRUTURA FINAL DA TABELA:\n";
    
    $result = $mysqli->query("DESCRIBE mensagens_comunicacao");
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $status = in_array($row['Field'], ['numero_whatsapp', 'telefone_origem', 'whatsapp_message_id']) ? '📱' : '📄';
            echo "   $status {$row['Field']} ({$row['Type']}) - {$row['Null']} - {$row['Default']}\n";
        }
    }
    
    echo "\n";
    
    // ===== 8. RESUMO FINAL =====
    echo "8. 📊 RESUMO DA CORREÇÃO:\n";
    echo "   ✅ Conexão com banco: OK\n";
    echo "   ✅ Estrutura da tabela: Verificada\n";
    echo "   ✅ Coluna telefone_origem: " . ($coluna_existe ? 'Já existia' : 'Adicionada com sucesso') . "\n";
    echo "   ✅ Backup: " . (isset($backup_table) ? "Criado ($backup_table)" : "Não necessário") . "\n";
    echo "   ✅ Teste de inserção: OK\n";
    echo "   ✅ Código analisado: " . count($arquivos_com_erro) . " arquivo(s) usam a coluna\n";
    
    echo "\n   🎯 PRÓXIMOS PASSOS:\n";
    echo "   1. Testar webhook novamente\n";
    echo "   2. Enviar mensagem real para 554797146908\n";
    echo "   3. Verificar se o erro foi resolvido\n";
    echo "   4. Monitorar logs para confirmar funcionamento\n";
    
    if (isset($backup_table)) {
        echo "\n   💾 BACKUP CRIADO: $backup_table\n";
        echo "   Para remover o backup após confirmação: DROP TABLE $backup_table;\n";
    }
    
} catch (Exception $e) {
    echo "\n❌ ERRO CRÍTICO: " . $e->getMessage() . "\n";
    echo "🔧 Ação recomendada: Verificar logs e tentar novamente\n";
    exit(1);
} finally {
    if (isset($mysqli)) {
        $mysqli->close();
    }
}

echo "\n=== FIM DA CORREÇÃO ===\n";
echo "Status: " . (isset($e) ? "❌ FALHOU" : "✅ SUCESSO") . "\n";
?> 