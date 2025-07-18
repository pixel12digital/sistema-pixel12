<?php
/**
 * Script para verificar e diagnosticar problemas de conexão com o banco de dados
 * Acesse: http://localhost/loja-virtual-revenda/painel/verificar_conexao_banco.php
 */

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>🔍 Diagnóstico de Conexão com Banco de Dados</h1>";

// 1. Verificar configurações
echo "<h2>1. Configurações Atuais</h2>";
require_once 'config.php';

echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 10px 0;'>";
echo "<tr><th>Configuração</th><th>Valor</th></tr>";
echo "<tr><td>DB_HOST</td><td>" . DB_HOST . "</td></tr>";
echo "<tr><td>DB_NAME</td><td>" . DB_NAME . "</td></tr>";
echo "<tr><td>DB_USER</td><td>" . DB_USER . "</td></tr>";
echo "<tr><td>DB_PASS</td><td>" . (strlen(DB_PASS) > 0 ? "***" . substr(DB_PASS, -3) : "vazio") . "</td></tr>";
echo "<tr><td>Ambiente</td><td>" . (strpos(DB_HOST, 'localhost') !== false ? 'Local' : 'Remoto') . "</td></tr>";
echo "</table>";

// 2. Verificar se MySQL está rodando localmente
echo "<h2>2. Status do MySQL Local</h2>";
if (strpos(DB_HOST, 'localhost') !== false || strpos(DB_HOST, '127.0.0.1') !== false) {
    $port = 3306;
    $connection = @fsockopen('localhost', $port, $errno, $errstr, 5);
    
    if ($connection) {
        echo "<p style='color: green;'>✅ MySQL está rodando na porta $port</p>";
        fclose($connection);
    } else {
        echo "<p style='color: red;'>❌ MySQL NÃO está rodando na porta $port</p>";
        echo "<p><strong>Solução:</strong> Inicie o MySQL no XAMPP Control Panel</p>";
    }
} else {
    echo "<p>ℹ️ Usando banco remoto, pulando verificação local</p>";
}

// 3. Testar conexão com o banco
echo "<h2>3. Teste de Conexão</h2>";
try {
    $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($mysqli->connect_errno) {
        echo "<p style='color: red;'>❌ Erro de conexão: " . $mysqli->connect_error . "</p>";
        echo "<p><strong>Código do erro:</strong> " . $mysqli->connect_errno . "</p>";
        
        // Sugestões baseadas no erro
        switch ($mysqli->connect_errno) {
            case 2002:
                echo "<p><strong>Sugestão:</strong> O servidor MySQL não está rodando ou não está acessível</p>";
                break;
            case 1045:
                echo "<p><strong>Sugestão:</strong> Usuário ou senha incorretos</p>";
                break;
            case 1049:
                echo "<p><strong>Sugestão:</strong> O banco de dados não existe</p>";
                break;
            case 2003:
                echo "<p><strong>Sugestão:</strong> Não foi possível conectar ao servidor MySQL</p>";
                break;
            default:
                echo "<p><strong>Sugestão:</strong> Verifique as configurações de conexão</p>";
        }
    } else {
        echo "<p style='color: green;'>✅ Conexão estabelecida com sucesso!</p>";
        echo "<p><strong>Versão do MySQL:</strong> " . $mysqli->server_info . "</p>";
        echo "<p><strong>Charset:</strong> " . $mysqli->character_set_name() . "</p>";
        
        // 4. Verificar tabelas necessárias
        echo "<h2>4. Verificação de Tabelas</h2>";
        $tabelas_necessarias = ['clientes', 'cobrancas', 'assinaturas'];
        $tabelas_encontradas = [];
        
        $result = $mysqli->query("SHOW TABLES");
        if ($result) {
            while ($row = $result->fetch_array()) {
                $tabelas_encontradas[] = $row[0];
            }
        }
        
        echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 10px 0;'>";
        echo "<tr><th>Tabela</th><th>Status</th><th>Registros</th></tr>";
        
        foreach ($tabelas_necessarias as $tabela) {
            if (in_array($tabela, $tabelas_encontradas)) {
                $count_result = $mysqli->query("SELECT COUNT(*) as total FROM $tabela");
                $count = $count_result ? $count_result->fetch_assoc()['total'] : 'Erro';
                echo "<tr><td>$tabela</td><td style='color: green;'>✅ Existe</td><td>$count</td></tr>";
            } else {
                echo "<tr><td>$tabela</td><td style='color: red;'>❌ Não encontrada</td><td>-</td></tr>";
            }
        }
        echo "</table>";
        
        $mysqli->close();
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Exceção: " . $e->getMessage() . "</p>";
}

// 5. Verificar configurações do XAMPP
echo "<h2>5. Configurações do XAMPP</h2>";
if (strpos(DB_HOST, 'localhost') !== false) {
    $xampp_paths = [
        'C:/xampp/mysql/bin/mysql.exe',
        'C:/xampp/mysql/data',
        'C:/xampp/apache/bin/httpd.exe'
    ];
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 10px 0;'>";
    echo "<tr><th>Componente</th><th>Status</th></tr>";
    
    foreach ($xampp_paths as $path) {
        if (file_exists($path)) {
            echo "<tr><td>$path</td><td style='color: green;'>✅ Encontrado</td></tr>";
        } else {
            echo "<tr><td>$path</td><td style='color: red;'>❌ Não encontrado</td></tr>";
        }
    }
    echo "</table>";
    
    echo "<h3>Comandos para iniciar o XAMPP:</h3>";
    echo "<ul>";
    echo "<li>Abra o XAMPP Control Panel</li>";
    echo "<li>Clique em 'Start' ao lado de 'Apache'</li>";
    echo "<li>Clique em 'Start' ao lado de 'MySQL'</li>";
    echo "<li>Verifique se ambos ficam com fundo verde</li>";
    echo "</ul>";
} else {
    echo "<p>ℹ️ Usando banco remoto, pulando verificação do XAMPP</p>";
}

// 6. Teste de sincronização
echo "<h2>6. Teste de Sincronização</h2>";
echo "<p><a href='sincronizar_asaas_ajax.php' target='_blank'>🔗 Testar Sincronização via AJAX</a></p>";
echo "<p><a href='sincroniza_asaas.php' target='_blank'>🔗 Testar Sincronização Direta</a></p>";

// 7. Logs de erro
echo "<h2>7. Logs de Erro Recentes</h2>";
$log_files = [
    '../logs/sincroniza_asaas_debug.log',
    '../logs/error.log',
    'error_log'
];

foreach ($log_files as $log_file) {
    if (file_exists($log_file)) {
        echo "<h3>$log_file</h3>";
        $lines = file($log_file);
        $recent_lines = array_slice($lines, -10); // Últimas 10 linhas
        echo "<pre style='background: #f5f5f5; padding: 10px; border-radius: 5px; max-height: 200px; overflow-y: auto;'>";
        foreach ($recent_lines as $line) {
            echo htmlspecialchars($line);
        }
        echo "</pre>";
    }
}

echo "<h2>8. Próximos Passos</h2>";
echo "<ol>";
echo "<li>Se o MySQL não estiver rodando, inicie-o no XAMPP Control Panel</li>";
echo "<li>Se houver erro de conexão, verifique as credenciais no arquivo config.php</li>";
echo "<li>Se as tabelas não existirem, execute o script de instalação</li>";
echo "<li>Teste a sincronização novamente após corrigir os problemas</li>";
echo "</ol>";

echo "<p><strong>Última atualização:</strong> " . date('Y-m-d H:i:s') . "</p>";
?> 