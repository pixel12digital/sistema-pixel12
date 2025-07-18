# Sistema de Monitoramento Automático - Versão 2.0

## 🚀 **Novidades da Versão 2.0**

### ✅ **Verificação de Status Real no Asaas**
- **Antes de enviar mensagens**, o sistema verifica o status real das cobranças no Asaas
- **Atualiza automaticamente** o banco de dados se houver divergências
- **Evita mensagens desnecessárias** para cobranças já pagas

### ⏰ **Sistema de Agendamento Inteligente**
- **Distribui mensagens** ao longo do dia (9h às 18h)
- **Respeita limites** do WhatsApp (máx. 50 mensagens/dia)
- **Intervalo mínimo** de 3 minutos entre mensagens
- **Prioridades inteligentes** baseadas em valor e tempo de vencimento

### 🎯 **Consolidação de Faturas**
- **Múltiplas faturas vencidas** são enviadas em uma única mensagem
- **Valor total consolidado** para melhor visualização
- **Links de pagamento** organizados

### 🔒 **Ativação Manual Obrigatória**
- **Só funciona após** primeira mensagem manual de validação
- **Checkbox "Monitorar"** deve ser marcado manualmente
- **Controle total** sobre quais clientes são monitorados

---

## 📋 **Funcionalidades**

### 1. **Validação Manual**
- Clique em **"Validar"** na página de Faturas
- Envia mensagem de apresentação: *"Olá! Este é nosso contato financeiro da Pixel12 Digital..."*
- **Obrigatório** antes de ativar monitoramento

### 2. **Ativação de Monitoramento**
- Marque o checkbox **"Monitorar"** após validação
- Cliente é adicionado ao sistema de monitoramento automático
- **Pode ser desativado** a qualquer momento

### 3. **Verificação de Status Asaas**
- **Antes de cada envio**, verifica status real no Asaas
- **Atualiza banco** se houver divergências
- **Cancela mensagem** se cobrança já foi paga

### 4. **Agendamento Inteligente**
- **Horário comercial**: 9h às 18h
- **Distribuição automática** ao longo do dia
- **Prioridades**:
  - **Alta**: >30 dias vencido ou valor >R$ 1.000
  - **Normal**: 7-30 dias vencido
  - **Baixa**: <7 dias vencido e valor <R$ 100

### 5. **Consolidação de Mensagens**
- **Múltiplas faturas** em uma única mensagem
- **Valor total** consolidado
- **Links organizados** para pagamento

### 6. **Respostas Automáticas**
- **"faturas" ou "consulta"** → Lista todas as faturas
- **"pagar" ou "pagamento"** → Links de pagamento
- **"atendente"** → Oferece transferência para humano
- **Outras mensagens** → Resposta padrão com opções

---

## 🗄️ **Estrutura do Banco**

### Tabela `clientes_monitoramento`
```sql
CREATE TABLE `clientes_monitoramento` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cliente_id` int(11) NOT NULL,
  `monitorado` tinyint(1) NOT NULL DEFAULT 0,
  `data_criacao` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `data_atualizacao` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `cliente_id` (`cliente_id`)
);
```

### Tabela `mensagens_agendadas`
```sql
CREATE TABLE `mensagens_agendadas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cliente_id` int(11) NOT NULL,
  `mensagem` text NOT NULL,
  `tipo` varchar(50) NOT NULL DEFAULT 'cobranca_vencida',
  `prioridade` enum('alta','normal','baixa') NOT NULL DEFAULT 'normal',
  `data_agendada` datetime NOT NULL,
  `status` enum('agendada','enviada','cancelada','erro') NOT NULL DEFAULT 'agendada',
  `observacao` text,
  `data_criacao` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `data_atualizacao` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
);
```

---

## 📁 **Arquivos do Sistema**

### APIs Principais
- `api/verificar_status_asaas.php` - Verifica status real no Asaas
- `api/agendar_envio_mensagens.php` - Agenda mensagens com distribuição inteligente
- `api/enviar_mensagem_validacao.php` - Envia mensagem de validação manual
- `api/salvar_monitoramento_cliente.php` - Salva status de monitoramento
- `api/listar_clientes_monitorados.php` - Lista clientes monitorados
- `api/verificar_cobrancas_vencidas.php` - Busca cobranças vencidas
- `api/enviar_mensagem_automatica.php` - Envia mensagens automáticas

### Scripts de Cron
- `cron/processar_mensagens_agendadas.php` - Processa mensagens agendadas (a cada 5 min)
- `cron/monitoramento_automatico.php` - Verifica cobranças vencidas (a cada 30 min)

### Frontend
- `assets/faturas_monitoramento.js` - Sistema principal de monitoramento
- `assets/cobrancas.js` - Tabela de faturas com coluna de monitoramento

### SQL
- `sql/criar_tabela_monitoramento_simples.sql` - Cria tabela de monitoramento
- `sql/criar_tabela_mensagens_agendadas.sql` - Cria tabela de mensagens agendadas

---

## ⚙️ **Configuração**

### 1. **Executar Scripts SQL**
```sql
-- Execute no phpMyAdmin
SOURCE criar_tabela_monitoramento_simples.sql;
SOURCE criar_tabela_mensagens_agendadas.sql;
```

### 2. **Configurar Cron Jobs**
```bash
# Processar mensagens agendadas (a cada 5 minutos)
0,5,10,15,20,25,30,35,40,45,50,55 * * * * php /caminho/para/painel/cron/processar_mensagens_agendadas.php

# Verificar cobranças vencidas (a cada 30 minutos)
0,30 * * * * php /caminho/para/painel/cron/monitoramento_automatico.php
```

### 3. **Configurar Webhook**
No VPS do WhatsApp, configurar webhook para enviar mensagens recebidas para:
```
http://seu-dominio.com/api/processar_mensagem_cliente.php
```

### 4. **Verificar Configurações**
- Chave da API do Asaas configurada em `configuracoes`
- Canal "financeiro" conectado no WhatsApp
- Diretório `logs` com permissão de escrita

---

## 📊 **Fluxo de Funcionamento**

### 1. **Validação Manual**
```
Usuário clica "Validar" → Envia mensagem de apresentação → Cliente recebe
```

### 2. **Ativação de Monitoramento**
```
Usuário marca "Monitorar" → Cliente é adicionado ao sistema → Monitoramento ativo
```

### 3. **Verificação Automática**
```
Cron verifica cobranças vencidas → Consulta status real no Asaas → Atualiza banco se necessário
```

### 4. **Agendamento de Mensagens**
```
Se há cobranças vencidas → Calcula prioridade → Agenda mensagem → Distribui ao longo do dia
```

### 5. **Processamento de Mensagens**
```
Cron processa mensagens agendadas → Verifica se cliente ainda monitorado → Envia via VPS → Registra histórico
```

### 6. **Respostas Automáticas**
```
Cliente envia mensagem → Webhook recebe → Identifica cliente → Processa resposta → Envia automaticamente
```

---

## 🔧 **Configurações Avançadas**

### Horários de Funcionamento
```php
$config_horarios = [
    'inicio_dia' => '09:00',
    'fim_dia' => '18:00',
    'intervalo_min' => 3, // minutos entre mensagens
    'max_por_hora' => 10, // máximo de mensagens por hora
    'max_por_dia' => 50   // máximo de mensagens por dia
];
```

### Prioridades
```php
// Alta: >30 dias vencido ou valor >R$ 1.000
// Normal: 7-30 dias vencido
// Baixa: <7 dias vencido e valor <R$ 100
```

### Intervalos de Verificação
- **Verificação de cobranças**: A cada 2 horas
- **Processamento de mensagens**: A cada 5 minutos
- **Primeira verificação**: 5 minutos após inicialização

---

## 📝 **Logs do Sistema**

### Arquivos de Log
- `logs/status_asaas.log` - Atualizações de status do Asaas
- `logs/agendamento_mensagens.log` - Agendamentos de mensagens
- `logs/processamento_agendadas.log` - Processamento de mensagens agendadas
- `logs/monitoramento_automatico.log` - Verificações automáticas

### Exemplo de Log
```
2024-01-15 10:30:00 - Mensagem agendada para cliente João Silva (ID: 123) - Horário: 2024-01-15 14:30:00 - Prioridade: alta
2024-01-15 10:30:05 - Status atualizado para cliente Maria Santos: 2 cobranças
2024-01-15 14:30:00 - Mensagem agendada 456 enviada para João Silva (ID: 123)
```

---

## 🚨 **Limites e Restrições**

### WhatsApp
- **Máximo 50 mensagens/dia** para evitar bloqueio
- **Intervalo mínimo 3 minutos** entre mensagens
- **Horário comercial** (9h-18h) para melhor recepção

### Asaas
- **Rate limit**: Máximo 100 consultas/minuto
- **Timeout**: 10 segundos por consulta
- **Retry**: 3 tentativas em caso de erro

### Sistema
- **Máximo 5 mensagens** processadas por vez
- **Timeout**: 10 segundos para envios
- **Logs**: Mantidos por 30 dias

---

## 🔍 **Troubleshooting**

### Problema: Mensagens não são enviadas
**Solução:**
1. Verificar se cron jobs estão ativos
2. Verificar logs em `logs/processamento_agendadas.log`
3. Verificar se cliente está sendo monitorado
4. Verificar conectividade com VPS

### Problema: Status não atualiza do Asaas
**Solução:**
1. Verificar chave da API do Asaas
2. Verificar logs em `logs/status_asaas.log`
3. Verificar conectividade com API do Asaas

### Problema: Muitas mensagens de erro
**Solução:**
1. Verificar limites do WhatsApp
2. Ajustar intervalos de envio
3. Verificar status do VPS

---

## 📞 **Suporte**

Para dúvidas ou problemas:
1. Verificar logs do sistema
2. Consultar documentação
3. Verificar configurações
4. Testar com script `teste_monitoramento.php`

---

## 🎯 **Benefícios da Versão 2.0**

✅ **Precisão**: Status real do Asaas antes de enviar  
✅ **Eficiência**: Distribuição inteligente de mensagens  
✅ **Controle**: Ativação manual obrigatória  
✅ **Consolidação**: Múltiplas faturas em uma mensagem  
✅ **Inteligência**: Prioridades baseadas em valor e tempo  
✅ **Segurança**: Limites para evitar bloqueios  
✅ **Monitoramento**: Logs detalhados de todas as ações  

**O sistema agora funciona como um verdadeiro "financeiro virtual" inteligente!** 🚀💬 