# 🚀 Deploy para Hostinger - Guia Atualizado

## 🎯 Nova Estratégia - Detecção Automática de Ambiente

O sistema agora **detecta automaticamente** se está rodando em desenvolvimento (XAMPP) ou produção (Hostinger) e ajusta as configurações automaticamente.

---

## ✨ Vantagens da Nova Abordagem

### **🔄 Rotina Mantida**
- ✅ `git commit` local → `git pull` via SSH na Hostinger
- ✅ **Mesmo `config.php`** para local e produção
- ✅ **Zero configuração manual** após setup inicial
- ✅ **Detecção automática** de ambiente

### **🔒 Segurança Garantida**
- ✅ Credenciais de **produção** ficam no código
- ✅ Credenciais de **desenvolvimento** são detectadas automaticamente
- ✅ API keys diferentes para teste/produção
- ✅ Cache desabilitado em desenvolvimento

---

## ⚙️ Como Funciona

### **Detecção Automática de Ambiente**
```php
// Sistema detecta automaticamente:
$is_local = (
    $_SERVER['SERVER_NAME'] === 'localhost' || 
    strpos($_SERVER['SERVER_NAME'], '127.0.0.1') !== false ||
    strpos($_SERVER['SERVER_NAME'], '.local') !== false ||
    !empty($_SERVER['XAMPP_ROOT']) ||
    strpos($_SERVER['DOCUMENT_ROOT'], 'xampp') !== false
);

if ($is_local) {
    // Configurações XAMPP (localhost, root, sem senha)
} else {
    // Configurações Hostinger (dados de produção)
}
```

### **Configurações por Ambiente**

#### **🏠 Desenvolvimento Local (XAMPP)**
```php
DB_HOST: localhost
DB_USER: root  
DB_PASS: (vazio)
DB_NAME: loja_virtual
ASAAS_API_KEY: chave_de_teste
DEBUG_MODE: true
ENABLE_CACHE: false
```

#### **🌐 Produção (Hostinger)**
```php
DB_HOST: srv1607.hstgr.io
DB_USER: u342734079_revendaweb
DB_PASS: Los@ngo#081081
DB_NAME: u342734079_revendaweb
ASAAS_API_KEY: chave_de_produção
DEBUG_MODE: false
ENABLE_CACHE: true
```

---

## 🔄 Novo Fluxo de Trabalho

### **1. Desenvolvimento Local**
```bash
# Trabalhar normalmente no XAMPP
# O sistema detecta automaticamente localhost
# Usa configurações de desenvolvimento

git add .
git commit -m "Nova funcionalidade"
git push origin main
```

### **2. Deploy na Hostinger**
```bash
# Via SSH na Hostinger
git pull origin main

# Sistema detecta automaticamente produção
# Usa configurações da Hostinger
# Pronto! ✅
```

### **3. Verificação (Opcional)**
```bash
# Para verificar qual ambiente foi detectado
tail -f logs/error.log | grep CONFIG

# Ou criar um script de teste
echo "<?php require 'config.php'; echo DEBUG_MODE ? 'DEV' : 'PROD'; ?>" > test_env.php
php test_env.php
```

---

## 🛠️ Setup Inicial (Uma vez só)

### **1. No seu XAMPP**
```bash
# Criar banco local (opcional - pode usar o da Hostinger)
mysql -u root -p
CREATE DATABASE loja_virtual;
exit;

# Sistema funcionará automaticamente
```

### **2. Primeiro Deploy**
```bash
# Via SSH na Hostinger
git clone [seu-repositorio]
cd loja-virtual-revenda

# Configurar permissões
chmod 755 painel/cache/
chmod 755 logs/

# Testar
php -r "require 'config.php'; echo 'Ambiente: ' . (DEBUG_MODE ? 'DEV' : 'PROD');"
```

---

## 🔧 Personalização (Opcional)

### **Sobrescrever Configurações Locais**
Se quiser usar configurações diferentes do padrão XAMPP:

```bash
# Criar arquivo .env.local
cp env.example .env.local

# Editar com suas configurações
# DB_HOST=meu_mysql_local
# DB_NAME=meu_banco_diferente
# ASAAS_API_KEY=minha_chave_teste
```

### **Forçar Ambiente Específico**
```php
// No início do config.php, se necessário
$is_local = true;  // Forçar desenvolvimento
$is_local = false; // Forçar produção
```

---

## 📊 Logs e Debugging

### **Verificar Detecção de Ambiente**
```bash
# Nos logs, procurar por:
grep "CONFIG" logs/error.log

# Exemplo de saída:
# [CONFIG] Ambiente detectado: DESENVOLVIMENTO | Host: localhost
# [CONFIG] Ambiente detectado: PRODUÇÃO | Host: seusite.com.br
```

### **Testar Configurações**
```php
// Criar test_config.php
<?php
require 'config.php';
echo "Ambiente: " . (DEBUG_MODE ? 'DESENVOLVIMENTO' : 'PRODUÇÃO') . "\n";
echo "Host DB: " . DB_HOST . "\n";
echo "Cache: " . (ENABLE_CACHE ? 'HABILITADO' : 'DESABILITADO') . "\n";
?>
```

---

## 🆘 Troubleshooting

### **Ambiente Detectado Incorretamente**
```bash
# Verificar variáveis do servidor
php -r "var_dump(\$_SERVER['SERVER_NAME'], \$_SERVER['DOCUMENT_ROOT']);"

# Ajustar detecção se necessário no config.php
# Adicionar mais condições à variável $is_local
```

### **Cache Funcionando em Desenvolvimento**
```bash
# Verificar se ambiente foi detectado corretamente
php -r "require 'config.php'; echo ENABLE_CACHE ? 'ON' : 'OFF';"

# Se estiver ON em localhost, há erro na detecção
```

### **Credenciais Incorretas**
```bash
# Testar conexão
php -r "
require 'config.php';
\$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
echo \$mysqli->connect_error ? 'ERRO: ' . \$mysqli->connect_error : 'CONECTADO!';
"
```

---

## 🔄 Migração de Rotina Existente

### **Se Você Já Tem o Sistema Rodando**
```bash
# 1. Backup dos configs atuais (por segurança)
cp config.php config.old.php
cp painel/config.php painel/config.old.php

# 2. Commit da nova versão
git add .
git commit -m "Implementar detecção automática de ambiente"
git push origin main

# 3. Deploy normal
# Via SSH na Hostinger:
git pull origin main

# 4. Testar
curl -s https://seusite.com/test_config.php
```

---

## ✅ Checklist Final

### **Desenvolvimento ✅**
- [ ] XAMPP detecta localhost corretamente
- [ ] Usa banco local ou remoto conforme preferência
- [ ] DEBUG_MODE = true
- [ ] ENABLE_CACHE = false
- [ ] API Asaas de teste

### **Produção ✅**
- [ ] Hostinger detecta ambiente de produção
- [ ] Conecta no banco da Hostinger
- [ ] DEBUG_MODE = false
- [ ] ENABLE_CACHE = true
- [ ] API Asaas de produção

### **Git ✅**
- [ ] `config.php` é versionado
- [ ] Mesmo arquivo funciona nos dois ambientes
- [ ] `git pull` na Hostinger funciona normalmente
- [ ] Zero configuração manual necessária

---

## 🎉 Resultado Final

**🎯 Agora você tem:**
- ✅ **Rotina mantida**: `git commit` → `git pull` SSH
- ✅ **Zero configuração**: Sistema detecta tudo automaticamente  
- ✅ **Segurança**: Ambientes isolados automaticamente
- ✅ **Simplicidade**: Um só `config.php` para tudo
- ✅ **Flexibilidade**: Pode personalizar via `.env.local` se quiser

**🚀 Deploy será simples assim:**
```bash
# Local
git add . && git commit -m "mudanças" && git push

# Hostinger (SSH)  
git pull

# Pronto! ✨
``` 