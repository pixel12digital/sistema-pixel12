#!/bin/bash

echo "🔍 === SONDAGEM COMPLETA VPS WHATSAPP ==="
echo "Data/Hora: $(date)"
echo ""

# 1. Processos WhatsApp
echo "📱 1. PROCESSOS WHATSAPP ATIVOS:"
ssh -o StrictHostKeyChecking=no root@212.85.11.238 "ps aux | grep -i whatsapp | grep -v grep"
echo ""

# 2. Serviços PM2
echo "⚙️ 2. SERVIÇOS PM2:"
ssh -o StrictHostKeyChecking=no root@212.85.11.238 "pm2 list"
echo ""

# 3. Portas em Uso
echo "🌐 3. PORTAS EM USO:"
ssh -o StrictHostKeyChecking=no root@212.85.11.238 "netstat -tlnp | grep -E ':(3000|3001|8080|5000|8000)'"
echo ""

# 4. Diretórios WhatsApp
echo "📁 4. DIRETÓRIOS WHATSAPP:"
ssh -o StrictHostKeyChecking=no root@212.85.11.238 "find /root -name '*whatsapp*' -type d 2>/dev/null"
echo ""

# 5. Instalações Node.js
echo "📦 5. INSTALAÇÕES NODE.JS:"
ssh -o StrictHostKeyChecking=no root@212.85.11.238 "ls -la /root/ | grep -E '(whatsapp|bailey|chrome|multi)'"
echo ""

# 6. Logs PM2
echo "📋 6. LOGS PM2 WHATSAPP-3000:"
ssh -o StrictHostKeyChecking=no root@212.85.11.238 "pm2 logs whatsapp-3000 --lines 10 --nostream"
echo ""

# 7. Configurações
echo "⚙️ 7. CONFIGURAÇÕES ECOSYSTEM:"
ssh -o StrictHostKeyChecking=no root@212.85.11.238 "cat /root/ecosystem.config.js"
echo ""

# 8. Sessões
echo "💬 8. SESSÕES WHATSAPP:"
ssh -o StrictHostKeyChecking=no root@212.85.11.238 "ls -la /root/sessions/ 2>/dev/null || echo 'Diretório sessions não encontrado'"
echo ""

# 9. Instalações Globais
echo "🌍 9. INSTALAÇÕES GLOBAIS NPM:"
ssh -o StrictHostKeyChecking=no root@212.85.11.238 "npm list -g | grep -i whatsapp"
echo ""

# 10. Processos Chrome
echo "🌐 10. PROCESSOS CHROME/CHROMIUM:"
ssh -o StrictHostKeyChecking=no root@212.85.11.238 "ps aux | grep -i chrome | grep -v grep"
echo ""

# 11. Uso de Recursos
echo "💻 11. USO DE RECURSOS:"
ssh -o StrictHostKeyChecking=no root@212.85.11.238 "top -bn1 | head -20"
echo ""

# 12. Firewall
echo "🔥 12. STATUS FIREWALL:"
ssh -o StrictHostKeyChecking=no root@212.85.11.238 "ufw status"
echo ""

# 13. Teste APIs
echo "🔌 13. TESTE CONECTIVIDADE APIs:"
echo "VPS 3000:"
curl.exe -s "http://212.85.11.238:3000/status" || echo "❌ Não responde"
echo ""
echo "VPS 3001:"
curl.exe -s "http://212.85.11.238:3001/status" || echo "❌ Não responde"
echo ""

# 14. Logs de Erro
echo "❌ 14. LOGS DE ERRO:"
ssh -o StrictHostKeyChecking=no root@212.85.11.238 "tail -20 /root/logs/whatsapp-3000-error.log 2>/dev/null || echo 'Log não encontrado'"
echo ""

# 15. Configurações de Rede
echo "🌐 15. CONFIGURAÇÕES DE REDE:"
ssh -o StrictHostKeyChecking=no root@212.85.11.238 "ss -tlnp | grep -E ':(3000|3001)'"
echo ""

echo "✅ Sondagem completa finalizada!" 