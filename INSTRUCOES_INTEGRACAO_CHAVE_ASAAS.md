# 🔑 Instruções de Integração - Gerenciamento Automático da Chave Asaas

## ✅ Sistema Criado

Criei um sistema completo de gerenciamento automático da chave da API do Asaas que:

1. **Atualiza automaticamente** os arquivos de configuração quando a chave é alterada
2. **Monitora periodicamente** o status da chave
3. **Notifica sobre problemas** em tempo real
4. **Integra-se facilmente** à sua interface existente

## 📁 Arquivos Criados

### 1. **API de Atualização** (`painel/api/atualizar_chave_asaas.php`)
- Endpoint para atualizar a chave via AJAX
- Valida a chave antes de aplicar
- Atualiza automaticamente `config.php` e `painel/config.php`

### 2. **Verificador Automático** (`painel/verificador_automatico_chave.php`)
- Monitora o status da chave periodicamente
- Cria alertas quando há problemas
- Pode ser executado via cron job

### 3. **Sistema de Integração** (`painel/integracao_chave_asaas.php`)
- Inclui CSS, JavaScript e HTML necessários
- Modal de configuração completo
- Notificações automáticas

## 🚀 Como Integrar na Sua Interface

### Passo 1: Incluir o Sistema de Integração

Adicione esta linha no **HEAD** da sua página (ex: `faturas.php`):

```php
<?php include 'integracao_chave_asaas.php'; ?>
```

### Passo 2: Adicionar Containers para Status e Alertas

Adicione estes elementos no seu HTML onde quiser mostrar o status:

```html
<!-- Para mostrar o status da chave -->
<div id="status-chave-asaas-container">
    <?php echo gerarHtmlStatusChaveAsaas(); ?>
</div>

<!-- Para mostrar alertas (opcional) -->
<div id="alertas-chave-asaas-container"></div>
```

### Passo 3: Modificar o Botão "Configurar API"

Substitua o botão existente por:

```html
<button onclick="abrirModalConfiguracaoAsaas()" class="btn-configurar-api">
    🔑 Configurar API
</button>
```

## 🔧 Configuração do Cron Job (Opcional)

Para verificação automática a cada 30 minutos, adicione ao cron:

```bash
*/30 * * * * php /caminho/para/painel/verificador_automatico_chave.php
```

## 📋 Exemplo de Integração Completa

```php
<!DOCTYPE html>
<html>
<head>
    <title>Sua Interface</title>
    <?php include 'integracao_chave_asaas.php'; ?>
</head>
<body>
    <!-- Seu conteúdo existente -->
    
    <!-- Status da chave -->
    <div id="status-chave-asaas-container">
        <?php echo gerarHtmlStatusChaveAsaas(); ?>
    </div>
    
    <!-- Alertas automáticos -->
    <div id="alertas-chave-asaas-container"></div>
    
    <!-- Botão para configurar -->
    <button onclick="abrirModalConfiguracaoAsaas()">
        🔑 Configurar API
    </button>
</body>
</html>
```

## 🎯 Funcionalidades Automáticas

### ✅ **Atualização Automática**
- Quando você altera a chave pela interface, ela é automaticamente:
  - Testada com a API do Asaas
  - Aplicada nos arquivos `config.php` e `painel/config.php`
  - Validada antes de ser salva

### ✅ **Monitoramento Contínuo**
- Verifica o status da chave a cada 5 minutos
- Mostra indicadores visuais (✅/❌)
- Atualiza automaticamente a interface

### ✅ **Alertas Inteligentes**
- Notifica quando a chave está inválida
- Sugere ações para resolver problemas
- Mostra histórico de verificações

### ✅ **Interface Intuitiva**
- Modal de configuração completo
- Validação em tempo real
- Notificações de sucesso/erro

## 🔍 Como Testar

1. **Inclua o sistema** na sua interface
2. **Clique em "Configurar API"**
3. **Cole uma nova chave** no modal
4. **Clique em "Aplicar Nova Chave"**
5. **Verifique** se os arquivos foram atualizados

## 📊 Endpoints Disponíveis

### Verificar Status
```
GET painel/verificador_automatico_chave.php?action=status
```

### Verificar Chave
```
GET painel/verificador_automatico_chave.php?action=verificar
```

### Ver Alertas
```
GET painel/verificador_automatico_chave.php?action=alertas
```

### Atualizar Chave
```
POST painel/api/atualizar_chave_asaas.php
Content-Type: application/json

{
    "nova_chave": "$aact_prod_..."
}
```

## 🛡️ Segurança

- Todas as chaves são validadas antes de serem aplicadas
- Logs de todas as alterações são mantidos
- Sistema de backup automático dos arquivos de configuração
- Validação de formato das chaves

## 🎉 Benefícios

1. **Sem comandos CLI** - Tudo pela interface web
2. **Atualização automática** - Não precisa editar arquivos manualmente
3. **Monitoramento contínuo** - Detecta problemas antes que afetem o sistema
4. **Interface familiar** - Integra-se perfeitamente ao seu design existente
5. **Logs completos** - Histórico de todas as alterações

---

**Pronto para usar!** Basta incluir o arquivo de integração na sua interface e o sistema funcionará automaticamente. 