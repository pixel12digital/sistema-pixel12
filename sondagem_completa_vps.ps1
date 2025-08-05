# SONDAGEM COMPLETA VPS - APENAS VISUALIZAÇÃO
# Execute este script para ver tudo que está instalado na VPS

Write-Host "🔍 === SONDAGEM COMPLETA VPS WHATSAPP ===" -ForegroundColor Cyan
Write-Host "Data/Hora: $(Get-Date)" -ForegroundColor Yellow
Write-Host ""

# 1. Processos WhatsApp Ativos
Write-Host "📱 1. PROCESSOS WHATSAPP ATIVOS:" -ForegroundColor Green
ssh -o StrictHostKeyChecking=no root@212.85.11.238 "ps aux | grep -i whatsapp | grep -v grep"
Write-Host ""

# 2. Serviços PM2
Write-Host "⚙️ 2. SERVIÇOS PM2:" -ForegroundColor Green
ssh -o StrictHostKeyChecking=no root@212.85.11.238 "pm2 list"
Write-Host ""

# 3. Portas em Uso
Write-Host "🌐 3. PORTAS EM USO:" -ForegroundColor Green
ssh -o StrictHostKeyChecking=no root@212.85.11.238 "netstat -tlnp | grep -E ':(3000|3001|8080|5000|8000)'"
Write-Host ""

# 4. Diretórios WhatsApp
Write-Host "📁 4. DIRETÓRIOS WHATSAPP:" -ForegroundColor Green
ssh -o StrictHostKeyChecking=no root@212.85.11.238 "find /root -name '*whatsapp*' -type d 2>/dev/null"
Write-Host ""

# 5. Instalações Node.js
Write-Host "📦 5. INSTALAÇÕES NODE.JS:" -ForegroundColor Green
ssh -o StrictHostKeyChecking=no root@212.85.11.238 "ls -la /root/ | grep -E '(whatsapp|bailey|chrome|multi)'"
Write-Host ""

# 6. Logs PM2
Write-Host "📋 6. LOGS PM2 WHATSAPP-3000:" -ForegroundColor Green
ssh -o StrictHostKeyChecking=no root@212.85.11.238 "pm2 logs whatsapp-3000 --lines 10 --nostream"
Write-Host ""

# 7. Configurações
Write-Host "⚙️ 7. CONFIGURAÇÕES ECOSYSTEM:" -ForegroundColor Green
ssh -o StrictHostKeyChecking=no root@212.85.11.238 "cat /root/ecosystem.config.js"
Write-Host ""

# 8. Sessões
Write-Host "💬 8. SESSÕES WHATSAPP:" -ForegroundColor Green
ssh -o StrictHostKeyChecking=no root@212.85.11.238 "ls -la /root/sessions/ 2>/dev/null || echo 'Diretório sessions não encontrado'"
Write-Host ""

# 9. Instalações Globais
Write-Host "🌍 9. INSTALAÇÕES GLOBAIS NPM:" -ForegroundColor Green
ssh -o StrictHostKeyChecking=no root@212.85.11.238 "npm list -g | grep -i whatsapp"
Write-Host ""

# 10. Processos Chrome
Write-Host "🌐 10. PROCESSOS CHROME/CHROMIUM:" -ForegroundColor Green
ssh -o StrictHostKeyChecking=no root@212.85.11.238 "ps aux | grep -i chrome | grep -v grep"
Write-Host ""

# 11. Uso de Recursos
Write-Host "💻 11. USO DE RECURSOS:" -ForegroundColor Green
ssh -o StrictHostKeyChecking=no root@212.85.11.238 "top -bn1 | head -20"
Write-Host ""

# 12. Firewall
Write-Host "🔥 12. STATUS FIREWALL:" -ForegroundColor Green
ssh -o StrictHostKeyChecking=no root@212.85.11.238 "ufw status"
Write-Host ""

# 13. Teste APIs
Write-Host "🔌 13. TESTE CONECTIVIDADE APIs:" -ForegroundColor Green
Write-Host "VPS 3000:" -ForegroundColor Yellow
curl.exe -s "http://212.85.11.238:3000/status"
Write-Host ""
Write-Host "VPS 3001:" -ForegroundColor Yellow
curl.exe -s "http://212.85.11.238:3001/status"
Write-Host ""

# 14. Logs de Erro
Write-Host "❌ 14. LOGS DE ERRO:" -ForegroundColor Green
ssh -o StrictHostKeyChecking=no root@212.85.11.238 "tail -20 /root/logs/whatsapp-3000-error.log 2>/dev/null || echo 'Log não encontrado'"
Write-Host ""

# 15. Configurações de Rede
Write-Host "🌐 15. CONFIGURAÇÕES DE REDE:" -ForegroundColor Green
ssh -o StrictHostKeyChecking=no root@212.85.11.238 "ss -tlnp | grep -E ':(3000|3001)'"
Write-Host ""

Write-Host "✅ Sondagem completa finalizada!" -ForegroundColor Green 