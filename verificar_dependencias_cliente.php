<?php
require_once 'config.php';
require_once 'painel/db.php';

echo "🔍 VERIFICANDO DEPENDÊNCIAS DO CLIENTE DUPLICADO\n";
echo "===============================================\n\n";

$cliente_id = 4295; // ID do cliente que queremos remover
$cliente_manter_id = 156; // ID do cliente que vamos manter

echo "📊 Verificando dependências do cliente ID $cliente_id:\n\n";

// Verificar tabela cobrancas
echo "💰 TABELA COBRANÇAS:\n";
$sql_cobrancas = "SELECT COUNT(*) as total FROM cobrancas WHERE cliente_id = $cliente_id";
$result = $mysqli->query($sql_cobrancas);
if ($result) {
    $total = $result->fetch_assoc()['total'];
    echo "   Total de cobranças: $total\n";
    
    if ($total > 0) {
        echo "   📋 Detalhes das cobranças:\n";
        $sql_detalhes = "SELECT id, asaas_id, valor, status, data_vencimento, data_criacao 
                        FROM cobrancas WHERE cliente_id = $cliente_id";
        $detalhes = $mysqli->query($sql_detalhes);
        while ($cobranca = $detalhes->fetch_assoc()) {
            echo "      ID: {$cobranca['id']} | Asaas: {$cobranca['asaas_id']} | Valor: {$cobranca['valor']} | Status: {$cobranca['status']}\n";
        }
    }
}

echo "\n";

// Verificar outras tabelas que podem referenciar clientes
$tabelas_para_verificar = [
    'pedidos' => 'cliente_id',
    'mensagens' => 'cliente_id',
    'historico_pagamentos' => 'cliente_id',
    'notificacoes' => 'cliente_id'
];

foreach ($tabelas_para_verificar as $tabela => $campo) {
    echo "📋 TABELA " . strtoupper($tabela) . ":\n";
    $sql = "SELECT COUNT(*) as total FROM $tabela WHERE $campo = $cliente_id";
    $result = $mysqli->query($sql);
    if ($result) {
        $total = $result->fetch_assoc()['total'];
        echo "   Total de registros: $total\n";
    } else {
        echo "   Tabela não existe ou erro na consulta\n";
    }
    echo "\n";
}

echo "🎯 ESTRATÉGIA DE CORREÇÃO:\n";
echo "==========================\n";

// Verificar se o cliente que vamos manter tem as mesmas dependências
echo "📊 Verificando dependências do cliente que vamos manter (ID $cliente_manter_id):\n";

$sql_cobrancas_manter = "SELECT COUNT(*) as total FROM cobrancas WHERE cliente_id = $cliente_manter_id";
$result = $mysqli->query($sql_cobrancas_manter);
if ($result) {
    $total_manter = $result->fetch_assoc()['total'];
    echo "   Cobranças do cliente a manter: $total_manter\n";
}

echo "\n💡 RECOMENDAÇÕES:\n";
echo "   1. Se houver cobranças no cliente duplicado, transferir para o cliente principal\n";
echo "   2. Atualizar todas as referências de $cliente_id para $cliente_manter_id\n";
echo "   3. Só então remover o cliente duplicado\n";
echo "   4. Verificar se não há conflitos de dados\n";

$mysqli->close();
?> 