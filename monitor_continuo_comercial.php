<?php
// Monitor contínuo para canal comercial
require_once "config.php";
require_once "painel/db.php";

$vps_ip = "212.85.11.238";
$max_attempts = 60; // 5 minutos (5 segundos cada)
$attempt = 0;

echo "🔍 Monitorando porta 3001 para capturar número...\n";

while ($attempt < $max_attempts) {
    $attempt++;
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "http://{$vps_ip}:3001/status");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code === 200) {
        $data = json_decode($response, true);
        if ($data && $data["ready"] && isset($data["clients_status"]["default"]["number"])) {
            $numero = $data["clients_status"]["default"]["number"];
            
            // Verificar se já está configurado
            $canal = $mysqli->query("SELECT identificador FROM canais_comunicacao WHERE nome_exibicao LIKE \"%Comercial%\"")->fetch_assoc();
            
            if (!$canal["identificador"] || $canal["identificador"] !== $numero) {
                $update = $mysqli->query("UPDATE canais_comunicacao SET identificador = \"$numero\", status = \"conectado\", data_conexao = NOW() WHERE nome_exibicao LIKE \"%Comercial%\"");
                if ($update) {
                    echo "✅ Número capturado automaticamente: $numero\n";
                    echo "✅ Canal comercial conectado com sucesso!\n";
                    file_put_contents("logs/captura_numero.log", date("Y-m-d H:i:s") . " - Número capturado: $numero\n", FILE_APPEND);
                    exit(0);
                }
            } else {
                echo "✅ Número já configurado: $numero\n";
                exit(0);
            }
        } else {
            echo "⏳ Aguardando QR code ser lido... (tentativa $attempt/$max_attempts)\n";
        }
    } else {
        echo "❌ Servidor não responde... (tentativa $attempt/$max_attempts)\n";
    }
    
    sleep(5);
}

echo "❌ Timeout: QR code não foi lido em 5 minutos\n";
echo "   Verifique se o servidor está rodando na porta 3001\n";
exit(1);
?>