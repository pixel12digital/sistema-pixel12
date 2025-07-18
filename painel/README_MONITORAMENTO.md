# 🤖 Sistema de Monitoramento Automático de Clientes - Pixel12 Digital

## 📋 Visão Geral

Este sistema implementa um robô de WhatsApp financeiro que monitora automaticamente clientes, envia mensagens de cobrança vencida e responde automaticamente às consultas dos clientes.

## ✨ Funcionalidades

### 🔍 **Validação de Clientes**
- Botão "Validar" na página de faturas
- Envia mensagem de apresentação: *"Olá! Este é nosso contato financeiro da Pixel12 Digital..."*
- Checkbox para ativar/desativar monitoramento

### 📊 **Monitoramento Automático**
- Verifica cobranças vencidas a cada 30 minutos
- Envia mensagens automáticas para clientes monitorados
- Agrupa múltiplas faturas em uma única mensagem
- Evita spam (máximo 1 mensagem por dia por cliente)

### 💬 **Respostas Automáticas**
- **"faturas" ou "consulta"** → Lista todas as faturas do cliente
- **"pagar" ou "pagamento"** → Envia links de pagamento
- **"atendente"** → Oferece transferência para humano
- Respostas padrão para outras mensagens

## 🛠️ Instalação

### 1. **Criar Tabela no Banco**
Execute o script SQL:
```sql
-- Executar: painel/sql/criar_tabela_monitoramento.sql
```

### 2. **Configurar Cron Job**
Adicione ao crontab:
```bash
# Verificar cobranças vencidas a cada 30 minutos
0,30 * * * * php /caminho/para/painel/cron/monitoramento_automatico.php
```

### 3. **Verificar Permissões**
```bash
# Criar diretório de logs
mkdir -p painel/logs
chmod 755 painel/logs
```

## 📁 Estrutura de Arquivos

```
painel/
├── assets/
│   └── faturas_monitoramento.js    # Sistema JavaScript
├── api/
│   ├── enviar_mensagem_validacao.php
│   ├── salvar_monitoramento_cliente.php
│   ├── listar_clientes_monitorados.php
│   ├── verificar_cobrancas_vencidas.php
│   ├── enviar_mensagem_automatica.php
│   ├── buscar_faturas_cliente.php
│   └── buscar_faturas_pendentes.php
├── cron/
│   └── monitoramento_automatico.php
├── sql/
│   └── criar_tabela_monitoramento.sql
└── logs/
    ├── monitoramento_clientes.log
    └── monitoramento_automatico.log
```

## 🚀 Como Usar

### **1. Validar Cliente**
1. Acesse a página de **Faturas**
2. Na coluna **Monitoramento**, clique em **"Validar"**
3. Sistema envia mensagem de apresentação
4. Marque o checkbox **"Monitorar"** para ativar monitoramento automático

### **2. Monitoramento Automático**
- Sistema verifica cobranças vencidas a cada 30 minutos
- Envia mensagens apenas para clientes com checkbox marcado
- Evita envio duplicado (máximo 1 por dia)

### **3. Respostas Automáticas**
O robô responde automaticamente quando cliente envia:
- **"faturas"** → Lista todas as faturas
- **"pagar"** → Envia links de pagamento
- **"atendente"** → Oferece transferência

## 🔧 Configuração

### **Mensagem de Validação**
Edite em `painel/assets/faturas_monitoramento.js`:
```javascript
this.mensagemValidacao = "Olá! Este é nosso contato financeiro da Pixel12 Digital...";
```

### **Frequência de Verificação**
Edite em `painel/assets/faturas_monitoramento.js`:
```javascript
// Verificar a cada 30 minutos
setInterval(() => {
    this.verificarCobrancasVencidas();
}, 30 * 60 * 1000);
```

### **VPS WhatsApp**
Configure a URL da VPS em todos os arquivos PHP:
```php
$ch = curl_init("http://212.85.11.238:3000/send");
```

## 📊 Logs e Monitoramento

### **Logs Disponíveis**
- `painel/logs/monitoramento_clientes.log` - Ações de monitoramento
- `painel/logs/monitoramento_automatico.log` - Execuções do cron
- `painel/log_envio_robo.txt` - Log geral de envios

### **Verificar Status**
```bash
# Ver logs em tempo real
tail -f painel/logs/monitoramento_automatico.log

# Verificar clientes monitorados
php painel/api/listar_clientes_monitorados.php
```

## 🔒 Segurança

### **Validações Implementadas**
- Verificação de cliente existente
- Validação de número de celular
- Controle de frequência de envio
- Logs detalhados de todas as ações

### **Proteções**
- Máximo 1 mensagem por dia por cliente
- Pausa de 2 segundos entre envios
- Validação de status do canal WhatsApp
- Tratamento de erros robusto

## 🐛 Troubleshooting

### **Problema: Mensagens não são enviadas**
1. Verificar se VPS está online: `http://212.85.11.238:3000/status`
2. Verificar logs: `tail -f painel/logs/monitoramento_automatico.log`
3. Verificar se canal financeiro está conectado

### **Problema: Cliente não recebe respostas**
1. Verificar se checkbox "Monitorar" está marcado
2. Verificar se cliente tem celular cadastrado
3. Verificar logs de processamento de mensagens

### **Problema: Cron não executa**
1. Verificar permissões do arquivo
2. Verificar sintaxe do crontab
3. Testar execução manual: `php painel/cron/monitoramento_automatico.php`

## 📞 Suporte

Para dúvidas ou problemas:
- Verificar logs em `painel/logs/`
- Consultar documentação da API WhatsApp
- Verificar status da VPS em `http://212.85.11.238:3000/status`

---

**Desenvolvido para Pixel12 Digital** 🚀 