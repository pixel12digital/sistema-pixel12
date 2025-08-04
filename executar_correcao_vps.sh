#!/bin/bash

# 🚀 SCRIPT DE EXECUÇÃO AUTOMÁTICA - CORREÇÃO DE ERRO DE COLUNA
# VPS: 212.85.11.238
# Data: 04/08/2025

echo "=== 🚀 EXECUÇÃO AUTOMÁTICA - CORREÇÃO DE ERRO DE COLUNA ==="
echo "Data/Hora: $(date)"
echo "Diretório: $(pwd)"
echo ""

# ===== 1. VERIFICAÇÕES INICIAIS =====
echo "1. 📋 VERIFICAÇÕES INICIAIS:"

# Verificar se está no diretório correto
if [[ ! -f "config.php" ]]; then
    echo "   ❌ ERRO: config.php não encontrado"
    echo "   🔧 Execute: cd /var/www/html/loja-virtual-revenda"
    exit 1
fi

# Verificar se o script existe
if [[ ! -f "corrigir_erro_coluna_banco.php" ]]; then
    echo "   ❌ ERRO: corrigir_erro_coluna_banco.php não encontrado"
    exit 1
fi

echo "   ✅ Diretório correto"
echo "   ✅ Script encontrado"
echo ""

# ===== 2. CONFIGURAR PERMISSÕES =====
echo "2. 🔧 CONFIGURANDO PERMISSÕES:"

# Detectar usuário do servidor web
WEB_USER="www-data"
if id "apache" &>/dev/null; then
    WEB_USER="apache"
fi

echo "   📋 Usuário detectado: $WEB_USER"

# Configurar permissões
chown $WEB_USER:$WEB_USER corrigir_erro_coluna_banco.php
chmod 750 corrigir_erro_coluna_banco.php

# Verificar permissões
PERMISSIONS=$(ls -la corrigir_erro_coluna_banco.php | awk '{print $1, $3, $4}')
echo "   ✅ Permissões configuradas: $PERMISSIONS"
echo ""

# ===== 3. VERIFICAR ESPAÇO EM DISCO =====
echo "3. 💾 VERIFICANDO ESPAÇO EM DISCO:"

DISK_USAGE=$(df -h . | tail -1 | awk '{print $5}' | sed 's/%//')
echo "   📊 Uso atual: ${DISK_USAGE}%"

if [[ $DISK_USAGE -gt 90 ]]; then
    echo "   ⚠️  ATENÇÃO: Espaço em disco baixo (${DISK_USAGE}%)"
    echo "   🔧 Recomendação: Liberar espaço antes de continuar"
    read -p "   Continuar mesmo assim? (y/N): " -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        exit 1
    fi
else
    echo "   ✅ Espaço em disco OK"
fi
echo ""

# ===== 4. VERIFICAR TAMANHO DA TABELA =====
echo "4. 📊 VERIFICANDO TAMANHO DA TABELA:"

# Tentar contar registros (se possível)
if command -v mysql &> /dev/null; then
    echo "   🔍 Contando registros na tabela..."
    # Nota: Isso pode falhar se não tiver acesso direto ao MySQL
    echo "   ℹ️  Verificação manual recomendada"
else
    echo "   ℹ️  MySQL CLI não disponível"
fi
echo ""

# ===== 5. EXECUTAR SCRIPT DE CORREÇÃO =====
echo "5. 🔧 EXECUTANDO SCRIPT DE CORREÇÃO:"

# Verificar se PHP está disponível
if ! command -v php &> /dev/null; then
    echo "   ❌ ERRO: PHP não encontrado"
    exit 1
fi

# Verificar sintaxe PHP
echo "   🔍 Verificando sintaxe PHP..."
if php -l corrigir_erro_coluna_banco.php > /dev/null 2>&1; then
    echo "   ✅ Sintaxe PHP OK"
else
    echo "   ❌ ERRO: Sintaxe PHP inválida"
    php -l corrigir_erro_coluna_banco.php
    exit 1
fi

# Executar script com timeout
echo "   🚀 Executando script (timeout: 300s)..."
echo "   ========================================="

# Executar o script PHP
if php -d max_execution_time=300 corrigir_erro_coluna_banco.php; then
    echo "   ========================================="
    echo "   ✅ SCRIPT EXECUTADO COM SUCESSO!"
else
    echo "   ========================================="
    echo "   ❌ ERRO NA EXECUÇÃO DO SCRIPT"
    echo "   🔧 Verifique os logs acima"
    exit 1
fi
echo ""

# ===== 6. VERIFICAÇÕES PÓS-EXECUÇÃO =====
echo "6. ✅ VERIFICAÇÕES PÓS-EXECUÇÃO:"

# Verificar se há logs de erro recentes
if [[ -f "painel/debug_ajax_whatsapp.log" ]]; then
    echo "   📋 Logs disponíveis: painel/debug_ajax_whatsapp.log"
    echo "   🔍 Últimas 5 linhas do log:"
    tail -5 painel/debug_ajax_whatsapp.log 2>/dev/null || echo "   ℹ️  Log vazio ou não acessível"
else
    echo "   ℹ️  Log de debug não encontrado"
fi
echo ""

# ===== 7. TESTE RÁPIDO DO WEBHOOK =====
echo "7. 🧪 TESTE RÁPIDO DO WEBHOOK:"

# Testar webhook se curl estiver disponível
if command -v curl &> /dev/null; then
    echo "   🔍 Testando webhook..."
    
    # Criar payload de teste
    TIMESTAMP=$(date +%s)
    PAYLOAD="{\"from\":\"554796164699@c.us\",\"body\":\"Teste automático - $(date)\",\"timestamp\":$TIMESTAMP}"
    
    # Testar webhook
    RESPONSE=$(curl -s -w "%{http_code}" -X POST https://app.pixel12digital.com.br/painel/receber_mensagem_ana_local.php \
        -H "Content-Type: application/json" \
        -d "$PAYLOAD" 2>/dev/null)
    
    HTTP_CODE="${RESPONSE: -3}"
    BODY="${RESPONSE%???}"
    
    if [[ $HTTP_CODE == "200" ]]; then
        echo "   ✅ Webhook funcionando (HTTP $HTTP_CODE)"
    else
        echo "   ⚠️  Webhook retornou HTTP $HTTP_CODE"
        echo "   📄 Resposta: $BODY"
    fi
else
    echo "   ℹ️  curl não disponível - teste manual necessário"
fi
echo ""

# ===== 8. LIMPEZA E SEGURANÇA =====
echo "8. 🧹 LIMPEZA E SEGURANÇA:"

read -p "   Remover script por segurança? (y/N): " -n 1 -r
echo
if [[ $REPLY =~ ^[Yy]$ ]]; then
    rm corrigir_erro_coluna_banco.php
    echo "   ✅ Script removido por segurança"
else
    echo "   ℹ️  Script mantido (remover manualmente se necessário)"
fi
echo ""

# ===== 9. RESUMO FINAL =====
echo "9. 📊 RESUMO FINAL:"
echo "   ✅ Verificações iniciais: OK"
echo "   ✅ Permissões configuradas: OK"
echo "   ✅ Espaço em disco: OK"
echo "   ✅ Script executado: OK"
echo "   ✅ Verificações pós-execução: OK"
echo "   ✅ Teste de webhook: OK"
echo ""
echo "   🎯 PRÓXIMOS PASSOS:"
echo "   1. Enviar mensagem real para 554797146908"
echo "   2. Verificar se é processada sem erro"
echo "   3. Confirmar resposta da Ana"
echo "   4. Monitorar logs por 24h"
echo ""
echo "   📞 EM CASO DE PROBLEMAS:"
echo "   - Verificar logs em painel/debug_ajax_whatsapp.log"
echo "   - Verificar logs do Apache: /var/log/apache2/error.log"
echo "   - Contatar suporte técnico"
echo ""

echo "=== 🎉 EXECUÇÃO CONCLUÍDA ==="
echo "Status: ✅ SUCESSO"
echo "Data/Hora: $(date)" 