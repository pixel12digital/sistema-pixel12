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

# Loja Virtual Revenda - Painel Administrativo

Sistema centralizado para revenda de sites e-commerce com código compartilhado e atualizações unificadas.

## 🚀 Características

- **Multi-tenant**: Cada cliente tem seu próprio banco de dados
- **Código centralizado**: Atualizações unificadas para todos os clientes
- **Painel administrativo**: Gestão completa de clientes, cobranças e suporte
- **Deploy automático**: Via GitHub Actions
- **Integração Asaas**: Cobranças automáticas
- **Templates personalizáveis**: Por nicho de mercado

## 📁 Estrutura do Projeto

```
loja-virtual-revenda/
├── app/
│   ├── core/                 # Código compartilhado
│   │   ├── ecommerce/        # Sistema e-commerce
│   │   ├── institutional/    # Sistema institucional
│   │   ├── database/         # Classes de banco
│   │   ├── auth/             # Autenticação
│   │   └── utils/            # Utilitários
│   ├── admin/                # Painel administrativo
│   ├── templates/            # Templates frontend
│   └── tenants/              # Configurações por cliente
├── config/                   # Configurações
├── database/                 # Migrations e seeds
├── public/                   # Arquivos públicos
├── storage/                  # Uploads e logs
├── scripts/                  # Scripts de instalação
└── docs/                     # Documentação
```

## 🛠️ Instalação

### Pré-requisitos
- PHP 8.0+
- MySQL 5.7+
- Composer
- Git

### Passos

1. **Clone o repositório**
```bash
git clone https://github.com/seu-usuario/loja-virtual-revenda.git
cd loja-virtual-revenda
```

2. **Instale as dependências**
```bash
composer install
```

3. **Configure o ambiente**
```bash
cp .env.example .env
# Edite o arquivo .env com suas configurações
```

4. **Configure o banco de dados**
```bash
php scripts/setup-database.php
```

5. **Execute as migrations**
```bash
php scripts/migrate.php
```

## 🔧 Configuração

### Variáveis de Ambiente (.env)
```env
# Database
DB_HOST=localhost
DB_NAME=admin_panel
DB_USER=root
DB_PASS=

# Asaas Integration
ASAAS_API_KEY=sua_chave_api
ASAAS_ENVIRONMENT=sandbox

# Deployment
DEPLOY_PATH=/home/user/public_html/
DEPLOY_URL=https://seudominio.com/
```

### GitHub Secrets (para deploy automático)
- `HOSTINGER_FTP_HOST`
- `HOSTINGER_FTP_USER`
- `HOSTINGER_FTP_PASS`
- `HOSTINGER_DB_HOST`
- `HOSTINGER_DB_USER`
- `HOSTINGER_DB_PASS`

## 📊 Funcionalidades

### Painel Administrativo
- ✅ Gestão de clientes
- ✅ Pipeline de vendas
- ✅ Integração Asaas (cobranças)
- ✅ Sistema de suporte
- ✅ Deploy automático
- ✅ Backup automático

### Tipos de Sites
- 🛒 **E-commerce**: Petshop, Eletro, Produtos Naturais
- 🏢 **Institucional**: Advogados, Turismo, Imobiliárias

## 🚀 Deploy Automático

O sistema usa GitHub Actions para deploy automático:

1. **Push para main** → Deploy automático
2. **Atualização de todos os clientes**
3. **Backup automático** antes do deploy
4. **Rollback** em caso de erro

## 📈 Escalabilidade

- **Código compartilhado**: 1 correção = todos atualizados
- **Bancos separados**: Isolamento por cliente
- **Templates flexíveis**: Personalização por nicho
- **Deploy otimizado**: Atualizações em massa

## 🔒 Segurança

- Autenticação multi-tenant
- Isolamento de dados por cliente
- Backup automático
- Logs de auditoria
- HTTPS obrigatório

## 📞 Suporte

Para suporte técnico:
- Email: suporte@seudominio.com
- WhatsApp: (11) 99999-9999
- Documentação: `/docs/`

## 📄 Licença

Este projeto é proprietário. Todos os direitos reservados.

---

**Desenvolvido para facilitar a revenda de sites com manutenção centralizada.**

---

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