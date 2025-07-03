<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../src/Services/AsaasIntegrationService.php';

class ClienteController {
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
     * Listar todos os clientes
     */
    public function listarClientes($filtro = '', $pagina = 1, $limite = 20) {
        $offset = ($pagina - 1) * $limite;
        $where = '';
        $params = [];
        $types = '';
        
        if (!empty($filtro)) {
            $where = "WHERE nome LIKE ? OR email LIKE ? OR cpf_cnpj LIKE ?";
            $filtro = "%$filtro%";
            $params = [$filtro, $filtro, $filtro];
            $types = 'sss';
        }
        
        $sql = "SELECT * FROM clientes $where ORDER BY data_criacao DESC LIMIT ? OFFSET ?";
        $params[] = $limite;
        $params[] = $offset;
        $types .= 'ii';
        
        $stmt = $this->mysqli->prepare($sql);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        
        $clientes = [];
        while ($row = $result->fetch_assoc()) {
            $clientes[] = $row;
        }
        $stmt->close();
        
        // Contar total para paginação
        $sqlCount = "SELECT COUNT(*) as total FROM clientes $where";
        $stmtCount = $this->mysqli->prepare($sqlCount);
        if (!empty($filtro)) {
            $stmtCount->bind_param('sss', $filtro, $filtro, $filtro);
        }
        $stmtCount->execute();
        $total = $stmtCount->get_result()->fetch_assoc()['total'];
        $stmtCount->close();
        
        return [
            'clientes' => $clientes,
            'total' => $total,
            'paginas' => ceil($total / $limite),
            'pagina_atual' => $pagina
        ];
    }
    
    /**
     * Buscar cliente por ID
     */
    public function buscarCliente($id) {
        $stmt = $this->mysqli->prepare("SELECT * FROM clientes WHERE id = ? LIMIT 1");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $cliente = $result->fetch_assoc();
        $stmt->close();
        
        return $cliente;
    }
    
    /**
     * Buscar cliente por CPF/CNPJ
     */
    public function buscarPorCpfCnpj($cpf_cnpj) {
        $stmt = $this->mysqli->prepare("SELECT * FROM clientes WHERE cpf_cnpj = ? LIMIT 1");
        $stmt->bind_param('s', $cpf_cnpj);
        $stmt->execute();
        $result = $stmt->get_result();
        $cliente = $result->fetch_assoc();
        $stmt->close();
        
        return $cliente;
    }
    
    /**
     * Criar novo cliente
     */
    public function criarCliente($dados) {
        // Validar dados obrigatórios
        if (empty($dados['nome']) || empty($dados['email']) || empty($dados['cpf_cnpj'])) {
            return [
                'success' => false,
                'message' => 'Nome, email e CPF/CNPJ são obrigatórios'
            ];
        }
        
        // Validar formato do email
        if (!filter_var($dados['email'], FILTER_VALIDATE_EMAIL)) {
            return [
                'success' => false,
                'message' => 'Email inválido'
            ];
        }
        
        // Validar CPF/CNPJ
        if (!$this->validarCpfCnpj($dados['cpf_cnpj'])) {
            return [
                'success' => false,
                'message' => 'CPF/CNPJ inválido'
            ];
        }
        
        // Verificar se já existe cliente com este CPF/CNPJ
        $clienteExistente = $this->buscarPorCpfCnpj($dados['cpf_cnpj']);
        if ($clienteExistente) {
            return [
                'success' => false,
                'message' => 'Já existe um cliente cadastrado com este CPF/CNPJ'
            ];
        }
        
        // Criar cliente usando o serviço de integração
        return $this->asaasService->criarCliente($dados);
    }
    
    /**
     * Atualizar cliente
     */
    public function atualizarCliente($id, $dados) {
        try {
            // Verificar se cliente existe
            $cliente = $this->buscarCliente($id);
            if (!$cliente) {
                return [
                    'success' => false,
                    'message' => 'Cliente não encontrado'
                ];
            }
            
            // Validar dados obrigatórios
            if (empty($dados['nome']) || empty($dados['email'])) {
                return [
                    'success' => false,
                    'message' => 'Nome e email são obrigatórios'
                ];
            }
            
            // Validar formato do email
            if (!filter_var($dados['email'], FILTER_VALIDATE_EMAIL)) {
                return [
                    'success' => false,
                    'message' => 'Email inválido'
                ];
            }
            
            // Atualizar no banco local
            $stmt = $this->mysqli->prepare("
                UPDATE clientes SET 
                    nome = ?, email = ?, telefone = ?, celular = ?, cep = ?, 
                    rua = ?, numero = ?, complemento = ?, bairro = ?, cidade = ?, 
                    estado = ?, pais = ?, referencia_externa = ?, observacoes = ?, 
                    razao_social = ?, data_atualizacao = NOW()
                WHERE id = ?
            ");
            
            $stmt->bind_param('sssssssssssssssi',
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
                $dados['referencia_externa'] ?? '',
                $dados['observacoes'] ?? '',
                $dados['razao_social'] ?? '',
                $id
            );
            
            if (!$stmt->execute()) {
                throw new Exception("Erro ao atualizar cliente: " . $stmt->error);
            }
            
            $stmt->close();
            
            // Atualizar também no Asaas se necessário
            if (!empty($cliente['asaas_id'])) {
                require_once __DIR__ . '/../src/Services/AsaasService.php';
                $asaasService = new \Services\AsaasService();
                
                // Mapear campos do banco local para campos do Asaas
                $asaasData = [];
                
                // Campos básicos
                if (!empty($dados['nome'])) {
                    $asaasData['name'] = $dados['nome'];
                }
                if (!empty($dados['email'])) {
                    $asaasData['email'] = $dados['email'];
                }
                if (!empty($dados['telefone'])) {
                    $asaasData['phone'] = $dados['telefone'];
                }
                if (!empty($dados['celular'])) {
                    $asaasData['mobilePhone'] = $dados['celular'];
                }
                
                // CPF/CNPJ - não permitir alteração, usar valor atual
                if (!empty($cliente['cpf_cnpj'])) {
                    $asaasData['cpfCnpj'] = $cliente['cpf_cnpj'];
                }
                
                // Endereço
                if (!empty($dados['cep'])) {
                    $asaasData['postalCode'] = $dados['cep'];
                }
                if (!empty($dados['rua'])) {
                    $asaasData['address'] = $dados['rua'];
                }
                if (!empty($dados['numero'])) {
                    $asaasData['addressNumber'] = $dados['numero'];
                }
                if (!empty($dados['complemento'])) {
                    $asaasData['complement'] = $dados['complemento'];
                }
                if (!empty($dados['bairro'])) {
                    $asaasData['province'] = $dados['bairro'];
                }
                if (!empty($dados['cidade'])) {
                    $asaasData['city'] = $dados['cidade'];
                }
                if (!empty($dados['estado'])) {
                    $asaasData['state'] = $dados['estado'];
                }
                if (!empty($dados['pais'])) {
                    $asaasData['country'] = $dados['pais'];
                }
                
                // Campos adicionais
                if (!empty($dados['referencia_externa'])) {
                    $asaasData['externalReference'] = $dados['referencia_externa'];
                }
                if (!empty($dados['observacoes'])) {
                    $asaasData['observations'] = $dados['observacoes'];
                }
                if (!empty($dados['razao_social'])) {
                    $asaasData['company'] = $dados['razao_social'];
                }
                
                try {
                    $asaasService->updateCustomer($cliente['asaas_id'], $asaasData);
                } catch (\Exception $e) {
                    // Logar erro ou retornar mensagem amigável
                    return [
                        'success' => false,
                        'message' => 'Cliente atualizado localmente, mas houve erro ao sincronizar com o Asaas: ' . $e->getMessage()
                    ];
                }
            }
            
            return [
                'success' => true,
                'message' => 'Cliente atualizado com sucesso'
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Buscar cobranças do cliente
     */
    public function buscarCobrancasCliente($cliente_id, $limite = 10) {
        $stmt = $this->mysqli->prepare("
            SELECT c.*, cl.nome as cliente_nome 
            FROM cobrancas c 
            LEFT JOIN clientes cl ON c.cliente_id = cl.id 
            WHERE c.cliente_id = ? 
            ORDER BY c.data_criacao DESC 
            LIMIT ?
        ");
        $stmt->bind_param('ii', $cliente_id, $limite);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $cobrancas = [];
        while ($row = $result->fetch_assoc()) {
            $cobrancas[] = $row;
        }
        $stmt->close();
        
        return $cobrancas;
    }
    
    /**
     * Buscar assinaturas do cliente
     */
    public function buscarAssinaturasCliente($cliente_id) {
        $stmt = $this->mysqli->prepare("
            SELECT a.*, cl.nome as cliente_nome 
            FROM assinaturas a 
            LEFT JOIN clientes cl ON a.cliente_id = cl.id 
            WHERE a.cliente_id = ? 
            ORDER BY a.created_at DESC
        ");
        $stmt->bind_param('i', $cliente_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $assinaturas = [];
        while ($row = $result->fetch_assoc()) {
            $assinaturas[] = $row;
        }
        $stmt->close();
        
        return $assinaturas;
    }
    
    /**
     * Validar CPF/CNPJ
     */
    private function validarCpfCnpj($cpf_cnpj) {
        // Remove caracteres não numéricos
        $cpf_cnpj = preg_replace('/[^0-9]/', '', $cpf_cnpj);
        
        // Verifica se tem 11 (CPF) ou 14 (CNPJ) dígitos
        if (strlen($cpf_cnpj) !== 11 && strlen($cpf_cnpj) !== 14) {
            return false;
        }
        
        // Validação básica - em produção seria mais robusta
        return true;
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
        $controller = new ClienteController();
        $action = $_POST['action'];
        
        switch ($action) {
            case 'criar':
                $resultado = $controller->criarCliente($_POST);
                break;
                
            case 'atualizar':
                $id = $_POST['id'] ?? 0;
                $resultado = $controller->atualizarCliente($id, $_POST);
                break;
                
            case 'buscar':
                $id = $_POST['id'] ?? 0;
                $cliente = $controller->buscarCliente($id);
                $resultado = [
                    'success' => $cliente ? true : false,
                    'data' => $cliente
                ];
                break;
                
            case 'listar':
                $filtro = $_POST['filtro'] ?? '';
                $pagina = $_POST['pagina'] ?? 1;
                $resultado = $controller->listarClientes($filtro, $pagina);
                break;
                
            case 'buscarPorCpfCnpj':
                $cpf_cnpj = $_POST['cpf_cnpj'] ?? '';
                $cliente = $controller->buscarPorCpfCnpj($cpf_cnpj);
                $resultado = [
                    'success' => $cliente ? true : false,
                    'data' => $cliente
                ];
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