#!/bin/bash

# 🔧 CONFIGURADOR DE CRON JOB PARA MONITORAMENTO DE DUPLICATAS
# Este script configura o monitoramento automático diário

echo "🔧 CONFIGURADOR DE MONITORAMENTO AUTOMÁTICO"
echo "=========================================="

# Verificar se estamos no diretório correto
if [ ! -f "monitor_prevencao_duplicatas.php" ]; then
    echo "❌ ERRO: Execute este script no diretório raiz do projeto"
    exit 1
fi

# Obter caminho absoluto do projeto
PROJECT_PATH=$(pwd)
PHP_PATH=$(which php)

echo "📁 Diretório do projeto: $PROJECT_PATH"
echo "🐘 Caminho do PHP: $PHP_PATH"
echo ""

# Verificar se PHP está disponível
if [ -z "$PHP_PATH" ]; then
    echo "❌ ERRO: PHP não encontrado no sistema"
    echo "   Instale o PHP ou configure o PATH corretamente"
    exit 1
fi

# Testar se o script de monitoramento funciona
echo "🧪 Testando script de monitoramento..."
$PHP_PATH monitor_prevencao_duplicatas.php > /dev/null 2>&1
if [ $? -eq 0 ]; then
    echo "✅ Script de monitoramento funcionando corretamente"
else
    echo "❌ ERRO: Script de monitoramento falhou"
    echo "   Verifique as configurações do banco de dados"
    exit 1
fi

echo ""

# Criar arquivo de cron temporário
CRON_FILE="/tmp/cron_monitoramento_duplicatas"

echo "# Monitoramento automático de duplicatas - Loja Virtual Revenda" > $CRON_FILE
echo "# Executar diariamente às 02:00" >> $CRON_FILE
echo "0 2 * * * $PHP_PATH $PROJECT_PATH/monitor_prevencao_duplicatas.php >> $PROJECT_PATH/logs/cron_monitoramento.log 2>&1" >> $CRON_FILE
echo "" >> $CRON_FILE
echo "# Verificação semanal manual (domingo às 03:00)" >> $CRON_FILE
echo "0 3 * * 0 $PHP_PATH $PROJECT_PATH/verificar_clientes_duplicados.php >> $PROJECT_PATH/logs/verificacao_semanal.log 2>&1" >> $CRON_FILE

echo "📋 CRON JOB CONFIGURADO:"
echo "========================"
cat $CRON_FILE
echo ""

# Perguntar se deseja instalar o cron job
echo "🤔 Deseja instalar este cron job? (s/n): "
read -r response

if [[ "$response" =~ ^[Ss]$ ]]; then
    echo ""
    echo "🔧 Instalando cron job..."
    
    # Fazer backup do cron atual
    crontab -l > /tmp/cron_backup_$(date +%Y%m%d_%H%M%S) 2>/dev/null || true
    
    # Adicionar novo cron job
    (crontab -l 2>/dev/null; cat $CRON_FILE) | crontab -
    
    if [ $? -eq 0 ]; then
        echo "✅ Cron job instalado com sucesso!"
        echo ""
        echo "📊 CRON JOBS ATIVOS:"
        echo "==================="
        crontab -l | grep -E "(monitor_prevencao_duplicatas|verificar_clientes_duplicados)" || echo "Nenhum cron job encontrado"
    else
        echo "❌ ERRO: Falha ao instalar cron job"
        exit 1
    fi
else
    echo "ℹ️  Cron job não foi instalado"
    echo "   Para instalar manualmente, execute:"
    echo "   crontab -e"
    echo "   E adicione as linhas mostradas acima"
fi

echo ""
echo "📁 DIRETÓRIOS CRIADOS:"
echo "======================"
echo "logs/ - Logs de monitoramento"
echo "backups/ - Backups automáticos"

# Criar diretórios se não existirem
mkdir -p logs backups

echo ""
echo "📋 PRÓXIMOS PASSOS:"
echo "=================="
echo "1. ✅ Cron job configurado (se instalado)"
echo "2. 📊 Monitoramento ativo diariamente às 02:00"
echo "3. 🔍 Verificação semanal aos domingos às 03:00"
echo "4. 📝 Logs salvos em logs/cron_monitoramento.log"
echo "5. 💾 Backups automáticos em backups/"
echo ""
echo "🔍 PARA VERIFICAR MANUALMENTE:"
echo "=============================="
echo "php monitor_prevencao_duplicatas.php"
echo "php verificar_clientes_duplicados.php"
echo ""
echo "📊 PARA VER LOGS:"
echo "================"
echo "tail -f logs/cron_monitoramento.log"
echo "tail -f logs/verificacao_semanal.log"
echo ""
echo "✅ CONFIGURAÇÃO CONCLUÍDA!"
echo ""
echo "💡 DICA: Teste o monitoramento executando:"
echo "   php monitor_prevencao_duplicatas.php"

# Limpar arquivo temporário
rm -f $CRON_FILE 