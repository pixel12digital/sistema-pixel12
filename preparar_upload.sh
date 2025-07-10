#!/bin/bash

echo "📦 PREPARANDO ARQUIVOS PARA UPLOAD"
echo "=================================="

# Criar diretório temporário
mkdir -p upload_wppconnect
cd upload_wppconnect

# Copiar arquivos necessários
echo "📁 Copiando arquivos..."
cp ../instalar_rapido.sh .
cp ../verificar_instalacao.sh .
cp ../teste_simples.php .
cp -r ../api .

# Criar arquivo de instruções
cat > INSTRUCOES_INSTALACAO.md << 'EOF'
# 📱 Instalação WPPConnect - Passo a Passo

## 1. Upload dos arquivos
```bash
# Via SCP (recomendado)
scp -r upload_wppconnect/* root@SEU_IP_VPS:/root/

# Ou via SFTP/FileZilla
# Faça upload de todos os arquivos para /root/
```

## 2. Conectar ao VPS
```bash
ssh root@SEU_IP_VPS
```

## 3. Verificar arquivos
```bash
ls -la /root/
ls -la /root/api/
```

## 4. Executar instalação
```bash
chmod +x instalar_rapido.sh
sudo bash instalar_rapido.sh
```

## 5. Verificar instalação
```bash
chmod +x verificar_instalacao.sh
bash verificar_instalacao.sh
```

## 6. Acessar interface
- URL: http://SEU_IP_VPS:8080
- Crie uma sessão chamada "default"
- Escaneie o QR Code com WhatsApp

## 7. Testar
- Acesse: http://SEU_IP_VPS/teste_simples.php
- Verifique se está funcionando

## 📞 Suporte
Se houver problemas, verifique os logs:
```bash
pm2 logs wppconnect
```
EOF

# Criar script de instalação completa
cat > instalar_completo.sh << 'EOF'
#!/bin/bash

echo "🚀 INSTALAÇÃO COMPLETA WPPConnect"
echo "================================="

# Verificar se está como root
if [ "$EUID" -ne 0 ]; then
    echo "❌ Execute como root: sudo bash instalar_completo.sh"
    exit 1
fi

# Atualizar sistema
echo "📦 Atualizando sistema..."
apt update && apt upgrade -y

# Instalar dependências
echo "📦 Instalando dependências..."
apt install -y nodejs npm git nginx curl wget

# Verificar Node.js
NODE_VERSION=$(node --version | cut -d'v' -f2 | cut -d'.' -f1)
if [ "$NODE_VERSION" -lt 16 ]; then
    echo "📦 Instalando Node.js 18..."
    curl -fsSL https://deb.nodesource.com/setup_18.x | bash -
    apt install -y nodejs
fi

echo "✅ Node.js $(node --version) instalado"

# Baixar WPPConnect
echo "📁 Baixando WPPConnect..."
cd /opt
git clone https://github.com/wppconnect-team/wppconnect-server.git wppconnect
cd wppconnect

# Instalar dependências
echo "📦 Instalando dependências do WPPConnect..."
npm install

# Instalar PM2
echo "📦 Instalando PM2..."
npm install -g pm2

# Configurar
echo "⚙️ Configurando..."
cat > .env << 'ENVEOF'
PORT=8080
HOST=0.0.0.0
SECRET_KEY=wppconnect_$(date +%s)
CORS_ORIGIN=*
WEBHOOK_BY_EVENTS=false
SESSION_DATA_PATH=/opt/wppconnect/sessions
ENVEOF

# Iniciar
echo "🚀 Iniciando WPPConnect..."
pm2 start src/server.js --name wppconnect
pm2 save
pm2 startup

# Configurar Nginx
echo "🌐 Configurando Nginx..."
cat > /etc/nginx/sites-available/wppconnect << 'NGINXEOF'
server {
    listen 80;
    server_name _;
    
    location / {
        proxy_pass http://localhost:8080;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection 'upgrade';
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
        proxy_cache_bypass $http_upgrade;
    }
}
NGINXEOF

ln -sf /etc/nginx/sites-available/wppconnect /etc/nginx/sites-enabled/
rm -f /etc/nginx/sites-enabled/default
systemctl restart nginx

# Configurar firewall
echo "🔥 Configurando firewall..."
ufw allow 80/tcp
ufw allow 443/tcp
ufw allow 22/tcp

echo "✅ INSTALAÇÃO CONCLUÍDA!"
echo "================================="
echo "🌐 Acesse: http://$(curl -s ifconfig.me):8080"
echo "📱 Crie uma sessão e escaneie o QR Code"
echo "📊 Status: pm2 status"
echo "📋 Logs: pm2 logs wppconnect"
EOF

chmod +x instalar_completo.sh

# Criar arquivo de teste rápido
cat > testar_rapido.php << 'EOF'
<?php
echo "<h2>🧪 Teste Rápido WPPConnect</h2>";

// Testar conectividade
$url = 'http://localhost:8080/api/sessions/find';
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "<h3>1. 🔧 Teste de API</h3>";
echo "URL: $url<br>";
echo "HTTP Code: $http_code<br>";
echo "Resposta: <pre>" . htmlspecialchars($response) . "</pre><br>";

if ($http_code == 200) {
    echo "✅ API funcionando!<br>";
} else {
    echo "❌ API não está respondendo<br>";
}

// Testar PM2
echo "<h3>2. 📊 Status PM2</h3>";
$pm2_status = shell_exec('pm2 status 2>&1');
echo "<pre>" . htmlspecialchars($pm2_status) . "</pre>";

// Testar porta
echo "<h3>3. 🌐 Teste de Porta</h3>";
$port_status = shell_exec('netstat -tlnp | grep :8080 2>&1');
echo "<pre>" . htmlspecialchars($port_status) . "</pre>";

echo "<h3>🎯 Próximos Passos:</h3>";
echo "<ol>";
echo "<li>Acesse: <a href='http://localhost:8080' target='_blank'>Interface Web</a></li>";
echo "<li>Crie uma sessão chamada 'default'</li>";
echo "<li>Escaneie o QR Code com WhatsApp</li>";
echo "<li>Teste o envio de mensagens</li>";
echo "</ol>";
?>
EOF

echo "✅ Arquivos preparados em: upload_wppconnect/"
echo ""
echo "📁 Conteúdo do pacote:"
ls -la
echo ""
echo "🎯 Próximo passo: Upload para o VPS" 