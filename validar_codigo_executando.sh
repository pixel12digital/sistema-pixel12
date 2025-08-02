#!/bin/bash

echo "🔍 VALIDAÇÃO E CORREÇÃO DO CÓDIGO EXECUTANDO"
echo "============================================="

# Variáveis
API_DIR="/var/whatsapp-api"
SERVER_IP="212.85.11.238"

echo ""
echo "📋 1. NAVEGANDO PARA O DIRETÓRIO CORRETO"
echo "-----------------------------------------"
cd $API_DIR
echo "📁 Diretório atual: $(pwd)"

echo ""
echo "📋 2. VERIFICANDO SE O ARQUIVO ESTÁ SENDO EXECUTADO"
echo "---------------------------------------------------"
echo "📄 Verificando se whatsapp-api-server.js existe:"
if [ -f "whatsapp-api-server.js" ]; then
    echo "✅ whatsapp-api-server.js encontrado"
    ls -la whatsapp-api-server.js
    
    echo ""
    echo "📄 Tamanho do arquivo:"
    wc -l whatsapp-api-server.js
    
    echo ""
    echo "📄 Verificando se contém as correções:"
    echo "   - Verificando se lê process.env.PORT:"
    if grep -q "process.env.PORT" whatsapp-api-server.js; then
        echo "   ✅ process.env.PORT encontrado"
    else
        echo "   ❌ process.env.PORT NÃO encontrado"
    fi
    
    echo "   - Verificando se escuta em 0.0.0.0:"
    if grep -q "0.0.0.0" whatsapp-api-server.js; then
        echo "   ✅ 0.0.0.0 encontrado"
    else
        echo "   ❌ 0.0.0.0 NÃO encontrado"
    fi
    
    echo "   - Verificando logs de debug QR:"
    if grep -q "QR payload raw" whatsapp-api-server.js; then
        echo "   ✅ Logs de debug QR encontrados"
    else
        echo "   ❌ Logs de debug QR NÃO encontrados"
    fi
    
else
    echo "❌ whatsapp-api-server.js NÃO encontrado!"
    exit 1
fi

echo ""
echo "📋 3. ADICIONANDO VALIDAÇÃO DE VERSÃO"
echo "--------------------------------------"
echo "📄 Adicionando logs de validação no início do arquivo..."

# Criar backup
cp whatsapp-api-server.js whatsapp-api-server.js.backup

# Adicionar validação no início do arquivo
cat > temp_header.js << 'EOF'
const fs = require('fs');
const path = require('path');

// VALIDAÇÃO DE VERSÃO
console.log('🔍 [VERSION CHECK] Arquivo sendo executado:', __filename);
console.log('🔍 [VERSION CHECK] Tamanho do arquivo:', fs.readFileSync(__filename).length, 'bytes');
console.log('🔍 [VERSION CHECK] Timestamp:', new Date().toISOString());
console.log('🔍 [VERSION CHECK] Diretório:', process.cwd());
console.log('🔍 [VERSION CHECK] PORT env:', process.env.PORT);
console.log('🔍 [VERSION CHECK] NODE_ENV:', process.env.NODE_ENV);

EOF

# Combinar header com o arquivo original
cat temp_header.js whatsapp-api-server.js > whatsapp-api-server.js.new
mv whatsapp-api-server.js.new whatsapp-api-server.js
rm temp_header.js

echo "✅ Validação de versão adicionada"

echo ""
echo "📋 4. VERIFICANDO E CORRIGINDO BINDING DO EXPRESS"
echo "-------------------------------------------------"
echo "📄 Verificando app.listen..."

# Verificar se está usando 0.0.0.0
if grep -q "app.listen(PORT, '0.0.0.0'" whatsapp-api-server.js; then
    echo "✅ app.listen já está configurado corretamente com 0.0.0.0"
else
    echo "❌ app.listen não está configurado com 0.0.0.0"
    echo "🔄 Corrigindo app.listen..."
    
    # Substituir app.listen para usar 0.0.0.0
    sed -i "s/app.listen(PORT, () => {/app.listen(PORT, '0.0.0.0', () => {/g" whatsapp-api-server.js
    sed -i "s/API WhatsApp rodando em http:\/\/localhost:/API WhatsApp rodando em http:\/\/0.0.0.0:/g" whatsapp-api-server.js
    
    echo "✅ app.listen corrigido"
fi

echo ""
echo "📋 5. VERIFICANDO E CORRIGINDO LOGS DE DEBUG QR"
echo "-----------------------------------------------"
echo "📄 Verificando logs de debug no handler QR..."

# Verificar se tem logs de debug QR
if grep -q "QR payload raw" whatsapp-api-server.js; then
    echo "✅ Logs de debug QR já existem"
else
    echo "❌ Logs de debug QR não encontrados"
    echo "🔄 Adicionando logs de debug QR..."
    
    # Adicionar logs de debug no handler QR
    sed -i '/client.on('\''qr'\'', (qr) => {/a\
            console.log(`🔍 [${sessionName}] QR payload raw:`, qr);\
            console.log(`🔍 [${sessionName}] sessionName value:`, sessionName);\
            console.log(`🔍 [${sessionName}] typeof sessionName:`, typeof sessionName);\
            console.log(`🔍 [${sessionName}] PORT value:`, PORT);\
            console.log(`🔍 [${sessionName}] clientStatus keys:`, Object.keys(clientStatus));' whatsapp-api-server.js
    
    echo "✅ Logs de debug QR adicionados"
fi

echo ""
echo "📋 6. LIMPANDO SESSÕES E CACHE"
echo "-------------------------------"
echo "🛑 Parando instâncias PM2..."
pm2 stop whatsapp-3000 whatsapp-3001

echo "🧹 Limpando sessões..."
rm -rf sessions/* 2>/dev/null || echo "Nenhuma sessão encontrada"

echo "🧹 Limpando cache do Puppeteer..."
rm -rf ~/.cache/puppeteer 2>/dev/null || echo "Cache do Puppeteer não encontrado"

echo "🧹 Limpando logs antigos..."
rm -rf logs/*.log 2>/dev/null || echo "Logs antigos não encontrados"

echo ""
echo "📋 7. REINICIANDO COM CÓDIGO CORRIGIDO"
echo "--------------------------------------"
echo "🚀 Reiniciando instâncias PM2..."
pm2 restart whatsapp-3000 whatsapp-3001 --update-env

echo ""
echo "📋 8. AGUARDANDO INICIALIZAÇÃO"
echo "-------------------------------"
echo "⏳ Aguardando 20 segundos para inicialização completa..."
sleep 20

echo ""
echo "📋 9. VERIFICANDO LOGS DE VALIDAÇÃO"
echo "-----------------------------------"
echo "📊 Logs de validação da instância 3001:"
pm2 logs whatsapp-3001 --lines 30 --nostream | grep -E "(VERSION CHECK|API rodando|Binding confirmado|Inicializando sessão)" || echo "Nenhum log de validação encontrado"

echo ""
echo "📋 10. TESTANDO QR LOCALMENTE"
echo "------------------------------"
echo "🔧 Testando QR da sessão comercial localmente:"
curl -s http://127.0.0.1:3001/qr?session=comercial | jq . || echo "❌ Falha no teste local"

echo ""
echo "📋 11. VERIFICANDO LOGS DE QR"
echo "------------------------------"
echo "📊 Procurando por logs de QR:"
pm2 logs whatsapp-3001 --lines 50 --nostream | grep -E "(QR payload raw|sessionName|comercial)" || echo "Nenhum log de QR encontrado"

echo ""
echo "📋 12. TESTANDO CONECTIVIDADE EXTERNA"
echo "-------------------------------------"
echo "🌍 Testando conectividade externa:"
curl -s http://${SERVER_IP}:3001/status | jq . || echo "❌ Falha na conectividade externa"

echo ""
echo "📋 13. VERIFICANDO FIREWALL"
echo "----------------------------"
echo "🔥 Status do UFW:"
ufw status | grep 3001 || echo "Porta 3001 não encontrada nas regras UFW"

if ! ufw status | grep -q "3001"; then
    echo "🔄 Liberando porta 3001 no UFW..."
    ufw allow 3001/tcp
    ufw reload
    echo "✅ Porta 3001 liberada"
fi

echo ""
echo "✅ VALIDAÇÃO E CORREÇÃO CONCLUÍDA!"
echo ""
echo "📝 PRÓXIMOS TESTES:"
echo "   1. Verifique os logs de validação acima"
echo "   2. Teste no painel: Atualizar Status da sessão comercial"
echo "   3. Monitore logs em tempo real: pm2 logs whatsapp-3001"
echo "   4. Teste externamente: curl http://${SERVER_IP}:3001/qr?session=comercial" 