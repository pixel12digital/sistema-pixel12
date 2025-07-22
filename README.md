# 💬 Sistema de Chat Centralizado com WhatsApp

Sistema completo de gestão de conversas WhatsApp com aprovação manual de clientes, similar ao Kommo CRM.

## 🎯 **Principais Funcionalidades**

### 📱 **Chat Centralizado**
- Interface moderna similar ao WhatsApp
- Atualização em tempo real (2-30s adaptativos)
- Três colunas: Conversas | Detalhes Cliente | Chat
- Sistema de cache inteligente para performance
- Polling adaptativo baseado em atividade do usuário

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

---

## 🚀 **Instalação e Configuração**

### **1. Requisitos**
- PHP 7.4+
- MySQL 5.7+
- Apache/Nginx
- Extensões PHP: mysqli, json, curl

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

#### **c) Crie as Tabelas do Sistema de Aprovação:**
```bash
php painel/api/criar_tabela_pendentes.php
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
```

#### **2. Logs Importantes:**
- `logs/webhook_whatsapp_*.log` - Mensagens recebidas
- `painel/debug_*.log` - Debug do sistema
- `api/debug_webhook.log` - Debug do webhook

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

#### **3. Performance lenta:**
```bash
# Verificar cache
php painel/api/record_activity.php

# Otimizar banco
OPTIMIZE TABLE mensagens_comunicacao, clientes, clientes_pendentes;
```

---

## 🌐 **Ambientes de Deploy**

### **🏠 Local (XAMPP)**
```bash
# URL: http://localhost/loja-virtual-revenda/
# Webhook: http://localhost:8080/loja-virtual-revenda/api/webhook_whatsapp.php
# Requer ngrok para receber mensagens externas
```

### **☁️ Produção (Hostinger)**
```bash
# URL: https://pixel12digital.com.br/app/
# Webhook: https://pixel12digital.com.br/app/api/webhook_whatsapp.php
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

---

## 📈 **Estatísticas e Métricas**

### **📊 Métricas Disponíveis:**
- Total de conversas ativas
- Mensagens não lidas
- Clientes pendentes de aprovação
- Taxa de aprovação/rejeição
- Performance do cache
- Status da conexão WhatsApp

### **🎯 KPIs Importantes:**
- **Tempo de resposta**: < 5 segundos
- **Taxa de entrega**: > 95%
- **Uptime WhatsApp**: > 99%
- **Cache hit rate**: > 80%

---

## 🛡️ **Segurança**

### **🔒 Medidas de Segurança:**
- Validação de entrada em todos os endpoints
- Escape de SQL para prevenir injection
- Rate limiting nos webhooks
- Logs de auditoria completos
- Sistema de aprovação manual para novos clientes

### **🚨 Monitoramento:**
- Logs de acesso suspeito
- Verificação de integridade do webhook
- Backup automático de mensagens importantes
- Alertas de falhas na conexão

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

### **📧 Contato:**
- **Email**: suporte@pixel12digital.com.br
- **GitHub**: https://github.com/pixel12digital/revenda-sites
- **Documentação**: Este README.md

---

## 📝 **Changelog**

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

### **v2.1.0 - Planejado**
- [ ] Interface web para aprovação de clientes
- [ ] Notificações push para novos pendentes  
- [ ] Integração com outros CRMs
- [ ] Relatórios avançados de conversas

### **v2.2.0 - Planejado**
- [ ] WebSockets para tempo real
- [ ] Suporte a múltiplos agentes
- [ ] Tags e categorias para clientes
- [ ] Automações baseadas em palavras-chave

---

**🎉 Sistema totalmente funcional e documentado! Pronto para produção.** 

Para suporte, consulte este README ou entre em contato com a equipe de desenvolvimento. 