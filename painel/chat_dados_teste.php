<?php
require_once 'config.php';
require_once 'db.php';

// Dados de teste para demonstração do chat
$dados_teste = [
    'canais' => [
        [
            'tipo' => 'whatsapp',
            'identificador' => '5511999999999',
            'nome_exibicao' => 'Suporte Principal',
            'status' => 'conectado',
            'data_conexao' => '2024-01-15 10:00:00'
        ],
        [
            'tipo' => 'whatsapp',
            'identificador' => '5511888888888',
            'nome_exibicao' => 'Vendas',
            'status' => 'conectado',
            'data_conexao' => '2024-01-16 14:30:00'
        ]
    ],
    'mensagens' => [
        [
            'canal_id' => 1,
            'cliente_id' => 1,
            'mensagem' => 'Olá! Preciso de ajuda com meu pedido.',
            'tipo' => 'texto',
            'data_hora' => '2024-01-20 09:15:00',
            'direcao' => 'recebido',
            'status' => 'lido'
        ],
        [
            'canal_id' => 1,
            'cliente_id' => 1,
            'mensagem' => 'Claro! Pode me informar o número do seu pedido?',
            'tipo' => 'texto',
            'data_hora' => '2024-01-20 09:16:00',
            'direcao' => 'enviado',
            'status' => 'entregue'
        ],
        [
            'canal_id' => 1,
            'cliente_id' => 1,
            'mensagem' => 'É o pedido #12345',
            'tipo' => 'texto',
            'data_hora' => '2024-01-20 09:18:00',
            'direcao' => 'recebido',
            'status' => 'lido'
        ],
        [
            'canal_id' => 2,
            'cliente_id' => 2,
            'mensagem' => 'Gostaria de saber sobre os preços dos seus produtos.',
            'tipo' => 'texto',
            'data_hora' => '2024-01-20 10:30:00',
            'direcao' => 'recebido',
            'status' => 'lido'
        ],
        [
            'canal_id' => 2,
            'cliente_id' => 2,
            'mensagem' => 'Vou te enviar nosso catálogo de preços!',
            'tipo' => 'texto',
            'data_hora' => '2024-01-20 10:32:00',
            'direcao' => 'enviado',
            'status' => 'entregue'
        ]
    ]
];

echo "<h2>Inserindo dados de teste para o chat...</h2>";

// Inserir canais de teste
foreach ($dados_teste['canais'] as $canal) {
    $sql = "INSERT IGNORE INTO canais_comunicacao (tipo, identificador, nome_exibicao, status, data_conexao) 
            VALUES ('{$canal['tipo']}', '{$canal['identificador']}', '{$canal['nome_exibicao']}', '{$canal['status']}', '{$canal['data_conexao']}')";
    
    if ($mysqli->query($sql)) {
        echo "<p>✅ Canal '{$canal['nome_exibicao']}' inserido/verificado</p>";
    } else {
        echo "<p>❌ Erro ao inserir canal: " . $mysqli->error . "</p>";
    }
}

// Inserir mensagens de teste
foreach ($dados_teste['mensagens'] as $mensagem) {
    $sql = "INSERT IGNORE INTO mensagens_comunicacao (canal_id, cliente_id, mensagem, tipo, data_hora, direcao, status) 
            VALUES ({$mensagem['canal_id']}, {$mensagem['cliente_id']}, '{$mensagem['mensagem']}', '{$mensagem['tipo']}', '{$mensagem['data_hora']}', '{$mensagem['direcao']}', '{$mensagem['status']}')";
    
    if ($mysqli->query($sql)) {
        echo "<p>✅ Mensagem inserida/verificada</p>";
    } else {
        echo "<p>❌ Erro ao inserir mensagem: " . $mysqli->error . "</p>";
    }
}

echo "<h3>✅ Dados de teste inseridos com sucesso!</h3>";
echo "<p><a href='chat.php'>Acessar o Chat Centralizado</a></p>";
?> 