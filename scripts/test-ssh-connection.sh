#!/bin/bash

# 🧪 Script de Teste de Conexão SSH para Deploy
# Este script testa a conectividade SSH com a VPS

set -e

# Cores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

echo -e "${BLUE}🔍 Teste de Conexão SSH - Sistema Pixel12${NC}"
echo "=================================================="

# Verificar se as variáveis de ambiente estão definidas
check_env_vars() {
    echo -e "${YELLOW}📋 Verificando variáveis de ambiente...${NC}"
    
    local required_vars=(
        "VPS_HOST"
        "VPS_USER" 
        "VPS_PORT"
        "VPS_PROJECT_PATH"
    )
    
    local missing_vars=()
    
    for var in "${required_vars[@]}"; do
        if [[ -z "${!var}" ]]; then
            missing_vars+=("$var")
        fi
    done
    
    if [[ ${#missing_vars[@]} -gt 0 ]]; then
        echo -e "${RED}❌ Variáveis de ambiente faltando:${NC}"
        printf '%s\n' "${missing_vars[@]}"
        echo -e "${YELLOW}💡 Configure estas variáveis no arquivo .env${NC}"
        return 1
    fi
    
    echo -e "${GREEN}✅ Todas as variáveis de ambiente estão configuradas${NC}"
    return 0
}

# Testar conectividade básica
test_connectivity() {
    echo -e "${YELLOW}🌐 Testando conectividade com a VPS...${NC}"
    
    if ping -c 1 "$VPS_HOST" >/dev/null 2>&1; then
        echo -e "${GREEN}✅ VPS responde ao ping${NC}"
    else
        echo -e "${RED}❌ VPS não responde ao ping${NC}"
        echo -e "${YELLOW}💡 Verifique se o IP/domínio está correto${NC}"
        return 1
    fi
}

# Testar porta SSH
test_ssh_port() {
    echo -e "${YELLOW}🔌 Testando porta SSH...${NC}"
    
    if nc -z -w5 "$VPS_HOST" "$VPS_PORT" 2>/dev/null; then
        echo -e "${GREEN}✅ Porta $VPS_PORT está aberta${NC}"
    else
        echo -e "${RED}❌ Porta $VPS_PORT não está acessível${NC}"
        echo -e "${YELLOW}💡 Verifique se o SSH está rodando na VPS${NC}"
        return 1
    fi
}

# Testar autenticação SSH
test_ssh_auth() {
    echo -e "${YELLOW}🔐 Testando autenticação SSH...${NC}"
    
    # Verificar se há chaves SSH carregadas
    if ssh-add -l >/dev/null 2>&1; then
        echo -e "${GREEN}✅ Chaves SSH carregadas no agente${NC}"
        ssh-add -l
    else
        echo -e "${YELLOW}⚠️ Nenhuma chave SSH carregada${NC}"
        echo -e "${YELLOW}💡 Execute: ssh-add ~/.ssh/id_rsa${NC}"
    fi
    
    # Testar conexão SSH
    if ssh -o ConnectTimeout=10 -o BatchMode=yes "$VPS_USER@$VPS_HOST" "echo '✅ Conexão SSH funcionando!'" 2>/dev/null; then
        echo -e "${GREEN}✅ Autenticação SSH bem-sucedida${NC}"
    else
        echo -e "${RED}❌ Falha na autenticação SSH${NC}"
        echo -e "${YELLOW}💡 Verifique:${NC}"
        echo -e "${YELLOW}   - Chave pública está na VPS${NC}"
        echo -e "${YELLOW}   - Permissões corretas (~/.ssh: 700, authorized_keys: 600)${NC}"
        echo -e "${YELLOW}   - Usuário e host estão corretos${NC}"
        return 1
    fi
}

# Testar acesso ao diretório do projeto
test_project_access() {
    echo -e "${YELLOW}📁 Testando acesso ao diretório do projeto...${NC}"
    
    if ssh -o ConnectTimeout=10 "$VPS_USER@$VPS_HOST" "[ -d '$VPS_PROJECT_PATH' ]" 2>/dev/null; then
        echo -e "${GREEN}✅ Diretório do projeto existe${NC}"
        
        # Verificar permissões
        local permissions=$(ssh "$VPS_USER@$VPS_HOST" "ls -ld '$VPS_PROJECT_PATH'" 2>/dev/null)
        echo -e "${BLUE}📋 Permissões: $permissions${NC}"
        
        # Verificar se é um repositório git
        if ssh "$VPS_USER@$VPS_HOST" "[ -d '$VPS_PROJECT_PATH/.git' ]" 2>/dev/null; then
            echo -e "${GREEN}✅ É um repositório Git${NC}"
            
            # Verificar branch atual
            local current_branch=$(ssh "$VPS_USER@$VPS_HOST" "cd '$VPS_PROJECT_PATH' && git branch --show-current" 2>/dev/null)
            echo -e "${BLUE}🌿 Branch atual: $current_branch${NC}"
        else
            echo -e "${YELLOW}⚠️ Diretório não é um repositório Git${NC}"
        fi
    else
        echo -e "${RED}❌ Diretório do projeto não existe${NC}"
        echo -e "${YELLOW}💡 Crie o diretório: mkdir -p $VPS_PROJECT_PATH${NC}"
        return 1
    fi
}

# Testar Node.js na VPS
test_nodejs() {
    echo -e "${YELLOW}📦 Testando Node.js na VPS...${NC}"
    
    local node_version=$(ssh "$VPS_USER@$VPS_HOST" "node --version" 2>/dev/null || echo "não encontrado")
    local npm_version=$(ssh "$VPS_USER@$VPS_HOST" "npm --version" 2>/dev/null || echo "não encontrado")
    
    if [[ "$node_version" != "não encontrado" ]]; then
        echo -e "${GREEN}✅ Node.js: $node_version${NC}"
    else
        echo -e "${RED}❌ Node.js não encontrado${NC}"
        echo -e "${YELLOW}💡 Instale Node.js na VPS${NC}"
        return 1
    fi
    
    if [[ "$npm_version" != "não encontrado" ]]; then
        echo -e "${GREEN}✅ NPM: $npm_version${NC}"
    else
        echo -e "${RED}❌ NPM não encontrado${NC}"
        echo -e "${YELLOW}💡 Instale NPM na VPS${NC}"
        return 1
    fi
}

# Testar banco de dados
test_database() {
    echo -e "${YELLOW}🗄️ Testando conexão com banco de dados...${NC}"
    
    # Verificar se as variáveis do banco estão definidas
    local db_vars=("VPS_DB_HOST" "VPS_DB_PORT" "VPS_DB_USER" "VPS_DB_NAME")
    local missing_db_vars=()
    
    for var in "${db_vars[@]}"; do
        if [[ -z "${!var}" ]]; then
            missing_db_vars+=("$var")
        fi
    done
    
    if [[ ${#missing_db_vars[@]} -gt 0 ]]; then
        echo -e "${YELLOW}⚠️ Variáveis do banco não configuradas:${NC}"
        printf '%s\n' "${missing_db_vars[@]}"
        echo -e "${YELLOW}💡 Configure as variáveis do banco para testar${NC}"
        return 0
    fi
    
    # Testar conexão com MySQL/MariaDB
    if command -v mysql >/dev/null 2>&1; then
        if mysql -h "$VPS_DB_HOST" -P "$VPS_DB_PORT" -u "$VPS_DB_USER" -p"$VPS_DB_PASS" -e "SELECT 1;" >/dev/null 2>&1; then
            echo -e "${GREEN}✅ Conexão com banco de dados bem-sucedida${NC}"
        else
            echo -e "${RED}❌ Falha na conexão com banco de dados${NC}"
            echo -e "${YELLOW}💡 Verifique as credenciais e se o banco está rodando${NC}"
        fi
    else
        echo -e "${YELLOW}⚠️ Cliente MySQL não encontrado localmente${NC}"
        echo -e "${YELLOW}💡 Teste a conexão diretamente na VPS${NC}"
    fi
}

# Função principal
main() {
    echo -e "${BLUE}🚀 Iniciando testes de conectividade...${NC}"
    echo ""
    
    local tests_passed=0
    local total_tests=6
    
    # Executar testes
    check_env_vars && ((tests_passed++))
    echo ""
    
    test_connectivity && ((tests_passed++))
    echo ""
    
    test_ssh_port && ((tests_passed++))
    echo ""
    
    test_ssh_auth && ((tests_passed++))
    echo ""
    
    test_project_access && ((tests_passed++))
    echo ""
    
    test_nodejs && ((tests_passed++))
    echo ""
    
    test_database && ((tests_passed++))
    echo ""
    
    # Resultado final
    echo "=================================================="
    if [[ $tests_passed -eq $total_tests ]]; then
        echo -e "${GREEN}🎉 Todos os testes passaram! Deploy deve funcionar.${NC}"
        exit 0
    else
        echo -e "${RED}❌ $((total_tests - tests_passed)) teste(s) falharam${NC}"
        echo -e "${YELLOW}💡 Corrija os problemas antes de fazer deploy${NC}"
        exit 1
    fi
}

# Executar script
main "$@"
