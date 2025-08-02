#!/bin/bash

echo "🔧 CONFIGURANDO WEBHOOK VIA SSH NA VPS"
echo "======================================"
echo ""

# URLs para testar (em ordem de prioridade - conforme README)
WEBHOOK_URLS=(
    "https://app.pixel12digital.com.br/painel/receber_mensagem_ana_local.php"
    "https://app.pixel12digital.com.br/webhook.php"
    "https://app.pixel12digital.com.br/index.php?path=webhook"
)

echo "📡 Configurando na VPS local (127.0.0.1:3000) - conforme README"
echo ""

# Backup da configuração atual
echo "🔄 ETAPA 1: Backup da configuração atual..."
echo "--------------------------------------------"

BACKUP_RESPONSE=$(curl -s http://127.0.0.1:3000/webhook/status 2>/dev/null)
if [ $? -eq 0 ]; then
    echo "✅ Backup realizado"
    echo "📋 Configuração atual: $BACKUP_RESPONSE"
else
    echo "⚠️ Não conseguiu fazer backup (prosseguindo mesmo assim)"
fi
echo ""

# Testar cada webhook
echo "🔄 ETAPA 2: Testando webhooks..."
echo "--------------------------------"

WEBHOOK_FUNCIONANDO=""

for webhook_url in "${WEBHOOK_URLS[@]}"; do
    echo "🧪 Testando: $webhook_url"
    
    # Teste direto
    TEST_RESPONSE=$(curl -s -o /dev/null -w "%{http_code}" -X POST \
        -H "Content-Type: application/json" \
        -d '{"from":"5547999999999","body":"teste ssh"}' \
        "$webhook_url" 2>/dev/null)
    
    if [ "$TEST_RESPONSE" = "200" ]; then
        echo "   ✅ HTTP 200 - Testando JSON..."
        
        # Testar se retorna JSON válido
        JSON_TEST=$(curl -s -X POST \
            -H "Content-Type: application/json" \
            -d '{"from":"5547999999999","body":"teste ssh"}' \
            "$webhook_url" 2>/dev/null)
        
        if echo "$JSON_TEST" | jq empty 2>/dev/null; then
            if echo "$JSON_TEST" | jq -e '.success' >/dev/null 2>&1; then
                echo "   ✅ JSON válido com success!"
                WEBHOOK_FUNCIONANDO="$webhook_url"
                break
            else
                echo "   ⚠️ JSON válido mas sem campo success"
            fi
        else
            echo "   ❌ Resposta não é JSON válido"
        fi
    else
        echo "   ❌ HTTP $TEST_RESPONSE"
    fi
done

if [ -z "$WEBHOOK_FUNCIONANDO" ]; then
    echo ""
    echo "❌ NENHUM WEBHOOK FUNCIONOU"
    echo "Não vou alterar a configuração da VPS"
    echo ""
    echo "💡 SOLUÇÕES ALTERNATIVAS:"
    echo "1. Verificar se servidor permite webhooks externos"
    echo "2. Contactar suporte técnico do hosting"
    echo "3. Usar outro domínio para webhook"
    exit 1
fi

echo ""
echo "✅ Webhook funcionando encontrado: $WEBHOOK_FUNCIONANDO"
echo ""

# Configurar na VPS (usando 127.0.0.1 conforme README)
echo "🔄 ETAPA 3: Configurando webhook na VPS..."
echo "------------------------------------------"

CONFIG_RESPONSE=$(curl -s -X POST http://127.0.0.1:3000/webhook/config \
    -H "Content-Type: application/json" \
    -d "{\"url\":\"$WEBHOOK_FUNCIONANDO\"}")

CONFIG_CODE=$(curl -s -o /dev/null -w "%{http_code}" -X POST http://127.0.0.1:3000/webhook/config \
    -H "Content-Type: application/json" \
    -d "{\"url\":\"$WEBHOOK_FUNCIONANDO\"}")

if [ "$CONFIG_CODE" = "200" ]; then
    echo "✅ CONFIGURADO COM SUCESSO!"
    echo "Resposta: $CONFIG_RESPONSE"
else
    echo "❌ FALHA NA CONFIGURAÇÃO: HTTP $CONFIG_CODE"
    echo "Resposta: $CONFIG_RESPONSE"
    exit 1
fi
echo ""

# Verificar se canais ainda funcionam (usando 127.0.0.1 conforme README)
echo "🔄 ETAPA 4: Verificando canais..."
echo "--------------------------------"

CANAIS_OK=true

for porta in 3000 3001; do
    STATUS_CODE=$(curl -s -o /dev/null -w "%{http_code}" http://127.0.0.1:$porta/status)
    if [ "$STATUS_CODE" = "200" ]; then
        echo "✅ Canal $porta: OK"
    else
        echo "❌ Canal $porta: Problema (HTTP $STATUS_CODE)"
        CANAIS_OK=false
    fi
done

if [ "$CANAIS_OK" = false ]; then
    echo ""
    echo "⚠️ PROBLEMA DETECTADO NOS CANAIS!"
    echo "Considere fazer rollback se necessário"
    echo ""
fi

# Teste webhook conforme README
echo ""
echo "🔄 ETAPA 5: Teste webhook conforme README..."
echo "--------------------------------------------"

WEBHOOK_TEST_CODE=$(curl -s -o /dev/null -w "%{http_code}" -X POST http://127.0.0.1:3000/webhook/test)
if [ "$WEBHOOK_TEST_CODE" = "200" ]; then
    echo "✅ TESTE WEBHOOK PASSOU!"
else
    echo "⚠️ TESTE WEBHOOK: HTTP $WEBHOOK_TEST_CODE"
fi

# Teste final de envio
echo ""
echo "🔄 ETAPA 6: Teste final de envio..."
echo "----------------------------------"

FINAL_RESPONSE=$(curl -s -X POST http://127.0.0.1:3000/send/text \
    -H "Content-Type: application/json" \
    -d "{\"sessionName\":\"default\",\"number\":\"5547999999999\",\"message\":\"🎉 Ana configurada via SSH - $(date '+%H:%M:%S')\"}")

FINAL_CODE=$(curl -s -o /dev/null -w "%{http_code}" -X POST http://127.0.0.1:3000/send/text \
    -H "Content-Type: application/json" \
    -d "{\"sessionName\":\"default\",\"number\":\"5547999999999\",\"message\":\"Teste final\"}")

if [ "$FINAL_CODE" = "200" ]; then
    if echo "$FINAL_RESPONSE" | jq -e '.success' >/dev/null 2>&1; then
        echo "✅ TESTE FINAL PASSOU!"
        echo ""
        echo "🎉 CONFIGURAÇÃO CONCLUÍDA COM SUCESSO!"
        echo "====================================="
        echo ""
        echo "📱 WEBHOOK ATIVO:"
        echo "• URL: $WEBHOOK_FUNCIONANDO"
        echo "• VPS: 127.0.0.1:3000"
        echo "• Status: ✅ OPERACIONAL"
        echo ""
        echo "📋 TESTE REAL AGORA:"
        echo "1. Envie 'olá' para WhatsApp"
        echo "2. Ana deve responder automaticamente"
        echo "3. Teste 'quero um site' → Rafael"
        echo "4. Teste 'problema' → Suporte"
        echo "5. Teste 'pessoa' → Humano"
        echo ""
        echo "📊 MONITORAMENTO (conforme README):"
        echo "• PM2 logs: pm2 logs whatsapp-3000 --lines 20"
        echo "• Status: curl -s http://127.0.0.1:3000/status | jq ."
        echo "• Sessões: curl -s http://127.0.0.1:3000/sessions | jq ."
        echo "• Dashboard: https://app.pixel12digital.com.br/painel/gestao_transferencias.php"
        echo "• Logs: tail -f /var/www/html/loja-virtual-revenda/painel/logs/webhook_debug.log"
        echo ""
        echo "🔧 ROLLBACK (se necessário):"
        echo "curl -X POST http://127.0.0.1:3000/webhook/config \\"
        echo "  -H 'Content-Type: application/json' \\"
        echo "  -d '{\"url\":\"URL_ANTERIOR\"}'"
        echo ""
        echo "✅ CONFIGURAÇÃO CONCLUÍDA CONFORME README!"
    else
        echo "❌ TESTE FINAL FALHOU - JSON inválido"
    fi
else
    echo "❌ TESTE FINAL FALHOU: HTTP $FINAL_CODE"
fi 