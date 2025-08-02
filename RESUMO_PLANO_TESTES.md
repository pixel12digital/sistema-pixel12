# 📋 RESUMO COMPLETO - PLANO DE TESTES WHATSAPP MULTI-CANAL

## 🎯 OBJETIVO
Validar que o sistema WhatsApp multi-canal está 100% operacional com envio e recebimento funcionando em ambos os canais (default:3000 e comercial:3001).

## 🚀 EXECUÇÃO DOS TESTES

### ⚠️ IMPORTANTE: Ambiente Correto
**TODOS os comandos devem ser executados no VPS via SSH, NÃO no Windows local:**

```bash
ssh root@212.85.11.238
cd /var/whatsapp-api
```

## 📊 ETAPA 1: TESTE COMPLETO DO SISTEMA

### Executar Script Principal
```bash
chmod +x teste_completo_sistema_whatsapp.sh
./teste_completo_sistema_whatsapp.sh
```

### ✅ Critérios de Sucesso:
- [ ] Processo whatsapp-3000 (default) está ONLINE
- [ ] Processo whatsapp-3001 (comercial) está ONLINE
- [ ] QR Code default interno funciona
- [ ] QR Code comercial interno funciona
- [ ] QR Code default externo funciona
- [ ] QR Code comercial externo funciona
- [ ] Porta 3000 está aberta
- [ ] Porta 3001 está aberta
- [ ] Sessão default está CONECTADA
- [ ] Sessão comercial está CONECTADA

## 📱 ETAPA 2: TESTE DE ENVIO E RECEBIMENTO

### Executar Script de Mensagens
```bash
chmod +x teste_envio_recebimento_mensagens.sh
./teste_envio_recebimento_mensagens.sh
```

### Durante o teste:
1. **Digite número de telefone** para teste (ex: 5511999999999)
2. **Aguarde processamento** das mensagens
3. **Verifique logs** de debug

## 🔍 ETAPA 3: VERIFICAÇÃO MANUAL NO PAINEL

### 1. Acessar Painel de Comunicação
```
http://212.85.11.238:8080/painel/comunicacao.php
```

### 2. Verificar Status dos Canais
- **🟢 Verde** = Conectado (ready=true)
- **🟡 Amarelo** = Pendente (ready=false)

### 3. Conectar Canais Pendentes
Se algum canal estiver amarelo:
1. Clique em **"Conectar"**
2. Escaneie o **QR Code** no WhatsApp
3. Aguarde status mudar para verde

### 4. Acessar Chat Central
```
http://212.85.11.238:8080/painel/chat.php
```

## 📤 ETAPA 4: TESTE DE ENVIO PELO PAINEL

### 1. Selecionar Cliente
- No chat central, escolha um cliente da lista

### 2. Escolher Canal de Envio
- No dropdown, selecione:
  - **"Default - Pixel"** (porta 3000)
  - **"Comercial - Pixel"** (porta 3001)

### 3. Enviar Mensagem Teste
- Digite: `"Hello from canal financeiro!"`
- Clique em **Enviar**

### 4. Verificar Console
- Abra **F12** no navegador
- Vá para aba **Console**
- Procure por: `CORS-FREE (debug)`
- Deve mostrar: `success: true`

## 📥 ETAPA 5: TESTE DE RECEBIMENTO

### 1. Enviar Mensagem do WhatsApp
- Do seu celular, envie mensagem para o número do canal
- Exemplo: `"Teste de recebimento - $(date)"`

### 2. Verificar Logs do PM2
```bash
# Para canal default
pm2 logs whatsapp-3000 --lines 20 | grep "Mensagem recebida"

# Para canal comercial
pm2 logs whatsapp-3001 --lines 20 | grep "Mensagem recebida"
```

### 3. Verificar Webhook PHP
- Logs PHP devem mostrar webhook chegando
- Campo `session` deve ser: `'default'` ou `'comercial'`

### 4. Verificar Chat Central
- Conversa deve aparecer automaticamente na lista
- Mensagem deve estar visível no histórico

## 🔄 ETAPA 6: REPETIR PARA AMBOS OS CANAIS

### Canal Default (3000)
1. Mude dropdown para **"Default - Pixel"**
2. Envie mensagem teste
3. Verifique recebimento
4. Confirme logs: `sessionName value: default`

### Canal Comercial (3001)
1. Mude dropdown para **"Comercial - Pixel"**
2. Envie mensagem teste
3. Verifique recebimento
4. Confirme logs: `sessionName value: comercial`

## 📊 ETAPA 7: VERIFICAÇÃO FINAL DE LOGS

### Verificar Logs de Debug
```bash
# Canal default
pm2 logs whatsapp-3000 --lines 10 | grep DEBUG

# Canal comercial
pm2 logs whatsapp-3001 --lines 10 | grep DEBUG
```

### Logs Esperados:
```
[DEBUG][default:3000] sessionName value: default
[DEBUG][comercial:3001] sessionName value: comercial
```

## 🔍 MONITORAMENTO CONTÍNUO (OPCIONAL)

### Executar Monitoramento
```bash
chmod +x monitoramento_continuo.sh
./monitoramento_continuo.sh
```

### O que monitora:
- Status dos processos PM2
- Conectividade das portas
- Status das sessões
- Conectividade com painel
- Uso de memória
- Logs de erro
- Mensagens recentes

## 🚨 TROUBLESHOOTING

### Se os Processos Não Iniciarem:
```bash
pm2 status
pm2 logs --err
pm2 restart all
```

### Se o QR Code Não Aparecer:
```bash
netstat -tlnp | grep :3000
netstat -tlnp | grep :3001
ufw status
```

### Se as Mensagens Não Enviarem:
```bash
pm2 logs whatsapp-3000 --follow
pm2 logs whatsapp-3001 --follow
```

### Se o Webhook Não Funcionar:
```bash
tail -f /var/log/apache2/error.log
curl -X POST http://212.85.11.238:8080/api/webhook.php
```

## 📋 CHECKLIST FINAL

### ✅ Infraestrutura
- [ ] Cada canal responde a `GET /qr?session=...` com QR limpo
- [ ] Painel consegue conectar via QR
- [ ] Status muda para "Conectado" (verde)

### ✅ Funcionalidade
- [ ] Envio de texto funciona em ambos os canais
- [ ] Webhooks chegam corretamente ao PHP
- [ ] Chat central atualiza em tempo real

### ✅ Logs e Debug
- [ ] Logs confirmam sessionName correto
- [ ] Logs mostram porta e payload do QR
- [ ] Debug funciona em ambos os canais

## 📞 COMANDOS ÚTEIS PARA REFERÊNCIA

### Verificar Status
```bash
pm2 status
pm2 logs whatsapp-3000 --lines 20
pm2 logs whatsapp-3001 --lines 20
```

### Obter QR Code
```bash
curl -s http://127.0.0.1:3000/qr?session=default | jq .
curl -s http://127.0.0.1:3001/qr?session=comercial | jq .
```

### Verificar Status das Sessões
```bash
curl -s http://127.0.0.1:3000/status | jq .
curl -s http://127.0.0.1:3001/status | jq .
```

### Reiniciar Processos
```bash
pm2 restart whatsapp-3000
pm2 restart whatsapp-3001
pm2 restart all
```

## 🎯 RESULTADO ESPERADO

Após executar todos os testes com sucesso:

1. **Ambos os canais conectados** (status verde no painel)
2. **Envio funcionando** em ambos os canais
3. **Recebimento funcionando** com webhook
4. **Chat central atualizando** em tempo real
5. **Logs de debug** confirmando sessionName correto

---

**🎉 SUCESSO:** Sistema WhatsApp multi-canal 100% operacional! 