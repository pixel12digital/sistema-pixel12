#!/bin/bash

echo "🔄 Reiniciando PM2 com configurações corrigidas..."

# Parar todas as instâncias
echo "📱 Parando todas as instâncias do PM2..."
pm2 delete all

# Limpar logs antigos
echo "🧹 Limpando logs antigos..."
rm -rf ./logs/*.log

# Criar diretório de logs se não existir
mkdir -p ./logs

# Iniciar com o novo ecosystem
echo "🚀 Iniciando instâncias com ecosystem.config.js..."
pm2 start ecosystem.config.js

# Salvar configuração
echo "💾 Salvando configuração do PM2..."
pm2 save

# Mostrar status
echo "📊 Status das instâncias:"
pm2 list

# Mostrar logs das últimas linhas
echo "📋 Últimas linhas dos logs da instância 3001:"
pm2 logs whatsapp-3001 --lines 20 --nostream

echo "✅ Reinicialização concluída!"
echo ""
echo "🔍 Para monitorar os logs em tempo real:"
echo "   pm2 logs whatsapp-3001 --lines 50 --nostream | grep 'QR payload raw'"
echo ""
echo "🌐 Para testar o QR code:"
echo "   curl -s http://localhost:3001/qr?session=comercial | jq ." 