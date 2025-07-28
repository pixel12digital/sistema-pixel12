# 📋 Regras Éticas de Monitoramento - Pixel12 Digital

## 🎯 **OBJETIVO**
Garantir que as mensagens de cobrança sejam enviadas respeitando horários comerciais e evitando feriados/finais de semana, proporcionando uma experiência ética e profissional.

## ⏰ **HORÁRIOS COMERCIAIS**

### ✅ **Horários Permitidos:**
- **Segunda a Sexta:** 08:00h às 18:00h
- **Sábados e Domingos:** ❌ **NÃO PERMITIDO**
- **Feriados:** ❌ **NÃO PERMITIDO**

### 🚫 **Horários Bloqueados:**
- Antes das 08:00h
- Após às 18:00h
- Finais de semana (Sábado e Domingo)
- Feriados nacionais

## 📅 **FERIADOS RECONHECIDOS**

### 🗓️ **Feriados Fixos:**
- **01/01** - Ano Novo
- **21/04** - Tiradentes
- **01/05** - Dia do Trabalho
- **07/09** - Independência do Brasil
- **12/10** - Nossa Senhora Aparecida
- **02/11** - Finados
- **15/11** - Proclamação da República
- **25/12** - Natal

### 🗓️ **Feriados Móveis:**
- **Carnaval** (47 dias antes da Páscoa)
- **Páscoa** (calculada automaticamente)
- **Corpus Christi** (60 dias após a Páscoa)

## 🔄 **LÓGICA DE AGENDAMENTO**

### 📊 **Como Funciona:**

1. **Sistema propõe horário** (ex: +1 day 10:00:00)
2. **Verifica se é final de semana** → Se sim, move para segunda-feira 10h
3. **Verifica se é feriado** → Se sim, move para próximo dia útil 10h
4. **Verifica horário comercial** → Se fora do horário, ajusta para 10h do próximo dia útil

### ⏱️ **Exemplos Práticos:**

```
CENÁRIO 1: Fatura vencida recente
Proposto: Sábado 10h
Resultado: Segunda-feira 10h

CENÁRIO 2: Fatura vencida média  
Proposto: Domingo 14h
Resultado: Segunda-feira 10h

CENÁRIO 3: Fatura vencida antiga
Proposto: Natal 16h
Resultado: Próximo dia útil 10h

CENÁRIO 4: Horário inadequado
Proposto: Terça-feira 22h
Resultado: Quarta-feira 10h
```

## 🛡️ **PROTEÇÕES IMPLEMENTADAS**

### ✅ **No Agendamento Automático:**
- Função `calcularHorarioAdequado()` ajusta automaticamente
- Respeita feriados e finais de semana
- Move para próximo dia útil quando necessário

### ✅ **No Monitoramento Manual:**
- Função `ehHorarioAdequadoParaEnvio()` verifica antes de enviar
- Se horário inadequado, agenda para horário comercial
- Evita envios imediatos em horários inadequados

### ✅ **Logs Detalhados:**
- Registra quando horário é ajustado
- Documenta motivos dos ajustes
- Facilita auditoria e controle

## 📊 **BENEFÍCIOS**

### 🎯 **Para o Cliente:**
- Recebe mensagens em horários apropriados
- Não é incomodado em finais de semana
- Experiência mais profissional e respeitosa

### 🎯 **Para a Empresa:**
- Imagem mais profissional
- Maior taxa de resposta
- Conformidade com boas práticas
- Redução de reclamações

### 🎯 **Para o Sistema:**
- Menos mensagens ignoradas
- Melhor eficácia nas cobranças
- Controle total sobre horários
- Logs para auditoria

## 🔧 **IMPLEMENTAÇÃO TÉCNICA**

### 📁 **Arquivos Modificados:**
- `painel/api/salvar_monitoramento_cliente.php`
- `painel/api/executar_monitoramento.php`

### 🆕 **Funções Adicionadas:**
- `calcularHorarioAdequado()`
- `ehHorarioAdequadoParaEnvio()`
- `ehFeriado()`
- `calcularPascoa()`

### 📝 **Logs Criados:**
- `../logs/agendamento_mensagens.log`
- `../logs/monitoramento_manual.log`

## 🎉 **RESULTADO FINAL**

**O sistema agora garante que todas as mensagens de cobrança sejam enviadas respeitando horários comerciais, evitando feriados e finais de semana, proporcionando uma experiência ética e profissional para todos os clientes!** ✨

---

*Implementado em: 28/07/2025*  
*Versão: 1.0*  
*Responsável: Sistema de Monitoramento Pixel12 Digital* 