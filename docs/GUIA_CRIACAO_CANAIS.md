# 🚀 GUIA COMPLETO - CRIAÇÃO DE NOVOS CANAIS

## 🎯 **Visão Geral**
Este guia fornece instruções passo a passo para criar novos canais WhatsApp no sistema multi-canal da Pixel12Digital.

## 📋 **Pré-requisitos**

### ✅ **Antes de Começar**
- [ ] Número de WhatsApp disponível
- [ ] Acesso à VPS (212.85.11.238)
- [ ] Acesso ao painel da Hostinger
- [ ] Conhecimento básico de PHP e MySQL
- [ ] Documentação do canal financeiro como referência

### 📊 **Sequência de IDs**
```
📊 SEQUÊNCIA DE CANAIS
├── Canal Financeiro: ID 3000 (canal 36) ✅ ATIVO
├── Canal Comercial: ID 3001 (canal 37) 🔄 PRÓXIMO
├── Canal Suporte: ID 3002 (canal 38) 📋 PLANEJADO
├── Canal Vendas: ID 3003 (canal 39) 📋 PLANEJADO
└── Canal Cliente X: ID 3004 (canal 40) 📋 PLANEJADO
```

## 🚀 **Passo a Passo - Criação de Canal**

### 1️⃣ **Planejamento do Canal**

#### **Definir Configurações Básicas**
```php
// Exemplo para Canal Comercial
define('CANAL_ID', 3001);                    // Próximo ID disponível
define('CANAL_NUMERO', 37);                  // Próximo número disponível
define('CANAL_NOME', 'Comercial');           // Nome do canal
define('CANAL_TIPO', 'whatsapp');            // Tipo de canal
define('CANAL_DESCRICAO', 'Canal para atendimento comercial');
```

#### **Definir Número do WhatsApp**
```php
define('CANAL_WHATSAPP_NUMERO', '47999999999');  // Número sem código do país
define('CANAL_WHATSAPP_COMPLETO', '5547999999999@c.us'); // Formato para API
```

### 2️⃣ **Criar Banco de Dados**

#### **No Painel da Hostinger**
1. Acessar [painel.hostinger.com](https://painel.hostinger.com)
2. Ir em **Bancos de Dados** → **MySQL**
3. Clicar em **Criar Banco de Dados**
4. Configurar:
   - **Nome**: `pixel12digital_comercial`
   - **Usuário**: `pixel12digital`
   - **Senha**: Gerar senha segura
   - **Host**: `localhost`

#### **Estrutura do Banco**
```sql
-- Conectar ao novo banco
USE pixel12digital_comercial;

-- Tabela de clientes
CREATE TABLE clientes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    contact_name VARCHAR(255),
    celular VARCHAR(20),
    telefone VARCHAR(20),
    cpf_cnpj VARCHAR(20),
    email VARCHAR(255),
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('ativo', 'inativo') DEFAULT 'ativo',
    INDEX idx_celular (celular),
    INDEX idx_cpf_cnpj (cpf_cnpj)
);

-- Tabela de mensagens
CREATE TABLE mensagens_comunicacao (
    id INT AUTO_INCREMENT PRIMARY KEY,
    canal_id INT NOT NULL,
    cliente_id INT,
    mensagem TEXT NOT NULL,
    tipo VARCHAR(50) DEFAULT 'texto',
    data_hora TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    direcao ENUM('recebido', 'enviado') NOT NULL,
    status VARCHAR(50) DEFAULT 'recebido',
    numero_whatsapp VARCHAR(50),
    INDEX idx_canal_data (canal_id, data_hora),
    INDEX idx_cliente (cliente_id),
    INDEX idx_numero (numero_whatsapp)
);

-- Tabela de canais
CREATE TABLE canais_comunicacao (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tipo VARCHAR(50) NOT NULL,
    identificador VARCHAR(100) UNIQUE,
    nome_exibicao VARCHAR(255) NOT NULL,
    status ENUM('conectado', 'desconectado', 'manutencao') DEFAULT 'conectado',
    data_conexao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ultima_atividade TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Inserir canal na tabela
INSERT INTO canais_comunicacao (tipo, identificador, nome_exibicao, status) 
VALUES ('whatsapp', 'comercial', 'WhatsApp Comercial', 'conectado');
```

### 3️⃣ **Criar Pasta do Canal**

#### **Na VPS**
```bash
# Acessar VPS
ssh root@212.85.11.238

# Navegar para o projeto
cd /var/www/html/loja-virtual-revenda/canais/

# Criar pasta do novo canal
mkdir comercial
cd comercial
```

### 4️⃣ **Copiar Template**

#### **Copiar Arquivos**
```bash
# Copiar configuração
cp ../template/canal_config.php canal_config.php

# Copiar README
cp ../template/README.md README.md

# Verificar arquivos
ls -la
```

### 5️⃣ **Configurar canal_config.php**

#### **Editar Configurações**
```bash
# Editar arquivo de configuração
nano canal_config.php
```

#### **Configurações Específicas**
```php
<?php
/**
 * CONFIGURAÇÃO ESPECÍFICA - CANAL COMERCIAL
 */

// ===== CONFIGURAÇÕES DO CANAL =====
define('CANAL_ID', 3001);                    // ID do canal
define('CANAL_NUMERO', 37);                  // Número do canal
define('CANAL_NOME', 'Comercial');           // Nome do canal
define('CANAL_TIPO', 'whatsapp');            // Tipo de canal
define('CANAL_DESCRICAO', 'Canal para atendimento comercial');

// ===== CONFIGURAÇÕES DO WHATSAPP =====
define('CANAL_WHATSAPP_NUMERO', '47999999999');  // Número do WhatsApp
define('CANAL_WHATSAPP_COMPLETO', '5547999999999@c.us'); // Formato API

// ===== CONFIGURAÇÕES DE BANCO DE DADOS =====
define('CANAL_USAR_BANCO_PRINCIPAL', false); // Usa banco separado
define('CANAL_BANCO_NOME', 'pixel12digital_comercial'); // Nome do banco
define('CANAL_BANCO_HOST', 'localhost');     // Host do banco
define('CANAL_BANCO_USER', 'pixel12digital'); // Usuário
define('CANAL_BANCO_PASS', 'SUA_SENHA_AQUI'); // Senha (alterar)

// ===== CONFIGURAÇÕES DE AUTOMAÇÃO =====
define('CANAL_AUTOMACAO_ATIVA', true);       // Automação ativa
define('CANAL_RESPOSTA_PADRAO', true);       // Sempre responder
define('CANAL_DIRECIONAR_CONTATO', true);    // Direcionar para contato

// ===== CONFIGURAÇÕES DE MENSAGENS =====
define('CANAL_CONTATO_DIRETO', '47 999999999'); // Contato direto
define('CANAL_PALAVRA_CHAVE_PRINCIPAL', 'ajuda'); // Palavra-chave

// ===== CONFIGURAÇÕES DE LOG =====
define('CANAL_LOG_ATIVO', true);             // Logs ativos
define('CANAL_LOG_PREFIXO', '[CANAL_COMERCIAL]'); // Prefixo dos logs

// ===== CONFIGURAÇÕES DE WEBHOOK =====
define('CANAL_WEBHOOK_URL', '/api/webhook_canal_37.php'); // URL do webhook
define('CANAL_WEBHOOK_ATIVO', true);         // Webhook ativo

// ... outras configurações ...
?>
```

### 6️⃣ **Criar Webhook Específico**

#### **Criar Arquivo Webhook**
```bash
# Navegar para pasta api
cd /var/www/html/loja-virtual-revenda/api/

# Criar webhook específico
nano webhook_canal_37.php
```

#### **Código do Webhook**
```php
<?php
/**
 * WEBHOOK ESPECÍFICO - CANAL COMERCIAL
 * 
 * Este endpoint recebe mensagens do servidor WhatsApp
 * e as processa no sistema do canal comercial
 */

// Cabeçalhos anti-cache
header('Cache-Control: no-cache, no-store, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');
header('Content-Type: application/json');

// Carregar configuração do canal
require_once __DIR__ . '/../canais/comercial/canal_config.php';

// Verificar se canal está ativo
if (!isCanalAtivo()) {
    http_response_code(503);
    echo json_encode([
        'success' => false,
        'message' => 'Canal inativo',
        'canal' => getCanalInfo()
    ]);
    exit;
}

// Conectar ao banco específico do canal
$mysqli = conectarBancoCanal();
if (!$mysqli) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro de conexão com banco de dados'
    ]);
    exit;
}

// Log da requisição
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// Salvar log
$log_file = '../logs/webhook_canal_37_' . date('Y-m-d') . '.log';
$log_data = date('Y-m-d H:i:s') . ' - ' . $input . "\n";
file_put_contents($log_file, $log_data, FILE_APPEND);

// Debug: Log inicial
error_log(CANAL_LOG_PREFIXO . " 🚀 Webhook iniciado - " . date('Y-m-d H:i:s'));
error_log(CANAL_LOG_PREFIXO . " 📥 Dados recebidos: " . json_encode($data));

// Verificar se é uma mensagem recebida
if (isset($data['event']) && $data['event'] === 'onmessage') {
    $message = $data['data'];
    
    // Extrair informações
    $numero = $message['from'];
    $texto = $message['text'] ?? '';
    $tipo = $message['type'] ?? 'text';
    $data_hora = date('Y-m-d H:i:s');
    
    error_log(CANAL_LOG_PREFIXO . " 📥 Mensagem recebida de: $numero - Texto: '$texto'");
    
    // Buscar cliente pelo número
    $numero_limpo = preg_replace('/\D/', '', $numero);
    $cliente_id = null;
    $cliente = null;
    
    // Buscar cliente com similaridade de número
    $formatos_busca = [
        $numero_limpo,
        ltrim($numero_limpo, '55'),
        substr($numero_limpo, -11),
        substr($numero_limpo, -10),
        substr($numero_limpo, -9),
    ];
    
    foreach ($formatos_busca as $formato) {
        if (strlen($formato) >= 9) {
            $sql = "SELECT id, nome, contact_name, celular, telefone FROM clientes 
                    WHERE REPLACE(REPLACE(REPLACE(REPLACE(celular, '(', ''), ')', ''), '-', ''), ' ', '') LIKE '%$formato%' 
                    OR REPLACE(REPLACE(REPLACE(REPLACE(telefone, '(', ''), ')', ''), '-', ''), ' ', '') LIKE '%$formato%'
                    LIMIT 1";
            $result = $mysqli->query($sql);
            
            if ($result && $result->num_rows > 0) {
                $cliente = $result->fetch_assoc();
                $cliente_id = $cliente['id'];
                error_log(CANAL_LOG_PREFIXO . " ✅ Cliente encontrado - ID: $cliente_id, Nome: {$cliente['nome']}");
                break;
            }
        }
    }
    
    // Salvar mensagem recebida
    $texto_escaped = $mysqli->real_escape_string($texto);
    $tipo_escaped = $mysqli->real_escape_string($tipo);
    $numero_escaped = $mysqli->real_escape_string($numero);
    
    $sql = "INSERT INTO mensagens_comunicacao (canal_id, cliente_id, mensagem, tipo, data_hora, direcao, status, numero_whatsapp) 
            VALUES (" . CANAL_NUMERO . ", " . ($cliente_id ? $cliente_id : 'NULL') . ", '$texto_escaped', '$tipo_escaped', '$data_hora', 'recebido', 'recebido', '$numero_escaped')";
    
    if ($mysqli->query($sql)) {
        $mensagem_id = $mysqli->insert_id;
        error_log(CANAL_LOG_PREFIXO . " ✅ Mensagem salva - ID: $mensagem_id, Cliente: $cliente_id");
    } else {
        error_log(CANAL_LOG_PREFIXO . " ❌ Erro ao salvar mensagem: " . $mysqli->error);
    }
    
    // Gerar resposta automática
    $resposta_automatica = gerarRespostaCanal($cliente_id, $cliente, $texto);
    
    // Enviar resposta
    if ($resposta_automatica) {
        $api_url = WHATSAPP_ROBOT_URL . "/send";
        $data_envio = [
            "to" => $numero,
            "message" => $resposta_automatica
        ];
        
        $ch = curl_init($api_url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data_envio));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        $api_response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($http_code === 200) {
            $api_result = json_decode($api_response, true);
            if ($api_result && isset($api_result["success"]) && $api_result["success"]) {
                error_log(CANAL_LOG_PREFIXO . " ✅ Resposta enviada com sucesso");
                
                // Salvar resposta enviada
                $resposta_escaped = $mysqli->real_escape_string($resposta_automatica);
                $sql_resposta = "INSERT INTO mensagens_comunicacao (canal_id, cliente_id, mensagem, tipo, data_hora, direcao, status, numero_whatsapp) 
                                VALUES (" . CANAL_NUMERO . ", " . ($cliente_id ? $cliente_id : "NULL") . ", \"$resposta_escaped\", \"texto\", \"$data_hora\", \"enviado\", \"enviado\", \"$numero_escaped\")";
                $mysqli->query($sql_resposta);
            }
        }
    }
    
    // Responder sucesso
    echo json_encode([
        'success' => true,
        'message' => 'Mensagem processada com sucesso',
        'canal' => getCanalInfo(),
        'cliente_id' => $cliente_id,
        'cliente_nome' => $cliente ? ($cliente['contact_name'] ?: $cliente['nome']) : null,
        'resposta_enviada' => !empty($resposta_automatica)
    ]);
    
} else {
    // Responder erro
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Evento inválido ou dados incompletos',
        'data_recebida' => $data
    ]);
}

/**
 * Gera resposta específica do canal
 */
function gerarRespostaCanal($cliente_id, $cliente, $texto) {
    $texto_lower = strtolower(trim($texto));
    
    // Verificar palavra-chave principal
    if (strpos($texto_lower, CANAL_PALAVRA_CHAVE_PRINCIPAL) !== false) {
        return "Olá! 👋\n\n" .
               "🛍️ Bem-vindo ao canal comercial da Pixel12Digital!\n\n" .
               "📞 Para atendimento personalizado, entre em contato:\n" .
               CANAL_CONTATO_DIRETO . "\n\n" .
               "Estamos prontos para ajudá-lo! 😊";
    }
    
    // Resposta padrão
    if ($cliente_id && $cliente) {
        $nome_cliente = $cliente['contact_name'] ?: $cliente['nome'];
        return "Olá $nome_cliente! 👋\n\n" .
               "🛍️ Este é o canal comercial da Pixel12Digital.\n\n" .
               "📞 Para atendimento personalizado:\n" .
               CANAL_CONTATO_DIRETO;
    } else {
        return "Olá! 👋\n\n" .
               "🛍️ Este é o canal comercial da Pixel12Digital.\n\n" .
               "📞 Para atendimento personalizado:\n" .
               CANAL_CONTATO_DIRETO;
    }
}
?>
```

### 7️⃣ **Configurar WhatsApp API**

#### **Acessar WhatsApp API**
```bash
# Na VPS
cd /var/www/whatsapp-api/

# Editar configuração
nano config.json
```

#### **Adicionar Novo Canal**
```json
{
  "webhooks": {
    "financeiro": "https://app.pixel12digital.com.br/api/webhook_whatsapp.php",
    "comercial": "https://app.pixel12digital.com.br/api/webhook_canal_37.php"
  },
  "canais": {
    "financeiro": {
      "numero": "5547997309525@c.us",
      "ativo": true
    },
    "comercial": {
      "numero": "5547999999999@c.us",
      "ativo": true
    }
  }
}
```

#### **Reiniciar API**
```bash
# Reiniciar WhatsApp API
pm2 restart whatsapp-api

# Verificar status
pm2 status
pm2 logs whatsapp-api
```

### 8️⃣ **Criar Script de Teste**

#### **Criar Arquivo de Teste**
```bash
# Navegar para pasta raiz
cd /var/www/html/loja-virtual-revenda/

# Criar script de teste
nano teste_canal_comercial.php
```

#### **Código do Teste**
```php
<?php
/**
 * TESTE CANAL COMERCIAL
 */

echo "🧪 TESTE CANAL COMERCIAL\n";
echo "========================\n";

// Dados da mensagem
$numero = "5547999999999@c.us";
$texto = "ajuda";
$tipo = "text";

// Dados para enviar
$dados = [
    "event" => "onmessage",
    "data" => [
        "from" => $numero,
        "text" => $texto,
        "type" => $tipo,
        "timestamp" => time()
    ]
];

echo "📤 Enviando mensagem para: https://app.pixel12digital.com.br/api/webhook_canal_37.php\n";
echo "📄 Dados: " . json_encode($dados) . "\n\n";

// Fazer requisição
$ch = curl_init("https://app.pixel12digital.com.br/api/webhook_canal_37.php");
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($dados));
curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "📊 HTTP Code: $http_code\n";
echo "📄 Resposta: $response\n\n";

// Decodificar resposta
$resultado = json_decode($response, true);

if ($resultado) {
    echo "📋 RESULTADO:\n";
    echo "   ✅ Success: " . ($resultado['success'] ? 'Sim' : 'Não') . "\n";
    echo "   🔄 Resposta Enviada: " . ($resultado['resposta_enviada'] ? 'Sim' : 'Não') . "\n";
    echo "   👤 Cliente ID: " . ($resultado['cliente_id'] ?? 'N/A') . "\n";
    echo "   👤 Cliente Nome: " . ($resultado['cliente_nome'] ?? 'N/A') . "\n";
    echo "   📊 Canal: " . ($resultado['canal']['nome'] ?? 'N/A') . "\n";
    echo "   🔢 Canal ID: " . ($resultado['canal']['id'] ?? 'N/A') . "\n";
} else {
    echo "❌ Erro ao decodificar resposta JSON\n";
}

echo "\n✅ Teste concluído!\n";
?>
```

### 9️⃣ **Testar Canal**

#### **Executar Teste**
```bash
# Executar teste
php teste_canal_comercial.php

# Verificar logs
tail -f logs/webhook_canal_37_$(date +%Y-%m-%d).log
```

#### **Verificar Banco**
```bash
# Conectar ao banco do canal
mysql -u pixel12digital -p pixel12digital_comercial

# Verificar mensagens
SELECT * FROM mensagens_comunicacao ORDER BY data_hora DESC LIMIT 5;

# Verificar clientes
SELECT * FROM clientes ORDER BY data_cadastro DESC LIMIT 5;
```

### 🔟 **Atualizar Documentação**

#### **Atualizar README do Canal**
```bash
# Editar README do canal
nano canais/comercial/README.md
```

#### **Atualizar Documentação VPS**
```bash
# Editar documentação principal
nano docs/README_VPS_MULTI_CANAL.md
```

## 📋 **Checklist Final**

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
- 📄 [README Principal](../README.md)
- 📄 [Documentação VPS](README_VPS_MULTI_CANAL.md)
- 📄 [Manutenção de Canais](MANUTENCAO_CANAIS.md)
- 📄 [Caminhos da VPS](CAMINHOS_VPS.md)

---

**Última atualização**: 31/07/2025  
**Versão**: 1.0.0  
**Responsável**: Pixel12Digital 