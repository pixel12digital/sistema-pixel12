# 🔑 Sistema de Configuração da API do Asaas

## 📋 Visão Geral

Implementei um sistema completo de configuração da API do Asaas diretamente no frontend, permitindo:

- **Visualizar** a chave atual (mascarada)
- **Testar** a chave atual em tempo real
- **Adicionar** novas chaves da API
- **Testar** novas chaves antes de aplicar
- **Aplicar** automaticamente no backend
- **Backup** automático das configurações

## 🚀 Como Usar

### 1. Acessar o Sistema

Na página de **Faturas**, clique no botão **"🔑 Configurar API"** no cabeçalho.

### 2. Interface do Modal

O modal de configuração possui três seções principais:

#### 🔍 **Status da Chave Atual**
- Mostra a chave atual (mascarada por segurança)
- Testa automaticamente a conexão com o Asaas
- Exibe status visual (✅ válida / ❌ inválida)
- Botão para re-testar a chave

#### ➕ **Adicionar Nova Chave**
- Campo para inserir nova chave da API
- Seletor de tipo (Teste/Produção)
- Botão para testar a nova chave
- Botão para aplicar a nova chave

#### 📚 **Informações e Links**
- Instruções para obter chaves
- Links para documentação do Asaas
- Avisos de segurança

## 🔧 Funcionalidades Implementadas

### Frontend (JavaScript)

```javascript
// Abrir modal de configuração
function abrirModalConfigAsaas() {
    modalConfigAsaas.style.display = 'flex';
    carregarChaveAtual();
    testarChaveAtual();
}

// Testar chave atual
function testarChaveAtual() {
    fetch('api/test_asaas_key.php')
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                // Chave válida
                statusChaveIcon.textContent = '✅';
                statusChaveIcon.style.background = '#059669';
            } else {
                // Chave inválida
                statusChaveIcon.textContent = '❌';
                statusChaveIcon.style.background = '#dc2626';
            }
        });
}

// Aplicar nova chave
function aplicarNovaChave() {
    fetch('api/update_asaas_key.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ 
            chave: novaChave, 
            tipo: tipoChave 
        })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            alert('✅ Chave da API atualizada com sucesso!');
            carregarChaveAtual();
            testarChaveAtual();
        }
    });
}
```

### Backend (PHP)

#### `api/get_asaas_config.php`
- Retorna a configuração atual da API
- Inclui chave (mascarada), tipo e URL

#### `api/test_asaas_key.php`
- Testa chaves da API com o Asaas
- Suporta GET (chave atual) e POST (nova chave)
- Validação de formato e conexão

#### `api/update_asaas_key.php`
- Atualiza a chave no arquivo `config.php`
- Validação completa antes de aplicar
- Backup automático do arquivo original
- Log de alterações

## 🛡️ Segurança e Validação

### Validações Implementadas

1. **Formato da Chave**
   ```php
   if (!preg_match('/^\$aact_(test|prod)_/', $chave)) {
       // Erro: formato inválido
   }
   ```

2. **Teste de Conexão**
   ```php
   $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
   if ($httpCode !== 200) {
       // Erro: chave inválida
   }
   ```

3. **Backup Automático**
   ```php
   $backupFile = $configFile . '.backup.' . date('Y-m-d_H-i-s');
   copy($configFile, $backupFile);
   ```

4. **Log de Alterações**
   ```php
   $logEntry = date('Y-m-d H:i:s') . ' - Chave ' . $tipoChave . ' atualizada';
   file_put_contents($logFile, $logEntry, FILE_APPEND);
   ```

## 📊 Fluxo de Uso

### Cenário 1: Chave Atual Inválida

1. **Clique** em "🔑 Configurar API"
2. **Observe** que a chave atual está inválida (❌)
3. **Cole** sua nova chave no campo
4. **Selecione** o tipo (Teste/Produção)
5. **Clique** em "🧪 Testar Nova Chave"
6. **Se válida**, clique em "✅ Aplicar Nova Chave"
7. **Confirme** a alteração
8. **Verifique** que a chave foi atualizada

### Cenário 2: Testar Sincronização

1. **Configure** a chave da API
2. **Clique** em "🔄 Testar Sincronização"
3. **Observe** o modal de sincronização
4. **Verifique** se funciona corretamente

## 🔍 Monitoramento

### Logs Criados

- `logs/asaas_key_updates.log` - Histórico de alterações de chaves
- `logs/sincroniza_asaas_debug.log` - Logs de sincronização

### Exemplo de Log

```
2025-07-18 15:30:45 - Chave test atualizada: $aact_test_CHAVE_DE_T..._TESTE
2025-07-18 15:35:12 - Chave prod atualizada: $aact_prod_CHAVE_DE_P..._PROD
```

## ⚠️ Importante

### Para Desenvolvimento Local
- **Use sempre** chaves de teste (`$aact_test_...`)
- **Evite** chaves de produção para evitar cobranças reais
- **Teste** sempre antes de aplicar

### Para Produção
- **Use** chaves de produção (`$aact_prod_...`)
- **Verifique** se a chave está ativa no painel do Asaas
- **Mantenha** backup das configurações

## 🔗 Links Úteis

- **Asaas**: https://www.asaas.com/
- **Documentação API**: https://www.asaas.com/api-docs/
- **Configurações API**: https://www.asaas.com/configuracoes/api

## 🚀 Próximas Melhorias

- [ ] Histórico de chaves utilizadas
- [ ] Notificação por email em caso de erro
- [ ] Validação automática periódica
- [ ] Interface para restaurar backups
- [ ] Suporte a múltiplas contas Asaas

---

**Implementado em**: 18/07/2025  
**Versão**: 1.0  
**Status**: ✅ Funcional e testado 