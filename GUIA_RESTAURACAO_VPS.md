# 🔧 GUIA DE RESTAURAÇÃO VPS - WHATSAPP API

## 📋 RESUMO DA RESTAURAÇÃO

Baseado na análise do código do commit restaurado, identificamos as seguintes configurações:

### 🖥️ VPS
- **IP**: 212.85.11.238
- **Usuário**: root
- **Serviços**: WhatsApp API em portas 3000 e 3001

### 📡 Canais WhatsApp
1. **Canal 3000 (Financeiro)**
   - Porta: 3000
   - Identificador: 554797146908@c.us
   - Nome: Pixel12Digital
   - Função: Ana (automação)

2. **Canal 3001 (Comercial)**
   - Porta: 3001
   - Identificador: 554797309525@c.us
   - Nome: Comercial - Pixel
   - Função: Humano (atendimento)

### 🔗 Webhooks
- **Produção**: https://app.pixel12digital.com.br/painel/receber_mensagem_ana_local.php
- **Local**: http://localhost:8080/loja-virtual-revenda/api/webhook_whatsapp.php
- **Alternativo**: https://revendawebvirtual.com.br/api/webhook_whatsapp.php

---

## 🚀 PROCESSO DE RESTAURAÇÃO

### ETAPA 1: Restaurar VPS via SSH

Execute o script SSH na VPS:

```bash
# Conectar na VPS e executar o script
ssh root@212.85.11.238 'bash -s' < comandos_ssh_restauracao.sh
```

**O que o script faz:**
1. ✅ Para todos os processos existentes
2. ✅ Cria/atualiza diretório `/var/whatsapp-api`
3. ✅ Instala/cria WhatsApp API
4. ✅ Configura PM2 para auto-restart
5. ✅ Inicia serviços nas portas 3000 e 3001
6. ✅ Configura webhooks para produção
7. ✅ Verifica status final

### ETAPA 2: Restaurar Configurações Locais

Execute o script PHP local:

```bash
# Executar script de restauração local
php restaurar_vps_completo.php
```

**O que o script faz:**
1. ✅ Verifica conectividade com VPS
2. ✅ Testa serviços dos canais
3. ✅ Configura webhooks
4. ✅ Testa envios de mensagem
5. ✅ Atualiza banco de dados local
6. ✅ Gera relatório final

---

## 🔍 VERIFICAÇÕES PÓS-RESTAURAÇÃO

### 1. Verificar Status da VPS

```bash
# Conectar na VPS
ssh root@212.85.11.238

# Verificar status PM2
pm2 status

# Verificar portas
netstat -tulpn | grep -E ":(3000|3001)"

# Verificar logs
pm2 logs --lines 20
```

### 2. Testar Endpoints

```bash
# Testar status dos canais
curl http://212.85.11.238:3000/status
curl http://212.85.11.238:3001/status

# Verificar webhooks
curl http://212.85.11.238:3000/webhook/config
curl http://212.85.11.238:3001/webhook/config

# Testar envio (substitua o número)
curl -X POST http://212.85.11.238:3000/send/text \
  -H "Content-Type: application/json" \
  -d '{"to":"5511999999999","message":"Teste restauração"}'
```

### 3. Verificar Painel Local

1. Acesse: `http://localhost:8080/loja-virtual-revenda/painel/comunicacao.php`
2. Verifique se os canais aparecem como "Conectado"
3. Teste envio de mensagem pelo painel

---

## 🛠️ COMANDOS DE MANUTENÇÃO

### Reiniciar Serviços

```bash
# Reiniciar todos os serviços
pm2 restart all

# Reiniciar canal específico
pm2 restart whatsapp-3000
pm2 restart whatsapp-3001
```

### Verificar Logs

```bash
# Logs gerais
pm2 logs --lines 50

# Logs específicos
pm2 logs whatsapp-3000 --lines 20
pm2 logs whatsapp-3001 --lines 20

# Monitorar em tempo real
pm2 monit
```

### Reconfigurar Webhooks

```bash
# Configurar webhook para canal 3000
curl -X POST http://212.85.11.238:3000/webhook/config \
  -H "Content-Type: application/json" \
  -d '{"url":"https://app.pixel12digital.com.br/painel/receber_mensagem_ana_local.php"}'

# Configurar webhook para canal 3001
curl -X POST http://212.85.11.238:3001/webhook/config \
  -H "Content-Type: application/json" \
  -d '{"url":"https://app.pixel12digital.com.br/painel/receber_mensagem_ana_local.php"}'
```

---

## 🔧 SOLUÇÃO DE PROBLEMAS

### Problema: VPS não responde

```bash
# Verificar se a VPS está online
ping 212.85.11.238

# Verificar se as portas estão abertas
telnet 212.85.11.238 3000
telnet 212.85.11.238 3001
```

### Problema: Serviços não iniciam

```bash
# Conectar na VPS
ssh root@212.85.11.238

# Verificar se Node.js está instalado
node --version

# Verificar se PM2 está instalado
pm2 --version

# Reinstalar PM2 se necessário
npm install -g pm2
```

### Problema: Webhooks não funcionam

```bash
# Verificar se o endpoint está acessível
curl -I https://app.pixel12digital.com.br/painel/receber_mensagem_ana_local.php

# Testar webhook manualmente
curl -X POST https://app.pixel12digital.com.br/painel/receber_mensagem_ana_local.php \
  -H "Content-Type: application/json" \
  -d '{"test":"webhook"}'
```

### Problema: Canais não aparecem no painel

1. Verifique se o banco de dados está conectado
2. Execute o script de atualização de canais:
   ```bash
   php restaurar_vps_completo.php
   ```
3. Verifique a tabela `canais_comunicacao` no banco

---

## 📊 MONITORAMENTO CONTÍNUO

### Scripts de Monitoramento

```bash
# Verificar status a cada 5 minutos
*/5 * * * * curl -s http://212.85.11.238:3000/status > /dev/null || echo "Canal 3000 offline"

# Verificar logs de erro
pm2 logs whatsapp-3000 --err --lines 10
pm2 logs whatsapp-3001 --err --lines 10
```

### Alertas Recomendados

1. **Canal offline**: Se algum canal não responder por mais de 5 minutos
2. **Erro de webhook**: Se webhook retornar erro 4xx ou 5xx
3. **Alto uso de memória**: Se PM2 usar mais de 80% de memória
4. **Logs de erro**: Se houver muitos erros nos logs

---

## ✅ CHECKLIST DE RESTAURAÇÃO

- [ ] Commit restaurado localmente
- [ ] Script SSH executado na VPS
- [ ] Script PHP executado localmente
- [ ] VPS respondendo nas portas 3000 e 3001
- [ ] Webhooks configurados corretamente
- [ ] Canais aparecem como "Conectado" no painel
- [ ] Teste de envio funcionando
- [ ] Logs sem erros críticos
- [ ] PM2 configurado para auto-restart

---

## 📞 SUPORTE

Se encontrar problemas durante a restauração:

1. **Verifique os logs**: `pm2 logs --lines 50`
2. **Teste conectividade**: `curl http://212.85.11.238:3000/status`
3. **Reinicie serviços**: `pm2 restart all`
4. **Execute novamente**: `php restaurar_vps_completo.php`

**Comandos de emergência:**
```bash
# Parar tudo e recomeçar
pm2 stop all && pm2 delete all
pm2 start ecosystem.config.js

# Verificar recursos do servidor
top
free -h
df -h
```

---

## 🎯 CONCLUSÃO

Após seguir este guia, a VPS estará completamente restaurada com:

- ✅ 2 canais WhatsApp funcionando (3000 e 3001)
- ✅ Webhooks configurados para produção
- ✅ PM2 gerenciando os serviços
- ✅ Auto-restart em caso de falha
- ✅ Logs organizados e monitorados
- ✅ Painel local sincronizado

**Status esperado:**
- VPS: Online e responsiva
- Canais: Conectados e funcionando
- Webhooks: Configurados e testados
- Sistema: Pronto para uso em produção 