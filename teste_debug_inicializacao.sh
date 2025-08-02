#!/bin/bash

# 🧪 TESTE DE DEBUG DA INICIALIZAÇÃO - WHATSAPP API
# Executar no VPS: ssh root@212.85.11.238 && cd /var/whatsapp-api
# chmod +x teste_debug_inicializacao.sh && ./teste_debug_inicializacao.sh

echo "🧪 TESTE DE DEBUG DA INICIALIZAÇÃO - WHATSAPP API"
echo "================================================"
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

echo "🔄 ETAPA 1: REINICIAR PROCESSOS COM LOGS DE DEBUG"
echo "================================================="

log_info "Parando processos PM2..."
pm2 stop all

log_info "Reiniciando processos com logs de debug..."
pm2 start ecosystem.config.js

log_info "Aguardando inicialização com logs de debug..."
sleep 20

echo ""
echo "📊 ETAPA 2: VERIFICAR STATUS DOS PROCESSOS"
echo "=========================================="

pm2 status

echo ""
echo "🔍 ETAPA 3: VERIFICAR LOGS DE DEBUG DETALHADOS"
echo "============================================="

log_info "Logs de debug da porta 3000 (últimas 30 linhas):"
pm2 logs whatsapp-3000 --lines 30 --nostream

log_info "Logs de debug da porta 3001 (últimas 30 linhas):"
pm2 logs whatsapp-3001 --lines 30 --nostream

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
echo "🔍 ETAPA 5: TESTAR INICIALIZAÇÃO MANUAL"
echo "======================================"

log_info "Testando inicialização manual na porta 3000..."
MANUAL_3000=$(curl -s -X POST http://127.0.0.1:3000/session/start/default)
echo "Manual 3000: $MANUAL_3000"

log_info "Testando inicialização manual na porta 3001..."
MANUAL_3001=$(curl -s -X POST http://127.0.0.1:3001/session/start/comercial)
echo "Manual 3001: $MANUAL_3001"

echo ""
echo "🔍 ETAPA 6: VERIFICAR SESSÕES APÓS MANUAL"
echo "========================================="

log_info "Verificando /sessions após inicialização manual..."
SESSIONS_AFTER_3000=$(curl -s http://127.0.0.1:3000/sessions)
echo "Sessions após manual 3000: $SESSIONS_AFTER_3000"

SESSIONS_AFTER_3001=$(curl -s http://127.0.0.1:3001/sessions)
echo "Sessions após manual 3001: $SESSIONS_AFTER_3001"

echo ""
echo "🔍 ETAPA 7: TESTAR QR CODES"
echo "==========================="

log_info "Testando QR default (3000)..."
QR_3000=$(curl -s http://127.0.0.1:3000/qr?session=default)
echo "QR 3000: $QR_3000"

log_info "Testando QR comercial (3001)..."
QR_3001=$(curl -s http://127.0.0.1:3001/qr?session=comercial)
echo "QR 3001: $QR_3001"

echo ""
echo "📋 RESUMO DOS LOGS DE DEBUG"
echo "==========================="

# Verificar se os logs de debug apareceram
log_info "Verificando se os logs de debug apareceram..."

# Verificar logs da porta 3000
DEBUG_3000=$(pm2 logs whatsapp-3000 --lines 50 --nostream 2>/dev/null | grep -E "(🔥 \[AUTO-POST\]|✅ \[INIT\]|🚩 \[AUTO-START\])" | wc -l)
if [ "$DEBUG_3000" -gt 0 ]; then
    log_success "Porta 3000: $DEBUG_3000 logs de debug encontrados ✅"
else
    log_error "Porta 3000: Nenhum log de debug encontrado ❌"
fi

# Verificar logs da porta 3001
DEBUG_3001=$(pm2 logs whatsapp-3001 --lines 50 --nostream 2>/dev/null | grep -E "(🔥 \[AUTO-POST\]|✅ \[INIT\]|🚩 \[AUTO-START\])" | wc -l)
if [ "$DEBUG_3001" -gt 0 ]; then
    log_success "Porta 3001: $DEBUG_3001 logs de debug encontrados ✅"
else
    log_error "Porta 3001: Nenhum log de debug encontrado ❌"
fi

# Verificar se as sessões foram criadas
if echo "$SESSIONS_AFTER_3000" | jq -e '.sessions' >/dev/null 2>&1; then
    SESSIONS_COUNT_3000=$(echo "$SESSIONS_AFTER_3000" | jq '.sessions | length')
    if [ "$SESSIONS_COUNT_3000" -gt 0 ]; then
        log_success "Porta 3000: $SESSIONS_COUNT_3000 sessões após manual ✅"
    else
        log_error "Porta 3000: 0 sessões após manual ❌"
    fi
else
    log_error "Porta 3000: Erro ao listar sessões após manual ❌"
fi

if echo "$SESSIONS_AFTER_3001" | jq -e '.sessions' >/dev/null 2>&1; then
    SESSIONS_COUNT_3001=$(echo "$SESSIONS_AFTER_3001" | jq '.sessions | length')
    if [ "$SESSIONS_COUNT_3001" -gt 0 ]; then
        log_success "Porta 3001: $SESSIONS_COUNT_3001 sessões após manual ✅"
    else
        log_error "Porta 3001: 0 sessões após manual ❌"
    fi
else
    log_error "Porta 3001: Erro ao listar sessões após manual ❌"
fi

echo ""
echo "🎯 LOGS ESPERADOS:"
echo "================="

echo "✅ Logs que devem aparecer:"
echo "   - 🚩 [AUTO-START] Iniciando sessão \"default/comercial\" automaticamente..."
echo "   - 🚩 [AUTO-START] URL do POST interno: http://127.0.0.1:3000/3001/session/start/..."
echo "   - 🎯 [AUTO-POST] Status interno: 200"
echo "   - 🔥 [AUTO-POST] Recebido POST /session/start/default/comercial"
echo "   - ✅ [INIT] initializeWhatsApp chamado para: default/comercial"
echo "   - ✅ [INIT] whatsappClients agora tem keys: [...]"
echo "   - 🚩 [AUTO-START] Sessão \"default/comercial\" iniciada: SUCESSO"

echo ""
echo "🔧 SE OS LOGS NÃO APARECEREM:"
echo "============================"

echo "1. Verifique se o POST interno está chegando:"
echo "   pm2 logs whatsapp-3000 --lines 50 | grep '🔥 \[AUTO-POST\]'"
echo "   pm2 logs whatsapp-3001 --lines 50 | grep '🔥 \[AUTO-POST\]'"

echo ""
echo "2. Verifique se initializeWhatsApp está sendo chamado:"
echo "   pm2 logs whatsapp-3000 --lines 50 | grep '✅ \[INIT\]'"
echo "   pm2 logs whatsapp-3001 --lines 50 | grep '✅ \[INIT\]'"

echo ""
echo "3. Teste manualmente:"
echo "   curl -X POST http://127.0.0.1:3000/session/start/default"
echo "   curl -X POST http://127.0.0.1:3001/session/start/comercial"

echo ""
echo "4. Se manual funcionar mas automático não:"
echo "   - Problema no URL do fetch interno"
echo "   - Problema no timing da chamada"
echo "   - Problema no binding do servidor"

echo ""
log_success "🧪 TESTE DE DEBUG DA INICIALIZAÇÃO FINALIZADO!" 