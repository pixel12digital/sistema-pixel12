#!/bin/bash

echo "🚀 Implementando Endpoint /send na VPS WhatsApp API"
echo "=================================================="

# 1. Localizar o processo Node.js
echo "📋 1. Localizando processo Node.js..."
PROCESS=$(ps aux | grep node | grep -v grep | head -1)
if [ -z "$PROCESS" ]; then
    echo "❌ Processo Node.js não encontrado!"
    echo "Verifique se o servidor WhatsApp está rodando."
    exit 1
fi

echo "✅ Processo encontrado: $PROCESS"

# 2. Localizar o diretório do projeto
echo ""
echo "📁 2. Localizando diretório do projeto..."
PROJECT_DIR=$(ps aux | grep node | grep -v grep | awk '{print $NF}' | head -1 | xargs dirname 2>/dev/null)
if [ -z "$PROJECT_DIR" ] || [ ! -d "$PROJECT_DIR" ]; then
    echo "❌ Diretório do projeto não encontrado!"
    echo "Tentando localizar manualmente..."
    
    # Tentar diretórios comuns
    for dir in /root /home/* /opt /var/www; do
        if [ -d "$dir" ] && find "$dir" -name "*.js" -type f | grep -q "app\|server\|index"; then
            PROJECT_DIR="$dir"
            break
        fi
    done
fi

if [ -z "$PROJECT_DIR" ]; then
    echo "❌ Não foi possível localizar o diretório do projeto."
    echo "Por favor, execute manualmente:"
    echo "1. cd /caminho/para/seu/projeto"
    echo "2. nano app.js (ou server.js, index.js)"
    exit 1
fi

echo "✅ Diretório encontrado: $PROJECT_DIR"

# 3. Fazer backup do arquivo principal
echo ""
echo "💾 3. Fazendo backup do arquivo principal..."
cd "$PROJECT_DIR"

# Encontrar o arquivo principal
MAIN_FILE=""
for file in app.js server.js index.js main.js; do
    if [ -f "$file" ]; then
        MAIN_FILE="$file"
        break
    fi
done

if [ -z "$MAIN_FILE" ]; then
    echo "❌ Arquivo principal não encontrado!"
    echo "Arquivos encontrados:"
    ls -la *.js 2>/dev/null || echo "Nenhum arquivo .js encontrado"
    exit 1
fi

echo "✅ Arquivo principal: $MAIN_FILE"
cp "$MAIN_FILE" "${MAIN_FILE}.backup.$(date +%Y%m%d_%H%M%S)"

# 4. Verificar se o endpoint já existe
echo ""
echo "🔍 4. Verificando se o endpoint /send já existe..."
if grep -q "app.post.*'/send'" "$MAIN_FILE"; then
    echo "⚠️ Endpoint /send já existe no arquivo!"
    echo "Deseja sobrescrever? (s/n)"
    read -r response
    if [[ ! "$response" =~ ^[Ss]$ ]]; then
        echo "Operação cancelada."
        exit 0
    fi
fi

# 5. Implementar o endpoint /send
echo ""
echo "🔧 5. Implementando endpoint /send..."

# Criar arquivo temporário com o endpoint
cat > /tmp/send_endpoint.js << 'EOF'
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
        
        // Log para debug
        console.log(`[SEND] Tentando enviar mensagem para ${to}: ${message}`);
        
        // Verificar se o cliente está conectado
        if (!client || !client.isConnected) {
            return res.status(503).json({
                success: false,
                error: 'WhatsApp não está conectado'
            });
        }
        
        // Formatar número (adicionar @c.us se necessário)
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
            message: 'Mensagem enviada com sucesso',
            timestamp: new Date().toISOString()
        });
        
    } catch (error) {
        console.error('[SEND] Erro ao enviar mensagem:', error);
        
        // Tratar erros específicos
        let errorMessage = 'Erro interno do servidor';
        let statusCode = 500;
        
        if (error.message.includes('not-authorized')) {
            errorMessage = 'WhatsApp não está autenticado';
            statusCode = 401;
        } elif (error.message.includes('not-found')) {
            errorMessage = 'Número de telefone não encontrado';
            statusCode = 404;
        } elif (error.message.includes('blocked')) {
            errorMessage = 'Número bloqueado ou não autorizado';
            statusCode = 403;
        } else {
            errorMessage = error.message || 'Erro desconhecido';
        }
        
        res.status(statusCode).json({
            success: false,
            error: errorMessage,
            timestamp: new Date().toISOString()
        });
    }
});
EOF

# 6. Adicionar o endpoint ao arquivo principal
echo ""
echo "📝 6. Adicionando endpoint ao arquivo principal..."

# Encontrar onde adicionar (antes do app.listen ou no final)
if grep -q "app.listen" "$MAIN_FILE"; then
    # Adicionar antes do app.listen
    sed -i '/app\.listen/ i\
// Endpoint para envio de mensagens WhatsApp\
app.post('\''/send'\'', async (req, res) => {\
    try {\
        const { to, message } = req.body;\
        \
        // Validar parâmetros\
        if (!to || !message) {\
            return res.status(400).json({\
                success: false,\
                error: '\''Parâmetros obrigatórios: to, message'\''\
            });\
        }\
        \
        // Log para debug\
        console.log(`[SEND] Tentando enviar mensagem para ${to}: ${message}`);\
        \
        // Verificar se o cliente está conectado\
        if (!client || !client.isConnected) {\
            return res.status(503).json({\
                success: false,\
                error: '\''WhatsApp não está conectado'\''\
            });\
        }\
        \
        // Formatar número (adicionar @c.us se necessário)\
        let formattedNumber = to;\
        if (!formattedNumber.includes('\''@'\'')) {\
            formattedNumber = formattedNumber + '\''@c.us'\'';\
        }\
        \
        // Enviar mensagem\
        const result = await client.sendMessage(formattedNumber, message);\
        \
        console.log(`[SEND] Mensagem enviada com sucesso. ID: ${result.id._serialized}`);\
        \
        res.json({\
            success: true,\
            messageId: result.id._serialized,\
            message: '\''Mensagem enviada com sucesso'\'',\
            timestamp: new Date().toISOString()\
        });\
        \
    } catch (error) {\
        console.error('\''[SEND] Erro ao enviar mensagem:'\'', error);\
        \
        // Tratar erros específicos\
        let errorMessage = '\''Erro interno do servidor'\'';\
        let statusCode = 500;\
        \
        if (error.message.includes('\''not-authorized'\'')) {\
            errorMessage = '\''WhatsApp não está autenticado'\'';\
            statusCode = 401;\
        } elif (error.message.includes('\''not-found'\'')) {\
            errorMessage = '\''Número de telefone não encontrado'\'';\
            statusCode = 404;\
        } elif (error.message.includes('\''blocked'\'')) {\
            errorMessage = '\''Número bloqueado ou não autorizado'\'';\
            statusCode = 403;\
        } else {\
            errorMessage = error.message || '\''Erro desconhecido'\'';\
        }\
        \
        res.status(statusCode).json({\
            success: false,\
            error: errorMessage,\
            timestamp: new Date().toISOString()\
        });\
    }\
});' "$MAIN_FILE"
else
    # Adicionar no final do arquivo
    cat /tmp/send_endpoint.js >> "$MAIN_FILE"
fi

# 7. Reiniciar o servidor
echo ""
echo "🔄 7. Reiniciando o servidor..."

# Encontrar e matar o processo atual
PID=$(ps aux | grep node | grep -v grep | awk '{print $2}' | head -1)
if [ ! -z "$PID" ]; then
    echo "Parando processo atual (PID: $PID)..."
    kill "$PID"
    sleep 2
fi

# Iniciar o servidor novamente
echo "Iniciando servidor..."
nohup node "$MAIN_FILE" > /tmp/whatsapp_api.log 2>&1 &
NEW_PID=$!

echo "✅ Servidor reiniciado (PID: $NEW_PID)"

# 8. Testar o endpoint
echo ""
echo "🧪 8. Testando o endpoint /send..."
sleep 3

# Teste simples
TEST_RESPONSE=$(curl -s -X POST http://localhost:3000/send \
  -H "Content-Type: application/json" \
  -d '{"to":"5511999999999","message":"Teste de endpoint"}' 2>/dev/null)

if echo "$TEST_RESPONSE" | grep -q "success"; then
    echo "✅ Endpoint /send funcionando!"
    echo "Resposta: $TEST_RESPONSE"
else
    echo "⚠️ Endpoint pode ter problemas. Verifique os logs:"
    echo "tail -f /tmp/whatsapp_api.log"
fi

# 9. Limpeza
rm -f /tmp/send_endpoint.js

echo ""
echo "🎉 Implementação concluída!"
echo "=========================="
echo "✅ Endpoint /send adicionado ao arquivo: $MAIN_FILE"
echo "✅ Backup criado: ${MAIN_FILE}.backup.*"
echo "✅ Servidor reiniciado (PID: $NEW_PID)"
echo ""
echo "📋 Para testar manualmente:"
echo "curl -X POST http://212.85.11.238:3000/send \\"
echo "  -H \"Content-Type: application/json\" \\"
echo "  -d '{\"to\":\"5511999999999\",\"message\":\"Teste\"}'"
echo ""
echo "📋 Para ver logs:"
echo "tail -f /tmp/whatsapp_api.log"
echo ""
echo "🔧 Agora você pode testar o envio de mensagens no chat!" 