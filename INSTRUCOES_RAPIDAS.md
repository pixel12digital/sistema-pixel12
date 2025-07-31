# 🚀 Instruções Rápidas - Canais WhatsApp

## 📋 Resumo do que foi feito

✅ **Canal Financeiro (3000)**: Corrigido e funcionando  
✅ **Endpoint /send**: Implementado  
✅ **Formato de número**: Corrigido (@c.us)  
✅ **URL problemática**: Removida  
✅ **Scripts automatizados**: Criados  

## 🔧 Como Adicionar Novos Canais

### Opção 1: Script Automatizado (Recomendado)

```bash
# Na VPS, execute:
chmod +x criar_novo_canal.sh
./criar_novo_canal.sh 3001 "Canal Comercial"
./criar_novo_canal.sh 3002 "Canal Suporte"
./criar_novo_canal.sh 3003 "Canal Marketing"
```

### Opção 2: Manual

```bash
# 1. Criar diretório
mkdir -p /var/whatsapp-api-canal-3001

# 2. Copiar arquivos
cp -r /var/whatsapp-api/* /var/whatsapp-api-canal-3001/

# 3. Alterar porta
cd /var/whatsapp-api-canal-3001
sed -i 's/const PORT = 3000/const PORT = 3001/' whatsapp-api-server.js

# 4. Criar ecosystem.config.js
cat > ecosystem.config.js << 'EOF'
module.exports = {
  apps: [{
    name: 'whatsapp-api-3001',
    script: 'whatsapp-api-server.js',
    cwd: '/var/whatsapp-api-canal-3001',
    instances: 1,
    autorestart: true,
    watch: false,
    max_memory_restart: '1G',
    env: {
      NODE_ENV: 'production',
      PORT: 3001
    }
  }]
}
EOF

# 5. Iniciar
pm2 start ecosystem.config.js
ufw allow 3001
```

## 📱 Atualizar Sistema PHP

```bash
# Executar script de atualização
php atualizar_configuracao_canais.php
```

## 🧪 Testar Canais

```bash
# Testar todos os canais
php teste_todos_canais.php

# Testar canal específico
php teste_canal_3001.php
```

## 🛠️ Comandos Úteis

### PM2 - Gerenciamento
```bash
# Listar canais
pm2 list

# Reiniciar canal
pm2 restart whatsapp-api-3001

# Ver logs
pm2 logs whatsapp-api-3001 --lines 20

# Monitorar
pm2 monit
```

### Verificação
```bash
# Verificar portas
netstat -tlnp | grep :300

# Testar conectividade
curl http://212.85.11.238:3001/status

# Testar envio
curl -X POST http://212.85.11.238:3001/send \
  -H 'Content-Type: application/json' \
  -d '{"to":"4796164699@c.us","message":"Teste"}'
```

## 📊 Status dos Canais

| Porta | Nome | Status | URL |
|-------|------|--------|-----|
| 3000 | Canal Financeiro | ✅ Online | http://212.85.11.238:3000 |
| 3001 | Canal Comercial | 🔄 Pendente | http://212.85.11.238:3001 |
| 3002 | Canal Suporte | 🔄 Pendente | http://212.85.11.238:3002 |
| 3003 | Canal Marketing | 🔄 Pendente | http://212.85.11.238:3003 |

## 🚨 Troubleshooting

### Canal não inicia
```bash
pm2 logs whatsapp-api-3001 --lines 20
netstat -tlnp | grep :3001
```

### Mensagens não são enviadas
```bash
# Verificar formato do número (@c.us)
# Verificar logs
pm2 logs whatsapp-api-3001 --lines 10
```

### Erro de conectividade
```bash
curl http://212.85.11.238:3001/status
ufw status
```

## 📁 Arquivos Importantes

- `README_CANAL_FINANCEIRO.md` - Documentação completa
- `criar_novo_canal.sh` - Script automatizado
- `atualizar_configuracao_canais.php` - Atualizar sistema PHP
- `teste_todos_canais.php` - Teste de todos os canais
- `teste_canal_3001.php` - Teste de canal específico

## 🎯 Checklist Rápido

- [ ] Executar `criar_novo_canal.sh` na VPS
- [ ] Escanear QR Code do novo canal
- [ ] Executar `atualizar_configuracao_canais.php`
- [ ] Testar com `teste_todos_canais.php`
- [ ] Verificar no painel administrativo

## 💡 Dicas Importantes

1. **Formato de número**: Sempre use `@c.us` no final
2. **Portas**: Use 3001, 3002, 3003, etc.
3. **Firewall**: Abra as portas necessárias
4. **Logs**: Monitore sempre os logs do PM2
5. **Backup**: Faça backup antes de alterações

---

**Última atualização**: 31/07/2025  
**Status**: Canal Financeiro Operacional ✅ 