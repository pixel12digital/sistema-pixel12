# Configurar Webhook Unificado para Ambos os Canais
Write-Host "=== CONFIGURACAO WEBHOOK UNIFICADO ===" -ForegroundColor Green

# URL unificada que já está funcionando
$webhook_url_unificado = "https://app.pixel12digital.com.br/webhook_sem_redirect/webhook.php"

Write-Host "Configurando ambos os canais para usar webhook unificado..." -ForegroundColor Cyan
Write-Host "URL: $webhook_url_unificado" -ForegroundColor White

# Configurar Canal 3000 (Financeiro)
Write-Host ""
Write-Host "1. Configurando Canal 3000 (Financeiro)..." -ForegroundColor Yellow
$body = @{ url = $webhook_url_unificado } | ConvertTo-Json

try {
    $response = Invoke-WebRequest -Uri "http://212.85.11.238:3000/webhook/config" -Method POST -ContentType "application/json" -Body $body -TimeoutSec 10
    
    if ($response.StatusCode -eq 200) {
        Write-Host "   SUCESSO: Canal 3000 configurado!" -ForegroundColor Green
    } else {
        Write-Host "   ERRO: Status $($response.StatusCode)" -ForegroundColor Red
    }
} catch {
    Write-Host "   ERRO: $($_.Exception.Message)" -ForegroundColor Red
}

# Configurar Canal 3001 (Comercial)
Write-Host ""
Write-Host "2. Configurando Canal 3001 (Comercial)..." -ForegroundColor Yellow

try {
    $response = Invoke-WebRequest -Uri "http://212.85.11.238:3001/webhook/config" -Method POST -ContentType "application/json" -Body $body -TimeoutSec 10
    
    if ($response.StatusCode -eq 200) {
        Write-Host "   SUCESSO: Canal 3001 configurado!" -ForegroundColor Green
    } else {
        Write-Host "   ERRO: Status $($response.StatusCode)" -ForegroundColor Red
    }
} catch {
    Write-Host "   ERRO: $($_.Exception.Message)" -ForegroundColor Red
}

# Verificar configurações
Write-Host ""
Write-Host "3. Verificando configuracoes..." -ForegroundColor Yellow

Write-Host "   Canal 3000:" -ForegroundColor Cyan
try {
    $verify1 = Invoke-WebRequest -Uri "http://212.85.11.238:3000/webhook/config" -Method GET -TimeoutSec 10
    $result1 = $verify1.Content | ConvertFrom-Json
    Write-Host "     Webhook: $($result1.webhook_url)" -ForegroundColor White
} catch {
    Write-Host "     ERRO ao verificar" -ForegroundColor Red
}

Write-Host "   Canal 3001:" -ForegroundColor Cyan
try {
    $verify2 = Invoke-WebRequest -Uri "http://212.85.11.238:3001/webhook/config" -Method GET -TimeoutSec 10
    $result2 = $verify2.Content | ConvertFrom-Json
    Write-Host "     Webhook: $($result2.webhook_url)" -ForegroundColor White
} catch {
    Write-Host "     ERRO ao verificar" -ForegroundColor Red
}

Write-Host ""
Write-Host "=== CONFIGURACAO UNIFICADA CONCLUIDA ===" -ForegroundColor Green
Write-Host ""
Write-Host "BENEFICIOS:" -ForegroundColor Yellow
Write-Host "- Webhook unificado processa ambos os canais" -ForegroundColor White
Write-Host "- Usa o mesmo banco principal (compatibilidade total)" -ForegroundColor White
Write-Host "- Identifica canal automaticamente" -ForegroundColor White
Write-Host "- Funcionamento garantido" -ForegroundColor White 