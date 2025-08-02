#!/bin/bash

# Script para corrigir inicialização das sessões na VPS
echo "🔧 CORRIGINDO INICIALIZAÇÃO DAS SESSÕES"
echo "======================================"
echo ""

# Cores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Função para log colorido
log_info() {
    echo -e "${BLUE}ℹ️  $1${NC}"
}

log_success() {
    echo -e "${GREEN}✅ $1${NC}"
}

log_warning() {
    echo -e "${YELLOW}⚠️  $1${NC}"
}

log_error() {
    echo -e "${RED}❌ $1${NC}"
}

echo "📋 PASSO 1: PARAR PROCESSOS"
echo "---------------------------"
log_info "Parando processos WhatsApp..."
pm2 stop whatsapp-3000 whatsapp-3001 2>/dev/null && log_success "Processos parados" || log_warning "Erro ao parar processos"

echo ""
echo "📋 PASSO 2: APLICAR CORREÇÃO NO CÓDIGO"
echo "--------------------------------------"
log_info "Aplicando correção na inicialização das sessões..."

# Fazer backup do arquivo original
cp /var/whatsapp-api/whatsapp-api-server.js /var/whatsapp-api/whatsapp-api-server.js.backup.$(date +%Y%m%d_%H%M%S)

# Aplicar correção usando sed
sed -i 's/console.log('\''\\n🔄 Inicializando sessão padrão...'\'');/console.log(`\\n🔄 Inicializando sessão: ${sessionName} (porta ${PORT})`);/' /var/whatsapp-api/whatsapp-api-server.js
sed -i 's/initializeWhatsApp('\''default'\'').catch(console.error);/let sessionName = '\''default'\''; \/\/ Padrão para porta 3000\n        \n        \/\/ Determinar sessão baseada na porta\n        if (PORT === 3001) {\n            sessionName = '\''comercial'\''; \/\/ Para porta 3001\n        }\n        \n        initializeWhatsApp(sessionName).catch(console.error);/' /var/whatsapp-api/whatsapp-api-server.js

log_success "Correção aplicada no código"

echo ""
echo "📋 PASSO 3: REINICIAR PROCESSOS"
echo "-------------------------------"
log_info "Reiniciando processos com correção..."
pm2 start whatsapp-3000 whatsapp-3001 2>/dev/null && log_success "Processos iniciados" || log_error "Erro ao iniciar processos"

echo ""
echo "📋 PASSO 4: AGUARDAR INICIALIZAÇÃO"
echo "----------------------------------"
log_info "Aguardando 15 segundos para inicialização..."
sleep 15

echo ""
echo "📋 PASSO 5: VERIFICAR LOGS"
echo "--------------------------"
log_info "Verificando logs de inicialização..."

echo "whatsapp-3000 (deve inicializar 'default'):"
pm2 logs whatsapp-3000 --lines 10 --nostream | grep -E "(Inicializando sessão|default)"

echo ""
echo "whatsapp-3001 (deve inicializar 'comercial'):"
pm2 logs whatsapp-3001 --lines 10 --nostream | grep -E "(Inicializando sessão|comercial)"

echo ""
echo "📋 PASSO 6: TESTAR QR CODES"
echo "---------------------------"
log_info "Testando QR Codes após correção..."

echo "QR Default (3000):"
curl -s http://212.85.11.238:3000/qr?session=default | jq . 2>/dev/null || curl -s http://212.85.11.238:3000/qr?session=default

echo ""
echo "QR Comercial (3001):"
curl -s http://212.85.11.238:3001/qr?session=comercial | jq . 2>/dev/null || curl -s http://212.85.11.238:3001/qr?session=comercial

echo ""
log_success "Correção concluída!"
echo ""
log_info "Próximos passos:"
echo "1. Verifique se os logs mostram a sessão correta sendo inicializada"
echo "2. Teste os QR Codes no painel administrativo"
echo "3. Se necessário, execute: pm2 logs whatsapp-3001 --lines 0" 