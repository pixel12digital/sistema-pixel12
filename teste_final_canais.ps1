# Teste Final Completo - Canais WhatsApp 3000 e 3001
# Numero de teste: 554796164699

Write-Host "=== TESTE FINAL CANAIS WHATSAPP ===" -ForegroundColor Green
Write-Host "Numero de teste: 554796164699" -ForegroundColor Yellow
Write-Host ""

# Funcao para testar canal com formato correto
function Test-CanalFinal {
    param(
        [string]$Porta,
        [string]$SessionName,
        [string]$CanalNome
    )
    
    Write-Host "--- Testando Canal $CanalNome (Porta $Porta) ---" -ForegroundColor Cyan
    
    $vps_url = "http://212.85.11.238:$Porta"
    $numero_teste = "554796164699"
    
    # 1. Verificar status do canal
    Write-Host "1. Verificando status do canal..." -ForegroundColor White
    try {
        $status_response = Invoke-WebRequest -Uri "$vps_url/status" -Method GET -TimeoutSec 10
        $status_data = $status_response.Content | ConvertFrom-Json
        
        if ($status_data.success) {
            Write-Host "   OK Status: $($status_data.message)" -ForegroundColor Green
            
            if ($status_data.clients_status.$SessionName) {
                $session_status = $status_data.clients_status.$SessionName.status
                Write-Host "   OK Sessao $SessionName`: $session_status" -ForegroundColor Green
            } else {
                Write-Host "   ATENCAO Sessao $SessionName nao encontrada" -ForegroundColor Yellow
            }
        } else {
            Write-Host "   ERRO no status: $($status_data.error)" -ForegroundColor Red
        }
    } catch {
        Write-Host "   ERRO ao verificar status: $($_.Exception.Message)" -ForegroundColor Red
    }
    
    # 2. Enviar mensagem de teste (formato correto)
    Write-Host "2. Enviando mensagem de teste..." -ForegroundColor White
    try {
        $mensagem_teste = "Teste final canal $CanalNome - $(Get-Date -Format 'HH:mm:ss')"
        $send_data = @{
            sessionName = $SessionName
            number = $numero_teste
            message = $mensagem_teste
        } | ConvertTo-Json
        
        $send_response = Invoke-WebRequest -Uri "$vps_url/send/text" -Method POST -ContentType "application/json" -Body $send_data -TimeoutSec 15
        $send_result = $send_response.Content | ConvertFrom-Json
        
        if ($send_result.success) {
            Write-Host "   OK Mensagem enviada com sucesso!" -ForegroundColor Green
            Write-Host "   Sessao: $($send_result.session)" -ForegroundColor Gray
            Write-Host "   Para: $($send_result.to)" -ForegroundColor Gray
            Write-Host "   Timestamp: $($send_result.timestamp)" -ForegroundColor Gray
        } else {
            Write-Host "   ERRO ao enviar: $($send_result.error)" -ForegroundColor Red
        }
    } catch {
        Write-Host "   ERRO ao enviar mensagem: $($_.Exception.Message)" -ForegroundColor Red
    }
    
    # 3. Simular recebimento de mensagem
    Write-Host "3. Simulando recebimento de mensagem..." -ForegroundColor White
    try {
        $webhook_data = @{
            event = "onmessage"
            data = @{
                from = $numero_teste
                text = "Resposta teste canal $CanalNome - $(Get-Date -Format 'HH:mm:ss')"
                type = "text"
                timestamp = [DateTimeOffset]::Now.ToUnixTimeSeconds()
                session = $SessionName
            }
        } | ConvertTo-Json -Depth 3
        
        $webhook_response = Invoke-WebRequest -Uri "https://app.pixel12digital.com.br/webhook_sem_redirect/webhook.php" -Method POST -ContentType "application/json" -Body $webhook_data -TimeoutSec 10
        $webhook_result = $webhook_response.Content | ConvertFrom-Json
        
        if ($webhook_result.status -eq "ok") {
            Write-Host "   OK Webhook processado com sucesso!" -ForegroundColor Green
            Write-Host "   Resposta: $($webhook_result.message)" -ForegroundColor Gray
        } else {
            Write-Host "   ERRO no webhook: $($webhook_result)" -ForegroundColor Red
        }
    } catch {
        Write-Host "   ERRO ao simular recebimento: $($_.Exception.Message)" -ForegroundColor Red
    }
    
    Write-Host ""
}

# Testar Canal 3000 (Financeiro)
Test-CanalFinal -Porta "3000" -SessionName "default" -CanalNome "Financeiro"

# Testar Canal 3001 (Comercial)
Test-CanalFinal -Porta "3001" -SessionName "comercial" -CanalNome "Comercial"

Write-Host "=== TESTE FINAL CONCLUIDO ===" -ForegroundColor Green
Write-Host "Verifique o painel em https://app.pixel12digital.com.br/painel/chat.php" -ForegroundColor Yellow
Write-Host "Verifique seu WhatsApp para as mensagens recebidas." -ForegroundColor Yellow 