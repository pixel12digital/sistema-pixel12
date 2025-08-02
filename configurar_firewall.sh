#!/bin/bash

echo "🔥 Configurando firewall para porta 3001..."

# Verificar se UFW está ativo
if command -v ufw &> /dev/null; then
    echo "📋 Status atual do UFW:"
    ufw status
    
    echo ""
    echo "🔓 Permitindo porta 3001 no UFW..."
    ufw allow 3001/tcp
    
    echo ""
    echo "🔄 Recarregando UFW..."
    ufw reload
    
    echo ""
    echo "📋 Status atualizado do UFW:"
    ufw status | grep 3001
else
    echo "⚠️ UFW não encontrado, tentando iptables..."
    
    # Configurar iptables
    echo "🔓 Permitindo porta 3001 no iptables..."
    iptables -A INPUT -p tcp --dport 3001 -j ACCEPT
    
    echo "💾 Salvando regras do iptables..."
    iptables-save > /etc/iptables/rules.v4 2>/dev/null || echo "⚠️ Não foi possível salvar regras do iptables"
fi

echo ""
echo "🌐 Verificando se a porta está acessível..."
ss -tlnp | grep :3001

echo ""
echo "✅ Configuração do firewall concluída!"
echo ""
echo "🔍 Para testar conectividade externa:"
echo "   curl -s http://212.85.11.238:3001/status | jq ." 