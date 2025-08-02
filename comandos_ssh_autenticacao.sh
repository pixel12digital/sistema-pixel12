#!/bin/bash

# Script de Diagnóstico e Correção - Problemas de Autenticação WhatsApp (MELHORADO)
# Execute este script na VPS: bash comandos_ssh_autenticacao.sh

echo "🔍 DIAGNÓSTICO DE AUTENTICAÇÃO WHATSAPP - COMANDOS SSH"
echo "======================================================"
echo ""

# Cores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Função para log colorido
log_info() {
    echo -e "${BLUE}ℹ️  $1${NC}"
}

log_success() {
    echo -e "${GREEN}✅ $1${NC}"
}

log_warning() {
    echo -e "${YELLOW}⚠️  $1${NC}"
}

log_error() {
    echo -e "${RED}❌ $1${NC}"
}

# PASSO 1: VERIFICAR LOGS DETALHADOS
echo "📋 PASSO 1: VERIFICAR LOGS DETALHADOS"
echo "------------------------------------"
echo ""

log_info "Verificando logs do whatsapp-3000 (últimas 100 linhas com filtros)..."
pm2 logs whatsapp-3000 --lines 100 | grep -E "(AUTH_FAILURE|authenticated|ready|disconnected|NAVIGATION|CONNECTION_LOST|LOGOUT)" || log_warning "Nenhum evento de autenticação encontrado no whatsapp-3000"

echo ""
log_info "Verificando logs do whatsapp-3001 (últimas 100 linhas com filtros)..."
pm2 logs whatsapp-3001 --lines 100 | grep -E "(AUTH_FAILURE|authenticated|ready|disconnected|NAVIGATION|CONNECTION_LOST|LOGOUT)" || log_warning "Nenhum evento de autenticação encontrado no whatsapp-3001"

echo ""
log_info "Verificando logs específicos de desconexão..."
pm2 logs whatsapp-3000 --lines 100 | grep -E "(AUTH_FAILURE|authenticated|disconnected)" || log_warning "Nenhum evento de autenticação/desconexão encontrado no whatsapp-3000"
pm2 logs whatsapp-3001 --lines 100 | grep -E "(AUTH_FAILURE|authenticated|disconnected)" || log_warning "Nenhum evento de autenticação/desconexão encontrado no whatsapp-3001"

echo ""
log_info "Verificando usuário do processo PM2..."
PM2_USER=$(ps -o user= -p $(pm2 pid whatsapp-3000 2>/dev/null) 2>/dev/null || echo "root")
echo "Usuário PM2: $PM2_USER"

echo ""
log_info "Verificando permissões das sessões..."
if [ -d "/var/whatsapp-api/sessions/" ]; then
    ls -la /var/whatsapp-api/sessions/
    echo ""
    if [ -d "/var/whatsapp-api/sessions/default/" ]; then
        ls -la /var/whatsapp-api/sessions/default/
    else
        log_warning "Diretório default não encontrado"
    fi
    echo ""
    if [ -d "/var/whatsapp-api/sessions/comercial/" ]; then
        ls -la /var/whatsapp-api/sessions/comercial/
    else
        log_warning "Diretório comercial não encontrado"
    fi
else
    log_error "Diretório de sessões não encontrado: /var/whatsapp-api/sessions/"
fi

echo ""
log_info "Verificando arquivos JSON nas sessões..."
find /var/whatsapp-api/sessions/ -name "*.json" -exec ls -la {} \; 2>/dev/null || log_warning "Nenhum arquivo JSON encontrado"

echo ""
log_info "Verificando se diretórios são graváveis..."
if [ -w "/var/whatsapp-api/sessions/default/" ]; then
    log_success "Diretório default é gravável"
else
    log_error "Diretório default NÃO é gravável"
fi

if [ -w "/var/whatsapp-api/sessions/comercial/" ]; then
    log_success "Diretório comercial é gravável"
else
    log_error "Diretório comercial NÃO é gravável"
fi

echo ""
echo "📋 PASSO 2: CORREÇÃO DE PERMISSÕES"
echo "----------------------------------"
echo ""

log_info "Corrigindo permissões usando usuário: $PM2_USER"
chown -R $PM2_USER:$PM2_USER /var/whatsapp-api/sessions/ 2>/dev/null && log_success "Proprietário corrigido" || log_error "Erro ao corrigir proprietário"

chmod -R 755 /var/whatsapp-api/sessions/ 2>/dev/null && log_success "Permissões gerais corrigidas" || log_error "Erro ao corrigir permissões gerais"

chmod -R 700 /var/whatsapp-api/sessions/default/ 2>/dev/null && log_success "Permissões default corrigidas" || log_error "Erro ao corrigir permissões default"

chmod -R 700 /var/whatsapp-api/sessions/comercial/ 2>/dev/null && log_success "Permissões comercial corrigidas" || log_error "Erro ao corrigir permissões comercial"

echo ""
echo "📋 PASSO 3: VERIFICAR SE NECESSÁRIO REGENERAR SESSÕES"
echo "-----------------------------------------------------"
echo ""

log_info "Verificando se há auth_failure nos logs..."
AUTH_FAILURE_3000=$(pm2 logs whatsapp-3000 --lines 50 2>/dev/null | grep -c "AUTH_FAILURE" || echo "0")
AUTH_FAILURE_3001=$(pm2 logs whatsapp-3001 --lines 50 2>/dev/null | grep -c "AUTH_FAILURE" || echo "0")

if [ "$AUTH_FAILURE_3000" -gt 0 ] || [ "$AUTH_FAILURE_3001" -gt 0 ]; then
    log_warning "AUTH_FAILURE detectado! Recomenda-se regenerar sessões."
    echo ""
    log_info "Para regenerar sessões, execute:"
    echo "   bash regenerar_sessoes.sh"
else
    log_success "Nenhum AUTH_FAILURE detectado nos logs recentes"
fi

echo ""
echo "📋 PASSO 4: TESTAR CONECTIVIDADE"
echo "--------------------------------"
echo ""

log_info "Testando status geral (comandos combinados)..."
for p in 3000 3001; do
    echo "Porta $p:"
    curl -s http://212.85.11.238:$p/status | jq . 2>/dev/null || curl -s http://212.85.11.238:$p/status
    echo ""
done

log_info "Testando QR Codes..."
echo "QR Default (3000):"
curl -s http://212.85.11.238:3000/qr?session=default | jq . 2>/dev/null || curl -s http://212.85.11.238:3000/qr?session=default

echo ""
echo "QR Comercial (3001):"
curl -s http://212.85.11.238:3001/qr?session=comercial | jq . 2>/dev/null || curl -s http://212.85.11.238:3001/qr?session=comercial

echo ""
echo "📋 PASSO 5: MONITORAMENTO CONTÍNUO"
echo "----------------------------------"
echo ""

log_info "Para monitoramento contínuo, execute:"
echo "   pm2 logs whatsapp-3000 --lines 0 | grep -E '(AUTH_FAILURE|authenticated|ready|disconnected|NAVIGATION|CONNECTION_LOST|LOGOUT)'"
echo "   pm2 logs whatsapp-3001 --lines 0 | grep -E '(AUTH_FAILURE|authenticated|ready|disconnected|NAVIGATION|CONNECTION_LOST|LOGOUT)'"

echo ""
log_success "Diagnóstico concluído!"
echo ""
log_info "Próximos passos:"
echo "1. Verifique os logs acima para identificar problemas"
echo "2. Se houver AUTH_FAILURE, execute: bash regenerar_sessoes.sh"
echo "3. Teste no painel administrativo"
echo "4. Monitore os logs em tempo real" 