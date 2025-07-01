<?php
namespace Services;

class AsaasService
{
    /**
     * @var string
     */
    private $apiKey;

    /**
     * @var string
     */
    private $apiUrl;

    /**
     * Construtor: carrega as variáveis do .env
     */
    public function __construct()
    {
        if (file_exists(__DIR__ . '/../../../.env')) {
            $env = parse_ini_file(__DIR__ . '/../../../.env');
        } elseif (file_exists(__DIR__ . '/../../.env')) {
            $env = parse_ini_file(__DIR__ . '/../../.env');
        } else {
            throw new \Exception('Arquivo .env não encontrado');
        }
        $this->apiKey = $env['ASAAS_API_KEY'] ?? '';
        $this->apiUrl = $env['ASAAS_API_URL'] ?? '';
        if (!$this->apiKey || !$this->apiUrl) {
            throw new \Exception('ASAAS_API_KEY ou ASAAS_API_URL não definidos no .env');
        }
    }

    /**
     * Cria um cliente no Asaas.
     *
     * @param array $data Campos esperados: name, cpfCnpj, email, mobilePhone, postalCode, address, addressNumber, complement, province, city, notificationDisabled
     * @return array Resposta decodificada da API Asaas
     * @throws \Exception Em caso de erro na requisição ou resposta com erros
     */
    public function createCustomer(array $data): array
    {
        $url = $this->apiUrl . '/customers';
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'access_token: ' . $this->apiKey,
        ]);
        $result = curl_exec($ch);
        if ($result === false) {
            throw new \Exception('cURL error: ' . curl_error($ch));
        }
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        $response = json_decode($result, true);
        if ($httpCode >= 400 || isset($response['errors'])) {
            $err = $response['errors'] ?? $response;
            throw new \Exception('Asaas API error: ' . json_encode($err));
        }
        return $response;
    }

    /**
     * Cria um pagamento (avulso ou parcelado) no Asaas.
     *
     * @param array $data Campos esperados: customer, value, dueDate, description, billingType,
     *                    (opcionais para parcelamento: installmentCount, totalValue)
     * @return array Resposta decodificada da API Asaas
     * @throws \Exception Em caso de erro na requisição ou resposta com erros
     */
    public function createPayment(array $data): array
    {
        $url = $this->apiUrl . '/payments';
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'access_token: ' . $this->apiKey,
        ]);
        $result = curl_exec($ch);
        if ($result === false) {
            throw new \Exception('cURL error: ' . curl_error($ch));
        }
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        $response = json_decode($result, true);
        if ($httpCode >= 400 || isset($response['errors'])) {
            $err = $response['errors'] ?? $response;
            throw new \Exception('Asaas API error: ' . json_encode($err));
        }
        return $response;
    }

    /**
     * Cria uma assinatura recorrente no Asaas.
     *
     * @param array $data Campos esperados: customer, value, nextDueDate, description, billingType, cycle
     * @return array Resposta decodificada da API Asaas
     * @throws \Exception Em caso de erro na requisição ou resposta com erros
     */
    public function createSubscription(array $data): array
    {
        $url = $this->apiUrl . '/subscriptions';
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'access_token: ' . $this->apiKey,
        ]);
        $result = curl_exec($ch);
        if ($result === false) {
            throw new \Exception('cURL error: ' . curl_error($ch));
        }
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        $response = json_decode($result, true);
        if ($httpCode >= 400 || isset($response['errors'])) {
            $err = $response['errors'] ?? $response;
            throw new \Exception('Asaas API error: ' . json_encode($err));
        }
        return $response;
    }

    /**
     * Busca detalhes de um pagamento pelo ID no Asaas.
     *
     * @param string $paymentId ID do pagamento a ser consultado
     * @return array Resposta decodificada da API Asaas
     * @throws \Exception Em caso de erro na requisição ou resposta com erros
     */
    public function getPayment(string $paymentId): array
    {
        $url = $this->apiUrl . '/payments/' . urlencode($paymentId);
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'access_token: ' . $this->apiKey,
        ]);
        $result = curl_exec($ch);
        if ($result === false) {
            throw new \Exception('cURL error: ' . curl_error($ch));
        }
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        $response = json_decode($result, true);
        if ($httpCode >= 400 || isset($response['errors'])) {
            $err = $response['errors'] ?? $response;
            throw new \Exception('Asaas API error: ' . json_encode($err));
        }
        return $response;
    }

    /**
     * Processa o payload de um webhook do Asaas e extrai os dados essenciais.
     *
     * @param array $payload O array decodificado recebido do Asaas (JSON do webhook).
     * @return array Dados extraídos: [
     *     'id' => string,            // ID do pagamento/assinatura
     *     'status' => string,        // status atual
     *     'dueDate' => string|null,  // data de vencimento (se disponível)
     *     'subscriptionId' => string|null // ID de assinatura (se aplicável)
     * ]
     * @throws \Exception Se o payload não contiver 'id' ou 'status'.
     */
    public function handleWebhook(array $payload): array
    {
        if (empty($payload['id']) || empty($payload['status'])) {
            throw new \Exception('Webhook payload inválido: campos "id" e "status" são obrigatórios.');
        }
        return [
            'id'             => $payload['id'],
            'status'         => $payload['status'],
            'dueDate'        => $payload['dueDate'] ?? null,
            'subscriptionId' => $payload['subscription'] ?? null,
        ];
    }

    // Métodos públicos serão implementados aqui
} 