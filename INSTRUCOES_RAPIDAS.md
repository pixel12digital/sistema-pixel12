# 🚀 Instruções Rápidas - Sistema WhatsApp Web

## ⚡ **Início Rápido**

### **1. Iniciar o Robô**
```bash
cd /c/xampp/htdocs/loja-virtual-revenda
node index.js
```

### **2. Conectar WhatsApp**
1. Acesse: `http://localhost:8080/loja-virtual-revenda/painel/`
2. Vá em **Comunicação → Gerenciar Canais**
3. Clique em **"Atualizar status"**
4. Escaneie o QR Code

### **3. Testar Envio**
```bash
curl -X POST http://localhost:3000/send \
  -H "Content-Type: application/json" \
  -d '{"to":"5561982428290","message":"Teste da nova solução"}'
```

## 🔧 **Configuração Automática (Opcional)**

### **Verificação de Status**
```bash
# Executar manualmente
php verificar_status_automatico.php

# Ou configurar cron (a cada 5 minutos)
0,5,10,15,20,25,30,35,40,45,50,55 * * * * php /caminho/para/verificar_status_automatico.php
```

## 📊 **Monitoramento**

### **Status do Robô**
```bash
curl http://localhost:3000/status
```

### **Verificar Logs**
```bash
# Logs de status (se configurado)
tail -f logs/status_check_$(date +%Y-%m-%d).log

# Logs do robô (se usando PM2)
pm2 logs whatsapp-robo
```

## 🚨 **Problemas Comuns**

### **WhatsApp Não Conecta**
```bash
# Limpar sessão
rm -rf ./.wwebjs_auth

# Reiniciar
node index.js
```

### **Mensagens com "Risco"**
- Aguarde 10-30 segundos
- Sistema verifica automaticamente
- Retry automático após 1 hora

### **Porta 3000 Ocupada**
```bash
# Verificar processo
netstat -an | grep 3000

# Matar processo se necessário
taskkill /F /PID [PID]
```

## 📁 **Arquivos Importantes**

- 🤖 `index.js` - Robô WhatsApp Web
- 📋 `verificar_status_automatico.php` - Monitoramento
- 📖 `README.md` - Documentação completa
- 📝 `CHANGELOG.md` - Histórico de mudanças

## ✅ **O que Mudou**

### **Antes (Problemas)**
- ❌ Mensagens com "risco" não entregues
- ❌ Bloqueios frequentes
- ❌ Sem monitoramento de status
- ❌ Dependência de APIs de terceiros

### **Agora (Solução)**
- ✅ WhatsApp Web direto (mais confiável)
- ✅ Monitoramento automático de status
- ✅ Retry automático após 1 hora
- ✅ Logs detalhados de todas as operações

## 🎯 **Resultado Esperado**

- ✅ **Mensagens entregues** com status correto (✓✓)
- ✅ **Menos bloqueios** do WhatsApp
- ✅ **Monitoramento** em tempo real
- ✅ **Recuperação automática** de falhas

---

**💡 Dica**: O WhatsApp Web é mais confiável porque usa a mesma interface que você usa no navegador! 