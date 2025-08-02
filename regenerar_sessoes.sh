#!/bin/bash

# Script de Regeneração de Sessões WhatsApp (MELHORADO)
# Execute este script na VPS: bash regenerar_sessoes.sh

echo "🔄 REGENERAÇÃO DE SESSÕES WHATSAPP"
echo "=================================="
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

# Confirmar ação
echo -e "${YELLOW}⚠️  ATENÇÃO: Esta ação irá:${NC}"
echo "   - Parar os processos WhatsApp"
echo "   - Fazer backup das sessões atuais"
echo "   - Limpar todas as sessões"
echo "   - Reiniciar os processos"
echo "   - Invalidar todos os dispositivos conectados"
echo ""
read -p "Deseja continuar? (s/N): " -n 1 -r
echo ""
if [[ ! $REPLY =~ ^[Ss]$ ]]; then
    log_warning "Operação cancelada pelo usuário"
    exit 1
fi

# PASSO 1: CRIAR BACKUP
echo "📋 PASSO 1: CRIAR BACKUP DAS SESSÕES"
echo "------------------------------------"
echo ""

BACKUP_DIR="/var/whatsapp-api/sessions_backup_$(date +%Y%m%d_%H%M%S)"
log_info "Criando backup em: $BACKUP_DIR"

if [ -d "/var/whatsapp-api/sessions/" ]; then
    mkdir -p "$BACKUP_DIR"
    cp -r /var/whatsapp-api/sessions/* "$BACKUP_DIR/" 2>/dev/null
    if [ $? -eq 0 ]; then
        log_success "Backup criado com sucesso"
        echo "   Backup: $BACKUP_DIR"
        echo "   Conteúdo:"
        ls -la "$BACKUP_DIR/" 2>/dev/null || echo "   (vazio)"
    else
        log_error "Erro ao criar backup"
        exit 1
    fi
else
    log_warning "Diretório de sessões não encontrado, continuando..."
fi

# PASSO 2: PARAR PROCESSOS
echo ""
echo "📋 PASSO 2: PARAR PROCESSOS"
echo "---------------------------"
echo ""

log_info "Parando processos WhatsApp (comando combinado)..."
pm2 stop whatsapp-3000 whatsapp-3001 2>/dev/null && log_success "Processos parados" || log_warning "Erro ao parar processos"

# Aguardar processos pararem
sleep 3

# PASSO 3: LIMPAR SESSÕES
echo ""
echo "📋 PASSO 3: LIMPAR SESSÕES"
echo "--------------------------"
echo ""

log_info "Limpando sessão default..."
if [ -d "/var/whatsapp-api/sessions/default/" ]; then
    rm -rf /var/whatsapp-api/sessions/default/* 2>/dev/null
    log_success "Sessão default limpa"
else
    log_warning "Diretório default não encontrado"
fi

log_info "Limpando sessão comercial..."
if [ -d "/var/whatsapp-api/sessions/comercial/" ]; then
    rm -rf /var/whatsapp-api/sessions/comercial/* 2>/dev/null
    log_success "Sessão comercial limpa"
else
    log_warning "Diretório comercial não encontrado"
fi

# PASSO 4: REINICIAR PROCESSOS
echo ""
echo "📋 PASSO 4: REINICIAR PROCESSOS"
echo "-------------------------------"
echo ""

log_info "Iniciando processos WhatsApp (comando combinado)..."
pm2 start whatsapp-3000 whatsapp-3001 2>/dev/null && log_success "Processos iniciados" || log_error "Erro ao iniciar processos"

# PASSO 5: AGUARDAR INICIALIZAÇÃO
echo ""
echo "📋 PASSO 5: AGUARDAR INICIALIZAÇÃO"
echo "----------------------------------"
echo ""

log_info "Aguardando 15 segundos para inicialização..."
sleep 15

# PASSO 6: VERIFICAR STATUS
echo ""
echo "📋 PASSO 6: VERIFICAR STATUS"
echo "----------------------------"
echo ""

log_info "Verificando status dos processos..."
pm2 ls | grep whatsapp

echo ""
log_info "Verificando logs de inicialização (últimas 20 linhas)..."
echo "whatsapp-3000:"
pm2 logs whatsapp-3000 --lines 20 2>/dev/null | tail -20

echo ""
echo "whatsapp-3001:"
pm2 logs whatsapp-3001 --lines 20 2>/dev/null | tail -20

# PASSO 7: TESTAR CONECTIVIDADE
echo ""
echo "📋 PASSO 7: TESTAR CONECTIVIDADE"
echo "--------------------------------"
echo ""

log_info "Testando endpoints (comandos combinados)..."
for p in 3000 3001; do
    echo "Status $p:"
    curl -s http://212.85.11.238:$p/status | jq . 2>/dev/null || curl -s http://212.85.11.238:$p/status
    echo ""
done

echo "QR Default:"
curl -s http://212.85.11.238:3000/qr?session=default | jq . 2>/dev/null || curl -s http://212.85.11.238:3000/qr?session=default

echo ""
echo "QR Comercial:"
curl -s http://212.85.11.238:3001/qr?session=comercial | jq . 2>/dev/null || curl -s http://212.85.11.238:3001/qr?session=comercial

# PASSO 8: INSTRUÇÕES FINAIS
echo ""
echo "📋 PASSO 8: INSTRUÇÕES FINAIS"
echo "-----------------------------"
echo ""

log_success "Regeneração de sessões concluída!"
echo ""
log_info "Próximos passos:"
echo "1. Acesse o painel administrativo"
echo "2. Clique em 'Atualizar Status'"
echo "3. Abra o modal de conexão WhatsApp"
echo "4. Escaneie os novos QR Codes"
echo "5. Monitore os logs: pm2 logs whatsapp-3000 --lines 0"
echo ""
log_info "Backup criado em: $BACKUP_DIR"
echo "Para restaurar backup (se necessário):"
echo "   cp -r $BACKUP_DIR/* /var/whatsapp-api/sessions/"
echo ""
log_warning "Lembre-se: Todos os dispositivos conectados foram invalidados!" 