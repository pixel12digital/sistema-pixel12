# 📋 TEMPLATE - NOVO CANAL

## 🎯 **Como Usar Este Template**

Este template serve como base para criar novos canais no sistema multi-canal da Pixel12Digital.

## 📁 **Estrutura do Template**

```
📁 canais/template/
├── 📄 canal_config.php     # Template de configuração
├── 📄 webhook.php          # Template de webhook (futuro)
└── 📄 README.md           # Esta documentação
```

## 🚀 **Passos para Criar Novo Canal**

### 1️⃣ **Criar Pasta do Canal**
```bash
# Na pasta canais/
mkdir comercial
cd comercial
```

### 2️⃣ **Copiar Arquivos do Template**
```bash
# Copiar configuração
cp ../template/canal_config.php canal_config.php

# Copiar webhook (quando disponível)
cp ../template/webhook.php webhook.php

# Copiar README
cp ../template/README.md README.md
```

### 3️⃣ **Configurar canal_config.php**

#### **Configurações Básicas**
```php
define('CANAL_ID', 3001);                    // Incrementar para cada canal
define('CANAL_NUMERO', 37);                  // Incrementar para cada canal
define('CANAL_NOME', 'Comercial');           // Nome do novo canal
define('CANAL_TIPO', 'whatsapp');            // Tipo de canal
define('CANAL_DESCRICAO', 'Canal para atendimento comercial');
```

#### **Configurações do WhatsApp**
```php
define('CANAL_WHATSAPP_NUMERO', '47999999999');  // Número do WhatsApp
define('CANAL_WHATSAPP_COMPLETO', '5547999999999@c.us'); // Formato API
```

#### **Configurações de Banco**
```php
define('CANAL_USAR_BANCO_PRINCIPAL', false); // false para bancos separados
define('CANAL_BANCO_NOME', 'pixel12digital_comercial'); // Nome do banco
define('CANAL_BANCO_HOST', 'localhost');     // Host do banco
define('CANAL_BANCO_USER', 'pixel12digital'); // Usuário
define('CANAL_BANCO_PASS', 'SUA_SENHA_AQUI'); // Senha
```

#### **Configurações de Mensagens**
```php
define('CANAL_CONTATO_DIRETO', '47 999999999'); // Contato direto
define('CANAL_PALAVRA_CHAVE_PRINCIPAL', 'ajuda'); // Palavra-chave
```

### 4️⃣ **Criar Banco de Dados**

#### **Na Hostinger**
1. Acessar painel da Hostinger
2. Criar novo banco de dados
3. Nome: `pixel12digital_[nome_canal]`
4. Usuário: `pixel12digital`
5. Senha: Gerar senha segura

#### **Estrutura do Banco**
```sql
-- Tabelas básicas para novo canal
CREATE TABLE clientes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255),
    contact_name VARCHAR(255),
    celular VARCHAR(20),
    telefone VARCHAR(20),
    cpf_cnpj VARCHAR(20),
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE mensagens_comunicacao (
    id INT AUTO_INCREMENT PRIMARY KEY,
    canal_id INT,
    cliente_id INT,
    mensagem TEXT,
    tipo VARCHAR(50),
    data_hora TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    direcao ENUM('recebido', 'enviado'),
    status VARCHAR(50),
    numero_whatsapp VARCHAR(50)
);

CREATE TABLE canais_comunicacao (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tipo VARCHAR(50),
    identificador VARCHAR(100),
    nome_exibicao VARCHAR(255),
    status VARCHAR(50),
    data_conexao TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### 5️⃣ **Configurar Webhook**

#### **Criar webhook específico**
```php
<?php
// /api/webhook_canal_37.php
require_once __DIR__ . '/../canais/comercial/canal_config.php';

// Lógica específica do canal comercial
// ...
?>
```

#### **Configurar WhatsApp API**
1. Acessar WhatsApp API na VPS
2. Configurar webhook para novo canal
3. Testar conectividade

### 6️⃣ **Testar Canal**

#### **Criar script de teste**
```php
<?php
// teste_canal_comercial.php
$numero = "5547999999999@c.us";
$texto = "teste canal comercial";

$dados = [
    "event" => "onmessage",
    "data" => [
        "from" => $numero,
        "text" => $texto,
        "type" => "text",
        "timestamp" => time()
    ]
];

// Fazer requisição para webhook do canal
$ch = curl_init("https://app.pixel12digital.com.br/api/webhook_canal_37.php");
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($dados));
curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
curl_close($ch);

echo "Resposta: $response\n";
?>
```

## 📋 **Checklist de Criação**

### ✅ **Configuração**
- [ ] Pasta do canal criada
- [ ] Arquivos do template copiados
- [ ] canal_config.php configurado
- [ ] README.md personalizado

### ✅ **Banco de Dados**
- [ ] Banco criado na Hostinger
- [ ] Tabelas básicas criadas
- [ ] Conexão testada
- [ ] Backup configurado

### ✅ **Webhook**
- [ ] Webhook específico criado
- [ ] WhatsApp API configurado
- [ ] URL de webhook testada
- [ ] Logs funcionando

### ✅ **Testes**
- [ ] Script de teste criado
- [ ] Mensagem de teste enviada
- [ ] Resposta automática funcionando
- [ ] Logs verificados

### ✅ **Documentação**
- [ ] README do canal atualizado
- [ ] Documentação VPS atualizada
- [ ] Contatos de suporte definidos
- [ ] Procedimentos de manutenção documentados

## 🔧 **Configurações Avançadas**

### **Automação Personalizada**
```php
// Definir lógica específica do canal
define('CANAL_AUTOMACAO_PERSONALIZADA', true);
define('CANAL_PALAVRAS_CHAVE', ['ajuda', 'suporte', 'vendas']);
define('CANAL_RESPOSTAS_PERSONALIZADAS', [
    'ajuda' => 'Como posso ajudá-lo?',
    'suporte' => 'Direcionando para suporte...',
    'vendas' => 'Falando com vendas...'
]);
```

### **Integrações**
```php
// Configurar integrações específicas
define('CANAL_INTEGRACAO_CRM', true);
define('CANAL_INTEGRACAO_EMAIL', true);
define('CANAL_INTEGRACAO_API', true);
```

## 🚨 **Troubleshooting**

### **Problemas Comuns**

#### **Canal não responde**
1. Verificar se webhook está ativo
2. Verificar configurações em canal_config.php
3. Verificar logs do canal
4. Testar conectividade com WhatsApp API

#### **Erro de banco de dados**
1. Verificar credenciais do banco
2. Verificar se banco existe
3. Verificar se tabelas foram criadas
4. Testar conexão manualmente

#### **Mensagens não chegam**
1. Verificar configuração do WhatsApp API
2. Verificar URL do webhook
3. Verificar se número está ativo
4. Verificar logs da API

## 📞 **Suporte**

### **Contatos**
- **Desenvolvimento**: Pixel12Digital
- **Email**: suporte@pixel12digital.com.br
- **WhatsApp**: 47 997309525

### **Documentação Relacionada**
- 📄 [README Principal](../../README.md)
- 📄 [Documentação VPS](../../docs/README_VPS_MULTI_CANAL.md)
- 📄 [Guia de Criação de Canais](../../docs/GUIA_CRIACAO_CANAIS.md)
- 📄 [Manutenção de Canais](../../docs/MANUTENCAO_CANAIS.md)

---

**Última atualização**: 31/07/2025  
**Versão**: 1.0.0  
**Responsável**: Pixel12Digital 