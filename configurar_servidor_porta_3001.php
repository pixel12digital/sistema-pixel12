<?php
/**
 * SCRIPT PARA CONFIGURAR SERVIDOR WHATSAPP NA PORTA 3001
 * Execute este script na VPS para configurar o servidor comercial
 */

echo "🔧 CONFIGURANDO SERVIDOR WHATSAPP NA PORTA 3001\n";
echo "===============================================\n\n";

// 1. Verificar se estamos na VPS
$hostname = gethostname();
$is_vps = (strpos($hostname, 'srv') !== false || strpos($hostname, 'vps') !== false);

if (!$is_vps) {
    echo "⚠️ ATENÇÃO: Este script deve ser executado na VPS\n";
    echo "   Hostname atual: $hostname\n";
    echo "   Execute: ssh root@212.85.11.238\n";
    echo "   E depois execute este script\n\n";
}

// 2. Verificar portas em uso
echo "🔍 VERIFICANDO PORTAS EM USO:\n";
$portas_3000 = shell_exec("netstat -tulpn | grep :3000");
$portas_3001 = shell_exec("netstat -tulpn | grep :3001");

if ($portas_3000) {
    echo "✅ Porta 3000 está em uso (Financeiro):\n";
    echo "   $portas_3000\n";
} else {
    echo "❌ Porta 3000 não está em uso\n";
}

if ($portas_3001) {
    echo "⚠️ Porta 3001 já está em uso:\n";
    echo "   $portas_3001\n";
} else {
    echo "✅ Porta 3001 está livre\n";
}

// 3. Verificar processos Node.js
echo "\n🔍 VERIFICANDO PROCESSOS NODE.JS:\n";
$processos_node = shell_exec("ps aux | grep node | grep -v grep");
if ($processos_node) {
    echo "📱 Processos Node.js ativos:\n";
    echo "$processos_node\n";
} else {
    echo "ℹ️ Nenhum processo Node.js encontrado\n";
}

// 4. Verificar se há servidor WhatsApp configurado
echo "\n🔍 VERIFICANDO CONFIGURAÇÃO ATUAL:\n";
$config_files = [
    '/root/whatsapp-bot/package.json',
    '/home/whatsapp-bot/package.json',
    '/opt/whatsapp-bot/package.json'
];

$found_config = false;
foreach ($config_files as $config) {
    if (file_exists($config)) {
        echo "✅ Configuração encontrada: $config\n";
        $found_config = true;
        break;
    }
}

if (!$found_config) {
    echo "❌ Nenhuma configuração de servidor WhatsApp encontrada\n";
    echo "   Verifique se o servidor está instalado\n";
}

// 5. Instruções para configurar porta 3001
echo "\n🔧 INSTRUÇÕES PARA CONFIGURAR PORTA 3001:\n";
echo "   Se você já tem um servidor WhatsApp rodando na porta 3000:\n\n";
echo "   1. Copie a configuração atual:\n";
echo "      cp -r /caminho/do/servidor/atual /caminho/do/servidor/comercial\n\n";
echo "   2. Modifique a porta no arquivo de configuração:\n";
echo "      # Geralmente em package.json, .env ou config.js\n";
echo "      # Mude a porta de 3000 para 3001\n\n";
echo "   3. Inicie o servidor na porta 3001:\n";
echo "      cd /caminho/do/servidor/comercial\n";
echo "      npm start\n\n";
echo "   4. Ou use PM2 para gerenciar:\n";
echo "      pm2 start app.js --name whatsapp-comercial -- --port 3001\n\n";

// 6. Verificar se PM2 está instalado
echo "\n🔍 VERIFICANDO PM2:\n";
$pm2_check = shell_exec("which pm2");
if ($pm2_check) {
    echo "✅ PM2 está instalado: $pm2_check\n";
    echo "   Use PM2 para gerenciar os servidores:\n";
    echo "   pm2 list\n";
    echo "   pm2 start app.js --name whatsapp-comercial -- --port 3001\n";
} else {
    echo "❌ PM2 não está instalado\n";
    echo "   Instale com: npm install -g pm2\n";
}

// 7. Comandos sugeridos
echo "\n📋 COMANDOS SUGERIDOS:\n";
echo "   # Verificar se porta 3001 está livre\n";
echo "   netstat -tulpn | grep :3001\n\n";
echo "   # Se estiver livre, configurar servidor\n";
echo "   # (depende da sua configuração atual)\n\n";
echo "   # Testar se está funcionando\n";
echo "   curl http://localhost:3001/status\n\n";

echo "✅ VERIFICAÇÃO CONCLUÍDA!\n";
echo "Configure o servidor na porta 3001 e depois execute o monitor local.\n";
?> 