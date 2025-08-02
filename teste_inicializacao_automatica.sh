#!/bin/bash

# 🧪 TESTE DA INICIALIZAÇÃO AUTOMÁTICA - WHATSAPP API
# Executar no VPS: ssh root@212.85.11.238 && cd /var/whatsapp-api
# chmod +x teste_inicializacao_automatica.sh && ./teste_inicializacao_automatica.sh

echo "🧪 TESTE DA INICIALIZAÇÃO AUTOMÁTICA - WHATSAPP API"
echo "=================================================="
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

echo "🔄 ETAPA 1: REINICIAR PROCESSOS COM INICIALIZAÇÃO AUTOMÁTICA"
echo "============================================================"

log_info "Parando processos PM2..."
pm2 stop all

log_info "Reiniciando processos com inicialização automática..."
pm2 start ecosystem.config.js

log_info "Aguardando inicialização automática das sessões..."
sleep 15

echo ""
echo "📊 ETAPA 2: VERIFICAR STATUS DOS PROCESSOS"
echo "=========================================="

pm2 status

echo ""
echo "🔍 ETAPA 3: VERIFICAR LOGS DE INICIALIZAÇÃO AUTOMÁTICA"
echo "====================================================="

log_info "Logs de inicialização automática da porta 3000 (últimas 20 linhas):"
pm2 logs whatsapp-3000 --lines 20 --nostream

log_info "Logs de inicialização automática da porta 3001 (últimas 20 linhas):"
pm2 logs whatsapp-3001 --lines 20 --nostream

echo ""
echo "🔍 ETAPA 4: TESTAR ENDPOINT /SESSIONS"
echo "====================================="

log_info "Testando /sessions na porta 3000..."
SESSIONS_3000=$(curl -s http://127.0.0.1:3000/sessions)
echo "Resposta 3000: $SESSIONS_3000"

log_info "Testando /sessions na porta 3001..."
SESSIONS_3001=$(curl -s http://127.0.0.1:3001/sessions)
echo "Resposta 3001: $SESSIONS_3001"

echo ""
echo "🔍 ETAPA 5: TESTAR ENDPOINTS QR"
echo "==============================="

log_info "Testando QR default (3000)..."
QR_3000=$(curl -s http://127.0.0.1:3000/qr?session=default)
echo "QR 3000: $QR_3000"

log_info "Testando QR comercial (3001)..."
QR_3001=$(curl -s http://127.0.0.1:3001/qr?session=comercial)
echo "QR 3001: $QR_3001"

echo ""
echo "📤 ETAPA 6: TESTAR ENVIO DE MENSAGEM"
echo "===================================="

# Preparar payload de teste
PAYLOAD_DEFAULT='{"sessionName":"default","number":"5511999999999","message":"Teste inicialização automática - Default"}'
PAYLOAD_COMERCIAL='{"sessionName":"comercial","number":"5511999999999","message":"Teste inicialização automática - Comercial"}'

log_info "Testando envio na porta 3000..."
SEND_3000=$(curl -s -X POST http://127.0.0.1:3000/send/text \
  -H "Content-Type: application/json" \
  -d "$PAYLOAD_DEFAULT")
echo "Envio 3000: $SEND_3000"

log_info "Testando envio na porta 3001..."
SEND_3001=$(curl -s -X POST http://127.0.0.1:3001/send/text \
  -H "Content-Type: application/json" \
  -d "$PAYLOAD_COMERCIAL")
echo "Envio 3001: $SEND_3001"

echo ""
echo "🔍 ETAPA 7: VERIFICAR STATUS DETALHADO"
echo "======================================"

log_info "Status detalhado porta 3000..."
STATUS_3000=$(curl -s http://127.0.0.1:3000/status)
echo "Status 3000: $STATUS_3000"

log_info "Status detalhado porta 3001..."
STATUS_3001=$(curl -s http://127.0.0.1:3001/status)
echo "Status 3001: $STATUS_3001"

echo ""
echo "🔍 ETAPA 8: TESTAR INICIALIZAÇÃO MANUAL (BACKUP)"
echo "================================================"

log_info "Testando inicialização manual na porta 3000..."
MANUAL_3000=$(curl -s -X POST http://127.0.0.1:3000/session/start/default)
echo "Manual 3000: $MANUAL_3000"

log_info "Testando inicialização manual na porta 3001..."
MANUAL_3001=$(curl -s -X POST http://127.0.0.1:3001/session/start/comercial)
echo "Manual 3001: $MANUAL_3001"

echo ""
echo "📋 RESUMO DA INICIALIZAÇÃO AUTOMÁTICA"
echo "====================================="

# Verificar se as sessões estão sendo criadas corretamente
if echo "$SESSIONS_3000" | jq -e '.sessions' >/dev/null 2>&1; then
    SESSIONS_COUNT_3000=$(echo "$SESSIONS_3000" | jq '.sessions | length')
    if [ "$SESSIONS_COUNT_3000" -gt 0 ]; then
        log_success "Porta 3000: $SESSIONS_COUNT_3000 sessões encontradas ✅"
    else
        log_error "Porta 3000: 0 sessões encontradas ❌"
    fi
else
    log_error "Porta 3000: Erro ao listar sessões ❌"
fi

if echo "$SESSIONS_3001" | jq -e '.sessions' >/dev/null 2>&1; then
    SESSIONS_COUNT_3001=$(echo "$SESSIONS_3001" | jq '.sessions | length')
    if [ "$SESSIONS_COUNT_3001" -gt 0 ]; then
        log_success "Porta 3001: $SESSIONS_COUNT_3001 sessões encontradas ✅"
    else
        log_error "Porta 3001: 0 sessões encontradas ❌"
    fi
else
    log_error "Porta 3001: Erro ao listar sessões ❌"
fi

# Verificar se os QR Codes estão funcionando
if echo "$QR_3000" | jq -e '.qr' >/dev/null 2>&1; then
    log_success "QR Code 3000 funcionando ✅"
elif echo "$QR_3000" | jq -e '.message' >/dev/null 2>&1; then
    QR_STATUS_3000=$(echo "$QR_3000" | jq -r '.message')
    if [[ "$QR_STATUS_3000" == *"não encontrada"* ]]; then
        log_error "QR Code 3000: Sessão não encontrada ❌"
    else
        log_warning "QR Code 3000: $QR_STATUS_3000 ⚠️"
    fi
else
    log_error "QR Code 3000: Resposta inválida ❌"
fi

if echo "$QR_3001" | jq -e '.qr' >/dev/null 2>&1; then
    log_success "QR Code 3001 funcionando ✅"
elif echo "$QR_3001" | jq -e '.message' >/dev/null 2>&1; then
    QR_STATUS_3001=$(echo "$QR_3001" | jq -r '.message')
    if [[ "$QR_STATUS_3001" == *"não encontrada"* ]]; then
        log_error "QR Code 3001: Sessão não encontrada ❌"
    else
        log_warning "QR Code 3001: $QR_STATUS_3001 ⚠️"
    fi
else
    log_error "QR Code 3001: Resposta inválida ❌"
fi

# Verificar se o envio está funcionando
if echo "$SEND_3000" | jq -e '.message' >/dev/null 2>&1; then
    SEND_STATUS_3000=$(echo "$SEND_3000" | jq -r '.message')
    if [[ "$SEND_STATUS_3000" == *"não encontrada"* ]]; then
        log_error "Envio 3000: Sessão não encontrada ❌"
    elif [[ "$SEND_STATUS_3000" == *"não está conectada"* ]]; then
        log_warning "Envio 3000: Sessão não conectada (esperado) ⚠️"
    else
        log_success "Envio 3000: Respondendo corretamente ✅"
    fi
else
    log_error "Envio 3000: Não responde ❌"
fi

if echo "$SEND_3001" | jq -e '.message' >/dev/null 2>&1; then
    SEND_STATUS_3001=$(echo "$SEND_3001" | jq -r '.message')
    if [[ "$SEND_STATUS_3001" == *"não encontrada"* ]]; then
        log_error "Envio 3001: Sessão não encontrada ❌"
    elif [[ "$SEND_STATUS_3001" == *"não está conectada"* ]]; then
        log_warning "Envio 3001: Sessão não conectada (esperado) ⚠️"
    else
        log_success "Envio 3001: Respondendo corretamente ✅"
    fi
else
    log_error "Envio 3001: Não responde ❌"
fi

echo ""
echo "🎯 RESULTADO ESPERADO:"
echo "====================="

echo "✅ Logs devem mostrar:"
echo "   - 🚩 [AUTO-START] Iniciando sessão \"default/comercial\" automaticamente..."
echo "   - 🚩 [AUTO-START] Sessão \"default/comercial\" iniciada: SUCESSO"
echo "   - ✅ [AUTO-START] whatsappClients[\"default/comercial\"] criado com sucesso"
echo "   - 🔍 [DEBUG] Total de sessões ativas: 1"

echo ""
echo "✅ Endpoints devem mostrar:"
echo "   - /sessions: total > 0"
echo "   - /qr: QR Code ou status apropriado"
echo "   - /send/text: resposta válida (mesmo que não conectado)"

echo ""
echo "🔧 SE AINDA HOUVER PROBLEMAS:"
echo "============================"

echo "1. Verifique se os logs mostram a inicialização automática:"
echo "   pm2 logs whatsapp-3000 --lines 25"
echo "   pm2 logs whatsapp-3001 --lines 25"

echo ""
echo "2. Teste inicialização manual:"
echo "   curl -X POST http://127.0.0.1:3000/session/start/default"
echo "   curl -X POST http://127.0.0.1:3001/session/start/comercial"

echo ""
echo "3. Verifique se as sessões foram criadas:"
echo "   curl -s http://127.0.0.1:3000/sessions | jq ."
echo "   curl -s http://127.0.0.1:3001/sessions | jq ."

echo ""
echo "4. Se necessário, reinicie novamente:"
echo "   pm2 restart all"

echo ""
log_success "🧪 TESTE DA INICIALIZAÇÃO AUTOMÁTICA FINALIZADO!" 