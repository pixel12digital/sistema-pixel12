-- Tabela para canais de comunicação (WhatsApp, Messenger, etc)
CREATE TABLE canais_comunicacao (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tipo VARCHAR(32) NOT NULL, -- ex: 'whatsapp'
    identificador VARCHAR(64) NOT NULL, -- ex: número ou id do canal
    status VARCHAR(32) NOT NULL, -- ex: 'conectado', 'offline'
    nome_exibicao VARCHAR(64),
    data_conexao DATETIME,
    UNIQUE KEY (tipo, identificador)
);

-- Tabela para mensagens de comunicação
CREATE TABLE mensagens_comunicacao (
    id INT AUTO_INCREMENT PRIMARY KEY,
    canal_id INT NOT NULL,
    cliente_id INT,
    mensagem TEXT NOT NULL,
    tipo VARCHAR(32) NOT NULL, -- ex: 'texto', 'imagem'
    data_hora DATETIME NOT NULL,
    direcao VARCHAR(16) NOT NULL, -- 'enviado' ou 'recebido'
    status VARCHAR(32), -- ex: 'entregue', 'lido'
    FOREIGN KEY (canal_id) REFERENCES canais_comunicacao(id)
); 