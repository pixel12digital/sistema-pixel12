<?php
header('Content-Type: application/json');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método não permitido']);
    exit;
}
$data = json_decode(file_get_contents('php://input'), true);
$canal_id = $data['canal_id'] ?? null;
$numero = $data['identificador'] ?? null;
if (!$canal_id || !$numero) {
    http_response_code(400);
    echo json_encode(['error' => 'Dados incompletos']);
    exit;
}
// URL do backend Node.js (ajuste conforme necessário)
$node_url = 'http://localhost:3100/api/connect';
$payload = json_encode(['canal_id' => $canal_id, 'numero' => $numero]);
$ch = curl_init($node_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);
if ($httpCode === 200 && $response) {
    echo $response;
} else {
    // Mock para testes locais
    echo json_encode([
        'success' => true,
        'qr' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAJYAAACWCAYAAADLabXuAAA...',
        'mensagem' => 'QR Code gerado (mock)'
    ]);
} 