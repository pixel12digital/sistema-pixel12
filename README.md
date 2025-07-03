# Loja Virtual Revenda

Sistema multi-tenant para revenda de sites com painel administrativo, integração Asaas e deploy facilitado na Hostinger.

---

## 🚀 Funcionalidades

- Gestão de clientes e cobranças (Asaas)
- Painel administrativo completo
- Deploy automatizado via Git
- Banco de dados centralizado (Hostinger)
- Estrutura pronta para produção

---

## 📁 Estrutura do Projeto

```
painel/                # Painel administrativo
public/                # Arquivos públicos (webhook, assets)
src/                   # Código de domínio (MVC)
database/migrations/   # Migrations do banco
config.php.example     # Exemplo de configuração
.gitignore
README.md
```

---

## ⚙️ Instalação

1. **Clone o repositório**
   ```sh
   git clone https://github.com/seu-usuario/seu-repositorio.git
   cd seu-repositorio
   ```

2. **Configure o ambiente**
   - Copie o arquivo de exemplo:
     ```sh
     cp config.php.example config.php
     ```
   - Edite `config.php` com os dados do banco centralizado e chave Asaas.

3. **Instale dependências (se houver)**
   - PHP: `composer install`
   - Node: `npm install` (se usar frontend moderno)

4. **Execute as migrations**
   - Manualmente ou via script, conforme seu setup.

---

## 🚀 Deploy na Hostinger

1. **Acesse a pasta `/public_html` do seu domínio na Hostinger**
2. **Clone o repositório:**
   ```sh
   git clone https://github.com/seu-usuario/seu-repositorio.git .
   ```
3. **Atualize sempre com:**
   ```sh
   git pull
   ```
4. **Configure o `config.php` com os dados do banco da Hostinger**

---

## 🛡️ Segurança

- Nunca versionar `config.php` real.
- `.gitignore` cobre arquivos sensíveis, temporários e de ambiente.
- Banco de dados centralizado, sempre protegido por senha forte.

---

## 📝 Observações

- Nunca crie uma pasta `public_html` dentro do repositório.
- O deploy é feito diretamente na raiz do domínio.
- O banco de dados deve ser sempre o centralizado da Hostinger.

---

## 📞 Suporte

- Email: suporte@seudominio.com
- WhatsApp: (11) 99999-9999

---

**Desenvolvido para facilitar a revenda de sites com manutenção centralizada.**

# Loja Virtual Multi-Cliente - Histórico e Orientações

## Sobre o Usuário
Este projeto está sendo desenvolvido para um usuário **não programador**. Todas as decisões técnicas, estruturação, criação de arquivos, organização do código e manutenção serão feitas pelo assistente de IA (ChatGPT/Cursor). O usuário deseja praticidade, agilidade e centralização, sem se preocupar com detalhes técnicos de programação.

## Histórico e Contexto
- O usuário deseja um **painel único** para gerenciar múltiplos clientes, cada um com seu próprio banco de dados.
- O sistema deve ser **simples, fácil de atualizar** (basta substituir arquivos do painel) e sem dependências complexas (sem Composer, sem .env, sem frameworks pesados).
- O objetivo é evitar conflitos, facilitar a manutenção e permitir que tudo seja gerenciado em um só lugar.
- O usuário não irá programar: **toda a criação, ajuste e manutenção do código será feita pelo assistente**.

## Estrutura Recomendada
```
public_html/                ← Raiz do site na Hostinger
│
├── painel/                 ← Painel administrativo central
│   ├── index.php           ← Login e dashboard
│   ├── clientes.php        ← Gerenciamento de clientes
│   ├── config.php          ← Configurações globais do sistema
│   ├── conexao.php         ← Função de conexão dinâmica (por cliente)
│   ├── assets/             ← CSS, JS, imagens do painel
│   └── ...                 ← Outras páginas do painel
│
├── clientes/               ← Pasta para arquivos públicos de cada cliente (opcional)
│   ├── cliente1/           ← Site do cliente 1 (se necessário)
│   ├── cliente2/           ← Site do cliente 2 (se necessário)
│   └── ...
│
└── README.txt              ← Instruções rápidas de uso
```

## Funcionamento
- **Login único** para o administrador.
- **Cada cliente tem seu próprio banco de dados**.
- **Atualização centralizada**: basta substituir os arquivos do painel.
- **Cadastro/edição de clientes**: feito pelo painel, associando cada cliente a um banco.
- **Gerenciamento fácil**: tudo em um só lugar.

## Vantagens
- Simplicidade máxima.
- Agilidade para criar, duplicar, migrar ou atualizar projetos.
- Isolamento de dados por cliente.
- Manutenção fácil e centralizada.
- Escalabilidade para novos clientes.

## Orientações para o Assistente
- Sempre que este projeto for aberto, **lembre-se que o usuário não é programador**.
- Explique cada passo de forma simples e didática.
- Faça toda a criação e manutenção do código.
- Evite dependências complexas.
- Mantenha tudo centralizado e fácil de atualizar.

---

**Este histórico deve ser mantido e atualizado em cada nova interação para garantir continuidade e clareza no suporte ao usuário.**

# 🔄 Sincronização Diária com Asaas (Objetivos e Fluxo)

## Objetivo
Manter uma **cópia local** (no banco de dados MySQL) de todos os dados financeiros relevantes do Asaas (clientes, cobranças, assinaturas, etc) para:
- Consultas rápidas e relatórios, mesmo se a API do Asaas estiver fora do ar
- Performance e escalabilidade do painel
- Geração de históricos, gráficos e exportações
- Possibilidade de integrações futuras com outros sistemas
- Garantir contingência e backup dos dados financeiros

## Como funciona
- O painel cadastra e consulta dados em tempo real via API do Asaas
- **Diariamente** (ou em outro intervalo definido), um script de sincronização busca todos os dados do Asaas e atualiza o banco local
- O banco local é considerado a "fonte de consulta" para relatórios, dashboards e histórico
- Em caso de divergência, o painel pode sempre "forçar" uma atualização manual

## Benefícios
- Segurança: dados disponíveis mesmo se o Asaas estiver indisponível
- Velocidade: consultas e relatórios instantâneos
- Flexibilidade: cruzamento de dados com outros módulos do painel
- Independência: possibilidade de exportar, migrar ou integrar com outros sistemas

## Fluxo de Sincronização
1. **Agendamento**: Um script PHP é executado diariamente (via cron, agendador de tarefas ou manualmente)
2. **Busca**: O script consulta a API do Asaas e traz todos os clientes, cobranças, assinaturas, etc
3. **Atualização**: Os dados são inseridos/atualizados no banco local (MySQL)
4. **Log**: A data/hora da última sincronização é registrada
5. **Painel**: O painel passa a exibir os dados do banco local, não diretamente da API

## Responsabilidades do Assistente
- Implementar e manter o script de sincronização
- Garantir que a estrutura do banco local esteja sempre compatível com a API do Asaas
- Documentar claramente como agendar e monitorar a rotina
- Orientar o usuário sobre como forçar sincronizações manuais, se necessário

## Instruções para Agendamento (Linux/Hostinger)
1. Suba o script `scripts/sincroniza_asaas.php` para o servidor
2. No painel de hospedagem, agende uma tarefa cron diária:
   ```
   php /caminho/para/scripts/sincroniza_asaas.php
   ```
3. Verifique os logs de execução e a data/hora da última sincronização no painel

## Observações
- O painel continuará funcionando mesmo sem sincronização, mas os dados locais podem ficar desatualizados
- Recomenda-se manter a rotina diária ativa para garantir histórico e performance
- Em caso de dúvidas, consulte o suporte técnico

---

# Loja Virtual Revenda – API de Faturas (Asaas)

## Endpoints RESTful

- **Listar faturas:**
  - `GET /api/invoices.php`
- **Criar fatura:**
  - `POST /api/invoices.php` (JSON: client_id, valor, etc.)
- **Reenviar link:**
  - `POST /api/invoices.php?id={asaas_id}&action=resend`
- **Cancelar fatura:**
  - `POST /api/invoices.php?id={asaas_id}&action=cancel`
- **Obter PDF:**
  - `GET /api/invoices.php?id={asaas_id}&action=pdf`

- **Webhook Asaas:**
  - `POST /api/webhooks.php` (configurar no painel Asaas)

## Sincronização diária
- Comando: `php api/asaasSync.php`
- Agende no painel Hostinger (Cron Jobs) para manter status sempre atualizado.

## Configuração
- Configure sua chave e endpoint Asaas em `config.php` ou `.env`.
- Certifique-se de que as views `clients`, `invoices`, `subscriptions` existem no banco.

## Deploy na Hostinger
- Basta enviar os arquivos PHP para o servidor.
- Não requer Composer ou dependências externas.
- Todos os endpoints funcionam em PHP puro.

## Observações
- Não altere nomes de classes/IDs no front.
- Para dúvidas ou ajustes, consulte o código dos endpoints em `/api`. 