# 🚀 Configuração de Deploy Automático

## 📋 Variáveis de Ambiente Necessárias

Para que o deploy automático funcione corretamente, você precisa configurar as seguintes variáveis de ambiente no GitHub:

### 🔐 Configurações SSH
- `VPS_SSH_PRIVATE_KEY`: Chave SSH privada para acessar a VPS
- `VPS_HOST`: Endereço IP ou domínio da VPS
- `VPS_USER`: Usuário SSH da VPS
- `VPS_PORT`: Porta SSH (padrão: 22)

### 🗄️ Configurações do Banco de Dados
- `VPS_DB_HOST`: Host do banco de dados
- `VPS_DB_PORT`: Porta do banco de dados
- `VPS_DB_USER`: Usuário do banco de dados
- `VPS_DB_PASS`: Senha do banco de dados
- `VPS_DB_NAME`: Nome do banco de dados

### ⚙️ Configurações da Aplicação
- `VPS_PORT`: Porta onde a aplicação rodará
- `VPS_JWT_SECRET`: Chave secreta para JWT
- `VPS_PROJECT_PATH`: Caminho do projeto na VPS

## 🔧 Como Configurar

### 1. Acesse as Configurações do Repositório
- Vá para `Settings` > `Secrets and variables` > `Actions`
- Clique em `New repository secret`

### 2. Configure a Chave SSH
```bash
# Na sua máquina local, gere uma nova chave SSH
ssh-keygen -t rsa -b 4096 -C "github-actions@sistema-pixel12"

# Copie a chave pública para a VPS
ssh-copy-id -i ~/.ssh/id_rsa.pub usuario@ip-da-vps

# Copie a chave privada (conteúdo do arquivo ~/.ssh/id_rsa)
cat ~/.ssh/id_rsa
```

### 3. Adicione Todas as Variáveis
```
VPS_SSH_PRIVATE_KEY: [conteúdo da chave privada]
VPS_HOST: [IP ou domínio da VPS]
VPS_USER: [usuário SSH]
VPS_PORT: [porta SSH, padrão 22]
VPS_DB_HOST: [host do banco]
VPS_DB_PORT: [porta do banco]
VPS_DB_USER: [usuário do banco]
VPS_DB_PASS: [senha do banco]
VPS_DB_NAME: [nome do banco]
VPS_JWT_SECRET: [chave secreta JWT]
VPS_PROJECT_PATH: [caminho do projeto na VPS]
```

## 🚨 Solução de Problemas

### Erro: "Error loading key "(stdin)": incomplete message"
Este erro geralmente ocorre quando:
1. A chave SSH está corrompida
2. A chave não foi copiada completamente
3. Há caracteres especiais na chave

**Solução:**
- Gere uma nova chave SSH
- Certifique-se de copiar a chave completa (incluindo as linhas BEGIN e END)
- Verifique se não há espaços extras

### Erro: "Permission denied (publickey)"
Este erro indica problemas de autenticação SSH:
1. Chave pública não foi adicionada à VPS
2. Permissões incorretas na VPS
3. Usuário SSH incorreto

**Solução:**
```bash
# Na VPS, verifique as permissões
chmod 700 ~/.ssh
chmod 600 ~/.ssh/authorized_keys

# Verifique se a chave está correta
cat ~/.ssh/authorized_keys
```

## 📝 Exemplo de Configuração na VPS

### 1. Estrutura de Diretórios
```bash
/home/usuario/sistema-pixel12/
├── .env
├── package.json
├── server.js
└── src/
```

### 2. Configuração do Serviço Systemd
```bash
# Crie o arquivo de serviço
sudo nano /etc/systemd/system/pixel12-api.service

# Conteúdo do arquivo:
[Unit]
Description=Sistema Pixel12 API
After=network.target

[Service]
Type=simple
User=usuario
WorkingDirectory=/home/usuario/sistema-pixel12
ExecStart=/usr/bin/node server.js
Restart=always
RestartSec=10
Environment=NODE_ENV=production

[Install]
WantedBy=multi-user.target

# Ative o serviço
sudo systemctl enable pixel12-api
sudo systemctl start pixel12-api
```

## ✅ Verificação do Deploy

Após o deploy, verifique:
1. **Logs do serviço**: `sudo journalctl -u pixel12-api -f`
2. **Status do serviço**: `sudo systemctl status pixel12-api`
3. **Porta da aplicação**: `netstat -tlnp | grep :3000`
4. **Logs da aplicação**: `tail -f logs/app.log`

## 🔄 Re-deploy Manual

Se precisar fazer um re-deploy manual:
```bash
# Na VPS
cd /home/usuario/sistema-pixel12
git pull origin master
npm ci --production
sudo systemctl restart pixel12-api
```

## 📞 Suporte

Em caso de problemas:
1. Verifique os logs do GitHub Actions
2. Consulte os logs da VPS
3. Teste a conexão SSH manualmente
4. Verifique as permissões e configurações
