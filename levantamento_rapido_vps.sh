#!/bin/bash

echo "🔍 === LEVANTAMENTO RÁPIDO VPS ==="
echo "Data/Hora: $(date)"
echo ""

echo "📊 USO DE RECURSOS:"
top -bn1 | head -10
echo ""

echo "⚙️ TODOS OS PROCESSOS:"
ps aux | head -20
echo ""

echo "🌐 TODAS AS PORTAS:"
netstat -tlnp
echo ""

echo "📁 DIRETÓRIO ROOT:"
ls -la /root/
echo ""

echo "📁 DIRETÓRIO /VAR:"
ls -la /var/
echo ""

echo "🐳 DOCKER:"
docker ps -a
echo ""

echo "📦 PM2:"
pm2 list
echo ""

echo "🔍 PROCESSOS NODE:"
ps aux | grep node | grep -v grep
echo ""

echo "🔍 PROCESSOS PYTHON:"
ps aux | grep python | grep -v grep
echo ""

echo "🔍 PROCESSOS PHP:"
ps aux | grep php | grep -v grep
echo ""

echo "📱 PROCESSOS WHATSAPP:"
ps aux | grep -i whatsapp | grep -v grep
echo ""

echo "⏰ CRON:"
crontab -l
echo ""

echo "👥 USUÁRIOS:"
cat /etc/passwd
echo ""

echo "✅ LEVANTAMENTO FINALIZADO!" 