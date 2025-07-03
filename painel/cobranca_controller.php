<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../src/Services/AsaasIntegrationService.php';

class CobrancaController {
    private $asaasService;
    private $mysqli;
    
    public function __construct() {
        $this->asaasService = new AsaasIntegrationService();
        $this->mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        
        if ($this->mysqli->connect_error) {
            throw new Exception("Erro de conexão com banco: " . $this->mysqli->connect_error);
        }
    }
    
    /**
     * Listar todas as cobranças
     */
    public function listarCobrancas($filtro = '', $status = '', $pagina = 1, $limite = 20) {
        $offset = ($pagina - 1) * $limite;
        $where = [];
        $params = [];
        $types = '';
        
        if (!empty($filtro)) {
            $where[] = "(c.descricao LIKE ? OR cl.nome LIKE ? OR c.asaas_payment_id LIKE ?)";
            $filtro = "%$filtro%";
            $params = array_merge($params, [$filtro, $filtro, $filtro]);
            $types .= 'sss';
        }
        
        if (!empty($status)) {
            $where[] = "c.status = ?";
            $params[] = $status;
            $types .= 's';
        }
        
        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
        
        $sql = "
            SELECT c.*, cl.nome as cliente_nome, cl.email as cliente_email 
            FROM cobrancas c 
            LEFT JOIN clientes cl ON c.cliente_id = cl.id 
            $whereClause 
            ORDER BY c.data_criacao DESC 
            LIMIT ? OFFSET ?
        ";
        
        $params[] = $limite;
        $params[] = $offset;
        $types .= 'ii';
        
        $stmt = $this->mysqli->prepare($sql);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        
        $cobrancas = [];
        while ($row = $result->fetch_assoc()) {
            $cobrancas[] = $row;
        }
        $stmt->close();
        
        // Contar total para paginação
        $sqlCount = "
            SELECT COUNT(*) as total 
            FROM cobrancas c 
            LEFT JOIN clientes cl ON c.cliente_id = cl.id 
            $whereClause
        ";
        $stmtCount = $this->mysqli->prepare($sqlCount);
        if (!empty($params) && count($params) > 2) {
            // Remove os parâmetros de LIMIT e OFFSET
            $countParams = array_slice($params, 0, -2);
            $countTypes = substr($types, 0, -2);
            $stmtCount->bind_param($countTypes, ...$countParams);
        }
        $stmtCount->execute();
        $total = $stmtCount->get_result()->fetch_assoc()['total'];
        $stmtCount->close();
        
        return [
            'cobrancas' => $cobrancas,
            'total' => $total,
            'paginas' => ceil($total / $limite),
            'pagina_atual' => $pagina
        ];
    }
    
    /**
     * Buscar cobrança por ID
     */
    public function buscarCobranca($id) {
        $stmt = $this->mysqli->prepare("
            SELECT c.*, cl.nome as cliente_nome, cl.email as cliente_email 
            FROM cobrancas c 
            LEFT JOIN clientes cl ON c.cliente_id = cl.id 
            WHERE c.id = ? 
            LIMIT 1
        ");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $cobranca = $result->fetch_assoc();
        $stmt->close();
        
        return $cobranca;
    }
    
    /**
     * Buscar cobrança por ID do Asaas
     */
    public function buscarPorAsaasId($asaas_id) {
        $stmt = $this->mysqli->prepare("
            SELECT c.*, cl.nome as cliente_nome, cl.email as cliente_email 
            FROM cobrancas c 
            LEFT JOIN clientes cl ON c.cliente_id = cl.id 
            WHERE c.asaas_payment_id = ? 
            LIMIT 1
        ");
        $stmt->bind_param('s', $asaas_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $cobranca = $result->fetch_assoc();
        $stmt->close();
        
        return $cobranca;
    }
    
    /**
     * Criar nova cobrança
     */
    public function criarCobranca($dados) {
        // Validar dados obrigatórios
        if (empty($dados['cliente_id']) || empty($dados['valor']) || empty($dados['vencimento'])) {
            return [
                'success' => false,
                'message' => 'Cliente, valor e vencimento são obrigatórios'
            ];
        }
        
        // Validar valor
        if (!is_numeric($dados['valor']) || $dados['valor'] <= 0) {
            return [
                'success' => false,
                'message' => 'Valor deve ser um número positivo'
            ];
        }
        
        // Validar data de vencimento
        $vencimento = date('Y-m-d', strtotime($dados['vencimento']));
        if ($vencimento < date('Y-m-d')) {
            return [
                'success' => false,
                'message' => 'Data de vencimento não pode ser anterior a hoje'
            ];
        }
        
        // Verificar se cliente existe
        $stmt = $this->mysqli->prepare("SELECT id, nome FROM clientes WHERE id = ? LIMIT 1");
        $stmt->bind_param('i', $dados['cliente_id']);
        $stmt->execute();
        $cliente = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        
        if (!$cliente) {
            return [
                'success' => false,
                'message' => 'Cliente não encontrado'
            ];
        }
        
        // Criar cobrança usando o serviço de integração
        return $this->asaasService->criarCobranca($dados);
    }
    
    /**
     * Atualizar status da cobrança
     */
    public function atualizarStatus($id, $status) {
        try {
            // Verificar se cobrança existe
            $cobranca = $this->buscarCobranca($id);
            if (!$cobranca) {
                return [
                    'success' => false,
                    'message' => 'Cobrança não encontrada'
                ];
            }
            
            // Validar status
            $statusValidos = ['PENDING', 'RECEIVED', 'CONFIRMED', 'OVERDUE', 'REFUNDED', 'CANCELLED'];
            if (!in_array($status, $statusValidos)) {
                return [
                    'success' => false,
                    'message' => 'Status inválido'
                ];
            }
            
            // Atualizar no banco local
            $stmt = $this->mysqli->prepare("
                UPDATE cobrancas SET 
                    status = ?, 
                    data_atualizacao = NOW()
                WHERE id = ?
            ");
            
            $stmt->bind_param('si', $status, $id);
            
            if (!$stmt->execute()) {
                throw new Exception("Erro ao atualizar status: " . $stmt->error);
            }
            
            $stmt->close();
            
            // Se for pagamento confirmado, atualizar data de pagamento
            if ($status === 'RECEIVED' || $status === 'CONFIRMED') {
                $stmt = $this->mysqli->prepare("
                    UPDATE cobrancas SET 
                        data_pagamento = CURDATE()
                    WHERE id = ?
                ");
                $stmt->bind_param('i', $id);
                $stmt->execute();
                $stmt->close();
            }
            
            return [
                'success' => true,
                'message' => 'Status atualizado com sucesso'
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Cancelar cobrança
     */
    public function cancelarCobranca($id) {
        try {
            // Verificar se cobrança existe
            $cobranca = $this->buscarCobranca($id);
            if (!$cobranca) {
                return [
                    'success' => false,
                    'message' => 'Cobrança não encontrada'
                ];
            }
            
            // Verificar se pode ser cancelada
            if ($cobranca['status'] === 'RECEIVED' || $cobranca['status'] === 'CONFIRMED') {
                return [
                    'success' => false,
                    'message' => 'Cobrança já foi paga e não pode ser cancelada'
                ];
            }
            
            if ($cobranca['status'] === 'CANCELLED') {
                return [
                    'success' => false,
                    'message' => 'Cobrança já foi cancelada'
                ];
            }
            
            // Cancelar no Asaas
            $response = $this->requestAsaas('POST', "/payments/{$cobranca['asaas_payment_id']}/cancel");
            
            if (!$response['success']) {
                return [
                    'success' => false,
                    'message' => 'Erro ao cancelar no Asaas: ' . $response['message']
                ];
            }
            
            // Atualizar status local
            return $this->atualizarStatus($id, 'CANCELLED');
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Reenviar link de pagamento
     */
    public function reenviarLink($id) {
        try {
            // Verificar se cobrança existe
            $cobranca = $this->buscarCobranca($id);
            if (!$cobranca) {
                return [
                    'success' => false,
                    'message' => 'Cobrança não encontrada'
                ];
            }
            
            // Verificar se pode reenviar
            if ($cobranca['status'] !== 'PENDING' && $cobranca['status'] !== 'OVERDUE') {
                return [
                    'success' => false,
                    'message' => 'Só é possível reenviar cobranças pendentes ou vencidas'
                ];
            }
            
            // Reenviar no Asaas
            $response = $this->requestAsaas('POST', "/payments/{$cobranca['asaas_payment_id']}/sendEmail");
            
            if (!$response['success']) {
                return [
                    'success' => false,
                    'message' => 'Erro ao reenviar: ' . $response['message']
                ];
            }
            
            return [
                'success' => true,
                'message' => 'Link reenviado com sucesso'
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Obter estatísticas das cobranças
     */
    public function getEstatisticas() {
        $stats = [];
        
        // Total de cobranças
        $result = $this->mysqli->query("SELECT COUNT(*) as total FROM cobrancas");
        $stats['total'] = $result->fetch_assoc()['total'];
        
        // Por status
        $result = $this->mysqli->query("
            SELECT status, COUNT(*) as quantidade 
            FROM cobrancas 
            GROUP BY status
        ");
        $stats['por_status'] = [];
        while ($row = $result->fetch_assoc()) {
            $stats['por_status'][$row['status']] = $row['quantidade'];
        }
        
        // Valor total recebido
        $result = $this->mysqli->query("
            SELECT SUM(valor) as total_recebido 
            FROM cobrancas 
            WHERE status IN ('RECEIVED', 'CONFIRMED')
        ");
        $stats['total_recebido'] = $result->fetch_assoc()['total_recebido'] ?? 0;
        
        // Valor total pendente
        $result = $this->mysqli->query("
            SELECT SUM(valor) as total_pendente 
            FROM cobrancas 
            WHERE status IN ('PENDING', 'OVERDUE')
        ");
        $stats['total_pendente'] = $result->fetch_assoc()['total_pendente'] ?? 0;
        
        return $stats;
    }
    
    /**
     * Fazer requisição para a API do Asaas
     */
    private function requestAsaas($method, $endpoint, $data = null) {
        $ch = curl_init();
        $url = rtrim(ASAAS_API_URL, '/') . '/' . ltrim($endpoint, '/');
        
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'access_token: ' . ASAAS_API_KEY
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

// Se for chamado diretamente via AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    try {
        $controller = new CobrancaController();
        $action = $_POST['action'];
        
        switch ($action) {
            case 'criar':
                $resultado = $controller->criarCobranca($_POST);
                break;
                
            case 'atualizar_status':
                $id = $_POST['id'] ?? 0;
                $status = $_POST['status'] ?? '';
                $resultado = $controller->atualizarStatus($id, $status);
                break;
                
            case 'cancelar':
                $id = $_POST['id'] ?? 0;
                $resultado = $controller->cancelarCobranca($id);
                break;
                
            case 'reenviar':
                $id = $_POST['id'] ?? 0;
                $resultado = $controller->reenviarLink($id);
                break;
                
            case 'buscar':
                $id = $_POST['id'] ?? 0;
                $cobranca = $controller->buscarCobranca($id);
                $resultado = [
                    'success' => $cobranca ? true : false,
                    'data' => $cobranca
                ];
                break;
                
            case 'listar':
                $filtro = $_POST['filtro'] ?? '';
                $status = $_POST['status'] ?? '';
                $pagina = $_POST['pagina'] ?? 1;
                $resultado = $controller->listarCobrancas($filtro, $status, $pagina);
                break;
                
            case 'estatisticas':
                $resultado = $controller->getEstatisticas();
                break;
                
            default:
                $resultado = [
                    'success' => false,
                    'message' => 'Ação não reconhecida'
                ];
        }
        
        echo json_encode($resultado);
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Erro: ' . $e->getMessage()
        ]);
    }
    
    exit;
}
?> 