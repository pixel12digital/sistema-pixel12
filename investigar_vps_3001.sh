#!/bin/bash

# Script para investigar problema na porta 3001
echo "🔍 INVESTIGANDO PROBLEMA VPS 3001"
echo "================================="
echo ""

echo "📋 1. VERIFICANDO STATUS DO PROCESSO"
echo "------------------------------------"
pm2 ls | grep whatsapp-3001

echo ""
echo "📋 2. VERIFICANDO LOGS DETALHADOS"
echo "---------------------------------"
echo "Últimas 50 linhas do whatsapp-3001:"
pm2 logs whatsapp-3001 --lines 50 --nostream

echo ""
echo "📋 3. VERIFICANDO EVENTOS DE QR"
echo "-------------------------------"
echo "Filtrando eventos de QR:"
pm2 logs whatsapp-3001 --lines 100 --nostream | grep -E "(qr|QR|QR Code)" || echo "Nenhum evento de QR encontrado"

echo ""
echo "📋 4. VERIFICANDO EVENTOS DE AUTENTICAÇÃO"
echo "----------------------------------------"
echo "Filtrando eventos de autenticação:"
pm2 logs whatsapp-3001 --lines 100 --nostream | grep -E "(AUTH_FAILURE|authenticated|ready|disconnected|NAVIGATION|CONNECTION_LOST|LOGOUT)" || echo "Nenhum evento de autenticação encontrado"

echo ""
echo "📋 5. TESTANDO ENDPOINT /QR DIRETAMENTE"
echo "--------------------------------------"
echo "Testando /qr?session=comercial:"
curl -s http://212.85.11.238:3001/qr?session=comercial | jq . 2>/dev/null || curl -s http://212.85.11.238:3001/qr?session=comercial

echo ""
echo "📋 6. TESTANDO ENDPOINT /STATUS"
echo "-------------------------------"
echo "Testando /status:"
curl -s http://212.85.11.238:3001/status | jq . 2>/dev/null || curl -s http://212.85.11.238:3001/status

echo ""
echo "📋 7. VERIFICANDO SESSÃO ESPECÍFICA"
echo "----------------------------------"
echo "Testando /session/comercial/status:"
curl -s http://212.85.11.238:3001/session/comercial/status | jq . 2>/dev/null || curl -s http://212.85.11.238:3001/session/comercial/status

echo ""
echo "📋 8. VERIFICANDO PERMISSÕES DA SESSÃO"
echo "-------------------------------------"
ls -la /var/whatsapp-api/sessions/comercial/ 2>/dev/null || echo "Diretório comercial não encontrado"

echo ""
echo "✅ Investigação concluída!"
echo ""
echo "📋 PRÓXIMOS PASSOS:"
echo "1. Se houver erro nos logs, corrigir o problema"
echo "2. Se não houver erro, tentar reiniciar apenas o processo 3001"
echo "3. Se persistir, verificar se há problema no código Node.js" 