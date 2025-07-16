# 🤖 Simulação Humana no WhatsApp Bot

## O que é?

A simulação humana torna o comportamento do robô mais natural, simulando como uma pessoa real usaria o WhatsApp Web.

## Como Funciona?

### **1. Pausas Aleatórias**
- **Pausa inicial**: 1-12 segundos (como se estivesse pensando)
- **Pausa entre caracteres**: 60-220ms (como digitação natural)
- **Pausa entre frases**: 300-1200ms (pontos, vírgulas, etc.)
- **Pausa final**: 1.5-4 segundos (como revisando antes de enviar)
- **Pausa pós-envio**: 1.2-3 segundos (como humano faria)

### **2. Velocidade de Digitação**
- **60-220ms por caractere** (velocidade humana real)
- **Tempo variável** baseado no tamanho da mensagem

### **3. Comportamento Aleatório**
- **Pausas diferentes** a cada envio
- **Tempos variáveis** para parecer mais natural
- **Correções ocasionais** (2% de chance por caractere)

### **4. Sistema de Fila**
- **Processamento sequencial**: uma mensagem por vez
- **Pausa entre mensagens**: 3-8 segundos
- **Evita spam**: comportamento mais natural
- **Logs detalhados**: acompanhe o progresso

## Endpoints de Controle

### **Verificar Status da Simulação**
```bash
GET http://localhost:3000/simulation
```

**Resposta:**
```json
{
  "success": true,
  "humanSimulation": true
}
```

### **Ativar/Desativar Simulação**
```bash
POST http://localhost:3000/simulation
Content-Type: application/json

{
  "enabled": true  // ou false
}
```

### **Verificar Status da Fila**
```bash
GET http://localhost:3000/queue
```

**Resposta:**
```json
{
  "success": true,
  "queueLength": 3,
  "isProcessing": true,
  "status": "processando"
}
```

### **Limpar Fila (Emergência)**
```bash
POST http://localhost:3000/queue/clear
```

**Resposta:**
```json
{
  "success": true,
  "message": "Fila limpa. 5 mensagens removidas.",
  "queueLength": 0
}
```

## Vantagens

### **✅ Reduz Detecção de Bot**
- Comportamento mais natural
- Menos chance de bloqueio
- Pausas como humano real
- Processamento sequencial

### **✅ Melhora Confiabilidade**
- Mensagens mais confiáveis
- Menos verificações do WhatsApp
- Entrega mais rápida
- Sistema de fila evita sobrecarga

### **✅ Controle Total**
- Pode ativar/desativar quando quiser
- Configuração em tempo real
- Logs detalhados
- Monitoramento da fila

## Exemplo de Uso

### **Enviar Mensagem (com fila automática):**
```bash
curl -X POST http://localhost:3000/send \
  -H "Content-Type: application/json" \
  -d '{
    "to": "5511999999999",
    "message": "Olá! Esta é uma mensagem de teste."
  }'
```

**Resposta:**
```json
{
  "success": true,
  "messageId": "true_5511999999999@c.us_ABC123",
  "status": "enviado",
  "queuePosition": 1
}
```

## Logs de Simulação

Quando ativada, você verá logs como:
```
📋 Mensagem adicionada à fila. Total: 3
📋 Processando fila: 3 mensagens pendentes
📤 Processando mensagem 1 da fila
🤖 Iniciando simulação humana aprimorada...
⏳ Pausa inicial simulada
📤 Enviando mensagem...
✅ Mensagem enviada com simulação humana aprimorada
✅ Mensagem processada com sucesso: true_5511999999999@c.us_ABC123
⏳ Aguardando 4500ms antes da próxima mensagem...
```

## Configuração Padrão

- **Simulação ativada por padrão**: `true`
- **Sistema de fila ativo**: sempre
- **Pausa entre mensagens**: 3-8 segundos
- **Pode ser alterada** via endpoint `/simulation`
- **Persiste** até reiniciar o robô

## Recomendações

### **Para Uso Diário:**
- ✅ **Manter ativada** para maior confiabilidade
- ✅ **Usar sistema de fila** para envios em massa
- ✅ **Monitorar logs** para verificar funcionamento
- ✅ **Verificar status da fila** via `/queue`

### **Para Testes:**
- ⚠️ **Desativar** para testes rápidos
- ⚠️ **Ativar** para produção
- ⚠️ **Limpar fila** se necessário via `/queue/clear`

### **Para Envio em Massa:**
- ✅ **Manter ativada** para evitar bloqueios
- ✅ **Sistema de fila** processa automaticamente
- ✅ **Pausas automáticas** entre mensagens
- ✅ **Usar horários** de menor movimento

## Comportamento da Fila

### **Como Funciona:**
1. **Mensagem recebida** → adicionada à fila
2. **Processamento sequencial** → uma por vez
3. **Pausa automática** → 3-8 segundos entre mensagens
4. **Logs detalhados** → acompanhe o progresso

### **Vantagens:**
- **Evita spam** → comportamento natural
- **Reduz bloqueios** → menos detecção de bot
- **Processamento confiável** → uma mensagem por vez
- **Controle total** → pode limpar fila se necessário 