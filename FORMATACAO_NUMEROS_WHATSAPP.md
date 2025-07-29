# Formatação de Números para WhatsApp

## Lógica Implementada

### **Garantia do Código +55 do Brasil**

O sistema foi configurado para **GARANTIR SEMPRE** o acréscimo do código `+55` do Brasil em todos os números de telefone antes do envio para o WhatsApp, independentemente do formato de entrada.

### **Função de Formatação**

```javascript
// JavaScript (index.js)
function formatarNumeroWhatsapp(numero) {
  // Remove caracteres especiais
  numero = String(numero).replace(/\D/g, '');
  
  // Remove código 55 se já existir para reprocessar
  if (numero.startsWith('55')) {
    numero = numero.slice(2);
  }
  
  // Validação e formatação...
  
  // GARANTIR SEMPRE o código +55 do Brasil
  return '55' + ddd + telefone + '@c.us';
}
```

```php
// PHP (enviar_mensagem_whatsapp.php)
function ajustarNumeroWhatsapp($numero) {
    // Remove caracteres especiais
    $numero = preg_replace('/\D/', '', $numero);
    
    // Remove código 55 se já existir para reprocessar
    if (strpos($numero, '55') === 0) {
        $numero = substr($numero, 2);
    }
    
    // Validação e formatação...
    
    // GARANTIR SEMPRE o código +55 do Brasil
    return '55' . $ddd . $telefone;
}
```

### **Processo de Formatação**

1. **Limpeza**: Remove todos os caracteres não numéricos
2. **Normalização**: Remove código 55 se já existir para reprocessar
3. **Validação**: Verifica DDD brasileiro válido
4. **Formatação**: Aplica regras de formatação (adiciona 9 se necessário)
5. **Garantia**: **SEMPRE** adiciona o código +55 do Brasil

### **Exemplos de Transformação**

| Entrada | Processamento | Saída Final |
|---------|---------------|-------------|
| `3484041589` | Remove 55 (não tem) → Valida DDD 34 → Adiciona 9 → Adiciona 55 | `553484041589@c.us` |
| `553484041589` | Remove 55 → Valida DDD 34 → Mantém formato → Adiciona 55 | `553484041589@c.us` |
| `(34) 8404-1589` | Remove caracteres → Remove 55 (não tem) → Valida DDD 34 → Adiciona 9 → Adiciona 55 | `553484041589@c.us` |
| `+55 34 8404-1589` | Remove caracteres → Remove 55 → Valida DDD 34 → Adiciona 9 → Adiciona 55 | `553484041589@c.us` |

### **Validações Implementadas**

- ✅ **DDD válido**: Lista de DDDs brasileiros válidos
- ✅ **Comprimento mínimo**: Mínimo 9 dígitos (DDD + número)
- ✅ **Comprimento máximo**: Máximo 11 dígitos (DDD + número)
- ✅ **Formato celular**: Adiciona 9 automaticamente se necessário
- ✅ **Código país**: **SEMPRE** adiciona +55 do Brasil

### **Arquivos Atualizados**

- ✅ `index.js` - Função `formatarNumeroWhatsapp()`
- ✅ `painel/enviar_mensagem_whatsapp.php` - Função `ajustarNumeroWhatsapp()`
- ✅ `painel/api/enviar_mensagem_automatica.php` - Formatação melhorada
- ✅ `painel/api/enviar_mensagem_validacao.php` - Formatação melhorada
- ✅ `painel/cron/processar_mensagens_agendadas.php` - Formatação melhorada

### **Resultado Final**

**TODOS** os números enviados para o WhatsApp terão o formato:
```
55 + DDD + Número + @c.us
```

**Exemplo**: `553484041589@c.us`

A lógica garante que **NUNCA** um número será enviado sem o código +55 do Brasil, independentemente de como foi inserido no sistema.

### **Benefícios da Implementação**

1. **Consistência**: Todos os números seguem o mesmo padrão
2. **Confiabilidade**: Validação robusta de DDDs brasileiros
3. **Flexibilidade**: Aceita qualquer formato de entrada
4. **Automatização**: Adiciona código +55 automaticamente
5. **Compatibilidade**: Formato correto para API do WhatsApp 