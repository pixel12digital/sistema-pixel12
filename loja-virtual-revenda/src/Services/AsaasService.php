<?php

class AsaasService
{
    private $asaas_api_url;
    private $asaas_api_key;
    private $mysqli;

    public function __construct($asaas_api_url, $asaas_api_key, $mysqli)
    {
        $this->asaas_api_url = $asaas_api_url;
        $this->asaas_api_key = $asaas_api_key;
        $this->mysqli = $mysqli;
    }

    private function getAsaas($endpoint)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->asaas_api_url . $endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'access_token: ' . $this->asaas_api_key
        ]);
        $result = curl_exec($ch);
        curl_close($ch);
        return json_decode($result, true);
    }

    public function sincronizarCobrancas()
    {
        echo date('Y-m-d H:i:s') . " - Iniciando sincronização com Asaas...\n";
        $offset = 0;
        $cobrancas = [];
        do {
            $resp = $this->getAsaas("/v3/payments?offset={$offset}&limit=100");
            if (!isset($resp['data'])) {
                echo "Erro ao obter dados do Asaas\n";
                exit;
            }
            foreach ($resp['data'] as $cob) {
                $cobrancas[] = $cob;
                // Verifica se já existe no banco local
                $stmt = $this->mysqli->prepare("SELECT id FROM cobrancas WHERE asaas_id = ?");
                $stmt->bind_param('s', $cob['id']);
                $stmt->execute();
                $stmt->store_result();
                if ($stmt->num_rows === 0) {
                    $stmt->close();
                    // Insere nova cobrança
                    $stmt = $this->mysqli->prepare("
                        INSERT INTO cobrancas (
                            cliente_id,
                            asaas_id,
                            valor,
                            status,
                            invoice_url,
                            installment_number,
                            subscription,
                            due_date,
                            created_at,
                            updated_at
                        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                    ");
                    $stmt->bind_param(
                        'isssssssss',
                        $cob['customer'],
                        $cob['id'],
                        $cob['value'],
                        $cob['status'],
                        $cob['invoiceUrl'],
                        $cob['installmentNumber'],
                        $cob['subscription'],
                        $cob['dueDate'],
                        $cob['dateCreated'],
                        $cob['dateUpdated']
                    );
                    $stmt->execute();
                    $stmt->close();
                } else {
                    $stmt->close();
                }
            }
            $offset += 100;
        } while (!empty($resp['data']) && count($resp['data']) === 100);
        echo "Cobranças sincronizadas: " . count($cobrancas) . "\n";
        // Registrar data/hora da última sincronização
        file_put_contents('ultima_sincronizacao.log', date('Y-m-d H:i:s'));
        echo "Sincronização concluída em " . date('Y-m-d H:i:s') . "\n";
    }
} 