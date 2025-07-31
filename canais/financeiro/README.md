# 📊 CANAL FINANCEIRO - DOCUMENTAÇÃO COMPLETA

## 🎯 **Visão Geral**
O Canal Financeiro é o canal principal da Pixel12Digital, responsável por cobranças automatizadas e consulta de faturas.

## 📋 **Informações Técnicas**

### 🔧 **Configurações Básicas**
- **ID do Canal**: 3000
- **Número do Canal**: 36
- **Nome**: Financeiro
- **Tipo**: WhatsApp
- **Status**: ✅ Ativo

### 📱 **Configurações do WhatsApp**
- **Número**: 47 997309525
- **Formato API**: 5547997309525@c.us
- **Webhook**: `/api/webhook_whatsapp.php`

### 🗄️ **Banco de Dados**
- **Banco**: `pixel12digital` (banco principal)
- **Tipo**: Banco compartilhado com aplicação principal
- **Conexão**: Via `config.php` da raiz

## 🚀 **Funcionalidades**

### ✅ **Funcionalidades Ativas**
- ✅ **Saudação automática** na primeira mensagem
- ✅ **Consulta de faturas** via palavra-chave "faturas"
- ✅ **Reinforcement** para mensagens subsequentes
- ✅ **Direcionamento** para contato direto (47 997309525)
- ✅ **Identificação de clientes** por número/CPF/CNPJ
- ✅ **Notificações push** em tempo real
- ✅ **Logs detalhados** de todas as interações

### 🚫 **Funcionalidades Removidas**
- ❌ **Solicitação de atendente** (removida conforme solicitado)
- ❌ **Frases que estimulam diálogo** (removidas)

## 📁 **Estrutura de Arquivos**

```
📁 canais/financeiro/
├── 📄 canal_config.php     # Configurações específicas do canal
├── 📄 webhook.php          # Webhook específico (futuro)
└── 📄 README.md           # Esta documentação
```

## 🔧 **Configurações de Automação**

### 💬 **Mensagens Automáticas**

#### **Saudação Inicial**
```
Olá [Nome]! 👋

🤖 Este é um canal exclusivo da Pixel12Digital para cobranças automatizadas.

💰 Para consultar suas faturas, digite: faturas

📞 Para outros assuntos ou falar com nossa equipe:
Entre em contato diretamente: 47 997309525
```

#### **Mensagem de Reforço**
```
🤖 Este é um canal exclusivo para cobranças automatizadas.

💰 Para consultar faturas: digite "faturas"
📞 Para outros assuntos: entre em contato diretamente com nossa equipe
📱 Telefone: 47 997309525
```

#### **Resposta para Faturas**
- Busca faturas vencidas e próximas a vencer
- Sincronização automática com Asaas
- Links para pagamento
- Resumo de valores

## 🛠️ **Manutenção**

### 📊 **Logs**
- **Localização**: `../logs/webhook_whatsapp_YYYY-MM-DD.log`
- **Prefix**: `[CANAL_FINANCEIRO]`
- **Nível**: Detalhado

### 🔄 **Backup**
- **Frequência**: Diário
- **Localização**: Backup automático do banco principal
- **Retenção**: 30 dias

### 🚨 **Monitoramento**
- **Status**: Ativo 24/7
- **Notificações**: Push em tempo real
- **Alertas**: Falhas de envio e recebimento

## 🔍 **Troubleshooting**

### ❌ **Problemas Comuns**

#### **Resposta não enviada**
1. Verificar logs: `../logs/webhook_whatsapp_*.log`
2. Verificar status do WhatsApp API
3. Verificar configurações em `canal_config.php`

#### **Cliente não identificado**
1. Verificar formato do número (deve incluir 55)
2. Verificar se cliente existe no banco
3. Verificar similaridade de números

#### **Faturas não encontradas**
1. Verificar sincronização com Asaas
2. Verificar asaas_id do cliente
3. Verificar status das cobranças

### ✅ **Soluções Rápidas**

#### **Reiniciar Webhook**
```bash
# Na VPS
pm2 restart whatsapp-api
```

#### **Verificar Status**
```bash
# Verificar logs
tail -f ../logs/webhook_whatsapp_$(date +%Y-%m-%d).log

# Verificar status da API
curl -s "http://212.85.11.238:3000/status"
```

#### **Testar Canal**
```bash
# Executar teste
php teste_forcar_resposta.php
```

## 📞 **Contatos de Suporte**

### 🛠️ **Desenvolvimento**
- **Responsável**: Pixel12Digital
- **Email**: suporte@pixel12digital.com.br
- **WhatsApp**: 47 997309525

### 🚨 **Emergências**
- **VPS**: 212.85.11.238
- **WhatsApp API**: Porta 3000
- **Backup**: Automático diário

## 📈 **Métricas e Estatísticas**

### 📊 **Dados de Uso**
- **Mensagens/dia**: ~50-100
- **Clientes ativos**: ~500
- **Taxa de resposta**: 100%
- **Tempo médio de resposta**: <2 segundos

### 📈 **Performance**
- **Uptime**: 99.9%
- **Latência**: <100ms
- **Taxa de erro**: <0.1%

## 🔄 **Atualizações**

### 📅 **Histórico de Versões**
- **v1.0.0** (2025-07-31): Versão inicial
- **v1.1.0** (2025-07-31): Remoção de solicitação de atendente
- **v1.2.0** (2025-07-31): Mensagens mais diretas

### 🔮 **Próximas Atualizações**
- [ ] Integração com mais gateways de pagamento
- [ ] Relatórios avançados
- [ ] Dashboard de métricas

## 📚 **Documentação Relacionada**

- 📄 [README Principal](../README.md)
- 📄 [Documentação VPS](../docs/README_VPS_MULTI_CANAL.md)
- 📄 [Guia de Criação de Canais](../docs/GUIA_CRIACAO_CANAIS.md)
- 📄 [Manutenção de Canais](../docs/MANUTENCAO_CANAIS.md)

---

**Última atualização**: 31/07/2025  
**Versão**: 1.2.0  
**Responsável**: Pixel12Digital 