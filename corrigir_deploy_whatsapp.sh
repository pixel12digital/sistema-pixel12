#!/bin/bash

echo "🔧 CORREÇÃO E DEPLOY WHATSAPP API"
echo "=================================="

# Variáveis
API_DIR="/var/whatsapp-api"
CURRENT_DIR=$(pwd)

echo ""
echo "📋 1. NAVEGANDO PARA O DIRETÓRIO CORRETO"
echo "-----------------------------------------"
if [ "$CURRENT_DIR" != "$API_DIR" ]; then
    echo "🔄 Navegando para $API_DIR..."
    cd $API_DIR
    echo "📁 Diretório atual: $(pwd)"
else
    echo "✅ Já estamos no diretório correto: $API_DIR"
fi

echo ""
echo "📋 2. VERIFICANDO SE O DIRETÓRIO EXISTE"
echo "----------------------------------------"
if [ ! -d "$API_DIR" ]; then
    echo "❌ Diretório $API_DIR não existe!"
    echo "🔄 Criando diretório..."
    sudo mkdir -p $API_DIR
    sudo chown $USER:$USER $API_DIR
    echo "✅ Diretório criado: $API_DIR"
else
    echo "✅ Diretório existe: $API_DIR"
fi

echo ""
echo "📋 3. PARANDO TODOS OS PROCESSOS PM2"
echo "-------------------------------------"
echo "🛑 Parando todas as instâncias..."
pm2 delete all 2>/dev/null || echo "Nenhum processo PM2 encontrado"

echo ""
echo "📋 4. VERIFICANDO ARQUIVOS ATUAIS"
echo "----------------------------------"
echo "📁 Conteúdo atual do diretório:"
ls -la

echo ""
echo "📋 5. VERIFICANDO SE OS ARQUIVOS PRINCIPAIS EXISTEM"
echo "---------------------------------------------------"
MISSING_FILES=()

if [ ! -f "ecosystem.config.js" ]; then
    echo "❌ ecosystem.config.js não encontrado"
    MISSING_FILES+=("ecosystem.config.js")
else
    echo "✅ ecosystem.config.js encontrado"
fi

if [ ! -f "whatsapp-api-server.js" ]; then
    echo "❌ whatsapp-api-server.js não encontrado"
    MISSING_FILES+=("whatsapp-api-server.js")
else
    echo "✅ whatsapp-api-server.js encontrado"
fi

echo ""
echo "📋 6. SE ARQUIVOS ESTÃO FALTANDO, CRIANDO-OS"
echo "---------------------------------------------"
if [ ${#MISSING_FILES[@]} -gt 0 ]; then
    echo "⚠️ Arquivos faltando: ${MISSING_FILES[@]}"
    echo "🔄 Criando arquivos necessários..."
    
    # Criar ecosystem.config.js se não existir
    if [[ " ${MISSING_FILES[@]} " =~ " ecosystem.config.js " ]]; then
        echo "📄 Criando ecosystem.config.js..."
        cat > ecosystem.config.js << 'EOF'
module.exports = {
  apps: [
    {
      name: 'whatsapp-3000',
      script: 'whatsapp-api-server.js',
      instances: 1,
      exec_mode: 'fork',
      autorestart: true,
      watch: false,
      max_memory_restart: '1G',
      env: {
        NODE_ENV: 'production',
        PORT: 3000
      },
      log_date_format: 'YYYY-MM-DD HH:mm:ss Z',
      log_file: './logs/combined-3000.log',
      out_file: './logs/out-3000.log',
      error_file: './logs/error-3000.log',
      max_restarts: 10,
      restart_delay: 5000,
      exp_backoff_restart_delay: 100,
      kill_timeout: 5000,
      pmx: true,
      node_args: '--max-old-space-size=2048',
      ignore_watch: ['node_modules', 'sessions', 'logs', 'tmp'],
      merge_logs: true,
      time: true,
      cron_restart: '0 4 * * *',
      health_check_grace_period: 3000,
      health_check_fatal_exceptions: true
    },
    {
      name: 'whatsapp-3001',
      script: 'whatsapp-api-server.js',
      instances: 1,
      exec_mode: 'fork',
      autorestart: true,
      watch: false,
      max_memory_restart: '1G',
      env: {
        NODE_ENV: 'production',
        PORT: 3001
      },
      log_date_format: 'YYYY-MM-DD HH:mm:ss Z',
      log_file: './logs/combined-3001.log',
      out_file: './logs/out-3001.log',
      error_file: './logs/error-3001.log',
      max_restarts: 10,
      restart_delay: 5000,
      exp_backoff_restart_delay: 100,
      kill_timeout: 5000,
      pmx: true,
      node_args: '--max-old-space-size=2048',
      ignore_watch: ['node_modules', 'sessions', 'logs', 'tmp'],
      merge_logs: true,
      time: true,
      cron_restart: '0 4 * * *',
      health_check_grace_period: 3000,
      health_check_fatal_exceptions: true
    }
  ]
};
EOF
        echo "✅ ecosystem.config.js criado"
    fi
    
    # Criar whatsapp-api-server.js se não existir
    if [[ " ${MISSING_FILES[@]} " =~ " whatsapp-api-server.js " ]]; then
        echo "📄 Criando whatsapp-api-server.js..."
        echo "⚠️ ATENÇÃO: whatsapp-api-server.js é um arquivo grande!"
        echo "   Você precisa copiar o arquivo completo do repositório"
        echo "   ou fazer um git pull/clone do projeto"
    fi
else
    echo "✅ Todos os arquivos principais existem"
fi

echo ""
echo "📋 7. CRIANDO DIRETÓRIO DE LOGS"
echo "--------------------------------"
mkdir -p logs
echo "✅ Diretório de logs criado"

echo ""
echo "📋 8. VERIFICANDO DEPENDÊNCIAS NODE.JS"
echo "--------------------------------------"
if [ ! -f "package.json" ]; then
    echo "📄 Criando package.json básico..."
    cat > package.json << 'EOF'
{
  "name": "whatsapp-api",
  "version": "1.0.0",
  "description": "WhatsApp Multi-Session API",
  "main": "whatsapp-api-server.js",
  "scripts": {
    "start": "node whatsapp-api-server.js",
    "dev": "nodemon whatsapp-api-server.js"
  },
  "dependencies": {
    "whatsapp-web.js": "^1.23.0",
    "express": "^4.18.2",
    "cors": "^2.8.5",
    "qrcode-terminal": "^0.12.0",
    "fs-extra": "^11.1.1",
    "multer": "^1.4.5-lts.1"
  },
  "devDependencies": {
    "nodemon": "^3.0.1"
  }
}
EOF
    echo "✅ package.json criado"
    
    echo "📦 Instalando dependências..."
    npm install
else
    echo "✅ package.json já existe"
fi

echo ""
echo "📋 9. INICIANDO PM2 COM CAMINHO ABSOLUTO"
echo "-----------------------------------------"
echo "🚀 Iniciando PM2..."
pm2 start $(pwd)/ecosystem.config.js

echo ""
echo "📋 10. SALVANDO CONFIGURAÇÃO PM2"
echo "--------------------------------"
pm2 save

echo ""
echo "📋 11. VERIFICANDO STATUS"
echo "-------------------------"
echo "📊 Status do PM2:"
pm2 list

echo ""
echo "📋 12. AGUARDANDO INICIALIZAÇÃO"
echo "-------------------------------"
echo "⏳ Aguardando 15 segundos para inicialização..."
sleep 15

echo ""
echo "📋 13. VERIFICANDO PORTAS"
echo "-------------------------"
echo "🔍 Verificando porta 3000:"
ss -tlnp | grep :3000 || echo "   ❌ Porta 3000 não está sendo escutada"
echo "🔍 Verificando porta 3001:"
ss -tlnp | grep :3001 || echo "   ❌ Porta 3001 não está sendo escutada"

echo ""
echo "📋 14. TESTANDO CONECTIVIDADE"
echo "-----------------------------"
echo "🔧 Testando porta 3000:"
curl -s http://127.0.0.1:3000/status 2>/dev/null | head -5 || echo "❌ Falha na porta 3000"
echo ""
echo "🔧 Testando porta 3001:"
curl -s http://127.0.0.1:3001/status 2>/dev/null | head -5 || echo "❌ Falha na porta 3001"

echo ""
echo "📋 15. VERIFICANDO LOGS"
echo "-----------------------"
echo "📊 Logs da instância 3001:"
pm2 logs whatsapp-3001 --lines 20 --nostream

echo ""
echo "✅ CORREÇÃO E DEPLOY CONCLUÍDO!"
echo ""
echo "📝 PRÓXIMOS PASSOS:"
echo "   1. Se whatsapp-api-server.js não existe: Copie do repositório"
echo "   2. Se portas não funcionam: Verifique logs com 'pm2 logs'"
echo "   3. Teste externamente: curl http://212.85.11.238:3001/status"
echo "   4. Monitore logs: pm2 logs whatsapp-3001" 