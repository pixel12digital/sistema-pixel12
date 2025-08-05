#!/bin/bash

echo "🧪 TESTE SIMPLES DE ENDPOINTS - VPS WHATSAPP"
echo "============================================"
echo ""

# Testar cada porta
for porta in 3000 3001; do
    echo "🔍 TESTANDO PORTA $porta"
    echo "------------------------"
    
    # Testar status
    echo "1. Status:"
    curl -s "http://localhost:$porta/status" | jq . 2>/dev/null || echo "   ❌ Falhou"
    
    # Testar webhook config
    echo "2. Webhook config:"
    curl -s "http://localhost:$porta/webhook/config" | jq . 2>/dev/null || echo "   ❌ Falhou"
    
    # Configurar webhook
    echo "3. Configurando webhook:"
    curl -s -X POST "http://localhost:$porta/webhook/config" \
        -H "Content-Type: application/json" \
        -d '{"url":"https://app.pixel12digital.com.br/painel/receber_mensagem_ana_local.php"}' | jq . 2>/dev/null || echo "   ❌ Falhou"
    
    echo ""
done

echo "✅ Teste concluído!" 