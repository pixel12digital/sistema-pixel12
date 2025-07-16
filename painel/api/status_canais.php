<?php
require_once '../config.php';
require_once '../db.php';
require_once '../cache_manager.php';

header('Content-Type: application/json');
header('Cache-Control: private, max-age=30'); // Cache HTTP de 30 segundos

// Cache para status dos canais
$status = cache_remember('status_canais_completo', function() use ($mysqli) {
    // Buscar canais do banco usando cache
    $canais = cache_status_canais($mysqli);
    $status = [];
    
    foreach ($canais as $canal) {
        $porta = intval($canal['porta']);
        $canal_id = intval($canal['id']);
        
        // Cache individual para cada canal
        $status_canal = cache_remember("status_canal_{$canal_id}", function() use ($canal, $porta, $mysqli, $canal_id) {
            $conectado = false;
            $lastSession = null;
            
            // Fazer request HTTP apenas se não estiver em cache
            $ch = curl_init("http://127.0.0.1:$porta/status");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 2);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1);
            
            $result = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            
            if ($result === false) {
                error_log("Erro cURL canal {$canal_id}: " . curl_error($ch));
            } else if ($httpCode === 200) {
                $json = json_decode($result, true);
                if ($json && isset($json['ready']) && $json['ready']) {
                    $conectado = true;
                    $lastSession = $json['lastSession'] ?? null;
                }
            }
            curl_close($ch);
            
            // Atualizar status no banco apenas se mudou
            $novo_status = $conectado ? 'conectado' : 'pendente';
            $status_atual = $canal['status'] ?? 'pendente';
            
            if ($novo_status !== $status_atual) {
                $mysqli->query("UPDATE canais_comunicacao SET status = '" . $mysqli->real_escape_string($novo_status) . "' WHERE id = $canal_id");
                // Invalidar cache relacionado
                cache_forget("status_canais");
                cache_forget("cliente_");
            }
            
            // Ajuste de fuso horário para lastSession
            if ($lastSession) {
                try {
                    $dt = new DateTime($lastSession, new DateTimeZone('UTC'));
                    $dt->setTimezone(new DateTimeZone('America/Sao_Paulo'));
                    $lastSession = $dt->format('Y-m-d H:i:s');
                } catch (Exception $e) {
                    // Se der erro, mantém original
                }
            }
            
            return [
                'id' => $canal['id'],
                'nome' => $canal['nome_exibicao'],
                'porta' => $porta,
                'conectado' => $conectado,
                'lastSession' => $lastSession,
                'tipo' => $canal['tipo'] ?? null
            ];
        }, 45); // Cache de 45 segundos para cada canal
        
        $status[] = $status_canal;
    }
    
    return $status;
}, 30); // Cache geral de 30 segundos

header('X-Cache-Status: ' . (cache_get('status_canais_completo') ? 'HIT' : 'MISS'));
echo json_encode($status);
?> 