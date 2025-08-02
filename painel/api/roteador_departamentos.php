<?php
/**
 * 🎯 ROTEADOR DE DEPARTAMENTOS - PASSO 2
 * 
 * Sistema inteligente que detecta qual departamento deve atender cada mensagem
 * Preparado para integração com Ana (agentes.pixel12digital.com.br)
 */

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../db.php';

header('Content-Type: application/json');

/**
 * Classe principal do roteador
 */
class RoteadorDepartamentos {
    
    private $mysqli;
    private $departamentos_cache = null;
    
    public function __construct($mysqli) {
        $this->mysqli = $mysqli;
    }
    
    /**
     * Processar mensagem recebida e rotear para departamento correto
     */
    public function processarMensagem($dados_mensagem) {
        $resultado = [
            'success' => false,
            'departamento' => null,
            'metodo_deteccao' => 'nenhum',
            'confianca' => 0,
            'resposta_sugerida' => '',
            'proxima_acao' => 'aguardar',
            'debug' => []
        ];
        
        try {
            // 1. Normalizar dados da mensagem
            $mensagem = $this->normalizarMensagem($dados_mensagem);
            $resultado['debug'][] = "Mensagem normalizada: " . substr($mensagem['texto'], 0, 50);
            
            // 2. Detectar departamento por palavras-chave
            $departamento = $this->detectarDepartamentoPorPalavras($mensagem['texto']);
            
            if ($departamento) {
                $resultado['success'] = true;
                $resultado['departamento'] = $departamento;
                $resultado['metodo_deteccao'] = 'palavras_chave';
                $resultado['confianca'] = $departamento['confianca'];
                $resultado['debug'][] = "Departamento detectado: {$departamento['nome']} ({$departamento['codigo']})";
                
                // 3. Gerar resposta inicial baseada no departamento
                $resultado['resposta_sugerida'] = $this->gerarRespostaInicial($departamento, $mensagem);
                $resultado['proxima_acao'] = 'responder_departamento';
                
            } else {
                // 4. Fallback para departamento geral ou Ana
                $resultado['departamento'] = $this->obterDepartamentoPadrao();
                $resultado['metodo_deteccao'] = 'fallback_padrao';
                $resultado['confianca'] = 50;
                $resultado['resposta_sugerida'] = $this->gerarRespostaGeral($mensagem);
                $resultado['proxima_acao'] = 'apresentar_opcoes';
                $resultado['debug'][] = "Usando departamento padrão (fallback)";
            }
            
        } catch (Exception $e) {
            $resultado['debug'][] = "Erro: " . $e->getMessage();
        }
        
        return $resultado;
    }
    
    /**
     * Normalizar dados da mensagem recebida
     */
    private function normalizarMensagem($dados) {
        return [
            'texto' => strtolower(trim($dados['body'] ?? $dados['message'] ?? '')),
            'numero' => $dados['from'] ?? '',
            'tipo' => $dados['type'] ?? 'text',
            'timestamp' => $dados['timestamp'] ?? time()
        ];
    }
    
    /**
     * Detectar departamento usando palavras-chave
     */
    private function detectarDepartamentoPorPalavras($texto) {
        $departamentos = $this->obterDepartamentos();
        $melhor_match = null;
        $maior_score = 0;
        
        foreach ($departamentos as $dept) {
            $palavras_chave = json_decode($dept['palavras_chave'], true) ?: [];
            $score = 0;
            $matches = 0;
            
            foreach ($palavras_chave as $palavra) {
                if (strpos($texto, strtolower($palavra)) !== false) {
                    $score += strlen($palavra); // Palavras maiores têm mais peso
                    $matches++;
                }
            }
            
            // Calcular score com base em matches e tamanho das palavras
            if ($matches > 0) {
                $score_final = ($score * $matches) / count($palavras_chave);
                
                if ($score_final > $maior_score) {
                    $maior_score = $score_final;
                    $melhor_match = $dept;
                    $melhor_match['confianca'] = min(95, 60 + ($matches * 10));
                }
            }
        }
        
        return $melhor_match;
    }
    
    /**
     * Obter todos os departamentos ativos
     */
    private function obterDepartamentos() {
        if ($this->departamentos_cache === null) {
            $sql = "SELECT * FROM departamentos 
                    WHERE canal_id = 36 AND status = 'ativo' 
                    ORDER BY ordem_exibicao";
            
            $result = $this->mysqli->query($sql);
            $this->departamentos_cache = [];
            
            if ($result) {
                while ($row = $result->fetch_assoc()) {
                    $this->departamentos_cache[] = $row;
                }
            }
        }
        
        return $this->departamentos_cache;
    }
    
    /**
     * Obter departamento padrão (primeiro da lista)
     */
    private function obterDepartamentoPadrao() {
        $departamentos = $this->obterDepartamentos();
        return $departamentos[0] ?? null;
    }
    
    /**
     * Gerar resposta inicial específica do departamento
     */
    private function gerarRespostaInicial($departamento, $mensagem) {
        $templates = [
            'FIN' => "Olá! 👋 Vejo que você tem uma questão financeira.\n\n💰 Sou a Ana, assistente do *FINANCEIRO* da Pixel12Digital.\n\nComo posso ajudá-lo hoje?",
            
            'SUP' => "Olá! 👋 Identifiquei que você precisa de suporte técnico.\n\n🔧 Sou a Ana, assistente do *SUPORTE TÉCNICO* da Pixel12Digital.\n\nVou ajudá-lo a resolver seu problema!",
            
            'COM' => "Olá! 👋 Vejo que você tem interesse comercial.\n\n💼 Sou a Ana, assistente *COMERCIAL* da Pixel12Digital.\n\nVamos conversar sobre nossos serviços?",
            
            'ADM' => "Olá! 👋 Identifiquei uma questão administrativa.\n\n📋 Sou a Ana, assistente *ADMINISTRATIVA* da Pixel12Digital.\n\nEm que posso auxiliá-lo?"
        ];
        
        return $templates[$departamento['codigo']] ?? $this->gerarRespostaGeral($mensagem);
    }
    
    /**
     * Gerar resposta geral quando não há detecção específica
     */
    private function gerarRespostaGeral($mensagem) {
        return "Olá! 👋 Eu sou a *Ana*, assistente virtual da *Pixel12Digital*.\n\n" .
               "📋 *Como posso ajudá-lo hoje?*\n\n" .
               "1️⃣ *FINANCEIRO* - Faturas e pagamentos\n" .
               "2️⃣ *SUPORTE* - Problemas técnicos\n" .
               "3️⃣ *COMERCIAL* - Vendas e orçamentos\n" .
               "4️⃣ *ADMINISTRAÇÃO* - Contratos e documentos\n\n" .
               "Digite o *número* ou descreva sua necessidade! 😊";
    }
    
    /**
     * Listar departamentos disponíveis
     */
    public function listarDepartamentos() {
        $departamentos = $this->obterDepartamentos();
        $lista = [];
        
        foreach ($departamentos as $dept) {
            $lista[] = [
                'codigo' => $dept['codigo'],
                'nome' => $dept['nome'],
                'descricao' => $dept['descricao'],
                'status' => $dept['status'],
                'palavras_chave' => json_decode($dept['palavras_chave'], true)
            ];
        }
        
        return $lista;
    }
    
    /**
     * Testar detecção com texto específico
     */
    public function testarDeteccao($texto_teste) {
        return $this->processarMensagem(['body' => $texto_teste, 'from' => 'teste']);
    }
}

// ===== PROCESSAMENTO DA REQUISIÇÃO =====

$roteador = new RoteadorDepartamentos($mysqli);

// Se for requisição POST, processar mensagem
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = file_get_contents('php://input');
    $dados = json_decode($input, true);
    
    if ($dados) {
        $resultado = $roteador->processarMensagem($dados);
        echo json_encode($resultado, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    } else {
        echo json_encode(['success' => false, 'error' => 'Dados inválidos']);
    }
    
// Se for GET, retornar informações dos departamentos ou teste
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    
    if (isset($_GET['acao'])) {
        switch ($_GET['acao']) {
            case 'listar':
                echo json_encode([
                    'success' => true,
                    'departamentos' => $roteador->listarDepartamentos()
                ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
                break;
                
            case 'testar':
                $texto = $_GET['texto'] ?? 'Olá, preciso de ajuda';
                $resultado = $roteador->testarDeteccao($texto);
                echo json_encode($resultado, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
                break;
                
            default:
                echo json_encode(['success' => false, 'error' => 'Ação inválida']);
        }
    } else {
        // Retornar status do roteador
        echo json_encode([
            'success' => true,
            'status' => 'ativo',
            'departamentos_count' => count($roteador->listarDepartamentos()),
            'versao' => '1.0 - Preparado para Ana'
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }
    
} else {
    echo json_encode(['success' => false, 'error' => 'Método não permitido']);
}

$mysqli->close();
?> 