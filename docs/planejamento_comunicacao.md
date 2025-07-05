# 📋 Planejamento Central de Comunicação (WhatsApp, Direct, Messenger, E-mail)

## **Visão Geral**
Centralizar o envio e recebimento de mensagens de múltiplos canais (WhatsApp, Direct, Messenger, E-mail) em um único painel, começando pelo WhatsApp (sem API oficial).

---

## **Nova Estrutura de Interface do Chat Centralizado**

O painel de chat será composto por **três colunas principais**:

### 1. Coluna Esquerda (Sidebar)
- Exibe **todos os chats abertos** (lista de conversas).
- Permite busca, filtro, seleção de conversa.

### 2. Coluna do Meio (Detalhes do Cliente)
- Ao clicar em um chat na coluna da esquerda, a coluna do meio exibe o **bloco completo de detalhes do cliente** (como em `cliente_detalhes.php`), com abas, cards, dados, etc.
- Toda navegação de detalhes, projetos, relacionamento, financeiro, etc., será feita dentro desta coluna central, sem sair da tela do chat.
- Links de detalhes de cliente, projetos, etc., sempre direcionam para o chat.php, atualizando apenas a coluna do meio.

### 3. Coluna Direita (Chat com Cliente)
- Exibe o **chat aberto** com o cliente selecionado (timeline de mensagens, envio de mensagens, anexos, etc.).
- Sempre mostra a conversa ativa com o cliente selecionado na coluna do meio.

### **Fluxo de Navegação**
- O usuário vê todos os chats abertos na coluna da esquerda.
- Ao clicar em um chat, a coluna do meio mostra todos os detalhes do cliente daquele chat (com abas e cards).
- A coluna da direita mostra a conversa (chat) com aquele cliente.
- **Tudo acontece na mesma tela, sem recarregar ou sair do chat.php.**

---

## **Checklist de Implementação da Nova Arquitetura**

- [ ] Estruturar layout de três colunas no chat.php
- [ ] Exibir lista de chats abertos na coluna da esquerda
- [ ] Integrar bloco completo de detalhes do cliente (com abas) na coluna do meio
- [ ] Exibir chat aberto com o cliente selecionado na coluna da direita
- [ ] Atualizar todos os links de detalhes de cliente para direcionar ao chat.php (coluna do meio)
- [ ] Garantir responsividade e experiência fluida
- [ ] Testar navegação entre abas e atualização dinâmica das colunas
- [ ] Validar integração visual e usabilidade

---

## **Checklist Geral de Etapas**

### **1. Estrutura do Projeto**
- [OK] Criar menu "Comunicação" em Configurações no painel PHP.
- [OK] Criar tela de gerenciamento de canais conectados (WhatsApp, etc).
- [OK] Criar tela de chat centralizado (visualização e envio de mensagens).

---

### **2. Banco de Dados**
- [OK] Criar tabela `canais_comunicacao` (armazenar números/canais conectados).
- [OK] Criar tabela `mensagens_comunicacao` (armazenar histórico de mensagens).
- [ ] (Opcional) Criar tabela de mapeamento cliente x canal.

---

### **3. Backend Node.js (WhatsApp)**
- [OK] Provisionar VPS para backend Node.js
- [OK] Escolher e liberar porta exclusiva (9100) sem conflito com AzuraCast
- [OK] Instalar Node.js e npm na VPS
- [OK] Subir servidor Node.js e testar acesso externo (http://212.85.11.238:9100/)
- [ ] Instalar e configurar Venom Bot ou Baileys
- [ ] Implementar endpoint para conectar novo número (QR Code)
- [ ] Implementar endpoint para enviar mensagem
- [ ] Implementar endpoint para receber mensagens (webhook ou polling)
- [ ] Implementar endpoint para listar status dos números conectados
- [ ] Proteger API com autenticação/token

#### **Resumo das etapas já realizadas:**
- VPS provisionada e acessível
- Node.js e npm instalados
- Porta 9100 testada e liberada
- Servidor Node.js respondendo externamente

#### **Próximo passo:**
Instalar e configurar Venom Bot ou Baileys para integração com WhatsApp.

---

### **4. Integração PHP ↔ Node.js**
- [OK] Implementar chamadas HTTP do PHP para o Node.js (enviar, receber, listar mensagens).
- [OK] Sincronizar mensagens recebidas/enviadas com o banco de dados do painel.
- [OK] Exibir status dos canais conectados no painel.

---

### **5. Frontend (PHP/JS)**
- [OK] Tela para conectar novo número WhatsApp (exibir QR Code).
- [OK] Tela para listar e gerenciar canais conectados.
- [OK] Tela de chat centralizado (visualizar histórico, enviar mensagem, receber em tempo real).
- [OK] Notificações de novas mensagens.

---

### **6. Segurança e Infraestrutura**
- [ ] Garantir que Node.js rode em porta livre e segura na VPS.
- [ ] Restringir acesso à API do Node.js (token, IP, firewall).
- [ ] Monitorar uso de recursos da VPS (CPU/RAM).

---

### **7. Documentação e Manutenção**
- [ ] Documentar endpoints da API Node.js.
- [ ] Documentar estrutura das tabelas.
- [ ] Documentar fluxo de integração PHP ↔ Node.js.
- [ ] Manter este checklist atualizado a cada etapa concluída.

---

## **Como usar este checklist**
- Ao iniciar cada etapa, marque como [OK] quando concluída.
- Mantenha este documento salvo no repositório ou em local de fácil acesso para consulta e atualização.

## **Observações Importantes**
- O backend Node.js **NÃO** deve rodar na Hostinger compartilhada, mas sim em uma VPS (pode ser na própria Hostinger, desde que não conflite com AzuraCast).
- Sempre escolha portas livres e proteja a API.
- O sistema é expansível para outros canais (Direct, Messenger, E-mail) no futuro.

---

## **Exemplo de Estrutura de Tabelas**

**canais_comunicacao**
| id | tipo      | identificador | status    | nome_exibicao | data_conexao |
|----|-----------|---------------|-----------|---------------|--------------|
| 1  | whatsapp  | 5511999999999 | conectado | Suporte 1     | 2024-07-04   |

**mensagens_comunicacao**
| id | canal_id | cliente_id | mensagem | tipo   | data_hora           | direcao  | status   |
|----|----------|------------|----------|--------|---------------------|----------|----------|
| 1  | 1        | 285        | Olá!     | texto  | 2024-07-04 14:00:00 | recebido | entregue | 