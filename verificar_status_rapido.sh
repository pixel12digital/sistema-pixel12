#!/bin/bash

# ⚡ VERIFICAÇÃO RÁPIDA - STATUS DO SISTEMA WHATSAPP
# Executar no VPS: ssh root@212.85.11.238 && cd /var/whatsapp-api
# chmod +x verificar_status_rapido.sh && ./verificar_status_rapido.sh

echo "⚡ VERIFICAÇÃO RÁPIDA DO SISTEMA WHATSAPP"
echo "========================================"
echo ""

# Cores
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m'

# Função para log
log_success() { echo -e "${GREEN}✅${NC} $1"; }
log_error() { echo -e "${RED}❌${NC} $1"; }
log_warning() { echo -e "${YELLOW}⚠️${NC} $1"; }

echo "📊 Status dos Processos PM2:"
pm2 status

echo ""
echo "🔍 Teste de Endpoints:"

# Testar QR Code default
echo -n "QR Default (3000): "
if curl -s http://127.0.0.1:3000/qr?session=default | jq -e '.qr' >/dev/null 2>&1; then
    log_success "OK"
else
    log_error "FALHOU"
fi

# Testar QR Code comercial
echo -n "QR Comercial (3001): "
if curl -s http://127.0.0.1:3001/qr?session=comercial | jq -e '.qr' >/dev/null 2>&1; then
    log_success "OK"
else
    log_error "FALHOU"
fi

# Testar status default
echo -n "Status Default (3000): "
STATUS_DEFAULT=$(curl -s http://127.0.0.1:3000/status | jq -r '.ready' 2>/dev/null)
if [ "$STATUS_DEFAULT" = "true" ]; then
    log_success "CONECTADO"
elif [ "$STATUS_DEFAULT" = "false" ]; then
    log_warning "PENDENTE"
else
    log_error "ERRO"
fi

# Testar status comercial
echo -n "Status Comercial (3001): "
STATUS_COMERCIAL=$(curl -s http://127.0.0.1:3001/status | jq -r '.ready' 2>/dev/null)
if [ "$STATUS_COMERCIAL" = "true" ]; then
    log_success "CONECTADO"
elif [ "$STATUS_COMERCIAL" = "false" ]; then
    log_warning "PENDENTE"
else
    log_error "ERRO"
fi

echo ""
echo "📡 Conectividade Externa:"

# Testar QR externo default
echo -n "QR Default Externo: "
if curl -s http://212.85.11.238:3000/qr?session=default | jq -e '.qr' >/dev/null 2>&1; then
    log_success "OK"
else
    log_error "FALHOU"
fi

# Testar QR externo comercial
echo -n "QR Comercial Externo: "
if curl -s http://212.85.11.238:3001/qr?session=comercial | jq -e '.qr' >/dev/null 2>&1; then
    log_success "OK"
else
    log_error "FALHOU"
fi

echo ""
echo "🌐 Painel:"
PANEL_STATUS=$(curl -s -o /dev/null -w "%{http_code}" http://212.85.11.238:8080/painel/)
if [ "$PANEL_STATUS" = "200" ]; then
    log_success "Painel acessível (HTTP $PANEL_STATUS)"
else
    log_error "Painel não acessível (HTTP $PANEL_STATUS)"
fi

echo ""
echo "📋 RESUMO:"
echo "=========="

# Contar sucessos
SUCCESS=0
TOTAL=0

# Verificar processos
if pm2 list | grep -q "whatsapp-3000.*online"; then SUCCESS=$((SUCCESS + 1)); fi
if pm2 list | grep -q "whatsapp-3001.*online"; then SUCCESS=$((SUCCESS + 1)); fi
TOTAL=$((TOTAL + 2))

# Verificar QR Codes
if curl -s http://127.0.0.1:3000/qr?session=default | jq -e '.qr' >/dev/null 2>&1; then SUCCESS=$((SUCCESS + 1)); fi
if curl -s http://127.0.0.1:3001/qr?session=comercial | jq -e '.qr' >/dev/null 2>&1; then SUCCESS=$((SUCCESS + 1)); fi
TOTAL=$((TOTAL + 2))

# Verificar conectividade externa
if curl -s http://212.85.11.238:3000/qr?session=default | jq -e '.qr' >/dev/null 2>&1; then SUCCESS=$((SUCCESS + 1)); fi
if curl -s http://212.85.11.238:3001/qr?session=comercial | jq -e '.qr' >/dev/null 2>&1; then SUCCESS=$((SUCCESS + 1)); fi
TOTAL=$((TOTAL + 2))

# Verificar painel
if [ "$PANEL_STATUS" = "200" ]; then SUCCESS=$((SUCCESS + 1)); fi
TOTAL=$((TOTAL + 1))

echo "Resultado: $SUCCESS/$TOTAL testes passaram"

if [ $SUCCESS -eq $TOTAL ]; then
    echo ""
    log_success "🎉 SISTEMA 100% OPERACIONAL!"
    echo ""
    echo "📋 PRÓXIMOS PASSOS:"
    echo "1. Acesse: http://212.85.11.238:8080/painel/comunicacao.php"
    echo "2. Conecte os canais pendentes (escanear QR)"
    echo "3. Teste envio/recebimento no chat central"
else
    echo ""
    log_warning "⚠️ ALGUNS TESTES FALHARAM"
    echo ""
    echo "🔧 AÇÕES:"
    echo "1. Execute: ./teste_completo_sistema_whatsapp.sh"
    echo "2. Verifique logs: pm2 logs --err"
    echo "3. Reinicie se necessário: pm2 restart all"
fi

echo ""
echo "⚡ VERIFICAÇÃO RÁPIDA FINALIZADA!" 