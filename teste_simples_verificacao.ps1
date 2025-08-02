# Teste Simples de Verificacao
Write-Host "=== TESTE SIMPLES DE VERIFICACAO ===" -ForegroundColor Green

# Enviar uma mensagem de teste final
$webhook_data = @{
    event = "onmessage"
    data = @{
        from = "554796164699"
        text = "Teste final de verificacao - $(Get-Date -Format 'HH:mm:ss') - Por favor, confirme se esta mensagem aparece no chat do sistema"
        type = "text"
        timestamp = [DateTimeOffset]::Now.ToUnixTimeSeconds()
        session = "default"
    }
} | ConvertTo-Json -Depth 3

Write-Host "Enviando mensagem de teste final..." -ForegroundColor Cyan
Write-Host "Mensagem: $($webhook_data)" -ForegroundColor Gray

try {
    $response = Invoke-WebRequest -Uri "https://app.pixel12digital.com.br/webhook_sem_redirect/webhook.php" -Method POST -ContentType "application/json" -Body $webhook_data -TimeoutSec 10
    $result = $response.Content | ConvertFrom-Json
    
    Write-Host "Status: $($response.StatusCode)" -ForegroundColor Green
    Write-Host "Resposta: $($response.Content)" -ForegroundColor Gray
    
    if ($result.status -eq "ok") {
        Write-Host "SUCESSO: Mensagem processada pelo webhook!" -ForegroundColor Green
    } else {
        Write-Host "ERRO: Webhook nao processou corretamente" -ForegroundColor Red
    }
} catch {
    Write-Host "ERRO: $($_.Exception.Message)" -ForegroundColor Red
}

Write-Host ""
Write-Host "=== INSTRUCOES FINAIS ===" -ForegroundColor Yellow
Write-Host "1. Acesse: https://app.pixel12digital.com.br/painel/chat.php" -ForegroundColor White
Write-Host "2. Procure por: 29.714.777 Charles Dietrich Wutzke" -ForegroundColor White
Write-Host "3. Verifique se a mensagem aparece na conversa" -ForegroundColor White
Write-Host "4. Confirme se o contador de 'Nao Lidas' foi atualizado" -ForegroundColor White
Write-Host ""
Write-Host "Se as mensagens aparecem no chat, o sistema esta funcionando corretamente!" -ForegroundColor Green 