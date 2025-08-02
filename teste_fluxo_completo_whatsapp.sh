#!/bin/bash

# 🧪 TESTE DE FLUXO COMPLETO - WHATSAPP API
# Executar no VPS: ssh root@212.85.11.238 && cd /var/whatsapp-api
# chmod +x teste_fluxo_completo_whatsapp.sh && ./teste_fluxo_completo_whatsapp.sh

echo "🧪 TESTE DE FLUXO COMPLETO - WHATSAPP API"
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

echo "🔄 ETAPA 1: VERIFICAR STATUS ATUAL"
echo "=================================="

log_info "Status dos processos PM2:"
pm2 status

echo ""
log_info "Verificando se as sessões estão sendo inicializadas..."
SESSIONS_3000=$(curl -s http://127.0.0.1:3000/sessions)
SESSIONS_3001=$(curl -s http://127.0.0.1:3001/sessions)

echo "Sessions 3000: $SESSIONS_3000"
echo "Sessions 3001: $SESSIONS_3001"

echo ""
echo "🔍 ETAPA 2: OBTER QR CODES PARA ESCANEAMENTO"
echo "============================================"

log_info "Obtendo QR Code da porta 3000 (default)..."
QR_3000=$(curl -s http://127.0.0.1:3000/qr?session=default)
echo "QR 3000: $QR_3000"

log_info "Obtendo QR Code da porta 3001 (comercial)..."
QR_3001=$(curl -s http://127.0.0.1:3001/qr?session=comercial)
echo "QR 3001: $QR_3001"

echo ""
echo "📱 ETAPA 3: INSTRUÇÕES PARA ESCANEAMENTO"
echo "========================================"

echo "🔍 Para escanear os QR Codes:"
echo ""
echo "1. Porta 3000 (Default):"
if echo "$QR_3000" | jq -e '.qr' >/dev/null 2>&1; then
    QR_CODE_3000=$(echo "$QR_3000" | jq -r '.qr')
    echo "   QR Code: $QR_CODE_3000"
    echo "   Ou acesse: http://212.85.11.238:3000/qr?session=default"
else
    echo "   ❌ QR Code não disponível"
    echo "   Status: $(echo "$QR_3000" | jq -r '.message // "Erro"')"
fi

echo ""
echo "2. Porta 3001 (Comercial):"
if echo "$QR_3001" | jq -e '.qr' >/dev/null 2>&1; then
    QR_CODE_3001=$(echo "$QR_3001" | jq -r '.qr')
    echo "   QR Code: $QR_CODE_3001"
    echo "   Ou acesse: http://212.85.11.238:3001/qr?session=comercial"
else
    echo "   ❌ QR Code não disponível"
    echo "   Status: $(echo "$QR_3001" | jq -r '.message // "Erro"')"
fi

echo ""
echo "📋 INSTRUÇÕES DE ESCANEAMENTO:"
echo "=============================="
echo "1. Abra o WhatsApp no seu celular"
echo "2. Vá em Configurações > Dispositivos vinculados"
echo "3. Toque em 'Vincular um dispositivo'"
echo "4. Escaneie o QR Code correspondente"
echo "5. Aguarde a confirmação de conexão"
echo "6. Repita para o segundo canal"

echo ""
echo "⏳ ETAPA 4: MONITORAMENTO DE AUTENTICAÇÃO"
echo "========================================="

log_info "Monitorando logs de autenticação..."
echo "Aguardando autenticação dos QR Codes..."
echo "Pressione Ctrl+C quando ambos estiverem autenticados"
echo ""

# Monitorar logs em tempo real
pm2 logs --lines 0 --follow | grep -E "(🔐 \[.*\] Cliente autenticado|✅ \[.*\] Cliente WhatsApp pronto!)" &
MONITOR_PID=$!

# Aguardar input do usuário
read -p "Pressione ENTER quando ambos os QR Codes estiverem escaneados e autenticados..."

# Parar monitoramento
kill $MONITOR_PID 2>/dev/null

echo ""
echo "🔍 ETAPA 5: VERIFICAR SESSÕES APÓS AUTENTICAÇÃO"
echo "==============================================="

log_info "Verificando sessões após autenticação..."
sleep 5

SESSIONS_AFTER_AUTH_3000=$(curl -s http://127.0.0.1:3000/sessions)
SESSIONS_AFTER_AUTH_3001=$(curl -s http://127.0.0.1:3001/sessions)

echo "Sessions 3000 após auth: $SESSIONS_AFTER_AUTH_3000"
echo "Sessions 3001 após auth: $SESSIONS_AFTER_AUTH_3001"

echo ""
echo "📤 ETAPA 6: TESTAR ENVIO DE MENSAGENS"
echo "====================================="

# Preparar payloads de teste
PAYLOAD_DEFAULT='{"sessionName":"default","number":"5511999999999","message":"🧪 Teste de envio - Canal Default - '$(date +%H:%M:%S)'"}'
PAYLOAD_COMERCIAL='{"sessionName":"comercial","number":"5511999999999","message":"🧪 Teste de envio - Canal Comercial - '$(date +%H:%M:%S)'"}'

log_info "Testando envio na porta 3000 (default)..."
SEND_3000=$(curl -s -X POST http://127.0.0.1:3000/send/text \
  -H "Content-Type: application/json" \
  -d "$PAYLOAD_DEFAULT")
echo "Envio 3000: $SEND_3000"

log_info "Testando envio na porta 3001 (comercial)..."
SEND_3001=$(curl -s -X POST http://127.0.0.1:3001/send/text \
  -H "Content-Type: application/json" \
  -d "$PAYLOAD_COMERCIAL")
echo "Envio 3001: $SEND_3001"

echo ""
echo "🔍 ETAPA 7: TESTAR VERIFICAÇÃO DE NÚMEROS"
echo "========================================="

log_info "Testando verificação de número na porta 3000..."
CHECK_3000=$(curl -s -X POST http://127.0.0.1:3000/check/number \
  -H "Content-Type: application/json" \
  -d '{"sessionName":"default","number":"5511999999999"}')
echo "Check 3000: $CHECK_3000"

log_info "Testando verificação de número na porta 3001..."
CHECK_3001=$(curl -s -X POST http://127.0.0.1:3001/check/number \
  -H "Content-Type: application/json" \
  -d '{"sessionName":"comercial","number":"5511999999999"}')
echo "Check 3001: $CHECK_3001"

echo ""
echo "🔍 ETAPA 8: TESTAR STATUS DETALHADO"
echo "==================================="

log_info "Status detalhado porta 3000..."
STATUS_3000=$(curl -s http://127.0.0.1:3000/status)
echo "Status 3000: $STATUS_3000"

log_info "Status detalhado porta 3001..."
STATUS_3001=$(curl -s http://127.0.0.1:3001/status)
echo "Status 3001: $STATUS_3001"

echo ""
echo "📋 RESUMO DOS TESTES"
echo "==================="

# Verificar QR Codes
if echo "$QR_3000" | jq -e '.qr' >/dev/null 2>&1; then
    log_success "QR Code 3000 disponível ✅"
else
    log_error "QR Code 3000 não disponível ❌"
fi

if echo "$QR_3001" | jq -e '.qr' >/dev/null 2>&1; then
    log_success "QR Code 3001 disponível ✅"
else
    log_error "QR Code 3001 não disponível ❌"
fi

# Verificar sessões após autenticação
if echo "$SESSIONS_AFTER_AUTH_3000" | jq -e '.sessions' >/dev/null 2>&1; then
    SESSIONS_COUNT_3000=$(echo "$SESSIONS_AFTER_AUTH_3000" | jq '.sessions | length')
    if [ "$SESSIONS_COUNT_3000" -gt 0 ]; then
        log_success "Porta 3000: $SESSIONS_COUNT_3000 sessões após auth ✅"
    else
        log_error "Porta 3000: 0 sessões após auth ❌"
    fi
else
    log_error "Porta 3000: Erro ao listar sessões após auth ❌"
fi

if echo "$SESSIONS_AFTER_AUTH_3001" | jq -e '.sessions' >/dev/null 2>&1; then
    SESSIONS_COUNT_3001=$(echo "$SESSIONS_AFTER_AUTH_3001" | jq '.sessions | length')
    if [ "$SESSIONS_COUNT_3001" -gt 0 ]; then
        log_success "Porta 3001: $SESSIONS_COUNT_3001 sessões após auth ✅"
    else
        log_error "Porta 3001: 0 sessões após auth ❌"
    fi
else
    log_error "Porta 3001: Erro ao listar sessões após auth ❌"
fi

# Verificar envio de mensagens
if echo "$SEND_3000" | jq -e '.success' >/dev/null 2>&1; then
    SEND_SUCCESS_3000=$(echo "$SEND_3000" | jq -r '.success')
    if [ "$SEND_SUCCESS_3000" = "true" ]; then
        log_success "Envio 3000: Sucesso ✅"
    else
        log_error "Envio 3000: Falha ❌"
    fi
else
    log_error "Envio 3000: Resposta inválida ❌"
fi

if echo "$SEND_3001" | jq -e '.success' >/dev/null 2>&1; then
    SEND_SUCCESS_3001=$(echo "$SEND_3001" | jq -r '.success')
    if [ "$SEND_SUCCESS_3001" = "true" ]; then
        log_success "Envio 3001: Sucesso ✅"
    else
        log_error "Envio 3001: Falha ❌"
    fi
else
    log_error "Envio 3001: Resposta inválida ❌"
fi

echo ""
echo "🎯 PRÓXIMOS PASSOS:"
echo "=================="

echo "1. Se os QR Codes apareceram:"
echo "   - Escaneie-os no WhatsApp"
echo "   - Aguarde a autenticação"
echo "   - Execute novamente este script"

echo ""
echo "2. Se as sessões foram criadas:"
echo "   - Teste o painel administrativo"
echo "   - Verifique se os QRs aparecem sem 'undefined'"
echo "   - Teste envio/recebimento no painel"

echo ""
echo "3. Se ainda houver problemas:"
echo "   - Verifique os logs: pm2 logs whatsapp-3000 --lines 50"
echo "   - Verifique permissões da pasta sessions/"
echo "   - Reinicie os processos: pm2 restart all"

echo ""
log_success "🧪 TESTE DE FLUXO COMPLETO FINALIZADO!" 