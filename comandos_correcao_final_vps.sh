#!/bin/bash

# 🔧 CORREÇÃO FINAL VPS - COMANDOS CORRETOS
# Executar: ssh root@212.85.11.238 && cd /var/whatsapp-api && chmod +x comandos_correcao_final_vps.sh && ./comandos_correcao_final_vps.sh

echo "🔧 CORREÇÃO FINAL VPS - COMANDOS CORRETOS"
echo "========================================="
echo ""

# Cores para output
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

# ETAPA 1: VERIFICAR ALTERAÇÃO NO ARQUIVO
echo "📋 ETAPA 1: VERIFICANDO ALTERAÇÃO NO ARQUIVO"
echo "============================================="

log_info "Verificando se a alteração foi aplicada corretamente..."
if grep -q "https://app.pixel12digital.com.br/painel/receber_mensagem_ana_local.php" whatsapp-api-server.js; then
    log_success "URL absoluta encontrada no arquivo"
else
    log_error "URL absoluta NÃO encontrada - necessário corrigir novamente"
    log_info "Aplicando correção novamente..."
    sed -i "s|let webhookUrl = 'api/webhook.php';|let webhookUrl = 'https://app.pixel12digital.com.br/painel/receber_mensagem_ana_local.php';|g" whatsapp-api-server.js
    if grep -q "https://app.pixel12digital.com.br/painel/receber_mensagem_ana_local.php" whatsapp-api-server.js; then
        log_success "Correção aplicada com sucesso"
    else
        log_error "Falha na correção - verificar arquivo manualmente"
        exit 1
    fi
fi

# Verificar se ainda há referências à URL relativa
log_info "Verificando se ainda há referências à URL relativa..."
if grep -q "api/webhook.php" whatsapp-api-server.js; then
    log_warning "Ainda há referências à URL relativa:"
    grep -n "api/webhook.php" whatsapp-api-server.js
    log_info "Aplicando correção global..."
    sed -i "s|api/webhook.php|https://app.pixel12digital.com.br/painel/receber_mensagem_ana_local.php|g" whatsapp-api-server.js
else
    log_success "Nenhuma referência à URL relativa encontrada"
fi

# ETAPA 2: REINICIAR PROCESSOS CORRETAMENTE
echo ""
echo "🔄 ETAPA 2: REINICIANDO PROCESSOS CORRETAMENTE"
echo "=============================================="

log_info "Parando todos os processos WhatsApp..."
pm2 stop whatsapp-3000 whatsapp-3001

log_info "Reiniciando whatsapp-3000 com --update-env..."
pm2 restart whatsapp-3000 --update-env
if [ $? -eq 0 ]; then
    log_success "whatsapp-3000 reiniciado"
else
    log_error "Erro ao reiniciar whatsapp-3000"
fi

log_info "Reiniciando whatsapp-3001 com --update-env..."
pm2 restart whatsapp-3001 --update-env
if [ $? -eq 0 ]; then
    log_success "whatsapp-3001 reiniciado"
else
    log_error "Erro ao reiniciar whatsapp-3001"
fi

log_info "Salvando configuração PM2..."
pm2 save

# ETAPA 3: VERIFICAR STATUS DOS PROCESSOS
echo ""
echo "📊 ETAPA 3: VERIFICANDO STATUS DOS PROCESSOS"
echo "============================================"

log_info "Status atual dos processos:"
pm2 list

# ETAPA 4: ANALISAR LOGS
echo ""
echo "📋 ETAPA 4: ANALISANDO LOGS"
echo "==========================="

log_info "Aguardando 10 segundos para inicialização..."
sleep 10

echo ""
log_info "Logs do whatsapp-3000 (últimas 30 linhas):"
echo "----------------------------------------------"
pm2 logs whatsapp-3000 --lines 30 --nostream

echo ""
log_info "Logs do whatsapp-3001 (últimas 30 linhas):"
echo "----------------------------------------------"
pm2 logs whatsapp-3001 --lines 30 --nostream

# ETAPA 5: TESTAR ENDPOINTS
echo ""
echo "🧪 ETAPA 5: TESTANDO ENDPOINTS"
echo "============================="

log_info "Testando status da porta 3000:"
STATUS_3000=$(curl -s http://127.0.0.1:3000/status)
if [ $? -eq 0 ]; then
    echo "$STATUS_3000" | jq '.' 2>/dev/null || echo "$STATUS_3000"
else
    log_error "Erro ao testar porta 3000"
fi

log_info "Testando status da porta 3001:"
STATUS_3001=$(curl -s http://127.0.0.1:3001/status)
if [ $? -eq 0 ]; then
    echo "$STATUS_3001" | jq '.' 2>/dev/null || echo "$STATUS_3001"
else
    log_error "Erro ao testar porta 3001"
fi

log_info "Testando QR Code da porta 3000:"
QR_3000=$(curl -s "http://127.0.0.1:3000/qr?session=default")
if [ $? -eq 0 ]; then
    echo "$QR_3000" | jq '.' 2>/dev/null || echo "$QR_3000"
else
    log_error "Erro ao testar QR Code da porta 3000"
fi

log_info "Testando QR Code da porta 3001:"
QR_3001=$(curl -s "http://127.0.0.1:3001/qr?session=comercial")
if [ $? -eq 0 ]; then
    echo "$QR_3001" | jq '.' 2>/dev/null || echo "$QR_3001"
else
    log_error "Erro ao testar QR Code da porta 3001"
fi

# ETAPA 6: VERIFICAR PROBLEMAS ESPECÍFICOS
echo ""
echo "🔍 ETAPA 6: VERIFICANDO PROBLEMAS ESPECÍFICOS"
echo "============================================="

# Verificar se há ERR_INVALID_URL nos logs
log_info "Verificando se ainda há ERR_INVALID_URL..."
if pm2 logs whatsapp-3000 --nostream | grep -q "ERR_INVALID_URL"; then
    log_error "whatsapp-3000 ainda tem ERR_INVALID_URL"
    log_info "Verificando linhas problemáticas..."
    pm2 logs whatsapp-3000 --nostream | grep -A5 -B5 "ERR_INVALID_URL"
else
    log_success "whatsapp-3000 sem ERR_INVALID_URL"
fi

if pm2 logs whatsapp-3001 --nostream | grep -q "ERR_INVALID_URL"; then
    log_error "whatsapp-3001 ainda tem ERR_INVALID_URL"
    log_info "Verificando linhas problemáticas..."
    pm2 logs whatsapp-3001 --nostream | grep -A5 -B5 "ERR_INVALID_URL"
else
    log_success "whatsapp-3001 sem ERR_INVALID_URL"
fi

# Verificar se há EADDRINUSE
log_info "Verificando se há conflitos de porta..."
if pm2 logs whatsapp-3000 --nostream | grep -q "EADDRINUSE"; then
    log_warning "whatsapp-3000 tem conflito de porta"
    log_info "Recomendado: alterar para modo 'fork'"
else
    log_success "whatsapp-3000 sem conflito de porta"
fi

if pm2 logs whatsapp-3001 --nostream | grep -q "EADDRINUSE"; then
    log_warning "whatsapp-3001 tem conflito de porta"
    log_info "Recomendado: alterar para modo 'fork'"
else
    log_success "whatsapp-3001 sem conflito de porta"
fi

# RESUMO FINAL
echo ""
echo "🎉 RESUMO DA CORREÇÃO"
echo "====================="
echo ""
echo "✅ AÇÕES REALIZADAS:"
echo "- Verificação da alteração no arquivo"
echo "- Reinicialização correta dos processos"
echo "- Análise completa dos logs"
echo "- Teste dos endpoints"
echo "- Verificação de problemas específicos"
echo ""
echo "📞 PRÓXIMOS PASSOS:"
echo "1. Analisar os logs acima"
echo "2. Se houver ERR_INVALID_URL: verificar arquivo manualmente"
echo "3. Se houver EADDRINUSE: alterar para modo 'fork'"
echo "4. Testar QR Code no painel"
echo ""
echo "✅ CORREÇÃO APLICADA!" 