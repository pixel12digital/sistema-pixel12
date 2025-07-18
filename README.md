# 🚀 Sistema de Loja Virtual com WhatsApp API

Sistema completo de loja virtual integrado com WhatsApp API para comunicação automatizada com clientes.

---

## 📋 Índice

- [🎯 Visão Geral](#-visão-geral)
- [🔧 Configuração](#-configuração)
- [📱 WhatsApp API](#-whatsapp-api)
- [🛠️ Funcionalidades](#️-funcionalidades)
- [📊 Monitoramento](#-monitoramento)
- [🔍 Troubleshooting](#-troubleshooting)
- [📚 Documentação](#-documentação)

---

## 🎯 Visão Geral

Sistema desenvolvido em PHP com integração completa ao WhatsApp via API Node.js, permitindo:
- Gestão de clientes e produtos
- Comunicação automatizada via WhatsApp
- Sistema de cobranças integrado
- Painel administrativo completo

---

## 🔧 Configuração

### **Requisitos:**
- PHP 7.4+
- MySQL/MariaDB
- Node.js 16+
- XAMPP (desenvolvimento local)

### **Instalação:**
1. Clone o repositório
2. Configure o banco de dados
3. Ajuste as configurações em `config.php`
4. Instale as dependências Node.js

---

## 📱 WhatsApp API

### **🆕 Formatação Simplificada de Números (NOVA)**

A formatação de números foi simplificada para máxima flexibilidade:

#### **Como Funciona:**
- **Sistema**: Apenas adiciona código do país (55) + sufixo (@c.us)
- **Você**: Gerencia as regras específicas no cadastro do cliente
- **Flexibilidade**: Cada número pode ter sua própria regra

#### **Exemplos Práticos:**

**DDD 47 (Santa Catarina) - 8 dígitos:**
```
Cadastro: 4799616469
Enviado: 554799616469@c.us
```

**DDD 11 (São Paulo) - 9 dígitos:**
```
Cadastro: 11987654321
Enviado: 5511987654321@c.us
```

**DDD 61 (Brasília) - 9 dígitos:**
```
Cadastro: 61987654321
Enviado: 5561987654321@c.us
```

#### **Vantagens:**
- ✅ **Flexibilidade total**: Você controla cada número individualmente
- ✅ **Sem regras complexas**: Não precisa de lógica condicional no código
- ✅ **Fácil manutenção**: Cada cliente tem seu número formatado corretamente
- ✅ **Compatibilidade**: Funciona com qualquer regra específica do WhatsApp

### **📋 Como Gerenciar no Cadastro:**

1. **Salve o número exatamente como deve ser enviado para o WhatsApp**
2. **Se o DDD 47 precisa de 8 dígitos**: salve `4799616469`
3. **Se o DDD 11 precisa de 9 dígitos**: salve `11987654321`

### **🔄 Migração de Dados:**

Para números existentes que não funcionam:
```sql
-- Exemplo: Atualizar número do cliente 156 (DDD 47 - 8 dígitos)
UPDATE clientes 
SET celular = '4799616469' 
WHERE id = 156 AND celular = '47996164699';
```

### **🧪 Testes:**

```bash
# DDD 47 (8 dígitos)
curl -X POST http://localhost:3000/send \
  -H "Content-Type: application/json" \
  -d '{"to": "4799616469", "message": "Teste DDD 47"}'

# DDD 11 (9 dígitos)
curl -X POST http://localhost:3000/send \
  -H "Content-Type: application/json" \
  -d '{"to": "11987654321", "message": "Teste DDD 11"}'
```

---

## 🛠️ Funcionalidades

### **Gestão de Clientes:**
- Cadastro completo com dados pessoais e de contato
- Histórico de comunicações
- Integração com sistema de cobranças
- **🆕 Edição de clientes diretamente no chat** - Modifique dados dos clientes sem sair da conversa

### **Comunicação WhatsApp:**
- Envio automático de mensagens
- Recebimento e armazenamento de respostas
- Sistema de filas para evitar spam
- Simulação de comportamento humano

### **Sistema de Cobranças:**
- Integração com Asaas
- Notificações automáticas
- Histórico de pagamentos

### **🆕 Interface de Chat Avançada:**
- **Edição inline de clientes**: Botão "Editar" em cada cliente
- **Formulário modal**: Interface intuitiva para modificação de dados
- **Validação em tempo real**: Feedback imediato de erros
- **Atualização automática**: Lista de clientes atualizada após edição
- **Integração AJAX**: Comunicação assíncrona com o servidor
- **Tratamento robusto de erros**: Respostas JSON consistentes

---

## 📊 Monitoramento

### **Status da API:**
```bash
# Verificar status
curl http://localhost:3000/status

# Verificar fila de mensagens
curl http://localhost:3000/queue

# Verificar simulação humana
curl http://localhost:3000/simulation
```

### **Logs do Sistema:**
```bash
# Logs do PM2
pm2 logs whatsapp-api

# Status do processo
pm2 status
```

---

## 🔍 Troubleshooting

### **Problemas Comuns:**

#### **1. WhatsApp não conecta:**
- Verificar QR Code em `/qr`
- Reautenticar se necessário
- Verificar logs do PM2

#### **2. Mensagens não chegam:**
- Verificar formatação do número no cadastro
- Confirmar se o WhatsApp aceita o formato
- Verificar logs de erro

#### **3. Erro de sintaxe:**
- Verificar arquivo `whatsapp-api-server.js`
- Testar com `node -c whatsapp-api-server.js`
- Restaurar backup se necessário

### **Comandos Úteis:**
```bash
# Reiniciar servidor
pm2 restart whatsapp-api

# Ver logs em tempo real
pm2 logs whatsapp-api --lines 50

# Limpar fila de mensagens
curl -X POST http://localhost:3000/queue/clear

# Desconectar WhatsApp
curl -X POST http://localhost:3000/logout
```

### **🆕 Troubleshooting - Edição de Clientes:**

#### **1. Erro de sintaxe PHP:**
```bash
# Verificar sintaxe dos arquivos
php -l components_cliente.php
php -l api/editar_cliente.php
```

#### **2. "Erro ao salvar" no formulário:**
- Verificar logs do servidor para erros de banco
- Confirmar se o arquivo `api/db.php` está acessível
- Verificar permissões de escrita no banco de dados

#### **3. Formulário não abre:**
- Verificar console do navegador para erros JavaScript
- Confirmar se o arquivo `components_cliente.php` está sendo carregado
- Verificar se não há conflitos de CSS/JavaScript

#### **4. Dados não são salvos:**
```sql
-- Testar conexão direta com banco
SELECT * FROM clientes WHERE id = 1;
UPDATE clientes SET nome = 'Teste' WHERE id = 1;
```

#### **5. URL incorreta na requisição AJAX:**
- Verificar se o caminho `/loja-virtual-revenda/api/editar_cliente.php` está correto
- Confirmar se o arquivo existe no local especificado
- Testar acesso direto ao endpoint via navegador

---

## 📚 Documentação

### **Arquivos de Documentação:**
- `FORMATACAO_NUMEROS_SIMPLIFICADA.md` - Guia completo da nova formatação
- `DOCUMENTACAO_COMPLETA_CHAT.md` - Histórico de correções e melhorias
- `COMANDOS_VPS_FORMATACAO.md` - Comandos para atualizar VPS
- `FUNCIONALIDADE_EDICAO_CLIENTES.md` - Documentação completa da funcionalidade de edição

### **Integração:**

#### **JavaScript (Frontend):**
```javascript
async function enviarWhatsApp(numero, mensagem) {
    try {
        const response = await fetch('http://212.85.11.238:3000/send', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                to: numero,
                message: mensagem
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            console.log('Mensagem enviada:', data.messageId);
            return true;
        } else {
            console.error('Erro:', data.error);
            return false;
        }
    } catch (error) {
        console.error('Erro na requisição:', error);
        return false;
    }
}
```

#### **PHP (Backend):**
```php
function enviarWhatsApp($numero, $mensagem) {
    $url = 'http://212.85.11.238:3000/send';
    $data = json_encode([
        'to' => $numero,
        'message' => $mensagem
    ]);
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    return json_decode($response, true);
}
```

---

## 🎯 Sistema Atual

### **✅ Status:**
- 🟢 **VPS**: Online e estável (212.85.11.238:3000)
- 🟢 **API**: Respondendo corretamente
- 🟢 **WhatsApp**: Conectado e enviando mensagens
- 🟢 **Formatação**: Simplificada e flexível
- 🟢 **🆕 Edição de Clientes**: Funcionalidade operacional no chat

### **📊 Estatísticas:**
- **Servidor**: PM2 online (PID: 138310)
- **Restarts**: 76 (normal)
- **Memória**: 54.9mb
- **Status**: Funcionando perfeitamente

---

## 📞 Suporte

Para problemas ou dúvidas:
1. Verificar logs do sistema
2. Consultar documentação específica
3. Testar com números conhecidos
4. Verificar formatação no cadastro

**Lembre-se**: O WhatsApp tem regras específicas que podem variar por número, mesmo dentro do mesmo DDD! 