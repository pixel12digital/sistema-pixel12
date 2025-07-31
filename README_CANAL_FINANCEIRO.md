# 📱 WhatsApp API - Canal Financeiro - Documentação Completa

## 🎯 Resumo do Projeto

Este documento descreve a correção do canal financeiro do WhatsApp e como adicionar novos canais usando diferentes portas (3000, 3001, 3002, 3003, etc.).

## ✅ Problemas Identificados e Corrigidos

### 1. **Endpoint `/send` Ausente**
- **Problema**: O endpoint `/send` não estava implementado no servidor WhatsApp API
- **Solução**: Adicionado o endpoint `app.post('/send', ...)` no arquivo `whatsapp-api-server.js`
- **Localização**: VPS em `212.85.11.238:3000`

### 2. **URL Problemática de Webhook**
- **Problema**: URL hardcoded `agentes.pixel12digital.com.br:8443` causando erros de timeout
- **Solução**: Comentada a linha 22 no arquivo `whatsapp-api-server.js`
- **Comando usado**: `sed -i '22 s/^/\/\/ /' /var/whatsapp-api/whatsapp-api-server.js`

### 3. **Formato Incorreto de Número**
- **Problema**: Números sem `@c.us` causavam erros na API
- **Solução**: Descoberto que o WhatsApp exige o formato `@c.us` no final
- **Formatos corretos**: 
  - `4796164699@c.us` (sem código do país)
  - `554796164699@c.us` (com código do país)

## 🔧 Arquivos Criados Durante o Diagnóstico

### Scripts de Teste
- `teste_canal_financeiro_local.php` - Teste local do canal
- `teste_canal_financeiro_vps.php` - Teste direto na VPS
- `verificar_endpoints_vps.php` - Verificação de todos os endpoints
- `teste_final_canal_financeiro.php` - Teste final completo
- `teste_formatos_numero.php` - Teste de formatos de número
- `teste_envio_4796164699_corrigido.php` - Teste específico com formato correto

### Scripts de Correção
- `executar_correcao_vps.php` - Comandos para correção na VPS
- `corrigir_canal_financeiro_vps.sh` - Script automatizado para VPS
- `comandos_vps_manual.txt` - Comandos manuais para SSH

### Documentação
- `RELATORIO_CANAL_FINANCEIRO.md` - Relatório completo do processo
- `README_CANAL_FINANCEIRO.md` - Este arquivo

## 🚀 Como Adicionar Novos Canais (Portas 3001, 3002, 3003, etc.)

### 1. **Preparação na VPS**

```bash
# Conectar via SSH na VPS
ssh root@212.85.11.238

# Criar diretórios para novos canais
mkdir -p /var/whatsapp-api-canal-3001
mkdir -p /var/whatsapp-api-canal-3002
mkdir -p /var/whatsapp-api-canal-3003
```

### 2. **Configurar Novo Canal (Exemplo: Porta 3001)**

```bash
# Copiar arquivos do canal existente
cp -r /var/whatsapp-api/* /var/whatsapp-api-canal-3001/

# Editar configuração da porta
cd /var/whatsapp-api-canal-3001
sed -i 's/const PORT = 3000/const PORT = 3001/' whatsapp-api-server.js

# Criar arquivo de configuração PM2
cat > ecosystem.config.js << 'EOF'
module.exports = {
  apps: [{
    name: 'whatsapp-api-3001',
    script: 'whatsapp-api-server.js',
    cwd: '/var/whatsapp-api-canal-3001',
    instances: 1,
    autorestart: true,
    watch: false,
    max_memory_restart: '1G',
    env: {
      NODE_ENV: 'production',
      PORT: 3001
    }
  }]
}
EOF
```

### 3. **Iniciar Novo Canal**

```bash
# Iniciar com PM2
pm2 start ecosystem.config.js

# Verificar status
pm2 list
pm2 logs whatsapp-api-3001 --lines 5
```

### 4. **Configurar Firewall (se necessário)**

```bash
# Abrir nova porta no firewall
ufw allow 3001
ufw allow 3002
ufw allow 3003

# Verificar portas abertas
netstat -tlnp | grep :300
```

### 5. **Testar Novo Canal**

```bash
# Testar conectividade
curl http://212.85.11.238:3001/status

# Testar envio de mensagem
curl -X POST http://212.85.11.238:3001/send \
  -H 'Content-Type: application/json' \
  -d '{"to":"4796164699@c.us","message":"Teste canal 3001"}'
```

## 📋 Configuração no Sistema PHP

### 1. **Atualizar `config.php`**

```php
// Adicionar novas constantes para novos canais
define('WHATSAPP_ROBOT_URL_3000', 'http://212.85.11.238:3000');
define('WHATSAPP_ROBOT_URL_3001', 'http://212.85.11.238:3001');
define('WHATSAPP_ROBOT_URL_3002', 'http://212.85.11.238:3002');
define('WHATSAPP_ROBOT_URL_3003', 'http://212.85.11.238:3003');
```

### 2. **Atualizar `ajax_whatsapp.php`**

```php
// Adicionar mapeamento de portas
$porta_urls = [
    '3000' => 'http://212.85.11.238:3000',
    '3001' => 'http://212.85.11.238:3001',
    '3002' => 'http://212.85.11.238:3002',
    '3003' => 'http://212.85.11.238:3003'
];

// Usar a porta correta
$porta = $_POST['porta'] ?? '3000';
$vps_url = $porta_urls[$porta] ?? $porta_urls['3000'];
```

### 3. **Adicionar Canais no Banco de Dados**

```sql
-- Inserir novos canais na tabela canais_comunicacao
INSERT INTO canais_comunicacao (nome, tipo, porta, status, data_criacao) VALUES
('Canal Financeiro', 'whatsapp', 3000, 1, NOW()),
('Canal Comercial', 'whatsapp', 3001, 1, NOW()),
('Canal Suporte', 'whatsapp', 3002, 1, NOW()),
('Canal Marketing', 'whatsapp', 3003, 1, NOW());
```

## 🔍 Scripts de Teste para Novos Canais

### Teste de Conectividade

```php
<?php
// teste_canal_3001.php
$vps_url = "http://212.85.11.238:3001";

echo "🔍 Testando Canal 3001...\n";

// Testar status
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $vps_url . "/status");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "Status: HTTP $http_code\n";
echo "Resposta: $response\n";

// Testar envio
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $vps_url . "/send");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
    'to' => '4796164699@c.us',
    'message' => 'Teste canal 3001 - ' . date('H:i:s')
]));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "Envio: HTTP $http_code\n";
echo "Resposta: $response\n";
?>
```

## 🛠️ Comandos Úteis para Gerenciamento

### PM2 - Gerenciamento de Processos

```bash
# Listar todos os processos
pm2 list

# Reiniciar canal específico
pm2 restart whatsapp-api-3001

# Parar canal específico
pm2 stop whatsapp-api-3001

# Ver logs de canal específico
pm2 logs whatsapp-api-3001 --lines 20

# Monitorar todos os canais
pm2 monit
```

### Verificação de Status

```bash
# Verificar portas em uso
netstat -tlnp | grep :300

# Verificar processos Node.js
ps aux | grep node

# Verificar uso de memória
pm2 show whatsapp-api-3001
```

## 📊 Monitoramento e Logs

### Logs Importantes

```bash
# Logs de erro
tail -f /root/.pm2/logs/whatsapp-api-3001-error.log

# Logs de saída
tail -f /root/.pm2/logs/whatsapp-api-3001-out.log

# Logs em tempo real
pm2 logs whatsapp-api-3001 --lines 0
```

### Métricas de Performance

```bash
# Ver métricas do PM2
pm2 show whatsapp-api-3001

# Monitorar CPU e memória
pm2 monit
```

## 🔒 Segurança e Backup

### Backup Automático

```bash
# Criar script de backup
cat > /root/backup_canais.sh << 'EOF'
#!/bin/bash
DATA=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/backup/canais_whatsapp"

mkdir -p $BACKUP_DIR

# Backup dos arquivos
tar -czf $BACKUP_DIR/canal_3000_$DATA.tar.gz /var/whatsapp-api/
tar -czf $BACKUP_DIR/canal_3001_$DATA.tar.gz /var/whatsapp-api-canal-3001/
tar -czf $BACKUP_DIR/canal_3002_$DATA.tar.gz /var/whatsapp-api-canal-3002/
tar -czf $BACKUP_DIR/canal_3003_$DATA.tar.gz /var/whatsapp-api-canal-3003/

# Backup do PM2
pm2 save

echo "Backup concluído: $DATA"
EOF

chmod +x /root/backup_canais.sh
```

### Firewall e Segurança

```bash
# Configurar firewall
ufw allow 3000
ufw allow 3001
ufw allow 3002
ufw allow 3003

# Verificar status
ufw status
```

## 🚨 Troubleshooting

### Problemas Comuns

1. **Canal não inicia**
   ```bash
   pm2 logs whatsapp-api-3001 --lines 20
   # Verificar se a porta está em uso
   netstat -tlnp | grep :3001
   ```

2. **Mensagens não são enviadas**
   ```bash
   # Verificar formato do número (@c.us)
   # Verificar logs do canal
   pm2 logs whatsapp-api-3001 --lines 10
   ```

3. **Erro de conectividade**
   ```bash
   # Testar conectividade
   curl http://212.85.11.238:3001/status
   # Verificar firewall
   ufw status
   ```

### Comandos de Diagnóstico

```bash
# Diagnóstico completo
echo "=== DIAGNÓSTICO CANAL 3001 ==="
echo "Status PM2:"
pm2 show whatsapp-api-3001
echo "Porta em uso:"
netstat -tlnp | grep :3001
echo "Teste de conectividade:"
curl -s http://212.85.11.238:3001/status
echo "Logs recentes:"
pm2 logs whatsapp-api-3001 --lines 5
```

## 📝 Checklist para Novo Canal

- [ ] Criar diretório na VPS
- [ ] Copiar arquivos do canal base
- [ ] Alterar porta no arquivo de configuração
- [ ] Criar arquivo ecosystem.config.js
- [ ] Iniciar com PM2
- [ ] Configurar firewall
- [ ] Testar conectividade
- [ ] Testar envio de mensagem
- [ ] Atualizar configuração PHP
- [ ] Adicionar no banco de dados
- [ ] Documentar nova porta

## 🎯 Status Atual

- ✅ **Canal Financeiro (3000)**: Funcionando
- ✅ **Endpoint /send**: Implementado
- ✅ **Formato de número**: Corrigido (@c.us)
- ✅ **URL problemática**: Removida
- 🔄 **Novos canais**: Prontos para implementação

---

**Última atualização**: 31/07/2025  
**Versão**: 1.0  
**Status**: Canal Financeiro Operacional 