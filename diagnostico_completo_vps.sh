#!/bin/bash

echo "=== 🔍 DIAGNÓSTICO COMPLETO VPS WHATSAPP ==="
echo "Data/Hora: $(date)"
echo ""

# 1. VERIFICAR PROCESSOS PM2
echo "1. 📋 VERIFICANDO PROCESSOS PM2:"
pm2 status
echo ""

# 2. VERIFICAR LOGS DE ERRO
echo "2. 🚨 VERIFICANDO LOGS DE ERRO (últimas 30 linhas):"
echo "--- LOGS WHATSAPP-3000 ---"
pm2 logs whatsapp-3000 --lines 30 --nostream
echo ""
echo "--- LOGS WHATSAPP-3001 ---"
pm2 logs whatsapp-3001 --lines 30 --nostream
echo ""

# 3. VERIFICAR ENDPOINTS HTTP
echo "3. 🌐 TESTANDO ENDPOINTS HTTP:"
echo "--- STATUS 3000 ---"
curl -s http://localhost:3000/status | jq . 2>/dev/null || curl -s http://localhost:3000/status
echo ""
echo "--- STATUS 3001 ---"
curl -s http://localhost:3001/status | jq . 2>/dev/null || curl -s http://localhost:3001/status
echo ""

# 4. VERIFICAR ARQUIVO JAVASCRIPT
echo "4. 📄 VERIFICANDO ARQUIVO JAVASCRIPT:"
echo "--- PRIMEIRAS 10 LINHAS ---"
head -10 /var/whatsapp-api/whatsapp-api-server.js
echo ""
echo "--- ÚLTIMAS 10 LINHAS ---"
tail -10 /var/whatsapp-api/whatsapp-api-server.js
echo ""

# 5. VERIFICAR SINTAXE JAVASCRIPT
echo "5. 🔧 VERIFICANDO SINTAXE JAVASCRIPT:"
node -c /var/whatsapp-api/whatsapp-api-server.js
echo ""

# 6. VERIFICAR DEPENDÊNCIAS NODE.JS
echo "6. 📦 VERIFICANDO DEPENDÊNCIAS:"
cd /var/whatsapp-api
ls -la node_modules/ | head -10
echo ""

# 7. VERIFICAR DIRETÓRIO DE SESSÕES
echo "7. 📁 VERIFICANDO DIRETÓRIO DE SESSÕES:"
ls -la /var/whatsapp-api/sessions/ 2>/dev/null || echo "Diretório sessions não existe"
echo ""

# 8. VERIFICAR ESPAÇO EM DISCO
echo "8. 💾 VERIFICANDO ESPAÇO EM DISCO:"
df -h /var/whatsapp-api
echo ""

# 9. VERIFICAR MEMÓRIA E CPU
echo "9. 🖥️ VERIFICANDO RECURSOS DO SISTEMA:"
free -h
echo ""
top -bn1 | head -10
echo ""

# 10. VERIFICAR PORTAS EM USO
echo "10. 🔌 VERIFICANDO PORTAS EM USO:"
netstat -tlnp | grep -E ":(3000|3001)"
echo ""

# 11. TESTAR INICIALIZAÇÃO MANUAL
echo "11. 🚀 TESTANDO INICIALIZAÇÃO MANUAL:"
echo "--- INICIANDO SESSÃO DEFAULT ---"
curl -X POST http://localhost:3000/session/start/default -H "Content-Type: application/json"
echo ""
echo "--- INICIANDO SESSÃO COMERCIAL ---"
curl -X POST http://localhost:3001/session/start/comercial -H "Content-Type: application/json"
echo ""

# 12. VERIFICAR QR CODE
echo "12. 📱 VERIFICANDO QR CODE:"
echo "--- QR DEFAULT ---"
curl -s http://localhost:3000/qr?session=default | jq . 2>/dev/null || curl -s http://localhost:3000/qr?session=default
echo ""
echo "--- QR COMERCIAL ---"
curl -s http://localhost:3001/qr?session=comercial | jq . 2>/dev/null || curl -s http://localhost:3001/qr?session=comercial
echo ""

echo "=== 🎯 DIAGNÓSTICO CONCLUÍDO ==="
echo "Verifique os resultados acima para identificar o problema." 