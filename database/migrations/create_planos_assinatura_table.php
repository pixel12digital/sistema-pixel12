<?php
// Migration para criar tabela de planos de assinatura
require_once __DIR__ . '/../painel/db.php';
$sql = "CREATE TABLE IF NOT EXISTS planos_assinatura (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(100) NOT NULL,
  descricao TEXT,
  valor DECIMAL(10,2) NOT NULL,
  periodicidade ENUM('mensal','trimestral','semestral','anual') NOT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
if ($mysqli->query($sql)) {
  echo "Tabela planos_assinatura criada com sucesso!\n";
} else {
  echo "Erro ao criar tabela: " . $mysqli->error . "\n";
} 