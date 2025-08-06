# 🔍 INSTRUÇÕES PARA DEBUG DO WEBHOOK

## 🎯 SITUAÇÃO ATUAL

**Problema:** Mensagens reais do WhatsApp não estão chegando ao chat e a Ana não está respondendo.

**Status:** 
- ✅ Webhooks configurados corretamente
- ✅ Sistema funcionando com testes simulados
- ✅ Ana respondendo corretamente
- ⚠️ Mensagens reais não estão sendo processadas

## 🔧 CONFIGURAÇÃO ATUAL PARA DEBUG

**Webhooks configurados para debug:**
- Canal 3000: `https://app.pixel12digital.com.br/debug_webhook_real.php`
- Canal 3001: `https://app.pixel12digital.com.br/debug_webhook_real.php`

## 📋 PASSOS PARA DEBUG

### 1️⃣ ENVIAR MENSAGEM REAL
1. Envie uma mensagem real para o WhatsApp
2. Pode ser qualquer mensagem, por exemplo: "Teste debug"

### 2️⃣ VERIFICAR DADOS RECEBIDOS
1. Acesse: `https://app.pixel12digital.com.br/debug_webhook_real.php`
2. Você verá exatamente os dados que chegaram do WhatsApp
3. Verifique se há dados ou se está vazio

### 3️⃣ VERIFICAR LOGS
1. Acesse o arquivo: `logs/debug_webhook_2025-08-06.log`
2. Verifique se há registros das mensagens
3. Analise o formato dos dados

### 4️⃣ ANALISAR PROBLEMAS
**Se não há dados:**
- O WhatsApp não está enviando para o webhook
- Problema de conectividade
- Problema no servidor VPS

**Se há dados mas formato diferente:**
- O formato dos dados é diferente do esperado
- Precisa ajustar o processamento

**Se há dados e formato correto:**
- O problema está no processamento
- Verificar se está sendo salvo no banco

## 🔄 VOLTAR AO NORMAL

Após identificar o problema, execute:
```bash
php configurar_webhook_normal.php
```

## 📊 INFORMAÇÕES TÉCNICAS

### URLs dos Webhooks
- **Debug:** `https://app.pixel12digital.com.br/debug_webhook_real.php`
- **Normal:** `https://app.pixel12digital.com.br/webhook_sem_redirect/webhook.php`

### Servidor VPS
- **IP:** 212.85.11.238
- **Porta 3000:** Canal principal
- **Porta 3001:** Canal comercial

### Arquivos de Log
- **Debug:** `logs/debug_webhook_YYYY-MM-DD.log`
- **Webhook:** `logs/webhook_sem_redirect_YYYY-MM-DD.log`

## 🎯 PRÓXIMOS PASSOS

1. **Envie uma mensagem real** para o WhatsApp
2. **Verifique os dados** em `https://app.pixel12digital.com.br/debug_webhook_real.php`
3. **Analise os logs** em `logs/debug_webhook_2025-08-06.log`
4. **Reporte os resultados** para continuarmos o diagnóstico

## 🔍 POSSÍVEIS CAUSAS

1. **WhatsApp não está enviando:** Problema na configuração do WhatsApp
2. **Conectividade:** Problema de rede ou firewall
3. **Formato de dados:** Dados chegam em formato diferente
4. **Processamento:** Erro no processamento dos dados
5. **Banco de dados:** Problema ao salvar no banco

## 📞 SUPORTE

Se precisar de ajuda adicional:
1. Compartilhe os dados que aparecem no debug
2. Compartilhe os logs
3. Descreva exatamente o que acontece quando envia uma mensagem 