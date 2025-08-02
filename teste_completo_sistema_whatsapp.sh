#!/bin/bash

# 🧪 SCRIPT DE TESTE COMPLETO - SISTEMA WHATSAPP MULTI-CANAL
# Executar no VPS via SSH: ssh root@212.85.11.238
# cd /var/whatsapp-api
# chmod +x teste_completo_sistema_whatsapp.sh
# ./teste_completo_sistema_whatsapp.sh

echo "🚀 INICIANDO TESTE COMPLETO DO SISTEMA WHATSAPP MULTI-CANAL"
echo "=========================================================="
echo ""

# Cores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Função para log colorido
log_info() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

log_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

log_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

log_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Função para verificar se comando existe
command_exists() {
    command -v "$1" >/dev/null 2>&1
}

# Verificar dependências
log_info "Verificando dependências..."
if ! command_exists pm2; then
    log_error "PM2 não encontrado. Instale com: npm install -g pm2"
    exit 1
fi

if ! command_exists curl; then
    log_error "curl não encontrado. Instale com: apt-get install curl"
    exit 1
fi

if ! command_exists jq; then
    log_warning "jq não encontrado. Instalando..."
    apt-get update && apt-get install -y jq
fi

log_success "Dependências verificadas!"

echo ""
echo "📋 ETAPA 1: VERIFICAÇÃO DE STATUS DOS PROCESSOS"
echo "================================================"

# Verificar status PM2
log_info "Verificando status dos processos PM2..."
pm2 status

# Verificar se os processos estão rodando
if pm2 list | grep -q "whatsapp-3000.*online"; then
    log_success "Processo whatsapp-3000 (default) está ONLINE"
else
    log_error "Processo whatsapp-3000 (default) está OFFLINE"
fi

if pm2 list | grep -q "whatsapp-3001.*online"; then
    log_success "Processo whatsapp-3001 (comercial) está ONLINE"
else
    log_error "Processo whatsapp-3001 (comercial) está OFFLINE"
fi

echo ""
echo "📊 ETAPA 2: VERIFICAÇÃO DE LOGS E DEBUG"
echo "======================================="

# Verificar logs recentes
log_info "Verificando logs da sessão default (3000)..."
pm2 logs whatsapp-3000 --lines 10 | grep -E "(DEBUG|sessionName|ready|connected)"

log_info "Verificando logs da sessão comercial (3001)..."
pm2 logs whatsapp-3001 --lines 10 | grep -E "(DEBUG|sessionName|ready|connected)"

echo ""
echo "🔍 ETAPA 3: TESTE DE ENDPOINTS QR CODE"
echo "======================================"

# Testar endpoint QR interno
log_info "Testando QR Code interno - Sessão default (3000)..."
QR_DEFAULT_INTERNAL=$(curl -s http://127.0.0.1:3000/qr?session=default)
if echo "$QR_DEFAULT_INTERNAL" | jq -e '.qr' >/dev/null 2>&1; then
    log_success "QR Code default interno OK"
    echo "$QR_DEFAULT_INTERNAL" | jq '.qr' | head -c 50
    echo "..."
else
    log_error "QR Code default interno FALHOU"
    echo "Resposta: $QR_DEFAULT_INTERNAL"
fi

log_info "Testando QR Code interno - Sessão comercial (3001)..."
QR_COMERCIAL_INTERNAL=$(curl -s http://127.0.0.1:3001/qr?session=comercial)
if echo "$QR_COMERCIAL_INTERNAL" | jq -e '.qr' >/dev/null 2>&1; then
    log_success "QR Code comercial interno OK"
    echo "$QR_COMERCIAL_INTERNAL" | jq '.qr' | head -c 50
    echo "..."
else
    log_error "QR Code comercial interno FALHOU"
    echo "Resposta: $QR_COMERCIAL_INTERNAL"
fi

# Testar endpoint QR externo
log_info "Testando QR Code externo - Sessão default (3000)..."
QR_DEFAULT_EXTERNAL=$(curl -s http://212.85.11.238:3000/qr?session=default)
if echo "$QR_DEFAULT_EXTERNAL" | jq -e '.qr' >/dev/null 2>&1; then
    log_success "QR Code default externo OK"
else
    log_error "QR Code default externo FALHOU"
    echo "Resposta: $QR_DEFAULT_EXTERNAL"
fi

log_info "Testando QR Code externo - Sessão comercial (3001)..."
QR_COMERCIAL_EXTERNAL=$(curl -s http://212.85.11.238:3001/qr?session=comercial)
if echo "$QR_COMERCIAL_EXTERNAL" | jq -e '.qr' >/dev/null 2>&1; then
    log_success "QR Code comercial externo OK"
else
    log_error "QR Code comercial externo FALHOU"
    echo "Resposta: $QR_COMERCIAL_EXTERNAL"
fi

echo ""
echo "📡 ETAPA 4: TESTE DE CONECTIVIDADE DE PORTAS"
echo "============================================"

# Verificar se as portas estão abertas
log_info "Verificando se as portas estão abertas..."
if netstat -tlnp | grep -q ":3000 "; then
    log_success "Porta 3000 está aberta e escutando"
else
    log_error "Porta 3000 não está aberta"
fi

if netstat -tlnp | grep -q ":3001 "; then
    log_success "Porta 3001 está aberta e escutando"
else
    log_error "Porta 3001 não está aberta"
fi

echo ""
echo "🔧 ETAPA 5: TESTE DE ENDPOINTS DE STATUS"
echo "========================================"

# Testar endpoint de status
log_info "Testando endpoint de status - Sessão default..."
STATUS_DEFAULT=$(curl -s http://127.0.0.1:3000/status)
if echo "$STATUS_DEFAULT" | jq -e '.ready' >/dev/null 2>&1; then
    READY_DEFAULT=$(echo "$STATUS_DEFAULT" | jq -r '.ready')
    if [ "$READY_DEFAULT" = "true" ]; then
        log_success "Sessão default está CONECTADA (ready=true)"
    else
        log_warning "Sessão default está PENDENTE (ready=false)"
    fi
else
    log_error "Endpoint de status default FALHOU"
    echo "Resposta: $STATUS_DEFAULT"
fi

log_info "Testando endpoint de status - Sessão comercial..."
STATUS_COMERCIAL=$(curl -s http://127.0.0.1:3001/status)
if echo "$STATUS_COMERCIAL" | jq -e '.ready' >/dev/null 2>&1; then
    READY_COMERCIAL=$(echo "$STATUS_COMERCIAL" | jq -r '.ready')
    if [ "$READY_COMERCIAL" = "true" ]; then
        log_success "Sessão comercial está CONECTADA (ready=true)"
    else
        log_warning "Sessão comercial está PENDENTE (ready=false)"
    fi
else
    log_error "Endpoint de status comercial FALHOU"
    echo "Resposta: $STATUS_COMERCIAL"
fi

echo ""
echo "📁 ETAPA 6: VERIFICAÇÃO DE ESTRUTURA DE ARQUIVOS"
echo "================================================"

# Verificar estrutura de diretórios
log_info "Verificando estrutura de diretórios..."

if [ -d "sessions" ]; then
    log_success "Diretório sessions existe"
    if [ -d "sessions/default" ]; then
        log_success "Diretório sessions/default existe"
    else
        log_warning "Diretório sessions/default não existe"
    fi
    if [ -d "sessions/comercial" ]; then
        log_success "Diretório sessions/comercial existe"
    else
        log_warning "Diretório sessions/comercial não existe"
    fi
else
    log_error "Diretório sessions não existe"
fi

if [ -d "logs" ]; then
    log_success "Diretório logs existe"
    ls -la logs/ | grep -E "(3000|3001)"
else
    log_error "Diretório logs não existe"
fi

echo ""
echo "🔄 ETAPA 7: TESTE DE WEBHOOK E CONECTIVIDADE"
echo "============================================"

# Verificar se o webhook está configurado
log_info "Verificando configuração de webhook..."
if grep -q "webhookUrl" whatsapp-api-server.js; then
    log_success "Webhook configurado no código"
else
    log_error "Webhook não encontrado no código"
fi

# Testar conectividade com o painel
log_info "Testando conectividade com o painel..."
PANEL_RESPONSE=$(curl -s -o /dev/null -w "%{http_code}" http://212.85.11.238:8080/painel/)
if [ "$PANEL_RESPONSE" = "200" ]; then
    log_success "Painel acessível (HTTP 200)"
else
    log_warning "Painel retornou HTTP $PANEL_RESPONSE"
fi

echo ""
echo "📊 RESUMO FINAL"
echo "==============="

echo ""
echo "✅ CRITÉRIOS DE SUCESSO:"
echo ""

# Contadores
SUCCESS_COUNT=0
TOTAL_TESTS=0

# Verificar processos online
TOTAL_TESTS=$((TOTAL_TESTS + 2))
if pm2 list | grep -q "whatsapp-3000.*online"; then
    echo "✔️ Processo whatsapp-3000 (default) está ONLINE"
    SUCCESS_COUNT=$((SUCCESS_COUNT + 1))
else
    echo "❌ Processo whatsapp-3000 (default) está OFFLINE"
fi

if pm2 list | grep -q "whatsapp-3001.*online"; then
    echo "✔️ Processo whatsapp-3001 (comercial) está ONLINE"
    SUCCESS_COUNT=$((SUCCESS_COUNT + 1))
else
    echo "❌ Processo whatsapp-3001 (comercial) está OFFLINE"
fi

# Verificar QR Codes
TOTAL_TESTS=$((TOTAL_TESTS + 4))
if echo "$QR_DEFAULT_INTERNAL" | jq -e '.qr' >/dev/null 2>&1; then
    echo "✔️ QR Code default interno funciona"
    SUCCESS_COUNT=$((SUCCESS_COUNT + 1))
else
    echo "❌ QR Code default interno falhou"
fi

if echo "$QR_COMERCIAL_INTERNAL" | jq -e '.qr' >/dev/null 2>&1; then
    echo "✔️ QR Code comercial interno funciona"
    SUCCESS_COUNT=$((SUCCESS_COUNT + 1))
else
    echo "❌ QR Code comercial interno falhou"
fi

if echo "$QR_DEFAULT_EXTERNAL" | jq -e '.qr' >/dev/null 2>&1; then
    echo "✔️ QR Code default externo funciona"
    SUCCESS_COUNT=$((SUCCESS_COUNT + 1))
else
    echo "❌ QR Code default externo falhou"
fi

if echo "$QR_COMERCIAL_EXTERNAL" | jq -e '.qr' >/dev/null 2>&1; then
    echo "✔️ QR Code comercial externo funciona"
    SUCCESS_COUNT=$((SUCCESS_COUNT + 1))
else
    echo "❌ QR Code comercial externo falhou"
fi

# Verificar portas
TOTAL_TESTS=$((TOTAL_TESTS + 2))
if netstat -tlnp | grep -q ":3000 "; then
    echo "✔️ Porta 3000 está aberta"
    SUCCESS_COUNT=$((SUCCESS_COUNT + 1))
else
    echo "❌ Porta 3000 não está aberta"
fi

if netstat -tlnp | grep -q ":3001 "; then
    echo "✔️ Porta 3001 está aberta"
    SUCCESS_COUNT=$((SUCCESS_COUNT + 1))
else
    echo "❌ Porta 3001 não está aberta"
fi

# Verificar status de conexão
TOTAL_TESTS=$((TOTAL_TESTS + 2))
if [ "$READY_DEFAULT" = "true" ]; then
    echo "✔️ Sessão default está CONECTADA"
    SUCCESS_COUNT=$((SUCCESS_COUNT + 1))
else
    echo "⚠️ Sessão default está PENDENTE (escanear QR)"
fi

if [ "$READY_COMERCIAL" = "true" ]; then
    echo "✔️ Sessão comercial está CONECTADA"
    SUCCESS_COUNT=$((SUCCESS_COUNT + 1))
else
    echo "⚠️ Sessão comercial está PENDENTE (escanear QR)"
fi

echo ""
echo "📈 RESULTADO FINAL: $SUCCESS_COUNT/$TOTAL_TESTS testes passaram"
echo ""

if [ $SUCCESS_COUNT -eq $TOTAL_TESTS ]; then
    log_success "🎉 TODOS OS TESTES PASSARAM! Sistema 100% operacional!"
    echo ""
    echo "📋 PRÓXIMOS PASSOS:"
    echo "1. Acesse o painel: http://212.85.11.238:8080/painel/comunicacao.php"
    echo "2. Verifique o status dos canais (verde = conectado, amarelo = pendente)"
    echo "3. Se pendente, clique em 'Conectar' e escaneie o QR Code"
    echo "4. Teste envio/recebimento de mensagens no chat central"
    echo "5. Monitore os logs: pm2 logs whatsapp-3000 --follow"
    echo ""
else
    log_warning "⚠️ ALGUNS TESTES FALHARAM. Verifique os logs acima."
    echo ""
    echo "🔧 AÇÕES CORRETIVAS:"
    echo "1. Verifique se PM2 está rodando: pm2 status"
    echo "2. Reinicie os processos: pm2 restart all"
    echo "3. Verifique logs de erro: pm2 logs --err"
    echo "4. Verifique firewall: ufw status"
    echo "5. Execute novamente este script após correções"
    echo ""
fi

echo "🧪 TESTE COMPLETO FINALIZADO!"
echo "==========================================================" 