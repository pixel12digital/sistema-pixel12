<?php
file_put_contents(__DIR__ . '/debug_webhook.log', date('Y-m-d H:i:s') . " - Teste de escrita\n", FILE_APPEND);
error_log("[WEBHOOK] Teste de escrita executado", 0);
header('Content-Type: application/json');
require_once '../config.php';
require_once '../db.php';

$input = file_get_contents('php://input');
$data = json_decode($input, true);
// Logar o conteúdo recebido
file_put_contents(__DIR__ . '/debug_webhook.log', date('Y-m-d H:i:s') . " - Dados recebidos: " . var_export($data, true) . "\n", FILE_APPEND);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método não permitido']);
    exit;
}

// Se for atualização de status do canal
if (isset($data['identificador']) && isset($data['status'])) {
    $identificador = $mysqli->real_escape_string($data['identificador']);
    $status = $mysqli->real_escape_string($data['status']);
    $mysqli->query("UPDATE canais_comunicacao SET status = '$status', data_conexao = NOW() WHERE identificador = '$identificador' LIMIT 1");
    echo json_encode(['success' => true]);
    exit;
}

// Se for mensagem recebida
if (isset($data['canal_id']) && isset($data['numero']) && isset($data['mensagem'])) {
    try {
        $canal_id = intval($data['canal_id']);
        $numero = $data['numero'];
        $mensagem = trim($data['mensagem']);
        $tipo = $data['tipo'] ?? 'texto';

        // Logar se algum campo está vazio
        if (!$canal_id || !$numero || !$mensagem) {
            $log_campos = date('Y-m-d H:i:s') . " - Campo vazio: canal_id='$canal_id', numero='$numero', mensagem='$mensagem'\n";
            file_put_contents(__DIR__ . '/debug_webhook.log', $log_campos, FILE_APPEND);
            throw new Exception('Dados incompletos');
        }

        // Verificar se o canal existe
        $canal = $mysqli->query("SELECT * FROM canais_comunicacao WHERE id = $canal_id")->fetch_assoc();
        if (!$canal) {
            throw new Exception('Canal não encontrado');
        }

        // Buscar cliente pelo número (testar múltiplos formatos em celular e telefone)
        $numero_limpo = preg_replace('/\D/', '', $numero);
        $formatos = [
            $numero_limpo,
            ltrim($numero_limpo, '55'),
            substr($numero_limpo, -10),
            substr($numero_limpo, -11),
        ];
        $cliente = null;
        $log_debug = date('Y-m-d H:i:s') . " - Recebido: $numero | Limpo: $numero_limpo | Testando formatos: " . implode(',', $formatos) . "\n";
        foreach ($formatos as $f) {
            $f_esc = $mysqli->real_escape_string($f);
            $sql_busca = "SELECT * FROM clientes WHERE REPLACE(REPLACE(REPLACE(REPLACE(celular, '(', ''), ')', ''), '-', ''), ' ', '') LIKE '%$f_esc%' OR REPLACE(REPLACE(REPLACE(REPLACE(telefone, '(', ''), ')', ''), '-', ''), ' ', '') LIKE '%$f_esc%' LIMIT 1";
            $res = $mysqli->query($sql_busca);
            $log_debug .= "SQL: $sql_busca | Resultado: ";
            if ($res && $res->num_rows > 0) {
                $cliente = $res->fetch_assoc();
                $log_debug .= "ENCONTRADO (id: {$cliente['id']})\n";
                break;
            } else {
                $log_debug .= "NÃO ENCONTRADO\n";
            }
        }
        file_put_contents(__DIR__ . '/debug_webhook.log', $log_debug, FILE_APPEND);

        if ($cliente) {
            $cliente_id = $cliente['id'];
            // Log detalhado antes de salvar
            $log_insert = date('Y-m-d H:i:s') . " - Tentando salvar mensagem: canal_id=$canal_id, cliente_id=$cliente_id, mensagem='$mensagem', tipo='$tipo'\n";
            file_put_contents(__DIR__ . '/debug_webhook.log', $log_insert, FILE_APPEND);
            // Salvar mensagem no banco normalmente
            $mensagem_escaped = $mysqli->real_escape_string($mensagem);
            $data_hora = date('Y-m-d H:i:s');
            $sql = "INSERT INTO mensagens_comunicacao (canal_id, cliente_id, mensagem, tipo, data_hora, direcao, status) 
                    VALUES ($canal_id, $cliente_id, '$mensagem_escaped', '$tipo', '$data_hora', 'recebido', 'recebido')";
            $log_sql = date('Y-m-d H:i:s') . " - SQL: $sql\n";
            file_put_contents(__DIR__ . '/debug_webhook.log', $log_sql, FILE_APPEND);
            if (!$mysqli->query($sql)) {
                $log_erro = date('Y-m-d H:i:s') . " - ERRO MYSQL: " . $mysqli->error . "\n";
                file_put_contents(__DIR__ . '/debug_webhook.log', $log_erro, FILE_APPEND);
                throw new Exception('Erro ao salvar mensagem: ' . $mysqli->error);
            } else {
                $log_ok = date('Y-m-d H:i:s') . " - Mensagem salva com sucesso! ID: " . $mysqli->insert_id . "\n";
                file_put_contents(__DIR__ . '/debug_webhook.log', $log_ok, FILE_APPEND);
            }
            require_once '../cache_invalidator.php';
            invalidate_message_cache($cliente_id);
            if (function_exists('cache_forget')) {
                cache_forget("mensagens_{$cliente_id}");
                cache_forget("historico_html_{$cliente_id}");
                cache_forget("mensagens_html_{$cliente_id}");
            }
            echo json_encode([
                'success' => true,
                'message' => 'Mensagem recebida e salva',
                'cliente_id' => $cliente_id,
                'cliente_nome' => $cliente['nome'],
                'mensagem_id' => $mysqli->insert_id
            ]);
        } else {
            // Salvar em mensagens_pendentes
            $mensagem_escaped = $mysqli->real_escape_string($mensagem);
            $data_hora = date('Y-m-d H:i:s');
            $canal_id = intval($canal_id);
            $numero_esc = $mysqli->real_escape_string($numero_limpo);
            $tipo_esc = $mysqli->real_escape_string($tipo);
            $sql = "INSERT INTO mensagens_pendentes (canal_id, numero, mensagem, tipo, data_hora) VALUES ($canal_id, '$numero_esc', '$mensagem_escaped', '$tipo_esc', '$data_hora')";
            if (!$mysqli->query($sql)) {
                throw new Exception('Erro ao salvar mensagem pendente: ' . $mysqli->error);
            }
            echo json_encode([
                'success' => true,
                'message' => 'Mensagem recebida e salva em pendentes',
                'numero' => $numero,
                'mensagem_id' => $mysqli->insert_id
            ]);
        }
        exit;
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
    exit;
}

// Se não for nenhum dos dois formatos
http_response_code(400);
echo json_encode(['success' => false, 'error' => 'Dados incompletos']);
?> 