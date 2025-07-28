# 📅 CRITÉRIO DE AGENDAMENTO - 04/08/2025 às 16h

## 🎯 **EXPLICAÇÃO DO CRITÉRIO:**

O agendamento para **04/08/2025 às 16h** foi baseado na **idade da fatura vencida** do cliente Eduardo.

## 📊 **ESTRATÉGIA DE AGENDAMENTO IMPLEMENTADA:**

### **3 Categorias de Faturas Vencidas:**

1. **🟢 Faturas Recentes (≤ 7 dias vencidas)**
   - **Agendamento:** +1 dia às 10h
   - **Prioridade:** Alta
   - **Justificativa:** Faturas recém-vencidas precisam de atenção imediata

2. **🟡 Faturas Médias (8-30 dias vencidas)**
   - **Agendamento:** +3 dias às 14h
   - **Prioridade:** Normal
   - **Justificativa:** Faturas com vencimento intermediário

3. **🔴 Faturas Antigas (> 30 dias vencidas)**
   - **Agendamento:** +7 dias às 16h
   - **Prioridade:** Baixa
   - **Justificativa:** Faturas muito antigas, abordagem mais suave

## 🔍 **CASO DO CLIENTE EDUARDO:**

### **Dados da Fatura:**
- **Cliente:** Eduardo da Silva Brito (ID: 274)
- **Valor:** R$ 347,00
- **Dias vencida:** 356 dias (mais de 1 ano!)
- **Categoria:** 🔴 **Fatura Antiga** (> 30 dias)

### **Cálculo do Agendamento:**
- **Data atual:** 28/07/2025
- **Categoria aplicada:** Faturas Antigas
- **Fórmula:** `+7 dias às 16h`
- **Resultado:** 04/08/2025 às 16:00:00

## 📋 **CÓDIGO IMPLEMENTADO:**

```php
// 3. Faturas vencidas antigas (mais de 30 dias) - enviar em 7 dias
if (!empty($faturas_vencidas_antigas)) {
    $mensagem = montarMensagemCobrancaVencida($faturas_vencidas_antigas, $faturas[0]);
    $horario_envio = date('Y-m-d H:i:s', strtotime('+7 days 16:00:00')); // Em 7 dias às 16h
    
    if (agendarMensagem($cliente_id, $mensagem, $horario_envio, 'baixa', $mysqli)) {
        $mensagens_agendadas++;
    }
}
```

## 🎯 **JUSTIFICATIVA DA ESTRATÉGIA:**

### **Por que 7 dias para faturas antigas?**
1. **📈 Abordagem Gradual:** Não sobrecarregar o cliente com cobranças agressivas
2. **⏰ Horário Estratégico:** 16h é um horário comercial adequado
3. **🎯 Prioridade Baixa:** Faturas antigas têm menor urgência
4. **📊 Análise Comportamental:** Cliente com fatura de 356 dias pode precisar de abordagem mais cuidadosa

### **Por que 16h?**
- **Horário comercial** (não muito cedo, não muito tarde)
- **Maior probabilidade** de o cliente estar disponível
- **Evita horários** de almoço ou rush

## 📊 **RESUMO:**

**Cliente Eduardo → Fatura de 356 dias → Categoria Antiga → +7 dias às 16h → 04/08/2025 16:00**

Esta estratégia garante uma abordagem **gradual e respeitosa** para clientes com faturas muito antigas, evitando cobranças agressivas que podem prejudicar o relacionamento. 