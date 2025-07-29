<?php
require_once 'config.php';
require_once 'painel/db.php';

echo "🔧 CORRIGINDO CLIENTES DUPLICADOS NO BANCO DE DADOS\n";
echo "==================================================\n\n";

// Função para mostrar detalhes de um cliente
function mostrarCliente($id) {
    global $mysqli;
    $sql = "SELECT id, nome, email, cpf_cnpj, telefone, asaas_id, data_criacao, data_atualizacao 
            FROM clientes WHERE id = $id";
    $result = $mysqli->query($sql);
    if ($result && $result->num_rows > 0) {
        $cliente = $result->fetch_assoc();
        echo "   ID: {$cliente['id']}\n";
        echo "   Nome: {$cliente['nome']}\n";
        echo "   Email: {$cliente['email']}\n";
        echo "   CPF/CNPJ: {$cliente['cpf_cnpj']}\n";
        echo "   Telefone: {$cliente['telefone']}\n";
        echo "   Asaas ID: {$cliente['asaas_id']}\n";
        echo "   Criado: {$cliente['data_criacao']}\n";
        echo "   Atualizado: {$cliente['data_atualizacao']}\n";
        return $cliente;
    }
    return null;
}

// Verificar duplicatas por CPF/CNPJ
echo "📊 Verificando duplicatas por CPF/CNPJ:\n";
$sql = "SELECT cpf_cnpj, COUNT(*) as total 
        FROM clientes 
        WHERE cpf_cnpj IS NOT NULL AND cpf_cnpj != '' 
        GROUP BY cpf_cnpj 
        HAVING COUNT(*) > 1 
        ORDER BY total DESC";

$result = $mysqli->query($sql);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $cpf = $row['cpf_cnpj'];
        $total = $row['total'];
        
        echo "\n🔍 CPF/CNPJ duplicado: $cpf ($total registros)\n";
        echo "==========================================\n";
        
        // Buscar todos os registros com este CPF
        $sql_clientes = "SELECT id, nome, email, cpf_cnpj, telefone, asaas_id, data_criacao, data_atualizacao 
                        FROM clientes 
                        WHERE cpf_cnpj = '$cpf' 
                        ORDER BY data_criacao";
        
        $clientes = $mysqli->query($sql_clientes);
        $registros = [];
        
        while ($cliente = $clientes->fetch_assoc()) {
            $registros[] = $cliente;
            echo "\n📋 Registro {$cliente['id']}:\n";
            mostrarCliente($cliente['id']);
        }
        
        // Decidir qual registro manter (geralmente o mais antigo ou mais completo)
        echo "\n🤔 ANÁLISE PARA DECISÃO:\n";
        
        $manter_id = null;
        $motivo = "";
        
        // Critérios para decidir qual manter:
        // 1. Se um tem asaas_id e outro não, manter o que tem
        // 2. Se ambos têm asaas_id, manter o mais antigo
        // 3. Se nenhum tem asaas_id, manter o mais antigo
        
        $com_asaas = array_filter($registros, function($r) { return !empty($r['asaas_id']); });
        $sem_asaas = array_filter($registros, function($r) { return empty($r['asaas_id']); });
        
        if (count($com_asaas) > 0) {
            // Manter o que tem asaas_id (mais antigo se houver mais de um)
            $manter = reset($com_asaas);
            foreach ($com_asaas as $r) {
                if (strtotime($r['data_criacao']) < strtotime($manter['data_criacao'])) {
                    $manter = $r;
                }
            }
            $manter_id = $manter['id'];
            $motivo = "Tem ID do Asaas e é o mais antigo";
        } else {
            // Manter o mais antigo
            $manter = reset($registros);
            foreach ($registros as $r) {
                if (strtotime($r['data_criacao']) < strtotime($manter['data_criacao'])) {
                    $manter = $r;
                }
            }
            $manter_id = $manter['id'];
            $motivo = "Mais antigo (nenhum tem ID do Asaas)";
        }
        
        echo "✅ DECISÃO: Manter registro ID $manter_id - $motivo\n";
        
        // Listar registros que serão removidos
        $remover_ids = [];
        foreach ($registros as $r) {
            if ($r['id'] != $manter_id) {
                $remover_ids[] = $r['id'];
            }
        }
        
        if (!empty($remover_ids)) {
            echo "🗑️  Registros que serão removidos: " . implode(', ', $remover_ids) . "\n";
            
            // Perguntar se deve prosseguir (em produção, você pode querer fazer backup primeiro)
            echo "\n⚠️  ATENÇÃO: Esta operação irá remover registros permanentemente!\n";
            echo "Deseja prosseguir? (s/n): ";
            
            // Em produção, você pode querer comentar esta parte e fazer a remoção automaticamente
            // ou implementar um sistema de backup antes da remoção
            
            // Por segurança, vou apenas mostrar o que seria feito
            echo "🔒 MODO SEGURO: Apenas mostrando o que seria feito\n";
            echo "Para executar a remoção, edite este script e remova a verificação de segurança\n";
            
            /*
            // Código para remoção (descomente para executar)
            foreach ($remover_ids as $id) {
                $sql_delete = "DELETE FROM clientes WHERE id = $id";
                if ($mysqli->query($sql_delete)) {
                    echo "✅ Removido registro ID $id\n";
                } else {
                    echo "❌ Erro ao remover registro ID $id: " . $mysqli->error . "\n";
                }
            }
            */
        }
        
        echo "\n" . str_repeat("=", 50) . "\n";
    }
} else {
    echo "✅ Nenhuma duplicata encontrada por CPF/CNPJ\n";
}

echo "\n📈 RESUMO FINAL:\n";
$total_clientes = $mysqli->query("SELECT COUNT(*) as total FROM clientes");
if ($total_clientes) {
    $total = $total_clientes->fetch_assoc()['total'];
    echo "   Total de clientes no banco: $total\n";
}

$mysqli->close();
echo "\n✅ Verificação concluída!\n";
?> 