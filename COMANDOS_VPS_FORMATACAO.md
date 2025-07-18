# 🔧 Comandos para Atualizar Formatação na VPS

## 🎯 Objetivo
Aplicar a formatação simplificada de números no servidor WhatsApp API da VPS.

---

## 📋 Opção 1: Script Automático

### **1. Fazer upload do script:**
```bash
# No seu computador local
scp atualizar_formatacao_vps.sh root@212.85.11.238:/tmp/
```

### **2. Executar na VPS:**
```bash
# Conectar na VPS
ssh root@212.85.11.238

# Executar o script
chmod +x /tmp/atualizar_formatacao_vps.sh
/tmp/atualizar_formatacao_vps.sh
```

---

## 📋 Opção 2: Comandos Manuais

### **1. Conectar na VPS:**
```bash
ssh root@212.85.11.238
```

### **2. Fazer backup:**
```bash
cd /var/whatsapp-api
cp whatsapp-api-server.js whatsapp-api-server.js.backup.$(date +%Y%m%d_%H%M%S)
```

### **3. Editar o arquivo:**
```bash
nano whatsapp-api-server.js
```

### **4. Localizar e substituir a função (linha ~172):**
```javascript
// Função simplificada para formatar número (apenas código do país + DDD + número)
function formatarNumeroWhatsapp(numero) {
  // Remover todos os caracteres não numéricos
  numero = String(numero).replace(/\D/g, '');
  
  // Se já tem código do país (55), remover para processar
  if (numero.startsWith('55')) {
    numero = numero.slice(2);
  }
  
  // Verificar se tem pelo menos DDD (2 dígitos) + número (8 dígitos)
  if (numero.length < 10) {
    return null; // Número muito curto
  }
  
  // Extrair DDD e número
  const ddd = numero.slice(0, 2);
  const telefone = numero.slice(2);
  
  // Retornar no formato: 55 + DDD + número + @c.us
  // Deixar o número como está (você gerencia as regras no cadastro)
  return '55' + ddd + telefone + '@c.us';
}
```

### **5. Corrigir as chamadas (linhas ~300 e ~400):**
```javascript
// Mudar de:
const msg = await addToMessageQueue(numeroAjustado + '@c.us', message);
// Para:
const msg = await addToMessageQueue(numeroAjustado, message);

// E também:
const msg = await client.sendMessage(numeroAjustado + '@c.us', message);
// Para:
const msg = await client.sendMessage(numeroAjustado, message);
```

### **6. Salvar e testar:**
```bash
# Testar sintaxe
node -c whatsapp-api-server.js

# Se OK, reiniciar
pm2 restart whatsapp-api

# Verificar status
pm2 status
```

---

## 🧪 Teste da Atualização

### **1. Teste básico:**
```bash
curl -X POST http://localhost:3000/send \
  -H "Content-Type: application/json" \
  -d '{"to": "4799616469", "message": "Teste formatação simplificada"}'
```

### **2. Verificar resposta:**
```json
{
  "success": true,
  "messageId": "true_554799616469@c.us_...",
  "status": "enviado",
  "queuePosition": 1
}
```

### **3. Verificar logs:**
```bash
pm2 logs whatsapp-api --lines 20
```

---

## 🔄 Rollback (se necessário)

### **Se algo der errado:**
```bash
# Restaurar backup
cp whatsapp-api-server.js.backup.* whatsapp-api-server.js

# Reiniciar
pm2 restart whatsapp-api

# Verificar
pm2 status
```

---

## ✅ Resultado Esperado

Após a atualização:
- ✅ Função simplificada aplicada
- ✅ Servidor reiniciado sem erros
- ✅ Teste de envio funcionando
- ✅ Números formatados como: `55 + DDD + número + @c.us`

**Exemplo:**
- Entrada: `4799616469`
- Saída: `554799616469@c.us` 