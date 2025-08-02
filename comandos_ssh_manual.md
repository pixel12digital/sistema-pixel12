# 🔧 COMANDOS SSH PARA CONFIGURAR WEBHOOK - CONFORME README

## 📡 **CONECTAR NA VPS:**
```bash
ssh root@212.85.11.238
```

---

## 🚀 **OPÇÃO A: SCRIPT AUTOMÁTICO**

### 1. Criar e executar script:
```bash
# Criar script na VPS
cat > configurar_webhook.sh << 'EOF'
[COPIAR CONTEÚDO DO script_ssh_configurar_webhook.sh AQUI]
EOF

# Dar permissão e executar
chmod +x configurar_webhook.sh
./configurar_webhook.sh
```

---

## ⚡ **OPÇÃO B: COMANDOS MANUAIS (CONFORME README)**

### 1. Verificar status atual:
```bash
curl -s http://127.0.0.1:3000/status | jq .
curl -s http://127.0.0.1:3000/webhook/status
```

### 2. Testar webhooks (escolha um que funcione):
```bash
# Teste webhook original (conforme README)
curl -X POST https://app.pixel12digital.com.br/painel/receber_mensagem_ana_local.php \
  -H "Content-Type: application/json" \
  -d '{"from":"5547999999999","body":"teste ssh"}'

# Teste webhook alternativo
curl -X POST https://app.pixel12digital.com.br/webhook.php \
  -H "Content-Type: application/json" \
  -d '{"from":"5547999999999","body":"teste ssh"}'
```

### 3. Configurar webhook (use a URL que funcionou):
```bash
# Para webhook original (PRIMEIRO - conforme README):
curl -X POST http://127.0.0.1:3000/webhook/config \
  -H "Content-Type: application/json" \
  -d '{"url":"https://app.pixel12digital.com.br/painel/receber_mensagem_ana_local.php"}'

# OU para webhook alternativo:
curl -X POST http://127.0.0.1:3000/webhook/config \
  -H "Content-Type: application/json" \
  -d '{"url":"https://app.pixel12digital.com.br/webhook.php"}'
```

### 4. Verificar configuração:
```bash
curl -s http://127.0.0.1:3000/webhook/status
```

### 5. Testar webhook (conforme README):
```bash
curl -X POST http://127.0.0.1:3000/webhook/test
```

### 6. Teste final de envio:
```bash
curl -X POST http://127.0.0.1:3000/send/text \
  -H "Content-Type: application/json" \
  -d '{"sessionName":"default","number":"5547999999999","message":"🎉 Ana configurada!"}'
```

---

## ✅ **VERIFICAÇÕES (CONFORME README):**

### Verificar PM2:
```bash
pm2 status
pm2 logs whatsapp-3000 --lines 10
```

### Verificar sessões:
```bash
curl -s http://127.0.0.1:3000/sessions | jq .
curl -s http://127.0.0.1:3001/sessions | jq .
```

### Verificar conectividade:
```bash
curl -s http://127.0.0.1:3000/status | jq .
curl -s http://127.0.0.1:3001/status | jq .
```

---

## 🔄 **ROLLBACK (se necessário):**
```bash
# Voltar para configuração anterior
curl -X POST http://127.0.0.1:3000/webhook/config \
  -H "Content-Type: application/json" \
  -d '{"url":"URL_ANTERIOR_AQUI"}'
```

---

## 📊 **MONITORAMENTO (CONFORME README):**
```bash
# Logs em tempo real
pm2 logs whatsapp-3000 --lines 20

# Status dos canais
curl -s http://127.0.0.1:3000/status | jq .
curl -s http://127.0.0.1:3001/status | jq .

# Verificar webhook
curl -s http://127.0.0.1:3000/webhook/status

# Logs das transferências (conforme README)
tail -f /var/www/html/loja-virtual-revenda/painel/logs/webhook_debug.log
```

---

## 🎯 **APÓS CONFIGURAR:**

1. **Teste via WhatsApp:** Envie "olá" para o número
2. **Monitore logs:** `pm2 logs whatsapp-3000`
3. **Verifique transferências:** Teste "quero um site", "problema", "pessoa"
4. **Dashboard:** https://app.pixel12digital.com.br/painel/gestao_transferencias.php 