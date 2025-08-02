#!/bin/bash

# 🔍 SCRIPT DE MONITORAMENTO CONTÍNUO - SISTEMA WHATSAPP
# Executar no VPS via SSH: ssh root@212.85.11.238
# cd /var/whatsapp-api
# chmod +x monitoramento_continuo.sh
# ./monitoramento_continuo.sh

echo "🔍 INICIANDO MONITORAMENTO CONTÍNUO DO SISTEMA WHATSAPP"
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
    echo -e "${BLUE}[$(date '+%H:%M:%S')][INFO]${NC} $1"
}

log_success() {
    echo -e "${GREEN}[$(date '+%H:%M:%S')][SUCCESS]${NC} $1"
}

log_warning() {
    echo -e "${YELLOW}[$(date '+%H:%M:%S')][WARNING]${NC} $1"
}

log_error() {
    echo -e "${RED}[$(date '+%H:%M:%S')][ERROR]${NC} $1"
}

# Função para verificar status dos processos
check_pm2_status() {
    local status=$(pm2 list 2>/dev/null | grep -E "(whatsapp-3000|whatsapp-3001)")
    
    if echo "$status" | grep -q "whatsapp-3000.*online"; then
        log_success "Processo whatsapp-3000 (default) ONLINE"
        return 0
    else
        log_error "Processo whatsapp-3000 (default) OFFLINE"
        return 1
    fi
    
    if echo "$status" | grep -q "whatsapp-3001.*online"; then
        log_success "Processo whatsapp-3001 (comercial) ONLINE"
        return 0
    else
        log_error "Processo whatsapp-3001 (comercial) OFFLINE"
        return 1
    fi
}

# Função para verificar conectividade das portas
check_ports() {
    local port_3000=$(netstat -tlnp 2>/dev/null | grep -c ":3000 ")
    local port_3001=$(netstat -tlnp 2>/dev/null | grep -c ":3001 ")
    
    if [ "$port_3000" -gt 0 ]; then
        log_success "Porta 3000 está aberta"
    else
        log_error "Porta 3000 não está aberta"
    fi
    
    if [ "$port_3001" -gt 0 ]; then
        log_success "Porta 3001 está aberta"
    else
        log_error "Porta 3001 não está aberta"
    fi
}

# Função para verificar status das sessões
check_sessions() {
    # Verificar sessão default
    local status_default=$(curl -s http://127.0.0.1:3000/status 2>/dev/null)
    if echo "$status_default" | jq -e '.ready' >/dev/null 2>&1; then
        local ready_default=$(echo "$status_default" | jq -r '.ready')
        if [ "$ready_default" = "true" ]; then
            log_success "Sessão default CONECTADA"
        else
            log_warning "Sessão default PENDENTE"
        fi
    else
        log_error "Não foi possível verificar sessão default"
    fi
    
    # Verificar sessão comercial
    local status_comercial=$(curl -s http://127.0.0.1:3001/status 2>/dev/null)
    if echo "$status_comercial" | jq -e '.ready' >/dev/null 2>&1; then
        local ready_comercial=$(echo "$status_comercial" | jq -r '.ready')
        if [ "$ready_comercial" = "true" ]; then
            log_success "Sessão comercial CONECTADA"
        else
            log_warning "Sessão comercial PENDENTE"
        fi
    else
        log_error "Não foi possível verificar sessão comercial"
    fi
}

# Função para verificar conectividade com painel
check_panel() {
    local panel_status=$(curl -s -o /dev/null -w "%{http_code}" http://212.85.11.238:8080/painel/ 2>/dev/null)
    
    if [ "$panel_status" = "200" ]; then
        log_success "Painel acessível (HTTP $panel_status)"
    else
        log_error "Painel não acessível (HTTP $panel_status)"
    fi
}

# Função para verificar uso de memória
check_memory() {
    local memory_usage=$(pm2 monit --no-daemon 2>/dev/null | grep -E "(whatsapp-3000|whatsapp-3001)" | awk '{print $4}' | head -2)
    
    if [ -n "$memory_usage" ]; then
        log_info "Uso de memória: $memory_usage"
    fi
}

# Função para verificar logs de erro recentes
check_error_logs() {
    local error_logs=$(pm2 logs --err --lines 5 2>/dev/null | tail -5)
    
    if [ -n "$error_logs" ]; then
        log_warning "Logs de erro recentes:"
        echo "$error_logs"
    fi
}

# Função para verificar mensagens recentes
check_recent_messages() {
    local default_messages=$(pm2 logs whatsapp-3000 --lines 10 2>/dev/null | grep -i "message\|mensagem" | tail -3)
    local comercial_messages=$(pm2 logs whatsapp-3001 --lines 10 2>/dev/null | grep -i "message\|mensagem" | tail -3)
    
    if [ -n "$default_messages" ]; then
        log_info "Mensagens recentes - Default:"
        echo "$default_messages"
    fi
    
    if [ -n "$comercial_messages" ]; then
        log_info "Mensagens recentes - Comercial:"
        echo "$comercial_messages"
    fi
}

# Função principal de monitoramento
monitor_system() {
    log_info "=== VERIFICAÇÃO DE STATUS ==="
    
    # Verificar processos PM2
    check_pm2_status
    
    # Verificar portas
    check_ports
    
    # Verificar sessões
    check_sessions
    
    # Verificar painel
    check_panel
    
    # Verificar uso de memória
    check_memory
    
    # Verificar logs de erro
    check_error_logs
    
    # Verificar mensagens recentes
    check_recent_messages
    
    log_info "=== FIM DA VERIFICAÇÃO ==="
    echo ""
}

# Função para salvar relatório
save_report() {
    local timestamp=$(date '+%Y-%m-%d_%H-%M-%S')
    local report_file="monitoramento_$timestamp.log"
    
    {
        echo "=== RELATÓRIO DE MONITORAMENTO - $(date) ==="
        echo ""
        pm2 status
        echo ""
        echo "=== STATUS DAS SESSÕES ==="
        curl -s http://127.0.0.1:3000/status | jq .
        curl -s http://127.0.0.1:3001/status | jq .
        echo ""
        echo "=== LOGS RECENTES ==="
        pm2 logs --lines 20
    } > "$report_file"
    
    log_info "Relatório salvo em: $report_file"
}

# Configurações
INTERVAL=30  # Intervalo em segundos
MAX_ITERATIONS=0  # 0 = infinito

echo "⏰ Configurações:"
echo "   - Intervalo: ${INTERVAL}s"
echo "   - Iterações: $([ $MAX_ITERATIONS -eq 0 ] && echo "Infinito" || echo "$MAX_ITERATIONS")"
echo "   - Pressione Ctrl+C para parar"
echo ""

# Loop principal
iteration=1
while [ $MAX_ITERATIONS -eq 0 ] || [ $iteration -le $MAX_ITERATIONS ]; do
    log_info "Iteração $iteration"
    monitor_system
    
    # Salvar relatório a cada 10 iterações
    if [ $((iteration % 10)) -eq 0 ]; then
        save_report
    fi
    
    # Aguardar próximo ciclo
    if [ $MAX_ITERATIONS -eq 0 ] || [ $iteration -lt $MAX_ITERATIONS ]; then
        log_info "Aguardando ${INTERVAL}s para próxima verificação..."
        sleep $INTERVAL
    fi
    
    iteration=$((iteration + 1))
done

log_info "Monitoramento finalizado!"
save_report 