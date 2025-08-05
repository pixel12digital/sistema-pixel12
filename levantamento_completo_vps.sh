#!/bin/bash

# LEVANTAMENTO COMPLETO VPS - SCRIPT COMPLETO
# Execute este script na VPS para mapear tudo

echo "🔍 === LEVANTAMENTO COMPLETO VPS ==="
echo "Data/Hora: $(date)"
echo "Servidor: $(hostname)"
echo "IP: $(hostname -I)"
echo ""

# 1. INFORMAÇÕES DO SISTEMA
echo "💻 === INFORMAÇÕES DO SISTEMA ==="
echo "Sistema: $(uname -a)"
echo "Distribuição: $(cat /etc/os-release | grep PRETTY_NAME)"
echo "Kernel: $(uname -r)"
echo "Arquitetura: $(uname -m)"
echo ""

# 2. USO DE RECURSOS
echo "📊 === USO DE RECURSOS ==="
echo "CPU:"
top -bn1 | head -10
echo ""
echo "Memória:"
free -h
echo ""
echo "Disco:"
df -h
echo ""

# 3. TODOS OS PROCESSOS ATIVOS
echo "⚙️ === TODOS OS PROCESSOS ATIVOS ==="
ps aux | head -20
echo ""

# 4. TODOS OS SERVIÇOS DO SISTEMA
echo "🔧 === SERVIÇOS DO SISTEMA ==="
systemctl list-units --type=service --state=active | head -20
echo ""

# 5. TODAS AS PORTAS EM USO
echo "🌐 === TODAS AS PORTAS EM USO ==="
netstat -tlnp
echo ""

# 6. DIRETÓRIOS PRINCIPAIS
echo "📁 === DIRETÓRIO ROOT ==="
ls -la /root/
echo ""

echo "📁 === DIRETÓRIO /VAR ==="
ls -la /var/
echo ""

echo "📁 === DIRETÓRIO /OPT ==="
ls -la /opt/
echo ""

echo "📁 === DIRETÓRIO /ETC ==="
ls -la /etc/ | head -20
echo ""

# 7. DOCKER
echo "🐳 === DOCKER ==="
echo "Containers:"
docker ps -a
echo ""
echo "Imagens:"
docker images
echo ""

# 8. CRON JOBS
echo "⏰ === CRON JOBS ==="
echo "Crontab do root:"
crontab -l
echo ""
echo "Cron.d:"
ls -la /etc/cron.d/
echo ""
echo "Cron.daily:"
ls -la /etc/cron.daily/
echo ""

# 9. USUÁRIOS
echo "👥 === USUÁRIOS ==="
cat /etc/passwd
echo ""

# 10. PM2
echo "📦 === PM2 ==="
pm2 list
echo ""
echo "PM2 startup:"
pm2 startup
echo ""

# 11. PROCESSOS POR TIPO
echo "🔍 === PROCESSOS NODE ==="
ps aux | grep node | grep -v grep
echo ""

echo "🔍 === PROCESSOS PYTHON ==="
ps aux | grep python | grep -v grep
echo ""

echo "🔍 === PROCESSOS PHP ==="
ps aux | grep php | grep -v grep
echo ""

echo "🔍 === PROCESSOS JAVA ==="
ps aux | grep java | grep -v grep
echo ""

echo "🔍 === PROCESSOS GO ==="
ps aux | grep go | grep -v grep
echo ""

echo "🔍 === PROCESSOS RUBY ==="
ps aux | grep ruby | grep -v grep
echo ""

echo "🔍 === PROCESSOS PERL ==="
ps aux | grep perl | grep -v grep
echo ""

echo "🔍 === PROCESSOS BASH ==="
ps aux | grep bash | grep -v grep
echo ""

echo "🔍 === PROCESSOS SH ==="
ps aux | grep sh | grep -v grep
echo ""

# 12. SERVIÇOS WEB
echo "🌐 === SERVIÇOS WEB ==="
echo "Nginx:"
ps aux | grep nginx | grep -v grep
echo ""
echo "Apache:"
ps aux | grep apache | grep -v grep
echo ""

# 13. BANCOS DE DADOS
echo "🗄️ === BANCOS DE DADOS ==="
echo "MySQL:"
ps aux | grep mysql | grep -v grep
echo ""
echo "PostgreSQL:"
ps aux | grep postgres | grep -v grep
echo ""
echo "Redis:"
ps aux | grep redis | grep -v grep
echo ""
echo "MongoDB:"
ps aux | grep mongo | grep -v grep
echo ""

# 14. ARQUIVOS DE CONFIGURAÇÃO
echo "⚙️ === ARQUIVOS DE CONFIGURAÇÃO ==="
echo "Config.js:"
find /var /opt /root -name "*.config.js" 2>/dev/null
echo ""
echo "Package.json:"
find /var /opt /root -name "package.json" 2>/dev/null
echo ""

# 15. LOGS DO SISTEMA
echo "📋 === LOGS DO SISTEMA ==="
echo "Syslog (últimas 20 linhas):"
tail -20 /var/log/syslog
echo ""

# 16. PROCESSOS WHATSAPP ESPECÍFICOS
echo "📱 === PROCESSOS WHATSAPP ==="
ps aux | grep -i whatsapp | grep -v grep
echo ""

# 17. DIRETÓRIOS WHATSAPP
echo "📁 === DIRETÓRIOS WHATSAPP ==="
find /var /opt /root -name "*whatsapp*" -type d 2>/dev/null
echo ""

# 18. ARQUIVOS WHATSAPP
echo "📄 === ARQUIVOS WHATSAPP ==="
find /var /opt /root -name "*whatsapp*" -type f 2>/dev/null
echo ""

# 19. PROCESSOS DE MONITORAMENTO
echo "📊 === PROCESSOS DE MONITORAMENTO ==="
ps aux | grep -i monitor | grep -v grep
echo ""

# 20. PROCESSOS DE AUTOMAÇÃO
echo "🤖 === PROCESSOS DE AUTOMAÇÃO ==="
ps aux | grep -E "(cron|automation|script)" | grep -v grep
echo ""

# 21. VERSÕES INSTALADAS
echo "📦 === VERSÕES INSTALADAS ==="
echo "Node.js: $(node --version 2>/dev/null || echo 'Não instalado')"
echo "NPM: $(npm --version 2>/dev/null || echo 'Não instalado')"
echo "PM2: $(pm2 --version 2>/dev/null || echo 'Não instalado')"
echo "Python: $(python3 --version 2>/dev/null || echo 'Não instalado')"
echo "PHP: $(php --version 2>/dev/null | head -1 || echo 'Não instalado')"
echo "Docker: $(docker --version 2>/dev/null || echo 'Não instalado')"
echo ""

# 22. REDE
echo "🌐 === INFORMAÇÕES DE REDE ==="
echo "Interfaces:"
ip addr show
echo ""
echo "Rotas:"
ip route show
echo ""

# 23. FIREWALL
echo "🔥 === FIREWALL ==="
ufw status
echo ""

# 24. ARQUIVOS DE LOG IMPORTANTES
echo "📋 === ARQUIVOS DE LOG IMPORTANTES ==="
echo "PM2 logs:"
ls -la /root/.pm2/logs/ 2>/dev/null || echo "Logs PM2 não encontrados"
echo ""
echo "WhatsApp logs:"
find /var /opt /root -name "*.log" | grep -E "(whatsapp|pm2|node)" 2>/dev/null
echo ""

echo "✅ === LEVANTAMENTO COMPLETO FINALIZADO ==="
echo "Data/Hora: $(date)"
echo "Script executado com sucesso!" 