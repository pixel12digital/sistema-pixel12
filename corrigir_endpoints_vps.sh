#!/bin/bash

# 🔧 CORREÇÃO DE ENDPOINTS - VPS WHATSAPP
# Executar na VPS: ssh root@212.85.11.238 && cd /var/whatsapp-api

echo "🔧 CORREÇÃO DE ENDPOINTS - VPS WHATSAPP"
echo "======================================="
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

# Verificar se estamos no diretório correto
if [ ! -f "whatsapp-api-server.js" ]; then
    log_error "Arquivo whatsapp-api-server.js não encontrado!"
    log_info "Certifique-se de estar no diretório /var/whatsapp-api"
    exit 1
fi

echo "🎯 ETAPA 1: VERIFICAR STATUS ATUAL"
echo "=================================="

# Verificar status do PM2
log_info "Verificando status do PM2..."
pm2 status

# Verificar se os processos estão rodando
if pm2 list | grep -q "whatsapp-3000.*online"; then
    log_success "✅ Processo whatsapp-3000 está online"
else
    log_warning "⚠️ Processo whatsapp-3000 não está online"
fi

if pm2 list | grep -q "whatsapp-3001.*online"; then
    log_success "✅ Processo whatsapp-3001 está online"
else
    log_warning "⚠️ Processo whatsapp-3001 não está online"
fi

echo ""
echo "🔧 ETAPA 2: TESTAR ENDPOINTS"
echo "============================"

# Testar endpoints para cada porta
for porta in 3000 3001; do
    echo ""
    log_info "Testando endpoints da porta $porta..."
    
    # Testar status
    log_info "Testando /status..."
    STATUS_RESPONSE=$(curl -s "http://localhost:$porta/status")
    if [ $? -eq 0 ]; then
        log_success "✅ /status OK"
        echo "   Resposta: $STATUS_RESPONSE"
    else
        log_error "❌ /status falhou"
    fi
    
    # Testar webhook config
    log_info "Testando /webhook/config..."
    WEBHOOK_RESPONSE=$(curl -s "http://localhost:$porta/webhook/config")
    if [ $? -eq 0 ]; then
        log_success "✅ /webhook/config OK"
        echo "   Resposta: $WEBHOOK_RESPONSE"
    else
        log_error "❌ /webhook/config falhou"
    fi
    
    # Configurar webhook
    log_info "Configurando webhook..."
    WEBHOOK_CONFIG=$(curl -s -X POST "http://localhost:$porta/webhook/config" \
        -H "Content-Type: application/json" \
        -d '{"url":"https://app.pixel12digital.com.br/painel/receber_mensagem_ana_local.php"}')
    if [ $? -eq 0 ]; then
        log_success "✅ Webhook configurado"
        echo "   Resposta: $WEBHOOK_CONFIG"
    else
        log_error "❌ Erro ao configurar webhook"
    fi
    
    # Testar envio de mensagem
    log_info "Testando /send/text..."
    SEND_RESPONSE=$(curl -s -X POST "http://localhost:$porta/send/text" \
        -H "Content-Type: application/json" \
        -d '{"sessionName":"default","number":"554796164699","message":"Teste canal '$porta'"}')
    if [ $? -eq 0 ]; then
        log_success "✅ /send/text OK"
        echo "   Resposta: $SEND_RESPONSE"
    else
        log_error "❌ /send/text falhou"
    fi
done

echo ""
echo "🔧 ETAPA 3: VERIFICAR LOGS"
echo "=========================="

# Verificar logs dos processos
log_info "Verificando logs do whatsapp-3000..."
pm2 logs whatsapp-3000 --lines 10 --nostream

log_info "Verificando logs do whatsapp-3001..."
pm2 logs whatsapp-3001 --lines 10 --nostream

echo ""
echo "🔧 ETAPA 4: REINICIAR PROCESSOS (se necessário)"
echo "==============================================="

# Perguntar se deve reiniciar
read -p "Deseja reiniciar os processos PM2? (y/n): " -n 1 -r
echo
if [[ $REPLY =~ ^[Yy]$ ]]; then
    log_info "Reiniciando processos..."
    pm2 restart all
    sleep 5
    pm2 status
else
    log_info "Pulando reinicialização..."
fi

echo ""
echo "🎯 ETAPA 5: TESTE FINAL"
echo "======================="

# Teste final dos endpoints
for porta in 3000 3001; do
    log_info "Teste final - Porta $porta:"
    
    # Status
    if curl -s "http://localhost:$porta/status" | grep -q "ready.*true"; then
        log_success "✅ Status OK"
    else
        log_error "❌ Status falhou"
    fi
    
    # Webhook
    if curl -s "http://localhost:$porta/webhook/config" | grep -q "webhook_url"; then
        log_success "✅ Webhook configurado"
    else
        log_error "❌ Webhook não configurado"
    fi
done

echo ""
echo "✅ CORREÇÃO CONCLUÍDA!"
echo "====================="
echo ""
echo "💡 PRÓXIMOS PASSOS:"
echo "1. Verifique se os endpoints estão funcionando"
echo "2. Teste o envio de mensagens"
echo "3. Configure os webhooks se necessário"
echo "4. Monitore os logs para erros"
echo ""
echo "🔗 URLs de teste:"
echo "   Status: http://212.85.11.238:3000/status"
echo "   Status: http://212.85.11.238:3001/status"
echo "   Webhook: http://212.85.11.238:3000/webhook/config"
echo "   Webhook: http://212.85.11.238:3001/webhook/config" 