# 📤 Upload para VPS - Passo a Passo

## 1. Preparar arquivos para upload

### Arquivos necessários:
- `instalar_rapido.sh`
- `api/whatsapp_simple.php`
- `teste_simples.php`
- `api/webhook.php`

## 2. Métodos de upload

### Opção A: Via SCP (Recomendado)
```bash
# No seu computador local
scp instalar_rapido.sh root@SEU_IP_VPS:/root/
scp -r api/ root@SEU_IP_VPS:/root/
scp teste_simples.php root@SEU_IP_VPS:/root/
```

### Opção B: Via SFTP/FileZilla
1. Conecte ao VPS via SFTP
2. Navegue até `/root/`
3. Faça upload dos arquivos

### Opção C: Via Git (Se tiver repositório)
```bash
# No VPS
cd /root
git clone https://github.com/seu-usuario/seu-repo.git
```

## 3. Verificar arquivos no VPS
```bash
# Conectar ao VPS
ssh root@SEU_IP_VPS

# Verificar se os arquivos estão lá
ls -la /root/
ls -la /root/api/
```

## 4. Executar instalação
```bash
# Dar permissão de execução
chmod +x instalar_rapido.sh

# Executar instalação
sudo bash instalar_rapido.sh
```

## 5. Verificar instalação
```bash
# Verificar se WPPConnect está rodando
pm2 status

# Verificar logs
pm2 logs wppconnect

# Testar API
curl http://localhost:8080/api/sessions/find
``` 