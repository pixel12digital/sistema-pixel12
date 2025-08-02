# Teste corrigido do Canal 3001 (Comercial)
Write-Host "=== TESTE CORRIGIDO CANAL 3001 ===" -ForegroundColor Green

$vps_url = "http://212.85.11.238:3001"
$numero_teste = "554796164699"

# Teste com formato correto da API
Write-Host "Enviando mensagem com formato correto..." -ForegroundColor Cyan
try {
    $send_data = @{
        sessionName = "comercial"
        number = $numero_teste
        message = "Teste corrigido - Canal Comercial $(Get-Date -Format 'HH:mm:ss')"
    } | ConvertTo-Json
    
    Write-Host "Dados enviados: $send_data" -ForegroundColor Gray
    
    $send_response = Invoke-WebRequest -Uri "$vps_url/send/text" -Method POST -ContentType "application/json" -Body $send_data -TimeoutSec 15
    Write-Host "Status: $($send_response.StatusCode)" -ForegroundColor Green
    Write-Host "Resposta: $($send_response.Content)" -ForegroundColor Gray
} catch {
    Write-Host "ERRO: $($_.Exception.Message)" -ForegroundColor Red
    if ($_.Exception.Response) {
        $error_content = $_.Exception.Response.GetResponseStream()
        $reader = New-Object System.IO.StreamReader($error_content)
        $error_text = $reader.ReadToEnd()
        Write-Host "Detalhes do erro: $error_text" -ForegroundColor Red
    }
}

Write-Host "=== TESTE CORRIGIDO CONCLUIDO ===" -ForegroundColor Green 