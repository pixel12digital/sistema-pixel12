#!/bin/bash

echo "⚡ === OTIMIZAÇÃO RÁPIDA VPS ==="
echo "Data/Hora: $(date)"
echo ""

# 1. PARAR MONITORAMENTO PESADO
echo "⏹️ Parando monitoramento pesado..."
crontab -r 2>/dev/null
pkill -f monitoramento_automatico.sh 2>/dev/null
pkill -f "pm2 status --no-daemon" 2>/dev/null
echo "✅ Monitoramento pesado parado"

# 2. LIMPAR CACHE
echo "🧹 Limpando cache..."
sync
echo 3 > /proc/sys/vm/drop_caches
pm2 flush 2>/dev/null
echo "✅ Cache limpo"

# 3. REINICIAR WHATSAPP
echo "🔄 Reiniciando WhatsApp..."
pm2 stop whatsapp-3000 2>/dev/null
pm2 stop whatsapp-3001 2>/dev/null
sleep 2
pm2 restart whatsapp-3000 2>/dev/null
pm2 restart whatsapp-3001 2>/dev/null
pm2 save 2>/dev/null
echo "✅ WhatsApp reiniciado"

# 4. MONITORAMENTO OTIMIZADO
echo "📊 Configurando monitoramento otimizado..."
echo "*/15 * * * * cd /var/whatsapp-api && pm2 list | grep -q 'online' || pm2 restart all" | crontab -
echo "✅ Monitoramento otimizado (15 min)"

# 5. VERIFICAÇÃO
echo "📊 Status final:"
pm2 list
echo ""
echo "💾 Memória:"
free -h
echo ""
echo "✅ Otimização rápida concluída!" 