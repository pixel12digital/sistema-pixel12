# 🧪 GUIA DE EXECUÇÃO DE TESTES - SISTEMA WHATSAPP MULTI-CANAL

## 📋 Visão Geral

Este guia explica como executar testes completos do sistema WhatsApp multi-canal no servidor VPS, validando todos os componentes conforme o plano de testes especificado.

## 🚀 Pré-requisitos

### 1. Acesso SSH ao VPS
```bash
ssh root@212.85.11.238
```

### 2. Navegar para o Diretório da API
```bash
cd /var/whatsapp-api
```

### 3. Verificar se os Scripts Estão Presentes
```bash
ls -la *.sh
```

Deve mostrar:
- `teste_completo_sistema_whatsapp.sh`
- `teste_envio_recebimento_mensagens.sh`

## 📊 ETAPA 1: TESTE COMPLETO DO SISTEMA

### Executar Teste Completo
```bash
chmod +x teste_completo_sistema_whatsapp.sh
./teste_completo_sistema_whatsapp.sh
```

### O que este teste verifica:
- ✅ Status dos processos PM2 (whatsapp-3000 e whatsapp-3001)
- ✅ Logs de debug e sessionName
- ✅ Endpoints QR Code (interno e externo)
- ✅ Conectividade de portas (3000 e 3001)
- ✅ Endpoints de status das sessões
- ✅ Estrutura de diretórios (sessions, logs)
- ✅ Configuração de webhook
- ✅ Conectividade com o painel

### Resultado Esperado:
```
📈 RESULTADO FINAL: X/Y testes passaram

✅ CRITÉRIOS DE SUCESSO:
✔️ Processo whatsapp-3000 (default) está ONLINE
✔️ Processo whatsapp-3001 (comercial) está ONLINE
✔️ QR Code default interno funciona
✔️ QR Code comercial interno funciona
✔️ QR Code default externo funciona
✔️ QR Code comercial externo funciona
✔️ Porta 3000 está aberta
✔️ Porta 3001 está aberta
✔️ Sessão default está CONECTADA
✔️ Sessão comercial está CONECTADA
```

## 📱 ETAPA 2: TESTE DE ENVIO E RECEBIMENTO

### Executar Teste de Mensagens
```bash
chmod +x teste_envio_recebimento_mensagens.sh
./teste_envio_recebimento_mensagens.sh
```

### O que este teste verifica:
- ✅ Status de conexão das sessões
- ✅ Envio de mensagens via API
- ✅ Logs de mensagens recebidas
- ✅ Funcionamento do webhook
- ✅ Conectividade com painel e chat
- ✅ Logs de debug com sessionName

### Durante o teste:
1. **Digite um número de telefone** para teste (ex: 5511999999999)
2. **Aguarde o processamento** das mensagens
3. **Verifique os logs** de debug

## 🔍 ETAPA 3: VERIFICAÇÃO MANUAL NO PAINEL

### 1. Acessar Painel de Comunicação
```
http://212.85.11.238:8080/painel/comunicacao.php
```

### 2. Verificar Status dos Canais
- **Verde** = Conectado (ready=true)
- **Amarelo** = Pendente (ready=false)

### 3. Conectar Canais Pendentes
Se algum canal estiver amarelo:
1. Clique em **"Conectar"**
2. Escaneie o **QR Code** no WhatsApp
3. Aguarde o status mudar para verde

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
- Do seu celular, envie uma mensagem para o número do canal
- Exemplo: `"Teste de recebimento - $(date)"`

### 2. Verificar Logs do PM2
```bash
# Para canal default
pm2 logs whatsapp-3000 --lines 20 | grep "Mensagem recebida"

# Para canal comercial
pm2 logs whatsapp-3001 --lines 20 | grep "Mensagem recebida"
```

### 3. Verificar Webhook PHP
- Os logs PHP devem mostrar o webhook chegando
- Campo `session` deve ser: `'default'` ou `'comercial'`

### 4. Verificar Chat Central
- A conversa deve aparecer automaticamente na lista
- A mensagem deve estar visível no histórico

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

## ✅ CRITÉRIOS DE SUCESSO FINAL

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

## 📞 SUPORTE

Se algum teste falhar:
1. Execute novamente o script completo
2. Verifique os logs de erro
3. Consulte a seção de troubleshooting
4. Entre em contato com a equipe de desenvolvimento

---

**🎯 OBJETIVO:** Garantir que o sistema WhatsApp multi-canal esteja 100% operacional com envio e recebimento funcionando em ambos os canais. 