# 🚀 COMANDOS CORRETOS PARA EXECUTAR NO VPS

**VPS:** 212.85.11.238  
**Data:** 04/08/2025  
**Problema:** Script não encontrado + Webhook com erro 400

---

## 🔧 **PASSO 1: NAVEGAR PARA DIRETÓRIO CORRETO**

```bash
# Você está em /root, precisa ir para o diretório do projeto
cd /var/www/html/loja-virtual-revenda

# Verificar se está no local correto
pwd
ls -la
```

**Saída esperada:**
```
/var/www/html/loja-virtual-revenda
total XX
drwxr-xr-x  XX www-data www-data 4096 Aug  4 15:30 .
drwxr-xr-x  XX root     root     4096 Aug  4 15:30 ..
-rw-r--r--   1 www-data www-data  XXXX config.php
-rw-r--r--   1 www-data www-data  XXXX corrigir_erro_coluna_banco.php
-rw-r--r--   1 www-data www-data  XXXX executar_correcao_vps.sh
...
```

---

## 🔧 **PASSO 2: VERIFICAR ARQUIVOS**

```bash
# Verificar se os arquivos estão lá
ls -la corrigir_erro_coluna_banco.php
ls -la executar_correcao_vps.sh
ls -la config.php
```

**Se algum arquivo não existir:**
```bash
# Verificar se está no diretório correto
find /var/www -name "config.php" 2>/dev/null
find /var/www -name "corrigir_erro_coluna_banco.php" 2>/dev/null
```

---

## 🔧 **PASSO 3: CONFIGURAR PERMISSÕES**

```bash
# Configurar proprietário
chown www-data:www-data corrigir_erro_coluna_banco.php
chown www-data:www-data executar_correcao_vps.sh

# Configurar permissões
chmod 750 corrigir_erro_coluna_banco.php
chmod +x executar_correcao_vps.sh

# Verificar permissões
ls -la corrigir_erro_coluna_banco.php
ls -la executar_correcao_vps.sh
```

---

## 🔧 **PASSO 4: EXECUTAR CORREÇÃO**

### **Opção A - Execução Manual:**
```bash
# Executar script PHP
php -d max_execution_time=300 corrigir_erro_coluna_banco.php
```

### **Opção B - Execução Automática:**
```bash
# Executar script bash
./executar_correcao_vps.sh
```

---

## 🚨 **PROBLEMA IDENTIFICADO NOS LOGS**

### **Erro Atual:**
```
❌ Erro ao enviar webhook - Status: 400
❌ Erro: {"success":false,"error":"Dados incompletos"}
```

### **Causa:**
O WhatsApp robot está enviando dados no formato:
```json
{
  "event": "onmessage",
  "data": {
    "from": "554796164699",
    "text": "mensagem",
    "type": "chat",
    "timestamp": 1754331667,
    "session": "default"
  }
}
```

Mas o webhook espera:
```json
{
  "from": "554796164699@c.us",
  "body": "mensagem",
  "timestamp": 1754331667
}
```

---

## 🔧 **PASSO 5: VERIFICAR FORMATO DE DADOS**

```bash
# Executar verificador de formato
php verificar_webhook_dados.php
```

---

## 🎯 **ORDEM DE EXECUÇÃO RECOMENDADA**

1. **✅ Navegar para diretório correto**
   ```bash
   cd /var/www/html/loja-virtual-revenda
   ```

2. **✅ Verificar arquivos**
   ```bash
   ls -la corrigir_erro_coluna_banco.php
   ```

3. **✅ Executar correção de coluna**
   ```bash
   php -d max_execution_time=300 corrigir_erro_coluna_banco.php
   ```

4. **✅ Verificar formato de dados**
   ```bash
   php verificar_webhook_dados.php
   ```

5. **✅ Corrigir formato de dados (se necessário)**
   - Ajustar webhook para aceitar formato do robot
   - OU ajustar robot para enviar formato correto

6. **✅ Testar com mensagem real**
   - Enviar mensagem para 554797146908
   - Verificar se é processada sem erro

---

## 📞 **EM CASO DE PROBLEMAS**

### **Se arquivos não existirem:**
```bash
# Procurar em outros diretórios
find /var/www -name "config.php" 2>/dev/null
find /var/www -name "*.php" | grep -i correcao
```

### **Se permissões falharem:**
```bash
# Verificar usuário atual
whoami

# Verificar usuário do servidor web
ps aux | grep apache
ps aux | grep nginx
```

### **Se PHP não funcionar:**
```bash
# Verificar versão do PHP
php -v

# Verificar se PHP está instalado
which php
```

---

## ✅ **CHECKLIST DE EXECUÇÃO**

- [ ] Navegar para `/var/www/html/loja-virtual-revenda`
- [ ] Verificar se arquivos existem
- [ ] Configurar permissões
- [ ] Executar correção de coluna
- [ ] Verificar formato de dados
- [ ] Testar webhook
- [ ] Enviar mensagem real
- [ ] Confirmar funcionamento

**Status:** 🚀 **PRONTO PARA EXECUÇÃO** 