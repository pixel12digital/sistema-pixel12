<?php
/**
 * 🔧 CORREÇÃO: EXTRAIR QR DO STATUS
 * 
 * Como o endpoint /qr não funciona, vamos extrair o QR do /status
 */

echo "🔧 CORRIGINDO EXTRAÇÃO DE QR DO STATUS\n";
echo "====================================\n\n";

$arquivo_ajax = 'painel/ajax_whatsapp.php';
$backup = $arquivo_ajax . '.backup_qr_status.' . date('Ymd_His');

// Backup
copy($arquivo_ajax, $backup);
echo "✅ Backup criado: $backup\n";

// Ler arquivo
$conteudo = file_get_contents($arquivo_ajax);

// SUBSTITUIR TODO O CASE 'qr' por uma versão que extrai do /status
$novo_case_qr = "case 'qr':
            // CORREÇÃO: Extrair QR do endpoint /status em vez de /qr
            error_log(\"[WhatsApp QR Debug] Porta: \$porta, Session: \$sessionName, VPS URL: \$vps_url\");
            
            // Usar endpoint /status para obter QR
            \$status_endpoint = \"/status\";
            \$ch = curl_init();
            curl_setopt(\$ch, CURLOPT_URL, \$vps_url . \$status_endpoint);
            curl_setopt(\$ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt(\$ch, CURLOPT_TIMEOUT, 10);
            curl_setopt(\$ch, CURLOPT_CONNECTTIMEOUT, 5);
            \$response = curl_exec(\$ch);
            \$http_code = curl_getinfo(\$ch, CURLINFO_HTTP_CODE);
            \$curl_error = curl_error(\$ch);
            curl_close(\$ch);
            
            error_log(\"[WhatsApp QR Response] HTTP Code: \$http_code, Response: \$response\");
            
            if (\$http_code == 200) {
                \$data = json_decode(\$response, true);
                
                // Extrair QR do clients_status
                \$qr = null;
                \$qrValid = false;
                
                if (isset(\$data['clients_status'])) {
                    foreach (\$data['clients_status'] as \$sessao => \$status_sessao) {
                        // Verificar se a sessão tem QR
                        if (isset(\$status_sessao['hasQR']) && \$status_sessao['hasQR'] && isset(\$status_sessao['qr'])) {
                            \$qrData = \$status_sessao['qr'];
                            
                            // Validar QR
                            if (!empty(\$qrData) && 
                                !str_starts_with(\$qrData, 'undefined') && 
                                !str_starts_with(\$qrData, 'simulate') && 
                                strlen(\$qrData) > 20) {
                                
                                \$qr = \$qrData;
                                \$qrValid = true;
                                error_log(\"[WhatsApp QR Valid] QR extraído da sessão \$sessao: \" . substr(\$qr, 0, 20) . \"...\");
                                break;
                            }
                        }
                    }
                }
                
                if (\$qrValid && \$qr) {
                    echo json_encode([
                        'success' => true,
                        'qr' => \$qr,
                        'message' => 'QR Code obtido com sucesso',
                        'session' => \$sessionName,
                        'extracted_from' => 'status_endpoint',
                        'debug' => [
                            'endpoint_used' => \$status_endpoint,
                            'session_used' => \$sessionName,
                            'porta_used' => \$porta,
                            'clients_available' => array_keys(\$data['clients_status'] ?? [])
                        ]
                    ]);
                } else {
                    echo json_encode([
                        'success' => false,
                        'error' => 'QR Code não disponível ou inválido',
                        'message' => 'Nenhum QR Code válido encontrado nas sessões',
                        'available_sessions' => array_keys(\$data['clients_status'] ?? []),
                        'debug' => [
                            'endpoint_used' => \$status_endpoint,
                            'session_used' => \$sessionName,
                            'porta_used' => \$porta,
                            'raw_status' => \$data
                        ]
                    ]);
                }
            } else {
                echo json_encode([
                    'success' => false,
                    'error' => 'Erro ao consultar status do servidor',
                    'http_code' => \$http_code,
                    'curl_error' => \$curl_error,
                    'debug' => [
                        'endpoint' => \$status_endpoint,
                        'session_used' => \$sessionName,
                        'porta_used' => \$porta
                    ]
                ]);
            }
            break;";

// Encontrar e substituir o case 'qr' inteiro
$padrao = "/case 'qr':.*?break;/s";
$conteudo = preg_replace($padrao, $novo_case_qr, $conteudo);

// Salvar
file_put_contents($arquivo_ajax, $conteudo);

echo "✅ Correção aplicada com sucesso!\n\n";

echo "🎯 MUDANÇA REALIZADA:\n";
echo "1. ✅ Case 'qr' substituído para extrair QR do endpoint /status\n";
echo "2. ✅ Lógica de validação de QR mantida\n";
echo "3. ✅ Debug melhorado para identificar problemas\n\n";

echo "🔄 TESTE AGORA:\n";
echo "1. Vá ao painel de comunicação\n";
echo "2. Clique em 'Conectar' em qualquer canal\n";
echo "3. O QR Code deve aparecer finalmente!\n";
?> 