#!/bin/bash

# 📊 MONITORAMENTO AUTOMÁTICO - WHATSAPP API
# Executar no VPS: ssh root@212.85.11.238 && cd /var/whatsapp-api
# chmod +x monitoramento_automatico.sh && ./monitoramento_automatico.sh
# Para agendar: crontab -e e adicionar: */5 * * * * /var/whatsapp-api/monitoramento_automatico.sh

echo "📊 MONITORAMENTO AUTOMÁTICO - WHATSAPP API"
echo "=========================================="
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

# Arquivo de log
LOG_FILE="/var/whatsapp-api/logs/monitoramento_$(date +%Y%m%d).log"
mkdir -p /var/whatsapp-api/logs

# Função para log
log_to_file() {
    echo "$(date '+%Y-%m-%d %H:%M:%S') - $1" >> "$LOG_FILE"
}

echo "🔍 ETAPA 1: VERIFICAR STATUS DOS PROCESSOS PM2"
echo "=============================================="

# Verificar se os processos estão rodando
PM2_STATUS=$(pm2 status --no-daemon 2>/dev/null)

if echo "$PM2_STATUS" | grep -q "whatsapp-3000.*online"; then
    log_success "✅ Processo whatsapp-3000 online"
    log_to_file "SUCCESS: Processo whatsapp-3000 online"
else
    log_error "❌ Processo whatsapp-3000 offline ou com erro"
    log_to_file "ERROR: Processo whatsapp-3000 offline"
fi

if echo "$PM2_STATUS" | grep -q "whatsapp-3001.*online"; then
    log_success "✅ Processo whatsapp-3001 online"
    log_to_file "SUCCESS: Processo whatsapp-3001 online"
else
    log_error "❌ Processo whatsapp-3001 offline ou com erro"
    log_to_file "ERROR: Processo whatsapp-3001 offline"
fi

echo ""
echo "🔍 ETAPA 2: VERIFICAR SESSÕES ATIVAS"
echo "===================================="

# Verificar sessões
SESSIONS_3000=$(curl -s http://127.0.0.1:3000/sessions 2>/dev/null)
SESSIONS_3001=$(curl -s http://127.0.0.1:3001/sessions 2>/dev/null)

if echo "$SESSIONS_3000" | jq -e '.sessions[0].status.status' >/dev/null 2>&1; then
    STATUS_3000=$(echo "$SESSIONS_3000" | jq -r '.sessions[0].status.status')
    if [ "$STATUS_3000" = "connected" ]; then
        log_success "✅ Sessão 3000 conectada"
        log_to_file "SUCCESS: Sessão 3000 conectada"
    else
        log_warning "⚠️ Sessão 3000 não conectada (status: $STATUS_3000)"
        log_to_file "WARNING: Sessão 3000 não conectada (status: $STATUS_3000)"
    fi
else
    log_error "❌ Erro ao verificar sessão 3000"
    log_to_file "ERROR: Erro ao verificar sessão 3000"
fi

if echo "$SESSIONS_3001" | jq -e '.sessions[0].status.status' >/dev/null 2>&1; then
    STATUS_3001=$(echo "$SESSIONS_3001" | jq -r '.sessions[0].status.status')
    if [ "$STATUS_3001" = "connected" ]; then
        log_success "✅ Sessão 3001 conectada"
        log_to_file "SUCCESS: Sessão 3001 conectada"
    else
        log_warning "⚠️ Sessão 3001 não conectada (status: $STATUS_3001)"
        log_to_file "WARNING: Sessão 3001 não conectada (status: $STATUS_3001)"
    fi
else
    log_error "❌ Erro ao verificar sessão 3001"
    log_to_file "ERROR: Erro ao verificar sessão 3001"
fi

echo ""
echo "🔍 ETAPA 3: VERIFICAR CONECTIVIDADE EXTERNA"
echo "=========================================="

# Verificar acesso externo
if curl -s --connect-timeout 10 http://212.85.11.238:3000/status >/dev/null 2>&1; then
    log_success "✅ API 3000 acessível externamente"
    log_to_file "SUCCESS: API 3000 acessível externamente"
else
    log_error "❌ API 3000 não acessível externamente"
    log_to_file "ERROR: API 3000 não acessível externamente"
fi

if curl -s --connect-timeout 10 http://212.85.11.238:3001/status >/dev/null 2>&1; then
    log_success "✅ API 3001 acessível externamente"
    log_to_file "SUCCESS: API 3001 acessível externamente"
else
    log_error "❌ API 3001 não acessível externamente"
    log_to_file "ERROR: API 3001 não acessível externamente"
fi

echo ""
echo "🔍 ETAPA 4: VERIFICAR USO DE RECURSOS"
echo "====================================="

# Verificar uso de memória
MEMORY_3000=$(pm2 show whatsapp-3000 --no-daemon 2>/dev/null | grep "memory" | awk '{print $4}')
MEMORY_3001=$(pm2 show whatsapp-3001 --no-daemon 2>/dev/null | grep "memory" | awk '{print $4}')

if [ ! -z "$MEMORY_3000" ]; then
    log_info "📊 Memória whatsapp-3000: $MEMORY_3000"
    log_to_file "INFO: Memória whatsapp-3000: $MEMORY_3000"
else
    log_warning "⚠️ Não foi possível obter uso de memória whatsapp-3000"
    log_to_file "WARNING: Não foi possível obter uso de memória whatsapp-3000"
fi

if [ ! -z "$MEMORY_3001" ]; then
    log_info "📊 Memória whatsapp-3001: $MEMORY_3001"
    log_to_file "INFO: Memória whatsapp-3001: $MEMORY_3001"
else
    log_warning "⚠️ Não foi possível obter uso de memória whatsapp-3001"
    log_to_file "WARNING: Não foi possível obter uso de memória whatsapp-3001"
fi

echo ""
echo "🔍 ETAPA 5: VERIFICAR LOGS DE ERRO"
echo "=================================="

# Verificar erros recentes
ERRORS_3000=$(pm2 logs whatsapp-3000 --lines 20 --nostream 2>/dev/null | grep -i error | wc -l)
ERRORS_3001=$(pm2 logs whatsapp-3001 --lines 20 --nostream 2>/dev/null | grep -i error | wc -l)

if [ "$ERRORS_3000" -eq 0 ]; then
    log_success "✅ Sem erros nos logs whatsapp-3000"
    log_to_file "SUCCESS: Sem erros nos logs whatsapp-3000"
else
    log_warning "⚠️ $ERRORS_3000 erros encontrados nos logs whatsapp-3000"
    log_to_file "WARNING: $ERRORS_3000 erros encontrados nos logs whatsapp-3000"
fi

if [ "$ERRORS_3001" -eq 0 ]; then
    log_success "✅ Sem erros nos logs whatsapp-3001"
    log_to_file "SUCCESS: Sem erros nos logs whatsapp-3001"
else
    log_warning "⚠️ $ERRORS_3001 erros encontrados nos logs whatsapp-3001"
    log_to_file "WARNING: $ERRORS_3001 erros encontrados nos logs whatsapp-3001"
fi

echo ""
echo "📋 RESUMO DO MONITORAMENTO"
echo "========================="

# Contar problemas
PROBLEMS=0

# Verificar processos
if ! echo "$PM2_STATUS" | grep -q "whatsapp-3000.*online"; then
    PROBLEMS=$((PROBLEMS + 1))
fi
if ! echo "$PM2_STATUS" | grep -q "whatsapp-3001.*online"; then
    PROBLEMS=$((PROBLEMS + 1))
fi

# Verificar sessões
if ! echo "$SESSIONS_3000" | jq -e '.sessions[0].status.status' >/dev/null 2>&1 || \
   [ "$(echo "$SESSIONS_3000" | jq -r '.sessions[0].status.status')" != "connected" ]; then
    PROBLEMS=$((PROBLEMS + 1))
fi
if ! echo "$SESSIONS_3001" | jq -e '.sessions[0].status.status' >/dev/null 2>&1 || \
   [ "$(echo "$SESSIONS_3001" | jq -r '.sessions[0].status.status')" != "connected" ]; then
    PROBLEMS=$((PROBLEMS + 1))
fi

# Verificar conectividade externa
if ! curl -s --connect-timeout 10 http://212.85.11.238:3000/status >/dev/null 2>&1; then
    PROBLEMS=$((PROBLEMS + 1))
fi
if ! curl -s --connect-timeout 10 http://212.85.11.238:3001/status >/dev/null 2>&1; then
    PROBLEMS=$((PROBLEMS + 1))
fi

if [ "$PROBLEMS" -eq 0 ]; then
    log_success "🎉 SISTEMA 100% OPERACIONAL!"
    log_to_file "SUCCESS: Sistema 100% operacional"
    echo "✅ Todos os componentes funcionando corretamente"
    echo "✅ Nenhum problema detectado"
else
    log_error "❌ $PROBLEMS PROBLEMAS DETECTADOS!"
    log_to_file "ERROR: $PROBLEMS problemas detectados"
    echo "🔧 Ações recomendadas:"
    echo "1. Verifique os logs: pm2 logs whatsapp-3000 --lines 50"
    echo "2. Reinicie os processos: pm2 restart all"
    echo "3. Verifique conectividade de rede"
    echo "4. Execute o teste: ./teste_final_producao.sh"
fi

echo ""
echo "📊 ESTATÍSTICAS:"
echo "================"
echo "Data/Hora: $(date)"
echo "Problemas detectados: $PROBLEMS"
echo "Log salvo em: $LOG_FILE"

# Manter apenas os últimos 7 dias de logs
find /var/whatsapp-api/logs -name "monitoramento_*.log" -mtime +7 -delete 2>/dev/null

echo ""
log_success "📊 MONITORAMENTO AUTOMÁTICO FINALIZADO!" 