#!/bin/bash

echo "🔍 VERIFICANDO INSTALAÇÃO WPPConnect"
echo "====================================="

# Verificar se PM2 está instalado
echo "1. 📦 Verificando PM2..."
if command -v pm2 &> /dev/null; then
    echo "✅ PM2 instalado"
else
    echo "❌ PM2 não encontrado"
    exit 1
fi

# Verificar se WPPConnect está rodando
echo "2. 🚀 Verificando WPPConnect..."
if pm2 list | grep -q "wppconnect"; then
    echo "✅ WPPConnect está rodando"
    pm2 status
else
    echo "❌ WPPConnect não está rodando"
    echo "Tentando iniciar..."
    cd /opt/wppconnect
    pm2 start src/server.js --name wppconnect
fi

# Verificar porta 8080
echo "3. 🌐 Verificando porta 8080..."
if netstat -tlnp | grep -q ":8080"; then
    echo "✅ Porta 8080 está ativa"
else
    echo "❌ Porta 8080 não está ativa"
fi

# Testar API
echo "4. 🔧 Testando API..."
response=$(curl -s http://localhost:8080/api/sessions/find)
if [ $? -eq 0 ]; then
    echo "✅ API respondendo"
    echo "Resposta: $response"
else
    echo "❌ API não está respondendo"
fi

# Verificar Nginx
echo "5. 🌐 Verificando Nginx..."
if systemctl is-active --quiet nginx; then
    echo "✅ Nginx está rodando"
else
    echo "❌ Nginx não está rodando"
    systemctl start nginx
fi

# Verificar configuração Nginx
echo "6. ⚙️ Verificando configuração Nginx..."
if nginx -t &> /dev/null; then
    echo "✅ Configuração Nginx OK"
else
    echo "❌ Erro na configuração Nginx"
    nginx -t
fi

# Verificar diretório de sessões
echo "7. 📁 Verificando diretório de sessões..."
if [ -d "/opt/wppconnect/sessions" ]; then
    echo "✅ Diretório de sessões existe"
    ls -la /opt/wppconnect/sessions/
else
    echo "❌ Diretório de sessões não existe"
    mkdir -p /opt/wppconnect/sessions
fi

# Verificar logs
echo "8. 📋 Verificando logs..."
if [ -f "/var/log/wppconnect/out.log" ]; then
    echo "✅ Logs existem"
    echo "Últimas linhas do log:"
    tail -5 /var/log/wppconnect/out.log
else
    echo "❌ Logs não encontrados"
fi

echo ""
echo "🎯 PRÓXIMOS PASSOS:"
echo "1. Acesse: http://SEU_IP_VPS:8080"
echo "2. Crie uma sessão"
echo "3. Escaneie o QR Code"
echo "4. Teste o envio de mensagens"
echo ""
echo "📊 Status geral:"
if pm2 list | grep -q "wppconnect" && netstat -tlnp | grep -q ":8080"; then
    echo "✅ INSTALAÇÃO CONCLUÍDA COM SUCESSO!"
else
    echo "❌ HÁ PROBLEMAS NA INSTALAÇÃO"
fi 