# Configurar Webhook Específico para Canal 3001 (Comercial)
Write-Host "=== CONFIGURACAO WEBHOOK CANAL 3001 (COMERCIAL) ===" -ForegroundColor Green

# URL específica para o canal comercial
$webhook_url_comercial = "https://app.pixel12digital.com.br/api/webhook_canal_37.php"

Write-Host "Configurando webhook do canal 3001 para: $webhook_url_comercial" -ForegroundColor Cyan

# Configurar webhook no servidor 3001
$body = @{
    url = $webhook_url_comercial
} | ConvertTo-Json

try {
    $response = Invoke-WebRequest -Uri "http://212.85.11.238:3001/webhook/config" -Method POST -ContentType "application/json" -Body $body -TimeoutSec 10
    
    if ($response.StatusCode -eq 200) {
        $result = $response.Content | ConvertFrom-Json
        Write-Host "SUCESSO: Webhook canal 3001 configurado!" -ForegroundColor Green
        Write-Host "URL configurada: $($result.webhook_url)" -ForegroundColor Gray
    } else {
        Write-Host "ERRO: Status $($response.StatusCode)" -ForegroundColor Red
    }
} catch {
    Write-Host "ERRO ao configurar webhook: $($_.Exception.Message)" -ForegroundColor Red
}

# Verificar se a configuração foi aplicada
Write-Host ""
Write-Host "Verificando configuracao..." -ForegroundColor Cyan
try {
    $verify_response = Invoke-WebRequest -Uri "http://212.85.11.238:3001/webhook/config" -Method GET -TimeoutSec 10
    $verify_result = $verify_response.Content | ConvertFrom-Json
    
    Write-Host "Webhook atual do canal 3001: $($verify_result.webhook_url)" -ForegroundColor White
    
    if ($verify_result.webhook_url -eq $webhook_url_comercial) {
        Write-Host "CONFIRMADO: Webhook configurado corretamente!" -ForegroundColor Green
    } else {
        Write-Host "ATENCAO: Webhook nao corresponde ao esperado" -ForegroundColor Yellow
    }
} catch {
    Write-Host "ERRO ao verificar configuracao: $($_.Exception.Message)" -ForegroundColor Red
}

Write-Host ""
Write-Host "=== CONFIGURACAO CONCLUIDA ===" -ForegroundColor Green 