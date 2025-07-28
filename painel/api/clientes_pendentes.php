<?php
/**
 * API para gerenciar clientes pendentes (sistema similar ao Kommo CRM)
 */
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../db.php';

header('Content-Type: application/json');

$action = $_GET['action'] ?? $_POST['action'] ?? 'list';

switch ($action) {
    case 'list':
        // Listar clientes pendentes
        $sql = "SELECT cp.*, 
                       COUNT(mp.id) as total_mensagens_db
                FROM clientes_pendentes cp
                LEFT JOIN mensagens_pendentes mp ON cp.id = mp.cliente_pendente_id
                WHERE cp.status = 'pendente'
                GROUP BY cp.id
                ORDER BY cp.data_ultima_mensagem DESC, cp.data_criacao DESC
                LIMIT 100";
        
        $result = $mysqli->query($sql);
        $pendentes = [];
        
        while ($row = $result->fetch_assoc()) {
            $pendentes[] = [
                'id' => intval($row['id']),
                'numero_whatsapp' => $row['numero_whatsapp'],
                'numero_formatado' => $row['numero_formatado'],
                'primeira_mensagem' => $row['primeira_mensagem'],
                'data_primeira_mensagem' => $row['data_primeira_mensagem'],
                'ultima_mensagem' => $row['ultima_mensagem'],
                'data_ultima_mensagem' => $row['data_ultima_mensagem'],
                'total_mensagens' => intval($row['total_mensagens']),
                'data_criacao' => $row['data_criacao'],
                'dados_extras' => $row['dados_extras'] ? json_decode($row['dados_extras'], true) : null
            ];
        }
        
        echo json_encode([
            'success' => true,
            'pendentes' => $pendentes,
            'total' => count($pendentes)
        ]);
        break;
        
    case 'messages':
        // Listar mensagens de um cliente pendente
        $pendente_id = intval($_GET['pendente_id'] ?? 0);
        
        if (!$pendente_id) {
            echo json_encode(['success' => false, 'error' => 'ID do cliente pendente requerido']);
            exit;
        }
        
        $sql = "SELECT * FROM mensagens_pendentes 
                WHERE cliente_pendente_id = $pendente_id 
                ORDER BY data_hora ASC";
        
        $result = $mysqli->query($sql);
        $mensagens = [];
        
        while ($row = $result->fetch_assoc()) {
            $mensagens[] = [
                'id' => intval($row['id']),
                'mensagem' => $row['mensagem'],
                'tipo' => $row['tipo'],
                'data_hora' => $row['data_hora'],
                'direcao' => $row['direcao']
            ];
        }
        
        echo json_encode([
            'success' => true,
            'mensagens' => $mensagens,
            'total' => count($mensagens)
        ]);
        break;
        
    case 'approve':
        // Aprovar cliente pendente
        $pendente_id = intval($_POST['pendente_id'] ?? 0);
        $nome_cliente = trim($_POST['nome_cliente'] ?? '');
        $email_cliente = trim($_POST['email_cliente'] ?? '');
        
        if (!$pendente_id) {
            echo json_encode(['success' => false, 'error' => 'ID do cliente pendente requerido']);
            exit;
        }
        
        // Buscar dados do cliente pendente
        $sql_pendente = "SELECT * FROM clientes_pendentes WHERE id = $pendente_id AND status = 'pendente' LIMIT 1";
        $result_pendente = $mysqli->query($sql_pendente);
        
        if (!$result_pendente || $result_pendente->num_rows === 0) {
            echo json_encode(['success' => false, 'error' => 'Cliente pendente não encontrado']);
            exit;
        }
        
        $pendente = $result_pendente->fetch_assoc();
        
        // Nome padrão se não fornecido
        if (!$nome_cliente) {
            $nome_cliente = "Cliente WhatsApp " . $pendente['numero_formatado'];
        }
        
        // Iniciar transação
        $mysqli->begin_transaction();
        
        try {
            // Criar cliente definitivo
            $nome_escaped = $mysqli->real_escape_string($nome_cliente);
            $email_escaped = $mysqli->real_escape_string($email_cliente);
            $data_criacao = date('Y-m-d H:i:s');
            
            $sql_criar = "INSERT INTO clientes (nome, celular, telefone, email, data_criacao, data_atualizacao) 
                         VALUES ('$nome_escaped', 
                                '" . $mysqli->real_escape_string($pendente['numero_whatsapp']) . "',
                                '" . $mysqli->real_escape_string($pendente['numero_formatado']) . "',
                                '$email_escaped', '$data_criacao', '$data_criacao')";
            
            if (!$mysqli->query($sql_criar)) {
                throw new Exception('Erro ao criar cliente: ' . $mysqli->error);
            }
            
            $novo_cliente_id = $mysqli->insert_id;
            
            // Buscar canal WhatsApp
            $canal_id = 1; // Padrão
            $canal_result = $mysqli->query("SELECT id FROM canais_comunicacao WHERE tipo = 'whatsapp' LIMIT 1");
            if ($canal_result && $canal_result->num_rows > 0) {
                $canal = $canal_result->fetch_assoc();
                $canal_id = $canal['id'];
            }
            
            // Migrar mensagens pendentes para mensagens definitivas
            $sql_mensagens = "SELECT * FROM mensagens_pendentes WHERE cliente_pendente_id = $pendente_id ORDER BY data_hora ASC";
            $result_mensagens = $mysqli->query($sql_mensagens);
            
            $mensagens_migradas = 0;
            while ($msg = $result_mensagens->fetch_assoc()) {
                $sql_migrar = "INSERT INTO mensagens_comunicacao 
                              (canal_id, cliente_id, mensagem, tipo, data_hora, direcao, status) 
                              VALUES ($canal_id, $novo_cliente_id, 
                                     '" . $mysqli->real_escape_string($msg['mensagem']) . "',
                                     '" . $mysqli->real_escape_string($msg['tipo']) . "',
                                     '" . $msg['data_hora'] . "', 
                                     '" . $msg['direcao'] . "', 'recebido')";
                
                if ($mysqli->query($sql_migrar)) {
                    $mensagens_migradas++;
                }
            }
            
            // Marcar cliente pendente como aprovado
            $sql_aprovar = "UPDATE clientes_pendentes SET 
                           status = 'aprovado',
                           data_decisao = NOW(),
                           usuario_decisao = 'admin'
                           WHERE id = $pendente_id";
            
            if (!$mysqli->query($sql_aprovar)) {
                throw new Exception('Erro ao marcar como aprovado');
            }
            
            // Confirmar transação
            $mysqli->commit();
            
            // Invalidar cache para mostrar novo cliente
            require_once '../cache_invalidator.php';
            invalidate_conversations_cache();
            
            echo json_encode([
                'success' => true,
                'message' => 'Cliente aprovado com sucesso',
                'cliente_id' => $novo_cliente_id,
                'nome' => $nome_cliente,
                'mensagens_migradas' => $mensagens_migradas
            ]);
            
        } catch (Exception $e) {
            $mysqli->rollback();
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        break;
        
    case 'reject':
        // Rejeitar cliente pendente
        $pendente_id = intval($_POST['pendente_id'] ?? 0);
        $motivo = trim($_POST['motivo'] ?? 'Rejeitado pelo administrador');
        
        if (!$pendente_id) {
            echo json_encode(['success' => false, 'error' => 'ID do cliente pendente requerido']);
            exit;
        }
        
        $motivo_escaped = $mysqli->real_escape_string($motivo);
        $sql_rejeitar = "UPDATE clientes_pendentes SET 
                        status = 'rejeitado',
                        data_decisao = NOW(),
                        usuario_decisao = 'admin',
                        motivo_rejeicao = '$motivo_escaped'
                        WHERE id = $pendente_id AND status = 'pendente'";
        
        if ($mysqli->query($sql_rejeitar)) {
            // Opcional: deletar mensagens pendentes após um tempo
            echo json_encode([
                'success' => true,
                'message' => 'Cliente rejeitado com sucesso'
            ]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Erro ao rejeitar cliente']);
        }
        break;
        
    case 'stats':
        // Estatísticas dos pendentes
        $stats = [];
        
        $result = $mysqli->query("SELECT COUNT(*) as total FROM clientes_pendentes WHERE status = 'pendente'");
        $stats['total_pendentes'] = $result->fetch_assoc()['total'];
        
        $result = $mysqli->query("SELECT COUNT(*) as total FROM clientes_pendentes WHERE status = 'aprovado'");
        $stats['total_aprovados'] = $result->fetch_assoc()['total'];
        
        $result = $mysqli->query("SELECT COUNT(*) as total FROM clientes_pendentes WHERE status = 'rejeitado'");
        $stats['total_rejeitados'] = $result->fetch_assoc()['total'];
        
        $result = $mysqli->query("SELECT COUNT(*) as total FROM clientes_pendentes WHERE status = 'pendente' AND DATE(data_criacao) = CURDATE()");
        $stats['pendentes_hoje'] = $result->fetch_assoc()['total'];
        
        echo json_encode([
            'success' => true,
            'stats' => $stats
        ]);
        break;
        
    default:
        echo json_encode(['success' => false, 'error' => 'Ação inválida']);
}
?> 