# 💬 Sistema de Gestão Integrado - Chat WhatsApp + Asaas

Sistema completo de gestão de conversas WhatsApp com aprovação manual de clientes e integração financeira com Asaas, similar ao Kommo CRM.

## 🎯 **Principais Funcionalidades**

### 📱 **Chat Centralizado**
- Interface moderna similar ao WhatsApp
- Atualização em tempo real (2-30s adaptativos)
- Três colunas: Conversas | Detalhes Cliente | Chat
- Sistema de cache inteligente para performance
- Polling adaptativo baseado em atividade do usuário

### 💰 **Integração Financeira Asaas**
- ✅ **Webhook funcional** para recebimento de notificações
- ✅ **Processamento automático** de pagamentos e assinaturas
- ✅ **Sincronização** com banco de dados local
- ✅ **Sistema de logs** completo para auditoria
- ✅ **Interface de testes** integrada

### 🔐 **Sistema de Aprovação Manual**
- **Números desconhecidos** ficam pendentes para aprovação
- **Controle total** sobre quais clientes podem usar o sistema
- **Migração automática** de mensagens ao aprovar
- **Histórico completo** de decisões (aprovado/rejeitado)

### 🤖 **Integração WhatsApp**
- Webhook para recebimento automático de mensagens
- Envio de mensagens via robô WhatsApp
- QR Code para conexão
- Status de conexão em tempo real

---

## 🏗️ **Arquitetura do Sistema**

### **📊 Estrutura de Banco de Dados**

#### **Tabelas Principais:**
- `clientes` - Clientes aprovados e ativos
- `mensagens_comunicacao` - Mensagens dos clientes ativos
- `canais_comunicacao` - Configurações dos canais (WhatsApp, etc.)

#### **Sistema de Aprovação:**
- `clientes_pendentes` - Números aguardando aprovação
- `mensagens_pendentes` - Mensagens de clientes pendentes

#### **Sistema Financeiro (Asaas):**
- `cobrancas` - Cobranças e pagamentos sincronizados
- `assinaturas` - Assinaturas recorrentes
- `configuracoes` - Chaves API e configurações

### **🔄 Fluxo de Mensagens**

```
Mensagem WhatsApp → Webhook → Verificação Cliente
                                     ↓
              Cliente Existente? ─── Sim ──→ Chat Normal
                     ↓
                    Não
                     ↓
              Tabela Pendentes ──→ Aguarda Aprovação
                     ↓                      ↓
               [Aprovado] ─────────→ Chat Normal
                     ↓
               [Rejeitado] ────────→ Mensagem Ignorada
```

### **💰 Fluxo de Pagamentos (Asaas)**

```
Pagamento Asaas → Webhook → Validação → Atualização DB
                                             ↓
                                     Log de Auditoria
                                             ↓
                                    Notificação Sistema
```

---

## 🚀 **Instalação e Configuração**

### **1. Requisitos**
- PHP 7.4+
- MySQL 5.7+
- Apache/Nginx
- Extensões PHP: mysqli, json, curl
- Conta Asaas (para integração financeira)

### **2. Configuração Inicial**

#### **a) Clone o Repositório:**
```bash
git clone https://github.com/pixel12digital/revenda-sites.git
cd revenda-sites
```

#### **b) Configure o Banco de Dados:**
```php
// painel/config.php
$host = 'localhost';
$username = 'seu_usuario';
$password = 'sua_senha';
$database = 'seu_banco';
```

#### **c) Configure a API do Asaas:**
```php
// painel/config.php
define('ASAAS_API_KEY', '$aact_prod_SUA_CHAVE_AQUI');
define('ASAAS_API_URL', 'https://www.asaas.com/api/v3');
```

#### **d) Crie as Tabelas do Sistema:**
```bash
# Sistema de aprovação
php painel/api/criar_tabela_pendentes.php

# Estrutura financeira
php painel/sql/criar_tabela_configuracoes.sql
```

### **3. Configuração WhatsApp**

#### **a) Configure o VPS WhatsApp:**
- URL do VPS: `http://212.85.11.238:3000`
- Configure o webhook para: `https://seu-dominio.com/api/webhook_whatsapp.php`

#### **b) Configure Automaticamente:**
```bash
# Local (XAMPP):
php painel/configurar_webhook_ambiente.php

# Produção (Hostinger):
php painel/diagnosticar_producao.php
```

### **4. Configuração Asaas (Nova!)**

#### **a) Configure o Webhook no Painel Asaas:**
1. Acesse: https://asaas.com/customerConfigurations/webhooks
2. **URL**: `https://seu-dominio.com/public/webhook_asaas.php`
3. **Eventos**: Selecione todos os eventos de pagamento e assinatura

#### **b) Teste o Webhook:**
```bash
# Usar interface integrada
# Acesse: https://seu-dominio.com/admin/webhook-test.php
# Clique em "💰 Testar Webhook Asaas"

# Ou via linha de comando:
php -f public/webhook_asaas.php << 'EOF'
{
  "event": "PAYMENT_RECEIVED",
  "payment": {
    "id": "pay_test_123",
    "status": "RECEIVED",
    "value": 100.00
  }
}
EOF
```

---

## 📋 **Como Usar o Sistema**

### **🎛️ Painel de Controle**

#### **1. Chat Centralizado**
```
Acesse: painel/chat.php
```
- **Coluna 1**: Lista de conversas ativas
- **Coluna 2**: Detalhes do cliente selecionado  
- **Coluna 3**: Chat com mensagens

#### **2. Conexão WhatsApp**
```
Acesse: painel/comunicacao.php
```
- Conectar via QR Code
- Monitorar status da conexão
- Gerenciar sessões

#### **3. Centro de Testes (Novo!)**
```
Acesse: admin/webhook-test.php
```
- **🌐 Teste VPS**: Conectividade com servidor
- **🔗 Teste Webhook**: Endpoints WhatsApp
- **💰 Teste Asaas**: Webhook financeiro
- **🗄️ Banco de Dados**: Verificação de tabelas
- **🧪 Fluxo Completo**: Teste de envio/recebimento de mensagens
- **🩺 Diagnóstico**: Verificação completa do sistema

### **🔐 Gerenciamento de Clientes Pendentes**

#### **1. Listar Pendentes:**
```bash
GET /painel/api/clientes_pendentes.php?action=list
```

#### **2. Ver Mensagens de um Pendente:**
```bash
GET /painel/api/clientes_pendentes.php?action=messages&pendente_id=123
```

#### **3. Aprovar Cliente:**
```bash
POST /painel/api/clientes_pendentes.php
{
    "action": "approve",
    "pendente_id": 123,
    "nome_cliente": "João Silva",
    "email_cliente": "joao@email.com"
}
```

#### **4. Rejeitar Cliente:**
```bash
POST /painel/api/clientes_pendentes.php
{
    "action": "reject", 
    "pendente_id": 123,
    "motivo": "Número suspeito"
}
```

#### **5. Estatísticas:**
```bash
GET /painel/api/clientes_pendentes.php?action=stats
```

### **💰 Gestão Financeira (Asaas)**

#### **1. Monitorar Pagamentos:**
```
Acesse: painel/faturas.php
```
- Ver status dos pagamentos em tempo real
- Sincronização automática via webhook
- Logs detalhados de transações

#### **2. Verificar Logs do Webhook:**
```bash
# Logs automáticos em:
tail -f logs/webhook_asaas_$(date +%Y-%m-%d).log
```

#### **3. Reenviar Link de Pagamento:**
```bash
# Via API:
POST /painel/api/asaas_reenviar.php
{
    "asaas_payment_id": "pay_123456789"
}
```

---

## ⚡ **Sistema de Cache Inteligente**

### **🧠 Cache Adaptativo:**

| **Situação** | **Cache** | **Polling** | **Performance** |
|--------------|-----------|-------------|-----------------|
| 🟢 **Usuário ativo** | 5s | 2s | Máxima responsividade |
| 🟡 **Moderadamente ativo** | 15s | 5s | Balanceado |
| 🔴 **Usuário inativo** | 30s | 30s | 80% menos consultas DB |

### **🔄 Invalidação Automática:**
- Cache limpo quando mensagem chega
- Detecção de atividade do usuário
- Transição automática entre modos

---

## 🛠️ **Manutenção e Monitoramento**

### **📊 Monitoramento**

#### **1. Status do Sistema:**
```bash
# Verificar WhatsApp
php painel/monitorar_mensagens.php

# Testar webhook
php painel/testar_webhook.php

# Diagnosticar produção  
php painel/diagnosticar_producao.php

# Verificar Asaas (Novo!)
php painel/api/verificar_status_asaas.php
```

#### **2. Logs Importantes:**
- `logs/webhook_whatsapp_*.log` - Mensagens WhatsApp recebidas
- `logs/webhook_asaas_*.log` - **Eventos Asaas processados**
- `painel/debug_*.log` - Debug do sistema
- `api/debug_webhook.log` - Debug do webhook

#### **3. Centro de Testes Integrado:**
```
URL: admin/webhook-test.php

Testes Disponíveis:
- 🌐 Conectividade VPS
- 🔗 Webhook WhatsApp  
- 💰 Webhook Asaas
- 🗄️ Banco de Dados
- 🧪 Fluxo Completo
- 🩺 Diagnóstico
```

### **🔧 Correções Comuns**

#### **1. Mensagens não aparecem:**
```bash
# Verificar webhook
curl -X POST https://seu-dominio.com/api/webhook_whatsapp.php

# Testar database
php painel/verificar_tabela_clientes.php

# Limpar cache
rm -rf /tmp/loja_virtual_cache/*
```

#### **2. WhatsApp desconectado:**
```bash
# Reconectar
php painel/corrigir_canal.php

# Reconfigurar webhook
php painel/configurar_webhook_ambiente.php
```

#### **3. Webhook Asaas não funciona:**
```bash
# Testar endpoint
curl -X POST https://seu-dominio.com/public/webhook_asaas.php \
  -H "Content-Type: application/json" \
  -d '{"event":"PAYMENT_RECEIVED","payment":{"id":"test","status":"RECEIVED"}}'

# Verificar logs
tail -f logs/webhook_asaas_$(date +%Y-%m-%d).log

# Interface de teste
# Acesse: admin/webhook-test.php → "💰 Testar Webhook Asaas"
```

#### **4. Performance lenta:**
```bash
# Verificar cache
php painel/api/record_activity.php

# Otimizar banco
OPTIMIZE TABLE mensagens_comunicacao, clientes, clientes_pendentes, cobrancas;
```

---

## 🌐 **Ambientes de Deploy**

### **🏠 Local (XAMPP)**
```bash
# URL: http://localhost/loja-virtual-revenda/
# Webhook WhatsApp: http://localhost:8080/loja-virtual-revenda/api/webhook_whatsapp.php
# Webhook Asaas: http://localhost:8080/loja-virtual-revenda/public/webhook_asaas.php
# Requer ngrok para receber mensagens externas
```

### **☁️ Produção (Hostinger)**
```bash
# URL: https://pixel12digital.com.br/app/
# Webhook WhatsApp: https://pixel12digital.com.br/app/api/webhook_whatsapp.php
# Webhook Asaas: https://pixel12digital.com.br/app/public/webhook_asaas.php
# Deploy via git pull
```

### **🔄 Deploy Automático:**
```bash
# Local → Produção
git add .
git commit -m "Suas mudanças"
git push

# Na Hostinger:
cd app
git pull
```

---

## 🔧 **API Reference**

### **📱 Chat APIs**

#### **Conversas:**
- `GET /painel/api/conversas_recentes.php` - Lista conversas
- `GET /painel/api/conversas_nao_lidas.php` - Conversas não lidas
- `GET /painel/api/mensagens_cliente.php?cliente_id=X` - Mensagens

#### **Mensagens:**
- `POST /chat_enviar.php` - Enviar mensagem
- `GET /painel/api/check_new_messages.php` - Verificar novas
- `POST /painel/api/record_activity.php` - Registrar atividade

### **🔐 Aprovação APIs**

#### **Clientes Pendentes:**
- `GET /painel/api/clientes_pendentes.php?action=list`
- `GET /painel/api/clientes_pendentes.php?action=messages&pendente_id=X`
- `POST /painel/api/clientes_pendentes.php` (approve/reject)
- `GET /painel/api/clientes_pendentes.php?action=stats`

### **🤖 WhatsApp APIs**

#### **Webhook:**
- `POST /api/webhook_whatsapp.php` - Receber mensagens
- `POST /ajax_whatsapp.php` - Controlar robô
- `GET /painel/api/whatsapp_webhook.php` - Status

### **💰 Asaas APIs (Novo!)**

#### **Webhook:**
- `POST /public/webhook_asaas.php` - **Receber eventos Asaas**
- `GET /painel/api/verificar_status_asaas.php` - Status da integração
- `POST /painel/api/update_asaas_key.php` - Atualizar chave API

#### **Gestão:**
- `GET /painel/faturas.php` - Interface de faturas
- `POST /painel/api/asaas_reenviar.php` - Reenviar links
- `GET /painel/clientes_asaas.php` - Clientes sincronizados

#### **Eventos Suportados:**
- `PAYMENT_RECEIVED` - Pagamento recebido
- `PAYMENT_CONFIRMED` - Pagamento confirmado  
- `PAYMENT_OVERDUE` - Pagamento vencido
- `PAYMENT_DELETED` - Pagamento excluído
- `PAYMENT_RESTORED` - Pagamento restaurado
- `PAYMENT_REFUNDED` - Pagamento estornado
- `SUBSCRIPTION_*` - Eventos de assinatura

### **🧪 Testing APIs (Novo!)**

#### **Centro de Testes:**
- `GET /admin/webhook-test.php` - Interface de testes
- `POST /admin/test-database.php` - Teste de banco de dados

---

## 📈 **Estatísticas e Métricas**

### **📊 Métricas Disponíveis:**
- Total de conversas ativas
- Mensagens não lidas
- Clientes pendentes de aprovação
- Taxa de aprovação/rejeição
- Performance do cache
- Status da conexão WhatsApp
- **Status da integração Asaas**
- **Pagamentos processados via webhook**

### **🎯 KPIs Importantes:**
- **Tempo de resposta**: < 5 segundos
- **Taxa de entrega**: > 95%
- **Uptime WhatsApp**: > 99%
- **Cache hit rate**: > 80%
- **Webhook Asaas**: > 99% sucesso
- **Sincronização financeira**: < 30 segundos

---

## 🛡️ **Segurança**

### **🔒 Medidas de Segurança:**
- Validação de entrada em todos os endpoints
- Escape de SQL para prevenir injection
- Rate limiting nos webhooks
- Logs de auditoria completos
- Sistema de aprovação manual para novos clientes
- **Validação de eventos Asaas**
- **Logs criptografados de transações**

### **🚨 Monitoramento:**
- Logs de acesso suspeito
- Verificação de integridade do webhook
- Backup automático de mensagens importantes
- Alertas de falhas na conexão
- **Monitoramento financeiro em tempo real**
- **Alertas de falhas no Asaas**

---

## 📞 **Suporte e Troubleshooting**

### **🆘 Problemas Comuns:**

#### **1. "Mensagens não chegam"**
```bash
# Verificar webhook
php painel/testar_webhook.php

# Verificar VPS
curl http://212.85.11.238:3000/status

# Reconfigurar
php painel/diagnosticar_producao.php
```

#### **2. "Sistema lento"**
```bash
# Limpar cache
rm -rf /tmp/loja_virtual_cache/*

# Verificar atividade
php painel/api/record_activity.php?cliente_id=1

# Otimizar DB
OPTIMIZE TABLE mensagens_comunicacao;
```

#### **3. "QR Code não aparece"**
```bash
# Verificar modal
php painel/iniciar_sessao.php

# Testar endpoints QR
php painel/descobrir_endpoints_qr.php
```

#### **4. "Webhook Asaas não funciona" (Novo!)**
```bash
# Verificar configuração
curl -X POST https://seu-dominio.com/public/webhook_asaas.php \
  -H "Content-Type: application/json" \
  -d '{"event":"PAYMENT_RECEIVED","payment":{"id":"test","status":"RECEIVED"}}'

# Verificar logs
tail -f logs/webhook_asaas_$(date +%Y-%m-%d).log

# Testar via interface
# Acesse: admin/webhook-test.php
# Clique em "💰 Testar Webhook Asaas"

# Verificar configuração no Asaas
# URL deve ser: https://seu-dominio.com/public/webhook_asaas.php
```

#### **5. "Pagamentos não sincronizam"**
```bash
# Verificar chave API
php painel/api/verificar_status_asaas.php

# Sincronização manual
php painel/sincroniza_asaas.php

# Verificar eventos configurados no Asaas
# Deve incluir: PAYMENT_*, SUBSCRIPTION_*
```

### **📧 Contato:**
- **Email**: suporte@pixel12digital.com.br
- **GitHub**: https://github.com/pixel12digital/revenda-sites
- **Documentação**: Este README.md

---

## 📝 **Changelog**

### **v2.1.0 - Integração Financeira Asaas (NOVO!)**
- ✅ **Webhook funcional** `public/webhook_asaas.php`
- ✅ **Processamento automático** de pagamentos e assinaturas
- ✅ **Sistema de logs** completo (`logs/webhook_asaas_*.log`)
- ✅ **Interface de testes** integrada ao centro de testes
- ✅ **Validação de eventos** e resposta JSON adequada
- ✅ **Suporte a múltiplos eventos** (PAYMENT_*, SUBSCRIPTION_*)
- ✅ **Criação automática** de tabelas se não existirem

### **v2.0.0 - Sistema de Aprovação Manual**
- ✅ Sistema de aprovação similar ao Kommo CRM
- ✅ Tabelas de clientes pendentes
- ✅ API completa para gerenciamento
- ✅ Migração automática de mensagens
- ✅ Cache inteligente adaptativo

### **v1.5.0 - Otimizações de Performance**  
- ✅ Cache adaptativo baseado em atividade
- ✅ Polling inteligente (2s-30s)
- ✅ Redução de 80% nas consultas quando inativo
- ✅ Sistema de invalidação agressiva

### **v1.0.0 - Chat Centralizado**
- ✅ Interface WhatsApp-like  
- ✅ Três colunas responsivas
- ✅ Integração com VPS WhatsApp
- ✅ Sistema de cache básico
- ✅ Webhook para recebimento

---

## 🎯 **Roadmap Futuro**

### **v2.2.0 - Planejado**
- [ ] Interface web para aprovação de clientes
- [ ] Notificações push para novos pendentes  
- [ ] Dashboard financeiro em tempo real
- [ ] Relatórios de pagamentos e inadimplência

### **v2.3.0 - Planejado**
- [ ] WebSockets para tempo real
- [ ] Suporte a múltiplos agentes
- [ ] Integração com outros gateways de pagamento
- [ ] Automações financeiras baseadas em eventos

### **v3.0.0 - Futuro**
- [ ] Integração com outros CRMs
- [ ] Sistema de comissões
- [ ] Relatórios avançados de conversas
- [ ] Tags e categorias para clientes

---

## 🏆 **Status do Sistema**

### **✅ Totalmente Funcional:**
- 💬 **Chat WhatsApp** - Sistema completo de mensagens
- 🔐 **Aprovação Manual** - Controle total de acesso
- 💰 **Integração Asaas** - Webhook e sincronização funcionais
- 🧪 **Centro de Testes** - Interface de diagnóstico completa
- 📊 **Monitoramento** - Logs e métricas em tempo real

### **🎯 Pronto para Produção:**
- ✅ Código testado e validado
- ✅ Documentação completa
- ✅ Sistema de logs robusto
- ✅ Interface de testes integrada
- ✅ Webhook Asaas 100% funcional

---

**🎉 Sistema totalmente funcional e documentado! Pronto para produção com integração financeira completa.** 

Para suporte, consulte este README ou entre em contato com a equipe de desenvolvimento. 