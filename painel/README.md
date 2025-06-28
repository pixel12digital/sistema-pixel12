# Painel Administrativo Multi-Cliente - Integração Asaas

## Visão Geral

Este painel permite o gerenciamento centralizado de clientes e cobranças, com integração total à API do Asaas. Todos os cadastros e operações são feitos pelo painel, garantindo controle, performance e consistência dos dados.

---

## Estrutura do Projeto

- **Banco de Dados MySQL**
  - Tabela `clientes`: espelha os clientes do Asaas
  - Tabela `cobrancas`: espelha as cobranças do Asaas
- **Painel PHP**
  - Cadastro, listagem e gestão de clientes
  - Integração via API com o Asaas
  - Todos os dados são salvos localmente e sincronizados com o Asaas

---

## Fluxo de Cadastro de Cliente

1. Acesse `clientes.php` para ver a lista de clientes.
2. Clique em "+ Novo Cliente" para abrir o formulário de cadastro.
3. Preencha os dados (nome, e-mail, telefone, CPF/CNPJ) e envie.
4. O sistema:
   - Cria o cliente no banco local
   - Cria o cliente no Asaas via API
   - Salva o `asaas_id` retornado
5. O cliente aparece na listagem imediatamente.

---

## Integração com o Asaas

- Todos os cadastros de clientes são replicados no Asaas.
- O campo `asaas_id` é usado para vincular o cliente local ao cliente do Asaas.
- O painel pode ser expandido para cadastrar cobranças, consultar status, sincronizar pagamentos, etc.

---

## Como rodar localmente

1. **Pré-requisitos:**
   - XAMPP ou similar (PHP + MySQL)
   - Banco criado: `admin_revenda_sites`
   - Tabelas criadas (veja `db.sql`)
2. **Configuração:**
   - Edite `config.php` e insira sua API Key do Asaas
   - Certifique-se que `db.php` está com os dados corretos de conexão
3. **Acesso:**
   - Acesse `clientes.php` para listar clientes
   - Acesse `cliente_add.php` para cadastrar novos clientes

---

## Próximos Passos Sugeridos

- Cadastro e listagem de cobranças integradas ao Asaas
- Sincronização automática de cobranças e status de pagamento
- Edição e exclusão de clientes/cobranças
- Dashboard de métricas e relatórios
- Integração com webhooks do Asaas para atualização em tempo real

---

## Observações

- Todas as operações devem ser feitas pelo painel para garantir consistência.
- O Asaas é usado apenas como backend financeiro; o painel é o ponto central de controle.
- Para grandes volumes, recomenda-se sincronização periódica e/ou uso de webhooks.

---

Dúvidas ou sugestões? Fale com o desenvolvedor responsável pelo painel. 