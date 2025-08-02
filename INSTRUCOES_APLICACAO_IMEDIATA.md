# 🚨 Instruções de Aplicação Imediata - WhatsApp API

## 📋 Problema Identificado
- PM2 não encontra `ecosystem.config.js`
- Scripts de automação não estão presentes
- Arquivos podem estar no diretório errado
- Deploy incompleto

## 🔧 Solução Imediata

### Passo 1: Verificar Estrutura Atual
```bash
# Verificar diretório atual
pwd

# Listar arquivos
ls -la

# Verificar se estamos no local correto
cd /var/whatsapp-api
ls -la
```

### Passo 2: Executar Diagnóstico
```bash
# Se os scripts não existem, execute manualmente:
echo "🔍 Verificando estrutura..."

# Verificar se ecosystem.config.js existe
if [ -f "ecosystem.config.js" ]; then
    echo "✅ ecosystem.config.js encontrado"
    cat ecosystem.config.js | head -20
else
    echo "❌ ecosystem.config.js NÃO encontrado"
fi

# Verificar se whatsapp-api-server.js existe
if [ -f "whatsapp-api-server.js" ]; then
    echo "✅ whatsapp-api-server.js encontrado"
else
    echo "❌ whatsapp-api-server.js NÃO encontrado"
fi
```

### Passo 3: Corrigir Estrutura de Arquivos

#### Se ecosystem.config.js não existe:
```bash
# Criar ecosystem.config.js
cat > ecosystem.config.js << 'EOF'
module.exports = {
  apps: [
    {
      name: 'whatsapp-3000',
      script: 'whatsapp-api-server.js',
      instances: 1,
      exec_mode: 'fork',
      autorestart: true,
      watch: false,
      max_memory_restart: '1G',
      env: {
        NODE_ENV: 'production',
        PORT: 3000
      },
      log_date_format: 'YYYY-MM-DD HH:mm:ss Z',
      log_file: './logs/combined-3000.log',
      out_file: './logs/out-3000.log',
      error_file: './logs/error-3000.log',
      max_restarts: 10,
      restart_delay: 5000,
      exp_backoff_restart_delay: 100,
      kill_timeout: 5000,
      pmx: true,
      node_args: '--max-old-space-size=2048',
      ignore_watch: ['node_modules', 'sessions', 'logs', 'tmp'],
      merge_logs: true,
      time: true,
      cron_restart: '0 4 * * *',
      health_check_grace_period: 3000,
      health_check_fatal_exceptions: true
    },
    {
      name: 'whatsapp-3001',
      script: 'whatsapp-api-server.js',
      instances: 1,
      exec_mode: 'fork',
      autorestart: true,
      watch: false,
      max_memory_restart: '1G',
      env: {
        NODE_ENV: 'production',
        PORT: 3001
      },
      log_date_format: 'YYYY-MM-DD HH:mm:ss Z',
      log_file: './logs/combined-3001.log',
      out_file: './logs/out-3001.log',
      error_file: './logs/error-3001.log',
      max_restarts: 10,
      restart_delay: 5000,
      exp_backoff_restart_delay: 100,
      kill_timeout: 5000,
      pmx: true,
      node_args: '--max-old-space-size=2048',
      ignore_watch: ['node_modules', 'sessions', 'logs', 'tmp'],
      merge_logs: true,
      time: true,
      cron_restart: '0 4 * * *',
      health_check_grace_period: 3000,
      health_check_fatal_exceptions: true
    }
  ]
};
EOF
```

#### Se whatsapp-api-server.js não existe:
```bash
# Você precisa copiar o arquivo do repositório
# Ou fazer um git pull/clone
echo "⚠️ whatsapp-api-server.js não encontrado!"
echo "   Você precisa copiar o arquivo do repositório"
echo "   ou fazer um git pull/clone do projeto"
```

### Passo 4: Criar Diretórios Necessários
```bash
# Criar diretório de logs
mkdir -p logs

# Criar diretório de sessões
mkdir -p sessions
```

### Passo 5: Verificar Dependências
```bash
# Verificar se package.json existe
if [ ! -f "package.json" ]; then
    echo "📄 Criando package.json..."
    cat > package.json << 'EOF'
{
  "name": "whatsapp-api",
  "version": "1.0.0",
  "description": "WhatsApp Multi-Session API",
  "main": "whatsapp-api-server.js",
  "scripts": {
    "start": "node whatsapp-api-server.js"
  },
  "dependencies": {
    "whatsapp-web.js": "^1.23.0",
    "express": "^4.18.2",
    "cors": "^2.8.5",
    "qrcode-terminal": "^0.12.0",
    "fs-extra": "^11.1.1",
    "multer": "^1.4.5-lts.1"
  }
}
EOF
    npm install
fi
```

### Passo 6: Iniciar PM2
```bash
# Parar processos existentes
pm2 delete all

# Iniciar com caminho absoluto
pm2 start $(pwd)/ecosystem.config.js

# Salvar configuração
pm2 save

# Verificar status
pm2 list
```

### Passo 7: Verificar Inicialização
```bash
# Aguardar inicialização
sleep 10

# Verificar portas
ss -tlnp | grep :3000
ss -tlnp | grep :3001

# Testar conectividade local
curl -s http://127.0.0.1:3000/status | head -5
curl -s http://127.0.0.1:3001/status | head -5

# Verificar logs
pm2 logs whatsapp-3001 --lines 20 --nostream
```

## 🧪 Testes de Verificação

### Teste 1: Verificar se PM2 está rodando
```bash
pm2 list
```

### Teste 2: Verificar se as portas estão sendo escutadas
```bash
ss -tlnp | grep :3001
```

### Teste 3: Testar conectividade local
```bash
curl -s http://127.0.0.1:3001/qr?session=comercial | jq .
```

### Teste 4: Verificar logs de inicialização
```bash
pm2 logs whatsapp-3001 --lines 50 | grep -E "(API rodando|Binding confirmado|Inicializando sessão)"
```

## 🚨 Se Ainda Há Problemas

### Se ecosystem.config.js não é encontrado:
```bash
# Verificar se está no diretório correto
pwd
ls -la ecosystem.config.js

# Se não existe, criar conforme passo 3
```

### Se whatsapp-api-server.js não existe:
```bash
# Você precisa copiar o arquivo do repositório
# Ou fazer um git pull/clone do projeto
echo "Copie o arquivo whatsapp-api-server.js do repositório"
```

### Se PM2 não inicia:
```bash
# Verificar logs de erro
pm2 logs --err

# Tentar iniciar manualmente
node whatsapp-api-server.js
```

## 📝 Resultado Esperado

Após aplicar estas correções:
- ✅ `ecosystem.config.js` presente no diretório
- ✅ `whatsapp-api-server.js` presente no diretório
- ✅ PM2 iniciando com sucesso
- ✅ Portas 3000 e 3001 sendo escutadas
- ✅ Logs mostrando inicialização correta
- ✅ Conectividade local funcionando

## 🔄 Próximos Passos

1. **Se tudo funcionar localmente**: Testar conectividade externa
2. **Se portas não funcionam**: Verificar logs do PM2
3. **Se arquivos não existem**: Copiar do repositório
4. **Se PM2 não inicia**: Verificar dependências Node.js 