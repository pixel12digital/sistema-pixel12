# 🔄 Configuração da Integração com Asaas

## 📋 Visão Geral

Este sistema implementa uma integração completa com o Asaas para gerenciamento de clientes, cobranças e assinaturas. O fluxo funciona da seguinte forma:

1. **Criação**: Clientes e cobranças são criados no sistema e automaticamente sincronizados com o Asaas
2. **Webhook**: Notificações de pagamento são recebidas via webhook e atualizam o banco local
3. **Sincronização**: Script diário mantém os dados sincronizados entre o sistema e o Asaas

## 🚀 Configuração Inicial

### 1. Executar Verificação do Banco de Dados

```bash
php fix_database_structure.php
```

Este script irá:
- Verificar se todas as tabelas necessárias existem
- Criar tabelas faltantes com a estrutura correta
- Verificar integridade dos dados existentes

### 2. Configurar Webhook no Asaas

1. Acesse o painel do Asaas
2. Vá em **Configurações > Webhooks**
3. Adicione um novo webhook com as seguintes configurações:

```
URL: https://seudominio.com/api/webhooks.php
Eventos: Todos os eventos de pagamento e assinatura
```

**Eventos importantes:**
- `PAYMENT_RECEIVED` - Pagamento recebido
- `PAYMENT_CONFIRMED` - Pagamento confirmado
- `PAYMENT_OVERDUE` - Pagamento vencido
- `SUBSCRIPTION_CREATED` - Assinatura criada
- `SUBSCRIPTION_PAYMENT_RECEIVED` - Pagamento de assinatura recebido

### 3. Testar o Webhook

```bash
php test_webhook.php
```

Este script simula um webhook do Asaas e verifica se:
- O webhook está funcionando
- Os dados estão sendo salvos no banco
- A estrutura está correta

## 📊 Estrutura do Banco de Dados

### Tabela `clientes`
```sql
CREATE TABLE clientes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    asaas_id VARCHAR(64) NOT NULL UNIQUE,
    nome VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    telefone VARCHAR(50),
    celular VARCHAR(20),
    cpf_cnpj VARCHAR(32),
    -- Endereço
    cep VARCHAR(10),
    rua VARCHAR(255),
    numero VARCHAR(10),
    complemento VARCHAR(50),
    bairro VARCHAR(100),
    cidade VARCHAR(100),
    estado VARCHAR(2),
    pais VARCHAR(50) DEFAULT 'Brasil',
    -- Outros campos
    notificacao_desativada TINYINT(1) DEFAULT 0,
    emails_adicionais VARCHAR(255),
    referencia_externa VARCHAR(100),
    observacoes TEXT,
    razao_social VARCHAR(255),
    criado_em_asaas DATETIME,
    data_criacao DATETIME DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

### Tabela `cobrancas`
```sql
CREATE TABLE cobrancas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    asaas_payment_id VARCHAR(64) NOT NULL UNIQUE,
    cliente_id INT,
    valor DECIMAL(10,2) NOT NULL,
    status VARCHAR(50) NOT NULL DEFAULT 'PENDING',
    vencimento DATE NOT NULL,
    data_pagamento DATE,
    data_criacao DATETIME DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    descricao VARCHAR(255),
    tipo VARCHAR(50) DEFAULT 'BOLETO',
    tipo_pagamento VARCHAR(20),
    url_fatura VARCHAR(255),
    parcela VARCHAR(32),
    assinatura_id VARCHAR(64),
    FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE SET NULL
);
```

### Tabela `assinaturas`
```sql
CREATE TABLE assinaturas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cliente_id INT NOT NULL,
    asaas_id VARCHAR(255) NOT NULL UNIQUE,
    status VARCHAR(50) NOT NULL DEFAULT 'ACTIVE',
    periodicidade VARCHAR(20) NOT NULL,
    start_date DATE,
    next_due_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE CASCADE
);
```

## 🔧 Serviços e Controladores

### AsaasIntegrationService
Localização: `src/Services/AsaasIntegrationService.php`

**Métodos principais:**
- `criarCliente($dados)` - Cria cliente no Asaas e no banco local
- `criarCobranca($dados)` - Cria cobrança no Asaas e no banco local
- `criarAssinatura($dados)` - Cria assinatura no Asaas e no banco local

### ClienteController
Localização: `painel/cliente_controller.php`

**Métodos principais:**
- `listarClientes($filtro, $pagina, $limite)` - Lista clientes com paginação
- `criarCliente($dados)` - Cria novo cliente
- `atualizarCliente($id, $dados)` - Atualiza dados do cliente
- `buscarCobrancasCliente($cliente_id)` - Busca cobranças do cliente

### CobrancaController
Localização: `painel/cobranca_controller.php`

**Métodos principais:**
- `listarCobrancas($filtro, $status, $pagina)` - Lista cobranças com filtros
- `criarCobranca($dados)` - Cria nova cobrança
- `cancelarCobranca($id)` - Cancela cobrança
- `reenviarLink($id)` - Reenvia link de pagamento
- `getEstatisticas()` - Obtém estatísticas das cobranças

## 📝 Webhook

### Endpoint
`/api/webhooks.php`

### Eventos Suportados
- **Pagamentos**: `PAYMENT_RECEIVED`, `PAYMENT_CONFIRMED`, `PAYMENT_OVERDUE`, etc.
- **Assinaturas**: `SUBSCRIPTION_CREATED`, `SUBSCRIPTION_PAYMENT_RECEIVED`, etc.

### Processamento
1. Recebe evento do Asaas
2. Valida dados recebidos
3. Atualiza banco local
4. Registra log para auditoria
5. Retorna resposta de sucesso

### Logs
Os logs são salvos em: `logs/webhook_YYYY-MM-DD.log`

## 🔄 Sincronização

### Script de Sincronização
Localização: `painel/sincroniza_asaas.php`

**Funcionalidades:**
- Sincroniza clientes do Asaas para o banco local
- Sincroniza cobranças do Asaas para o banco local
- Sincroniza assinaturas do Asaas para o banco local
- Registra data/hora da última sincronização

### Agendamento
Para manter os dados sempre atualizados, agende a execução diária:

**Linux/Hostinger (Cron):**
```bash
# Executar diariamente às 2h da manhã
0 2 * * * php /caminho/para/painel/sincroniza_asaas.php
```

**Windows (Agendador de Tarefas):**
- Abra o Agendador de Tarefas
- Crie uma nova tarefa
- Configure para executar diariamente: `php C:\xampp\htdocs\loja-virtual-revenda\painel\sincroniza_asaas.php`

## 🎯 Fluxo de Trabalho

### 1. Criar Cliente
```php
$controller = new ClienteController();
$resultado = $controller->criarCliente([
    'nome' => 'João Silva',
    'email' => 'joao@email.com',
    'cpf_cnpj' => '12345678901',
    'telefone' => '(11) 99999-9999'
]);
```

### 2. Criar Cobrança
```php
$controller = new CobrancaController();
$resultado = $controller->criarCobranca([
    'cliente_id' => 1,
    'valor' => 100.00,
    'vencimento' => '2024-01-15',
    'descricao' => 'Mensalidade Janeiro'
]);
```

### 3. Receber Pagamento (via Webhook)
Quando o cliente paga, o Asaas envia um webhook que:
1. Atualiza o status da cobrança para `RECEIVED`
2. Registra a data de pagamento
3. Atualiza a tabela de faturas
4. Registra log para auditoria

## 🔍 Monitoramento

### Logs de Webhook
Verifique os logs em: `logs/webhook_*.log`

### Última Sincronização
Arquivo: `painel/ultima_sincronizacao.log`

### Estatísticas
Use o método `getEstatisticas()` do `CobrancaController` para obter:
- Total de cobranças
- Cobranças por status
- Valor total recebido
- Valor total pendente

## ⚠️ Troubleshooting

### Webhook não está funcionando
1. Verifique se a URL está correta no painel do Asaas
2. Teste com: `php test_webhook.php`
3. Verifique os logs em `logs/webhook_*.log`
4. Confirme se o servidor está acessível

### Sincronização falhando
1. Verifique as credenciais da API no `config.php`
2. Confirme se a API do Asaas está funcionando
3. Verifique os logs de erro do PHP
4. Teste a conexão com o banco de dados

### Dados não sincronizados
1. Execute manualmente: `php painel/sincroniza_asaas.php`
2. Verifique se há erros na execução
3. Confirme se as tabelas existem e têm a estrutura correta
4. Verifique se há dados no Asaas para sincronizar

## 📞 Suporte

Para dúvidas ou problemas:
1. Verifique os logs primeiro
2. Execute os scripts de teste
3. Consulte esta documentação
4. Entre em contato com o suporte técnico

---

**Última atualização**: Janeiro 2024
**Versão**: 1.0 