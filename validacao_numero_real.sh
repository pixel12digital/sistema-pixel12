#!/bin/bash

# 🎯 VALIDAÇÃO COM NÚMERO REAL - WHATSAPP API
# Executar no VPS: ssh root@212.85.11.238 && cd /var/whatsapp-api
# chmod +x validacao_numero_real.sh && ./validacao_numero_real.sh

echo "🎯 VALIDAÇÃO COM NÚMERO REAL - WHATSAPP API"
echo "==========================================="
echo "Número de teste: 554796164699"
echo "Data/Hora: $(date)"
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

# Número de teste
TEST_NUMBER="554796164699"

echo "🔍 ETAPA 1: VERIFICAR SESSÕES ATIVAS"
echo "===================================="

log_info "Verificando sessão na porta 3000 (default)..."
SESSIONS_3000=$(curl -s http://127.0.0.1:3000/sessions)
echo "Sessions 3000: $SESSIONS_3000"

log_info "Verificando sessão na porta 3001 (comercial)..."
SESSIONS_3001=$(curl -s http://127.0.0.1:3001/sessions)
echo "Sessions 3001: $SESSIONS_3001"

# Verificar se as sessões estão ativas
if echo "$SESSIONS_3000" | jq -e '.total' >/dev/null 2>&1; then
    TOTAL_3000=$(echo "$SESSIONS_3000" | jq -r '.total')
    if [ "$TOTAL_3000" -eq 1 ]; then
        log_success "✅ Porta 3000: total=1 (sessão ativa)"
    else
        log_error "❌ Porta 3000: total=$TOTAL_3000 (esperado: 1)"
    fi
else
    log_error "❌ Porta 3000: Erro ao verificar sessões"
fi

if echo "$SESSIONS_3001" | jq -e '.total' >/dev/null 2>&1; then
    TOTAL_3001=$(echo "$SESSIONS_3001" | jq -r '.total')
    if [ "$TOTAL_3001" -eq 1 ]; then
        log_success "✅ Porta 3001: total=1 (sessão ativa)"
    else
        log_error "❌ Porta 3001: total=$TOTAL_3001 (esperado: 1)"
    fi
else
    log_error "❌ Porta 3001: Erro ao verificar sessões"
fi

echo ""
echo "📤 ETAPA 2: TESTAR ENVIO DE MENSAGEM VIA API"
echo "============================================"

log_info "Enviando mensagem para porta 3000 (default)..."
SEND_3000=$(curl -s -X POST http://127.0.0.1:3000/send/text \
  -H "Content-Type: application/json" \
  -d "{\"sessionName\":\"default\",\"number\":\"$TEST_NUMBER\",\"message\":\"🧪 Teste API Default - $(date +%H:%M:%S)\"}")
echo "Envio 3000: $SEND_3000"

log_info "Enviando mensagem para porta 3001 (comercial)..."
SEND_3001=$(curl -s -X POST http://127.0.0.1:3001/send/text \
  -H "Content-Type: application/json" \
  -d "{\"sessionName\":\"comercial\",\"number\":\"$TEST_NUMBER\",\"message\":\"🧪 Teste API Comercial - $(date +%H:%M:%S)\"}")
echo "Envio 3001: $SEND_3001"

# Verificar se os envios foram bem-sucedidos
if echo "$SEND_3000" | jq -e '.success' >/dev/null 2>&1 && \
   echo "$SEND_3000" | jq -r '.success' | grep -q "true"; then
    log_success "✅ Envio porta 3000: SUCCESS"
else
    log_error "❌ Envio porta 3000: FALHA"
fi

if echo "$SEND_3001" | jq -e '.success' >/dev/null 2>&1 && \
   echo "$SEND_3001" | jq -r '.success' | grep -q "true"; then
    log_success "✅ Envio porta 3001: SUCCESS"
else
    log_error "❌ Envio porta 3001: FALHA"
fi

echo ""
echo "🔍 ETAPA 3: TESTAR VERIFICAÇÃO DE NÚMERO"
echo "========================================"

log_info "Verificando número na porta 3000 (default)..."
CHECK_3000=$(curl -s -X POST http://127.0.0.1:3000/check/number \
  -H "Content-Type: application/json" \
  -d "{\"sessionName\":\"default\",\"number\":\"$TEST_NUMBER\"}")
echo "Check 3000: $CHECK_3000"

log_info "Verificando número na porta 3001 (comercial)..."
CHECK_3001=$(curl -s -X POST http://127.0.0.1:3001/check/number \
  -H "Content-Type: application/json" \
  -d "{\"sessionName\":\"comercial\",\"number\":\"$TEST_NUMBER\"}")
echo "Check 3001: $CHECK_3001"

# Verificar se os números estão registrados
if echo "$CHECK_3000" | jq -e '.isRegistered' >/dev/null 2>&1 && \
   echo "$CHECK_3000" | jq -r '.isRegistered' | grep -q "true"; then
    log_success "✅ Check porta 3000: Número registrado"
else
    log_warning "⚠️ Check porta 3000: Número não registrado ou erro"
fi

if echo "$CHECK_3001" | jq -e '.isRegistered' >/dev/null 2>&1 && \
   echo "$CHECK_3001" | jq -r '.isRegistered' | grep -q "true"; then
    log_success "✅ Check porta 3001: Número registrado"
else
    log_warning "⚠️ Check porta 3001: Número não registrado ou erro"
fi

echo ""
echo "🔧 ETAPA 4: CONFIGURAR WEBHOOKS"
echo "==============================="

log_info "Configurando webhook para porta 3000..."
WEBHOOK_SET_3000=$(curl -s -X POST http://127.0.0.1:3000/webhook/config \
  -H "Content-Type: application/json" \
  -d '{"url":"http://212.85.11.238:8080/api/webhook.php"}')
echo "Webhook 3000: $WEBHOOK_SET_3000"

log_info "Configurando webhook para porta 3001..."
WEBHOOK_SET_3001=$(curl -s -X POST http://127.0.0.1:3001/webhook/config \
  -H "Content-Type: application/json" \
  -d '{"url":"http://212.85.11.238:8080/api/webhook.php"}')
echo "Webhook 3001: $WEBHOOK_SET_3001"

echo ""
echo "📊 ETAPA 5: VERIFICAR MONITORAMENTO"
echo "=================================="

log_info "Verificando logs de webhook..."
WEBHOOK_LOGS_3000=$(pm2 logs whatsapp-3000 --lines 10 --nostream 2>/dev/null | grep -i webhook | wc -l)
WEBHOOK_LOGS_3001=$(pm2 logs whatsapp-3001 --lines 10 --nostream 2>/dev/null | grep -i webhook | wc -l)

log_info "Logs de webhook porta 3000: $WEBHOOK_LOGS_3000 entradas"
log_info "Logs de webhook porta 3001: $WEBHOOK_LOGS_3001 entradas"

log_info "Verificando monitoramento automático..."
if [ -f "/var/whatsapp-api/logs/monitoramento_$(date +%Y%m%d).log" ]; then
    log_success "✅ Arquivo de monitoramento encontrado"
    log_info "Últimas 5 linhas do monitoramento:"
    tail -n 5 "/var/whatsapp-api/logs/monitoramento_$(date +%Y%m%d).log"
else
    log_warning "⚠️ Arquivo de monitoramento não encontrado"
fi

echo ""
echo "🌐 ETAPA 6: TESTAR ACESSO EXTERNO"
echo "================================="

log_info "Testando acesso externo à porta 3000..."
if curl -s --connect-timeout 10 http://212.85.11.238:3000/status >/dev/null 2>&1; then
    log_success "✅ API 3000 acessível externamente"
else
    log_error "❌ API 3000 não acessível externamente"
fi

log_info "Testando acesso externo à porta 3001..."
if curl -s --connect-timeout 10 http://212.85.11.238:3001/status >/dev/null 2>&1; then
    log_success "✅ API 3001 acessível externamente"
else
    log_error "❌ API 3001 não acessível externamente"
fi

echo ""
echo "📋 RESUMO DA VALIDAÇÃO"
echo "======================"

# Contar sucessos
SUCCESS_COUNT=0
TOTAL_TESTS=0

# Testar sessões
TOTAL_TESTS=$((TOTAL_TESTS + 2))
if [ "$TOTAL_3000" -eq 1 ] && [ "$TOTAL_3001" -eq 1 ]; then
    SUCCESS_COUNT=$((SUCCESS_COUNT + 2))
    log_success "✅ Sessões ativas: 2/2"
else
    log_error "❌ Sessões ativas: Falha"
fi

# Testar envios
TOTAL_TESTS=$((TOTAL_TESTS + 2))
if echo "$SEND_3000" | jq -e '.success' >/dev/null 2>&1 && \
   echo "$SEND_3000" | jq -r '.success' | grep -q "true" && \
   echo "$SEND_3001" | jq -e '.success' >/dev/null 2>&1 && \
   echo "$SEND_3001" | jq -r '.success' | grep -q "true"; then
    SUCCESS_COUNT=$((SUCCESS_COUNT + 2))
    log_success "✅ Envios: 2/2"
else
    log_error "❌ Envios: Falha"
fi

# Testar conectividade externa
TOTAL_TESTS=$((TOTAL_TESTS + 2))
if curl -s --connect-timeout 10 http://212.85.11.238:3000/status >/dev/null 2>&1 && \
   curl -s --connect-timeout 10 http://212.85.11.238:3001/status >/dev/null 2>&1; then
    SUCCESS_COUNT=$((SUCCESS_COUNT + 2))
    log_success "✅ Conectividade externa: 2/2"
else
    log_error "❌ Conectividade externa: Falha"
fi

echo ""
echo "📊 ESTATÍSTICAS FINAIS:"
echo "======================="
echo "Testes realizados: $TOTAL_TESTS"
echo "Sucessos: $SUCCESS_COUNT"
echo "Taxa de sucesso: $((SUCCESS_COUNT * 100 / TOTAL_TESTS))%"

if [ "$SUCCESS_COUNT" -eq "$TOTAL_TESTS" ]; then
    log_success "🎉 VALIDAÇÃO 100% SUCESSO!"
    echo ""
    echo "✅ Sistema completamente operacional!"
    echo "✅ Número $TEST_NUMBER validado com sucesso!"
    echo "✅ Ambos os canais funcionando perfeitamente!"
    echo ""
    echo "🎯 PRÓXIMOS PASSOS:"
    echo "1. Teste o painel administrativo: http://212.85.11.238:8080/painel/comunicacao.php"
    echo "2. Envie mensagens do WhatsApp para testar recebimento"
    echo "3. Monitore os logs: pm2 logs whatsapp-3000 --lines 20"
    echo "4. Configure monitoramento automático: crontab -e"
else
    log_error "❌ ALGUNS TESTES FALHARAM!"
    echo ""
    echo "🔧 Ações necessárias:"
    echo "1. Verifique os logs: pm2 logs whatsapp-3000 --lines 50"
    echo "2. Reinicie os processos: pm2 restart all"
    echo "3. Execute novamente: ./validacao_numero_real.sh"
fi

echo ""
echo "📚 COMANDOS PARA TESTAR RECEBIMENTO:"
echo "===================================="
echo "# Envie uma mensagem do WhatsApp para testar webhook"
echo "# Depois verifique os logs:"
echo "pm2 logs whatsapp-3000 --lines 20 | grep webhook"
echo "pm2 logs whatsapp-3001 --lines 20 | grep webhook"

echo ""
log_success "🎯 VALIDAÇÃO COM NÚMERO REAL FINALIZADA!" 