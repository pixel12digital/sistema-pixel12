# 🎯 COMANDOS CORRETOS PARA EXECUTAR NO VPS

## ⚠️ IMPORTANTE: O que NÃO fazer

**NUNCA execute estas linhas (são apenas logs de debug):**
```
🚩 ==========================================
🚩 Arquivo sendo executado: /var/whatsapp-api/whatsapp-api-server.js
🚩 PORT env: 3001
🚩 ==========================================
[DEBUG][comercial:3001] QR raw → [QR_CODE_VALIDO]
[DEBUG][comercial:3001] sessionName value: comercial
```

**Esses são apenas OUTPUTS de debug, não comandos!**

## ✅ COMANDOS CORRETOS PARA EXECUTAR

### 1. Conectar ao VPS
```bash
ssh root@212.85.11.238
cd /var/whatsapp-api
```

### 2. Verificar se os scripts estão presentes
```bash
ls -la *.sh
```

Deve mostrar:
- `teste_completo_sistema_whatsapp.sh`
- `teste_envio_recebimento_mensagens.sh`
- `monitoramento_continuo.sh`
- `verificar_status_rapido.sh`

### 3. Tornar scripts executáveis
```bash
chmod +x *.sh
```

### 4. Executar verificação rápida
```bash
./verificar_status_rapido.sh
```

### 5. Executar teste completo
```bash
./teste_completo_sistema_whatsapp.sh
```

### 6. Executar teste de mensagens
```bash
./teste_envio_recebimento_mensagens.sh
```

### 7. Monitoramento contínuo (opcional)
```bash
./monitoramento_continuo.sh
```

## 🔍 COMANDOS DE DIAGNÓSTICO

### Verificar status PM2
```bash
pm2 status
```

### Verificar logs
```bash
pm2 logs whatsapp-3000 --lines 10
pm2 logs whatsapp-3001 --lines 10
```

### Testar QR Codes
```bash
curl -s http://127.0.0.1:3000/qr?session=default | jq .
curl -s http://127.0.0.1:3001/qr?session=comercial | jq .
```

### Testar status das sessões
```bash
curl -s http://127.0.0.1:3000/status | jq .
curl -s http://127.0.0.1:3001/status | jq .
```

### Verificar portas
```bash
netstat -tlnp | grep :3000
netstat -tlnp | grep :3001
```

## 🚨 TROUBLESHOOTING

### Se os processos não iniciarem
```bash
pm2 restart all
pm2 logs --err
```

### Se o QR Code não aparecer
```bash
ufw status
pm2 restart whatsapp-3000
pm2 restart whatsapp-3001
```

### Se as mensagens não enviarem
```bash
pm2 logs whatsapp-3000 --follow
pm2 logs whatsapp-3001 --follow
```

## 📋 SEQUÊNCIA RECOMENDADA

1. **Conectar ao VPS:**
   ```bash
   ssh root@212.85.11.238
   cd /var/whatsapp-api
   ```

2. **Verificação rápida:**
   ```bash
   chmod +x verificar_status_rapido.sh
   ./verificar_status_rapido.sh
   ```

3. **Se tudo OK, teste completo:**
   ```bash
   chmod +x teste_completo_sistema_whatsapp.sh
   ./teste_completo_sistema_whatsapp.sh
   ```

4. **Teste de mensagens:**
   ```bash
   chmod +x teste_envio_recebimento_mensagens.sh
   ./teste_envio_recebimento_mensagens.sh
   ```

5. **Acessar painel:**
   ```
   http://212.85.11.238:8080/painel/comunicacao.php
   ```

## 💡 DICAS IMPORTANTES

- **Sempre copie apenas o texto após `#` ou `$`**
- **Ignore símbolos como 🚩, , ✅, ❌ - são apenas debug**
- **Execute comandos um por vez**
- **Se der erro, verifique se está no diretório correto: `/var/whatsapp-api`**
- **Use `Ctrl+C` para parar scripts em execução**

## 🎯 RESULTADO ESPERADO

Após executar todos os comandos corretamente:

1. **Ambos os processos PM2 online**
2. **QR Codes funcionando em ambas as portas**
3. **Sessões conectadas ou pendentes (prontas para QR)**
4. **Painel acessível**
5. **Testes de envio/recebimento funcionando**

---

**🎉 SUCESSO:** Sistema WhatsApp multi-canal 100% operacional! 