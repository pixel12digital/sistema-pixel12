#!/bin/bash

# 🔧 CONFIGURAÇÃO DE WEBHOOKS - WHATSAPP API
# Executar no VPS: ssh root@212.85.11.238 && cd /var/whatsapp-api
# chmod +x configurar_webhooks.sh && ./configurar_webhooks.sh

echo "🔧 CONFIGURAÇÃO DE WEBHOOKS - WHATSAPP API"
echo "=========================================="
echo ""

# Cores
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

# Função para log
log_info() { echo -e "${BLUE}[INFO]${NC} $1"; }
log_success() { echo -e "${GREEN}[SUCCESS]${NC} $1"; }
log_warning() { echo -e "${YELLOW}[WARNING]${NC} $1"; }
log_error() { echo -e "${RED}[ERROR]${NC} $1"; }

echo "🎯 ETAPA 1: VERIFICAR STATUS ATUAL DOS WEBHOOKS"
echo "=============================================="

log_info "Verificando configuração atual do webhook na porta 3000..."
WEBHOOK_CONFIG_3000=$(curl -s http://127.0.0.1:3000/webhook/config)
echo "Webhook 3000: $WEBHOOK_CONFIG_3000"

log_info "Verificando configuração atual do webhook na porta 3001..."
WEBHOOK_CONFIG_3001=$(curl -s http://127.0.0.1:3001/webhook/config)
echo "Webhook 3001: $WEBHOOK_CONFIG_3001"

echo ""
echo "🔧 ETAPA 2: CONFIGURAR WEBHOOKS"
echo "==============================="

# URL do webhook (ajuste conforme necessário)
WEBHOOK_URL="http://212.85.11.238:8080/api/webhook.php"

log_info "Configurando webhook para porta 3000 (default)..."
WEBHOOK_SET_3000=$(curl -s -X POST http://127.0.0.1:3000/webhook/config \
  -H "Content-Type: application/json" \
  -d "{\"url\":\"$WEBHOOK_URL\"}")
echo "Configuração 3000: $WEBHOOK_SET_3000"

log_info "Configurando webhook para porta 3001 (comercial)..."
WEBHOOK_SET_3001=$(curl -s -X POST http://127.0.0.1:3001/webhook/config \
  -H "Content-Type: application/json" \
  -d "{\"url\":\"$WEBHOOK_URL\"}")
echo "Configuração 3001: $WEBHOOK_SET_3001"

echo ""
echo "🧪 ETAPA 3: TESTAR WEBHOOKS"
echo "==========================="

log_info "Testando webhook da porta 3000..."
WEBHOOK_TEST_3000=$(curl -s -X POST http://127.0.0.1:3000/webhook/test)
echo "Teste 3000: $WEBHOOK_TEST_3000"

log_info "Testando webhook da porta 3001..."
WEBHOOK_TEST_3001=$(curl -s -X POST http://127.0.0.1:3001/webhook/test)
echo "Teste 3001: $WEBHOOK_TEST_3001"

echo ""
echo "🔍 ETAPA 4: VERIFICAR CONFIGURAÇÃO FINAL"
echo "======================================="

log_info "Verificando configuração final do webhook na porta 3000..."
WEBHOOK_FINAL_3000=$(curl -s http://127.0.0.1:3000/webhook/config)
echo "Final 3000: $WEBHOOK_FINAL_3000"

log_info "Verificando configuração final do webhook na porta 3001..."
WEBHOOK_FINAL_3001=$(curl -s http://127.0.0.1:3001/webhook/config)
echo "Final 3001: $WEBHOOK_FINAL_3001"

echo ""
echo "📋 RESUMO DA CONFIGURAÇÃO"
echo "========================"

# Verificar se os webhooks foram configurados corretamente
if echo "$WEBHOOK_SET_3000" | jq -e '.success' >/dev/null 2>&1 && \
   echo "$WEBHOOK_SET_3000" | jq -r '.success' | grep -q "true"; then
    log_success "✅ Webhook 3000 configurado com sucesso"
else
    log_error "❌ Falha ao configurar webhook 3000"
fi

if echo "$WEBHOOK_SET_3001" | jq -e '.success' >/dev/null 2>&1 && \
   echo "$WEBHOOK_SET_3001" | jq -r '.success' | grep -q "true"; then
    log_success "✅ Webhook 3001 configurado com sucesso"
else
    log_error "❌ Falha ao configurar webhook 3001"
fi

# Verificar se os testes passaram
if echo "$WEBHOOK_TEST_3000" | jq -e '.success' >/dev/null 2>&1 && \
   echo "$WEBHOOK_TEST_3000" | jq -r '.success' | grep -q "true"; then
    log_success "✅ Teste webhook 3000 passou"
else
    log_warning "⚠️ Teste webhook 3000 falhou (pode ser normal se o endpoint não existir)"
fi

if echo "$WEBHOOK_TEST_3001" | jq -e '.success' >/dev/null 2>&1 && \
   echo "$WEBHOOK_TEST_3001" | jq -r '.success' | grep -q "true"; then
    log_success "✅ Teste webhook 3001 passou"
else
    log_warning "⚠️ Teste webhook 3001 falhou (pode ser normal se o endpoint não existir)"
fi

echo ""
echo "🎯 PRÓXIMOS PASSOS:"
echo "=================="

echo "1. Verifique se o endpoint $WEBHOOK_URL existe e está funcionando"
echo "2. Envie uma mensagem de teste para os números registrados"
echo "3. Verifique se o webhook está recebendo as mensagens"
echo "4. Monitore os logs: pm2 logs whatsapp-3000 --lines 20"

echo ""
echo "📚 COMANDOS PARA TESTAR RECEBIMENTO:"
echo "===================================="

echo "# Enviar mensagem de teste"
echo "curl -X POST http://127.0.0.1:3000/send/text \\"
echo "  -H \"Content-Type: application/json\" \\"
echo "  -d '{\"sessionName\":\"default\",\"number\":\"5511999999999\",\"message\":\"Teste webhook\"}'"

echo ""
echo "# Verificar logs de webhook"
echo "pm2 logs whatsapp-3000 --lines 20 | grep webhook"
echo "pm2 logs whatsapp-3001 --lines 20 | grep webhook"

echo ""
log_success "🔧 CONFIGURAÇÃO DE WEBHOOKS FINALIZADA!" 