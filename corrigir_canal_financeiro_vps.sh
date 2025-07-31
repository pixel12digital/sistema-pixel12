#!/bin/bash

echo "🔧 CORREÇÃO DO CANAL FINANCEIRO - VPS"
echo "====================================="
echo "Data/Hora: $(date)"
echo ""

# Verificar se estamos no diretório correto
if [ ! -f "whatsapp-api-server.js" ]; then
    echo "❌ Arquivo whatsapp-api-server.js não encontrado!"
    echo "📁 Diretório atual: $(pwd)"
    echo "🔍 Procurando arquivo..."
    
    # Procurar o arquivo
    find /var -name "whatsapp-api-server.js" 2>/dev/null | head -5
    
    echo ""
    echo "💡 Execute: cd /var/whatsapp-api (ou o diretório correto)"
    exit 1
fi

echo "✅ Arquivo whatsapp-api-server.js encontrado!"
echo "📁 Diretório: $(pwd)"
echo ""

# Fazer backup
echo "📋 Fazendo backup do arquivo atual..."
backup_file="whatsapp-api-server.js.backup.$(date +%Y%m%d_%H%M%S)"
cp whatsapp-api-server.js "$backup_file"
echo "✅ Backup criado: $backup_file"
echo ""

# Verificar se o endpoint /send já existe
if grep -q "app.post('/send'" whatsapp-api-server.js; then
    echo "⚠️ Endpoint /send já existe no arquivo!"
    echo "🔍 Verificando se está funcionando..."
    
    # Testar o endpoint
    response=$(curl -s -o /dev/null -w "%{http_code}" -X POST http://localhost:3000/send \
        -H 'Content-Type: application/json' \
        -d '{"to":"554797146908","message":"teste"}')
    
    if [ "$response" = "200" ]; then
        echo "✅ Endpoint /send já está funcionando!"
        echo "🎉 Nenhuma correção necessária."
        exit 0
    else
        echo "❌ Endpoint /send existe mas não está funcionando (HTTP $response)"
        echo "🔄 Vamos corrigir..."
    fi
else
    echo "📝 Adicionando endpoint /send..."
fi

echo ""

# Adicionar o endpoint /send
echo "🔧 Adicionando endpoint /send ao final do arquivo..."

cat >> whatsapp-api-server.js << 'EOF'

// Endpoint para envio de mensagens WhatsApp
app.post('/send', async (req, res) => {
    try {
        const { to, message } = req.body;
        
        // Validar parâmetros
        if (!to || !message) {
            return res.status(400).json({
                success: false,
                error: 'Parâmetros obrigatórios: to, message'
            });
        }
        
        console.log(`[SEND] Tentando enviar mensagem para ${to}: ${message}`);
        
        // Verificar se o cliente está conectado
        const client = whatsappClients['default'];
        if (!client || !clientStatus['default'] || clientStatus['default'].status !== 'connected') {
            return res.status(503).json({
                success: false,
                error: 'WhatsApp não está conectado'
            });
        }
        
        // Formatar número
        let formattedNumber = to;
        if (!formattedNumber.includes('@')) {
            formattedNumber = formattedNumber + '@c.us';
        }
        
        // Enviar mensagem
        const result = await client.sendMessage(formattedNumber, message);
        
        console.log(`[SEND] Mensagem enviada com sucesso. ID: ${result.id._serialized}`);
        
        res.json({
            success: true,
            messageId: result.id._serialized,
            message: 'Mensagem enviada com sucesso'
        });
        
    } catch (error) {
        console.error('[SEND] Erro ao enviar mensagem:', error);
        res.status(500).json({
            success: false,
            error: error.message || 'Erro interno do servidor'
        });
    }
});
EOF

echo "✅ Endpoint /send adicionado!"
echo ""

# Verificar se o PM2 está rodando
echo "🔍 Verificando status do PM2..."
if ! command -v pm2 &> /dev/null; then
    echo "❌ PM2 não está instalado!"
    echo "💡 Instale com: npm install -g pm2"
    exit 1
fi

pm2_status=$(pm2 status | grep whatsapp-bot || echo "not_found")
if [[ "$pm2_status" == "not_found" ]]; then
    echo "⚠️ Processo whatsapp-bot não encontrado no PM2"
    echo "🔍 Processos ativos:"
    pm2 list
    echo ""
    echo "💡 Se o processo tiver outro nome, ajuste o comando abaixo"
else
    echo "✅ Processo whatsapp-bot encontrado no PM2"
fi

echo ""

# Reiniciar o servidor
echo "🔄 Reiniciando servidor WhatsApp..."
pm2 restart whatsapp-bot

if [ $? -eq 0 ]; then
    echo "✅ Servidor reiniciado com sucesso!"
else
    echo "❌ Erro ao reiniciar servidor!"
    echo "🔍 Tentando parar e iniciar novamente..."
    pm2 stop whatsapp-bot
    sleep 2
    pm2 start whatsapp-bot
fi

echo ""

# Aguardar um pouco para o servidor inicializar
echo "⏳ Aguardando servidor inicializar..."
sleep 5

# Verificar se o servidor está funcionando
echo "🔍 Verificando status do servidor..."
status_response=$(curl -s http://localhost:3000/status)
if [ $? -eq 0 ]; then
    echo "✅ Servidor respondendo!"
    echo "📊 Status: $status_response"
else
    echo "❌ Servidor não está respondendo!"
    echo "🔍 Verificando logs..."
    pm2 logs whatsapp-bot --lines 10
    exit 1
fi

echo ""

# Testar o novo endpoint
echo "🧪 Testando endpoint /send..."
test_response=$(curl -s -w "%{http_code}" -X POST http://localhost:3000/send \
    -H 'Content-Type: application/json' \
    -d '{"to":"554797146908","message":"Teste de correção - '$(date +%H:%M:%S)'"}')

http_code="${test_response: -3}"
response_body="${test_response%???}"

echo "📊 Resposta do teste:"
echo "HTTP Code: $http_code"
echo "Body: $response_body"

if [ "$http_code" = "200" ]; then
    echo ""
    echo "🎉 SUCESSO! Endpoint /send funcionando!"
    echo "✅ Canal financeiro corrigido com sucesso!"
    echo ""
    echo "📋 Resumo:"
    echo "- Backup criado: $backup_file"
    echo "- Endpoint /send adicionado"
    echo "- Servidor reiniciado"
    echo "- Teste de envio: OK"
    echo ""
    echo "🚀 O canal financeiro está pronto para uso!"
else
    echo ""
    echo "❌ Problema no endpoint /send (HTTP $http_code)"
    echo "🔍 Verificando logs..."
    pm2 logs whatsapp-bot --lines 20
    echo ""
    echo "💡 Verifique os logs acima para identificar o problema"
fi

echo ""
echo "📅 Correção concluída em: $(date)" 