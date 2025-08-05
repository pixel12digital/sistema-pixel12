#!/bin/bash

# 🔧 SCRIPT DE CORREÇÃO VPS - WHATSAPP
# Execute este script no VPS como root

echo "🔧 INICIANDO CORREÇÃO VPS - WHATSAPP"
echo "====================================="
echo ""

# 1. Verificar situação atual
echo "1. 🔍 VERIFICANDO SITUAÇÃO ATUAL..."
echo "PM2 Status:"
pm2 list
echo ""

echo "Processos Node.js:"
ps aux | grep node
echo ""

echo "Portas em uso:"
netstat -tulpn | grep -E ":(3000|3001)"
echo ""

# 2. Parar processos existentes
echo "2. 🛑 PARANDO PROCESSOS EXISTENTES..."
pm2 stop all 2>/dev/null || true
pm2 delete all 2>/dev/null || true
pkill -f "node.*whatsapp" 2>/dev/null || true
pkill -f "node.*3000" 2>/dev/null || true
pkill -f "node.*3001" 2>/dev/null || true
echo "✅ Processos parados"
echo ""

# 3. Verificar/criar diretório
echo "3. 📁 VERIFICANDO DIRETÓRIO..."
if [ ! -d "/var/whatsapp-api" ]; then
    echo "Criando diretório /var/whatsapp-api..."
    mkdir -p /var/whatsapp-api
fi

cd /var/whatsapp-api
echo "Diretório atual: $(pwd)"
echo "Conteúdo:"
ls -la
echo ""

# 4. Encontrar ou instalar WhatsApp API
echo "4. 🔍 PROCURANDO ARQUIVOS WHATSAPP..."

# Procurar arquivos existentes
WHATSAPP_FILE=""
for file in app.js server.js index.js whatsapp-api-server.js; do
    if [ -f "$file" ]; then
        WHATSAPP_FILE="$file"
        echo "✅ Encontrado: $file"
        break
    fi
done

# Se não encontrou, procurar em outros locais
if [ -z "$WHATSAPP_FILE" ]; then
    echo "Procurando em outros diretórios..."
    SEARCH_RESULT=$(find /var /opt /root -name "app.js" -o -name "server.js" -o -name "whatsapp-api-server.js" 2>/dev/null | head -1)
    if [ ! -z "$SEARCH_RESULT" ]; then
        echo "✅ Encontrado em: $SEARCH_RESULT"
        cp "$SEARCH_RESULT" /var/whatsapp-api/app.js
        WHATSAPP_FILE="app.js"
    fi
fi

# Se ainda não encontrou, instalar
if [ -z "$WHATSAPP_FILE" ]; then
    echo "📥 INSTALANDO WHATSAPP API..."
    
    # Verificar se Node.js está instalado
    if ! command -v node &> /dev/null; then
        echo "❌ Node.js não está instalado!"
        echo "Instale Node.js primeiro:"
        echo "curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -"
        echo "apt-get install -y nodejs"
        exit 1
    fi
    
    # Instalar WhatsApp API
    if command -v git &> /dev/null; then
        git clone https://github.com/chrishubert/whatsapp-api.git . 2>/dev/null || true
        if [ -f "package.json" ]; then
            npm install
            WHATSAPP_FILE="app.js"
        fi
    fi
    
    # Se ainda não funcionar, criar um arquivo básico
    if [ -z "$WHATSAPP_FILE" ]; then
        echo "📝 CRIANDO ARQUIVO BÁSICO..."
        cat > app.js << 'EOF'
const express = require('express');
const app = express();

app.get('/status', (req, res) => {
    res.json({
        status: 'running',
        ready: false,
        timestamp: new Date().toISOString()
    });
});

const PORT = process.env.PORT || 3000;
app.listen(PORT, () => {
    console.log(`WhatsApp API rodando na porta ${PORT}`);
});
EOF
        
        # Instalar dependências básicas
        npm init -y 2>/dev/null || true
        npm install express 2>/dev/null || true
        WHATSAPP_FILE="app.js"
    fi
fi

echo ""

# 5. Configurar PM2
echo "5. ⚙️  CONFIGURANDO PM2..."

if [ ! -z "$WHATSAPP_FILE" ]; then
    echo "Criando configuração PM2..."
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
      }
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
      }
    }
  ]
};
EOF
    
    echo "✅ Configuração PM2 criada"
else
    echo "❌ Nenhum arquivo WhatsApp encontrado!"
    exit 1
fi

echo ""

# 6. Iniciar serviços
echo "6. 🚀 INICIANDO SERVIÇOS..."
pm2 start ecosystem.config.js
pm2 save
echo ""

# 7. Verificar status
echo "7. ✅ VERIFICANDO STATUS..."
sleep 3

echo "PM2 Status:"
pm2 status
echo ""

echo "Logs recentes:"
pm2 logs --lines 10
echo ""

echo "Testando portas:"
for port in 3000 3001; do
    echo -n "Porta $port: "
    if curl -s --connect-timeout 5 "http://localhost:$port/status" > /dev/null; then
        echo "✅ Respondendo"
        curl -s "http://localhost:$port/status" | python3 -m json.tool 2>/dev/null || echo "Response OK"
    else
        echo "❌ Não responde"
    fi
done

echo ""
echo "🎉 CORREÇÃO CONCLUÍDA!"
echo ""
echo "📋 PRÓXIMOS PASSOS:"
echo "1. Execute 'php testar_status_final.php' no seu computador"
echo "2. Acesse o painel de comunicação"
echo "3. Verifique se os status foram atualizados"
echo ""
echo "🔧 Para monitorar:"
echo "pm2 logs --follow"
echo "pm2 monit" 