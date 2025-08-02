# Teste de Recebimento - Chat do Sistema
# Cliente: 29.714.777 Charles Dietrich Wutzke (ID: 4296)
# Numero: 554796164699

Write-Host "=== TESTE DE RECEBIMENTO - CHAT DO SISTEMA ===" -ForegroundColor Green
Write-Host "Cliente: 29.714.777 Charles Dietrich Wutzke" -ForegroundColor Yellow
Write-Host "Numero: 554796164699" -ForegroundColor Yellow
Write-Host ""

# Funcao para testar recebimento por canal
function Test-RecebimentoCanal {
    param(
        [string]$CanalNome,
        [string]$SessionName,
        [string]$MensagemTeste
    )
    
    Write-Host "--- Testando Recebimento Canal $CanalNome ---" -ForegroundColor Cyan
    
    $numero_teste = "554796164699"
    $timestamp = [DateTimeOffset]::Now.ToUnixTimeSeconds()
    
    # Simular mensagem recebida do WhatsApp
    $webhook_data = @{
        event = "onmessage"
        data = @{
            from = $numero_teste
            text = $MensagemTeste
            type = "text"
            timestamp = $timestamp
            session = $SessionName
        }
    } | ConvertTo-Json -Depth 3
    
    Write-Host "Enviando webhook para o sistema..." -ForegroundColor White
    Write-Host "Mensagem: $MensagemTeste" -ForegroundColor Gray
    
    try {
        $webhook_response = Invoke-WebRequest -Uri "https://app.pixel12digital.com.br/webhook_sem_redirect/webhook.php" -Method POST -ContentType "application/json" -Body $webhook_data -TimeoutSec 10
        $webhook_result = $webhook_response.Content | ConvertFrom-Json
        
        if ($webhook_result.status -eq "ok") {
            Write-Host "   OK Webhook processado com sucesso!" -ForegroundColor Green
            Write-Host "   Resposta: $($webhook_result.message)" -ForegroundColor Gray
            Write-Host "   Ambiente: $($webhook_result.ambiente)" -ForegroundColor Gray
            Write-Host "   Timestamp: $($webhook_result.timestamp)" -ForegroundColor Gray
        } else {
            Write-Host "   ERRO no webhook: $($webhook_result)" -ForegroundColor Red
        }
    } catch {
        Write-Host "   ERRO ao enviar webhook: $($_.Exception.Message)" -ForegroundColor Red
    }
    
    Write-Host ""
}

# Teste 1: Recebimento Canal Financeiro (3000)
Test-RecebimentoCanal -CanalNome "Financeiro" -SessionName "default" -MensagemTeste "Teste de recebimento Financeiro - $(Get-Date -Format 'HH:mm:ss') - Preciso de ajuda com pagamento"

# Aguardar 3 segundos
Start-Sleep -Seconds 3

# Teste 2: Recebimento Canal Comercial (3001)
Test-RecebimentoCanal -CanalNome "Comercial" -SessionName "comercial" -MensagemTeste "Teste de recebimento Comercial - $(Get-Date -Format 'HH:mm:ss') - Gostaria de informações sobre produtos"

# Aguardar 3 segundos
Start-Sleep -Seconds 3

# Teste 3: Mensagem com emoji e formatação
Test-RecebimentoCanal -CanalNome "Financeiro" -SessionName "default" -MensagemTeste "Teste com emoji - $(Get-Date -Format 'HH:mm:ss') - Estou com duvida sobre minha fatura 😊"

# Aguardar 3 segundos
Start-Sleep -Seconds 3

# Teste 4: Mensagem longa
Test-RecebimentoCanal -CanalNome "Comercial" -SessionName "comercial" -MensagemTeste "Teste mensagem longa - $(Get-Date -Format 'HH:mm:ss') - Olá, gostaria de saber mais sobre os servicos oferecidos pela empresa. Tenho interesse em contratar e preciso de mais detalhes sobre precos e prazos de entrega."

Write-Host "=== TESTE DE RECEBIMENTO CONCLUIDO ===" -ForegroundColor Green
Write-Host ""
Write-Host "INSTRUCOES PARA VERIFICACAO:" -ForegroundColor Yellow
Write-Host "1. Acesse: https://app.pixel12digital.com.br/painel/chat.php" -ForegroundColor White
Write-Host "2. Procure por: 29.714.777 Charles Dietrich Wutzke" -ForegroundColor White
Write-Host "3. Verifique se as mensagens aparecem na conversa" -ForegroundColor White
Write-Host "4. Confirme se o contador de 'Nao Lidas' foi atualizado" -ForegroundColor White
Write-Host ""
Write-Host "Mensagens enviadas:" -ForegroundColor Cyan
Write-Host "- Teste de recebimento Financeiro (Canal 3000)" -ForegroundColor Gray
Write-Host "- Teste de recebimento Comercial (Canal 3001)" -ForegroundColor Gray
Write-Host "- Teste com emoji (Canal 3000)" -ForegroundColor Gray
Write-Host "- Teste mensagem longa (Canal 3001)" -ForegroundColor Gray 