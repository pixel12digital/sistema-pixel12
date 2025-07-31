<?php
// Monitor automático para capturar número do canal comercial
require_once "config.php";
require_once "painel/db.php";

$vps_ip = "212.85.11.238";

// Verificar porta 3001
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
        
        // Verificar se o número já está configurado
        $canal = $mysqli->query("SELECT identificador FROM canais_comunicacao WHERE nome_exibicao LIKE \"%Comercial%\"")->fetch_assoc();
        
        if (!$canal["identificador"] || $canal["identificador"] !== $numero) {
            $update = $mysqli->query("UPDATE canais_comunicacao SET identificador = \"$numero\", status = \"conectado\", data_conexao = NOW() WHERE nome_exibicao LIKE \"%Comercial%\"");
            if ($update) {
                echo "✅ Número capturado automaticamente: $numero\n";
                file_put_contents("logs/captura_numero.log", date("Y-m-d H:i:s") . " - Número capturado: $numero\n", FILE_APPEND);
            }
        }
    }
}
?>