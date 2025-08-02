#!/bin/bash

echo "🧹 LIMPEZA E UNIFICAÇÃO DO WHATSAPP API"
echo "======================================="

# Variáveis
API_DIR="/var/whatsapp-api"
BACKUP_DIR="/var/whatsapp-api/backups_$(date +%Y%m%d_%H%M%S)"

echo ""
echo "📋 1. NAVEGANDO PARA O DIRETÓRIO CORRETO"
echo "-----------------------------------------"
cd $API_DIR
echo "📁 Diretório atual: $(pwd)"

echo ""
echo "📋 2. CRIANDO DIRETÓRIO DE BACKUP"
echo "----------------------------------"
mkdir -p $BACKUP_DIR
echo "✅ Backup será salvo em: $BACKUP_DIR"

echo ""
echo "📋 3. LISTANDO ARQUIVOS ATUAIS"
echo "-------------------------------"
echo "📄 Arquivos encontrados:"
ls -la *.js | grep whatsapp

echo ""
echo "📋 4. MOVENDO BACKUPS E ARQUIVOS DUPLICADOS"
echo "--------------------------------------------"
echo "🔄 Movendo arquivos para backup..."

# Mover todos os backups e arquivos duplicados
mv whatsapp-api-server.js.backup* $BACKUP_DIR/ 2>/dev/null || echo "   Nenhum backup encontrado"
mv whatsapp-api-server.js.bak $BACKUP_DIR/ 2>/dev/null || echo "   Nenhum .bak encontrado"
mv whatsapp-api-server.js.save $BACKUP_DIR/ 2>/dev/null || echo "   Nenhum .save encontrado"
mv whatsapp-api-server-3000.js $BACKUP_DIR/ 2>/dev/null || echo "   Nenhum -3000.js encontrado"

echo "✅ Arquivos movidos para backup"

echo ""
echo "📋 5. VERIFICANDO ARQUIVO PRINCIPAL"
echo "-----------------------------------"
echo "📄 Verificando whatsapp-api-server.js atual:"
if [ -f "whatsapp-api-server.js" ]; then
    echo "✅ whatsapp-api-server.js encontrado"
    echo "📄 Tamanho: $(wc -l < whatsapp-api-server.js) linhas"
    echo "📄 Última modificação: $(stat -c %y whatsapp-api-server.js)"
else
    echo "❌ whatsapp-api-server.js NÃO encontrado!"
    echo "🔄 Restaurando do backup mais recente..."
    ls -la $BACKUP_DIR/whatsapp-api-server.js.backup* | tail -1 | awk '{print $9}' | xargs -I {} cp {} whatsapp-api-server.js
    echo "✅ Restaurado do backup"
fi

echo ""
echo "📋 6. ADICIONANDO LOG DE VERSÃO"
echo "-------------------------------"
echo "📄 Adicionando log de versão no início do arquivo..."

# Criar backup do arquivo atual
cp whatsapp-api-server.js $BACKUP_DIR/whatsapp-api-server.js.before_version_log

# Adicionar log de versão no início
cat > temp_version_header.js << 'EOF'
const fs = require('fs');
const path = require('path');

// 🚩 VERSION CHECK - INÍCIO
console.log('🚩 ==========================================');
console.log('🚩 WHATSAPP API SERVER - VERSION CHECK');
console.log('🚩 ==========================================');
console.log('🚩 Arquivo sendo executado:', __filename);
console.log('🚩 Tamanho do arquivo:', fs.readFileSync(__filename).length, 'bytes');
console.log('🚩 Timestamp de execução:', new Date().toISOString());
console.log('🚩 Diretório de trabalho:', process.cwd());
console.log('🚩 PORT env:', process.env.PORT);
console.log('🚩 NODE_ENV:', process.env.NODE_ENV);
console.log('🚩 PID:', process.pid);
console.log('🚩 ==========================================');

EOF

# Combinar header com o arquivo original
cat temp_version_header.js whatsapp-api-server.js > whatsapp-api-server.js.new
mv whatsapp-api-server.js.new whatsapp-api-server.js
rm temp_version_header.js

echo "✅ Log de versão adicionado"

echo ""
echo "📋 7. VERIFICANDO SE CONTÉM LOGS DE DEBUG QR"
echo "---------------------------------------------"
echo "📄 Verificando se contém logs de debug QR..."

if grep -q "QR payload raw" whatsapp-api-server.js; then
    echo "✅ Logs de debug QR já existem"
else
    echo "❌ Logs de debug QR não encontrados"
    echo "🔄 Adicionando logs de debug QR..."
    
    # Adicionar logs de debug no handler QR
    sed -i '/client.on('\''qr'\'', (qr) => {/a\
            console.log(`🔍 [DEBUG][${sessionName}:${PORT}] QR raw →`, qr);\
            console.log(`🔍 [DEBUG][${sessionName}:${PORT}] sessionName value:`, sessionName);\
            console.log(`🔍 [DEBUG][${sessionName}:${PORT}] typeof sessionName:`, typeof sessionName);\
            console.log(`🔍 [DEBUG][${sessionName}:${PORT}] PORT value:`, PORT);\
            console.log(`🔍 [DEBUG][${sessionName}:${PORT}] clientStatus keys:`, Object.keys(clientStatus));' whatsapp-api-server.js
    
    echo "✅ Logs de debug QR adicionados"
fi

echo ""
echo "📋 8. VERIFICANDO BINDING DO EXPRESS"
echo "------------------------------------"
echo "📄 Verificando app.listen..."

if grep -q "0.0.0.0" whatsapp-api-server.js; then
    echo "✅ app.listen já está configurado com 0.0.0.0"
else
    echo "❌ app.listen não está configurado com 0.0.0.0"
    echo "🔄 Corrigindo app.listen..."
    
    sed -i "s/app.listen(PORT, () => {/app.listen(PORT, '0.0.0.0', () => {/g" whatsapp-api-server.js
    sed -i "s/API WhatsApp rodando em http:\/\/localhost:/API WhatsApp rodando em http:\/\/0.0.0.0:/g" whatsapp-api-server.js
    
    echo "✅ app.listen corrigido"
fi

echo ""
echo "📋 9. PARANDO PROCESSOS PM2"
echo "----------------------------"
echo "🛑 Parando todas as instâncias PM2..."
pm2 stop whatsapp-3000 whatsapp-3001 2>/dev/null || echo "   Instâncias já paradas"
pm2 delete whatsapp-3000 whatsapp-3001 2>/dev/null || echo "   Instâncias já deletadas"

echo ""
echo "📋 10. LIMPANDO SESSÕES E CACHE"
echo "-------------------------------"
echo "🧹 Limpando sessões..."
rm -rf sessions/* 2>/dev/null || echo "   Nenhuma sessão encontrada"

echo "🧹 Limpando cache do Puppeteer..."
rm -rf ~/.cache/puppeteer 2>/dev/null || echo "   Cache do Puppeteer não encontrado"

echo "🧹 Limpando logs antigos..."
rm -rf logs/*.log 2>/dev/null || echo "   Logs antigos não encontrados"

echo ""
echo "📋 11. INICIANDO PROCESSOS COM ARQUIVO UNIFICADO"
echo "------------------------------------------------"
echo "🚀 Iniciando instância 3000..."
pm2 start whatsapp-api-server.js --name whatsapp-3000 --env PORT=3000

echo "🚀 Iniciando instância 3001..."
pm2 start whatsapp-api-server.js --name whatsapp-3001 --env PORT=3001

echo "💾 Salvando configuração PM2..."
pm2 save

echo ""
echo "📋 12. VERIFICANDO STATUS"
echo "-------------------------"
echo "📊 Status das instâncias:"
pm2 list

echo ""
echo "📋 13. AGUARDANDO INICIALIZAÇÃO"
echo "-------------------------------"
echo "⏳ Aguardando 15 segundos para inicialização..."
sleep 15

echo ""
echo "📋 14. VERIFICANDO LOGS DE VERSÃO"
echo "---------------------------------"
echo "📊 Logs de versão da instância 3001:"
pm2 logs whatsapp-3001 --lines 15 --nostream | grep -E "(🚩|VERSION CHECK)" || echo "   Nenhum log de versão encontrado"

echo ""
echo "📋 15. TESTANDO QR CODES"
echo "------------------------"
echo "🔧 Testando QR da sessão comercial:"
curl -s http://127.0.0.1:3001/qr?session=comercial | jq -r '.qr' | head -c 50 || echo "❌ Falha no teste"

echo ""
echo "📋 16. VERIFICANDO LOGS DE QR"
echo "-----------------------------"
echo "📊 Procurando por logs de QR:"
pm2 logs whatsapp-3001 --lines 30 --nostream | grep -E "(QR raw|sessionName|comercial)" || echo "   Nenhum log de QR encontrado"

echo ""
echo "📋 17. LISTANDO ARQUIVOS FINAIS"
echo "-------------------------------"
echo "📄 Arquivos no diretório após limpeza:"
ls -la *.js | grep whatsapp

echo ""
echo "📄 Conteúdo do diretório de backup:"
ls -la $BACKUP_DIR/

echo ""
echo "✅ LIMPEZA E UNIFICAÇÃO CONCLUÍDA!"
echo ""
echo "🎯 RESUMO:"
echo "   - Arquivos duplicados movidos para: $BACKUP_DIR"
echo "   - Apenas um whatsapp-api-server.js mantido"
echo "   - Log de versão adicionado"
echo "   - PM2 reiniciado com arquivo unificado"
echo ""
echo "📝 PRÓXIMOS TESTES:"
echo "   1. Verifique os logs de versão acima"
echo "   2. Teste no painel: Atualizar Status da sessão comercial"
echo "   3. Monitore logs: pm2 logs whatsapp-3001"
echo "   4. Se necessário, restaure de: $BACKUP_DIR" 