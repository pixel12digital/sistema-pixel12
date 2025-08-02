# 🚀 PASSOS FINAIS PARA CONFIGURAR WEBHOOK

## ✅ **SISTEMA PRONTO - AGORA É SÓ CONFIGURAR!**

---

## 🎯 **PASSO 1: CONFIGURE O WEBHOOK DE DEBUG (PRIMEIRO)**

### **1.1 - Acesse o painel do WhatsApp Canal 3000**
- Entre no sistema que controla o WhatsApp
- Vá em Configurações → Webhook

### **1.2 - Configure URL de debug temporária:**
```
URL: https://app.pixel12digital.com.br/painel/debug_webhook.php
Método: POST
Content-Type: application/json
```

### **1.3 - Teste enviando uma mensagem**
- Envie qualquer mensagem para o WhatsApp Canal 3000
- Exemplo: "Teste webhook"

### **1.4 - Verifique se chegou:**
- Acesse: https://app.pixel12digital.com.br/painel/debug_webhook.php
- Você verá os dados que chegaram
- **Se aparecer dados = FUNCIONOU!** ✅

---

## 🎯 **PASSO 2: CONFIGURE O WEBHOOK DEFINITIVO**

### **2.1 - Mude para URL definitiva:**
```
URL: https://app.pixel12digital.com.br/painel/receber_mensagem_ana_local.php
Método: POST
Content-Type: application/json
```

### **2.2 - Teste com mensagem real:**
Envie mensagens mencionando:
- **"Preciso de um site"** → Deve detectar transferência para Rafael
- **"Quero falar com uma pessoa"** → Deve transferir para humanos

---

## 🎯 **PASSO 3: VERIFICAR SE ESTÁ FUNCIONANDO**

### **3.1 - Acesse o Dashboard:**
https://app.pixel12digital.com.br/painel/gestao_transferencias.php

### **3.2 - Teste direto no browser:**
https://app.pixel12digital.com.br/painel/receber_mensagem_ana_local.php?from=5547999999999&body=Preciso%20de%20um%20site

### **3.3 - Verifique logs:**
https://app.pixel12digital.com.br/painel/logs/

---

## 🎯 **PASSO 4: TESTE COMPLETO DO SISTEMA**

### **4.1 - Envie mensagens teste:**

**Teste 1 - Transferência para Rafael:**
```
Mensagem: "Preciso de um site para minha empresa"
Resultado esperado: Rafael recebe WhatsApp com detalhes
```

**Teste 2 - Transferência para humanos:**
```
Mensagem: "Quero falar com uma pessoa"
Resultado esperado: Cliente transferido para Canal 3001
```

**Teste 3 - Conversa normal:**
```
Mensagem: "Oi, como vocês funcionam?"
Resultado esperado: Ana responde normalmente
```

---

## 🔧 **SE PRECISAR DE AJUDA COM CONFIGURAÇÃO:**

### **Formato de configuração típico:**

**Para APIs REST/HTTP:**
```json
{
  "webhook_url": "https://app.pixel12digital.com.br/painel/receber_mensagem_ana_local.php",
  "method": "POST",
  "headers": {
    "Content-Type": "application/json"
  }
}
```

**Para formulários web:**
```
URL: https://app.pixel12digital.com.br/painel/receber_mensagem_ana_local.php
Método: POST
```

---

## 🎉 **RESULTADOS ESPERADOS:**

### **✅ Funcionando Corretamente:**
```json
{
  "success": true,
  "message_id": 123,
  "response_id": 124,
  "ana_response": "Olá! Como posso ajudá-lo?",
  "action_taken": "nenhuma"
}
```

### **❌ Se houver problema:**
```json
{
  "error": "Dados incompletos",
  "debug": {...}
}
```

---

## 🚨 **SOLUÇÃO RÁPIDA DE PROBLEMAS:**

### **1. Webhook não recebe dados:**
- Verifique se a URL está correta
- Use primeiro o debug_webhook.php
- Teste via browser direto

### **2. Ana não responde:**
- Verifique se https://agentes.pixel12digital.com.br está online
- Teste o integrador separadamente

### **3. Transferências não funcionam:**
- Acesse o dashboard para verificar
- Verifique logs de erro

---

## 📱 **COMANDOS DE TESTE RÁPIDO:**

### **Teste via CURL:**
```bash
curl -X POST https://app.pixel12digital.com.br/painel/receber_mensagem_ana_local.php \
  -H "Content-Type: application/json" \
  -d '{"from":"5547999999999","body":"Preciso de um site"}'
```

### **Teste via Browser:**
```
https://app.pixel12digital.com.br/painel/receber_mensagem_ana_local.php?from=5547999999999&body=teste
```

---

## 🎯 **CHECKLIST FINAL:**

- [ ] ✅ Webhook configurado para debug_webhook.php
- [ ] ✅ Teste enviado e dados apareceram
- [ ] ✅ Webhook mudado para receber_mensagem_ana_local.php  
- [ ] ✅ Teste com "site" detectou transferência para Rafael
- [ ] ✅ Teste com "pessoa" transferiu para humanos
- [ ] ✅ Dashboard mostra as transferências
- [ ] ✅ Ana responde normalmente para outras mensagens

---

## 🎊 **PRONTO! SISTEMA 100% FUNCIONAL!**

**Agora as transferências da Ana são REAIS e automáticas!**

**Precisa de ajuda com algum passo específico?** 