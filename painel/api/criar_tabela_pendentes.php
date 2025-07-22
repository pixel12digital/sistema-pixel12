<?php
/**
 * Script para criar tabela de clientes pendentes
 */
require_once '../config.php';
require_once '../db.php';

// Criar tabela de clientes pendentes
$sql = "CREATE TABLE IF NOT EXISTS clientes_pendentes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    numero_whatsapp VARCHAR(20) NOT NULL,
    numero_formatado VARCHAR(25) NOT NULL,
    primeira_mensagem TEXT,
    data_primeira_mensagem DATETIME,
    total_mensagens INT DEFAULT 1,
    ultima_mensagem TEXT,
    data_ultima_mensagem DATETIME,
    status ENUM('pendente', 'aprovado', 'rejeitado') DEFAULT 'pendente',
    data_criacao DATETIME DEFAULT CURRENT_TIMESTAMP,
    data_decisao DATETIME NULL,
    usuario_decisao VARCHAR(100) NULL,
    motivo_rejeicao TEXT NULL,
    dados_extras JSON NULL,
    INDEX idx_numero (numero_whatsapp),
    INDEX idx_status (status),
    INDEX idx_data_criacao (data_criacao)
)";

if ($mysqli->query($sql)) {
    echo "✅ Tabela clientes_pendentes criada com sucesso!\n";
} else {
    echo "❌ Erro ao criar tabela: " . $mysqli->error . "\n";
}

// Criar tabela de mensagens pendentes
$sql_mensagens = "CREATE TABLE IF NOT EXISTS mensagens_pendentes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cliente_pendente_id INT NOT NULL,
    numero_whatsapp VARCHAR(20) NOT NULL,
    mensagem TEXT NOT NULL,
    tipo VARCHAR(20) DEFAULT 'text',
    data_hora DATETIME NOT NULL,
    direcao ENUM('recebido', 'enviado') DEFAULT 'recebido',
    dados_webhook JSON NULL,
    FOREIGN KEY (cliente_pendente_id) REFERENCES clientes_pendentes(id) ON DELETE CASCADE,
    INDEX idx_cliente_pendente (cliente_pendente_id),
    INDEX idx_data_hora (data_hora)
)";

if ($mysqli->query($sql_mensagens)) {
    echo "✅ Tabela mensagens_pendentes criada com sucesso!\n";
} else {
    echo "❌ Erro ao criar tabela mensagens: " . $mysqli->error . "\n";
}

echo "\n🎯 Sistema de aprovação manual configurado!\n";
?> 