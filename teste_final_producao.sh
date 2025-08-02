#!/bin/bash

# 🧪 TESTE FINAL DE PRODUÇÃO - WHATSAPP API
# Executar no VPS: ssh root@212.85.11.238 && cd /var/whatsapp-api
# chmod +x teste_final_producao.sh && ./teste_final_producao.sh

echo "🧪 TESTE FINAL DE PRODUÇÃO - WHATSAPP API"
echo "========================================="
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

echo "🎯 ETAPA 1: VALIDAÇÃO FINAL DOS ENDPOINTS"
echo "========================================"

log_info "Verificando sessões ativas..."
SESSIONS_3000=$(curl -s http://127.0.0.1:3000/sessions)
SESSIONS_3001=$(curl -s http://127.0.0.1:3001/sessions)

echo "Sessions 3000: $SESSIONS_3000"
echo "Sessions 3001: $SESSIONS_3001"

# Verificar se as sessões estão conectadas
if echo "$SESSIONS_3000" | jq -e '.sessions[0].status.status' >/dev/null 2>&1; then
    STATUS_3000=$(echo "$SESSIONS_3000" | jq -r '.sessions[0].status.status')
    if [ "$STATUS_3000" = "connected" ]; then
        log_success "Porta 3000: Sessão conectada ✅"
    else
        log_error "Porta 3000: Sessão não conectada (status: $STATUS_3000) ❌"
    fi
else
    log_error "Porta 3000: Erro ao verificar status ❌"
fi

if echo "$SESSIONS_3001" | jq -e '.sessions[0].status.status' >/dev/null 2>&1; then
    STATUS_3001=$(echo "$SESSIONS_3001" | jq -r '.sessions[0].status.status')
    if [ "$STATUS_3001" = "connected" ]; then
        log_success "Porta 3001: Sessão conectada ✅"
    else
        log_error "Porta 3001: Sessão não conectada (status: $STATUS_3001) ❌"
    fi
else
    log_error "Porta 3001: Erro ao verificar status ❌"
fi

echo ""
echo "📤 ETAPA 2: TESTE DE ENVIO COM NÚMEROS REAIS"
echo "============================================="

# Testar com números reais (substitua pelos números de teste)
NUMBER_TEST="5511999999999"  # Substitua por um número real para teste

log_info "Testando envio para número real na porta 3000..."
SEND_REAL_3000=$(curl -s -X POST http://127.0.0.1:3000/send/text \
  -H "Content-Type: application/json" \
  -d "{\"sessionName\":\"default\",\"number\":\"$NUMBER_TEST\",\"message\":\"🧪 Teste Final - Canal Default - $(date +%H:%M:%S)\"}")
echo "Envio real 3000: $SEND_REAL_3000"

log_info "Testando envio para número real na porta 3001..."
SEND_REAL_3001=$(curl -s -X POST http://127.0.0.1:3001/send/text \
  -H "Content-Type: application/json" \
  -d "{\"sessionName\":\"comercial\",\"number\":\"$NUMBER_TEST\",\"message\":\"🧪 Teste Final - Canal Comercial - $(date +%H:%M:%S)\"}")
echo "Envio real 3001: $SEND_REAL_3001"

echo ""
echo "🔍 ETAPA 3: TESTE DE VERIFICAÇÃO DE NÚMEROS REAIS"
echo "================================================"

log_info "Verificando número real na porta 3000..."
CHECK_REAL_3000=$(curl -s -X POST http://127.0.0.1:3000/check/number \
  -H "Content-Type: application/json" \
  -d "{\"sessionName\":\"default\",\"number\":\"$NUMBER_TEST\"}")
echo "Check real 3000: $CHECK_REAL_3000"

log_info "Verificando número real na porta 3001..."
CHECK_REAL_3001=$(curl -s -X POST http://127.0.0.1:3001/check/number \
  -H "Content-Type: application/json" \
  -d "{\"sessionName\":\"comercial\",\"number\":\"$NUMBER_TEST\"}")
echo "Check real 3001: $CHECK_REAL_3001"

echo ""
echo "🌐 ETAPA 4: TESTE DE ACESSO EXTERNO"
echo "==================================="

log_info "Testando acesso externo à porta 3000..."
EXTERNAL_3000=$(curl -s http://212.85.11.238:3000/status)
if [ $? -eq 0 ]; then
    log_success "Porta 3000: Acessível externamente ✅"
else
    log_error "Porta 3000: Não acessível externamente ❌"
fi

log_info "Testando acesso externo à porta 3001..."
EXTERNAL_3001=$(curl -s http://212.85.11.238:3001/status)
if [ $? -eq 0 ]; then
    log_success "Porta 3001: Acessível externamente ✅"
else
    log_error "Porta 3001: Não acessível externamente ❌"
fi

echo ""
echo "🔧 ETAPA 5: TESTE DE WEBHOOK"
echo "============================"

log_info "Verificando configuração atual do webhook..."
WEBHOOK_CONFIG=$(curl -s http://127.0.0.1:3000/webhook/config)
echo "Webhook config: $WEBHOOK_CONFIG"

log_info "Testando webhook..."
WEBHOOK_TEST=$(curl -s -X POST http://127.0.0.1:3000/webhook/test)
echo "Webhook test: $WEBHOOK_TEST"

echo ""
echo "📊 ETAPA 6: VERIFICAÇÃO DE RECURSOS"
echo "=================================="

log_info "Verificando uso de memória dos processos..."
pm2 status

log_info "Verificando logs de erro..."
ERROR_LOGS_3000=$(pm2 logs whatsapp-3000 --lines 10 --nostream 2>/dev/null | grep -i error | wc -l)
ERROR_LOGS_3001=$(pm2 logs whatsapp-3001 --lines 10 --nostream 2>/dev/null | grep -i error | wc -l)

if [ "$ERROR_LOGS_3000" -eq 0 ]; then
    log_success "Porta 3000: Sem erros nos logs ✅"
else
    log_warning "Porta 3000: $ERROR_LOGS_3000 erros encontrados ⚠️"
fi

if [ "$ERROR_LOGS_3001" -eq 0 ]; then
    log_success "Porta 3001: Sem erros nos logs ✅"
else
    log_warning "Porta 3001: $ERROR_LOGS_3001 erros encontrados ⚠️"
fi

echo ""
echo "🎯 ETAPA 7: VALIDAÇÃO DO PAINEL ADMINISTRATIVO"
echo "============================================="

log_info "URLs do painel administrativo:"
echo "   - Painel principal: http://212.85.11.238:8080/painel/"
echo "   - Comunicação: http://212.85.11.238:8080/painel/comunicacao.php"
echo "   - Status: http://212.85.11.238:8080/painel/status.php"

echo ""
echo "📋 CHECKLIST DE VALIDAÇÃO DO PAINEL:"
echo "===================================="
echo "1. Acesse http://212.85.11.238:8080/painel/comunicacao.php"
echo "2. Clique em 'Atualizar Status'"
echo "3. Verifique se aparecem:"
echo "   - ✅ Canal Default: Conectado"
echo "   - ✅ Canal Comercial: Conectado"
echo "   - ✅ QR Codes sem 'undefined'"
echo "4. Teste envio de mensagem via painel"
echo "5. Verifique se a mensagem chega no WhatsApp"

echo ""
echo "📋 RESUMO FINAL DOS TESTES"
echo "========================="

# Verificar se tudo está funcionando
ALL_TESTS_PASSED=true

# Verificar sessões
if echo "$SESSIONS_3000" | jq -e '.sessions[0].status.status' >/dev/null 2>&1 && \
   echo "$SESSIONS_3000" | jq -r '.sessions[0].status.status' | grep -q "connected"; then
    log_success "✅ Sessão 3000 conectada"
else
    log_error "❌ Sessão 3000 não conectada"
    ALL_TESTS_PASSED=false
fi

if echo "$SESSIONS_3001" | jq -e '.sessions[0].status.status' >/dev/null 2>&1 && \
   echo "$SESSIONS_3001" | jq -r '.sessions[0].status.status' | grep -q "connected"; then
    log_success "✅ Sessão 3001 conectada"
else
    log_error "❌ Sessão 3001 não conectada"
    ALL_TESTS_PASSED=false
fi

# Verificar envio
if echo "$SEND_REAL_3000" | jq -e '.success' >/dev/null 2>&1 && \
   echo "$SEND_REAL_3000" | jq -r '.success' | grep -q "true"; then
    log_success "✅ Envio 3000 funcionando"
else
    log_error "❌ Envio 3000 falhou"
    ALL_TESTS_PASSED=false
fi

if echo "$SEND_REAL_3001" | jq -e '.success' >/dev/null 2>&1 && \
   echo "$SEND_REAL_3001" | jq -r '.success' | grep -q "true"; then
    log_success "✅ Envio 3001 funcionando"
else
    log_error "❌ Envio 3001 falhou"
    ALL_TESTS_PASSED=false
fi

# Verificar acesso externo
if curl -s http://212.85.11.238:3000/status >/dev/null 2>&1; then
    log_success "✅ Acesso externo 3000 OK"
else
    log_error "❌ Acesso externo 3000 falhou"
    ALL_TESTS_PASSED=false
fi

if curl -s http://212.85.11.238:3001/status >/dev/null 2>&1; then
    log_success "✅ Acesso externo 3001 OK"
else
    log_error "❌ Acesso externo 3001 falhou"
    ALL_TESTS_PASSED=false
fi

echo ""
if [ "$ALL_TESTS_PASSED" = true ]; then
    log_success "🎉 SISTEMA 100% OPERACIONAL!"
    echo ""
    echo "✅ Todos os testes passaram!"
    echo "✅ WhatsApp multi-canal funcionando perfeitamente!"
    echo "✅ Pronto para produção!"
    echo ""
    echo "📞 Próximos passos:"
    echo "1. Configure webhooks para recebimento de mensagens"
    echo "2. Teste o painel administrativo"
    echo "3. Configure números reais para envio"
    echo "4. Monitore logs regularmente"
else
    log_error "❌ ALGUNS TESTES FALHARAM!"
    echo ""
    echo "🔧 Ações necessárias:"
    echo "1. Verifique os logs: pm2 logs whatsapp-3000 --lines 50"
    echo "2. Reinicie os processos: pm2 restart all"
    echo "3. Verifique conectividade de rede"
    echo "4. Confirme configurações do firewall"
fi

echo ""
echo "📚 COMANDOS ÚTEIS PARA OPERAÇÃO:"
echo "================================"
echo "pm2 status                    - Ver status dos processos"
echo "pm2 logs whatsapp-3000       - Ver logs do canal default"
echo "pm2 logs whatsapp-3001       - Ver logs do canal comercial"
echo "pm2 restart all              - Reiniciar todos os processos"
echo "curl -s http://127.0.0.1:3000/sessions | jq .  - Verificar sessões"
echo "curl -s http://127.0.0.1:3001/sessions | jq .  - Verificar sessões"

echo ""
log_success "🧪 TESTE FINAL DE PRODUÇÃO FINALIZADO!" 