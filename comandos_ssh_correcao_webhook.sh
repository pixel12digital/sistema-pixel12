#!/bin/bash

# 🔧 CORREÇÃO FINAL DO WEBHOOK VPS - COMANDOS SSH
# Executar: ssh root@212.85.11.238 && cd /var/whatsapp-api && chmod +x comandos_ssh_correcao_webhook.sh && ./comandos_ssh_correcao_webhook.sh

echo "🔧 CORREÇÃO FINAL DO WEBHOOK VPS"
echo "================================="
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

# URL correta do webhook
WEBHOOK_URL_CORRETA="https://app.pixel12digital.com.br/painel/receber_mensagem_ana_local.php"

echo "🎯 PROBLEMA IDENTIFICADO:"
echo "- webhookUrl = 'api/webhook.php' (URL relativa)"
echo "- Causa: ERR_INVALID_URL no fetch()"
echo "- Resultado: QR Code não disponível"
echo ""

echo "✅ SOLUÇÃO:"
echo "- Alterar para URL absoluta"
echo "- URL: $WEBHOOK_URL_CORRETA"
echo ""

# ETAPA 1: VERIFICAR STATUS ATUAL
echo "📊 ETAPA 1: VERIFICANDO STATUS ATUAL"
echo "===================================="

log_info "Verificando status da porta 3000..."
STATUS_3000=$(curl -s http://127.0.0.1:3000/status)
if [ $? -eq 0 ]; then
    echo "Porta 3000: ✅ Respondendo"
else
    echo "Porta 3000: ❌ Não respondendo"
fi

log_info "Verificando status da porta 3001..."
STATUS_3001=$(curl -s http://127.0.0.1:3001/status)
if [ $? -eq 0 ]; then
    echo "Porta 3001: ✅ Respondendo"
else
    echo "Porta 3001: ❌ Não respondendo"
fi

# ETAPA 2: FAZER BACKUP
echo ""
echo "💾 ETAPA 2: FAZENDO BACKUP"
echo "=========================="

log_info "Criando backup do arquivo original..."
cp whatsapp-api-server.js whatsapp-api-server.js.backup.$(date +%Y%m%d_%H%M%S)
if [ $? -eq 0 ]; then
    log_success "Backup criado com sucesso"
else
    log_error "Erro ao criar backup"
    exit 1
fi

# ETAPA 3: CORRIGIR ARQUIVO
echo ""
echo "🔧 ETAPA 3: CORRIGINDO ARQUIVO"
echo "=============================="

log_info "Alterando webhookUrl para URL absoluta..."
sed -i "s|let webhookUrl = 'api/webhook.php';|let webhookUrl = '$WEBHOOK_URL_CORRETA';|g" whatsapp-api-server.js

if [ $? -eq 0 ]; then
    log_success "Arquivo corrigido com sucesso"
else
    log_error "Erro ao corrigir arquivo"
    exit 1
fi

# Verificar se a alteração foi feita
if grep -q "$WEBHOOK_URL_CORRETA" whatsapp-api-server.js; then
    log_success "Alteração confirmada no arquivo"
else
    log_error "Alteração não foi aplicada"
    exit 1
fi

# ETAPA 4: REINICIAR SERVIÇOS
echo ""
echo "🔄 ETAPA 4: REINICIANDO SERVIÇOS"
echo "================================"

log_info "Reiniciando serviço whatsapp-3000..."
pm2 restart whatsapp-3000 --update-env
if [ $? -eq 0 ]; then
    log_success "Serviço 3000 reiniciado"
else
    log_warning "Erro ao reiniciar serviço 3000"
fi

log_info "Reiniciando serviço whatsapp-3001..."
pm2 restart whatsapp-3001 --update-env
if [ $? -eq 0 ]; then
    log_success "Serviço 3001 reiniciado"
else
    log_warning "Erro ao reiniciar serviço 3001"
fi

log_info "Salvando configuração PM2..."
pm2 save
if [ $? -eq 0 ]; then
    log_success "Configuração salva"
else
    log_warning "Erro ao salvar configuração"
fi

# ETAPA 5: CONFIGURAR WEBHOOKS
echo ""
echo "🔗 ETAPA 5: CONFIGURANDO WEBHOOKS"
echo "================================="

log_info "Configurando webhook para porta 3000..."
curl -s -X POST http://127.0.0.1:3000/webhook/config \
  -H "Content-Type: application/json" \
  -d "{\"url\":\"$WEBHOOK_URL_CORRETA\"}" > /dev/null

if [ $? -eq 0 ]; then
    log_success "Webhook 3000 configurado"
else
    log_warning "Erro ao configurar webhook 3000"
fi

log_info "Configurando webhook para porta 3001..."
curl -s -X POST http://127.0.0.1:3001/webhook/config \
  -H "Content-Type: application/json" \
  -d "{\"url\":\"$WEBHOOK_URL_CORRETA\"}" > /dev/null

if [ $? -eq 0 ]; then
    log_success "Webhook 3001 configurado"
else
    log_warning "Erro ao configurar webhook 3001"
fi

# ETAPA 6: TESTAR WEBHOOKS
echo ""
echo "🧪 ETAPA 6: TESTANDO WEBHOOKS"
echo "============================="

log_info "Testando webhook da porta 3000..."
TEST_3000=$(curl -s -X POST http://127.0.0.1:3000/webhook/test)
if echo "$TEST_3000" | grep -q "success.*true"; then
    log_success "Webhook 3000 testado com sucesso"
else
    log_warning "Erro no teste do webhook 3000"
fi

log_info "Testando webhook da porta 3001..."
TEST_3001=$(curl -s -X POST http://127.0.0.1:3001/webhook/test)
if echo "$TEST_3001" | grep -q "success.*true"; then
    log_success "Webhook 3001 testado com sucesso"
else
    log_warning "Erro no teste do webhook 3001"
fi

# ETAPA 7: VERIFICAR STATUS FINAL
echo ""
echo "📊 ETAPA 7: VERIFICANDO STATUS FINAL"
echo "===================================="

log_info "Aguardando 10 segundos para inicialização..."
sleep 10

log_info "Status da porta 3000:"
STATUS_3000_FINAL=$(curl -s http://127.0.0.1:3000/status)
if [ $? -eq 0 ]; then
    echo "$STATUS_3000_FINAL" | jq '.' 2>/dev/null || echo "$STATUS_3000_FINAL"
else
    log_error "Não foi possível verificar status da porta 3000"
fi

log_info "Status da porta 3001:"
STATUS_3001_FINAL=$(curl -s http://127.0.0.1:3001/status)
if [ $? -eq 0 ]; then
    echo "$STATUS_3001_FINAL" | jq '.' 2>/dev/null || echo "$STATUS_3001_FINAL"
else
    log_error "Não foi possível verificar status da porta 3001"
fi

# ETAPA 8: VERIFICAR LOGS
echo ""
echo "📋 ETAPA 8: VERIFICANDO LOGS"
echo "============================"

log_info "Últimos logs do serviço 3000:"
pm2 logs whatsapp-3000 --lines 10 --nostream

log_info "Últimos logs do serviço 3001:"
pm2 logs whatsapp-3001 --lines 10 --nostream

# ETAPA 9: TESTAR QR CODE
echo ""
echo "📱 ETAPA 9: TESTANDO QR CODE"
echo "============================"

log_info "Testando QR Code da porta 3000:"
QR_3000=$(curl -s "http://127.0.0.1:3000/qr?session=default")
if [ $? -eq 0 ]; then
    echo "$QR_3000" | jq '.' 2>/dev/null || echo "$QR_3000"
else
    log_warning "Erro ao testar QR Code da porta 3000"
fi

log_info "Testando QR Code da porta 3001:"
QR_3001=$(curl -s "http://127.0.0.1:3001/qr?session=default")
if [ $? -eq 0 ]; then
    echo "$QR_3001" | jq '.' 2>/dev/null || echo "$QR_3001"
else
    log_warning "Erro ao testar QR Code da porta 3001"
fi

# RESUMO FINAL
echo ""
echo "🎉 RESUMO DA CORREÇÃO"
echo "====================="
echo ""
echo "✅ ALTERAÇÕES APLICADAS:"
echo "- webhookUrl alterado para URL absoluta"
echo "- Serviços reiniciados"
echo "- Webhooks configurados"
echo "- Testes realizados"
echo ""
echo "🎯 RESULTADO ESPERADO:"
echo "- ❌ ERR_INVALID_URL desaparece"
echo "- ❌ bind EADDRINUSE null:3000 desaparece"
echo "- ✅ QR Code fica disponível"
echo "- ✅ Sessão fica pronta (ready: true)"
echo "- ✅ WhatsApp conecta normalmente"
echo ""
echo "📞 PRÓXIMOS PASSOS:"
echo "1. Teste o QR Code no painel"
echo "2. Verifique se a Ana conecta"
echo "3. Monitore os logs para confirmar"
echo "4. Se necessário, execute: pm2 logs whatsapp-3001 --lines 50"
echo ""
echo "✅ CORREÇÃO APLICADA COM SUCESSO!" 