<?php
require_once 'config.php';

echo "🔍 VERIFICAÇÃO DOS BANCOS SEPARADOS POR CANAL\n";
echo "=============================================\n\n";

// 1. Verificar configuração atual
echo "📊 CONFIGURAÇÃO ATUAL:\n";
echo "   Host: " . DB_HOST . "\n";
echo "   Usuário: " . DB_USER . "\n";
echo "   Banco Principal: " . DB_NAME . "\n\n";

// 2. Lista de bancos separados por canal
$bancos_canais = [
    3000 => ['nome' => 'pixel12digital', 'descricao' => 'Financeiro (Principal)'],
    3001 => ['nome' => 'pixel12digital_comercial', 'descricao' => 'Comercial'],
    3002 => ['nome' => 'pixel12digital_suporte', 'descricao' => 'Suporte'],
    3003 => ['nome' => 'pixel12digital_vendas', 'descricao' => 'Vendas']
];

// 3. Verificar cada banco
echo "📋 VERIFICAÇÃO DOS BANCOS:\n";
foreach ($bancos_canais as $porta => $banco) {
    echo "\n🔍 Porta $porta - {$banco['descricao']}:\n";
    echo "   Banco: {$banco['nome']}\n";
    
    try {
        $mysqli_teste = new mysqli(DB_HOST, DB_USER, 'SUA_SENHA_AQUI', $banco['nome']);
        if (!$mysqli_teste->connect_error) {
            echo "   ✅ Banco existe e está acessível\n";
            
            // Verificar tabelas
            $tabelas = $mysqli_teste->query("SHOW TABLES");
            if ($tabelas && $tabelas->num_rows > 0) {
                echo "   📄 Tabelas encontradas:\n";
                while ($tabela = $tabelas->fetch_array()) {
                    echo "      - {$tabela[0]}\n";
                }
                
                // Verificar mensagens se a tabela existir
                $mensagens = $mysqli_teste->query("SELECT COUNT(*) as total FROM mensagens_comunicacao");
                if ($mensagens) {
                    $total = $mensagens->fetch_assoc()['total'];
                    echo "   📨 Total de mensagens: $total\n";
                    
                    if ($total > 0) {
                        echo "   📊 Últimas mensagens:\n";
                        $ultimas = $mysqli_teste->query("SELECT * FROM mensagens_comunicacao ORDER BY data_hora DESC LIMIT 3");
                        while ($msg = $ultimas->fetch_assoc()) {
                            echo "      ID {$msg['id']} - {$msg['data_hora']} - " . substr($msg['mensagem'], 0, 30) . "...\n";
                        }
                    }
                }
            } else {
                echo "   ⚠️ Banco existe mas não tem tabelas\n";
            }
            
            $mysqli_teste->close();
        } else {
            echo "   ❌ Erro ao conectar: " . $mysqli_teste->connect_error . "\n";
        }
    } catch (Exception $e) {
        echo "   ❌ Exceção: " . $e->getMessage() . "\n";
    }
}

// 4. Testar salvamento no banco correto
echo "\n🧪 TESTANDO SALVAMENTO NO BANCO CORRETO:\n";

// Simular mensagem para canal 3001 (Comercial)
$dados_teste = [
    'from' => '554797146908@c.us',
    'to' => '4797309525@c.us', // Canal Comercial
    'body' => 'Teste banco separado - ' . date('H:i:s'),
    'timestamp' => time()
];

echo "   Dados de teste:\n";
echo "      From: {$dados_teste['from']}\n";
echo "      To: {$dados_teste['to']}\n";
echo "      Body: {$dados_teste['body']}\n";

// Conectar ao banco comercial
try {
    $mysqli_comercial = new mysqli(DB_HOST, DB_USER, 'SUA_SENHA_AQUI', 'pixel12digital_comercial');
    if (!$mysqli_comercial->connect_error) {
        echo "   ✅ Conectado ao banco comercial\n";
        
        // Simular salvamento
        $numero_remetente = str_replace('@c.us', '', $dados_teste['from']);
        $data_hora = date('Y-m-d H:i:s');
        $canal_id = 37; // Canal Comercial
        
        $sql = "INSERT INTO mensagens_comunicacao (canal_id, numero_whatsapp, mensagem, tipo, data_hora, direcao) 
                VALUES ($canal_id, '$numero_remetente', '{$dados_teste['body']}', 'texto', '$data_hora', 'recebido')";
        
        $insert = $mysqli_comercial->query($sql);
        
        if ($insert) {
            $mensagem_id = $mysqli_comercial->insert_id;
            echo "   ✅ Mensagem salva no banco comercial! ID: $mensagem_id\n";
            
            // Verificar se foi salva
            $mensagem_salva = $mysqli_comercial->query("SELECT * FROM mensagens_comunicacao WHERE id = $mensagem_id")->fetch_assoc();
            if ($mensagem_salva) {
                echo "   📊 Mensagem verificada:\n";
                echo "      ID: {$mensagem_salva['id']}\n";
                echo "      Canal ID: {$mensagem_salva['canal_id']}\n";
                echo "      Mensagem: {$mensagem_salva['mensagem']}\n";
                echo "      Data/Hora: {$mensagem_salva['data_hora']}\n";
            }
        } else {
            echo "   ❌ Erro ao salvar: " . $mysqli_comercial->error . "\n";
        }
        
        $mysqli_comercial->close();
    } else {
        echo "   ❌ Erro ao conectar ao banco comercial: " . $mysqli_comercial->connect_error . "\n";
    }
} catch (Exception $e) {
    echo "   ❌ Exceção ao conectar ao banco comercial: " . $e->getMessage() . "\n";
}

// 5. Verificar configuração dos canais no banco principal
echo "\n📱 CONFIGURAÇÃO DOS CANAIS NO BANCO PRINCIPAL:\n";
try {
    $mysqli_principal = new mysqli(DB_HOST, DB_USER, 'SUA_SENHA_AQUI', DB_NAME);
    if (!$mysqli_principal->connect_error) {
        $canais = $mysqli_principal->query("SELECT id, nome_exibicao, porta, status, identificador FROM canais_comunicacao WHERE status <> 'excluido'");
        while ($canal = $canais->fetch_assoc()) {
            $status_icon = $canal['status'] === 'conectado' ? '🟢' : '🟡';
            echo "   {$status_icon} {$canal['nome_exibicao']} (ID: {$canal['id']}, Porta: {$canal['porta']})\n";
            echo "      Status: {$canal['status']} | Identificador: {$canal['identificador']}\n";
        }
        $mysqli_principal->close();
    }
} catch (Exception $e) {
    echo "   ❌ Erro ao verificar canais: " . $e->getMessage() . "\n";
}

echo "\n🎯 DIAGNÓSTICO COMPLETO!\n";
echo "Para implementar bancos separados:\n";
echo "1. ✅ Verificar se os bancos existem\n";
echo "2. ✅ Criar tabelas nos bancos separados\n";
echo "3. ✅ Configurar credenciais corretas\n";
echo "4. ✅ Testar salvamento em cada banco\n";
echo "5. ✅ Verificar leitura de múltiplos bancos\n";
?> 