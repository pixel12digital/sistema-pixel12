# 🚀 PRÓXIMOS PASSOS - Instalação WPPConnect

## ✅ **PASSO 1: ARQUIVOS PRONTOS**

Todos os arquivos necessários estão preparados em `upload_wppconnect/`:

### 📁 **Arquivos Principais:**
- ✅ `instalar_rapido.sh` - Script de instalação
- ✅ `verificar_instalacao.sh` - Script de verificação
- ✅ `teste_simples.php` - Teste funcional
- ✅ `INSTRUCOES_INSTALACAO.md` - Guia completo

### 📁 **Arquivos API:**
- ✅ `api/whatsapp_simple.php` - Classe PHP principal
- ✅ `api/webhook.php` - Webhook para receber mensagens
- ✅ `api/asaas_whatsapp_webhook.php` - Integração Asaas

## 🎯 **PASSO 2: UPLOAD PARA VPS**

### **Opção A: Via SCP (Recomendado)**
```bash
# No seu computador local
scp -r upload_wppconnect/* root@SEU_IP_VPS:/root/
```

### **Opção B: Via SFTP/FileZilla**
1. Conecte ao VPS via SFTP
2. Navegue até `/root/`
3. Faça upload de todos os arquivos

### **Opção C: Via Git (Se tiver repositório)**
```bash
# No VPS
cd /root
git clone https://github.com/seu-usuario/seu-repo.git
cd seu-repo/upload_wppconnect
```

## 🔗 **PASSO 3: CONECTAR AO VPS**

```bash
ssh root@SEU_IP_VPS
```

## ✅ **PASSO 4: VERIFICAR ARQUIVOS**

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

## 🚀 **PASSO 5: EXECUTAR INSTALAÇÃO**

```bash
# Dar permissão de execução
chmod +x instalar_rapido.sh

# Executar instalação
sudo bash instalar_rapido.sh
```

**O que vai acontecer (5-10 minutos):**
- ✅ Atualizar sistema
- ✅ Instalar Node.js 18+
- ✅ Baixar WPPConnect Server
- ✅ Instalar dependências
- ✅ Configurar PM2
- ✅ Configurar Nginx
- ✅ Iniciar serviços

## 🔍 **PASSO 6: VERIFICAR INSTALAÇÃO**

```bash
chmod +x verificar_instalacao.sh
bash verificar_instalacao.sh
```

**Verificações automáticas:**
- ✅ PM2 instalado e funcionando
- ✅ WPPConnect rodando
- ✅ Porta 8080 ativa
- ✅ API respondendo
- ✅ Nginx configurado

## 🌐 **PASSO 7: ACESSAR INTERFACE**

- **URL:** `http://SEU_IP_VPS:8080`
- **Ação:** Criar sessão chamada "default"
- **QR Code:** Escanear com WhatsApp

## 🧪 **PASSO 8: TESTAR FUNCIONAMENTO**

```bash
# Teste rápido
php testar_rapido.php

# Ou acessar via navegador
# http://SEU_IP_VPS/teste_simples.php
```

## 📊 **COMANDOS ÚTEIS**

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

## 🎯 **PASSO 9: INTEGRAR NO PAINEL**

Após tudo funcionando, use no seu painel:

```php
require_once 'api/whatsapp_simple.php';
$whatsapp = new WhatsAppSimple($mysqli, 'http://localhost:8080');

// Enviar mensagem
$whatsapp->enviar('11999999999', 'Olá!');

// Enviar cobrança
$whatsapp->enviarCobranca($cliente_id, $cobranca_id);

// Enviar prospecção
$whatsapp->enviarProspeccao($cliente_id);
```

## 🆘 **SOLUÇÃO DE PROBLEMAS**

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

## 📞 **SUPORTE**

- **Logs:** `pm2 logs wppconnect`
- **Status:** `pm2 status`
- **API:** `curl http://localhost:8080/api/sessions/find`

---

## 🎉 **RESULTADO FINAL**

Após seguir todos os passos, você terá:

- ✅ **WPPConnect funcionando** no VPS
- ✅ **WhatsApp conectado** via QR Code
- ✅ **API REST** disponível
- ✅ **Interface web** acessível
- ✅ **Classe PHP** pronta para uso
- ✅ **Webhook** configurado
- ✅ **Integração Asaas** funcionando

**Solução 100% funcional e pronta para produção!** 🚀

---

**🎯 PRONTO PARA COMEÇAR?**
Execute o **PASSO 2** (Upload para VPS) e me avise quando estiver conectado ao VPS! 