# Teste Final - Ambos os Canais com Webhook Unificado
Write-Host "=== TESTE FINAL AMBOS OS CANAIS ===" -ForegroundColor Green
Write-Host "Cliente: 29.714.777 Charles Dietrich Wutzke" -ForegroundColor Yellow
Write-Host "Numero: 554796164699" -ForegroundColor Yellow
Write-Host ""

# Função para testar recebimento por canal com webhook unificado
function Test-CanalUnificado {
    param(
        [string]$CanalNome,
        [string]$SessionName,
        [string]$CanalId
    )
    
    Write-Host "--- Testando $CanalNome (Session: $SessionName) ---" -ForegroundColor Cyan
    
    $numero_teste = "554796164699"
    $timestamp = [DateTimeOffset]::Now.ToUnixTimeSeconds()
    $mensagem_teste = "TESTE FINAL $CanalNome UNIFICADO - $(Get-Date -Format 'HH:mm:ss')"
    
    # Simular mensagem recebida do WhatsApp
    $webhook_data = @{
        event = "onmessage"
        data = @{
            from = $numero_teste
            text = $mensagem_teste
            type = "text"
            timestamp = $timestamp
            session = $SessionName
        }
    } | ConvertTo-Json -Depth 3
    
    Write-Host "Enviando para webhook unificado..." -ForegroundColor White
    Write-Host "Mensagem: $mensagem_teste" -ForegroundColor Gray
    
    try {
        $webhook_response = Invoke-WebRequest -Uri "https://app.pixel12digital.com.br/webhook_sem_redirect/webhook.php" -Method POST -ContentType "application/json" -Body $webhook_data -TimeoutSec 10
        $webhook_result = $webhook_response.Content | ConvertFrom-Json
        
        if ($webhook_result.status -eq "ok") {
            Write-Host "   OK Webhook processado com sucesso!" -ForegroundColor Green
            Write-Host "   Resposta: $($webhook_result.message)" -ForegroundColor Gray
            Write-Host "   Ambiente: $($webhook_result.ambiente)" -ForegroundColor Gray
            Write-Host "   Webhook Type: $($webhook_result.webhook_type)" -ForegroundColor Gray
        } else {
            Write-Host "   ERRO no webhook: $($webhook_result)" -ForegroundColor Red
        }
    } catch {
        Write-Host "   ERRO ao enviar webhook: $($_.Exception.Message)" -ForegroundColor Red
    }
    
    Write-Host ""
}

# Teste Canal Financeiro (3000)
Test-CanalUnificado -CanalNome "Financeiro" -SessionName "default" -CanalId "36"

# Aguardar 3 segundos
Start-Sleep -Seconds 3

# Teste Canal Comercial (3001)
Test-CanalUnificado -CanalNome "Comercial" -SessionName "comercial" -CanalId "37"

# Aguardar 3 segundos
Start-Sleep -Seconds 3

# Teste adicional: Mensagem longa no canal comercial
Write-Host "--- Teste Adicional: Mensagem Longa Canal Comercial ---" -ForegroundColor Cyan

$webhook_data_longa = @{
    event = "onmessage"
    data = @{
        from = "554796164699"
        text = "TESTE MENSAGEM LONGA COMERCIAL - $(Get-Date -Format 'HH:mm:ss') - Esta é uma mensagem mais longa para verificar se o sistema processa corretamente mensagens extensas vindas do canal comercial. Deve aparecer no chat como 'Comercial - Pixel'."
        type = "text"
        timestamp = [DateTimeOffset]::Now.ToUnixTimeSeconds()
        session = "comercial"
    }
} | ConvertTo-Json -Depth 3

try {
    $response_longa = Invoke-WebRequest -Uri "https://app.pixel12digital.com.br/webhook_sem_redirect/webhook.php" -Method POST -ContentType "application/json" -Body $webhook_data_longa -TimeoutSec 10
    $result_longa = $response_longa.Content | ConvertFrom-Json
    
    if ($result_longa.status -eq "ok") {
        Write-Host "   OK Mensagem longa processada!" -ForegroundColor Green
    }
} catch {
    Write-Host "   ERRO na mensagem longa: $($_.Exception.Message)" -ForegroundColor Red
}

Write-Host ""
Write-Host "=== TESTE FINAL CONCLUIDO ===" -ForegroundColor Green
Write-Host ""
Write-Host "RESULTADO ESPERADO:" -ForegroundColor Yellow
Write-Host "1. Acesse: https://app.pixel12digital.com.br/painel/chat.php" -ForegroundColor White
Write-Host "2. Procure por: 29.714.777 Charles Dietrich Wutzke" -ForegroundColor White
Write-Host "3. Deve haver mensagens de AMBOS os canais:" -ForegroundColor White
Write-Host "   - Mensagens do 'Financeiro'" -ForegroundColor Gray
Write-Host "   - Mensagens do 'Comercial - Pixel'" -ForegroundColor Gray
Write-Host "4. Contador de 'Nao Lidas' deve estar atualizado" -ForegroundColor White
Write-Host ""
Write-Host "SE FUNCIONAR: O sistema esta 100% operacional!" -ForegroundColor Green 