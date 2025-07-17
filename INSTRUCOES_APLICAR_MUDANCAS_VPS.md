# 🔧 Instruções para Aplicar Mudanças na VPS

## 📋 Resumo das Mudanças

Identificamos que o problema com o QR code não atualizando está relacionado à falta de endpoints específicos para QR code no servidor WhatsApp. As seguintes mudanças foram implementadas:

### ✅ Mudanças Realizadas

1. **Novo endpoint `/qr`** - Retorna QR code específico da sessão
2. **Endpoint `/qr/default`** - Compatibilidade com sessão padrão
3. **Endpoint `/qr/:sessionName`** - Para sessões específicas
4. **Status melhorado** - Inclui QR code quando disponível
5. **Proxy PHP atualizado** - Usa novos endpoints

---

## 🚀 Passos para Aplicar na VPS

### **Passo 1: Acessar a VPS**

```bash
ssh root@212.85.11.238
```

### **Passo 2: Navegar para o diretório do projeto**

```bash
cd /root/whatsapp-api
# ou
cd /home/user/whatsapp-api
# (dependendo de onde o projeto está instalado)
```

### **Passo 3: Fazer backup do arquivo atual**

```bash
cp whatsapp-api-server.js whatsapp-api-server.js.backup
```

### **Passo 4: Editar o arquivo whatsapp-api-server.js**

```bash
nano whatsapp-api-server.js
```

### **Passo 5: Adicionar os novos endpoints**

Localize a seção onde estão os endpoints (após o endpoint `/session/:sessionName/status`) e adicione:

```javascript
// Endpoint específico para QR Code
app.get('/qr', (req, res) => {
    const sessionName = req.query.session || 'default';
    
    if (!whatsappClients[sessionName]) {
        return res.status(404).json({
            success: false,
            message: `Sessão ${sessionName} não encontrada`,
            suggestion: 'Inicie uma sessão primeiro usando POST /session/start/default'
        });
    }
    
    const status = clientStatus[sessionName];
    
    if (!status) {
        return res.status(503).json({
            success: false,
            message: 'Status da sessão não disponível'
        });
    }
    
    if (status.status === 'connected') {
        return res.json({
            success: true,
            qr: null,
            ready: true,
            message: 'WhatsApp já está conectado',
            status: 'connected'
        });
    }
    
    if (status.status === 'qr_ready' && status.qr) {
        return res.json({
            success: true,
            qr: status.qr,
            ready: false,
            message: 'QR Code disponível para escaneamento',
            status: 'qr_ready'
        });
    }
    
    return res.status(503).json({
        success: false,
        qr: null,
        ready: false,
        message: 'QR Code não disponível no momento',
        status: status.status,
        suggestion: 'Aguarde alguns segundos e tente novamente'
    });
});

// Endpoint para QR Code da sessão default (compatibilidade)
app.get('/qr/default', (req, res) => {
    // Redirecionar para o endpoint principal
    res.redirect('/qr?session=default');
});

// Endpoint para QR Code da sessão específica
app.get('/qr/:sessionName', (req, res) => {
    const { sessionName } = req.params;
    res.redirect(`/qr?session=${sessionName}`);
});
```

### **Passo 6: Atualizar o endpoint de status**

Localize o endpoint `/status` e substitua por:

```javascript
// Status geral
app.get('/status', (req, res) => {
    // Preparar resposta com informações detalhadas
    const response = {
        success: true,
        message: 'WhatsApp Multi-Sessão API funcionando',
        timestamp: new Date().toISOString(),
        sessions: Object.keys(whatsappClients).length,
        clients_status: clientStatus,
        ready: false
    };
    
    // Verificar se alguma sessão está conectada
    const connectedSessions = Object.values(clientStatus).filter(status => status.status === 'connected');
    if (connectedSessions.length > 0) {
        response.ready = true;
        response.message = 'WhatsApp conectado e funcionando';
    }
    
    // Adicionar QR code se disponível
    if (clientStatus.default && clientStatus.default.qr) {
        response.qr_available = true;
        response.qr = clientStatus.default.qr;
    }
    
    res.json(response);
});
```

### **Passo 7: Salvar o arquivo**

No nano:
- Pressione `Ctrl + X`
- Pressione `Y` para confirmar
- Pressione `Enter` para salvar

### **Passo 8: Reiniciar o servidor**

```bash
# Verificar status atual
pm2 status

# Reiniciar o serviço
pm2 restart whatsapp-api

# Verificar se reiniciou corretamente
pm2 status

# Verificar logs
pm2 logs whatsapp-api
```

### **Passo 9: Testar os novos endpoints**

```bash
# Testar status
curl http://localhost:3000/status

# Testar QR code
curl http://localhost:3000/qr

# Testar QR code da sessão default
curl http://localhost:3000/qr/default
```

---

## 🧪 Testes de Verificação

### **Teste 1: Status da API**

```bash
curl -X GET "http://212.85.11.238:3000/status" | jq
```

**Resposta esperada:**
```json
{
  "success": true,
  "message": "WhatsApp Multi-Sessão API funcionando",
  "timestamp": "2024-01-XX...",
  "sessions": 1,
  "clients_status": {
    "default": {
      "status": "qr_ready",
      "qr": "data:image/png;base64,...",
      "message": "Escaneie o QR Code no WhatsApp"
    }
  },
  "ready": false,
  "qr_available": true,
  "qr": "data:image/png;base64,..."
}
```

### **Teste 2: Endpoint QR**

```bash
curl -X GET "http://212.85.11.238:3000/qr" | jq
```

**Resposta esperada:**
```json
{
  "success": true,
  "qr": "data:image/png;base64,...",
  "ready": false,
  "message": "QR Code disponível para escaneamento",
  "status": "qr_ready"
}
```

### **Teste 3: QR Default**

```bash
curl -X GET "http://212.85.11.238:3000/qr/default" | jq
```

---

## 🔍 Troubleshooting

### **Problema: Servidor não reinicia**

```bash
# Verificar se há erros
pm2 logs whatsapp-api

# Parar e iniciar novamente
pm2 stop whatsapp-api
pm2 start whatsapp-api-server.js --name whatsapp-api

# Verificar se Node.js está funcionando
node --version
npm --version
```

### **Problema: Endpoints não respondem**

```bash
# Verificar se a porta está aberta
netstat -tlnp | grep 3000

# Verificar firewall
ufw status

# Testar localmente
curl http://localhost:3000/status
```

### **Problema: QR Code não aparece**

```bash
# Verificar logs do WhatsApp
pm2 logs whatsapp-api

# Verificar se a sessão foi iniciada
curl http://localhost:3000/sessions

# Forçar nova sessão
curl -X POST "http://localhost:3000/session/start/default"
```

---

## 📱 Teste no Frontend

Após aplicar as mudanças:

1. **Acesse:** `https://app.pixel12digital.com.br/painel/whatsapp.php`
2. **Clique em:** "Conectar WhatsApp"
3. **Verifique se:** O QR code aparece e atualiza
4. **Teste:** Escaneie com o WhatsApp

### **URLs de Teste Direto:**

- **Status:** `https://app.pixel12digital.com.br/painel/ajax_whatsapp.php?action=status`
- **QR Code:** `https://app.pixel12digital.com.br/painel/ajax_whatsapp.php?action=qr`
- **Teste Completo:** `https://app.pixel12digital.com.br/teste_qr_direto.php`

---

## ✅ Checklist de Verificação

- [ ] Arquivo `whatsapp-api-server.js` atualizado
- [ ] Servidor reiniciado com `pm2 restart whatsapp-api`
- [ ] Endpoint `/status` retorna QR code quando disponível
- [ ] Endpoint `/qr` funciona corretamente
- [ ] Endpoint `/qr/default` funciona corretamente
- [ ] Frontend consegue buscar QR code
- [ ] QR code atualiza automaticamente
- [ ] Conexão WhatsApp funciona após escaneamento

---

## 🆘 Suporte

Se encontrar problemas:

1. **Verifique os logs:** `pm2 logs whatsapp-api`
2. **Teste endpoints:** Use os comandos curl acima
3. **Reinicie servidor:** `pm2 restart whatsapp-api`
4. **Verifique conectividade:** `curl http://localhost:3000/status`

**Status esperado após mudanças:**
- ✅ VPS online e respondendo
- ✅ Endpoints QR funcionando
- ✅ QR code atualizando automaticamente
- ✅ Conexão WhatsApp funcionando 