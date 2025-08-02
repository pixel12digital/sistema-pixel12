# 🚀 CONFIGURAÇÃO DO WEBHOOK - PASSO A PASSO

## 📍 **PASSO 1: ENCONTRAR O PAINEL WHATSAPP**

### **🌐 Tente acessar essas URLs no browser:**

```
http://212.85.11.238:8080
http://212.85.11.238/admin  
http://212.85.11.238/dashboard
http://212.85.11.238:3000
http://212.85.11.238:3001
```

### **🔑 Credenciais para testar:**
- admin / admin
- admin / password
- whatsapp / whatsapp
- root / root

---

## 📱 **PASSO 2: CONFIGURAR O WEBHOOK**

### **✅ Quando encontrar o painel, procure por:**
- "Webhook"
- "API Settings" 
- "Configurações"
- "Sessões"
- "Bot Settings"

### **🎯 DADOS PARA CONFIGURAR:**

```
URL do Webhook: https://app.pixel12digital.com.br/painel/receber_mensagem_ana_local.php
Método: POST
Content-Type: application/json
Eventos: message (ou all messages)
```

---

## 🧪 **PASSO 3: TESTE IMEDIATO**

### **Teste 1 - Comercial (Rafael):**
Envie via WhatsApp Canal 3000:
```
"Quero um site para minha empresa"
```
**Resultado esperado:** Rafael recebe WhatsApp automático

### **Teste 2 - Suporte Técnico:**
Envie via WhatsApp Canal 3000:
```
"Meu site está fora do ar"
```
**Resultado esperado:** Equipe técnica recebe notificação

### **Teste 3 - Atendimento Humano:**
Envie via WhatsApp Canal 3000:
```
"Quero falar com uma pessoa"
```
**Resultado esperado:** Transferência para Canal 3001

---

## 📊 **PASSO 4: MONITORAR**

### **Dashboard de transferências:**
```
https://app.pixel12digital.com.br/painel/gestao_transferencias.php
```

### **Logs em tempo real:**
```
https://app.pixel12digital.com.br/painel/logs/
```

---

## 🔧 **SE NÃO ENCONTRAR O PAINEL WEB:**

### **Opção A: Configuração via arquivo**
O sistema pode usar configuração por arquivo `.env` ou `config.json`

### **Opção B: API direta**
Alguns sistemas permitem configurar via API REST

### **Opção C: Suporte do fornecedor**
Entre em contato com quem forneceu o sistema WhatsApp

---

## 🆘 **TROUBLESHOOTING**

### **Problema: Webhook não recebe dados**
1. Teste primeiro: https://app.pixel12digital.com.br/painel/debug_webhook.php
2. Verifique se a URL está correta
3. Confirme método POST

### **Problema: Ana não responde**
1. Teste: https://agentes.pixel12digital.com.br (deve estar online)
2. Verifique logs de erro no servidor

### **Problema: Transferências não funcionam**
1. Acesse o dashboard para verificar
2. Execute manualmente: https://app.pixel12digital.com.br/painel/api/executar_transferencias.php

---

## ✅ **CHECKLIST DE CONFIGURAÇÃO:**

- [ ] 🌐 Painel WhatsApp acessado
- [ ] 🔗 URL do webhook configurada
- [ ] ✉️ Método POST selecionado
- [ ] 🧪 Teste comercial funcionando (Rafael recebe)
- [ ] 🔧 Teste suporte funcionando (Equipe técnica recebe)
- [ ] 👥 Teste humano funcionando (Canal 3001 ativo)
- [ ] 📊 Dashboard mostrando estatísticas
- [ ] 🤖 Ana respondendo normalmente

---

## 🎉 **RESULTADO FINAL ESPERADO:**

✅ **Ana responde** mensagens normais  
✅ **Rafael recebe** apenas clientes comerciais  
✅ **Suporte recebe** apenas problemas técnicos  
✅ **Humanos recebem** pedidos gerais  
✅ **Sistema monitora** tudo em tempo real

**O sistema inteligente estará 100% ativo!** 🚀 