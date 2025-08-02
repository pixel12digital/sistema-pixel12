$webhookData = @{
    event = "onmessage"
    data = @{
        from = "554797309525"
        text = "Teste de mensagem de entrada - " + (Get-Date -Format "HH:mm:ss")
        type = "text"
        timestamp = [DateTimeOffset]::Now.ToUnixTimeSeconds()
        session = "default"
    }
} | ConvertTo-Json -Depth 3

Write-Host "Enviando mensagem simulada para o webhook..."
Write-Host "Dados: $webhookData"

$response = Invoke-WebRequest -Uri "https://app.pixel12digital.com.br/webhook_sem_redirect/webhook.php" -Method POST -ContentType "application/json" -Body $webhookData

Write-Host "Status: $($response.StatusCode)"
Write-Host "Resposta: $($response.Content)" 