<?php
class AsaasService {
    private $apiKey;
    private $apiUrl;

    public function __construct() {
        $this->apiKey = getenv('ASAAS_API_KEY') ?: (defined('ASAAS_API_KEY') ? ASAAS_API_KEY : '');
        $this->apiUrl = getenv('ASAAS_API_URL') ?: (defined('ASAAS_API_URL') ? ASAAS_API_URL : '');
    }

    public function request($method, $endpoint, $data = null) {
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
        return [
            'status' => $httpCode,
            'body' => json_decode($result, true)
        ];
    }

    public function criarFatura($data) {
        return $this->request('POST', 'payments', $data);
    }

    public function reenviarLink($asaas_id) {
        return $this->request('POST', "payments/$asaas_id/sendEmail");
    }

    public function cancelarFatura($asaas_id) {
        return $this->request('POST', "payments/$asaas_id/cancel");
    }

    public function obterPDF($asaas_id) {
        return $this->request('GET', "payments/$asaas_id/identificationField");
    }

    public function atualizarStatus($asaas_id, $status) {
        // O status é atualizado via webhook normalmente, mas pode ser implementado aqui se necessário
        return $this->request('POST', "payments/$asaas_id", ['status' => $status]);
    }

    public function getApiKey() {
        return $this->apiKey;
    }

    public function getApiUrl() {
        return $this->apiUrl;
    }
} 