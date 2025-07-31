#!/bin/bash

echo "🔧 CONFIGURANDO SERVIDOR WHATSAPP NA PORTA 3001"
echo "==============================================="
echo ""

# 1. Verificar configuração atual
echo "📊 CONFIGURAÇÃO ATUAL:"
echo "   Servidor principal: /var/whatsapp-api/"
echo "   Porta atual: 3000"
echo "   Processo: $(ps aux | grep 'whatsapp-api-server.js' | grep -v grep | wc -l) ativo"
echo ""

# 2. Verificar se porta 3001 está livre
echo "🔍 VERIFICANDO PORTA 3001:"
if netstat -tulpn | grep :3001 > /dev/null; then
    echo "❌ Porta 3001 já está em uso:"
    netstat -tulpn | grep :3001
    exit 1
else
    echo "✅ Porta 3001 está livre"
fi
echo ""

# 3. Criar diretório para servidor comercial
echo "📁 CRIANDO DIRETÓRIO PARA SERVIDOR COMERCIAL:"
COMERCIAL_DIR="/var/whatsapp-api-comercial"
if [ -d "$COMERCIAL_DIR" ]; then
    echo "⚠️ Diretório já existe: $COMERCIAL_DIR"
    read -p "Deseja sobrescrever? (s/n): " -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[Ss]$ ]]; then
        echo "❌ Operação cancelada"
        exit 1
    fi
    rm -rf "$COMERCIAL_DIR"
fi

echo "📋 Copiando configuração atual..."
cp -r /var/whatsapp-api "$COMERCIAL_DIR"
echo "✅ Diretório criado: $COMERCIAL_DIR"
echo ""

# 4. Configurar porta 3001
echo "🔧 CONFIGURANDO PORTA 3001:"
cd "$COMERCIAL_DIR"

# Verificar arquivos de configuração
CONFIG_FILES=("package.json" ".env" "config.js" "server.js" "app.js")

for file in "${CONFIG_FILES[@]}"; do
    if [ -f "$file" ]; then
        echo "📄 Configurando $file..."
        
        # Backup do arquivo original
        cp "$file" "$file.backup"
        
        # Substituir porta 3000 por 3001
        sed -i 's/:3000/:3001/g' "$file"
        sed -i 's/port.*3000/port: 3001/g' "$file"
        sed -i 's/PORT=3000/PORT=3001/g' "$file"
        
        echo "✅ $file configurado"
    fi
done
echo ""

# 5. Verificar se PM2 está instalado
echo "🔍 VERIFICANDO PM2:"
if command -v pm2 &> /dev/null; then
    echo "✅ PM2 está instalado"
    PM2_AVAILABLE=true
else
    echo "❌ PM2 não está instalado"
    echo "   Instalando PM2..."
    npm install -g pm2
    PM2_AVAILABLE=true
fi
echo ""

# 6. Iniciar servidor na porta 3001
echo "🚀 INICIANDO SERVIDOR NA PORTA 3001:"

if [ "$PM2_AVAILABLE" = true ]; then
    echo "📱 Iniciando com PM2..."
    
    # Parar processo se já existir
    pm2 stop whatsapp-comercial 2>/dev/null
    pm2 delete whatsapp-comercial 2>/dev/null
    
    # Iniciar novo processo
    cd "$COMERCIAL_DIR"
    pm2 start whatsapp-api-server.js --name whatsapp-comercial -- --port 3001
    
    echo "✅ Servidor iniciado com PM2"
    echo "   Comando: pm2 start whatsapp-api-server.js --name whatsapp-comercial -- --port 3001"
else
    echo "📱 Iniciando diretamente..."
    cd "$COMERCIAL_DIR"
    nohup node whatsapp-api-server.js --port 3001 > /var/log/whatsapp-comercial.log 2>&1 &
    echo "✅ Servidor iniciado em background"
    echo "   Log: /var/log/whatsapp-comercial.log"
fi
echo ""

# 7. Verificar se está funcionando
echo "🔍 VERIFICANDO SE ESTÁ FUNCIONANDO:"
sleep 3

if curl -s http://localhost:3001/status > /dev/null; then
    echo "✅ Servidor na porta 3001 está funcionando!"
    echo "   Status: $(curl -s http://localhost:3001/status | jq -r '.message // .ready // "OK"')"
else
    echo "❌ Servidor não está respondendo na porta 3001"
    echo "   Verifique os logs:"
    if [ "$PM2_AVAILABLE" = true ]; then
        echo "   pm2 logs whatsapp-comercial"
    else
        echo "   tail -f /var/log/whatsapp-comercial.log"
    fi
fi
echo ""

# 8. Comandos úteis
echo "📋 COMANDOS ÚTEIS:"
echo "   # Verificar status do servidor comercial"
echo "   curl http://localhost:3001/status"
echo ""
echo "   # Verificar processos PM2"
echo "   pm2 list"
echo ""
echo "   # Ver logs do servidor comercial"
if [ "$PM2_AVAILABLE" = true ]; then
    echo "   pm2 logs whatsapp-comercial"
else
    echo "   tail -f /var/log/whatsapp-comercial.log"
fi
echo ""
echo "   # Parar servidor comercial"
if [ "$PM2_AVAILABLE" = true ]; then
    echo "   pm2 stop whatsapp-comercial"
else
    echo "   pkill -f 'whatsapp-api-server.js.*3001'"
fi
echo ""

echo "✅ CONFIGURAÇÃO CONCLUÍDA!"
echo "Agora execute o monitor local para capturar o número automaticamente." 