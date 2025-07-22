<?php
/**
 * API para gerenciar clientes criados automaticamente via WhatsApp
 */
require_once '../config.php';
require_once '../db.php';

header('Content-Type: application/json');

$action = $_GET['action'] ?? 'list';

switch ($action) {
    case 'list':
        // Listar clientes criados automaticamente
        $sql = "SELECT c.*, 
                       COUNT(m.id) as total_mensagens,
                       MAX(m.data_hora) as ultima_mensagem
                FROM clientes c
                LEFT JOIN mensagens_comunicacao m ON c.id = m.cliente_id
                WHERE c.nome LIKE 'Cliente WhatsApp%'
                GROUP BY c.id
                ORDER BY ultima_mensagem DESC, c.data_criacao DESC
                LIMIT 50";
        
        $result = $mysqli->query($sql);
        $clientes = [];
        
        while ($row = $result->fetch_assoc()) {
            $clientes[] = [
                'id' => $row['id'],
                'nome' => $row['nome'],
                'celular' => $row['celular'],
                'telefone' => $row['telefone'],
                'email' => $row['email'],
                'data_criacao' => $row['data_criacao'],
                'total_mensagens' => intval($row['total_mensagens']),
                'ultima_mensagem' => $row['ultima_mensagem'],
                'status' => $row['total_mensagens'] > 0 ? 'ativo' : 'inativo'
            ];
        }
        
        echo json_encode([
            'success' => true,
            'clientes' => $clientes,
            'total' => count($clientes)
        ]);
        break;
        
    case 'update':
        // Atualizar dados de um cliente
        $cliente_id = intval($_POST['cliente_id'] ?? 0);
        $nome = trim($_POST['nome'] ?? '');
        $email = trim($_POST['email'] ?? '');
        
        if (!$cliente_id || !$nome) {
            echo json_encode(['success' => false, 'error' => 'Dados obrigatórios ausentes']);
            exit;
        }
        
        $nome_escaped = $mysqli->real_escape_string($nome);
        $email_escaped = $mysqli->real_escape_string($email);
        
        $sql = "UPDATE clientes SET 
                nome = '$nome_escaped', 
                email = '$email_escaped',
                data_atualizacao = NOW()
                WHERE id = $cliente_id";
        
        if ($mysqli->query($sql)) {
            echo json_encode(['success' => true, 'message' => 'Cliente atualizado com sucesso']);
        } else {
            echo json_encode(['success' => false, 'error' => 'Erro ao atualizar cliente: ' . $mysqli->error]);
        }
        break;
        
    case 'stats':
        // Estatísticas de clientes automáticos
        $stats = [];
        
        // Total de clientes automáticos
        $result = $mysqli->query("SELECT COUNT(*) as total FROM clientes WHERE nome LIKE 'Cliente WhatsApp%'");
        $stats['total_automaticos'] = $result->fetch_assoc()['total'];
        
        // Clientes com mensagens
        $result = $mysqli->query("SELECT COUNT(DISTINCT c.id) as total 
                                 FROM clientes c 
                                 INNER JOIN mensagens_comunicacao m ON c.id = m.cliente_id 
                                 WHERE c.nome LIKE 'Cliente WhatsApp%'");
        $stats['com_mensagens'] = $result->fetch_assoc()['total'];
        
        // Criados hoje
        $result = $mysqli->query("SELECT COUNT(*) as total FROM clientes 
                                 WHERE nome LIKE 'Cliente WhatsApp%' 
                                 AND DATE(data_criacao) = CURDATE()");
        $stats['criados_hoje'] = $result->fetch_assoc()['total'];
        
        // Criados esta semana
        $result = $mysqli->query("SELECT COUNT(*) as total FROM clientes 
                                 WHERE nome LIKE 'Cliente WhatsApp%' 
                                 AND data_criacao >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)");
        $stats['criados_semana'] = $result->fetch_assoc()['total'];
        
        echo json_encode([
            'success' => true,
            'stats' => $stats
        ]);
        break;
        
    default:
        echo json_encode(['success' => false, 'error' => 'Ação inválida']);
}
?> 