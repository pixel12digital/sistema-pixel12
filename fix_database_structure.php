<?php
require_once 'config.php';

echo "=== VERIFICAÇÃO E CORREÇÃO DA ESTRUTURA DO BANCO ===\n\n";

try {
    $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($mysqli->connect_error) {
        throw new Exception("Erro de conexão: " . $mysqli->connect_error);
    }
    
    // 1. Verificar e corrigir tabela clientes
    echo "1. Verificando tabela 'clientes'...\n";
    
    $result = $mysqli->query("SHOW TABLES LIKE 'clientes'");
    if ($result->num_rows === 0) {
        echo "   ❌ Tabela 'clientes' não existe. Criando...\n";
        
        $sql = "CREATE TABLE clientes (
            id INT AUTO_INCREMENT PRIMARY KEY,
            asaas_id VARCHAR(64) NOT NULL UNIQUE,
            nome VARCHAR(255) NOT NULL,
            email VARCHAR(255) NOT NULL,
            telefone VARCHAR(50),
            celular VARCHAR(20),
            cep VARCHAR(10),
            rua VARCHAR(255),
            numero VARCHAR(10),
            complemento VARCHAR(50),
            bairro VARCHAR(100),
            cidade VARCHAR(100),
            estado VARCHAR(2),
            pais VARCHAR(50) DEFAULT 'Brasil',
            notificacao_desativada TINYINT(1) DEFAULT 0,
            emails_adicionais VARCHAR(255),
            referencia_externa VARCHAR(100),
            observacoes TEXT,
            razao_social VARCHAR(255),
            criado_em_asaas DATETIME,
            cpf_cnpj VARCHAR(32),
            data_criacao DATETIME DEFAULT CURRENT_TIMESTAMP,
            data_atualizacao DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_asaas_id (asaas_id),
            INDEX idx_cpf_cnpj (cpf_cnpj),
            INDEX idx_email (email)
        )";
        
        if ($mysqli->query($sql)) {
            echo "   ✅ Tabela 'clientes' criada com sucesso\n";
        } else {
            throw new Exception("Erro ao criar tabela clientes: " . $mysqli->error);
        }
    } else {
        echo "   ✅ Tabela 'clientes' existe\n";
    }
    
    // 2. Verificar e corrigir tabela cobrancas
    echo "\n2. Verificando tabela 'cobrancas'...\n";
    
    $result = $mysqli->query("SHOW TABLES LIKE 'cobrancas'");
    if ($result->num_rows === 0) {
        echo "   ❌ Tabela 'cobrancas' não existe. Criando...\n";
        
        $sql = "CREATE TABLE cobrancas (
            id INT AUTO_INCREMENT PRIMARY KEY,
            asaas_payment_id VARCHAR(64) NOT NULL UNIQUE,
            cliente_id INT,
            valor DECIMAL(10,2) NOT NULL,
            status VARCHAR(50) NOT NULL DEFAULT 'PENDING',
            vencimento DATE NOT NULL,
            data_pagamento DATE,
            data_criacao DATETIME DEFAULT CURRENT_TIMESTAMP,
            data_atualizacao DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            descricao VARCHAR(255),
            tipo VARCHAR(50) DEFAULT 'BOLETO',
            tipo_pagamento VARCHAR(20),
            url_fatura VARCHAR(255),
            parcela VARCHAR(32),
            assinatura_id VARCHAR(64),
            INDEX idx_asaas_payment_id (asaas_payment_id),
            INDEX idx_cliente_id (cliente_id),
            INDEX idx_status (status),
            INDEX idx_vencimento (vencimento),
            FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE SET NULL
        )";
        
        if ($mysqli->query($sql)) {
            echo "   ✅ Tabela 'cobrancas' criada com sucesso\n";
        } else {
            throw new Exception("Erro ao criar tabela cobrancas: " . $mysqli->error);
        }
    } else {
        echo "   ✅ Tabela 'cobrancas' existe\n";
        
        // Verificar se tem as colunas necessárias
        $result = $mysqli->query("DESCRIBE cobrancas");
        $colunas = [];
        while ($row = $result->fetch_assoc()) {
            $colunas[] = $row['Field'];
        }
        
        $colunasNecessarias = ['asaas_payment_id', 'cliente_id', 'valor', 'status', 'vencimento', 'data_pagamento'];
        foreach ($colunasNecessarias as $coluna) {
            if (!in_array($coluna, $colunas)) {
                echo "   ⚠️  Coluna '$coluna' não existe na tabela cobrancas\n";
            }
        }
    }
    
    // 3. Verificar e corrigir tabela assinaturas
    echo "\n3. Verificando tabela 'assinaturas'...\n";
    
    $result = $mysqli->query("SHOW TABLES LIKE 'assinaturas'");
    if ($result->num_rows === 0) {
        echo "   ❌ Tabela 'assinaturas' não existe. Criando...\n";
        
        $sql = "CREATE TABLE assinaturas (
            id INT AUTO_INCREMENT PRIMARY KEY,
            cliente_id INT NOT NULL,
            asaas_id VARCHAR(255) NOT NULL UNIQUE,
            status VARCHAR(50) NOT NULL DEFAULT 'ACTIVE',
            periodicidade VARCHAR(20) NOT NULL,
            start_date DATE,
            next_due_date DATE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_asaas_id (asaas_id),
            INDEX idx_cliente_id (cliente_id),
            INDEX idx_status (status),
            FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE CASCADE
        )";
        
        if ($mysqli->query($sql)) {
            echo "   ✅ Tabela 'assinaturas' criada com sucesso\n";
        } else {
            throw new Exception("Erro ao criar tabela assinaturas: " . $mysqli->error);
        }
    } else {
        echo "   ✅ Tabela 'assinaturas' existe\n";
    }
    
    // 4. Verificar e corrigir tabela faturas (se necessário)
    echo "\n4. Verificando tabela 'faturas'...\n";
    
    $result = $mysqli->query("SHOW TABLES LIKE 'faturas'");
    if ($result->num_rows === 0) {
        echo "   ❌ Tabela 'faturas' não existe. Criando...\n";
        
        $sql = "CREATE TABLE faturas (
            id INT AUTO_INCREMENT PRIMARY KEY,
            cliente_id INT NOT NULL,
            asaas_id VARCHAR(255) NOT NULL UNIQUE,
            valor DECIMAL(10,2) NOT NULL,
            status VARCHAR(50) NOT NULL DEFAULT 'PENDING',
            invoice_url VARCHAR(255),
            due_date DATE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_asaas_id (asaas_id),
            INDEX idx_cliente_id (cliente_id),
            INDEX idx_status (status),
            FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE CASCADE
        )";
        
        if ($mysqli->query($sql)) {
            echo "   ✅ Tabela 'faturas' criada com sucesso\n";
        } else {
            throw new Exception("Erro ao criar tabela faturas: " . $mysqli->error);
        }
    } else {
        echo "   ✅ Tabela 'faturas' existe\n";
    }
    
    // 5. Criar diretório de logs se não existir
    echo "\n5. Verificando diretório de logs...\n";
    
    $logDir = __DIR__ . '/logs';
    if (!is_dir($logDir)) {
        if (mkdir($logDir, 0755, true)) {
            echo "   ✅ Diretório de logs criado: $logDir\n";
        } else {
            echo "   ⚠️  Não foi possível criar o diretório de logs\n";
        }
    } else {
        echo "   ✅ Diretório de logs existe\n";
    }
    
    // 6. Verificar dados existentes
    echo "\n6. Verificando dados existentes...\n";
    
    $result = $mysqli->query("SELECT COUNT(*) as total FROM clientes");
    $totalClientes = $result->fetch_assoc()['total'];
    echo "   - Clientes: $totalClientes\n";
    
    $result = $mysqli->query("SELECT COUNT(*) as total FROM cobrancas");
    $totalCobrancas = $result->fetch_assoc()['total'];
    echo "   - Cobranças: $totalCobrancas\n";
    
    $result = $mysqli->query("SELECT COUNT(*) as total FROM assinaturas");
    $totalAssinaturas = $result->fetch_assoc()['total'];
    echo "   - Assinaturas: $totalAssinaturas\n";
    
    $result = $mysqli->query("SELECT COUNT(*) as total FROM faturas");
    $totalFaturas = $result->fetch_assoc()['total'];
    echo "   - Faturas: $totalFaturas\n";
    
    $mysqli->close();
    
    echo "\n✅ Verificação concluída com sucesso!\n";
    echo "\n📋 PRÓXIMOS PASSOS:\n";
    echo "1. Configure o webhook no painel do Asaas para: " . $_SERVER['HTTP_HOST'] . "/api/webhooks.php\n";
    echo "2. Teste o webhook executando: php test_webhook.php\n";
    echo "3. Execute a sincronização: php painel/sincroniza_asaas.php\n";
    
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
}
?> 