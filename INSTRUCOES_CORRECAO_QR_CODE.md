# 🔧 Instruções para Correção do Problema do QR Code

## 📋 Problema Identificado
A sessão "comercial" (porta 3001) estava retornando QR codes com prefixo "undefined," devido a problemas na lógica de inicialização e configuração do PM2.

## ✅ Correções Aplicadas

### 1. **Correção da Declaração de PORT**
- **Arquivo**: `whatsapp-api-server.js` (linha 9)
- **Antes**: `const PORT = 3000;`
- **Depois**: `const PORT = parseInt(process.env.PORT, 10) || 3000;`

### 2. **Logs de Debug Adicionados**
- **Arquivo**: `whatsapp-api-server.js` (linha ~95)
- Adicionados logs para debugar o problema:
  ```javascript
  console.log(`🔍 [${sessionName}] QR payload raw:`, qr);
  console.log(`🔍 [${sessionName}] sessionName value:`, sessionName);
  console.log(`🔍 [${sessionName}] typeof sessionName:`, typeof sessionName);
  ```

### 3. **Logs de Debug na Inicialização**
- **Arquivo**: `whatsapp-api-server.js` (linha ~640)
- Adicionados logs para verificar a determinação da sessão:
  ```javascript
  console.log(`🔍 [DEBUG] PORT value:`, PORT);
  console.log(`🔍 [DEBUG] process.env.PORT:`, process.env.PORT);
  console.log(`🔍 [DEBUG] sessionName determined:`, sessionName);
  ```

### 4. **Configuração do PM2 Corrigida**
- **Arquivo**: `ecosystem.config.js`
- Criadas duas instâncias separadas:
  - `whatsapp-3000` (porta 3000, sessão "default")
  - `whatsapp-3001` (porta 3001, sessão "comercial")

## 🚀 Como Aplicar as Correções

### Passo 1: Parar Instâncias Atuais
```bash
pm2 delete all
```

### Passo 2: Limpar Logs Antigos
```bash
rm -rf ./logs/*.log
mkdir -p ./logs
```

### Passo 3: Iniciar com Nova Configuração
```bash
pm2 start ecosystem.config.js
pm2 save
```

### Passo 4: Verificar Status
```bash
pm2 list
```

### Passo 5: Monitorar Logs
```bash
# Para ver logs da instância 3001
pm2 logs whatsapp-3001 --lines 50 --nostream | grep "QR payload raw"

# Para ver logs em tempo real
pm2 logs whatsapp-3001
```

## 🧪 Como Testar

### Teste 1: Verificar Status das Sessões
```bash
# Status geral
curl -s http://localhost:3001/status | jq .

# Status da sessão comercial
curl -s http://localhost:3001/session/comercial/status | jq .
```

### Teste 2: Verificar QR Code
```bash
# Testar QR da sessão comercial
curl -s http://localhost:3001/qr?session=comercial | jq .

# Verificar se não começa com "undefined,"
curl -s http://localhost:3001/qr?session=comercial | jq -r '.qr' | head -c 20
```

### Teste 3: Usar Scripts Automatizados
```bash
# Executar script de reinicialização
./reiniciar_pm2_corrigido.sh

# Executar script de teste
./testar_qr_corrigido.sh
```

## 🔍 O que Esperar Após as Correções

### ✅ Comportamento Correto
- Sessão "default" (porta 3000): QR normal
- Sessão "comercial" (porta 3001): QR normal (sem prefixo "undefined,")
- Logs mostrando `sessionName: comercial` para porta 3001
- QR codes válidos sem prefixos estranhos

### 📊 Logs Esperados
```
🔍 [DEBUG] PORT value: 3001
🔍 [DEBUG] process.env.PORT: 3001
🔍 [DEBUG] sessionName determined: comercial
🔄 Inicializando sessão: comercial (porta 3001)
🔍 [comercial] QR payload raw: [QR_CODE_VALIDO]
🔍 [comercial] sessionName value: comercial
🔍 [comercial] typeof sessionName: string
```

## 🚨 Se o Problema Persistir

### Verificar 1: Variáveis de Ambiente
```bash
# Verificar se a variável PORT está sendo passada
pm2 env whatsapp-3001
```

### Verificar 2: Logs Detalhados
```bash
# Ver todos os logs da instância
pm2 logs whatsapp-3001 --lines 100 --nostream
```

### Verificar 3: Reiniciar com Força
```bash
pm2 delete all
pm2 start ecosystem.config.js --update-env
pm2 save
```

## 📝 Notas Importantes

1. **Isolamento de Portas**: Cada instância agora roda em uma porta isolada
2. **Logs Separados**: Cada instância tem seus próprios arquivos de log
3. **Variáveis de Ambiente**: O PM2 agora passa corretamente a variável PORT
4. **Debug Aprimorado**: Logs detalhados para identificar problemas futuros

## 🎯 Resultado Esperado

Após aplicar todas as correções:
- ✅ QR code da sessão "comercial" sem prefixo "undefined,"
- ✅ Sessões isoladas e funcionando independentemente
- ✅ Logs claros e informativos
- ✅ Configuração PM2 estável e confiável 