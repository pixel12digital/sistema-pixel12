#!/bin/bash

echo "🌐 Configurando Proxy Reverso Nginx para WhatsApp API..."

# Verificar se Nginx está instalado
if ! command -v nginx &> /dev/null; then
    echo "📦 Instalando Nginx..."
    apt update
    apt install -y nginx
fi

# Criar diretório de logs se não existir
mkdir -p /var/log/nginx

# Copiar configuração
echo "📋 Copiando configuração do proxy reverso..."
cp nginx_whatsapp_proxy.conf /etc/nginx/sites-available/whatsapp-proxy

# Ativar site
echo "🔗 Ativando site..."
ln -sf /etc/nginx/sites-available/whatsapp-proxy /etc/nginx/sites-enabled/

# Remover site default se existir
if [ -f /etc/nginx/sites-enabled/default ]; then
    echo "🗑️ Removendo site default..."
    rm /etc/nginx/sites-enabled/default
fi

# Testar configuração
echo "🧪 Testando configuração do Nginx..."
nginx -t

if [ $? -eq 0 ]; then
    echo "✅ Configuração válida!"
    
    # Recarregar Nginx
    echo "🔄 Recarregando Nginx..."
    systemctl reload nginx
    
    # Verificar status
    echo "📊 Status do Nginx:"
    systemctl status nginx --no-pager -l
    
    echo ""
    echo "✅ Proxy reverso configurado!"
    echo ""
    echo "🌐 URLs disponíveis:"
    echo "   - Sessão Default: http://212.85.11.238/whatsapp/default/"
    echo "   - Sessão Comercial: http://212.85.11.238/whatsapp/comercial/"
    echo "   - Health Check: http://212.85.11.238/whatsapp-health"
    echo ""
    echo "🧪 Para testar:"
    echo "   curl -s http://212.85.11.238/whatsapp/comercial/status | jq ."
else
    echo "❌ Erro na configuração do Nginx!"
    exit 1
fi 