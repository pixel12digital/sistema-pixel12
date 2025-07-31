<?php
/**
 * CORRIGIR BANCO COMERCIAL
 * 
 * Este script verifica e corrige a estrutura do banco comercial
 * para garantir que todas as tabelas necessárias existam
 */

echo "🔧 CORRIGINDO BANCO COMERCIAL\n";
echo "=============================\n\n";

require_once 'canais/comercial/canal_config.php';

// Conectar ao banco comercial
$mysqli = conectarBancoCanal();
if (!$mysqli) {
    echo "❌ Não foi possível conectar ao banco comercial\n";
    exit;
}

echo "✅ Conectado ao banco: " . CANAL_BANCO_NOME . "\n\n";

// 1. Verificar tabelas existentes
echo "🔍 VERIFICANDO TABELAS EXISTENTES:\n";
$result = $mysqli->query("SHOW TABLES");
$tabelas_existentes = [];
while ($row = $result->fetch_array()) {
    $tabelas_existentes[] = $row[0];
}

echo "  📋 Tabelas encontradas:\n";
foreach ($tabelas_existentes as $tabela) {
    echo "    - $tabela\n";
}

// 2. Verificar se mensagens_pendentes existe
echo "\n🔍 VERIFICANDO TABELA mensagens_pendentes:\n";
if (in_array('mensagens_pendentes', $tabelas_existentes)) {
    echo "  ✅ Tabela mensagens_pendentes existe\n";
} else {
    echo "  ❌ Tabela mensagens_pendentes não existe - criando...\n";
    
    $sql_criar = "CREATE TABLE mensagens_pendentes (
        id INT AUTO_INCREMENT PRIMARY KEY,
        canal_id INT NOT NULL,
        numero VARCHAR(20) NOT NULL,
        mensagem TEXT NOT NULL,
        tipo VARCHAR(50) DEFAULT 'texto',
        data_hora TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        status VARCHAR(50) DEFAULT 'pendente',
        processado BOOLEAN DEFAULT FALSE,
        INDEX idx_canal_id (canal_id),
        INDEX idx_numero (numero),
        INDEX idx_data_hora (data_hora)
    )";
    
    if ($mysqli->query($sql_criar)) {
        echo "  ✅ Tabela mensagens_pendentes criada com sucesso!\n";
    } else {
        echo "  ❌ Erro ao criar tabela: " . $mysqli->error . "\n";
    }
}

// 3. Verificar estrutura da tabela mensagens_comunicacao
echo "\n🔍 VERIFICANDO ESTRUTURA DA TABELA mensagens_comunicacao:\n";
$result = $mysqli->query("DESCRIBE mensagens_comunicacao");
if ($result) {
    echo "  ✅ Tabela mensagens_comunicacao existe\n";
    echo "  📋 Colunas:\n";
    while ($row = $result->fetch_assoc()) {
        echo "    - {$row['Field']} ({$row['Type']})\n";
    }
} else {
    echo "  ❌ Tabela mensagens_comunicacao não existe - criando...\n";
    
    $sql_criar = "CREATE TABLE mensagens_comunicacao (
        id INT AUTO_INCREMENT PRIMARY KEY,
        canal_id INT NOT NULL,
        cliente_id INT,
        cobranca_id INT,
        mensagem TEXT NOT NULL,
        anexo VARCHAR(255),
        tipo VARCHAR(32) NOT NULL,
        data_hora DATETIME NOT NULL,
        direcao VARCHAR(16) NOT NULL,
        status VARCHAR(32),
        status_conversa ENUM('aberta', 'fechada') DEFAULT 'aberta',
        numero_whatsapp VARCHAR(20),
        whatsapp_message_id VARCHAR(255),
        motivo_erro VARCHAR(255),
        INDEX idx_canal_id (canal_id),
        INDEX idx_cliente_id (cliente_id),
        INDEX idx_data_hora (data_hora),
        INDEX idx_numero_whatsapp (numero_whatsapp)
    )";
    
    if ($mysqli->query($sql_criar)) {
        echo "  ✅ Tabela mensagens_comunicacao criada com sucesso!\n";
    } else {
        echo "  ❌ Erro ao criar tabela: " . $mysqli->error . "\n";
    }
}

// 4. Verificar tabela clientes
echo "\n🔍 VERIFICANDO TABELA clientes:\n";
$result = $mysqli->query("DESCRIBE clientes");
if ($result) {
    echo "  ✅ Tabela clientes existe\n";
} else {
    echo "  ❌ Tabela clientes não existe - criando...\n";
    
    $sql_criar = "CREATE TABLE clientes (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nome VARCHAR(255),
        contact_name VARCHAR(255),
        celular VARCHAR(20),
        telefone VARCHAR(20),
        cpf_cnpj VARCHAR(20),
        email VARCHAR(255),
        data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_celular (celular),
        INDEX idx_telefone (telefone),
        INDEX idx_cpf_cnpj (cpf_cnpj)
    )";
    
    if ($mysqli->query($sql_criar)) {
        echo "  ✅ Tabela clientes criada com sucesso!\n";
    } else {
        echo "  ❌ Erro ao criar tabela: " . $mysqli->error . "\n";
    }
}

// 5. Verificar tabela canais_comunicacao
echo "\n🔍 VERIFICANDO TABELA canais_comunicacao:\n";
$result = $mysqli->query("SELECT * FROM canais_comunicacao WHERE id = 37");
if ($result && $result->num_rows > 0) {
    $canal = $result->fetch_assoc();
    echo "  ✅ Canal 37 configurado:\n";
    echo "    Nome: {$canal['nome_exibicao']}\n";
    echo "    Porta: {$canal['porta']}\n";
    echo "    Identificador: {$canal['identificador']}\n";
    echo "    Status: {$canal['status']}\n";
} else {
    echo "  ❌ Canal 37 não encontrado - criando...\n";
    
    $sql_inserir = "INSERT INTO canais_comunicacao (id, tipo, identificador, nome_exibicao, status, porta, data_conexao) 
                    VALUES (37, 'whatsapp', '4797309525@c.us', 'Comercial - Pixel', 'conectado', 3001, NOW())";
    
    if ($mysqli->query($sql_inserir)) {
        echo "  ✅ Canal 37 criado com sucesso!\n";
    } else {
        echo "  ❌ Erro ao criar canal: " . $mysqli->error . "\n";
    }
}

// 6. Testar salvamento de mensagem
echo "\n🧪 TESTANDO SALVAMENTO DE MENSAGEM:\n";
$dados_teste = [
    'from' => '554797146908@c.us',
    'body' => 'Teste correção banco comercial - ' . date('H:i:s'),
    'timestamp' => time()
];

$resultado = salvarMensagemCanal($dados_teste);

if ($resultado) {
    echo "  ✅ Mensagem salva com sucesso!\n";
    
    // Verificar onde foi salva
    $sql_verificar = "SELECT * FROM mensagens_comunicacao ORDER BY data_hora DESC LIMIT 1";
    $result = $mysqli->query($sql_verificar);
    
    if ($result && $result->num_rows > 0) {
        $msg = $result->fetch_assoc();
        echo "  📋 Mensagem encontrada na tabela mensagens_comunicacao:\n";
        echo "    ID: {$msg['id']}\n";
        echo "    Canal ID: {$msg['canal_id']}\n";
        echo "    Mensagem: " . substr($msg['mensagem'], 0, 50) . "...\n";
    } else {
        echo "  ⚠️ Mensagem não encontrada na tabela mensagens_comunicacao\n";
        
        // Verificar se foi salva em pendentes
        $sql_pendentes = "SELECT * FROM mensagens_pendentes ORDER BY data_hora DESC LIMIT 1";
        $result = $mysqli->query($sql_pendentes);
        
        if ($result && $result->num_rows > 0) {
            $msg = $result->fetch_assoc();
            echo "  📋 Mensagem encontrada na tabela mensagens_pendentes:\n";
            echo "    ID: {$msg['id']}\n";
            echo "    Canal ID: {$msg['canal_id']}\n";
            echo "    Número: {$msg['numero']}\n";
            echo "    Mensagem: " . substr($msg['mensagem'], 0, 50) . "...\n";
        }
    }
} else {
    echo "  ❌ Erro ao salvar mensagem\n";
}

$mysqli->close();

echo "\n🎯 RESULTADO DA CORREÇÃO:\n";
echo "✅ Script de correção executado!\n";
echo "📋 Próximos passos:\n";
echo "1. Testar webhook específico novamente\n";
echo "2. Verificar se mensagens são salvas no banco correto\n";
echo "3. Configurar automações específicas do canal\n";

echo "\n🌐 LINKS ÚTEIS:\n";
echo "• phpMyAdmin Comercial: https://auth-db1607.hstgr.io/index.php?route=/sql&pos=0&db=u342734079_wts_com_pixel\n";
echo "• Webhook Específico: https://app.pixel12digital.com.br/api/webhook_canal_37.php\n";
echo "• VPS Status: http://212.85.11.238:3001/status\n";
?> 