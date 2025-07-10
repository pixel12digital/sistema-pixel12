# 📱 Instalação WPPConnect - Passo a Passo

## 🎯 OBJETIVO
Instalar WPPConnect no VPS e configurar integração com WhatsApp

## 📋 PRÉ-REQUISITOS
- VPS com Ubuntu/Debian
- Acesso root
- Domínio configurado (opcional)

## 🚀 PASSO A PASSO

### 1. 📤 UPLOAD DOS ARQUIVOS

#### Opção A: Via SCP (Recomendado)
```bash
# No seu computador local
scp -r upload_wppconnect/* root@SEU_IP_VPS:/root/
```

#### Opção B: Via SFTP/FileZilla
1. Conecte ao VPS via SFTP
2. Navegue até `/root/`
3. Faça upload de todos os arquivos

#### Opção C: Via Git
```bash
# No VPS
cd /root
git clone https://github.com/seu-usuario/seu-repo.git
cd seu-repo
```

### 2. 🔗 CONECTAR AO VPS
```bash
ssh root@SEU_IP_VPS
```

### 3. ✅ VERIFICAR ARQUIVOS
```bash
ls -la /root/
ls -la /root/api/
```

**Resultado esperado:**
- `instalar_rapido.sh`
- `verificar_instalacao.sh`
- `teste_simples.php`
- `api/whatsapp_simple.php`
- `api/webhook.php`

### 4. 🚀 EXECUTAR INSTALAÇÃO
```bash
# Dar permissão de execução
chmod +x instalar_rapido.sh

# Executar instalação
sudo bash instalar_rapido.sh
```

**O que vai acontecer:**
- ✅ Instalar Node.js 18+
- ✅ Baixar WPPConnect Server
- ✅ Instalar dependências
- ✅ Configurar PM2
- ✅ Configurar Nginx
- ✅ Iniciar serviços

### 5. 🔍 VERIFICAR INSTALAÇÃO
```bash
chmod +x verificar_instalacao.sh
bash verificar_instalacao.sh
```

**Verificações:**
- ✅ PM2 instalado e funcionando
- ✅ WPPConnect rodando
- ✅ Porta 8080 ativa
- ✅ API respondendo
- ✅ Nginx configurado

### 6. 🌐 ACESSAR INTERFACE
- **URL:** `http://SEU_IP_VPS:8080`
- **Ação:** Criar sessão chamada "default"
- **QR Code:** Escanear com WhatsApp

### 7. 🧪 TESTAR FUNCIONAMENTO
```bash
# Teste rápido via PHP
php testar_rapido.php

# Ou acessar via navegador
# http://SEU_IP_VPS/teste_simples.php
```

## 📊 COMANDOS ÚTEIS

### Verificar Status
```bash
pm2 status
pm2 logs wppconnect
```

### Reiniciar Serviços
```bash
pm2 restart wppconnect
systemctl restart nginx
```

### Verificar Portas
```bash
netstat -tlnp | grep :8080
ufw status
```

## 🎯 PRÓXIMOS PASSOS

### 1. Configurar Domínio (Opcional)
```bash
# Obter SSL
certbot --nginx -d wpp.seudominio.com
```

### 2. Integrar no Painel
```php
require_once 'api/whatsapp_simple.php';
$whatsapp = new WhatsAppSimple($mysqli, 'http://localhost:8080');
$whatsapp->enviar('11999999999', 'Olá!');
```

### 3. Configurar Webhook Asaas
- URL: `https://seudominio.com/api/asaas_whatsapp_webhook.php`
- Eventos: PAYMENT_RECEIVED, PAYMENT_OVERDUE

## 🆘 SOLUÇÃO DE PROBLEMAS

### WPPConnect não inicia
```bash
cd /opt/wppconnect
pm2 start src/server.js --name wppconnect
pm2 logs wppconnect
```

### Porta 8080 não acessível
```bash
ufw allow 8080/tcp
systemctl restart nginx
```

### Node.js versão antiga
```bash
curl -fsSL https://deb.nodesource.com/setup_18.x | bash -
apt install -y nodejs
```

## 📞 SUPORTE
- **Logs:** `pm2 logs wppconnect`
- **Status:** `pm2 status`
- **API:** `curl http://localhost:8080/api/sessions/find`

---

**✅ Se tudo funcionou, você tem WhatsApp integrado!** 🎉 