# Modal de Cliente - Documentação

## Visão Geral

O modal de cliente é um componente reutilizável que permite cadastrar novos clientes em qualquer página do painel administrativo. Ele carrega o formulário de cadastro via AJAX e integra com a API do Asaas para busca e cadastro de clientes.

## Arquivos Necessários

- `modal_cliente.js` - Componente JavaScript reutilizável
- `cliente_form.php` - Formulário de cadastro (carregado via AJAX)
- `cliente_busca.php` - Busca cliente no Asaas
- `cliente_add.php` - Processa cadastro do cliente
- `config.php` - Configurações da API do Asaas

## Uso Básico

### 1. Incluir os Scripts

```html
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="modal_cliente.js"></script>
```

### 2. Criar o Botão

```html
<button class="btn" id="btnNovoCliente">+ Novo Cliente</button>
```

### 3. Inicializar o Modal

```javascript
$(document).ready(function() {
    new ModalCliente({
        btnOpenId: 'btnNovoCliente'
    });
});
```

## Opções de Configuração

### Parâmetros Disponíveis

- `modalId` (string): ID do modal (padrão: 'modalCliente')
- `btnOpenId` (string): ID do botão que abre o modal (obrigatório)
- `onSuccess` (function): Callback executado após cadastro bem-sucedido
- `onError` (function): Callback executado em caso de erro

### Exemplos de Uso

#### Uso Simples
```javascript
new ModalCliente({
    btnOpenId: 'btnNovoCliente'
});
```

#### Com Callbacks Personalizados
```javascript
new ModalCliente({
    btnOpenId: 'btnNovoCliente',
    onSuccess: function(resp) {
        alert('Cliente cadastrado: ' + resp.message);
        // Atualizar lista, etc.
    },
    onError: function(resp) {
        console.log('Erro:', resp.message);
    }
});
```

#### Múltiplos Modais na Mesma Página
```javascript
new ModalCliente({
    modalId: 'modalCliente1',
    btnOpenId: 'btnModal1'
});

new ModalCliente({
    modalId: 'modalCliente2',
    btnOpenId: 'btnModal2'
});
```

## Funcionalidades

### Busca de Cliente
- Busca automática no Asaas pelo CPF/CNPJ
- Preenche dados automaticamente se cliente existir
- Permite cadastro manual se cliente não existir

### Validação
- Validação de CPF/CNPJ obrigatório
- Validação de campos obrigatórios
- Feedback visual de status (sucesso, erro, info)

### Integração
- Integração completa com API do Asaas
- Cadastro no banco de dados local
- Sincronização automática

## Estilos CSS

O modal inclui automaticamente os estilos CSS necessários. Os estilos seguem o padrão visual do painel:

- Cores: Roxo (#a259e6) e tons escuros
- Responsivo para mobile
- Animações suaves
- Feedback visual para diferentes estados

## Fluxo de Funcionamento

1. **Clique no botão** → Abre o modal
2. **Carregamento** → Formulário é carregado via AJAX
3. **Busca de CPF/CNPJ** → Consulta API do Asaas
4. **Preenchimento** → Dados são preenchidos automaticamente ou manualmente
5. **Submissão** → Dados são enviados para processamento
6. **Feedback** → Sucesso ou erro é exibido
7. **Fechamento** → Modal fecha automaticamente após sucesso

## Tratamento de Erros

### Erros Comuns
- **CPF/CNPJ não informado**: Validação client-side
- **Cliente não encontrado**: Permite cadastro manual
- **Erro de conexão**: Feedback visual e retry
- **Erro de cadastro**: Mensagem específica do servidor

### Callbacks de Erro
```javascript
onError: function(resp) {
    console.log('Erro:', resp.message);
    // Tratamento personalizado do erro
}
```

## Integração com Outras Páginas

### Páginas que Usam o Modal
- `clientes.php` - Listagem de clientes
- `teste_modal.php` - Página de teste
- `exemplo_uso_modal.php` - Exemplos de uso

### Como Adicionar em Novas Páginas
1. Incluir os scripts necessários
2. Criar botão com ID único
3. Inicializar o modal com configurações apropriadas
4. Definir callbacks se necessário

## Manutenção

### Atualizações
- O modal é auto-contido e não afeta outras funcionalidades
- Atualizações no `modal_cliente.js` são aplicadas automaticamente
- CSS é injetado automaticamente se não existir

### Debug
- Console logs para desenvolvimento
- Feedback visual para usuário
- Tratamento de erros robusto

## Compatibilidade

- **Navegadores**: Chrome, Firefox, Safari, Edge
- **jQuery**: 3.6.0+
- **PHP**: 7.4+
- **MySQL**: 5.7+

## Exemplo Completo

```html
<!DOCTYPE html>
<html>
<head>
    <title>Minha Página</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="modal_cliente.js"></script>
</head>
<body>
    <button class="btn" id="btnNovoCliente">+ Novo Cliente</button>
    
    <script>
    $(document).ready(function() {
        new ModalCliente({
            btnOpenId: 'btnNovoCliente',
            onSuccess: function(resp) {
                location.reload(); // Recarregar página
            }
        });
    });
    </script>
</body>
</html>
``` 