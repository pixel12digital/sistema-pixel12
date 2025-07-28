<?php
/**
 * VERIFICADOR DE BANCO DISPONÍVEL
 * 
 * Script para verificar quando o banco de dados estiver disponível novamente
 */

echo "🔍 VERIFICANDO DISPONIBILIDADE DO BANCO\n";
echo "======================================\n\n";

$max_tentativas = 10;
$intervalo = 30; // segundos

echo "⏰ Verificando a cada $intervalo segundos...\n";
echo "🔄 Máximo de tentativas: $max_tentativas\n\n";

for ($i = 1; $i <= $max_tentativas; $i++) {
    echo "Tentativa $i/$max_tentativas... ";
    
    try {
        require_once 'config.php';
        $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        
        if (!$mysqli->connect_errno) {
            $mysqli->set_charset('utf8mb4');
            
            // Testar uma consulta simples
            $result = $mysqli->query("SELECT 1");
            if ($result) {
                echo "✅ BANCO DISPONÍVEL!\n\n";
                
                echo "🎉 O banco de dados está funcionando novamente!\n";
                echo "📋 Próximos passos:\n";
                echo "   1. Acesse o chat normal: painel/chat.php\n";
                echo "   2. Teste o envio de mensagens\n";
                echo "   3. Verifique se as mensagens estão sendo salvas\n\n";
                
                // Verificar se há mensagens temporárias para migrar
                $mensagens_file = 'logs/mensagens_temporarias.json';
                if (file_exists($mensagens_file)) {
                    $mensagens_temp = json_decode(file_get_contents($mensagens_file), true);
                    if (!empty($mensagens_temp)) {
                        echo "📬 Encontradas " . count($mensagens_temp) . " mensagens temporárias\n";
                        echo "💾 Execute: php migrar_mensagens_temporarias.php\n";
                    }
                }
                
                $mysqli->close();
                exit(0);
            }
        }
        
        $mysqli->close();
        echo "❌ Ainda indisponível\n";
        
    } catch (Exception $e) {
        echo "❌ Erro: " . $e->getMessage() . "\n";
    }
    
    if ($i < $max_tentativas) {
        echo "⏳ Aguardando $intervalo segundos...\n";
        sleep($intervalo);
    }
}

echo "\n❌ Banco ainda indisponível após $max_tentativas tentativas\n";
echo "💡 Recomendações:\n";
echo "   - Aguarde mais 1 hora para resetar o limite de conexões\n";
echo "   - Use o chat temporário: painel/chat_temporario.php\n";
echo "   - Contate o provedor para aumentar o limite\n";
?> 