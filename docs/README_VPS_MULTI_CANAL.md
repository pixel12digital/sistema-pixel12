# 🖥️ VPS MULTI-CANAL - DOCUMENTAÇÃO COMPLETA

## 🎯 **Visão Geral**
Esta documentação descreve a estrutura completa da VPS para gerenciamento de múltiplos canais WhatsApp da Pixel12Digital.

## 📊 **Estrutura de Canais**

### 🏗️ **Arquitetura Multi-Canal**
```
📊 SISTEMA MULTI-CANAL PIXEL12DIGITAL
├── CANAL FINANCEIRO (ATIVO)
│   ├── ID: 3000 (canal 36)
│   ├── Número: 47 997309525
│   ├── Banco: pixel12digital (principal)
│   └── Webhook: /api/webhook_whatsapp.php
│
├── 💼 CANAL COMERCIAL (FUTURO)
│   ├── ID: 3001 (canal 37)
│   ├── Número: 47 999999999
│   ├── Banco: pixel12digital_comercial
│   └── Webhook: /api/webhook_canal_37.php
│
├──️ CANAL SUPORTE (FUTURO)
│   ├── ID: 3002 (canal 38)
│   ├── Número: 47 888888888
│   ├── Banco: pixel12digital_suporte
│   └── Webhook: /api/webhook_canal_38.php
│
└── 📈 CANAL VENDAS (FUTURO)
    ├── ID: 3003 (canal 39)
    ├── Número: 47 777777777
    ├── Banco: pixel12digital_vendas
    └── Webhook: /api/webhook_canal_39.php
```

## 🖥️ **Configurações da VPS**

### 📍 **Informações do Servidor**
- **IP**: 212.85.11.238
- **Sistema**: Ubuntu 20.04 LTS
- **Provedor**: Hostinger
- **Localização**: Brasil
- **Uptime**: 99.9%

### 🔧 **Serviços Ativos**

#### **WhatsApp API**
- **Porta**: 3000
- **Status**: ✅ Ativo
- **Process Manager**: PM2
- **Comando**: `pm2 start whatsapp-api`
- **Logs**: `/var/log/whatsapp-api.log`

#### **Nginx**
- **Porta**: 80/443
- **Status**: ✅ Ativo
- **Config**: `/etc/nginx/sites-available/pixel12digital`
- **SSL**: Let's Encrypt

#### **MySQL**
- **Porta**: 3306
- **Status**: ✅ Ativo
- **Usuário**: pixel12digital
- **Backup**: Automático diário

## 📁 **Estrutura de Pastas**

### 🗂️ **Estrutura Completa**
```
📁 /var/www/html/loja-virtual-revenda/
├── 📁 api/ (WEBHOOKS PRINCIPAIS)
│   ├── webhook_whatsapp.php (CANAL FINANCEIRO)
│   ├── webhook_canal_37.php (FUTURO - COMERCIAL)
│   ├── webhook_canal_38.php (FUTURO - SUPORTE)
│   └── webhook_canal_39.php (FUTURO - VENDAS)
│
├── 📁 canais/ (MÓDULO MULTI-CANAL)
│   ├── 📁 financeiro/
│   │   ├── canal_config.php
│   │   ├── webhook.php (futuro)
│   │   └── README.md
│   │
│   ├── 📁 comercial/ (FUTURO)
│   │   ├── canal_config.php
│   │   ├── webhook.php
│   │   └── README.md
│   │
│   ├── 📁 suporte/ (FUTURO)
│   │   ├── canal_config.php
│   │   ├── webhook.php
│   │   └── README.md
│   │
│   └── 📁 template/
│       ├── canal_config.php
│       ├── webhook.php
│       └── README.md
│
├── 📁 docs/ (DOCUMENTAÇÃO)
│   ├── README_VPS_MULTI_CANAL.md (ESTE ARQUIVO)
│   ├── GUIA_CRIACAO_CANAIS.md
│   ├── MANUTENCAO_CANAIS.md
│   └── CAMINHOS_VPS.md
│
├── 📁 logs/ (LOGS DO SISTEMA)
│   ├── webhook_whatsapp_YYYY-MM-DD.log
│   ├── webhook_canal_37_YYYY-MM-DD.log (FUTURO)
│   ├── webhook_canal_38_YYYY-MM-DD.log (FUTURO)
│   └── webhook_canal_39_YYYY-MM-DD.log (FUTURO)
│
├── 📁 painel/ (PAINEL ADMINISTRATIVO)
│   ├── index.php
│   ├── api/
│   └── assets/
│
├── 📁 backups/ (BACKUPS AUTOMÁTICOS)
│   ├── database/
│   ├── logs/
│   └── configs/
│
├── config.php (CONFIGURAÇÃO PRINCIPAL)
├── README.md (DOCUMENTAÇÃO PRINCIPAL)
└── .env (VARIÁVEIS DE AMBIENTE)
```

## 🗄️ **Bancos de Dados**

### 📊 **Estrutura de Bancos**
```
🗄️ BANCOS DE DADOS HOSTINGER
├── pixel12digital (PRINCIPAL)
│   ├── Canal Financeiro
│   ├── Aplicação principal
│   └── Sistema de gestão
│
├── pixel12digital_comercial (FUTURO)
│   ├── Canal Comercial
│   ├── Clientes comerciais
│   └── Mensagens comerciais
│
├── pixel12digital_suporte (FUTURO)
│   ├── Canal Suporte
│   ├── Tickets de suporte
│   └── Mensagens de suporte
│
└── pixel12digital_vendas (FUTURO)
    ├── Canal Vendas
    ├── Leads de vendas
    └── Mensagens de vendas
```

### 🔐 **Credenciais de Banco**
```php
// Configurações padrão
define('DB_HOST', 'localhost');
define('DB_USER', 'pixel12digital');
define('DB_PASS', 'SUA_SENHA_AQUI'); // Alterar para cada banco
define('DB_NAME', 'pixel12digital'); // Alterar para cada canal
```

## 🔧 **Configurações de Webhook**

### 📡 **URLs dos Webhooks**
```
🌐 WEBHOOKS ATIVOS
├── Canal Financeiro: https://app.pixel12digital.com.br/api/webhook_whatsapp.php
├── Canal Comercial: https://app.pixel12digital.com.br/api/webhook_canal_37.php (FUTURO)
├── Canal Suporte: https://app.pixel12digital.com.br/api/webhook_canal_38.php (FUTURO)
└── Canal Vendas: https://app.pixel12digital.com.br/api/webhook_canal_39.php (FUTURO)
```

### ⚙️ **Configurações do WhatsApp API**
```javascript
// Configuração na VPS (porta 3000)
{
  "webhooks": {
    "financeiro": "https://app.pixel12digital.com.br/api/webhook_whatsapp.php",
    "comercial": "https://app.pixel12digital.com.br/api/webhook_canal_37.php",
    "suporte": "https://app.pixel12digital.com.br/api/webhook_canal_38.php",
    "vendas": "https://app.pixel12digital.com.br/api/webhook_canal_39.php"
  }
}
```

## 🛠️ **Comandos de Manutenção**

### 🔄 **Gerenciamento de Serviços**
```bash
# WhatsApp API
pm2 status                    # Verificar status
pm2 restart whatsapp-api      # Reiniciar API
pm2 logs whatsapp-api         # Ver logs
pm2 stop whatsapp-api         # Parar API
pm2 start whatsapp-api        # Iniciar API

# Nginx
sudo systemctl status nginx   # Verificar status
sudo systemctl restart nginx  # Reiniciar Nginx
sudo nginx -t                 # Testar configuração

# MySQL
sudo systemctl status mysql   # Verificar status
sudo systemctl restart mysql  # Reiniciar MySQL
mysql -u pixel12digital -p    # Acessar MySQL
```

### 📊 **Monitoramento**
```bash
# Verificar uso de recursos
htop                         # Monitor de processos
df -h                        # Espaço em disco
free -h                      # Uso de memória
netstat -tulpn               # Portas ativas

# Verificar logs
tail -f /var/log/nginx/access.log    # Logs do Nginx
tail -f /var/log/whatsapp-api.log    # Logs da API
tail -f /var/www/html/loja-virtual-revenda/logs/webhook_whatsapp_$(date +%Y-%m-%d).log
```

### 🔄 **Backup e Restauração**
```bash
# Backup do banco principal
mysqldump -u pixel12digital -p pixel12digital > backup_principal_$(date +%Y%m%d).sql

# Backup de logs
tar -czf logs_backup_$(date +%Y%m%d).tar.gz /var/www/html/loja-virtual-revenda/logs/

# Backup de configurações
tar -czf configs_backup_$(date +%Y%m%d).tar.gz /var/www/html/loja-virtual-revenda/canais/
```

## 🚨 **Procedimentos de Emergência**

### ⚡ **Canal Não Responde**
1. **Verificar status da API**
   ```bash
   curl -s "http://212.85.11.238:3000/status"
   ```

2. **Verificar logs do canal**
   ```bash
   tail -f /var/www/html/loja-virtual-revenda/logs/webhook_whatsapp_$(date +%Y-%m-%d).log
   ```

3. **Reiniciar WhatsApp API**
   ```bash
   pm2 restart whatsapp-api
   ```

4. **Verificar webhook**
   ```bash
   curl -X POST "https://app.pixel12digital.com.br/api/webhook_whatsapp.php" \
        -H "Content-Type: application/json" \
        -d '{"test": "ping"}'
   ```

### 🔥 **VPS Inacessível**
1. **Acessar via SSH**
   ```bash
   ssh root@212.85.11.238
   ```

2. **Verificar serviços críticos**
   ```bash
   systemctl status nginx mysql
   pm2 status
   ```

3. **Reiniciar serviços**
   ```bash
   systemctl restart nginx mysql
   pm2 restart all
   ```

### 💾 **Problemas de Banco**
1. **Verificar conexão**
   ```bash
   mysql -u pixel12digital -p -e "SELECT 1;"
   ```

2. **Verificar espaço em disco**
   ```bash
   df -h
   ```

3. **Restaurar backup se necessário**
   ```bash
   mysql -u pixel12digital -p pixel12digital < backup_principal_YYYYMMDD.sql
   ```

## 📈 **Métricas e Monitoramento**

### 📊 **Métricas de Performance**
- **Uptime**: 99.9%
- **Latência**: <100ms
- **Taxa de erro**: <0.1%
- **Mensagens/dia**: ~500-1000
- **Canais ativos**: 1 (expandindo para 4)

### 📈 **Recursos da VPS**
- **CPU**: 2 cores
- **RAM**: 4GB
- **Disco**: 80GB SSD
- **Banda**: 1Gbps
- **Backup**: Diário automático

## 🔐 **Segurança**

### 🛡️ **Medidas de Segurança**
- ✅ **Firewall ativo**
- ✅ **SSL/TLS configurado**
- ✅ **Backup automático**
- ✅ **Logs de acesso**
- ✅ **Monitoramento 24/7**

### 🔑 **Acessos**
- **SSH**: Porta 22 (chave pública)
- **HTTP**: Porta 80 (redireciona para HTTPS)
- **HTTPS**: Porta 443
- **WhatsApp API**: Porta 3000 (interno)

## 📞 **Contatos de Suporte**

### 🛠️ **Desenvolvimento**
- **Responsável**: Pixel12Digital
- **Email**: suporte@pixel12digital.com.br
- **WhatsApp**: 47 997309525
- **Telegram**: @pixel12digital

### 🚨 **Emergências**
- **VPS**: 212.85.11.238
- **Hostinger**: Suporte 24/7
- **Backup**: Automático + manual

## 📚 **Documentação Relacionada**

### 📄 **Documentos Principais**
- 📄 [README Principal](../README.md)
- 📄 [Guia de Criação de Canais](GUIA_CRIACAO_CANAIS.md)
- 📄 [Manutenção de Canais](MANUTENCAO_CANAIS.md)
- 📄 [Caminhos da VPS](CAMINHOS_VPS.md)

### 📄 **Documentação por Canal**
- 📄 [Canal Financeiro](../canais/financeiro/README.md)
- 📄 [Template para Novos Canais](../canais/template/README.md)

## 🔄 **Atualizações**

### 📅 **Histórico de Versões**
- **v1.0.0** (2025-07-31): Estrutura inicial
- **v1.1.0** (2025-07-31): Módulo multi-canal
- **v1.2.0** (2025-07-31): Documentação completa

### 🔮 **Próximas Atualizações**
- [ ] Dashboard de monitoramento
- [ ] Alertas automáticos
- [ ] Backup em nuvem
- [ ] Escalabilidade automática

---

**Última atualização**: 31/07/2025  
**Versão**: 1.2.0  
**Responsável**: Pixel12Digital  
**VPS**: 212.85.11.238 