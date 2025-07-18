# 🔑 Scripts de Teste de Chave da API do Asaas

Este repositório contém scripts para testar chaves da API do Asaas de diferentes formas.

## 📋 Scripts Disponíveis

### 1. `teste_chave_online.php` - Interface Web
**Descrição**: Interface web completa para testar chaves da API
**Uso**: Acesse via navegador
**Recursos**:
- Interface moderna e responsiva
- Validação de formato
- Teste em tempo real
- Resultados detalhados
- Instruções de uso

### 2. `teste_chave_cli.php` - Linha de Comando
**Descrição**: Script para executar via terminal
**Uso**: `php teste_chave_cli.php "sua_chave_aqui"`
**Recursos**:
- Execução rápida
- Resultados no terminal
- Diagnóstico automático
- Informações detalhadas

### 3. `teste_chave_especifica.php` - Teste Específico
**Descrição**: Teste específico da chave aplicada no sistema
**Uso**: Acesse via navegador
**Recursos**:
- Teste da chave atual do sistema
- Análise detalhada
- Múltiplos testes (com/sem SSL)
- Diagnóstico completo

## 🚀 Como Usar

### Opção 1: Interface Web (Recomendado)

1. **Faça upload** do arquivo `teste_chave_online.php` para seu servidor
2. **Acesse** via navegador: `https://seudominio.com/teste_chave_online.php`
3. **Cole** sua chave da API no campo
4. **Clique** em "🧪 Testar Chave"
5. **Analise** os resultados

### Opção 2: Linha de Comando

```bash
# Testar chave de teste
php teste_chave_cli.php '$aact_test_CHAVE_DE_TESTE'

# Testar chave de produção
php teste_chave_cli.php '$aact_prod_CHAVE_DE_PRODUCAO'
```

### Opção 3: Teste Específico

1. **Acesse** `teste_chave_especifica.php` no navegador
2. **Analise** os resultados dos testes
3. **Verifique** o diagnóstico fornecido

## 📊 Resultados Possíveis

### ✅ Chave Válida
- Código HTTP: 200
- Conexão estabelecida com sucesso
- Chave pode ser usada no sistema

### ❌ Chave Inválida
- Código HTTP: 401
- Possíveis causas:
  - Chave incorreta ou expirada
  - Conta do Asaas inativa
  - Chave sem permissões
  - Chave revogada

### 🌐 Erro de Conexão
- Problemas de conectividade
- Firewall bloqueando
- Servidor sem acesso à internet

## 🔧 Requisitos

- PHP 7.4 ou superior
- Extensão cURL habilitada
- Acesso à internet
- Chave válida da API do Asaas

## 📝 Como Obter uma Chave da API

1. **Acesse**: https://www.asaas.com/
2. **Faça login** na sua conta
3. **Vá em**: Configurações → API
4. **Copie** a chave desejada:
   - **Chave de Teste**: Para desenvolvimento
   - **Chave de Produção**: Para ambiente de produção

## ⚠️ Importante

- **Desenvolvimento**: Use sempre chaves de teste
- **Produção**: Use chaves de produção apenas quando necessário
- **Segurança**: Nunca compartilhe suas chaves
- **Backup**: Mantenha backup das configurações

## 🛠️ Solução de Problemas

### Erro 401 - Chave Inválida
1. Verifique se a chave foi copiada corretamente
2. Confirme se a chave está ativa no painel do Asaas
3. Verifique se a conta está ativa
4. Teste com uma nova chave

### Erro de Conexão
1. Verifique a conexão com a internet
2. Confirme se não há firewall bloqueando
3. Teste em outro servidor/rede
4. Verifique as configurações SSL

### Formato Inválido
1. A chave deve começar com `$aact_test_` ou `$aact_prod_`
2. Verifique se não há espaços extras
3. Confirme se todos os caracteres foram copiados

## 📞 Suporte

Se encontrar problemas:

1. **Verifique** os logs de erro
2. **Teste** com diferentes chaves
3. **Confirme** as configurações do servidor
4. **Consulte** a documentação do Asaas

## 🔗 Links Úteis

- [Documentação da API Asaas](https://www.asaas.com/api-docs/)
- [Painel do Asaas](https://www.asaas.com/)
- [Configurações de API](https://www.asaas.com/configuracoes/api)

---

**Última atualização**: 18/07/2025  
**Versão**: 1.0  
**Status**: ✅ Funcional e testado 