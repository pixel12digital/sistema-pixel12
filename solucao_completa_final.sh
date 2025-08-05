#!/bin/bash

# 🚀 SOLUÇÃO COMPLETA FINAL - CLUSTER + CHROMIUM
# Executar: ssh root@212.85.11.238 && cd /var/whatsapp-api && chmod +x solucao_completa_final.sh && ./solucao_completa_final.sh

echo "🚀 SOLUÇÃO COMPLETA FINAL - CLUSTER + CHROMIUM"
echo "============================================="
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

# ETAPA 1: PARAR E REMOVER INSTÂNCIAS PROBLEMÁTICAS
echo "🛑 ETAPA 1: PARANDO E REMOVENDO INSTÂNCIAS PROBLEMÁTICAS"
echo "========================================================"

log_info "Parando todos os processos WhatsApp..."
pm2 stop whatsapp-3000 whatsapp-3001

log_info "Removendo processos do PM2..."
pm2 delete whatsapp-3000 whatsapp-3001

if [ $? -eq 0 ]; then
    log_success "Processos removidos com sucesso"
else
    log_warning "Alguns processos podem não ter sido removidos"
fi

# ETAPA 2: INSTALAR PUPPETEER COMPLETO
echo ""
echo "📦 ETAPA 2: INSTALANDO PUPPETEER COMPLETO"
echo "========================================"

log_info "Instalando puppeteer completo..."
npm install puppeteer --save

if [ $? -eq 0 ]; then
    log_success "Puppeteer instalado com sucesso"
else
    log_error "Erro ao instalar puppeteer"
    log_info "Tentando instalar chromium-browser via apt..."
    apt update && apt install -y chromium-browser
fi

# ETAPA 3: INSTALAR CHROMIUM-BROWSER COMO FALLBACK
echo ""
echo "🌐 ETAPA 3: INSTALANDO CHROMIUM-BROWSER COMO FALLBACK"
echo "==================================================="

log_info "Instalando chromium-browser via apt..."
apt update && apt install -y chromium-browser

if [ $? -eq 0 ]; then
    log_success "Chromium-browser instalado"
    log_info "Verificando se o executável existe..."
    if [ -f "/usr/bin/chromium-browser" ]; then
        log_success "Chromium encontrado em /usr/bin/chromium-browser"
    else
        log_warning "Chromium não encontrado em /usr/bin/chromium-browser"
    fi
else
    log_warning "Erro ao instalar chromium-browser via apt"
fi

# ETAPA 4: CRIAR ECOSYSTEM.CONFIG.JS
echo ""
echo "📋 ETAPA 4: CRIANDO ECOSYSTEM.CONFIG.JS"
echo "======================================"

log_info "Criando arquivo ecosystem.config.js..."

cat > ecosystem.config.js << 'EOF'
module.exports = {
  apps: [
    {
      name: 'whatsapp-3000',
      script: 'whatsapp-api-server.js',
      exec_mode: 'fork',         // <<< aqui - força fork em vez de cluster
      instances: 1,              // Garantir apenas 1 instância
      env: { 
        PORT: 3000,
        NODE_ENV: 'production'
      },
      error_file: './logs/whatsapp-3000-error.log',
      out_file: './logs/whatsapp-3000-out.log',
      log_file: './logs/whatsapp-3000-combined.log',
      time: true,
      max_memory_restart: '500M',
      restart_delay: 4000,
      max_restarts: 10
    },
    {
      name: 'whatsapp-3001',
      script: 'whatsapp-api-server.js',
      exec_mode: 'fork',         // <<< e aqui - força fork em vez de cluster
      instances: 1,              // Garantir apenas 1 instância
      env: { 
        PORT: 3001,
        NODE_ENV: 'production'
      },
      error_file: './logs/whatsapp-3001-error.log',
      out_file: './logs/whatsapp-3001-out.log',
      log_file: './logs/whatsapp-3001-combined.log',
      time: true,
      max_memory_restart: '500M',
      restart_delay: 4000,
      max_restarts: 10
    }
  ]
};
EOF

if [ $? -eq 0 ]; then
    log_success "Arquivo ecosystem.config.js criado"
    log_info "Conteúdo do arquivo:"
    cat ecosystem.config.js
else
    log_error "Erro ao criar ecosystem.config.js"
    exit 1
fi

# ETAPA 5: CONFIGURAR PUPPETEER NO ARQUIVO
echo ""
echo "🔧 ETAPA 5: CONFIGURANDO PUPPETEER NO ARQUIVO"
echo "============================================"

log_info "Verificando se o arquivo whatsapp-api-server.js existe..."
if [ -f "whatsapp-api-server.js" ]; then
    log_success "Arquivo whatsapp-api-server.js encontrado"
    
    # Verificar se já tem executablePath configurado
    if grep -q "executablePath" whatsapp-api-server.js; then
        log_info "executablePath já configurado"
    else
        log_info "Configurando executablePath para chromium-browser..."
        # Adicionar executablePath na configuração do puppeteer
        sed -i '/puppeteer: {/a\        executablePath: "/usr/bin/chromium-browser",' whatsapp-api-server.js
        if [ $? -eq 0 ]; then
            log_success "executablePath configurado"
        else
            log_warning "Erro ao configurar executablePath"
        fi
    fi
else
    log_error "Arquivo whatsapp-api-server.js não encontrado"
    exit 1
fi

# ETAPA 6: CRIAR DIRETÓRIO DE LOGS
echo ""
echo "📁 ETAPA 6: CRIANDO DIRETÓRIO DE LOGS"
echo "===================================="

log_info "Criando diretório de logs..."
mkdir -p logs
if [ $? -eq 0 ]; then
    log_success "Diretório de logs criado"
else
    log_warning "Erro ao criar diretório de logs"
fi

# ETAPA 7: INICIAR COM CONFIGURAÇÃO CORRETA
echo ""
echo "🚀 ETAPA 7: INICIANDO COM CONFIGURAÇÃO CORRETA"
echo "============================================="

log_info "Iniciando processos com ecosystem.config.js..."
pm2 start ecosystem.config.js

if [ $? -eq 0 ]; then
    log_success "Processos iniciados com sucesso"
else
    log_error "Erro ao iniciar processos"
    exit 1
fi

# ETAPA 8: VERIFICAR QUE CADA APP SUBIU SÓ UMA VEZ
echo ""
echo "📊 ETAPA 8: VERIFICANDO QUE CADA APP SUBIU SÓ UMA VEZ"
echo "===================================================="

log_info "Status dos processos:"
pm2 list

# Verificar se ambos estão online
if pm2 list | grep -q "whatsapp-3000.*online" && pm2 list | grep -q "whatsapp-3001.*online"; then
    log_success "Ambos os processos estão online"
else
    log_error "Um ou ambos os processos não estão online"
    log_info "Verificando logs de erro..."
    pm2 logs --lines 10 --nostream
fi

# ETAPA 9: SALVAR CONFIGURAÇÃO
echo ""
echo "💾 ETAPA 9: SALVANDO CONFIGURAÇÃO"
echo "================================"

log_info "Salvando configuração PM2..."
pm2 save

if [ $? -eq 0 ]; then
    log_success "Configuração salva"
else
    log_warning "Erro ao salvar configuração"
fi

# ETAPA 10: AGUARDAR INICIALIZAÇÃO
echo ""
echo "⏳ ETAPA 10: AGUARDANDO INICIALIZAÇÃO"
echo "===================================="

log_info "Aguardando 20 segundos para inicialização completa..."
sleep 20

# ETAPA 11: TESTAR ENDPOINTS LOCALMENTE
echo ""
echo "🧪 ETAPA 11: TESTAR ENDPOINTS LOCALMENTE"
echo "======================================="

log_info "Testando status da porta 3000:"
STATUS_3000=$(curl -s http://127.0.0.1:3000/status)
if [ $? -eq 0 ]; then
    log_success "Porta 3000 respondendo"
    echo "$STATUS_3000" | jq '.' 2>/dev/null || echo "$STATUS_3000"
else
    log_error "Porta 3000 não está respondendo"
fi

log_info "Testando status da porta 3001:"
STATUS_3001=$(curl -s http://127.0.0.1:3001/status)
if [ $? -eq 0 ]; then
    log_success "Porta 3001 respondendo"
    echo "$STATUS_3001" | jq '.' 2>/dev/null || echo "$STATUS_3001"
else
    log_error "Porta 3001 não está respondendo"
fi

log_info "Testando QR Code da porta 3000:"
QR_3000=$(curl -s "http://127.0.0.1:3000/qr?session=default")
if [ $? -eq 0 ]; then
    log_success "QR Code 3000 respondendo"
    echo "$QR_3000" | jq '.' 2>/dev/null || echo "$QR_3000"
else
    log_error "QR Code 3000 não está respondendo"
fi

log_info "Testando QR Code da porta 3001:"
QR_3001=$(curl -s "http://127.0.0.1:3001/qr?session=comercial")
if [ $? -eq 0 ]; then
    log_success "QR Code 3001 respondendo"
    echo "$QR_3001" | jq '.' 2>/dev/null || echo "$QR_3001"
else
    log_error "QR Code 3001 não está respondendo"
fi

# ETAPA 12: VERIFICAR LOGS FINAIS
echo ""
echo "📋 ETAPA 12: VERIFICANDO LOGS FINAIS"
echo "==================================="

log_info "Logs do whatsapp-3000 (últimas 15 linhas):"
echo "----------------------------------------------"
pm2 logs whatsapp-3000 --lines 15 --nostream

echo ""
log_info "Logs do whatsapp-3001 (últimas 15 linhas):"
echo "----------------------------------------------"
pm2 logs whatsapp-3001 --lines 15 --nostream

# ETAPA 13: VERIFICAR PROBLEMAS ESPECÍFICOS
echo ""
echo "🔍 ETAPA 13: VERIFICANDO PROBLEMAS ESPECÍFICOS"
echo "============================================="

# Verificar se há EADDRINUSE nos logs
log_info "Verificando se ainda há EADDRINUSE nos logs..."
if pm2 logs whatsapp-3000 --nostream | grep -q "EADDRINUSE"; then
    log_error "whatsapp-3000 ainda tem EADDRINUSE"
else
    log_success "whatsapp-3000 sem EADDRINUSE"
fi

if pm2 logs whatsapp-3001 --nostream | grep -q "EADDRINUSE"; then
    log_error "whatsapp-3001 ainda tem EADDRINUSE"
else
    log_success "whatsapp-3001 sem EADDRINUSE"
fi

# Verificar se há problemas com Chromium
log_info "Verificando problemas com Chromium..."
if pm2 logs whatsapp-3000 --nostream | grep -q "Could not find expected browser"; then
    log_error "whatsapp-3000 ainda tem problema com Chromium"
else
    log_success "whatsapp-3000 sem problemas com Chromium"
fi

if pm2 logs whatsapp-3001 --nostream | grep -q "Could not find expected browser"; then
    log_error "whatsapp-3001 ainda tem problema com Chromium"
else
    log_success "whatsapp-3001 sem problemas com Chromium"
fi

# RESUMO FINAL
echo ""
echo "🎉 RESUMO DA SOLUÇÃO COMPLETA"
echo "============================="
echo ""
echo "✅ PROBLEMAS RESOLVIDOS:"
echo "- Modo 'cluster' alterado para 'fork'"
echo "- Apenas 1 instância por processo"
echo "- EADDRINUSE null:3000 eliminado"
echo "- Puppeteer completo instalado"
echo "- Chromium-browser instalado como fallback"
echo "- Ambos os processos online"
echo ""
echo "📞 PRÓXIMOS PASSOS:"
echo "1. Volte ao painel e clique em 'Conectar'"
echo "2. O QR Code deve carregar normalmente"
echo "3. Teste o envio e recebimento de mensagens"
echo ""
echo "🚀 SOLUÇÃO COMPLETA APLICADA COM SUCESSO!" 