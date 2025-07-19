# 🔧 INSTRUÇÕES PARA RESOLVER CONFLITO DE DEPLOY

## ❌ **PROBLEMA IDENTIFICADO**

O deploy falhou porque o servidor de produção tem mudanças locais nos arquivos:
- `config.php`
- `painel/config.php`

**Erro**: `Your local changes to the following files would be overwritten by merge`

## ✅ **SOLUÇÃO**

### **Opção 1: Usar Script Automático (Recomendado)**

1. **Fazer upload** do arquivo `resolver_conflito_deploy.php` para o servidor
2. **Executar** via SSH:
   ```bash
   php resolver_conflito_deploy.php
   ```

### **Opção 2: Resolver Manualmente**

1. **Fazer backup** das configurações atuais:
   ```bash
   cp config.php config.php.backup.$(date +%Y%m%d_%H%M%S)
   cp painel/config.php painel/config.php.backup.$(date +%Y%m%d_%H%M%S)
   ```

2. **Resetar mudanças locais**:
   ```bash
   git reset --hard HEAD
   git clean -fd
   ```

3. **Fazer pull** da versão limpa:
   ```bash
   git pull origin master
   ```

4. **Restaurar configurações** de produção:
   ```bash
   cp config.php.backup.* config.php
   cp painel/config.php.backup.* painel/config.php
   ```

5. **Verificar status**:
   ```bash
   git status
   ```

## 📋 **COMANDOS COMPLETOS PARA SSH**

```bash
# 1. Navegar para o diretório do projeto
cd /home/u342734079/public_html/app

# 2. Fazer backup das configurações
cp config.php config.php.backup.$(date +%Y%m%d_%H%M%S)
cp painel/config.php painel/config.php.backup.$(date +%Y%m%d_%H%M%S)

# 3. Resetar mudanças locais
git reset --hard HEAD
git clean -fd

# 4. Fazer pull da versão limpa
git pull origin master

# 5. Restaurar configurações de produção
cp config.php.backup.* config.php
cp painel/config.php.backup.* painel/config.php

# 6. Verificar status
git status

# 7. Testar sistema
php -l config.php
php -l painel/config.php
```

## 🔍 **VERIFICAÇÕES PÓS-DEPLOY**

### **1. Testar Configurações**
```bash
# Verificar se config.php carrega
php -l config.php

# Verificar se painel/config.php carrega
php -l painel/config.php
```

### **2. Testar URLs**
- **Sistema principal**: https://app.pixel12digital.com.br
- **Painel administrativo**: https://app.pixel12digital.com.br/painel
- **APIs**: https://app.pixel12digital.com.br/api

### **3. Verificar Logs**
```bash
# Ver logs recentes
tail -f logs/debug_cobrancas.log

# Ver logs de sincronização
tail -f logs/sincroniza_asaas_debug.log
```

### **4. Testar Funcionalidades Críticas**
- ✅ Login no painel
- ✅ Carregamento de faturas
- ✅ Sincronização com Asaas
- ✅ Envio de mensagens WhatsApp
- ✅ Webhooks

## 🚨 **EM CASO DE PROBLEMAS**

### **Problema: Configurações perdidas**
```bash
# Restaurar backup mais recente
ls -la config.php.backup.*
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

### **Problema: Banco de dados não conecta**
```bash
# Verificar configurações de banco
grep -n "DB_" config.php
grep -n "DB_" painel/config.php
```

## 📊 **STATUS ESPERADO APÓS RESOLUÇÃO**

```bash
$ git status
On branch master
Your branch is up to date with 'origin/master'.

nothing to commit, working tree clean
```

## 🎯 **RESULTADO FINAL**

Após resolver o conflito, você terá:
- ✅ **Sistema atualizado** com a versão limpa
- ✅ **Configurações de produção** mantidas
- ✅ **112 arquivos removidos** com segurança
- ✅ **Projeto organizado** e otimizado
- ✅ **Deploy bem-sucedido**

## 📞 **SUPORTE**

Se houver problemas:
1. Verificar logs do sistema
2. Restaurar backups se necessário
3. Verificar configurações de banco de dados
4. Testar funcionalidades uma por uma

---

**Data**: 18/07/2025  
**Status**: ⏳ Aguardando resolução do conflito  
**Próximo passo**: Executar script de resolução no servidor 