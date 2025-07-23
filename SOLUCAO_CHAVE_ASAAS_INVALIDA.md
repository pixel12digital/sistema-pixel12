# 🔑 Solução: Chave da API Asaas Inválida

## 🚨 **Problema Identificado**

Sua chave da API do Asaas está sendo reportada como **inválida** mesmo estando "habilitada" no painel do Asaas.

### **📊 Diagnóstico Realizado:**
- ✅ **Conectividade**: OK
- ✅ **Formato da chave**: Válido (166 caracteres)
- ✅ **Estrutura**: `$aact_prod_` correto
- ❌ **Resposta API**: HTTP 401 - "A chave de API fornecida é inválida"

---

## 🔍 **Possíveis Causas**

### **1. 🔄 Chave Desatualizada**
- A chave no sistema pode estar desatualizada
- Você pode ter gerado uma nova chave no Asaas

### **2. 📋 Erro de Cópia**
- Espaços em branco no início/fim
- Quebras de linha acidentais
- Caracteres especiais copiados

### **3. 🌐 Ambiente Incorreto**
- Chave de teste sendo usada em produção
- Chave de produção sendo usada em teste

### **4. ⏰ Chave Expirada/Revogada**
- Chave pode ter sido revogada no Asaas
- Possível problema de sincronização

---

## ✅ **Soluções (Ordem de Prioridade)**

### **🎯 Solução 1: Atualizar Chave Via Interface**

1. **Acesse o painel**: `https://app.pixel12digital.com.br/painel/faturas.php`
2. **Clique em**: "🔑 Configurar API"
3. **Na seção "Adicionar Nova Chave"**:
   - Cole a chave EXATA do Asaas
   - Verifique se não há espaços extras
   - Clique em "🧪 Testar Nova Chave"
4. **Se o teste passar**: Clique em "✅ Aplicar Nova Chave"

### **🎯 Solução 2: Obter Nova Chave do Asaas**

1. **Acesse**: https://www.asaas.com
2. **Faça login** na sua conta
3. **Vá em**: Integrações → Chaves de API
4. **Gere uma nova chave** (recomendado)
5. **Copie exatamente** (sem espaços)
6. **Cole no sistema** via interface

### **🎯 Solução 3: Verificar Chave Atual**

Acesse no navegador:
```
https://app.pixel12digital.com.br/painel/api/debug_asaas_key.php
```

Isso mostrará diagnóstico completo do problema.

---

## 🧪 **Como Testar se Está Funcionando**

### **1. Via Interface Web:**
```
https://app.pixel12digital.com.br/painel/faturas.php
```
- O status deve aparecer como "✅ Chave Válida"

### **2. Via Terminal:**
```bash
cd painel/api
php test_asaas_key.php
```

### **3. Teste de Sincronização:**
```
https://app.pixel12digital.com.br/painel/faturas.php
```
- Clique em "🔄 Sincronizar com Asaas"

---

## 🔧 **Correção Manual (Se Necessário)**

### **Editar Arquivo de Configuração:**

1. **Abra**: `config.php`
2. **Localize a linha** (aproximadamente linha 70):
   ```php
   define('ASAAS_API_KEY', '$aact_prod_000MzkwODA2...');
   ```
3. **Substitua** pela chave correta do Asaas
4. **Salve** o arquivo

### **Exemplo de Chave Válida:**
```php
// ANTES (inválida)
define('ASAAS_API_KEY', '$aact_prod_000MzkwODA2MWY2OGM3...');

// DEPOIS (nova chave do Asaas)
define('ASAAS_API_KEY', '$aact_prod_NOVA_CHAVE_AQUI...');
```

---

## 📋 **Checklist de Verificação**

- [ ] **Chave copiada** sem espaços extras
- [ ] **Tipo correto**: Produção (`$aact_prod_`) para ambiente real
- [ ] **Chave ativa** no painel do Asaas
- [ ] **Teste passou** na interface do sistema
- [ ] **Sincronização** funcionando

---

## 🚨 **Erros Comuns**

### **❌ "Chave de API fornecida é inválida"**
**Causa**: Chave incorreta ou desatualizada
**Solução**: Obter nova chave do Asaas

### **❌ "Erro de conectividade"**
**Causa**: Problema de rede/firewall
**Solução**: Verificar conexão de internet

### **❌ "Formato de chave inválido"**
**Causa**: Chave mal copiada
**Solução**: Copiar novamente do Asaas

---

## 📞 **Onde Obter a Chave Correta**

### **No Painel do Asaas:**

1. **Login**: https://www.asaas.com
2. **Menu**: Integrações
3. **Submenu**: Chaves de API
4. **Seção**: "Pixel12Digital Sistema Financeiro" (ou similar)
5. **Ação**: Copiar chave de **Produção**

### **⚠️ Importante:**
- Use **chave de produção** para ambiente real
- Use **chave de teste** apenas para desenvolvimento
- **NÃO compartilhe** a chave com terceiros

---

## ✅ **Verificação Final**

Após aplicar a correção:

1. **Status da API**: Deve mostrar "✅ Chave Válida"
2. **Sincronização**: Deve funcionar sem erros
3. **Faturas**: Devem ser carregadas normalmente
4. **Monitoramento**: Sistema deve funcionar corretamente

---

## 📞 **Suporte**

Se o problema persistir:

1. **Execute o diagnóstico**: `/painel/api/debug_asaas_key.php`
2. **Verifique os logs**: `/logs/asaas_test_debug.log`
3. **Contate o suporte** com os logs

**🎯 A solução mais rápida é atualizar a chave via interface web no painel de faturas!** 