<?php
// Migration para criar a tabela de canais padrão por função
require_once __DIR__ . '/../painel/db.php';
$sql = "CREATE TABLE IF NOT EXISTS canais_padrao_funcoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    funcao VARCHAR(50) NOT NULL,
    canal_id INT NOT NULL,
    UNIQUE KEY (funcao)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
if ($mysqli->query($sql)) {
    echo "Tabela canais_padrao_funcoes criada com sucesso!\n";
} else {
    echo "Erro ao criar tabela: " . $mysqli->error . "\n";
} 