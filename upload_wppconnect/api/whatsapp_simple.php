<?php
/**
 * Integração SIMPLES com WPPConnect
 * Copie este arquivo e use! Zero configuração complexa.
 */

class WhatsAppSimple {
    private $base_url;
    private $mysqli;
    
    public function __construct($mysqli, $base_url = 'http://localhost:8080') {
        $this->base_url = rtrim($base_url, '/');
        $this->mysqli = $mysqli;
    }
    
    /**
     * ENVIAR MENSAGEM - Função principal
     */
    public function enviar($numero, $mensagem, $sessao = 'default') {
        $url = $this->base_url . '/api/sendText/' . $sessao;
        
        $data = [
            'number' => $this->formatarNumero($numero),
            'text' => $mensagem
        ];
        
        return $this->fazerRequisicao($url, $data);
    }
    
    /**
     * ENVIAR COBRANÇA ASAAS
     */
    public function enviarCobranca($cliente_id, $cobranca_id, $sessao = 'default') {
        // Buscar dados
        $sql = "SELECT c.*, cl.nome, cl.celular 
                FROM cobrancas c 
                JOIN clientes cl ON c.cliente_id = cl.id 
                WHERE c.id = $cobranca_id AND c.cliente_id = $cliente_id";
        
        $result = $this->mysqli->query($sql);
        if (!$result || $result->num_rows === 0) {
            return ['sucesso' => false, 'erro' => 'Cobrança não encontrada'];
        }
        
        $cobranca = $result->fetch_assoc();
        
        // Criar mensagem
        $mensagem = "💳 *Cobrança Gerada*\n\n";
        $mensagem .= "Olá {$cobranca['nome']}!\n\n";
        $mensagem .= "Valor: *R$ " . number_format($cobranca['valor'], 2, ',', '.') . "*\n";
        $mensagem .= "Vencimento: *" . date('d/m/Y', strtotime($cobranca['data_vencimento'])) . "*\n\n";
        
        if ($cobranca['link_pagamento']) {
            $mensagem .= "🔗 *Link:* {$cobranca['link_pagamento']}\n\n";
        }
        
        $mensagem .= "Obrigado! 🙏";
        
        // Enviar
        $resultado = $this->enviar($cobranca['celular'], $mensagem, $sessao);
        
        // Salvar no histórico
        if ($resultado['sucesso']) {
            $this->salvarHistorico($cliente_id, $cobranca_id, $mensagem, 'cobranca');
        }
        
        return $resultado;
    }
    
    /**
     * ENVIAR PROSPECÇÃO
     */
    public function enviarProspeccao($cliente_id, $mensagem = null, $sessao = 'default') {
        $sql = "SELECT * FROM clientes WHERE id = $cliente_id";
        $result = $this->mysqli->query($sql);
        
        if (!$result || $result->num_rows === 0) {
            return ['sucesso' => false, 'erro' => 'Cliente não encontrado'];
        }
        
        $cliente = $result->fetch_assoc();
        
        if (!$mensagem) {
            $mensagem = "Olá {$cliente['nome']}! 👋\n\nTemos uma oferta especial para você!\n\nQuer saber mais?";
        }
        
        $resultado = $this->enviar($cliente['celular'], $mensagem, $sessao);
        
        if ($resultado['sucesso']) {
            $this->salvarHistorico($cliente_id, null, $mensagem, 'prospeccao');
        }
        
        return $resultado;
    }
    
    /**
     * VERIFICAR STATUS
     */
    public function status($sessao = 'default') {
        $url = $this->base_url . '/api/sessions/find/' . $sessao;
        return $this->fazerRequisicao($url);
    }
    
    /**
     * INICIAR SESSÃO
     */
    public function iniciarSessao($sessao = 'default') {
        $url = $this->base_url . '/api/sessions/start';
        $data = [
            'session' => $sessao,
            'webhook' => 'https://seudominio.com/api/webhook.php'
        ];
        
        return $this->fazerRequisicao($url, $data, 'POST');
    }
    
    /**
     * OBTER QR CODE
     */
    public function qrCode($sessao = 'default') {
        $url = $this->base_url . '/api/sessions/qrcode/' . $sessao;
        return $this->fazerRequisicao($url);
    }
    
    /**
     * Formatar número
     */
    private function formatarNumero($numero) {
        $numero = preg_replace('/\D/', '', $numero);
        if (!preg_match('/^55/', $numero)) {
            $numero = '55' . $numero;
        }
        return $numero;
    }
    
    /**
     * Salvar histórico
     */
    private function salvarHistorico($cliente_id, $cobranca_id, $mensagem, $tipo) {
        $mensagem_escaped = $this->mysqli->real_escape_string($mensagem);
        $tipo_escaped = $this->mysqli->real_escape_string($tipo);
        $cobranca_id = $cobranca_id ? intval($cobranca_id) : 'NULL';
        $data_hora = date('Y-m-d H:i:s');
        
        $sql = "INSERT INTO mensagens_comunicacao (cliente_id, cobranca_id, mensagem, tipo, data_hora, direcao, status) 
                VALUES ($cliente_id, $cobranca_id, '$mensagem_escaped', '$tipo_escaped', '$data_hora', 'enviado', 'entregue')";
        
        return $this->mysqli->query($sql);
    }
    
    /**
     * Fazer requisição HTTP
     */
    private function fazerRequisicao($url, $data = null, $metodo = 'GET') {
        $ch = curl_init();
        
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        
        if ($metodo === 'POST' && $data) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            return ['sucesso' => false, 'erro' => $error];
        }
        
        $json = json_decode($response, true);
        
        if ($http_code >= 200 && $http_code < 300) {
            return ['sucesso' => true, 'dados' => $json];
        } else {
            return ['sucesso' => false, 'erro' => $json['message'] ?? 'Erro HTTP ' . $http_code];
        }
    }
}

// EXEMPLO DE USO:
/*
require_once 'painel/config.php';
require_once 'painel/db.php';
require_once 'api/whatsapp_simple.php';

$whatsapp = new WhatsAppSimple($mysqli, 'http://localhost:8080');

// Enviar mensagem simples
$resultado = $whatsapp->enviar('11999999999', 'Olá! Teste de mensagem.');

// Enviar cobrança
$resultado = $whatsapp->enviarCobranca(1, 1);

// Enviar prospecção
$resultado = $whatsapp->enviarProspeccao(1);

// Verificar status
$status = $whatsapp->status();
*/
?> 