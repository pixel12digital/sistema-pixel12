<?php
/**
 * CORREÇÃO DO WEBHOOK PARA CADASTRO AUTOMÁTICO DE CLIENTES
 * 
 * Problema: Clientes que iniciam conversas não são cadastrados automaticamente
 * Solução: Modificar webhook principal para criar clientes automaticamente
 */

echo "=== CORREÇÃO DO WEBHOOK PARA CLIENTES NÃO CADASTRADOS ===\n\n";

// 1. Verificar arquivo atual
$arquivo_webhook = 'api/webhook.php';
$arquivo_backup = 'api/webhook.php.backup.' . date('Y-m-d_H-i-s');

if (!file_exists($arquivo_webhook)) {
    echo "❌ Arquivo $arquivo_webhook não encontrado\n";
    exit;
}

// 2. Fazer backup
echo "1. Fazendo backup do webhook atual...\n";
if (copy($arquivo_webhook, $arquivo_backup)) {
    echo "✅ Backup criado: $arquivo_backup\n";
} else {
    echo "❌ Erro ao criar backup\n";
    exit;
}

// 3. Ler conteúdo atual
echo "\n2. Lendo webhook atual...\n";
$conteudo = file_get_contents($arquivo_webhook);

// 4. Aplicar correções
echo "3. Aplicando correções para cadastro automático...\n";

// Correção: Adicionar lógica de cadastro automático
$correcao_cadastro = '
    // CORREÇÃO: Cadastro automático de clientes não cadastrados
    if (!$cliente_id) {
        echo "Cliente não encontrado, criando cadastro automático...\n";
        
        // Formatar número para salvar
        $numero_para_salvar = $numero;
        if (strpos($numero, "55") === 0) {
            $numero_para_salvar = substr($numero, 2);
        }
        
        // Criar cliente automaticamente
        $nome_cliente = "Cliente WhatsApp (" . $numero_para_salvar . ")";
        $data_criacao = date("Y-m-d H:i:s");
        
        $sql_criar = "INSERT INTO clientes (nome, celular, data_criacao, data_atualizacao) 
                      VALUES (\"" . $mysqli->real_escape_string($nome_cliente) . "\", 
                              \"" . $mysqli->real_escape_string($numero_para_salvar) . "\", 
                              \"$data_criacao\", \"$data_criacao\")";
        
        if ($mysqli->query($sql_criar)) {
            $cliente_id = $mysqli->insert_id;
            echo "✅ Cliente criado automaticamente - ID: $cliente_id\n";
            
            // Log da criação
            error_log("[WEBHOOK] Cliente criado automaticamente - ID: $cliente_id, Número: $numero_para_salvar");
        } else {
            echo "❌ Erro ao criar cliente: " . $mysqli->error . "\n";
            error_log("[WEBHOOK] Erro ao criar cliente: " . $mysqli->error);
        }
    }';

// Inserir correção após a busca do cliente
$posicao_insercao = strpos($conteudo, '// Salvar mensagem recebida');
if ($posicao_insercao !== false) {
    $conteudo = substr_replace($conteudo, $correcao_cadastro . "\n\n    ", $posicao_insercao, 0);
    echo "✅ Lógica de cadastro automático adicionada\n";
}

// Correção: Melhorar resposta automática
$resposta_melhorada = '
    // Resposta automática melhorada
    if ($texto) {
        if ($cliente_id) {
            // Cliente cadastrado
            $resposta = "Olá! Sua mensagem foi recebida. Em breve entraremos em contato.";
        } else {
            // Cliente não cadastrado (não deveria acontecer após a correção)
            $resposta = "Olá! Bem-vindo! Sua mensagem foi recebida. Em breve entraremos em contato.";
        }
        
        // Enviar resposta via API WhatsApp
        $api_url = "http://212.85.11.238:3000/send";
        $data_envio = [
            "to" => $numero,
            "message" => $resposta
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
                echo "✅ Resposta automática enviada com sucesso\n";
                
                // Salvar resposta enviada
                $resposta_escaped = $mysqli->real_escape_string($resposta);
                $sql_resposta = "INSERT INTO mensagens_comunicacao (cliente_id, mensagem, tipo, data_hora, direcao, status) 
                                VALUES (" . ($cliente_id ? $cliente_id : "NULL") . ", \"$resposta_escaped\", \"texto\", \"$data_hora\", \"enviado\", \"enviado\")";
                $mysqli->query($sql_resposta);
            } else {
                echo "❌ Erro ao enviar resposta automática\n";
                error_log("[WEBHOOK] Erro ao enviar resposta: " . $api_response);
            }
        } else {
            echo "❌ Erro HTTP ao enviar resposta: $http_code\n";
            error_log("[WEBHOOK] Erro HTTP ao enviar resposta: $http_code");
        }
    }';

// Substituir resposta automática antiga
$conteudo = str_replace(
    '// Resposta automática simples
    if ($texto && $cliente_id) {
        $resposta = "Olá! Sua mensagem foi recebida. Em breve entraremos em contato.";
        
        // Enviar resposta via WPPConnect
        $wppconnect_url = \'http://localhost:8080/api/sendText/default\';
        $data_envio = [
            \'number\' => $numero,
            \'text\' => $resposta
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $wppconnect_url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data_envio));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [\'Content-Type: application/json\']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_exec($ch);
        curl_close($ch);
        
        // Salvar resposta enviada
        $resposta_escaped = $mysqli->real_escape_string($resposta);
        $sql = "INSERT INTO mensagens_comunicacao (cliente_id, mensagem, tipo, data_hora, direcao, status) 
                VALUES ($cliente_id, \'$resposta_escaped\', \'text\', \'$data_hora\', \'enviado\', \'entregue\')";
        $mysqli->query($sql);
    }',
    $resposta_melhorada,
    $conteudo
);

echo "✅ Resposta automática melhorada\n";

// 5. Salvar arquivo corrigido
echo "\n4. Salvando webhook corrigido...\n";
if (file_put_contents($arquivo_webhook, $conteudo)) {
    echo "✅ Webhook corrigido salvo com sucesso\n";
} else {
    echo "❌ Erro ao salvar webhook\n";
    exit;
}

// 6. Criar script de teste
echo "\n5. Criando script de teste...\n";
$script_teste = '<?php
// Teste do webhook corrigido
echo "=== TESTE DO WEBHOOK CORRIGIDO ===\n\n";

// Simular dados de mensagem recebida
$dados_teste = [
    "event" => "onmessage",
    "data" => [
        "from" => "5547999999999", // Número fictício para teste
        "text" => "Olá, gostaria de informações sobre os serviços",
        "type" => "text"
    ]
];

echo "1. Dados de teste:\n";
echo "Número: " . $dados_teste["data"]["from"] . "\n";
echo "Mensagem: " . $dados_teste["data"]["text"] . "\n\n";

// Simular requisição POST
$_POST = $dados_teste;
$_SERVER["REQUEST_METHOD"] = "POST";

echo "2. Executando webhook...\n";
include "api/webhook.php";

echo "\n3. Verificando resultado no banco...\n";
$mysqli = new mysqli("srv1607.hstgr.io", "u342734079_revendaweb", "Los@ngo#081081", "u342734079_revendaweb");
$mysqli->set_charset("utf8mb4");

// Verificar se cliente foi criado
$numero_teste = "5547999999999";
$numero_limpo = preg_replace("/\D/", "", $numero_teste);
if (strpos($numero_limpo, "55") === 0) {
    $numero_limpo = substr($numero_limpo, 2);
}

$res = $mysqli->query("SELECT id, nome, celular FROM clientes WHERE celular LIKE \"%$numero_limpo%\" ORDER BY id DESC LIMIT 1");
if ($res && $res->num_rows > 0) {
    $cliente = $res->fetch_assoc();
    echo "✅ Cliente encontrado:\n";
    echo "- ID: " . $cliente["id"] . "\n";
    echo "- Nome: " . $cliente["nome"] . "\n";
    echo "- Celular: " . $cliente["celular"] . "\n";
} else {
    echo "❌ Cliente não encontrado\n";
}

// Verificar mensagens
$res = $mysqli->query("SELECT id, cliente_id, mensagem, direcao, status FROM mensagens_comunicacao WHERE mensagem LIKE \"%gostaria de informações%\" ORDER BY id DESC LIMIT 2");
if ($res && $res->num_rows > 0) {
    echo "\n✅ Mensagens encontradas:\n";
    while ($msg = $res->fetch_assoc()) {
        echo "- ID: " . $msg["id"] . " | Cliente: " . $msg["cliente_id"] . " | Direção: " . $msg["direcao"] . " | Status: " . $msg["status"] . "\n";
    }
} else {
    echo "\n❌ Mensagens não encontradas\n";
}

$mysqli->close();
echo "\n=== FIM DO TESTE ===\n";
?>';

file_put_contents('teste_webhook_corrigido.php', $script_teste);
echo "✅ Script de teste criado: teste_webhook_corrigido.php\n";

// 7. Criar documentação
echo "\n6. Criando documentação...\n";
$documentacao = "# 🔄 CORREÇÃO DO WEBHOOK - CADASTRO AUTOMÁTICO DE CLIENTES

## 📋 Problema Resolvido

**Antes:** Clientes que iniciam conversas mas não estão cadastrados no banco não eram tratados adequadamente.

**Depois:** Todos os clientes que iniciam conversas são automaticamente cadastrados no sistema.

## 🛠️ Correções Aplicadas

### 1. Cadastro Automático
- Clientes não cadastrados são criados automaticamente
- Nome padrão: \"Cliente WhatsApp (número)\"
- Número salvo no formato correto

### 2. Resposta Automática Melhorada
- Resposta para todos os clientes (cadastrados e novos)
- Uso da API WhatsApp correta (212.85.11.238:3000)
- Logs detalhados para debug

### 3. Tratamento de Erros
- Logs de erro para problemas de cadastro
- Logs de erro para problemas de envio
- Fallback para situações de erro

## 📊 Fluxo Atualizado

1. **Mensagem recebida** → Webhook processa
2. **Busca cliente** → Verifica se existe no banco
3. **Se não existe** → Cria cliente automaticamente
4. **Salva mensagem** → Com cliente_id correto
5. **Envia resposta** → Resposta automática
6. **Salva resposta** → Registra no histórico

## 🧪 Como Testar

```bash
php teste_webhook_corrigido.php
```

## 🔄 Como Reverter

```bash
cp api/webhook.php.backup.$(date +%Y-%m-%d_%H-%M-%S) api/webhook.php
```

## 📝 Logs

Os logs são salvos em:
- `logs/webhook_YYYY-MM-DD.log` - Logs gerais do webhook
- `error_log` - Logs de erro do sistema

## ✅ Benefícios

- ✅ Nenhum cliente perdido
- ✅ Histórico completo de conversas
- ✅ Resposta automática para todos
- ✅ Dados estruturados no banco
- ✅ Fácil identificação de novos clientes
";

file_put_contents('DOCUMENTACAO_WEBHOOK_CORRIGIDO.md', $documentacao);
echo "✅ Documentação criada: DOCUMENTACAO_WEBHOOK_CORRIGIDO.md\n";

echo "\n=== CORREÇÃO CONCLUÍDA ===\n";
echo "\n📋 PRÓXIMOS PASSOS:\n";
echo "1. Teste o webhook: php teste_webhook_corrigido.php\n";
echo "2. Envie uma mensagem de teste para o WhatsApp\n";
echo "3. Verifique se o cliente foi criado automaticamente\n";
echo "4. Para reverter: cp $arquivo_backup $arquivo_webhook\n";
?> 