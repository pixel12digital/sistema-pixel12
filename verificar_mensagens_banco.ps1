# Verificar Mensagens no Banco de Dados
# Cliente: 29.714.777 Charles Dietrich Wutzke (ID: 4296)

Write-Host "=== VERIFICACAO DE MENSAGENS NO BANCO ===" -ForegroundColor Green
Write-Host "Cliente: 29.714.777 Charles Dietrich Wutzke" -ForegroundColor Yellow
Write-Host ""

# Funcao para consultar mensagens do cliente
function Get-MensagensCliente {
    param(
        [string]$ClienteID = "4296"
    )
    
    Write-Host "Consultando mensagens do cliente ID: $ClienteID" -ForegroundColor Cyan
    
    try {
        # URL da API para consultar mensagens
        $api_url = "https://app.pixel12digital.com.br/api/historico_mensagens.php"
        $params = @{
            cliente_id = $ClienteID
            limit = 10
        }
        
        $query_string = ($params.GetEnumerator() | ForEach-Object { "$($_.Key)=$($_.Value)" }) -join "&"
        $full_url = "$api_url`?$query_string"
        
        Write-Host "Consultando: $full_url" -ForegroundColor Gray
        
        $response = Invoke-WebRequest -Uri $full_url -Method GET -TimeoutSec 10
        $result = $response.Content | ConvertFrom-Json
        
        if ($result.success) {
            Write-Host "   OK Consulta realizada com sucesso!" -ForegroundColor Green
            Write-Host "   Total de mensagens: $($result.total)" -ForegroundColor White
            
            if ($result.mensagens) {
                Write-Host "   Ultimas mensagens:" -ForegroundColor Yellow
                foreach ($msg in $result.mensagens) {
                    $direcao = if ($msg.direcao -eq "recebido") { "📥" } else { "📤" }
                    $canal = $msg.canal_nome ?? "N/A"
                    Write-Host "   $direcao [$canal] $($msg.data_hora): $($msg.mensagem)" -ForegroundColor Gray
                }
            } else {
                Write-Host "   Nenhuma mensagem encontrada" -ForegroundColor Yellow
            }
        } else {
            Write-Host "   ERRO na consulta: $($result.error)" -ForegroundColor Red
        }
    } catch {
        Write-Host "   ERRO ao consultar mensagens: $($_.Exception.Message)" -ForegroundColor Red
    }
    
    Write-Host ""
}

# Funcao para verificar contador de nao lidas
function Get-ContadorNaoLidas {
    Write-Host "Verificando contador de nao lidas..." -ForegroundColor Cyan
    
    try {
        $api_url = "https://app.pixel12digital.com.br/api/status_canais.php"
        $response = Invoke-WebRequest -Uri $api_url -Method GET -TimeoutSec 10
        $result = $response.Content | ConvertFrom-Json
        
        if ($result.success) {
            Write-Host "   OK Status dos canais consultado!" -ForegroundColor Green
            Write-Host "   Total de conversas nao lidas: $($result.total_nao_lidas)" -ForegroundColor White
            
            if ($result.conversas_nao_lidas) {
                Write-Host "   Conversas com mensagens nao lidas:" -ForegroundColor Yellow
                foreach ($conv in $result.conversas_nao_lidas) {
                    Write-Host "   📱 $($conv.cliente_nome) - $($conv.ultima_mensagem)" -ForegroundColor Gray
                }
            }
        } else {
            Write-Host "   ERRO na consulta: $($result.error)" -ForegroundColor Red
        }
    } catch {
        Write-Host "   ERRO ao consultar status: $($_.Exception.Message)" -ForegroundColor Red
    }
    
    Write-Host ""
}

# Executar verificacoes
Get-MensagensCliente -ClienteID "4296"
Get-ContadorNaoLidas

Write-Host "=== VERIFICACAO CONCLUIDA ===" -ForegroundColor Green
Write-Host ""
Write-Host "PROXIMOS PASSOS:" -ForegroundColor Yellow
Write-Host "1. Verifique o painel em: https://app.pixel12digital.com.br/painel/chat.php" -ForegroundColor White
Write-Host "2. Confirme se as mensagens aparecem na conversa do cliente" -ForegroundColor White
Write-Host "3. Verifique se o contador de 'Nao Lidas' foi atualizado" -ForegroundColor White
Write-Host "4. Teste enviando uma mensagem real do WhatsApp para os canais" -ForegroundColor White 