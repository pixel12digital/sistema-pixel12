#!/bin/bash

# Script para verificar logs após regeneração de sessões
echo "🔍 VERIFICANDO LOGS APÓS REGENERAÇÃO"
echo "===================================="
echo ""

echo "📋 1. VERIFICANDO STATUS DOS PROCESSOS"
echo "--------------------------------------"
pm2 ls | grep whatsapp

echo ""
echo "📋 2. VERIFICANDO LOGS DE INICIALIZAÇÃO"
echo "---------------------------------------"
echo "whatsapp-3000 (últimas 30 linhas):"
pm2 logs whatsapp-3000 --lines 30 --nostream

echo ""
echo "whatsapp-3001 (últimas 30 linhas):"
pm2 logs whatsapp-3001 --lines 30 --nostream

echo ""
echo "📋 3. VERIFICANDO EVENTOS DE AUTENTICAÇÃO"
echo "-----------------------------------------"
echo "whatsapp-3000 - eventos de autenticação:"
pm2 logs whatsapp-3000 --lines 50 --nostream | grep -E "(AUTH_FAILURE|authenticated|ready|disconnected|NAVIGATION|CONNECTION_LOST|LOGOUT|qr|QR)" || echo "Nenhum evento encontrado"

echo ""
echo "whatsapp-3001 - eventos de autenticação:"
pm2 logs whatsapp-3001 --lines 50 --nostream | grep -E "(AUTH_FAILURE|authenticated|ready|disconnected|NAVIGATION|CONNECTION_LOST|LOGOUT|qr|QR)" || echo "Nenhum evento encontrado"

echo ""
echo "📋 4. TESTANDO QR CODES APÓS REGENERAÇÃO"
echo "----------------------------------------"
echo "QR Default (3000):"
curl -s http://212.85.11.238:3000/qr?session=default | jq . 2>/dev/null || curl -s http://212.85.11.238:3000/qr?session=default

echo ""
echo "QR Comercial (3001):"
curl -s http://212.85.11.238:3001/qr?session=comercial | jq . 2>/dev/null || curl -s http://212.85.11.238:3001/qr?session=comercial

echo ""
echo "📋 5. VERIFICANDO PERMISSÕES DAS SESSÕES"
echo "----------------------------------------"
ls -la /var/whatsapp-api/sessions/
echo ""
ls -la /var/whatsapp-api/sessions/default/ 2>/dev/null || echo "Diretório default não encontrado"
echo ""
ls -la /var/whatsapp-api/sessions/comercial/ 2>/dev/null || echo "Diretório comercial não encontrado"

echo ""
echo "✅ Verificação concluída!" 