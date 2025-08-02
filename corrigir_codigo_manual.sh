#!/bin/bash

echo "🔧 CORREÇÃO MANUAL DO CÓDIGO WHATSAPP API"
echo "=========================================="

# Variáveis
API_DIR="/var/whatsapp-api"

echo ""
echo "📋 1. NAVEGANDO PARA O DIRETÓRIO CORRETO"
echo "-----------------------------------------"
cd $API_DIR
echo "📁 Diretório atual: $(pwd)"

echo ""
echo "📋 2. CRIANDO BACKUP DO ARQUIVO ATUAL"
echo "--------------------------------------"
cp whatsapp-api-server.js whatsapp-api-server.js.backup.$(date +%Y%m%d_%H%M%S)
echo "✅ Backup criado"

echo ""
echo "📋 3. VERIFICANDO CONTEÚDO ATUAL"
echo "--------------------------------"
echo "📄 Primeiras 20 linhas do arquivo:"
head -20 whatsapp-api-server.js

echo ""
echo "📄 Verificando se contém process.env.PORT:"
grep -n "process.env.PORT" whatsapp-api-server.js || echo "❌ process.env.PORT não encontrado"

echo ""
echo "📄 Verificando app.listen:"
grep -n "app.listen" whatsapp-api-server.js || echo "❌ app.listen não encontrado"

echo ""
echo "📋 4. CORRIGINDO DECLARAÇÃO DE PORT"
echo "-----------------------------------"
echo "📄 Substituindo declaração de PORT..."

# Substituir declaração de PORT
sed -i 's/const PORT = 3000;/const PORT = parseInt(process.env.PORT, 10) || 3000;/g' whatsapp-api-server.js

echo "✅ Declaração de PORT corrigida"

echo ""
echo "📋 5. CORRIGINDO APP.LISTEN"
echo "----------------------------"
echo "📄 Corrigindo app.listen para usar 0.0.0.0..."

# Substituir app.listen
sed -i "s/app.listen(PORT, () => {/app.listen(PORT, '0.0.0.0', () => {/g" whatsapp-api-server.js

echo "✅ app.listen corrigido"

echo ""
echo "📋 6. ADICIONANDO LOGS DE DEBUG"
echo "-------------------------------"
echo "📄 Adicionando logs de debug no início..."

# Adicionar logs de debug no início
cat > temp_debug.js << 'EOF'
const fs = require('fs');

// DEBUG: VALIDAÇÃO DE VERSÃO
console.log('🔍 [DEBUG] Arquivo sendo executado:', __filename);
console.log('🔍 [DEBUG] Tamanho do arquivo:', fs.readFileSync(__filename).length, 'bytes');
console.log('🔍 [DEBUG] Timestamp:', new Date().toISOString());
console.log('🔍 [DEBUG] Diretório:', process.cwd());
console.log('🔍 [DEBUG] PORT env:', process.env.PORT);
console.log('🔍 [DEBUG] NODE_ENV:', process.env.NODE_ENV);

EOF

# Combinar com o arquivo original
cat temp_debug.js whatsapp-api-server.js > whatsapp-api-server.js.new
mv whatsapp-api-server.js.new whatsapp-api-server.js
rm temp_debug.js

echo "✅ Logs de debug adicionados"

echo ""
echo "📋 7. VERIFICANDO SE AS CORREÇÕES FORAM APLICADAS"
echo "-------------------------------------------------"
echo "📄 Verificando process.env.PORT:"
grep -n "process.env.PORT" whatsapp-api-server.js

echo ""
echo "📄 Verificando app.listen:"
grep -n "app.listen" whatsapp-api-server.js

echo ""
echo "📄 Verificando logs de debug:"
grep -n "DEBUG" whatsapp-api-server.js | head -5

echo ""
echo "📋 8. LIMPANDO SESSÕES"
echo "----------------------"
echo "🛑 Parando instâncias PM2..."
pm2 stop whatsapp-3000 whatsapp-3001

echo "🧹 Limpando sessões..."
rm -rf sessions/* 2>/dev/null || echo "Nenhuma sessão encontrada"

echo "🧹 Limpando cache..."
rm -rf ~/.cache/puppeteer 2>/dev/null || echo "Cache não encontrado"

echo ""
echo "📋 9. REINICIANDO"
echo "-----------------"
echo "🚀 Reiniciando instâncias PM2..."
pm2 restart whatsapp-3000 whatsapp-3001 --update-env

echo ""
echo "📋 10. AGUARDANDO INICIALIZAÇÃO"
echo "-------------------------------"
echo "⏳ Aguardando 15 segundos..."
sleep 15

echo ""
echo "📋 11. VERIFICANDO LOGS"
echo "-----------------------"
echo "📊 Logs de debug da instância 3001:"
pm2 logs whatsapp-3001 --lines 20 --nostream | grep -E "(DEBUG|API rodando|Binding confirmado)" || echo "Nenhum log de debug encontrado"

echo ""
echo "📋 12. TESTANDO LOCALMENTE"
echo "---------------------------"
echo "🔧 Testando QR da sessão comercial:"
curl -s http://127.0.0.1:3001/qr?session=comercial | jq . || echo "❌ Falha no teste local"

echo ""
echo "✅ CORREÇÃO MANUAL CONCLUÍDA!"
echo ""
echo "📝 PRÓXIMOS PASSOS:"
echo "   1. Verifique os logs de debug acima"
echo "   2. Teste no painel: Atualizar Status da sessão comercial"
echo "   3. Se ainda houver problemas, execute: pm2 logs whatsapp-3001" 