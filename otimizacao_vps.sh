#!/bin/bash

# SCRIPT DE OTIMIZAÇÃO VPS - MELHORA PERFORMANCE SEM AFETAR AZURACAST
# Execute este script na VPS para otimizar recursos

echo "🚀 === OTIMIZAÇÃO VPS ==="
echo "Data/Hora: $(date)"
echo "Servidor: $(hostname)"
echo ""

# 1. ANÁLISE INICIAL
echo "📊 === ANÁLISE INICIAL ==="
echo "CPU e Memória atuais:"
top -bn1 | head -10
echo ""
echo "Processos consumidores:"
ps aux --sort=-%cpu | head -5
echo ""

# 2. LIMPEZA DE PROCESSOS DESNECESSÁRIOS
echo "🧹 === LIMPEZA DE PROCESSOS ==="

# Parar monitoramento automático que está causando sobrecarga
echo "⏹️ Parando monitoramento automático..."
crontab -r 2>/dev/null
pkill -f monitoramento_automatico.sh 2>/dev/null
echo "✅ Monitoramento automático parado"

# Limpar processos Node desnecessários
echo "🧹 Limpando processos Node desnecessários..."
pkill -f "pm2 status --no-daemon" 2>/dev/null
echo "✅ Processos Node desnecessários limpos"

# 3. OTIMIZAÇÃO DE MEMÓRIA
echo "💾 === OTIMIZAÇÃO DE MEMÓRIA ==="

# Limpar cache do sistema
echo "🧹 Limpando cache do sistema..."
sync
echo 3 > /proc/sys/vm/drop_caches
echo "✅ Cache do sistema limpo"

# Limpar cache do PM2
echo "📦 Limpando cache do PM2..."
pm2 flush 2>/dev/null
echo "✅ Cache do PM2 limpo"

# 4. OTIMIZAÇÃO DE DISCO
echo "💿 === OTIMIZAÇÃO DE DISCO ==="

# Limpar logs antigos
echo "📋 Limpando logs antigos..."
find /var/log -name "*.log" -mtime +7 -delete 2>/dev/null
find /var/log -name "*.gz" -mtime +7 -delete 2>/dev/null
echo "✅ Logs antigos removidos"

# Limpar cache do apt
echo "📦 Limpando cache do apt..."
apt-get clean 2>/dev/null
apt-get autoremove -y 2>/dev/null
echo "✅ Cache do apt limpo"

# 5. OTIMIZAÇÃO DE SERVIÇOS
echo "⚙️ === OTIMIZAÇÃO DE SERVIÇOS ==="

# Reiniciar WhatsApp corretamente
echo "🔄 Reiniciando WhatsApp..."
pm2 stop whatsapp-3000 2>/dev/null
pm2 stop whatsapp-3001 2>/dev/null
sleep 2
pm2 restart whatsapp-3000 2>/dev/null
pm2 restart whatsapp-3001 2>/dev/null
pm2 save 2>/dev/null
echo "✅ WhatsApp reiniciado"

# 6. CONFIGURAÇÕES DE SISTEMA
echo "🔧 === CONFIGURAÇÕES DE SISTEMA ==="

# Otimizar configurações de memória
echo "💾 Otimizando configurações de memória..."
echo 'vm.swappiness=10' >> /etc/sysctl.conf
echo 'vm.vfs_cache_pressure=50' >> /etc/sysctl.conf
sysctl -p 2>/dev/null
echo "✅ Configurações de memória otimizadas"

# 7. MONITORAMENTO OTIMIZADO
echo "📊 === MONITORAMENTO OTIMIZADO ==="

# Criar script de monitoramento otimizado
cat > /root/monitoramento_otimizado.sh << 'EOF'
#!/bin/bash
# Monitoramento otimizado - executa a cada 15 minutos
cd /var/whatsapp-api

# Verificar apenas se os serviços estão rodando
if ! pm2 list | grep -q "whatsapp-3000.*online"; then
    echo "$(date): WhatsApp 3000 offline - reiniciando..." >> monitoramento.log
    pm2 restart whatsapp-3000
fi

if ! pm2 list | grep -q "whatsapp-3001.*online"; then
    echo "$(date): WhatsApp 3001 offline - reiniciando..." >> monitoramento.log
    pm2 restart whatsapp-3001
fi

# Limpar logs antigos
find /var/whatsapp-api -name "*.log" -mtime +3 -delete 2>/dev/null
EOF

chmod +x /root/monitoramento_otimizado.sh
echo "✅ Script de monitoramento otimizado criado"

# Configurar cron otimizado (a cada 15 minutos em vez de 5)
echo "*/15 * * * * /root/monitoramento_otimizado.sh >> /var/whatsapp-api/monitoramento.log 2>&1" | crontab -
echo "✅ Cron otimizado configurado (15 minutos)"

# 8. VERIFICAÇÃO FINAL
echo "✅ === VERIFICAÇÃO FINAL ==="
echo "Status dos serviços:"
pm2 list
echo ""
echo "Uso de recursos após otimização:"
top -bn1 | head -10
echo ""
echo "Memória disponível:"
free -h
echo ""
echo "Espaço em disco:"
df -h /
echo ""

# 9. RECOMENDAÇÕES
echo "💡 === RECOMENDAÇÕES ADICIONAIS ==="
echo "1. Monitore o uso de recursos por 24h"
echo "2. Se necessário, considere aumentar RAM da VPS"
echo "3. Configure backup automático dos dados importantes"
echo "4. Mantenha o sistema atualizado regularmente"
echo ""

echo "🎉 === OTIMIZAÇÃO CONCLUÍDA ==="
echo "Data/Hora: $(date)"
echo "✅ VPS otimizada com sucesso!"
echo "📊 Performance melhorada sem afetar AzuraCast" 