#!/bin/bash

# 🔧 APLICAR CORREÇÃO - VPS WHATSAPP
# Executar na VPS: ssh root@212.85.11.238 && cd /var/whatsapp-api

echo "🔧 APLICANDO CORREÇÃO - VPS WHATSAPP"
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

# Verificar se estamos no diretório correto
if [ ! -f "whatsapp-api-server.js" ]; then
    log_error "Arquivo whatsapp-api-server.js não encontrado!"
    log_info "Certifique-se de estar no diretório /var/whatsapp-api"
    exit 1
fi

echo "🎯 ETAPA 1: BACKUP DO ARQUIVO ATUAL"
echo "==================================="

# Fazer backup do arquivo atual
BACKUP_FILE="whatsapp-api-server.js.backup.$(date +%Y%m%d_%H%M%S)"
cp whatsapp-api-server.js "$BACKUP_FILE"
log_success "✅ Backup criado: $BACKUP_FILE"

echo ""
echo "🔧 ETAPA 2: APLICAR CORREÇÃO"
echo "============================"

# Parar processos
log_info "Parando processos PM2..."
pm2 stop all

# Limpar processos
log_info "Limpando processos..."
pkill -f "node.*whatsapp"

# Aplicar correção - substituir o arquivo
log_info "Aplicando correção..."
cp corrigir_servidor_whatsapp.js whatsapp-api-server.js
log_success "✅ Correção aplicada"

# Reiniciar
log_info "Reiniciando processos..."
pm2 start ecosystem.config.js

# Aguardar inicialização
sleep 15

# Verificar status
log_info "Verificando status..."
pm2 status

echo ""
echo "🧪 ETAPA 3: TESTAR ENDPOINTS"
echo "============================"

# Testar endpoints
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
echo "✅ CORREÇÃO APLICADA COM SUCESSO!"
echo "================================"
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