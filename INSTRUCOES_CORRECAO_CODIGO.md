# 🔧 Instruções de Correção do Código - WhatsApp API

## 📋 Problema Identificado
- PM2 está rodando corretamente
- Portas 3000 e 3001 estão sendo escutadas
- Mas o QR ainda retorna com prefixo "undefined,"
- O código em execução não é a versão corrigida

## ✅ Soluções Criadas

### 1. **Script de Validação Automática**
- `validar_codigo_executando.sh` - Valida e corrige automaticamente
- `corrigir_codigo_manual.sh` - Correção manual se a automática falhar

## 🚀 Como Aplicar

### Opção 1: Correção Automática (Recomendada)
```bash
# Navegar para o diretório
cd /var/whatsapp-api

# Executar validação e correção automática
chmod +x validar_codigo_executando.sh
./validar_codigo_executando.sh
```

### Opção 2: Correção Manual
```bash
# Se a automática não funcionar
chmod +x corrigir_codigo_manual.sh
./corrigir_codigo_manual.sh
```

## 🔍 O que os Scripts Fazem

### 1. **Validação de Versão**
- Adiciona logs de debug no início do arquivo
- Mostra qual arquivo está sendo executado
- Exibe tamanho do arquivo e timestamp
- Confirma variáveis de ambiente

### 2. **Correção da Declaração de PORT**
- Substitui `const PORT = 3000;` por `const PORT = parseInt(process.env.PORT, 10) || 3000;`
- Garante que a porta seja lida da variável de ambiente

### 3. **Correção do Binding do Express**
- Substitui `app.listen(PORT, () => {` por `app.listen(PORT, '0.0.0.0', () => {`
- Garante que a API seja acessível externamente

### 4. **Adição de Logs de Debug QR**
- Adiciona logs detalhados no handler de QR
- Mostra `sessionName`, `PORT`, e outros valores
- Confirma que o QR está sendo atribuído corretamente

### 5. **Limpeza de Sessões e Cache**
- Remove sessões antigas que podem estar corrompidas
- Limpa cache do Puppeteer
- Garante inicialização limpa

## 🧪 Testes de Verificação

### Teste 1: Verificar Logs de Validação
```bash
pm2 logs whatsapp-3001 --lines 30 --nostream | grep -E "(VERSION CHECK|DEBUG|API rodando)"
```

**Resultado Esperado:**
```
🔍 [VERSION CHECK] Arquivo sendo executado: /var/whatsapp-api/whatsapp-api-server.js
🔍 [VERSION CHECK] Tamanho do arquivo: XXXXX bytes
🔍 [VERSION CHECK] PORT env: 3001
🌐 API WhatsApp rodando em http://0.0.0.0:3001
```

### Teste 2: Verificar Logs de QR
```bash
pm2 logs whatsapp-3001 --lines 50 --nostream | grep -E "(QR payload raw|sessionName|comercial)"
```

**Resultado Esperado:**
```
🔍 [comercial] QR payload raw: [QR_CODE_VALIDO]
🔍 [comercial] sessionName value: comercial
🔍 [comercial] typeof sessionName: string
```

### Teste 3: Testar QR Localmente
```bash
curl -s http://127.0.0.1:3001/qr?session=comercial | jq .
```

**Resultado Esperado:**
```json
{
  "success": true,
  "qr": "[QR_CODE_SEM_UNDEFINED]",
  "ready": false,
  "message": "QR Code disponível para escaneamento",
  "status": "qr_ready"
}
```

### Teste 4: Testar Conectividade Externa
```bash
curl -s http://212.85.11.238:3001/qr?session=comercial | jq .
```

## 🚨 Troubleshooting

### Se os Logs Não Aparecem:
```bash
# Verificar se o PM2 está rodando
pm2 list

# Verificar se as portas estão sendo escutadas
ss -tlnp | grep :3001

# Verificar logs de erro
pm2 logs whatsapp-3001 --err
```

### Se o QR Ainda Tem "undefined,":
```bash
# Verificar se as correções foram aplicadas
grep -n "process.env.PORT" whatsapp-api-server.js
grep -n "0.0.0.0" whatsapp-api-server.js

# Se não foram aplicadas, executar correção manual
./corrigir_codigo_manual.sh
```

### Se a Conectividade Externa Falha:
```bash
# Verificar firewall
ufw status | grep 3001

# Se não estiver liberada
ufw allow 3001/tcp
ufw reload
```

## 📝 Resultado Esperado

Após aplicar as correções:
- ✅ Logs de validação mostrando arquivo correto
- ✅ Logs de QR mostrando `sessionName: comercial`
- ✅ QR code sem prefixo "undefined,"
- ✅ Conectividade externa funcionando
- ✅ Painel conseguindo acessar a API

## 🔄 Monitoramento Contínuo

Para monitorar em tempo real:
```bash
# Logs em tempo real
pm2 logs whatsapp-3001

# Status das instâncias
pm2 monit

# Verificar conectividade periodicamente
watch -n 5 'curl -s http://212.85.11.238:3001/status | jq .'
```

## 🎯 Próximos Passos

1. **Execute o script de validação**
2. **Verifique os logs de validação**
3. **Teste o QR localmente**
4. **Teste no painel**
5. **Monitore os logs em tempo real**

Se ainda houver problemas após estas correções, teremos visibilidade total do que está acontecendo através dos logs de debug. 