# 🚀 GUIA RÁPIDO - RESOLVER CONFLITO DE DEPLOY

## ❌ **PROBLEMA ATUAL**

O arquivo `resolver_conflito_deploy.php` não está no servidor de produção, por isso aparece "Página não encontrada".

## ✅ **SOLUÇÃO RÁPIDA**

### **Opção 1: Upload Manual (Recomendado)**

1. **Fazer download** do arquivo `resolver_conflito_deploy.php` do repositório
2. **Fazer upload** via File Manager do Hostinger para:
   ```
   /home/u342734079/public_html/app/
   ```
3. **Executar** via SSH:
   ```bash
   cd /home/u342734079/public_html/app
   php resolver_conflito_deploy.php
   ```

### **Opção 2: Comandos Manuais (Alternativa)**

Se preferir resolver sem o script:

```bash
# 1. Conectar via SSH ao servidor
ssh u342734079@us-phx-web1607.hostinger.com

# 2. Navegar para o diretório
cd /home/u342734079/public_html/app

# 3. Fazer backup das configurações
cp config.php config.php.backup.$(date +%Y%m%d_%H%M%S)
cp painel/config.php painel/config.php.backup.$(date +%Y%m%d_%H%M%S)

# 4. Resetar mudanças locais
git reset --hard HEAD
git clean -fd

# 5. Fazer pull da versão limpa
git pull origin master

# 6. Restaurar configurações de produção
cp config.php.backup.* config.php
cp painel/config.php.backup.* painel/config.php

# 7. Verificar status
git status
```

## 📋 **PASSOS DETALHADOS**

### **1. Download do Arquivo**
- Acesse: https://github.com/pixel12digital/revenda-sites
- Navegue até o arquivo `resolver_conflito_deploy.php`
- Clique em "Raw" e salve o arquivo

### **2. Upload via Hostinger**
- Acesse o painel do Hostinger
- Vá em "File Manager"
- Navegue até `/public_html/app/`
- Faça upload do arquivo `resolver_conflito_deploy.php`

### **3. Execução via SSH**
```bash
# Conectar ao servidor
ssh u342734079@us-phx-web1607.hostinger.com

# Navegar para o diretório
cd /home/u342734079/public_html/app

# Executar o script
php resolver_conflito_deploy.php
```

## 🔍 **VERIFICAÇÕES PÓS-DEPLOY**

### **1. Testar URLs**
- ✅ Sistema principal: https://app.pixel12digital.com.br
- ✅ Painel: https://app.pixel12digital.com.br/painel
- ✅ APIs: https://app.pixel12digital.com.br/api

### **2. Verificar Status Git**
```bash
git status
# Deve mostrar: "nothing to commit, working tree clean"
```

### **3. Testar Funcionalidades**
- ✅ Login no painel
- ✅ Carregamento de faturas
- ✅ Sincronização com Asaas
- ✅ Envio de mensagens WhatsApp

## 🚨 **EM CASO DE PROBLEMAS**

### **Problema: Script não executa**
```bash
# Verificar se o arquivo existe
ls -la resolver_conflito_deploy.php

# Verificar permissões
chmod 755 resolver_conflito_deploy.php

# Executar com debug
php -f resolver_conflito_deploy.php
```

### **Problema: Configurações perdidas**
```bash
# Listar backups
ls -la config.php.backup.*

# Restaurar backup mais recente
cp config.php.backup.MAIS_RECENTE config.php
cp painel/config.php.backup.MAIS_RECENTE painel/config.php
```

## 🎯 **RESULTADO ESPERADO**

Após executar o script:
- ✅ Sistema atualizado com a versão limpa
- ✅ Configurações de produção mantidas
- ✅ 112 arquivos removidos com segurança
- ✅ Projeto organizado e otimizado

## 📞 **SUPORTE RÁPIDO**

**Se precisar de ajuda**:
1. Verificar se o arquivo foi enviado corretamente
2. Executar comandos manualmente se o script falhar
3. Verificar logs do sistema
4. Restaurar backups se necessário

---

**Status**: ⏳ Aguardando upload do arquivo para o servidor  
**Próximo passo**: Fazer upload do `resolver_conflito_deploy.php` via File Manager 