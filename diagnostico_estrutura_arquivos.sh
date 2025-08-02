#!/bin/bash

echo "🔍 DIAGNÓSTICO ESTRUTURA DE ARQUIVOS WHATSAPP API"
echo "=================================================="

# Variáveis
API_DIR="/var/whatsapp-api"
CURRENT_DIR=$(pwd)

echo ""
echo "📋 1. VERIFICANDO DIRETÓRIO ATUAL"
echo "----------------------------------"
echo "📁 Diretório atual: $CURRENT_DIR"
echo "📁 Diretório esperado: $API_DIR"

echo ""
echo "📋 2. VERIFICANDO SE ESTAMOS NO LOCAL CORRETO"
echo "----------------------------------------------"
if [ "$CURRENT_DIR" = "$API_DIR" ]; then
    echo "✅ Estamos no diretório correto: $API_DIR"
else
    echo "❌ Estamos no diretório errado!"
    echo "   Atual: $CURRENT_DIR"
    echo "   Esperado: $API_DIR"
    echo ""
    echo "🔄 Navegando para o diretório correto..."
    cd $API_DIR
    echo "📁 Novo diretório: $(pwd)"
fi

echo ""
echo "📋 3. VERIFICANDO ARQUIVOS NECESSÁRIOS"
echo "--------------------------------------"
echo "📄 Verificando ecosystem.config.js:"
if [ -f "ecosystem.config.js" ]; then
    echo "✅ ecosystem.config.js encontrado"
    ls -la ecosystem.config.js
    echo ""
    echo "📄 Conteúdo do ecosystem.config.js:"
    cat ecosystem.config.js | head -30
else
    echo "❌ ecosystem.config.js NÃO encontrado!"
    echo "🔍 Procurando em outros locais..."
    find / -name "ecosystem.config.js" 2>/dev/null | head -10
fi

echo ""
echo "📄 Verificando whatsapp-api-server.js:"
if [ -f "whatsapp-api-server.js" ]; then
    echo "✅ whatsapp-api-server.js encontrado"
    ls -la whatsapp-api-server.js
else
    echo "❌ whatsapp-api-server.js NÃO encontrado!"
    echo "🔍 Procurando em outros locais..."
    find / -name "whatsapp-api-server.js" 2>/dev/null | head -10
fi

echo ""
echo "📄 Verificando scripts de automação:"
SCRIPTS=("configurar_firewall.sh" "verificacao_exaustiva_whatsapp.sh" "reinicializacao_completa_whatsapp.sh" "testar_conectividade_completa.sh")
for script in "${SCRIPTS[@]}"; do
    if [ -f "$script" ]; then
        echo "✅ $script encontrado"
    else
        echo "❌ $script NÃO encontrado"
    fi
done

echo ""
echo "📋 4. LISTANDO TODOS OS ARQUIVOS NO DIRETÓRIO"
echo "----------------------------------------------"
echo "📁 Conteúdo do diretório atual:"
ls -la

echo ""
echo "📋 5. VERIFICANDO STATUS DO PM2"
echo "--------------------------------"
echo "📊 Status atual do PM2:"
pm2 list

echo ""
echo "📋 6. VERIFICANDO SE AS PORTAS ESTÃO SENDO ESCUTADAS"
echo "----------------------------------------------------"
echo "🔍 Verificando porta 3000:"
ss -tlnp | grep :3000 || echo "   ❌ Porta 3000 não está sendo escutada"
echo "🔍 Verificando porta 3001:"
ss -tlnp | grep :3001 || echo "   ❌ Porta 3001 não está sendo escutada"

echo ""
echo "📋 7. TESTANDO CONECTIVIDADE LOCAL"
echo "----------------------------------"
echo "🔧 Testando porta 3000:"
curl -s http://127.0.0.1:3000/status 2>/dev/null | head -5 || echo "❌ Falha na porta 3000"
echo ""
echo "🔧 Testando porta 3001:"
curl -s http://127.0.0.1:3001/status 2>/dev/null | head -5 || echo "❌ Falha na porta 3001"

echo ""
echo "✅ DIAGNÓSTICO CONCLUÍDO!"
echo ""
echo "📝 PRÓXIMOS PASSOS:"
echo "   1. Se arquivos não encontrados: Transferir do repositório"
echo "   2. Se no diretório errado: cd /var/whatsapp-api"
echo "   3. Se PM2 não rodando: pm2 start ecosystem.config.js"
echo "   4. Se portas não acessíveis: Verificar logs do PM2" 