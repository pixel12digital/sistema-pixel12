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
        // Inclua o config.php se ainda não estiver incluído
        if (!defined('ASAAS_API_KEY') || !defined('ASAAS_API_URL')) {
            if (file_exists(__DIR__ . '/../../config.php')) {
                require_once __DIR__ . '/../../config.php';
            } elseif (file_exists(__DIR__ . '/../../../config.php')) {
                require_once __DIR__ . '/../../../config.php';
            } else {
                throw new \Exception('Arquivo config.php não encontrado');
            }
        }
        $this->apiKey = ASAAS_API_KEY;
        $this->apiUrl = ASAAS_API_URL;
        if (!$this->apiKey || !$this->apiUrl) {
            throw new \Exception('ASAAS_API_KEY ou ASAAS_API_URL não definidos no config.php');
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
        $data['notificationDisabled'] = true; // Sempre desativa notificação
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

    /**
     * Busca detalhes de um cliente pelo ID no Asaas.
     *
     * @param string $customerId ID do cliente no Asaas
     * @return array Resposta decodificada da API Asaas
     * @throws \Exception Em caso de erro na requisição ou resposta com erros
     */
    public function getCustomer(string $customerId): array
    {
        $url = $this->apiUrl . '/customers/' . urlencode($customerId);
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
     * Atualiza um cliente existente no Asaas.
     *
     * @param string $customerId ID do cliente no Asaas
     * @param array $data Campos permitidos: name, cpfCnpj, email, phone, mobilePhone, postalCode, address, addressNumber, complement, province, city, state, country, externalReference, observations, company
     * @return array Resposta decodificada da API Asaas
     * @throws \Exception Em caso de erro na requisição ou resposta com erros
     */
    public function updateCustomer(string $customerId, array $data): array
    {
        // Filtrar apenas campos permitidos pelo Asaas
        $allowedFields = [
            'name', 'cpfCnpj', 'email', 'phone', 'mobilePhone', 
            'postalCode', 'address', 'addressNumber', 'complement', 
            'province', 'city', 'state', 'country', 
            'externalReference', 'observations', 'company'
        ];
        
        $filteredData = array_intersect_key($data, array_flip($allowedFields));
        
        // Remover campos vazios para não sobrescrever dados existentes
        $filteredData = array_filter($filteredData, function($value) {
            return $value !== '' && $value !== null;
        });
        
        $url = $this->apiUrl . '/customers/' . urlencode($customerId);
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($filteredData));
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
     * Busca todas as cobranças (payments) do cliente no Asaas.
     * @param string $customerId ID do cliente no Asaas
     * @return array Lista de cobranças
     */
    public function getCustomerPayments(string $customerId): array
    {
        error_log('[Asaas] getCustomerPayments - Token início: ' . substr(trim($this->apiKey), 0, 8) . '...');
        error_log('[Asaas] getCustomerPayments - ID do cliente: ' . var_export($customerId, true));
        $url = $this->apiUrl . '/payments?customer=' . urlencode($customerId) . '&limit=100';
        error_log('[Asaas] getCustomerPayments - URL: ' . $url);
        $header = [
            'Content-Type: application/json',
            'access_token: ' . trim($this->apiKey)
        ];
        error_log('[Asaas] Header enviado: ' . var_export($header, true));
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        error_log('[Asaas] getCustomerPayments - HTTP Code: ' . $httpCode);
        error_log('[Asaas] getCustomerPayments - cURL error: ' . $error);
        error_log('[Asaas] getCustomerPayments - Resposta bruta: ' . var_export($result, true));
        if ($result === false || $result === "") {
            throw new \Exception('cURL error: ' . $error);
        }
        $response = json_decode($result, true);
        if ($httpCode >= 400 || isset($response['errors'])) {
            $err = $response['errors'] ?? $response;
            error_log('[Asaas] getCustomerPayments - Erro HTTP ou errors: ' . json_encode($err));
            throw new \Exception('Asaas API error: ' . json_encode($err) . ' | Resposta bruta: ' . $result);
        }
        if (!is_array($response) || !isset($response['data'])) {
            error_log('[Asaas] getCustomerPayments - Resposta inesperada: ' . var_export($result, true));
            throw new \Exception('Asaas API error: resposta inesperada: ' . $result);
        }
        return $response['data'];
    }

    /**
     * Busca todas as assinaturas (subscriptions) do cliente no Asaas.
     * @param string $customerId ID do cliente no Asaas
     * @return array Lista de assinaturas
     */
    public function getCustomerSubscriptions(string $customerId): array
    {
        error_log('[Asaas] getCustomerSubscriptions - Token início: ' . substr($this->apiKey, 0, 8) . '...');
        error_log('[Asaas] getCustomerSubscriptions - ID do cliente: ' . var_export($customerId, true));
        $url = $this->apiUrl . '/subscriptions?customer=' . urlencode($customerId) . '&limit=100';
        error_log('[Asaas] getCustomerSubscriptions - URL: ' . $url);
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'access_token: ' . $this->apiKey,
        ]);
        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        error_log('[Asaas] getCustomerSubscriptions - HTTP Code: ' . $httpCode);
        error_log('[Asaas] getCustomerSubscriptions - cURL error: ' . $error);
        error_log('[Asaas] getCustomerSubscriptions - Resposta bruta: ' . var_export($result, true));
        if ($result === false || $result === "") {
            throw new \Exception('cURL error: ' . $error);
        }
        $response = json_decode($result, true);
        if ($httpCode >= 400 || isset($response['errors'])) {
            $err = $response['errors'] ?? $response;
            error_log('[Asaas] getCustomerSubscriptions - Erro HTTP ou errors: ' . json_encode($err));
            throw new \Exception('Asaas API error: ' . json_encode($err) . ' | Resposta bruta: ' . $result);
        }
        if (!is_array($response) || !isset($response['data'])) {
            error_log('[Asaas] getCustomerSubscriptions - Resposta inesperada: ' . var_export($result, true));
            throw new \Exception('Asaas API error: resposta inesperada: ' . $result);
        }
        return $response['data'];
    }

    // Métodos públicos serão implementados aqui

    public function sincronizarFaturas(): int
    {
        $offset = 0;
        $total = 0;
        do {
            $resp = $this->getAsaas("/v3/payments?offset={$offset}&limit=100");
            foreach ($resp['data'] as $item) {
                \App\Models\Fatura::updateOrCreate(
                    ['asaas_id' => $item['id']],
                    [
                        'cliente_id'   => $item['customer'],
                        'valor'        => $item['value'],
                        'status'       => $item['status'],
                        'invoice_url'  => $item['invoiceUrl'],
                        'due_date'     => $item['dueDate'],
                        'created_at'   => $item['dateCreated'],
                        'updated_at'   => $item['dateUpdated'],
                    ]
                );
                $total++;
            }
            $offset += 100;
        } while (!empty($resp['data']));
        return $total;
    }

    public function sincronizarAssinaturas(): int
    {
        $offset = 0;
        $total = 0;
        do {
            $resp = $this->getAsaas("/v3/subscriptions?offset={$offset}&limit=100");
            foreach ($resp['data'] as $item) {
                \App\Models\Assinatura::updateOrCreate(
                    ['asaas_id' => $item['id']],
                    [
                        'cliente_id'    => $item['customer'],
                        'status'        => $item['status'],
                        'periodicidade' => $item['billingType'],
                        'start_date'    => $item['dateCreated'],
                        'next_due_date' => $item['nextDueDate'],
                        'created_at'    => $item['dateCreated'],
                        'updated_at'    => $item['dateUpdated'],
                    ]
                );
                $total++;
            }
            $offset += 100;
        } while (!empty($resp['data']));
        return $total;
    }
} 