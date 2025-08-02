#!/bin/bash

# 🧪 SCRIPT DE TESTE - ENVIO E RECEBIMENTO DE MENSAGENS
# Executar no VPS via SSH: ssh root@212.85.11.238
# cd /var/whatsapp-api
# chmod +x teste_envio_recebimento_mensagens.sh
# ./teste_envio_recebimento_mensagens.sh

echo "📱 TESTE DE ENVIO E RECEBIMENTO DE MENSAGENS WHATSAPP"
echo "===================================================="
echo ""

# Cores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Função para log colorido
log_info() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

log_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

log_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

log_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Função para gerar timestamp
timestamp() {
    date '+%Y-%m-%d %H:%M:%S'
}

# Função para testar envio de mensagem
test_send_message() {
    local port=$1
    local session=$2
    local phone=$3
    local message=$4
    
    log_info "Testando envio via porta $port (sessão: $session)..."
    
    # Preparar payload
    local payload=$(cat <<EOF
{
    "session": "$session",
    "phone": "$phone",
    "message": "$message"
}
EOF
)
    
    # Enviar mensagem
    local response=$(curl -s -X POST \
        -H "Content-Type: application/json" \
        -d "$payload" \
        "http://127.0.0.1:$port/send/text")
    
    # Verificar resposta
    if echo "$response" | jq -e '.success' >/dev/null 2>&1; then
        local success=$(echo "$response" | jq -r '.success')
        if [ "$success" = "true" ]; then
            log_success "Mensagem enviada com sucesso via $session"
            echo "Resposta: $response"
            return 0
        else
            log_error "Falha no envio via $session"
            echo "Resposta: $response"
            return 1
        fi
    else
        log_error "Resposta inválida do servidor $session"
        echo "Resposta: $response"
        return 1
    fi
}

# Função para verificar logs de mensagens recebidas
check_received_messages() {
    local port=$1
    local session=$2
    
    log_info "Verificando logs de mensagens recebidas em $session..."
    
    # Verificar logs recentes por mensagens recebidas
    local recent_logs=$(pm2 logs "whatsapp-$port" --lines 20 2>/dev/null | grep -i "message\|mensagem" | tail -5)
    
    if [ -n "$recent_logs" ]; then
        log_success "Logs de mensagens encontrados em $session:"
        echo "$recent_logs"
    else
        log_warning "Nenhum log de mensagem recente encontrado em $session"
    fi
}

# Função para testar webhook
test_webhook() {
    local port=$1
    local session=$2
    
    log_info "Testando webhook para sessão $session..."
    
    # Simular payload de webhook
    local webhook_payload=$(cat <<EOF
{
    "session": "$session",
    "from": "5511999999999",
    "message": "Teste de webhook - $(timestamp)",
    "timestamp": "$(date +%s)"
}
EOF
)
    
    # Enviar para webhook (se configurado)
    local webhook_response=$(curl -s -X POST \
        -H "Content-Type: application/json" \
        -d "$webhook_payload" \
        "http://212.85.11.238:8080/api/webhook.php")
    
    if [ -n "$webhook_response" ]; then
        log_success "Webhook respondeu para $session"
        echo "Resposta: $webhook_response"
    else
        log_warning "Webhook não respondeu para $session"
    fi
}

echo "📋 ETAPA 1: VERIFICAÇÃO DE STATUS DAS SESSÕES"
echo "=============================================="

# Verificar status das sessões
log_info "Verificando status das sessões..."

# Sessão default (3000)
STATUS_DEFAULT=$(curl -s http://127.0.0.1:3000/status)
if echo "$STATUS_DEFAULT" | jq -e '.ready' >/dev/null 2>&1; then
    READY_DEFAULT=$(echo "$STATUS_DEFAULT" | jq -r '.ready')
    if [ "$READY_DEFAULT" = "true" ]; then
        log_success "Sessão default (3000) está CONECTADA"
        DEFAULT_READY=true
    else
        log_warning "Sessão default (3000) está PENDENTE"
        DEFAULT_READY=false
    fi
else
    log_error "Não foi possível verificar status da sessão default"
    DEFAULT_READY=false
fi

# Sessão comercial (3001)
STATUS_COMERCIAL=$(curl -s http://127.0.0.1:3001/status)
if echo "$STATUS_COMERCIAL" | jq -e '.ready' >/dev/null 2>&1; then
    READY_COMERCIAL=$(echo "$STATUS_COMERCIAL" | jq -r '.ready')
    if [ "$READY_COMERCIAL" = "true" ]; then
        log_success "Sessão comercial (3001) está CONECTADA"
        COMERCIAL_READY=true
    else
        log_warning "Sessão comercial (3001) está PENDENTE"
        COMERCIAL_READY=false
    fi
else
    log_error "Não foi possível verificar status da sessão comercial"
    COMERCIAL_READY=false
fi

echo ""
echo "📤 ETAPA 2: TESTE DE ENVIO DE MENSAGENS"
echo "======================================="

# Solicitar número de telefone para teste
echo ""
read -p "Digite o número de telefone para teste (ex: 5511999999999): " TEST_PHONE

if [ -z "$TEST_PHONE" ]; then
    log_warning "Número não informado. Usando número padrão para teste."
    TEST_PHONE="5511999999999"
fi

# Testar envio na sessão default
if [ "$DEFAULT_READY" = "true" ]; then
    test_send_message "3000" "default" "$TEST_PHONE" "🧪 Teste de envio - Canal Default - $(timestamp)"
else
    log_warning "Sessão default não está conectada. Pulando teste de envio."
fi

# Testar envio na sessão comercial
if [ "$COMERCIAL_READY" = "true" ]; then
    test_send_message "3001" "comercial" "$TEST_PHONE" "🧪 Teste de envio - Canal Comercial - $(timestamp)"
else
    log_warning "Sessão comercial não está conectada. Pulando teste de envio."
fi

echo ""
echo "📥 ETAPA 3: VERIFICAÇÃO DE LOGS DE RECEBIMENTO"
echo "=============================================="

# Aguardar um pouco para processamento
log_info "Aguardando 3 segundos para processamento..."
sleep 3

# Verificar logs de mensagens recebidas
check_received_messages "3000" "default"
check_received_messages "3001" "comercial"

echo ""
echo "🔄 ETAPA 4: TESTE DE WEBHOOK"
echo "============================"

# Testar webhook para ambas as sessões
test_webhook "3000" "default"
test_webhook "3001" "comercial"

echo ""
echo "📊 ETAPA 5: VERIFICAÇÃO DE CONECTIVIDADE COM PAINEL"
echo "=================================================="

# Verificar se o painel está acessível
log_info "Verificando conectividade com o painel..."
PANEL_STATUS=$(curl -s -o /dev/null -w "%{http_code}" http://212.85.11.238:8080/painel/)

if [ "$PANEL_STATUS" = "200" ]; then
    log_success "Painel está acessível (HTTP $PANEL_STATUS)"
else
    log_warning "Painel retornou HTTP $PANEL_STATUS"
fi

# Verificar endpoint de chat
CHAT_STATUS=$(curl -s -o /dev/null -w "%{http_code}" http://212.85.11.238:8080/painel/chat.php)

if [ "$CHAT_STATUS" = "200" ]; then
    log_success "Chat central está acessível (HTTP $CHAT_STATUS)"
else
    log_warning "Chat central retornou HTTP $CHAT_STATUS"
fi

echo ""
echo "🔍 ETAPA 6: VERIFICAÇÃO DE DEBUG E SESSIONNAME"
echo "=============================================="

# Verificar logs de debug para sessionName
log_info "Verificando logs de debug para sessionName..."

# Logs da sessão default
log_info "Logs de debug - Sessão default (3000):"
pm2 logs whatsapp-3000 --lines 15 2>/dev/null | grep -E "(DEBUG|sessionName|default)" | tail -5

# Logs da sessão comercial
log_info "Logs de debug - Sessão comercial (3001):"
pm2 logs whatsapp-3001 --lines 15 2>/dev/null | grep -E "(DEBUG|sessionName|comercial)" | tail -5

echo ""
echo "📈 RESUMO FINAL - TESTE DE MENSAGENS"
echo "===================================="

echo ""
echo "✅ CRITÉRIOS DE SUCESSO PARA MENSAGENS:"
echo ""

# Contadores
SUCCESS_COUNT=0
TOTAL_TESTS=0

# Verificar status das sessões
TOTAL_TESTS=$((TOTAL_TESTS + 2))
if [ "$DEFAULT_READY" = "true" ]; then
    echo "✔️ Sessão default está CONECTADA"
    SUCCESS_COUNT=$((SUCCESS_COUNT + 1))
else
    echo "❌ Sessão default está PENDENTE"
fi

if [ "$COMERCIAL_READY" = "true" ]; then
    echo "✔️ Sessão comercial está CONECTADA"
    SUCCESS_COUNT=$((SUCCESS_COUNT + 1))
else
    echo "❌ Sessão comercial está PENDENTE"
fi

# Verificar painel
TOTAL_TESTS=$((TOTAL_TESTS + 2))
if [ "$PANEL_STATUS" = "200" ]; then
    echo "✔️ Painel está acessível"
    SUCCESS_COUNT=$((SUCCESS_COUNT + 1))
else
    echo "❌ Painel não está acessível"
fi

if [ "$CHAT_STATUS" = "200" ]; then
    echo "✔️ Chat central está acessível"
    SUCCESS_COUNT=$((SUCCESS_COUNT + 1))
else
    echo "❌ Chat central não está acessível"
fi

echo ""
echo "📊 RESULTADO: $SUCCESS_COUNT/$TOTAL_TESTS testes passaram"
echo ""

if [ $SUCCESS_COUNT -eq $TOTAL_TESTS ]; then
    log_success "🎉 SISTEMA DE MENSAGENS 100% OPERACIONAL!"
    echo ""
    echo "📋 PRÓXIMOS PASSOS PARA TESTE MANUAL:"
    echo "1. Acesse: http://212.85.11.238:8080/painel/chat.php"
    echo "2. Selecione um cliente e escolha o canal de envio"
    echo "3. Envie uma mensagem teste pelo painel"
    echo "4. Verifique se a mensagem chega no WhatsApp"
    echo "5. Envie uma mensagem do WhatsApp para o número do canal"
    echo "6. Verifique se aparece no chat central"
    echo ""
else
    log_warning "⚠️ ALGUNS TESTES FALHARAM."
    echo ""
    echo "🔧 AÇÕES CORRETIVAS:"
    if [ "$DEFAULT_READY" = "false" ] || [ "$COMERCIAL_READY" = "false" ]; then
        echo "1. Conecte as sessões pendentes:"
        echo "   - Acesse: http://212.85.11.238:8080/painel/comunicacao.php"
        echo "   - Clique em 'Conectar' nos canais pendentes"
        echo "   - Escaneie os QR Codes"
    fi
    echo "2. Verifique logs de erro: pm2 logs --err"
    echo "3. Reinicie processos se necessário: pm2 restart all"
    echo "4. Execute novamente este script após correções"
    echo ""
fi

echo "📱 TESTE DE MENSAGENS FINALIZADO!"
echo "====================================================" 