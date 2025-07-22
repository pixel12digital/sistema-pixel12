# 🔧 COMANDOS PARA RESOLVER CONFLITO NO SERVIDOR

## ✅ **SITUAÇÃO ATUAL**

Você está conectado via SSH no servidor de produção e o conflito está confirmado:
- `config.php` tem mudanças locais
- `painel/config.php` tem mudanças locais

## 🚀 **COMANDOS PARA EXECUTAR NO SERVIDOR**

### **1. Fazer Backup das Configurações Atuais**
```bash
cp config.php config.php.backup.$(date +%Y%m%d_%H%M%S)
cp painel/config.php painel/config.php.backup.$(date +%Y%m%d_%H%M%S)
```

### **2. Resetar Mudanças Locais**
```bash
git reset --hard HEAD
git clean -fd
```

### **3. Fazer Pull da Versão Limpa**
```bash
git pull origin master
```

### **4. Restaurar Configurações de Produção**
```bash
cp config.php.backup.* config.php
cp painel/config.php.backup.* painel/config.php
```

### **5. Verificar Status**
```bash
git status
```

### **6. Testar Configurações**
```bash
php -l config.php
php -l painel/config.php
```

## 📋 **SEQUÊNCIA COMPLETA DE COMANDOS**

Execute estes comandos um por vez no servidor:

```bash
# 1. Backup
cp config.php config.php.backup.$(date +%Y%m%d_%H%M%S)
cp painel/config.php painel/config.php.backup.$(date +%Y%m%d_%H%M%S)

# 2. Reset
git reset --hard HEAD
git clean -fd

# 3. Pull
git pull origin master

# 4. Restore
cp config.php.backup.* config.php
cp painel/config.php.backup.* painel/config.php

# 5. Status
git status

# 6. Test
php -l config.php
php -l painel/config.php
```

## 🎯 **RESULTADO ESPERADO**

Após executar os comandos:
- ✅ Sistema atualizado com a versão limpa
- ✅ Configurações de produção mantidas
- ✅ 112 arquivos removidos com segurança
- ✅ Projeto organizado e otimizado

## 🔍 **VERIFICAÇÕES PÓS-DEPLOY**

### **1. Verificar Status Git**
```bash
git status
# Deve mostrar: "nothing to commit, working tree clean"
```

### **2. Testar URLs**
- Sistema principal: https://app.pixel12digital.com.br
- Painel: https://app.pixel12digital.com.br/painel
- APIs: https://app.pixel12digital.com.br/api

### **3. Verificar Logs**
```bash
tail -f logs/debug_cobrancas.log
tail -f logs/sincroniza_asaas_debug.log
```

## 🚨 **EM CASO DE PROBLEMAS**

### **Problema: Configurações perdidas**
```bash
# Listar backups
ls -la config.php.backup.*

# Restaurar backup mais recente
cp config.php.backup.MAIS_RECENTE config.php
cp painel/config.php.backup.MAIS_RECENTE painel/config.php
```

### **Problema: Sistema não carrega**
```bash
# Verificar permissões
chmod 644 config.php
chmod 644 painel/config.php

# Verificar sintaxe PHP
php -l config.php
php -l painel/config.php
```

---

**Status**: ⏳ Aguardando execução dos comandos no servidor  
**Próximo passo**: Executar a sequência de comandos acima 