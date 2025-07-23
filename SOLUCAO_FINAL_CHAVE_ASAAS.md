# 🆘 SOLUÇÃO DEFINITIVA: Chave API Asaas Inválida

## 🚨 **STATUS ATUAL**
- ❌ **Chave atual**: INVÁLIDA (confirmed via API test)
- ❌ **Sincronização**: FALHANDO
- ❌ **Sistema**: Não consegue conectar com Asaas

---

## 🔑 **GERAR NOVA CHAVE (OBRIGATÓRIO)**

### **Passo 1: Acessar Painel Asaas**
1. 🌐 Acesse: https://app.asaas.com
2. 🔐 Login com suas credenciais
3. 📱 Complete autenticação 2FA se solicitado

### **Passo 2: Gerar Nova Chave**
1. No menu lateral → **"Configurações"**
2. Clique em **"API"** 
3. Na seção **"Chaves de API"**:
   - ❌ **DESATIVE** a chave atual
   - ➕ Clique em **"Gerar Nova Chave"**
   - ✅ **ATIVE** a nova chave
   - 📋 **COPIE** a nova chave

### **Passo 3: Aplicar Nova Chave**

**🔗 Via Interface Web (Recomendado):**
1. Acesse: `https://app.pixel12digital.com.br/painel/faturas.php`
2. Clique em **"🔑 Configurar API"**
3. Cole a nova chave no campo
4. Clique em **"🧪 Testar Nova Chave"**
5. Se aparecer "✅ Chave válida" → **"✅ Aplicar"**

**⚙️ Via Arquivo (Alternativo):**
1. Edite o arquivo `config.php`
2. Substitua a linha:
   ```php
   define('ASAAS_API_KEY', 'NOVA_CHAVE_AQUI');
   ```

---

## ⚡ **TESTE IMEDIATO**

Após aplicar a nova chave, execute:

```bash
cd painel
php verificar_sincronizacao.php
```

**✅ Resultado esperado:**
```
✅ Conexão OK (HTTP 200)
📊 Total de clientes no Asaas: XXX
```

---

## 🔄 **EXECUTAR SINCRONIZAÇÃO**

Quando a chave estiver válida:

```bash
php sincroniza_asaas.php
```

**✅ Resultado esperado:**
```
✅ Clientes sincronizados: XXX
✅ Cobranças sincronizadas: XXX
✅ Sincronização concluída com sucesso!
```

---

## 🛠️ **TROUBLESHOOTING**

### Se a nova chave ainda der erro:

1. **Aguarde 5 minutos** (propagação no servidor)
2. **Verifique permissões** da chave no painel Asaas
3. **Confirme que está ativa** 
4. **Teste novamente**

### Possíveis problemas:
- 🚫 **Conta suspensa/limitada**
- 🔄 **Delay de ativação** (até 15 min)
- 🔐 **Permissões insuficientes**
- 📞 **Contate suporte Asaas** se persistir

---

## ✅ **APÓS RESOLVER**

1. ✅ Sincronização funcionando
2. ✅ Faturas atualizadas automaticamente  
3. ✅ Status "Vencida" aplicado corretamente
4. ✅ Sistema operacional

**🎯 Meta**: 0 erros na sincronização! 