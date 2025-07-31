#!/bin/bash

# Script para criar novos canais WhatsApp
# Uso: ./criar_novo_canal.sh [PORTA] [NOME_CANAL]

set -e

# Cores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Função para imprimir com cores
print_status() {
    echo -e "${GREEN}[INFO]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

print_header() {
    echo -e "${BLUE}================================${NC}"
    echo -e "${BLUE}  CRIADOR DE CANAIS WHATSAPP${NC}"
    echo -e "${BLUE}================================${NC}"
}

# Verificar argumentos
if [ $# -lt 2 ]; then
    print_error "Uso: $0 [PORTA] [NOME_CANAL]"
    print_error "Exemplo: $0 3001 'Canal Comercial'"
    exit 1
fi

PORTA=$1
NOME_CANAL=$2
CANAL_DIR="/var/whatsapp-api-canal-$PORTA"
PM2_NAME="whatsapp-api-$PORTA"

print_header
print_status "Criando canal na porta $PORTA"
print_status "Nome do canal: $NOME_CANAL"
print_status "Diretório: $CANAL_DIR"
echo

# Verificar se já existe
if [ -d "$CANAL_DIR" ]; then
    print_warning "Diretório $CANAL_DIR já existe!"
    read -p "Deseja sobrescrever? (y/N): " -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        print_error "Operação cancelada"
        exit 1
    fi
    rm -rf "$CANAL_DIR"
fi

# 1. Criar diretório
print_status "1. Criando diretório..."
mkdir -p "$CANAL_DIR"

# 2. Copiar arquivos do canal base
print_status "2. Copiando arquivos do canal base..."
cp -r /var/whatsapp-api/* "$CANAL_DIR/"

# 3. Alterar porta no arquivo de configuração
print_status "3. Configurando porta $PORTA..."
cd "$CANAL_DIR"
sed -i "s/const PORT = 3000/const PORT = $PORTA/" whatsapp-api-server.js

# 4. Criar arquivo ecosystem.config.js
print_status "4. Criando configuração PM2..."
cat > ecosystem.config.js << EOF
module.exports = {
  apps: [{
    name: '$PM2_NAME',
    script: 'whatsapp-api-server.js',
    cwd: '$CANAL_DIR',
    instances: 1,
    autorestart: true,
    watch: false,
    max_memory_restart: '1G',
    env: {
      NODE_ENV: 'production',
      PORT: $PORTA
    },
    error_file: '/root/.pm2/logs/$PM2_NAME-error.log',
    out_file: '/root/.pm2/logs/$PM2_NAME-out.log',
    log_file: '/root/.pm2/logs/$PM2_NAME-combined.log'
  }]
}
EOF

# 5. Instalar dependências (se necessário)
print_status "5. Verificando dependências..."
if [ -f "package.json" ]; then
    npm install --production
fi

# 6. Configurar firewall
print_status "6. Configurando firewall..."
ufw allow $PORTA

# 7. Iniciar com PM2
print_status "7. Iniciando canal com PM2..."
pm2 start ecosystem.config.js

# 8. Verificar status
print_status "8. Verificando status..."
sleep 3
pm2 list | grep $PM2_NAME

# 9. Testar conectividade
print_status "9. Testando conectividade..."
sleep 2
if curl -s "http://212.85.11.238:$PORTA/status" > /dev/null; then
    print_status "✅ Canal $PORTA está respondendo!"
else
    print_warning "⚠️ Canal $PORTA não está respondendo ainda. Aguarde alguns segundos."
fi

# 10. Criar script de teste
print_status "10. Criando script de teste..."
cat > "teste_canal_$PORTA.php" << EOF
<?php
/**
 * Teste do Canal $PORTA - $NOME_CANAL
 */

echo "🔍 TESTE DO CANAL $PORTA - $NOME_CANAL\n";
echo "=====================================\n\n";

\$vps_url = "http://212.85.11.238:$PORTA";

echo "1️⃣ Testando status...\n";
\$ch = curl_init();
curl_setopt(\$ch, CURLOPT_URL, \$vps_url . "/status");
curl_setopt(\$ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt(\$ch, CURLOPT_TIMEOUT, 10);
\$response = curl_exec(\$ch);
\$http_code = curl_getinfo(\$ch, CURLINFO_HTTP_CODE);
curl_close(\$ch);

echo "   📊 HTTP Code: \$http_code\n";
echo "   📄 Resposta: \$response\n\n";

echo "2️⃣ Testando envio de mensagem...\n";
\$ch = curl_init();
curl_setopt(\$ch, CURLOPT_URL, \$vps_url . "/send");
curl_setopt(\$ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt(\$ch, CURLOPT_POST, true);
curl_setopt(\$ch, CURLOPT_POSTFIELDS, json_encode([
    'to' => '4796164699@c.us',
    'message' => "Teste canal $PORTA - " . date('H:i:s')
]));
curl_setopt(\$ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt(\$ch, CURLOPT_TIMEOUT, 15);
\$response = curl_exec(\$ch);
\$http_code = curl_getinfo(\$ch, CURLINFO_HTTP_CODE);
curl_close(\$ch);

echo "   📊 HTTP Code: \$http_code\n";
echo "   📄 Resposta: \$response\n\n";

\$data = json_decode(\$response, true);
if (\$data && isset(\$data['success']) && \$data['success']) {
    echo "   ✅ SUCESSO! Canal funcionando!\n";
} else {
    echo "   ❌ ERRO no teste\n";
}

echo "🎯 Teste concluído!\n";
?>
EOF

# 11. Criar comandos úteis
print_status "11. Criando arquivo de comandos úteis..."
cat > "comandos_canal_$PORTA.txt" << EOF
# Comandos úteis para o canal $PORTA ($NOME_CANAL)

# Ver status
pm2 show $PM2_NAME

# Ver logs
pm2 logs $PM2_NAME --lines 20

# Reiniciar
pm2 restart $PM2_NAME

# Parar
pm2 stop $PM2_NAME

# Iniciar
pm2 start $PM2_NAME

# Verificar porta
netstat -tlnp | grep :$PORTA

# Testar conectividade
curl http://212.85.11.238:$PORTA/status

# Testar envio
curl -X POST http://212.85.11.238:$PORTA/send \\
  -H 'Content-Type: application/json' \\
  -d '{"to":"4796164699@c.us","message":"Teste"}'

# Executar teste PHP
php teste_canal_$PORTA.php
EOF

# 12. Resumo final
echo
print_status "🎉 CANAL CRIADO COM SUCESSO!"
echo
echo -e "${BLUE}📋 RESUMO:${NC}"
echo "   Porta: $PORTA"
echo "   Nome: $NOME_CANAL"
echo "   Diretório: $CANAL_DIR"
echo "   PM2: $PM2_NAME"
echo "   URL: http://212.85.11.238:$PORTA"
echo
echo -e "${BLUE}📁 ARQUIVOS CRIADOS:${NC}"
echo "   - $CANAL_DIR/ecosystem.config.js"
echo "   - $CANAL_DIR/teste_canal_$PORTA.php"
echo "   - $CANAL_DIR/comandos_canal_$PORTA.txt"
echo
echo -e "${BLUE}🔧 PRÓXIMOS PASSOS:${NC}"
echo "   1. Escanear QR Code: pm2 logs $PM2_NAME"
echo "   2. Testar canal: php teste_canal_$PORTA.php"
echo "   3. Atualizar config.php com nova URL"
echo "   4. Adicionar no banco de dados"
echo
echo -e "${BLUE}📞 TESTE RÁPIDO:${NC}"
echo "   curl http://212.85.11.238:$PORTA/status"
echo

print_status "Canal $PORTA ($NOME_CANAL) está pronto para uso!" 