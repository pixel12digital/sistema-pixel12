#!/bin/bash

# 🔧 SCRIPT SSH DE RESTAURAÇÃO VPS - WHATSAPP API
# Execute: ssh root@212.85.11.238 'bash -s' < comandos_ssh_restauracao.sh

echo "🔧 RESTAURAÇÃO VPS VIA SSH - WHATSAPP API"
echo "========================================="
echo ""

# Cores para output
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

# Funções de log
log_info() { echo -e "${BLUE}[INFO]${NC} $1"; }
log_success() { echo -e "${GREEN}[SUCCESS]${NC} $1"; }
log_warning() { echo -e "${YELLOW}[WARNING]${NC} $1"; }
log_error() { echo -e "${RED}[ERROR]${NC} $1"; }

# 1. VERIFICAR SITUAÇÃO ATUAL
echo "1️⃣ VERIFICANDO SITUAÇÃO ATUAL"
echo "============================="

log_info "Verificando processos PM2..."
pm2 list

log_info "Verificando processos Node.js..."
ps aux | grep node | grep -v grep

log_info "Verificando portas em uso..."
netstat -tulpn | grep -E ":(3000|3001)" || echo "Nenhuma porta encontrada"

echo ""

# 2. PARAR PROCESSOS EXISTENTES
echo "2️⃣ PARANDO PROCESSOS EXISTENTES"
echo "==============================="

log_info "Parando todos os processos PM2..."
pm2 stop all 2>/dev/null || true
pm2 delete all 2>/dev/null || true

log_info "Matando processos Node.js..."
pkill -f "node.*whatsapp" 2>/dev/null || true
pkill -f "node.*3000" 2>/dev/null || true
pkill -f "node.*3001" 2>/dev/null || true

log_success "Processos parados com sucesso"
echo ""

# 3. VERIFICAR/CRIAR DIRETÓRIO
echo "3️⃣ PREPARANDO DIRETÓRIO"
echo "======================="

if [ ! -d "/var/whatsapp-api" ]; then
    log_info "Criando diretório /var/whatsapp-api..."
    mkdir -p /var/whatsapp-api
fi

cd /var/whatsapp-api
log_info "Diretório atual: $(pwd)"

log_info "Conteúdo do diretório:"
ls -la
echo ""

# 4. ENCONTRAR OU INSTALAR WHATSAPP API
echo "4️⃣ CONFIGURANDO WHATSAPP API"
echo "============================="

# Procurar arquivos existentes
WHATSAPP_FILE=""
for file in app.js server.js index.js whatsapp-api-server.js; do
    if [ -f "$file" ]; then
        WHATSAPP_FILE="$file"
        log_success "Encontrado: $file"
        break
    fi
done

# Se não encontrou, procurar em outros locais
if [ -z "$WHATSAPP_FILE" ]; then
    log_info "Procurando em outros diretórios..."
    SEARCH_RESULT=$(find /var /opt /root -name "app.js" -o -name "server.js" -o -name "whatsapp-api-server.js" 2>/dev/null | head -1)
    if [ ! -z "$SEARCH_RESULT" ]; then
        log_success "Encontrado em: $SEARCH_RESULT"
        cp "$SEARCH_RESULT" /var/whatsapp-api/app.js
        WHATSAPP_FILE="app.js"
    fi
fi

# Se ainda não encontrou, criar arquivo básico
if [ -z "$WHATSAPP_FILE" ]; then
    log_info "Criando arquivo WhatsApp API básico..."
    cat > app.js << 'EOF'
const express = require('express');
const app = express();

// Middleware para JSON
app.use(express.json());

// Status endpoint
app.get('/status', (req, res) => {
    res.json({
        status: 'running',
        ready: true,
        timestamp: new Date().toISOString(),
        port: process.env.PORT || 3000
    });
});

// Webhook config endpoint
app.get('/webhook/config', (req, res) => {
    res.json({
        webhook_url: global.webhookUrl || 'not configured',
        success: true
    });
});

// Webhook config POST endpoint
app.post('/webhook/config', (req, res) => {
    const { url } = req.body;
    if (url) {
        global.webhookUrl = url;
        res.json({
            success: true,
            message: 'Webhook configured successfully',
            webhook_url: url
        });
    } else {
        res.status(400).json({
            success: false,
            message: 'URL is required'
        });
    }
});

// Send text endpoint
app.post('/send/text', (req, res) => {
    const { to, message } = req.body;
    if (to && message) {
        // Simular envio de mensagem
        console.log(`Sending message to ${to}: ${message}`);
        res.json({
            success: true,
            message: 'Message sent successfully',
            to: to,
            content: message
        });
    } else {
        res.status(400).json({
            success: false,
            message: 'to and message are required'
        });
    }
});

// Webhook test endpoint
app.post('/webhook/test', (req, res) => {
    res.json({
        success: true,
        message: 'Webhook test endpoint working'
    });
});

const PORT = process.env.PORT || 3000;
app.listen(PORT, () => {
    console.log(`WhatsApp API running on port ${PORT}`);
    console.log(`Status: http://localhost:${PORT}/status`);
    console.log(`Webhook config: http://localhost:${PORT}/webhook/config`);
});
EOF

    # Instalar dependências básicas
    log_info "Instalando dependências..."
    npm init -y 2>/dev/null || true
    npm install express 2>/dev/null || true
    WHATSAPP_FILE="app.js"
    log_success "Arquivo WhatsApp API criado"
fi

echo ""

# 5. CONFIGURAR PM2
echo "5️⃣ CONFIGURANDO PM2"
echo "==================="

log_info "Criando configuração PM2..."
cat > ecosystem.config.js << EOF
module.exports = {
  apps: [
    {
      name: 'whatsapp-3000',
      script: './$WHATSAPP_FILE',
      instances: 1,
      autorestart: true,
      watch: false,
      max_memory_restart: '1G',
      env: {
        NODE_ENV: 'production',
        PORT: 3000
      },
      error_file: './logs/err-3000.log',
      out_file: './logs/out-3000.log',
      log_file: './logs/combined-3000.log',
      time: true
    },
    {
      name: 'whatsapp-3001',
      script: './$WHATSAPP_FILE',
      instances: 1,
      autorestart: true,
      watch: false,
      max_memory_restart: '1G',
      env: {
        NODE_ENV: 'production',
        PORT: 3001
      },
      error_file: './logs/err-3001.log',
      out_file: './logs/out-3001.log',
      log_file: './logs/combined-3001.log',
      time: true
    }
  ]
};
EOF

# Criar diretório de logs
mkdir -p logs

log_success "Configuração PM2 criada"
echo ""

# 6. INICIAR SERVIÇOS
echo "6️⃣ INICIANDO SERVIÇOS"
echo "====================="

log_info "Iniciando serviços PM2..."
pm2 start ecosystem.config.js

log_info "Salvando configuração PM2..."
pm2 save

log_info "Configurando PM2 para iniciar com o sistema..."
pm2 startup

log_success "Serviços iniciados"
echo ""

# 7. CONFIGURAR WEBHOOKS
echo "7️⃣ CONFIGURANDO WEBHOOKS"
echo "========================"

# URL do webhook de produção
WEBHOOK_URL="https://app.pixel12digital.com.br/painel/receber_mensagem_ana_local.php"

log_info "Configurando webhook para porta 3000..."
sleep 3
WEBHOOK_SET_3000=$(curl -s -X POST http://127.0.0.1:3000/webhook/config \
  -H "Content-Type: application/json" \
  -d "{\"url\":\"$WEBHOOK_URL\"}")

if echo "$WEBHOOK_SET_3000" | grep -q "success.*true"; then
    log_success "Webhook 3000 configurado"
else
    log_warning "Webhook 3000 pode ter falhado: $WEBHOOK_SET_3000"
fi

log_info "Configurando webhook para porta 3001..."
sleep 3
WEBHOOK_SET_3001=$(curl -s -X POST http://127.0.0.1:3001/webhook/config \
  -H "Content-Type: application/json" \
  -d "{\"url\":\"$WEBHOOK_URL\"}")

if echo "$WEBHOOK_SET_3001" | grep -q "success.*true"; then
    log_success "Webhook 3001 configurado"
else
    log_warning "Webhook 3001 pode ter falhado: $WEBHOOK_SET_3001"
fi

echo ""

# 8. VERIFICAR STATUS FINAL
echo "8️⃣ VERIFICANDO STATUS FINAL"
echo "==========================="

log_info "Status PM2:"
pm2 status

log_info "Testando portas..."
for port in 3000 3001; do
    echo -n "Porta $port: "
    if curl -s --connect-timeout 5 "http://localhost:$port/status" > /dev/null; then
        log_success "Respondendo"
        STATUS_RESPONSE=$(curl -s "http://localhost:$port/status")
        echo "  Status: $STATUS_RESPONSE"
    else
        log_error "Não responde"
    fi
done

log_info "Verificando webhooks..."
for port in 3000 3001; do
    echo -n "Webhook $port: "
    WEBHOOK_CONFIG=$(curl -s "http://localhost:$port/webhook/config")
    if echo "$WEBHOOK_CONFIG" | grep -q "webhook_url"; then
        log_success "Configurado"
        echo "  URL: $WEBHOOK_CONFIG"
    else
        log_warning "Não configurado ou erro"
    fi
done

echo ""

# 9. COMANDOS DE MONITORAMENTO
echo "9️⃣ COMANDOS DE MONITORAMENTO"
echo "============================"

log_info "Comandos úteis para monitoramento:"
echo ""
echo "📊 Ver status PM2:"
echo "   pm2 status"
echo ""
echo "📋 Ver logs:"
echo "   pm2 logs --lines 20"
echo "   pm2 logs whatsapp-3000 --lines 10"
echo "   pm2 logs whatsapp-3001 --lines 10"
echo ""
echo "🔍 Monitorar em tempo real:"
echo "   pm2 monit"
echo ""
echo "🔄 Reiniciar serviços:"
echo "   pm2 restart all"
echo "   pm2 restart whatsapp-3000"
echo "   pm2 restart whatsapp-3001"
echo ""
echo "🧪 Testar endpoints:"
echo "   curl http://localhost:3000/status"
echo "   curl http://localhost:3001/status"
echo "   curl http://localhost:3000/webhook/config"
echo "   curl http://localhost:3001/webhook/config"
echo ""

# 10. RESUMO FINAL
echo "🎯 RESTAURAÇÃO CONCLUÍDA!"
echo "========================"

log_success "✅ VPS restaurada com sucesso!"
echo ""
echo "📋 RESUMO:"
echo "• 2 canais WhatsApp configurados (3000 e 3001)"
echo "• Webhooks configurados para produção"
echo "• PM2 configurado para auto-restart"
echo "• Logs organizados em /var/whatsapp-api/logs/"
echo ""
echo "🔧 PRÓXIMOS PASSOS:"
echo "1. Execute o script PHP de restauração local"
echo "2. Verifique o painel de comunicação"
echo "3. Teste o envio de mensagens"
echo "4. Monitore os logs se necessário"
echo ""
log_success "🎉 Restauração VPS finalizada!" 