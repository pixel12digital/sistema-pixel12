# 🚀 INSTRUÇÕES DE EXECUÇÃO - VPS PRODUÇÃO

## 📋 **PRÉ-REQUISITOS**

### **1. Acesso ao VPS**
```bash
ssh root@212.85.11.238
```

### **2. Verificar Diretório de Trabalho**
```bash
cd /var/www/html/loja-virtual-revenda
pwd
ls -la
```

### **3. Verificar Arquivos Necessários**
```bash
# Verificar se config.php existe
ls -la config.php

# Verificar se o script de correção existe
ls -la corrigir_erro_coluna_banco.php
```

### **4. 🔧 CONFIGURAR PERMISSÕES (IMPORTANTE)**
```bash
# Definir proprietário correto (www-data ou apache)
chown www-data:www-data corrigir_erro_coluna_banco.php

# Definir permissões seguras
chmod 750 corrigir_erro_coluna_banco.php

# Verificar permissões
ls -la corrigir_erro_coluna_banco.php
```

---

## 🔧 **EXECUÇÃO DO SCRIPT DE CORREÇÃO**

### **1. Executar Script (COM TIMEOUT)**
```bash
# Execução com timeout aumentado (recomendado)
php -d max_execution_time=300 corrigir_erro_coluna_banco.php

# OU execução normal (se tabela for pequena)
php corrigir_erro_coluna_banco.php
```

### **2. Saídas Esperadas**

#### **✅ CASO SUCESSO:**
```
=== 🔧 CORREÇÃO DE ERRO DE COLUNA (PRODUÇÃO) ===
Data/Hora: 2025-08-04 16:30:00
Diretório: /var/www/html/loja-virtual-revenda

✅ Conectado ao banco de dados: u342734079_revendaweb

1. 📋 VERIFICANDO ESTRUTURA DA TABELA:
   • id (int(11))
   • canal_id (int(11))
   • numero_whatsapp (varchar(20))
   • telefone_origem (varchar(20))  ← NOVA COLUNA

   ❌ Coluna 'telefone_origem' não encontrada

2. 💾 CRIANDO BACKUP AUTOMÁTICO:
   ✅ Backup criado: mensagens_comunicacao_backup_20250804_163000 (1234 registros)

3. 🔧 ADICIONANDO COLUNA 'telefone_origem' (COM TRANSAÇÃO):
   ✅ Coluna 'telefone_origem' adicionada com sucesso!
   ✅ Verificação: Coluna confirmada no banco
   ✅ Transação confirmada

[...]

8. 📊 RESUMO DA CORREÇÃO:
   ✅ Conexão com banco: OK
   ✅ Estrutura da tabela: Verificada
   ✅ Coluna telefone_origem: Adicionada com sucesso
   ✅ Backup: Criado (mensagens_comunicacao_backup_20250804_163000)
   ✅ Teste de inserção: OK
   ✅ Código analisado: 2 arquivo(s) usam a coluna

=== FIM DA CORREÇÃO ===
Status: ✅ SUCESSO
```

#### **❌ CASO ERRO:**
```
❌ ERRO CRÍTICO: Erro ao adicionar coluna: Duplicate column name 'telefone_origem'
🔧 Ação recomendada: Verificar logs e tentar novamente
Status: ❌ FALHOU
```

---

## 🧪 **TESTE PÓS-CORREÇÃO**

### **1. Testar Webhook Novamente**
```bash
# Testar endpoint que estava com erro
curl -X POST https://app.pixel12digital.com.br/painel/receber_mensagem_ana_local.php \
  -H "Content-Type: application/json" \
  -d '{"from":"554796164699@c.us","body":"Teste pós-correção","timestamp":'$(date +%s)'}'
```

### **2. Verificar Logs**
```bash
# Verificar logs do webhook
tail -f /var/www/html/loja-virtual-revenda/painel/debug_ajax_whatsapp.log

# Verificar logs de erro do PHP
tail -f /var/log/apache2/error.log
```

### **3. Testar Mensagem Real**
1. **Enviar mensagem WhatsApp** para: `554797146908`
2. **Verificar se é recebida** sem erro
3. **Confirmar resposta** da Ana
4. **Validar gravação** no banco

---

## 📊 **VERIFICAÇÕES ADICIONAIS**

### **1. Verificar Estrutura da Tabela**
```sql
DESCRIBE mensagens_comunicacao;
```

### **2. Verificar Backup Criado**
```sql
SHOW TABLES LIKE 'mensagens_comunicacao_backup%';
```

### **3. Testar Inserção Manual**
```sql
INSERT INTO mensagens_comunicacao 
(canal_id, numero_whatsapp, telefone_origem, mensagem, tipo, data_hora, direcao, status) 
VALUES (36, '554796164699', '554796164699', 'Teste manual', 'texto', NOW(), 'recebido', 'teste');

SELECT * FROM mensagens_comunicacao WHERE mensagem = 'Teste manual';
DELETE FROM mensagens_comunicacao WHERE mensagem = 'Teste manual';
```

---

## 🧹 **LIMPEZA (OPCIONAL)**

### **1. Remover Backup Após Confirmação**
```sql
-- APENAS APÓS CONFIRMAR QUE TUDO FUNCIONA
DROP TABLE mensagens_comunicacao_backup_20250804_163000;
```

### **2. Verificar Espaço em Disco**
```bash
df -h
du -sh /var/www/html/loja-virtual-revenda/
```

### **3. 🔧 REMOVER SCRIPT APÓS EXECUÇÃO (SEGURANÇA)**
```bash
# Após confirmar que tudo funcionou, remover o script por segurança
rm corrigir_erro_coluna_banco.php
echo "Script removido por segurança"
```

---

## 🚨 **EM CASO DE PROBLEMAS**

### **1. Erro de Conexão com Banco**
```bash
# Verificar se MySQL está rodando
systemctl status mysql

# Verificar credenciais
cat /var/www/html/loja-virtual-revenda/config.php | grep DB_
```

### **2. Erro de Permissões**
```bash
# Verificar permissões do arquivo
ls -la corrigir_erro_coluna_banco.php

# Ajustar se necessário
chmod 644 corrigir_erro_coluna_banco.php
chown www-data:www-data corrigir_erro_coluna_banco.php
```

### **3. Erro de Sintaxe PHP**
```bash
# Verificar sintaxe
php -l corrigir_erro_coluna_banco.php
```

### **4. ⏰ Timeout de Execução**
```bash
# Se o script demorar muito, aumentar timeout
php -d max_execution_time=600 corrigir_erro_coluna_banco.php

# Verificar configuração atual
php -i | grep max_execution_time
```

### **5. 💾 Problemas de Backup**
```bash
# Verificar espaço em disco antes do backup
df -h

# Verificar tamanho da tabela
mysql -u u342734079_revendaweb -p u342734079_revendaweb -e "SELECT COUNT(*) as total FROM mensagens_comunicacao;"
```

---

## 📞 **CONTATO EM CASO DE EMERGÊNCIA**

Se houver problemas críticos:
1. **NÃO** executar comandos adicionais
2. **Documentar** o erro exato
3. **Fazer screenshot** da saída
4. **Contatar** suporte técnico

---

## ✅ **CHECKLIST FINAL**

- [ ] Script executado com sucesso
- [ ] Backup criado automaticamente
- [ ] Coluna `telefone_origem` adicionada
- [ ] Teste de inserção funcionando
- [ ] Webhook testado sem erros
- [ ] Mensagem real enviada e processada
- [ ] Logs verificados
- [ ] Sistema funcionando normalmente
- [ ] Script removido por segurança (opcional)

**Status Final:** ✅ **PRONTO PARA PRODUÇÃO** 