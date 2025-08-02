# Teste detalhado do Canal 3001 (Comercial)
Write-Host "=== TESTE DETALHADO CANAL 3001 ===" -ForegroundColor Green

$vps_url = "http://212.85.11.238:3001"
$numero_teste = "554796164699"

# 1. Verificar status detalhado
Write-Host "1. Status detalhado do canal 3001..." -ForegroundColor Cyan
try {
    $status_response = Invoke-WebRequest -Uri "$vps_url/status" -Method GET -TimeoutSec 10
    $status_data = $status_response.Content | ConvertFrom-Json
    
    Write-Host "   Resposta completa:" -ForegroundColor Yellow
    Write-Host "   $($status_response.Content)" -ForegroundColor Gray
    
    if ($status_data.clients_status.comercial) {
        Write-Host "   Sessao comercial encontrada:" -ForegroundColor Green
        Write-Host "   Status: $($status_data.clients_status.comercial.status)" -ForegroundColor White
        Write-Host "   Mensagem: $($status_data.clients_status.comercial.message)" -ForegroundColor White
    }
} catch {
    Write-Host "   ERRO: $($_.Exception.Message)" -ForegroundColor Red
}

# 2. Testar diferentes formatos de envio
Write-Host "2. Testando diferentes formatos de envio..." -ForegroundColor Cyan

# Teste 1: Formato padrão
Write-Host "   Teste 1: Formato padrao" -ForegroundColor White
try {
    $send_data1 = @{
        number = $numero_teste
        message = "Teste 1 - Canal Comercial $(Get-Date -Format 'HH:mm:ss')"
    } | ConvertTo-Json
    
    Write-Host "   Dados enviados: $send_data1" -ForegroundColor Gray
    
    $send_response1 = Invoke-WebRequest -Uri "$vps_url/send/text" -Method POST -ContentType "application/json" -Body $send_data1 -TimeoutSec 15
    Write-Host "   Status: $($send_response1.StatusCode)" -ForegroundColor Green
    Write-Host "   Resposta: $($send_response1.Content)" -ForegroundColor Gray
} catch {
    Write-Host "   ERRO: $($_.Exception.Message)" -ForegroundColor Red
    if ($_.Exception.Response) {
        $error_content = $_.Exception.Response.GetResponseStream()
        $reader = New-Object System.IO.StreamReader($error_content)
        $error_text = $reader.ReadToEnd()
        Write-Host "   Detalhes do erro: $error_text" -ForegroundColor Red
    }
}

# Teste 2: Com sessão específica
Write-Host "   Teste 2: Com sessao especifica" -ForegroundColor White
try {
    $send_data2 = @{
        number = $numero_teste
        message = "Teste 2 - Canal Comercial $(Get-Date -Format 'HH:mm:ss')"
        session = "comercial"
    } | ConvertTo-Json
    
    Write-Host "   Dados enviados: $send_data2" -ForegroundColor Gray
    
    $send_response2 = Invoke-WebRequest -Uri "$vps_url/send/text" -Method POST -ContentType "application/json" -Body $send_data2 -TimeoutSec 15
    Write-Host "   Status: $($send_response2.StatusCode)" -ForegroundColor Green
    Write-Host "   Resposta: $($send_response2.Content)" -ForegroundColor Gray
} catch {
    Write-Host "   ERRO: $($_.Exception.Message)" -ForegroundColor Red
}

# Teste 3: Verificar endpoints disponíveis
Write-Host "3. Verificando endpoints disponiveis..." -ForegroundColor Cyan
try {
    $sessions_response = Invoke-WebRequest -Uri "$vps_url/sessions" -Method GET -TimeoutSec 10
    Write-Host "   Sessoes disponiveis: $($sessions_response.Content)" -ForegroundColor Gray
} catch {
    Write-Host "   ERRO ao verificar sessoes: $($_.Exception.Message)" -ForegroundColor Red
}

Write-Host "=== TESTE DETALHADO CONCLUIDO ===" -ForegroundColor Green 