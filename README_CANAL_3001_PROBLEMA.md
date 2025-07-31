# 🔍 Problema: Mensagens do Canal 3001 Comercial Não Estão Sendo Salvas

## 📋 Resumo do Problema

O canal comercial na porta 3001 não está salvando mensagens recebidas no banco de dados. O diagnóstico identificou que o servidor na porta 3001 está funcionando, mas há problemas de configuração que impedem o correto processamento das mensagens.

## 🔍 Diagnóstico Realizado

### Status Atual dos Canais:
- **Canal Financeiro (ID 36)**: Porta 3000 - Status: pendente - 328 mensagens
- **Canal Comercial (ID 37)**: Porta 3001 - Status: pendente - 4 mensagens

### Problemas Identificados:

1. **❌ Servidor 3001 não tem endpoint `/send`**
   - HTTP Code: 404 ao tentar enviar mensagem
   - O servidor não tem o endpoint implementado

2. **❌ Canal 3001 sem identificador configurado**
   - Campo `identificador` está vazio no banco
   - Isso impede que o webhook identifique qual canal usar

3. **❌ Webhook específico não existe**
   - Arquivo `api/webhook_canal_37.php` não encontrado
   - Sistema usa webhook principal para todos os canais

## 🔧 Soluções Implementadas

### 1. Script de Diagnóstico Completo
```bash
php diagnosticar_canal_3001.php
```
- Verifica status do servidor 3001
- Identifica problemas de configuração
- Fornece recomendações específicas

### 2. Script de Correção Automática
```bash
php corrigir_canal_3001_completo.php
```
- Configura identificador do canal automaticamente
- Testa recebimento de mensagens
- Verifica se mensagens são salvas corretamente

## 🚀 Passos para Correção

### Passo 1: Configurar Servidor na VPS
```bash
# Conectar na VPS
ssh root@212.85.11.238

# Criar diretório para porta 3001
cd /var
mkdir -p whatsapp-api-3001

# Copiar arquivos do servidor existente
cp -r whatsapp-api/* whatsapp-api-3001/

# Alterar porta no arquivo de configuração
cd whatsapp-api-3001
sed -i 's/const PORT = 3000/const PORT = 3001/' whatsapp-api-server.js

# Iniciar servidor com PM2
pm2 start whatsapp-api-server.js --name whatsapp-3001
pm2 save
```

### Passo 2: Conectar WhatsApp
1. Acessar `http://212.85.11.238:3001` no navegador
2. Escanear QR Code com WhatsApp
3. Aguardar conexão ser estabelecida

### Passo 3: Configurar Identificador
```bash
# Executar script de correção
php corrigir_canal_3001_completo.php
```

### Passo 4: Testar Recebimento
1. Enviar mensagem para o número do canal 3001
2. Verificar se aparece no chat do sistema
3. Confirmar que está associado ao canal correto

## 📊 Arquitetura do Sistema

### Estrutura de Canais:
```
Canal Financeiro (ID 36)
├── Porta: 3000
├── Banco: pixel12digital (principal)
├── Identificador: 554797146908@c.us
└── Status: pendente

Canal Comercial (ID 37)
├── Porta: 3001
├── Banco: pixel12digital_comercial
├── Identificador: [NÃO CONFIGURADO]
└── Status: pendente
```

### Fluxo de Mensagens:
```
Mensagem WhatsApp → Servidor 3001 → Webhook → Banco de Dados
```

## 🔍 Verificações Importantes

### 1. Status do Servidor
```bash
curl http://212.85.11.238:3001/status
```
**Resposta esperada:**
```json
{
  "success": true,
  "ready": true,
  "clients_status": {
    "default": {
      "status": "connected",
      "number": "554796164699"
    }
  }
}
```

### 2. Endpoint /send
```bash
curl -X POST http://212.85.11.238:3001/send \
  -H 'Content-Type: application/json' \
  -d '{"to":"4796164699@c.us","message":"teste"}'
```

### 3. Configuração do Canal no Banco
```sql
SELECT id, nome_exibicao, porta, status, identificador 
FROM canais_comunicacao 
WHERE porta = 3001;
```

## 🛠️ Troubleshooting

### Problema: Servidor 3001 não responde
**Solução:**
1. Verificar se o processo está rodando: `pm2 list`
2. Reiniciar servidor: `pm2 restart whatsapp-3001`
3. Verificar logs: `pm2 logs whatsapp-3001`

### Problema: Endpoint /send não existe
**Solução:**
1. Verificar se o arquivo `whatsapp-api-server.js` tem o endpoint implementado
2. Comparar com o servidor da porta 3000
3. Copiar implementação se necessário

### Problema: Mensagens não são salvas
**Solução:**
1. Verificar se o webhook está configurado corretamente
2. Verificar logs do webhook: `tail -f logs/webhook_whatsapp_*.log`
3. Testar webhook manualmente

### Problema: Canal não é identificado
**Solução:**
1. Verificar se o identificador está configurado no banco
2. Verificar se o webhook principal tem lógica para identificar canais por porta
3. Implementar lógica de identificação se necessário

## 📝 Logs Importantes

### Logs do Servidor 3001
```bash
pm2 logs whatsapp-3001 --lines 20
```

### Logs do Webhook
```bash
tail -f logs/webhook_whatsapp_*.log
```

### Logs do Sistema
```bash
tail -f painel/debug_*.log
```

## 🎯 Status Atual

- ✅ **Servidor 3001**: Funcionando
- ❌ **Endpoint /send**: Não implementado
- ❌ **Identificador**: Não configurado
- ❌ **Webhook específico**: Não existe
- ⚠️ **Mensagens salvas**: 4 (poucas)

## 🔄 Próximos Passos

1. **Implementar endpoint /send** no servidor 3001
2. **Configurar identificador** do canal automaticamente
3. **Testar recebimento** de mensagens
4. **Verificar salvamento** no banco correto
5. **Monitorar funcionamento** por 24h

## 📞 Suporte

Para problemas específicos:
1. Execute o script de diagnóstico: `php diagnosticar_canal_3001.php`
2. Verifique os logs do sistema
3. Consulte este README para soluções
4. Entre em contato com a equipe de desenvolvimento

---

**Última atualização**: 31/07/2025  
**Status**: Em correção  
**Prioridade**: Alta 