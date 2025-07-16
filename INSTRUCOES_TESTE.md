# INSTRUÇÕES PARA TESTE DO SISTEMA DE CHAT - CHARLES DIETRICH

## Status Atual
✅ **Robô WhatsApp**: Rodando em http://localhost:3000  
✅ **XAMPP**: Rodando na porta 8080  
✅ **Banco de Dados**: 149 clientes, 1 canal WhatsApp conectado, 68 mensagens  
✅ **Cliente de Teste**: Charles Dietrich (ID: 156) - 47996164699

## Como Testar

### 1. Teste Básico (Recomendado primeiro)
Abra no navegador: `http://localhost:8080/loja-virtual-revenda/teste_chat.html`

Este arquivo já está configurado com seus dados:
- **Cliente**: Charles Dietrich (ID: 156)
- **Celular**: 47996164699
- **Canal**: Financeiro (ID: 36)

### 2. Teste do Chat Principal
Abra no navegador: `http://localhost:8080/loja-virtual-revenda/painel/chat.php?cliente_id=156`

**O que você verá:**
- Área de debug verde no canto superior direito
- Lista de conversas à esquerda (sua conversa deve aparecer)
- Área de detalhes do cliente no topo (seus dados)
- Chat na parte inferior (com suas mensagens anteriores)

### 3. Teste Passo a Passo

#### A. Testar Busca de Clientes
1. Digite "Charles" no campo de busca
2. Verifique se aparece na área de debug
3. Clique em um resultado da busca

#### B. Testar Abertura de Conversa
1. Clique em sua conversa na lista esquerda
2. Verifique se a URL muda para `?cliente_id=156`
3. Verifique se seus detalhes carregam
4. Verifique se o histórico de mensagens aparece (você já tem 4 mensagens)

#### C. Testar Envio de Mensagem
1. Digite uma mensagem no campo
2. Selecione o canal "Financeiro" no dropdown
3. Clique em "Enviar"
4. Verifique se a mensagem aparece no chat
5. Verifique os logs de debug

### 4. Verificar Logs de Debug

#### Logs do Frontend (JavaScript)
- Abra o Console do navegador (F12)
- Veja a área verde de debug na tela
- Todos os eventos são logados em tempo real

#### Logs do Backend (PHP)
- Verifique o arquivo de log do PHP (geralmente em `C:\xampp\php\logs\php_error_log`)
- Ou adicione `error_reporting(E_ALL); ini_set('display_errors', 1);` no início dos arquivos PHP

### 5. Dados de Teste Disponíveis

**Seu Cliente:**
- ID: 156
- Nome: Charles Dietrich
- Celular: 47996164699
- Mensagens existentes: 4 mensagens enviadas

**Canal de Teste:**
- ID: 36
- Nome: Financeiro
- Tipo: whatsapp
- Status: conectado

### 6. Possíveis Problemas e Soluções

#### Problema: Nenhuma conversa aparece
**Solução:** Sua conversa deve aparecer pois você tem mensagens no banco

#### Problema: Clique no número não funciona
**Solução:** Seu número está formatado corretamente (47996164699)

#### Problema: Mensagem não envia
**Solução:** 
1. Verifique se o robô está rodando: `http://localhost:3000/status`
2. Verifique os logs de debug
3. Verifique se o canal está conectado

#### Problema: Erro 404 nas APIs
**Solução:** Verifique se os arquivos estão no caminho correto:
- `painel/api/historico_mensagens.php`
- `painel/api/detalhes_cliente.php`
- `painel/api/formulario_envio.php`
- `painel/api/buscar_clientes.php`

### 7. Comandos Úteis

**Verificar se robô está rodando:**
```bash
netstat -an | findstr :3000
```

**Verificar se XAMPP está rodando:**
```bash
netstat -an | findstr :8080
```

**Verificar seus dados no banco:**
```bash
php verificar_cliente_celular.php
```

### 8. Testes Específicos para Você

#### Teste 1: Busca por Nome
- Digite "Charles" na busca
- Deve encontrar você

#### Teste 2: Busca por Número
- Digite "47996164699" na busca
- Deve encontrar você

#### Teste 3: Histórico de Mensagens
- Use ID 156
- Deve mostrar suas 4 mensagens anteriores

#### Teste 4: Envio de Mensagem
- Use ID 156 e canal 36
- Deve enviar para seu WhatsApp

#### Teste 5: Clique no Número
- No chat principal, clique no seu número
- Deve abrir a conversa

### 9. Próximos Passos

Se tudo estiver funcionando:
1. ✅ Teste com seu número de celular
2. Teste o envio de anexos
3. Teste a busca por número de telefone
4. Teste o polling automático de mensagens

Se houver problemas:
1. Verifique os logs de debug
2. Verifique o console do navegador
3. Verifique os logs do PHP
4. Reporte os erros específicos encontrados

### 10. Links Diretos para Teste

- **Teste Básico**: http://localhost:8080/loja-virtual-revenda/teste_chat.html
- **Chat Principal**: http://localhost:8080/loja-virtual-revenda/painel/chat.php?cliente_id=156
- **Status do Robô**: http://localhost:3000/status 