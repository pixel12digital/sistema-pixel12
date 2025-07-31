<?php
/**
 * VERIFICAÇÃO DO BANCO COMERCIAL
 * 
 * Este script verifica se o banco u342734079_wts_com_pixel existe
 * e se o usuário tem as permissões corretas
 */

echo "🔍 VERIFICAÇÃO DO BANCO COMERCIAL\n";
echo "=================================\n\n";

// Configurações corretas
$host = 'srv1607.hstgr.io';
$user = 'u342734079_wts_com_pixel';
$pass = 'Los@ngo#081081';
$banco = 'u342734079_wts_com_pixel';

echo "📊 CONFIGURAÇÕES:\n";
echo "  Host: $host\n";
echo "  Usuário: $user\n";
echo "  Banco: $banco\n";
echo "  phpMyAdmin: https://auth-db1607.hstgr.io/index.php?route=/sql&pos=0&db=$banco&table=mensagens_comunicacao\n\n";

// 1. Tentar conectar sem especificar banco
echo "🔍 TESTANDO CONEXÃO SEM BANCO:\n";
try {
    $mysqli = new mysqli($host, $user, $pass);
    if (!$mysqli->connect_error) {
        echo "  ✅ Conectado ao servidor MySQL\n";
        
        // Verificar bancos disponíveis
        $bancos = $mysqli->query("SHOW DATABASES");
        echo "  📋 Bancos disponíveis:\n";
        $banco_encontrado = false;
        while ($banco_info = $bancos->fetch_array()) {
            $nome_banco = $banco_info[0];
            echo "    - $nome_banco";
            if ($nome_banco === $banco) {
                echo " ✅ (ENCONTRADO)";
                $banco_encontrado = true;
            }
            echo "\n";
        }
        
        if (!$banco_encontrado) {
            echo "  ❌ Banco '$banco' não encontrado!\n";
            echo "  💡 Precisa criar o banco no painel da Hostinger\n";
        } else {
            echo "  ✅ Banco '$banco' existe!\n";
            
            // Tentar conectar ao banco específico
            echo "\n🔍 TESTANDO CONEXÃO AO BANCO ESPECÍFICO:\n";
            $mysqli->close();
            
            try {
                $mysqli_banco = new mysqli($host, $user, $pass, $banco);
                if (!$mysqli_banco->connect_error) {
                    echo "  ✅ Conectado ao banco '$banco'\n";
                    
                    // Verificar tabelas
                    $tabelas = $mysqli_banco->query("SHOW TABLES");
                    if ($tabelas && $tabelas->num_rows > 0) {
                        echo "  📄 Tabelas encontradas:\n";
                        while ($tabela = $tabelas->fetch_array()) {
                            echo "    - {$tabela[0]}\n";
                        }
                        
                        // Verificar se tem as tabelas necessárias
                        $tabelas_necessarias = ['clientes', 'mensagens_comunicacao', 'canais_comunicacao'];
                        foreach ($tabelas_necessarias as $tabela) {
                            $result = $mysqli_banco->query("SHOW TABLES LIKE '$tabela'");
                            if ($result && $result->num_rows > 0) {
                                echo "    ✅ Tabela '$tabela' existe\n";
                            } else {
                                echo "    ❌ Tabela '$tabela' não existe\n";
                            }
                        }
                        
                        // Verificar mensagens existentes
                        $mensagens = $mysqli_banco->query("SELECT COUNT(*) as total FROM mensagens_comunicacao");
                        if ($mensagens) {
                            $total = $mensagens->fetch_assoc()['total'];
                            echo "    📨 Total de mensagens: $total\n";
                        }
                        
                        // Verificar clientes existentes
                        $clientes = $mysqli_banco->query("SELECT COUNT(*) as total FROM clientes");
                        if ($clientes) {
                            $total_clientes = $clientes->fetch_assoc()['total'];
                            echo "    👥 Total de clientes: $total_clientes\n";
                        }
                        
                    } else {
                        echo "  ⚠️ Banco existe mas não tem tabelas\n";
                        echo "  💡 Precisa criar as tabelas\n";
                    }
                    
                    if (isset($mysqli_banco)) {
                        $mysqli_banco->close();
                    }
                } else {
                    echo "  ❌ Erro ao conectar ao banco: " . $mysqli_banco->connect_error . "\n";
                    echo "  💡 Problema de permissões - verificar no painel da Hostinger\n";
                }
            } catch (Exception $e) {
                echo "  ❌ Exceção ao conectar ao banco: " . $e->getMessage() . "\n";
            }
        }
        
        if (isset($mysqli)) {
            $mysqli->close();
        }
    } else {
        echo "  ❌ Erro ao conectar ao servidor: " . $mysqli->connect_error . "\n";
    }
} catch (Exception $e) {
    echo "  ❌ Exceção ao conectar: " . $e->getMessage() . "\n";
}

echo "\n🎯 PRÓXIMOS PASSOS:\n";
echo "1. Se o banco não existe: Criar no painel da Hostinger\n";
echo "2. Se o banco existe mas sem tabelas: Executar script SQL\n";
echo "3. Se há erro de permissões: Verificar usuário no painel\n";
echo "4. Se tudo OK: Testar salvamento de mensagens\n";

echo "\n📋 COMANDOS ÚTEIS:\n";
echo "• Criar banco: CREATE DATABASE $banco;\n";
echo "• Conceder permissões: GRANT ALL PRIVILEGES ON $banco.* TO '$user'@'%';\n";
echo "• Aplicar permissões: FLUSH PRIVILEGES;\n";

echo "\n🌐 ACESSO AO BANCO:\n";
echo "• phpMyAdmin: https://auth-db1607.hstgr.io/index.php?route=/sql&pos=0&db=$banco&table=mensagens_comunicacao\n";
echo "• Usuário: $user\n";
echo "• Senha: $pass\n";
?> 