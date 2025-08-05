#!/bin/bash

# 🔧 APLICAR CONFIGURAÇÃO CORRETA PM2
# Executar: ssh root@212.85.11.238 && cd /var/whatsapp-api && chmod +x aplicar_configuracao_correta.sh && ./aplicar_configuracao_correta.sh

echo "🔧 APLICAR CONFIGURAÇÃO CORRETA PM2"
echo "==================================="
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

# ETAPA 1: PARAR PROCESSOS ATUAIS
echo "🛑 ETAPA 1: PARANDO PROCESSOS ATUAIS"
echo "===================================="

log_info "Parando todos os processos WhatsApp..."
pm2 stop whatsapp-3000 whatsapp-3001
pm2 delete whatsapp-3000 whatsapp-3001

# ETAPA 2: CRIAR DIRETÓRIO DE LOGS
echo ""
echo "📁 ETAPA 2: CRIANDO DIRETÓRIO DE LOGS"
echo "===================================="

log_info "Criando diretório de logs..."
mkdir -p logs
if [ $? -eq 0 ]; then
    log_success "Diretório de logs criado"
else
    log_warning "Erro ao criar diretório de logs"
fi

# ETAPA 3: VERIFICAR ARQUIVO DE CONFIGURAÇÃO
echo ""
echo "📋 ETAPA 3: VERIFICANDO ARQUIVO DE CONFIGURAÇÃO"
echo "=============================================="

if [ -f "ecosystem.config.js" ]; then
    log_success "Arquivo ecosystem.config.js encontrado"
    log_info "Conteúdo do arquivo:"
    cat ecosystem.config.js
else
    log_error "Arquivo ecosystem.config.js não encontrado"
    exit 1
fi

# ETAPA 4: INICIAR COM CONFIGURAÇÃO CORRETA
echo ""
echo "🚀 ETAPA 4: INICIANDO COM CONFIGURAÇÃO CORRETA"
echo "============================================="

log_info "Iniciando processos com ecosystem.config.js..."
pm2 start ecosystem.config.js

if [ $? -eq 0 ]; then
    log_success "Processos iniciados com sucesso"
else
    log_error "Erro ao iniciar processos"
    exit 1
fi

# ETAPA 5: VERIFICAR STATUS
echo ""
echo "📊 ETAPA 5: VERIFICANDO STATUS"
echo "============================="

log_info "Status dos processos:"
pm2 list

# ETAPA 6: SALVAR CONFIGURAÇÃO
echo ""
echo "💾 ETAPA 6: SALVANDO CONFIGURAÇÃO"
echo "================================"

log_info "Salvando configuração PM2..."
pm2 save

if [ $? -eq 0 ]; then
    log_success "Configuração salva"
else
    log_warning "Erro ao salvar configuração"
fi

# ETAPA 7: AGUARDAR INICIALIZAÇÃO
echo ""
echo "⏳ ETAPA 7: AGUARDANDO INICIALIZAÇÃO"
echo "==================================="

log_info "Aguardando 15 segundos para inicialização completa..."
sleep 15

# ETAPA 8: VERIFICAR LOGS
echo ""
echo "📋 ETAPA 8: VERIFICANDO LOGS"
echo "============================"

log_info "Logs do whatsapp-3000 (últimas 20 linhas):"
echo "----------------------------------------------"
pm2 logs whatsapp-3000 --lines 20 --nostream

echo ""
log_info "Logs do whatsapp-3001 (últimas 20 linhas):"
echo "----------------------------------------------"
pm2 logs whatsapp-3001 --lines 20 --nostream

# ETAPA 9: TESTAR ENDPOINTS
echo ""
echo "🧪 ETAPA 9: TESTANDO ENDPOINTS"
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

# ETAPA 10: VERIFICAR PROBLEMAS
echo ""
echo "🔍 ETAPA 10: VERIFICANDO PROBLEMAS"
echo "=================================="

# Verificar ERR_INVALID_URL
log_info "Verificando ERR_INVALID_URL..."
if pm2 logs whatsapp-3000 --nostream | grep -q "ERR_INVALID_URL"; then
    log_error "whatsapp-3000 ainda tem ERR_INVALID_URL"
else
    log_success "whatsapp-3000 sem ERR_INVALID_URL"
fi

if pm2 logs whatsapp-3001 --nostream | grep -q "ERR_INVALID_URL"; then
    log_error "whatsapp-3001 ainda tem ERR_INVALID_URL"
else
    log_success "whatsapp-3001 sem ERR_INVALID_URL"
fi

# Verificar EADDRINUSE
log_info "Verificando conflitos de porta..."
if pm2 logs whatsapp-3000 --nostream | grep -q "EADDRINUSE"; then
    log_error "whatsapp-3000 ainda tem conflito de porta"
else
    log_success "whatsapp-3000 sem conflito de porta"
fi

if pm2 logs whatsapp-3001 --nostream | grep -q "EADDRINUSE"; then
    log_error "whatsapp-3001 ainda tem conflito de porta"
else
    log_success "whatsapp-3001 sem conflito de porta"
fi

# RESUMO FINAL
echo ""
echo "🎉 RESUMO DA CONFIGURAÇÃO"
echo "========================="
echo ""
echo "✅ CONFIGURAÇÃO APLICADA:"
echo "- Modo 'fork' em vez de 'cluster'"
echo "- Apenas 1 instância por processo"
echo "- Logs organizados em diretório separado"
echo "- Configuração PM2 persistida"
echo ""
echo "📞 PRÓXIMOS PASSOS:"
echo "1. Verificar se os logs estão limpos"
echo "2. Testar QR Code no painel"
echo "3. Se ainda houver problemas, verificar arquivo manualmente"
echo ""
echo "✅ CONFIGURAÇÃO APLICADA COM SUCESSO!" 