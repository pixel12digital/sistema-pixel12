# 📱 WhatsApp Multi-Canal API

Sistema de WhatsApp multi-canal com duas instâncias independentes (default e comercial) gerenciadas por PM2.

## 🧪 Como Executar os Testes

```bash
# Conectar ao VPS
ssh root@212.85.11.238
cd /var/whatsapp-api

# Tornar scripts executáveis
chmod +x *.sh

# Executar testes
./teste_fluxo_completo_whatsapp.sh
./teste_final_producao.sh
./validacao_numero_real.sh
```

**⚠️ IMPORTANTE:** Sempre copie só o texto após `#` ou `$` – nunca inclua os símbolos de debug (🚩, 🔥, ✅) no prompt.

## ✅ Testes e Validações

### **Validação de Sessões:**
- **Porta 3000 (default):** 1 sessão, `hasClient=true`
- **Porta 3001 (comercial):** 1 sessão, `hasClient=true`

### **Envio de Mensagens:**
- **Testado para o número real `554796164699` em ambos os canais** → ✅ Sucesso
- **API Default:** `"success":true`
- **API Comercial:** `"success":true`

### **Verificação de Número:**
- **Porta 3000:** `isRegistered=true`
- **Porta 3001:** `isRegistered=true`

### **Webhooks:**
- **Configurados em:** `http://212.85.11.238:8080/api/webhook.php` → ✅ Sucesso
- **Ambos os canais:** Webhooks funcionando corretamente

### **Acesso Externo:**
- **API 3000 e 3001 acessíveis externamente** → ✅ Sucesso
- **URLs públicas funcionando:** `http://212.85.11.238:3000` e `http://212.85.11.238:3001`

### **Painel Administrativo:**
- **Canais conectados e enviando mensagens via interface** → ✅ Sucesso
- **QR Codes funcionando sem "undefined"**
- **Status atualizado corretamente**

### **Monitoramento Automático (cron):**
Entrada no `crontab`:
```cron
*/5 * * * * cd /var/whatsapp-api && ./monitoramento_automatico.sh >> /var/whatsapp-api/monitoramento.log 2>&1
```

### **Estatísticas de Validação:**
- **Testes realizados:** 6
- **Sucessos:** 6
- **Taxa de sucesso:** 100%
- **Status:** ✅ **SISTEMA 100% OPERACIONAL**

## 🚀 Operação e Manutenção

### **Comandos Essenciais:**

```bash
# Verificar status dos processos
pm2 status

# Ver logs em tempo real
pm2 logs whatsapp-3000 --lines 20
pm2 logs whatsapp-3001 --lines 20

# Reiniciar processos
pm2 restart all

# Verificar sessões ativas
curl -s http://127.0.0.1:3000/sessions | jq .
curl -s http://127.0.0.1:3001/sessions | jq .

# Testar envio de mensagem
curl -X POST http://127.0.0.1:3000/send/text \
  -H "Content-Type: application/json" \
  -d '{"sessionName":"default","number":"554796164699","message":"Teste"}'
```

### **URLs de Acesso:**

- **API Default:** http://212.85.11.238:3000
- **API Comercial:** http://212.85.11.238:3001
- **Painel Administrativo:** http://212.85.11.238:8080/painel/
- **Comunicação:** http://212.85.11.238:8080/painel/comunicacao.php

### **Monitoramento:**

```bash
# Verificar uso de recursos
pm2 monit

# Ver logs de erro
pm2 logs whatsapp-3000 --err --lines 50
pm2 logs whatsapp-3001 --err --lines 50

# Verificar conectividade
curl -s http://212.85.11.238:3000/status | jq .
curl -s http://212.85.11.238:3001/status | jq .
```

### **Troubleshooting:**

1. **Se as sessões não aparecerem:**
   ```bash
   pm2 restart all
   sleep 30
   curl -s http://127.0.0.1:3000/sessions | jq .
   ```

2. **Se o envio falhar:**
   ```bash
   pm2 logs whatsapp-3000 --lines 20
   curl -s http://127.0.0.1:3000/qr?session=default | jq .
   ```

3. **Se o painel não funcionar:**
   - Verifique se o Apache está rodando: `systemctl status apache2`
   - Verifique permissões: `ls -la /var/www/html/painel/`

## 📋 Checklist de Validação

### **✅ Sistema Operacional:**
- [x] PM2 processos online
- [x] Sessões conectadas em ambas as portas
- [x] QR Codes disponíveis (se necessário)
- [x] Envio de mensagens funcionando
- [x] Painel administrativo acessível
- [x] Webhooks configurados

### **✅ Logs Esperados:**
- `🚩 [STARTUP] Porta X → sessão="Y"`
- `🚩 [AUTO-START] Iniciando sessão "Y" automaticamente...`
- `🎯 [AUTO-POST] Status interno: 200`
- `✅ [READY] whatsappClients["Y"] registrado com sucesso`

## 🔧 Configuração de Webhooks

```bash
# Configurar webhook para recebimento
curl -X POST http://127.0.0.1:3000/webhook/config \
  -H "Content-Type: application/json" \
  -d '{"url":"http://212.85.11.238:8080/api/webhook.php"}'

# Testar webhook
curl -X POST http://127.0.0.1:3000/webhook/test
```

## 📞 Suporte

Para problemas ou dúvidas:
1. Verifique os logs: `pm2 logs whatsapp-3000 --lines 50`
2. Execute o teste: `./teste_final_producao.sh`
3. Reinicie se necessário: `pm2 restart all`

---

**🎉 Sistema WhatsApp Multi-Canal 100% Operacional e Validado!**

**Última Validação:** $(date)
**Número Testado:** 554796164699
**Status:** ✅ **APROVADO PARA PRODUÇÃO** 