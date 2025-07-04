# 📋 Planejamento Central de Comunicação (WhatsApp, Direct, Messenger, E-mail)

## **Visão Geral**
Centralizar o envio e recebimento de mensagens de múltiplos canais (WhatsApp, Direct, Messenger, E-mail) em um único painel, começando pelo WhatsApp (sem API oficial).

---

## **Checklist de Etapas**

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

---

## **Como usar este checklist**
- Ao iniciar cada etapa, marque como [OK] quando concluída.
- Mantenha este documento salvo no repositório ou em local de fácil acesso para consulta e atualização. 