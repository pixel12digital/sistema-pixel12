<?php
require_once __DIR__ . '/../../config.php';

class AsaasIntegrationService {
    private $apiKey;
    private $apiUrl;
    private $mysqli;
    
    public function __construct() {
        $this->apiKey = ASAAS_API_KEY;
        $this->apiUrl = ASAAS_API_URL;
        $this->mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        
        if ($this->mysqli->connect_error) {
            throw new Exception("Erro de conexão com banco: " . $this->mysqli->connect_error);
        }
    }
    
    /**
     * Criar cliente no Asaas e no banco local
     */
    public function criarCliente($dados) {
        try {
            // Validar dados obrigatórios
            if (empty($dados['nome']) || empty($dados['email']) || empty($dados['cpf_cnpj'])) {
                throw new Exception("Nome, email e CPF/CNPJ são obrigatórios");
            }
            
            // Verificar se cliente já existe localmente
            $stmt = $this->mysqli->prepare("SELECT id, asaas_id FROM clientes WHERE cpf_cnpj = ? LIMIT 1");
            $stmt->bind_param('s', $dados['cpf_cnpj']);
            $stmt->execute();
            $stmt->bind_result($cliente_id, $asaas_id);
            $stmt->fetch();
            $stmt->close();
            
            if ($cliente_id) {
                // Cliente já existe, retornar dados
                return [
                    'success' => true,
                    'message' => 'Cliente já existe',
                    'cliente_id' => $cliente_id,
                    'asaas_id' => $asaas_id
                ];
            }
            
            // Criar cliente no Asaas
            $asaasData = [
                'name' => $dados['nome'],
                'email' => $dados['email'],
                'phone' => $dados['telefone'] ?? '',
                'mobilePhone' => $dados['celular'] ?? '',
                'cpfCnpj' => $dados['cpf_cnpj'],
                'postalCode' => $dados['cep'] ?? '',
                'address' => $dados['rua'] ?? '',
                'addressNumber' => $dados['numero'] ?? '',
                'complement' => $dados['complemento'] ?? '',
                'province' => $dados['bairro'] ?? '',
                'city' => $dados['cidade'] ?? '',
                'state' => $dados['estado'] ?? '',
                'country' => $dados['pais'] ?? 'Brasil',
                'externalReference' => $dados['referencia_externa'] ?? '',
                'observations' => $dados['observacoes'] ?? '',
                'company' => $dados['razao_social'] ?? ''
            ];
            
            $response = $this->requestAsaas('POST', '/customers', $asaasData);
            
            if (!$response['success']) {
                throw new Exception("Erro ao criar cliente no Asaas: " . $response['message']);
            }
            
            $asaas_id = $response['data']['id'];
            
            // Salvar no banco local
            $stmt = $this->mysqli->prepare("
                INSERT INTO clientes (
                    asaas_id, nome, email, telefone, celular, cep, rua, numero, 
                    complemento, bairro, cidade, estado, pais, cpf_cnpj, 
                    referencia_externa, observacoes, razao_social, data_criacao, data_atualizacao
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
            ");
            
            $stmt->bind_param('sssssssssssssssss',
                $asaas_id,
                $dados['nome'],
                $dados['email'],
                $dados['telefone'] ?? '',
                $dados['celular'] ?? '',
                $dados['cep'] ?? '',
                $dados['rua'] ?? '',
                $dados['numero'] ?? '',
                $dados['complemento'] ?? '',
                $dados['bairro'] ?? '',
                $dados['cidade'] ?? '',
                $dados['estado'] ?? '',
                $dados['pais'] ?? 'Brasil',
                $dados['cpf_cnpj'],
                $dados['referencia_externa'] ?? '',
                $dados['observacoes'] ?? '',
                $dados['razao_social'] ?? ''
            );
            
            if (!$stmt->execute()) {
                throw new Exception("Erro ao salvar cliente no banco: " . $stmt->error);
            }
            
            $cliente_id = $this->mysqli->insert_id;
            $stmt->close();
            
            return [
                'success' => true,
                'message' => 'Cliente criado com sucesso',
                'cliente_id' => $cliente_id,
                'asaas_id' => $asaas_id
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Criar cobrança no Asaas e no banco local
     */
    public function criarCobranca($dados) {
        try {
            // Validar dados obrigatórios
            if (empty($dados['cliente_id']) || empty($dados['valor']) || empty($dados['vencimento'])) {
                throw new Exception("Cliente, valor e vencimento são obrigatórios");
            }
            
            // Buscar dados do cliente
            $stmt = $this->mysqli->prepare("SELECT asaas_id, nome FROM clientes WHERE id = ? LIMIT 1");
            $stmt->bind_param('i', $dados['cliente_id']);
            $stmt->execute();
            $stmt->bind_result($asaas_customer_id, $cliente_nome);
            $stmt->fetch();
            $stmt->close();
            
            if (!$asaas_customer_id) {
                throw new Exception("Cliente não encontrado ou sem ID do Asaas");
            }
            
            // Criar cobrança no Asaas
            $asaasData = [
                'customer' => $asaas_customer_id,
                'value' => floatval($dados['valor']),
                'dueDate' => $dados['vencimento'],
                'description' => $dados['descricao'] ?? 'Cobrança gerada pelo sistema',
                'billingType' => $dados['tipo'] ?? 'BOLETO',
                'externalReference' => $dados['referencia_externa'] ?? '',
                'notificationDisabled' => $dados['notificacao_desabilitada'] ?? false
            ];
            
            $response = $this->requestAsaas('POST', '/payments', $asaasData);
            
            if (!$response['success']) {
                throw new Exception("Erro ao criar cobrança no Asaas: " . $response['message']);
            }
            
            $asaas_payment_id = $response['data']['id'];
            
            // Salvar no banco local
            $stmt = $this->mysqli->prepare("
                INSERT INTO cobrancas (
                    asaas_payment_id, cliente_id, valor, status, vencimento, 
                    descricao, tipo, url_fatura, data_criacao, data_atualizacao
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
            ");
            
            $status = $response['data']['status'] ?? 'PENDING';
            $url_fatura = $response['data']['invoiceUrl'] ?? '';
            
            $stmt->bind_param('sidsdsss',
                $asaas_payment_id,
                $dados['cliente_id'],
                $dados['valor'],
                $status,
                $dados['vencimento'],
                $dados['descricao'] ?? 'Cobrança gerada pelo sistema',
                $dados['tipo'] ?? 'BOLETO',
                $url_fatura
            );
            
            if (!$stmt->execute()) {
                throw new Exception("Erro ao salvar cobrança no banco: " . $stmt->error);
            }
            
            $cobranca_id = $this->mysqli->insert_id;
            $stmt->close();
            
            return [
                'success' => true,
                'message' => 'Cobrança criada com sucesso',
                'cobranca_id' => $cobranca_id,
                'asaas_payment_id' => $asaas_payment_id,
                'url_fatura' => $url_fatura
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Criar assinatura no Asaas e no banco local
     */
    public function criarAssinatura($dados) {
        try {
            // Validar dados obrigatórios
            if (empty($dados['cliente_id']) || empty($dados['valor']) || empty($dados['periodicidade'])) {
                throw new Exception("Cliente, valor e periodicidade são obrigatórios");
            }
            
            // Buscar dados do cliente
            $stmt = $this->mysqli->prepare("SELECT asaas_id FROM clientes WHERE id = ? LIMIT 1");
            $stmt->bind_param('i', $dados['cliente_id']);
            $stmt->execute();
            $stmt->bind_result($asaas_customer_id);
            $stmt->fetch();
            $stmt->close();
            
            if (!$asaas_customer_id) {
                throw new Exception("Cliente não encontrado ou sem ID do Asaas");
            }
            
            // Criar assinatura no Asaas
            $asaasData = [
                'customer' => $asaas_customer_id,
                'value' => floatval($dados['valor']),
                'cycle' => $dados['periodicidade'], // WEEKLY, BIWEEKLY, MONTHLY, QUARTERLY, SEMIANNUALLY, YEARLY
                'description' => $dados['descricao'] ?? 'Assinatura gerada pelo sistema',
                'billingType' => $dados['tipo'] ?? 'BOLETO',
                'endDate' => $dados['data_fim'] ?? null,
                'maxPayments' => $dados['max_parcelas'] ?? null
            ];
            
            $response = $this->requestAsaas('POST', '/subscriptions', $asaasData);
            
            if (!$response['success']) {
                throw new Exception("Erro ao criar assinatura no Asaas: " . $response['message']);
            }
            
            $asaas_subscription_id = $response['data']['id'];
            
            // Salvar no banco local
            $stmt = $this->mysqli->prepare("
                INSERT INTO assinaturas (
                    cliente_id, asaas_id, status, periodicidade, start_date, 
                    next_due_date, created_at, updated_at
                ) VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())
            ");
            
            $status = $response['data']['status'] ?? 'ACTIVE';
            $start_date = $response['data']['startDate'] ?? date('Y-m-d');
            $next_due_date = $response['data']['nextDueDate'] ?? null;
            
            $stmt->bind_param('isssss',
                $dados['cliente_id'],
                $asaas_subscription_id,
                $status,
                $dados['periodicidade'],
                $start_date,
                $next_due_date
            );
            
            if (!$stmt->execute()) {
                throw new Exception("Erro ao salvar assinatura no banco: " . $stmt->error);
            }
            
            $assinatura_id = $this->mysqli->insert_id;
            $stmt->close();
            
            return [
                'success' => true,
                'message' => 'Assinatura criada com sucesso',
                'assinatura_id' => $assinatura_id,
                'asaas_subscription_id' => $asaas_subscription_id
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Fazer requisição para a API do Asaas
     */
    private function requestAsaas($method, $endpoint, $data = null) {
        $ch = curl_init();
        $url = rtrim($this->apiUrl, '/') . '/' . ltrim($endpoint, '/');
        
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'access_token: ' . $this->apiKey
        ]);
        
        if ($data) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
        
        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($result === false) {
            return [
                'success' => false,
                'message' => 'Erro de conexão com a API do Asaas'
            ];
        }
        
        $response = json_decode($result, true);
        
        if ($httpCode >= 200 && $httpCode < 300) {
            return [
                'success' => true,
                'data' => $response
            ];
        } else {
            $error = isset($response['errors']) ? json_encode($response['errors']) : 'Erro desconhecido';
            return [
                'success' => false,
                'message' => "Erro HTTP $httpCode: $error"
            ];
        }
    }
    
    /**
     * Destrutor para fechar conexão
     */
    public function __destruct() {
        if ($this->mysqli) {
            $this->mysqli->close();
        }
    }
}
?> 