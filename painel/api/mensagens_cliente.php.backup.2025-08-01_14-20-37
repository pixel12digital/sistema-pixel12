<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../db.php';

header('Content-Type: application/json; charset=utf-8');

$cliente_id = isset($_GET['cliente_id']) ? intval($_GET['cliente_id']) : 0;

if (!$cliente_id) {
    echo json_encode(['success' => false, 'error' => 'ID do cliente não fornecido']);
    exit;
}

try {
    // Primeiro, buscar todas as mensagens do banco principal
    $sql_principal = "SELECT m.*, 
                           c.nome_exibicao as canal_nome,
                           c.porta as canal_porta,
                           c.identificador as canal_identificador,
                           CASE 
                               WHEN m.direcao = 'enviado' THEN 'Você'
                               WHEN m.direcao = 'recebido' THEN c.nome_exibicao
                               ELSE 'Sistema'
                           END as contato_interagiu
                    FROM mensagens_comunicacao m
                    LEFT JOIN canais_comunicacao c ON m.canal_id = c.id
                    WHERE m.cliente_id = ?
                    ORDER BY m.data_hora ASC";
    
    $stmt = $mysqli->prepare($sql_principal);
    $stmt->bind_param('i', $cliente_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $mensagens = [];
    while ($msg = $result->fetch_assoc()) {
        $mensagens[] = [
            'id' => $msg['id'],
            'mensagem' => $msg['mensagem'],
            'direcao' => $msg['direcao'],
            'status' => $msg['status'],
            'data_hora' => $msg['data_hora'],
            'anexo' => $msg['anexo'],
            'canal_nome' => $msg['canal_nome'] ?: 'WhatsApp',
            'canal_porta' => $msg['canal_porta'] ?: 3000,
            'canal_identificador' => $msg['canal_identificador'] ?: '',
            'contato_interagiu' => $msg['contato_interagiu'] ?: 'Sistema'
        ];
    }
    $stmt->close();
    
    // Agora buscar mensagens dos bancos separados (se existirem)
    $bancos_separados = [
        3001 => 'u342734079_wts_com_pixel', // Canal Comercial
        3002 => 'pixel12digital_suporte', 
        3003 => 'pixel12digital_vendas'
    ];
    
    foreach ($bancos_separados as $porta => $banco_nome) {
        try {
            if ($porta === 3001) {
                // Canal Comercial - usar configuração específica
                require_once __DIR__ . '/../../canais/comercial/canal_config.php';
                $mysqli_separado = conectarBancoCanal();
            } else {
                // Outros canais - usar conexão padrão
                $mysqli_separado = new mysqli('srv1607.hstgr.io', 'u342734079_revendaweb', 'Los@ngo#081081', $banco_nome);
            }
            
            if ($mysqli_separado && !$mysqli_separado->connect_error) {
                // Buscar cliente no banco principal
                $cliente_principal = $mysqli->query("SELECT celular, telefone FROM clientes WHERE id = $cliente_id")->fetch_assoc();
                
                if ($cliente_principal) {
                    $celular = $cliente_principal['celular'];
                    $telefone = $cliente_principal['telefone'];
                    
                    // Buscar cliente correspondente no banco separado
                    $cliente_separado = null;
                    if ($celular) {
                        $cliente_separado = $mysqli_separado->query("SELECT id FROM clientes WHERE celular = '$celular' OR telefone = '$celular' LIMIT 1")->fetch_assoc();
                    }
                    if (!$cliente_separado && $telefone) {
                        $cliente_separado = $mysqli_separado->query("SELECT id FROM clientes WHERE celular = '$telefone' OR telefone = '$telefone' LIMIT 1")->fetch_assoc();
                    }
                    
                    if ($cliente_separado) {
                        $cliente_separado_id = $cliente_separado['id'];
                        
                        // Definir nome do canal baseado na porta
                        $canal_nome_separado = '';
                        switch ($porta) {
                            case 3001:
                                $canal_nome_separado = 'Comercial - Pixel';
                                break;
                            case 3002:
                                $canal_nome_separado = 'Suporte - Pixel';
                                break;
                            case 3003:
                                $canal_nome_separado = 'Vendas - Pixel';
                                break;
                        }
                        
                        // Buscar mensagens do banco separado
                        $sql_separado = "SELECT m.*, 
                                               '$canal_nome_separado' as canal_nome,
                                               $porta as canal_porta,
                                               'canal_$porta' as canal_identificador,
                                               CASE 
                                                   WHEN m.direcao = 'enviado' THEN 'Você'
                                                   WHEN m.direcao = 'recebido' THEN '$canal_nome_separado'
                                                   ELSE 'Sistema'
                                               END as contato_interagiu
                                        FROM mensagens_comunicacao m
                                        WHERE m.cliente_id = $cliente_separado_id
                                        ORDER BY m.data_hora ASC";
                        
                        $result_separado = $mysqli_separado->query($sql_separado);
                        while ($msg = $result_separado->fetch_assoc()) {
                            $mensagens[] = [
                                'id' => 'S' . $porta . '_' . $msg['id'], // Prefixo para identificar origem
                                'mensagem' => $msg['mensagem'],
                                'direcao' => $msg['direcao'],
                                'status' => $msg['status'],
                                'data_hora' => $msg['data_hora'],
                                'anexo' => $msg['anexo'] ?? null,
                                'canal_nome' => $msg['canal_nome'],
                                'canal_porta' => $msg['canal_porta'],
                                'canal_identificador' => $msg['canal_identificador'],
                                'contato_interagiu' => $msg['contato_interagiu']
                            ];
                        }
                    }
                }
                $mysqli_separado->close();
            }
        } catch (Exception $e) {
            // Ignorar erros dos bancos separados - usar apenas o principal
            error_log("[MENSAGENS] Erro ao acessar banco $banco_nome: " . $e->getMessage());
        }
    }
    
    // Ordenar todas as mensagens por data/hora
    usort($mensagens, function($a, $b) {
        return strtotime($a['data_hora']) - strtotime($b['data_hora']);
    });
    
    echo json_encode([
        'success' => true,
        'mensagens' => $mensagens,
        'total' => count($mensagens)
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Erro ao carregar mensagens: ' . $e->getMessage()
    ]);
}
?> 