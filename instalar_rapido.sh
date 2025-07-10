#!/bin/bash

echo "🚀 INSTALAÇÃO SUPER RÁPIDA - WPPConnect"
echo "========================================"

# Verificar se é root
if [ "$EUID" -ne 0 ]; then
    echo "❌ Execute como root: sudo bash instalar_rapido.sh"
    exit 1
fi

echo "📦 Instalando dependências..."
apt update
apt install -y nodejs npm git nginx curl

echo "📁 Baixando WPPConnect..."
cd /opt
git clone https://github.com/wppconnect-team/wppconnect-server.git wppconnect
cd wppconnect

echo "📦 Instalando dependências..."
npm install

echo "📦 Instalando PM2..."
npm install -g pm2

echo "⚙️ Configurando..."
cat > .env << EOF
PORT=8080
HOST=0.0.0.0
SECRET_KEY=wppconnect_$(date +%s)
CORS_ORIGIN=*
WEBHOOK_BY_EVENTS=false
SESSION_DATA_PATH=/opt/wppconnect/sessions
EOF

echo "🚀 Iniciando..."
pm2 start src/server.js --name wppconnect
pm2 save
pm2 startup

echo "🌐 Configurando Nginx..."
cat > /etc/nginx/sites-available/wppconnect << EOF
server {
    listen 80;
    server_name wpp.seudominio.com;
    
    location / {
        proxy_pass http://localhost:8080;
        proxy_http_version 1.1;
        proxy_set_header Upgrade \$http_upgrade;
        proxy_set_header Connection 'upgrade';
        proxy_set_header Host \$host;
        proxy_set_header X-Real-IP \$remote_addr;
        proxy_set_header X-Forwarded-For \$proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto \$scheme;
        proxy_cache_bypass \$http_upgrade;
    }
}
EOF

ln -sf /etc/nginx/sites-available/wppconnect /etc/nginx/sites-enabled/
rm -f /etc/nginx/sites-enabled/default
systemctl restart nginx

echo "✅ PRONTO!"
echo "========================================"
echo "🌐 Acesse: http://wpp.seudominio.com"
echo "📱 Crie uma sessão e escaneie o QR Code"
echo "🔧 API: http://localhost:8080"
echo "📊 Status: pm2 status"
echo "📋 Logs: pm2 logs wppconnect"
echo ""
echo "🎯 Próximo passo: Configure seu domínio e SSL"
echo "certbot --nginx -d wpp.seudominio.com" 