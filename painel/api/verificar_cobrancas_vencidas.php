<?php
header('Content-Type: application/json');
require_once '../config.php';
require_once '../db.php';

try {
    // Buscar clientes monitorados com cobranças vencidas
    $sql = "SELECT DISTINCT 
                c.id as cliente_id,
                c.nome as cliente_nome,
                c.celular as cliente_celular,
                GROUP_CONCAT(
                    CONCAT(
                        cob.id, '|',
                        cob.valor, '|',
                        DATE_FORMAT(cob.vencimento, '%d/%m/%Y'), '|',
                        cob.url_fatura
                    ) SEPARATOR '||'
                ) as faturas_info
            FROM clientes c
            JOIN clientes_monitoramento cm ON c.id = cm.cliente_id
            JOIN cobrancas cob ON c.id = cob.cliente_id
            WHERE cm.monitorado = 1
            AND cob.status IN ('PENDING', 'OVERDUE')
            AND cob.vencimento < CURDATE()
            AND c.celular IS NOT NULL
            AND c.celular != ''
            GROUP BY c.id, c.nome, c.celular
            ORDER BY cob.vencimento ASC";
    
    $result = $mysqli->query($sql);
    
    if (!$result) {
        throw new Exception("Erro ao buscar cobranças vencidas: " . $mysqli->error);
    }

    $cobrancas = [];
    while ($row = $result->fetch_assoc()) {
        // Processar faturas do cliente
        $faturas = [];
        $faturas_info = explode('||', $row['faturas_info']);
        
        foreach ($faturas_info as $fatura_info) {
            $dados = explode('|', $fatura_info);
            if (count($dados) >= 4) {
                $faturas[] = [
                    'id' => $dados[0],
                    'valor' => $dados[1],
                    'vencimento_formatado' => $dados[2],
                    'url_fatura' => $dados[3]
                ];
            }
        }

        // Calcular valor total vencido
        $valor_total = array_sum(array_column($faturas, 'valor'));
        
        // Usar primeira fatura como link principal
        $link_pagamento = $faturas[0]['url_fatura'] ?? '';

        $cobrancas[] = [
            'cliente_id' => $row['cliente_id'],
            'cliente_nome' => $row['cliente_nome'],
            'cliente_celular' => $row['cliente_celular'],
            'faturas' => $faturas,
            'valor_total' => $valor_total,
            'link_pagamento' => $link_pagamento,
            'quantidade_faturas' => count($faturas)
        ];
    }

    // Log da verificação
    $log_data = date('Y-m-d H:i:s') . " - Verificação de cobranças vencidas: " . count($cobrancas) . " clientes encontrados\n";
    file_put_contents('../logs/monitoramento_clientes.log', $log_data, FILE_APPEND);

    echo json_encode([
        'success' => true,
        'cobrancas' => $cobrancas,
        'total_clientes' => count($cobrancas),
        'data_verificacao' => date('Y-m-d H:i:s')
    ]);

} catch (Exception $e) {
    error_log("Erro ao verificar cobranças vencidas: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'cobrancas' => []
    ]);
}
?> 