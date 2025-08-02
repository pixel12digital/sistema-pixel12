#!/bin/bash

# 🧪 SCRIPT DE TESTE DAS CORREÇÕES - WHATSAPP API
# Executar no VPS: ssh root@212.85.11.238 && cd /var/whatsapp-api
# chmod +x teste_correcoes_whatsapp.sh && ./teste_correcoes_whatsapp.sh

echo "🧪 TESTE DAS CORREÇÕES - WHATSAPP API"
echo "===================================="
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

echo "🔄 ETAPA 1: REINICIAR PROCESSOS COM CÓDIGO CORRIGIDO"
echo "===================================================="

log_info "Parando processos PM2..."
pm2 stop all

log_info "Reiniciando processos com código corrigido..."
pm2 start ecosystem.config.js

log_info "Aguardando inicialização..."
sleep 5

echo ""
echo "📊 ETAPA 2: VERIFICAR STATUS DOS PROCESSOS"
echo "=========================================="

pm2 status

echo ""
echo "🔍 ETAPA 3: TESTAR ENDPOINT /SESSIONS"
echo "====================================="

log_info "Testando /sessions na porta 3000..."
SESSIONS_3000=$(curl -s http://127.0.0.1:3000/sessions)
echo "Resposta 3000: $SESSIONS_3000"

log_info "Testando /sessions na porta 3001..."
SESSIONS_3001=$(curl -s http://127.0.0.1:3001/sessions)
echo "Resposta 3001: $SESSIONS_3001"

echo ""
echo "🔍 ETAPA 4: TESTAR ENDPOINTS QR COM DEBUG"
echo "========================================="

log_info "Testando QR default (3000)..."
QR_3000=$(curl -s http://127.0.0.1:3000/qr?session=default)
echo "QR 3000: $QR_3000"

log_info "Testando QR comercial (3001)..."
QR_3001=$(curl -s http://127.0.0.1:3001/qr?session=comercial)
echo "QR 3001: $QR_3001"

echo ""
echo "📤 ETAPA 5: TESTAR ENVIO DE MENSAGEM"
echo "===================================="

# Preparar payload de teste
PAYLOAD_DEFAULT='{"sessionName":"default","number":"5511999999999","message":"Teste correção - Default"}'
PAYLOAD_COMERCIAL='{"sessionName":"comercial","number":"5511999999999","message":"Teste correção - Comercial"}'

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
echo "📊 ETAPA 6: VERIFICAR LOGS DE DEBUG"
echo "==================================="

log_info "Logs da porta 3000 (últimas 10 linhas):"
pm2 logs whatsapp-3000 --lines 10 --nostream

log_info "Logs da porta 3001 (últimas 10 linhas):"
pm2 logs whatsapp-3001 --lines 10 --nostream

echo ""
echo "🔍 ETAPA 7: TESTAR CONECTIVIDADE EXTERNA"
echo "========================================"

log_info "Testando QR externo default..."
QR_EXT_3000=$(curl -s http://212.85.11.238:3000/qr?session=default)
if echo "$QR_EXT_3000" | jq -e '.qr' >/dev/null 2>&1; then
    log_success "QR externo 3000 OK"
else
    log_error "QR externo 3000 FALHOU"
    echo "Resposta: $QR_EXT_3000"
fi

log_info "Testando QR externo comercial..."
QR_EXT_3001=$(curl -s http://212.85.11.238:3001/qr?session=comercial)
if echo "$QR_EXT_3001" | jq -e '.qr' >/dev/null 2>&1; then
    log_success "QR externo 3001 OK"
else
    log_error "QR externo 3001 FALHOU"
    echo "Resposta: $QR_EXT_3001"
fi

echo ""
echo "📋 RESUMO DAS CORREÇÕES"
echo "======================"

# Verificar se as sessões estão sendo criadas corretamente
if echo "$SESSIONS_3000" | jq -e '.sessions' >/dev/null 2>&1; then
    SESSIONS_COUNT_3000=$(echo "$SESSIONS_3000" | jq '.sessions | length')
    log_success "Porta 3000: $SESSIONS_COUNT_3000 sessões encontradas"
else
    log_error "Porta 3000: Erro ao listar sessões"
fi

if echo "$SESSIONS_3001" | jq -e '.sessions' >/dev/null 2>&1; then
    SESSIONS_COUNT_3001=$(echo "$SESSIONS_3001" | jq '.sessions | length')
    log_success "Porta 3001: $SESSIONS_COUNT_3001 sessões encontradas"
else
    log_error "Porta 3001: Erro ao listar sessões"
fi

# Verificar se os QR Codes estão funcionando
if echo "$QR_3000" | jq -e '.qr' >/dev/null 2>&1; then
    log_success "QR Code 3000 funcionando"
else
    log_error "QR Code 3000 falhou"
fi

if echo "$QR_3001" | jq -e '.qr' >/dev/null 2>&1; then
    log_success "QR Code 3001 funcionando"
else
    log_error "QR Code 3001 falhou"
fi

# Verificar se o envio está funcionando (mesmo que falhe por não estar conectado)
if echo "$SEND_3000" | jq -e '.message' >/dev/null 2>&1; then
    log_success "Endpoint envio 3000 respondendo"
else
    log_error "Endpoint envio 3000 não responde"
fi

if echo "$SEND_3001" | jq -e '.message' >/dev/null 2>&1; then
    log_success "Endpoint envio 3001 respondendo"
else
    log_error "Endpoint envio 3001 não responde"
fi

echo ""
echo "🎯 PRÓXIMOS PASSOS:"
echo "=================="

echo "1. Verifique os logs acima para confirmar que:"
echo "   - sessionName está sendo definido corretamente"
echo "   - whatsappClients[sessionName] está sendo criado"
echo "   - Os endpoints estão respondendo com debug"

echo ""
echo "2. Se os QR Codes aparecerem, escaneie-os no WhatsApp"

echo ""
echo "3. Execute o teste completo:"
echo "   ./teste_completo_sistema_whatsapp.sh"

echo ""
echo "4. Teste no painel:"
echo "   http://212.85.11.238:8080/painel/comunicacao.php"

echo ""
log_success "🧪 TESTE DAS CORREÇÕES FINALIZADO!" 