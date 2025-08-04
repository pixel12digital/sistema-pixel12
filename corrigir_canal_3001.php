<?php
/**
 * Corrigir identificador do canal 3001 para usar o número correto 554797309525
 */

require_once 'config.php';
require_once 'painel/db.php';

echo "=== CORREÇÃO DO CANAL 3001 ===\n\n";

try {
    // 1. Verificar configuração atual
    echo "1. CONFIGURAÇÃO ATUAL DO CANAL 3001:\n";
    $sql = "SELECT * FROM canais_comunicacao WHERE porta = 3001";
    $result = $mysqli->query($sql);
    
    if ($result && $result->num_rows > 0) {
        $canal = $result->fetch_assoc();
        echo "   ID: {$canal['id']}\n";
        echo "   Nome: {$canal['nome_exibicao']}\n";
        echo "   Identificador atual: {$canal['identificador']}\n";
        echo "   Status: {$canal['status']}\n";
        echo "   Porta: {$canal['porta']}\n";
        
        $identificador_atual = $canal['identificador'];
        $identificador_correto = '554797309525@c.us';
        
        if ($identificador_atual === $identificador_correto) {
            echo "   ✅ Canal 3001 já está configurado corretamente!\n";
            echo "   Não é necessário fazer alterações.\n";
            exit;
        } else {
            echo "   ❌ Identificador incorreto detectado!\n";
            echo "   Atual: $identificador_atual\n";
            echo "   Correto: $identificador_correto\n";
        }
    } else {
        echo "   ❌ Canal 3001 não encontrado no banco\n";
        exit;
    }
    echo "\n";
    
    // 2. Fazer backup da configuração atual
    echo "2. FAZENDO BACKUP DA CONFIGURAÇÃO ATUAL:\n";
    $backup_data = [
        'id' => $canal['id'],
        'nome_exibicao' => $canal['nome_exibicao'],
        'identificador' => $canal['identificador'],
        'status' => $canal['status'],
        'porta' => $canal['porta'],
        'data_backup' => date('Y-m-d H:i:s')
    ];
    
    $backup_file = 'backup_canal_3001_' . date('Y-m-d_H-i-s') . '.json';
    file_put_contents($backup_file, json_encode($backup_data, JSON_PRETTY_PRINT));
    echo "   ✅ Backup salvo em: $backup_file\n";
    echo "\n";
    
    // 3. Atualizar o identificador do canal
    echo "3. ATUALIZANDO IDENTIFICADOR DO CANAL:\n";
    
    $sql_update = "UPDATE canais_comunicacao 
                   SET identificador = '554797309525@c.us' 
                   WHERE porta = 3001";
    
    if ($mysqli->query($sql_update)) {
        echo "   ✅ Identificador atualizado com sucesso!\n";
        echo "   Linhas afetadas: " . $mysqli->affected_rows . "\n";
    } else {
        echo "   ❌ Erro ao atualizar: " . $mysqli->error . "\n";
        exit;
    }
    echo "\n";
    
    // 4. Verificar se a atualização foi bem-sucedida
    echo "4. VERIFICANDO ATUALIZAÇÃO:\n";
    $sql_verify = "SELECT * FROM canais_comunicacao WHERE porta = 3001";
    $result_verify = $mysqli->query($sql_verify);
    
    if ($result_verify && $result_verify->num_rows > 0) {
        $canal_atualizado = $result_verify->fetch_assoc();
        echo "   ID: {$canal_atualizado['id']}\n";
        echo "   Nome: {$canal_atualizado['nome_exibicao']}\n";
        echo "   Identificador: {$canal_atualizado['identificador']}\n";
        echo "   Status: {$canal_atualizado['status']}\n";
        echo "   Porta: {$canal_atualizado['porta']}\n";
        
        if ($canal_atualizado['identificador'] === '554797309525@c.us') {
            echo "   ✅ Identificador corrigido com sucesso!\n";
        } else {
            echo "   ❌ Identificador ainda incorreto!\n";
        }
    }
    echo "\n";
    
    // 5. Testar conectividade do canal
    echo "5. TESTANDO CONECTIVIDADE DO CANAL:\n";
    
    $api_url = "http://212.85.11.238:3001/status";
    $ch = curl_init($api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code === 200) {
        echo "   ✅ API do canal 3001 está respondendo\n";
        $status_data = json_decode($response, true);
        if ($status_data && isset($status_data['clients_status']['comercial']['status'])) {
            $status = $status_data['clients_status']['comercial']['status'];
            echo "   Status da sessão comercial: $status\n";
        }
    } else {
        echo "   ❌ API do canal 3001 não está respondendo (HTTP: $http_code)\n";
    }
    echo "\n";
    
    // 6. Limpar cache se necessário
    echo "6. LIMPANDO CACHE:\n";
    
    // Verificar se existe função de cache
    if (function_exists('cache_forget')) {
        // Limpar cache de canais
        cache_forget('status_canais');
        cache_forget('canais_conectados');
        echo "   ✅ Cache de canais limpo\n";
    } else {
        echo "   ⚠️ Função de cache não disponível\n";
    }
    echo "\n";
    
    // 7. Resumo da correção
    echo "7. RESUMO DA CORREÇÃO:\n";
    echo "   ✅ Backup da configuração anterior criado\n";
    echo "   ✅ Identificador do canal 3001 atualizado\n";
    echo "   ✅ De: $identificador_atual\n";
    echo "   ✅ Para: 554797309525@c.us\n";
    echo "   ✅ Canal 3001 agora está configurado corretamente\n";
    echo "\n";
    
    // 8. Instruções para teste
    echo "8. INSTRUÇÕES PARA TESTE:\n";
    echo "   - Acesse o painel de chat: https://app.pixel12digital.com.br/painel/chat.php\n";
    echo "   - Selecione o canal 'Comercial - Pixel' (3001)\n";
    echo "   - Envie uma mensagem de teste\n";
    echo "   - Verifique se a mensagem está sendo enviada do número 554797309525\n";
    echo "\n";
    
    echo "=== CORREÇÃO CONCLUÍDA ===\n";
    
} catch (Exception $e) {
    echo "❌ Erro durante a correção: " . $e->getMessage() . "\n";
}
?> 