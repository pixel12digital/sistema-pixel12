#!/bin/bash

echo "🔍 Verificando acessibilidade da porta 3001..."

# Verificar se a porta está sendo escutada
echo "📊 Verificando se a porta 3001 está sendo escutada:"
ss -tlnp | grep :3001 || netstat -tlnp | grep :3001

echo ""
echo "🌐 Verificando se está escutando em 0.0.0.0 (acessível externamente):"
ss -tlnp | grep :3001 | grep "0.0.0.0" || echo "❌ Porta 3001 não está acessível externamente"

echo ""
echo "🔧 Testando conectividade local:"
curl -s http://localhost:3001/status | jq . || echo "❌ Não consegue conectar localmente"

echo ""
echo "🌍 Testando conectividade externa (substitua pelo IP correto):"
echo "curl -s http://212.85.11.238:3001/status"
curl -s http://212.85.11.238:3001/status | jq . || echo "❌ Não consegue conectar externamente"

echo ""
echo "📋 Status do firewall (UFW):"
ufw status | grep 3001 || echo "Porta 3001 não encontrada nas regras UFW"

echo ""
echo "✅ Verificação concluída!" 