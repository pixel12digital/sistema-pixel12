<?php
/**
 * CRIAR TABELAS - CANAL COMERCIAL
 * 
 * Este script cria as tabelas necessárias no banco u342734079_wts_com_pixel
 */

echo "🔧 CRIANDO TABELAS - CANAL COMERCIAL\n";
echo "====================================\n\n";

// Incluir configuração do canal
require_once __DIR__ . '/canal_config.php';

echo "📊 CONECTANDO AO BANCO:\n";
$mysqli = conectarBancoCanal();

if (!$mysqli) {
    echo "  ❌ Erro ao conectar ao banco\n";
    exit;
}

echo "  ✅ Conectado ao banco: " . CANAL_BANCO_NOME . "\n\n";

// 1. Criar tabela mensagens_pendentes
echo "📄 CRIANDO TABELA mensagens_pendentes:\n";
$sql_mensagens_pendentes = "
CREATE TABLE IF NOT EXISTS mensagens_pendentes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    canal_id INT NOT NULL,
    numero VARCHAR(20) NOT NULL,
    mensagem TEXT NOT NULL,
    tipo VARCHAR(20) DEFAULT 'texto',
    data_hora DATETIME DEFAULT CURRENT_TIMESTAMP,
    status VARCHAR(20) DEFAULT 'pendente',
    INDEX idx_canal_id (canal_id),
    INDEX idx_numero (numero),
    INDEX idx_data_hora (data_hora)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
";

if ($mysqli->query($sql_mensagens_pendentes)) {
    echo "  ✅ Tabela mensagens_pendentes criada/verificada\n";
} else {
    echo "  ❌ Erro ao criar mensagens_pendentes: " . $mysqli->error . "\n";
}

// 2. Verificar se a tabela canais_comunicacao tem o canal comercial
echo "\n📋 VERIFICANDO CANAL COMERCIAL:\n";
$sql_verificar_canal = "SELECT * FROM canais_comunicacao WHERE id = " . CANAL_NUMERO;
$result = $mysqli->query($sql_verificar_canal);

if ($result && $result->num_rows > 0) {
    $canal = $result->fetch_assoc();
    echo "  ✅ Canal comercial já existe:\n";
    echo "    ID: {$canal['id']}\n";
    echo "    Nome: {$canal['nome_exibicao']}\n";
    echo "    Porta: {$canal['porta']}\n";
    echo "    Identificador: {$canal['identificador']}\n";
} else {
    echo "  ⚠️ Canal comercial não encontrado, criando...\n";
    
    $sql_criar_canal = "
    INSERT INTO canais_comunicacao (id, nome_exibicao, porta, identificador, status, tipo) 
    VALUES (" . CANAL_NUMERO . ", '" . CANAL_NOME . "', " . CANAL_ID . ", '" . CANAL_WHATSAPP_COMPLETO . "', 'ativo', 'whatsapp')
    ";
    
    if ($mysqli->query($sql_criar_canal)) {
        echo "  ✅ Canal comercial criado\n";
    } else {
        echo "  ❌ Erro ao criar canal: " . $mysqli->error . "\n";
    }
}

// 3. Verificar estrutura da tabela mensagens_comunicacao
echo "\n📋 VERIFICANDO ESTRUTURA mensagens_comunicacao:\n";
$sql_estrutura = "DESCRIBE mensagens_comunicacao";
$result = $mysqli->query($sql_estrutura);

if ($result) {
    echo "  ✅ Estrutura da tabela mensagens_comunicacao:\n";
    while ($coluna = $result->fetch_assoc()) {
        echo "    - {$coluna['Field']} ({$coluna['Type']}) {$coluna['Null']} {$coluna['Key']}\n";
    }
} else {
    echo "  ❌ Erro ao verificar estrutura: " . $mysqli->error . "\n";
}

// 4. Verificar estrutura da tabela clientes
echo "\n📋 VERIFICANDO ESTRUTURA clientes:\n";
$sql_estrutura_clientes = "DESCRIBE clientes";
$result = $mysqli->query($sql_estrutura_clientes);

if ($result) {
    echo "  ✅ Estrutura da tabela clientes:\n";
    while ($coluna = $result->fetch_assoc()) {
        echo "    - {$coluna['Field']} ({$coluna['Type']}) {$coluna['Null']} {$coluna['Key']}\n";
    }
} else {
    echo "  ❌ Erro ao verificar estrutura clientes: " . $mysqli->error . "\n";
}

// 5. Testar inserção de mensagem
echo "\n🧪 TESTANDO INSERÇÃO DE MENSAGEM:\n";

// Primeiro criar um cliente de teste
$sql_cliente_teste = "
INSERT INTO clientes (nome, celular, status) 
VALUES ('Cliente Teste', '554797146908', 'ativo')
";

if ($mysqli->query($sql_cliente_teste)) {
    $cliente_id = $mysqli->insert_id;
    echo "  ✅ Cliente de teste criado (ID: $cliente_id)\n";
    
    // Agora inserir mensagem
    $sql_teste = "
    INSERT INTO mensagens_comunicacao (canal_id, cliente_id, mensagem, tipo, data_hora, direcao, status) 
    VALUES (" . CANAL_NUMERO . ", $cliente_id, 'Teste de configuração - " . date('H:i:s') . "', 'texto', NOW(), 'recebido', 'recebido')
    ";
    
    if ($mysqli->query($sql_teste)) {
        echo "  ✅ Mensagem de teste inserida (ID: " . $mysqli->insert_id . ")\n";
        
        // Verificar total de mensagens
        $total = $mysqli->query("SELECT COUNT(*) as total FROM mensagens_comunicacao")->fetch_assoc()['total'];
        echo "  📨 Total de mensagens no banco: $total\n";
    } else {
        echo "  ❌ Erro ao inserir mensagem de teste: " . $mysqli->error . "\n";
    }
} else {
    echo "  ❌ Erro ao criar cliente de teste: " . $mysqli->error . "\n";
}

$mysqli->close();

echo "\n🎯 CONFIGURAÇÃO CONCLUÍDA!\n";
echo "O banco comercial está pronto para receber mensagens.\n";
echo "• Banco: " . CANAL_BANCO_NOME . "\n";
echo "• Canal: " . CANAL_NOME . " (ID: " . CANAL_NUMERO . ")\n";
echo "• Porta: " . CANAL_ID . "\n";
echo "• WhatsApp: " . CANAL_WHATSAPP_NUMERO . "\n";
?> 