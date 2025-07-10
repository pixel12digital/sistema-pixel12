# 📱 WhatsApp Integration - WPPConnect

Solução **100% funcional** para integração WhatsApp com seu painel de revenda.

## 🚀 Instalação Rápida (5 minutos)

### 1. No VPS
```bash
# Upload e execução
wget https://raw.githubusercontent.com/seu-usuario/seu-repo/main/instalar_rapido.sh
sudo bash instalar_rapido.sh
```

### 2. Configurar Domínio
```bash
# Obter SSL
certbot --nginx -d wpp.seudominio.com
```

### 3. Conectar WhatsApp
1. Acesse: `https://wpp.seudominio.com`
2. Clique "Nova Sessão"
3. Escaneie QR Code
4. Pronto!

## 📁 Arquivos Principais

- **`instalar_rapido.sh`** - Instalação automática
- **`api/whatsapp_simple.php`** - Classe PHP para integração
- **`teste_simples.php`** - Teste funcional

## 💻 Como Usar

### Enviar Mensagem Simples
```php
require_once 'api/whatsapp_simple.php';
$whatsapp = new WhatsAppSimple($mysqli, 'http://localhost:8080');

$resultado = $whatsapp->enviar('11999999999', 'Olá!');
```

### Enviar Cobrança Asaas
```php
$resultado = $whatsapp->enviarCobranca($cliente_id, $cobranca_id);
```

### Enviar Prospecção
```php
$resultado = $whatsapp->enviarProspeccao($cliente_id);
```

### Verificar Status
```php
$status = $whatsapp->status();
```

## ✅ Funcionalidades

- ✅ Envio de mensagens de texto
- ✅ Envio de cobranças Asaas automáticas
- ✅ Campanhas de prospecção
- ✅ Suporte automático
- ✅ Histórico de mensagens
- ✅ Interface web pronta
- ✅ API REST completa

## 🔧 Comandos Úteis

```bash
# Verificar status
pm2 status

# Ver logs
pm2 logs wppconnect

# Reiniciar
pm2 restart wppconnect

# Parar
pm2 stop wppconnect
```

## 🌐 URLs

- **Interface Web:** `https://wpp.seudominio.com`
- **API Base:** `http://localhost:8080`
- **Documentação:** https://wppconnect.io/

## 🎯 Próximos Passos

1. Configure seu domínio real
2. Teste com números reais
3. Integre no seu painel
4. Configure webhooks Asaas

---

**Solução 100% funcional e testada!** 🎉 