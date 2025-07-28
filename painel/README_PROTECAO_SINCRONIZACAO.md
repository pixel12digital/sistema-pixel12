# 🛡️ Proteção de Dados na Sincronização com Asaas

## 📋 Problema Resolvido

**Problema:** A sincronização tradicional (`sincroniza_asaas.php`) sobrescreve **TODOS** os dados dos clientes, incluindo edições manuais feitas via edição inline, causando perda de informações importantes.

**Solução:** Implementação de uma **sincronização segura** que preserva dados editados manualmente e respeita configurações de proteção.

## ✨ Características da Proteção

### 🕒 **Proteção Temporal**
- **24 horas de proteção:** Dados editados nas últimas 24 horas não são sobrescritos
- **Timestamp inteligente:** Verifica `data_atualizacao` para determinar se dados são recentes
- **Configurável:** Período de proteção pode ser ajustado

### 🎯 **Campos Críticos (Nunca Sobrescritos)**
- **Nome** - Dados pessoais fundamentais
- **Email** - Contato principal
- **CPF/CNPJ** - Documento de identificação
- **Telefone** - Contato telefônico
- **Celular** - Contato móvel

### 📝 **Campos Apenas Vazios**
- **Endereço completo** (CEP, Rua, Número, Complemento, Bairro, Cidade, Estado, País)
- **Razão Social** - Informações empresariais
- **Observações** - Anotações personalizadas
- **Referência Externa** - Dados de integração

### 🔄 **Campos Normais**
- **Notificação Desativada** - Configurações do sistema
- **E-mails Adicionais** - Contatos secundários

## 🛠️ Arquivos Implementados

### 1. `sincroniza_asaas_seguro.php` (PRINCIPAL)
- **Sincronização inteligente** que preserva dados editados
- **Log detalhado** de todas as operações
- **Criação de novos clientes** quando necessário
- **Sincronização de cobranças** (sempre atualizada)

### 2. `config_sincronizacao_segura.php` (CONFIGURAÇÃO)
- **Parâmetros configuráveis** de proteção
- **Funções auxiliares** para verificação de proteção
- **Interface web** para ajustar configurações
- **Relatórios** de proteção

### 3. `teste_protecao_sincronizacao.php` (TESTE)
- **Simulação de edições** para testar proteção
- **Verificação de configurações** atuais
- **Estatísticas** de proteção
- **Interface de teste** completa

## 🎮 Como Usar

### Executar Sincronização Segura
```bash
cd painel
php sincroniza_asaas_seguro.php
```

### Testar Proteção
```bash
cd painel
php teste_protecao_sincronizacao.php
```

### Ajustar Configurações
Acesse: `http://seu-dominio/painel/config_sincronizacao_segura.php?ajustar_config`

## 🔍 Lógica de Proteção

### 1. **Verificação Temporal**
```php
// Se cliente foi editado nas últimas 24h, preservar dados
if (foiEditadoRecentemente($data_atualizacao)) {
    return true; // Preserva dados locais
}
```

### 2. **Campos Críticos**
```php
// Campos críticos nunca são sobrescritos se já têm valor
if (isCampoCritico($campo) && !empty($valor_atual)) {
    return false; // Não atualiza
}
```

### 3. **Campos Apenas Vazios**
```php
// Campos vazios só são preenchidos se estiverem vazios
if (isCampoApenasVazio($campo) && !empty($valor_atual)) {
    return false; // Não atualiza
}
```

### 4. **Atualização Inteligente**
```php
// Só atualiza se:
// 1. Campo está vazio E valor do Asaas não está vazio
// 2. OU campo não é crítico E valores são diferentes
if (deveAtualizarCampo($campo, $valor_atual, $valor_asaas)) {
    // Atualiza campo
}
```

## 📊 Exemplo de Funcionamento

### Cenário 1: Cliente Editado Recentemente
```
Cliente: João Silva
- Editado via edição inline há 2 horas
- Nome: "João Silva [CORRIGIDO]"
- Email: "joao.corrigido@email.com"

Resultado da Sincronização:
✅ Dados PRESERVADOS (editados recentemente)
✅ Nome e email mantidos como editados
✅ Apenas campos vazios preenchidos do Asaas
```

### Cenário 2: Cliente Não Editado
```
Cliente: Maria Santos
- Última edição há 48 horas
- Nome: "Maria Santos"
- Email: "maria@email.com"

Resultado da Sincronização:
✅ Dados ATUALIZADOS do Asaas
✅ Campos críticos preservados se diferentes
✅ Campos vazios preenchidos
```

### Cenário 3: Novo Cliente
```
Cliente: Pedro Costa
- Não existe no banco local
- Existe apenas no Asaas

Resultado da Sincronização:
✅ Cliente CRIADO no banco local
✅ Todos os dados importados do Asaas
```

## 🔧 Configurações Disponíveis

### Proteção Temporal
- **Horas de proteção:** 24 (padrão)
- **Range:** 1-168 horas (1 semana)

### Campos Críticos
- **nome, email, cpf_cnpj, telefone, celular**
- **Nunca sobrescritos** se já têm valor

### Campos Apenas Vazios
- **cep, rua, numero, complemento, bairro, cidade, estado, pais, razao_social, observacoes, referencia_externa**
- **Só preenchidos** se estiverem vazios

### Performance
- **Limite de páginas:** 50 (clientes) / 30 (cobranças)
- **Registros por página:** 100
- **Timeout API:** 30 segundos

## 📈 Benefícios

### ✅ **Preservação de Dados**
- Edições manuais não são perdidas
- Dados críticos sempre protegidos
- Histórico de alterações mantido

### ✅ **Flexibilidade**
- Configurações ajustáveis
- Diferentes níveis de proteção
- Logs detalhados para auditoria

### ✅ **Compatibilidade**
- Funciona com edição inline
- Compatível com sistema existente
- Não quebra sincronizações atuais

### ✅ **Segurança**
- Validação de dados
- Tratamento de erros
- Backup implícito via logs

## 🚀 Migração Recomendada

### 1. **Testar Proteção**
```bash
php teste_protecao_sincronizacao.php
```

### 2. **Configurar Parâmetros**
Acesse a interface de configuração e ajuste conforme necessário.

### 3. **Executar Sincronização Segura**
```bash
php sincroniza_asaas_seguro.php
```

### 4. **Monitorar Logs**
Verifique o arquivo `logs/sincronizacao_segura.log` para acompanhar as operações.

### 5. **Substituir Sincronização Tradicional**
Renomeie `sincroniza_asaas.php` para `sincroniza_asaas_backup.php` e use `sincroniza_asaas_seguro.php` como padrão.

## 🐛 Troubleshooting

### Problemas Comuns

#### 1. **Dados ainda sendo sobrescritos**
- Verificar se está usando o script correto
- Confirmar configurações de proteção
- Verificar logs de sincronização

#### 2. **Proteção não funcionando**
- Verificar timestamp de `data_atualizacao`
- Confirmar se cliente tem `asaas_id`
- Testar com script de teste

#### 3. **Performance lenta**
- Ajustar limite de páginas
- Verificar timeout da API
- Monitorar logs de erro

### Debug
- **Logs detalhados:** `logs/sincronizacao_segura.log`
- **Teste de proteção:** `teste_protecao_sincronizacao.php`
- **Configurações:** `config_sincronizacao_segura.php`

## 📞 Suporte

Para dúvidas ou problemas:
1. **Executar teste de proteção**
2. **Verificar logs de sincronização**
3. **Ajustar configurações se necessário**
4. **Consultar documentação do sistema**

---

**Versão:** 1.0  
**Data:** 2025-01-15  
**Autor:** Sistema de Proteção de Sincronização 