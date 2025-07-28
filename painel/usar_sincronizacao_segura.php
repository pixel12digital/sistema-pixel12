<?php
/**
 * Guia de Uso - Sincronização Segura
 * Demonstra como usar a sincronização que preserva dados editados
 */

require_once __DIR__ . '/../config.php';
require_once 'db.php';
require_once 'config_sincronizacao_segura.php';

// Função para mostrar diferenças entre sincronizações
function mostrarDiferencas() {
    echo "<h3>🔄 Diferenças entre Sincronizações</h3>";
    echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 20px 0;'>";
    echo "<tr style='background: #f3f4f6;'>";
    echo "<th style='padding: 10px;'>Característica</th>";
    echo "<th style='padding: 10px;'>Sincronização Tradicional</th>";
    echo "<th style='padding: 10px;'>Sincronização Segura</th>";
    echo "</tr>";
    
    $diferencas = [
        ['Comportamento', 'Sobrescreve TODOS os dados', 'Preserva dados editados'],
        ['Proteção Temporal', 'Nenhuma', '24 horas (configurável)'],
        ['Campos Críticos', 'Sobrescritos', 'Nunca sobrescritos'],
        ['Campos Vazios', 'Sobrescritos', 'Só preenchidos se vazios'],
        ['Log Detalhado', 'Básico', 'Completo com justificativas'],
        ['Edição Inline', 'Perde dados', 'Preserva dados'],
        ['Segurança', 'Baixa', 'Alta'],
        ['Flexibilidade', 'Nenhuma', 'Totalmente configurável']
    ];
    
    foreach ($diferencas as $diff) {
        echo "<tr>";
        echo "<td style='padding: 10px; font-weight: bold;'>{$diff[0]}</td>";
        echo "<td style='padding: 10px; color: #dc2626;'>{$diff[1]}</td>";
        echo "<td style='padding: 10px; color: #059669;'>{$diff[2]}</td>";
        echo "</tr>";
    }
    
    echo "</table>";
}

// Função para mostrar comandos de uso
function mostrarComandos() {
    echo "<h3>💻 Comandos de Uso</h3>";
    echo "<div style='background: #f8fafc; padding: 15px; border-radius: 6px; margin: 20px 0;'>";
    echo "<h4>🚫 Sincronização Tradicional (NÃO USAR para dados editados):</h4>";
    echo "<code style='background: #fee2e2; padding: 5px; border-radius: 3px;'>php sincroniza_asaas.php</code>";
    echo "<p style='color: #dc2626; font-size: 0.9em;'>⚠️ Sobrescreve TODOS os dados, incluindo edições manuais!</p>";
    
    echo "<h4>✅ Sincronização Segura (RECOMENDADA):</h4>";
    echo "<code style='background: #d1fae5; padding: 5px; border-radius: 3px;'>php sincroniza_asaas_seguro.php</code>";
    echo "<p style='color: #059669; font-size: 0.9em;'>✅ Preserva dados editados e respeita configurações de proteção.</p>";
    
    echo "<h4>🧪 Testar Proteção:</h4>";
    echo "<code style='background: #dbeafe; padding: 5px; border-radius: 3px;'>php teste_protecao_sincronizacao.php</code>";
    echo "<p style='color: #2563eb; font-size: 0.9em;'>🔍 Simula edições e verifica se a proteção está funcionando.</p>";
    echo "</div>";
}

// Função para mostrar cenários de uso
function mostrarCenarios() {
    echo "<h3>📋 Cenários de Uso</h3>";
    
    echo "<div style='margin: 20px 0;'>";
    echo "<h4>🎯 Cenário 1: Cliente Editado Recentemente</h4>";
    echo "<div style='background: #f0fdf4; padding: 15px; border-left: 4px solid #059669; border-radius: 4px;'>";
    echo "<p><strong>Situação:</strong> Cliente foi editado via edição inline há 2 horas</p>";
    echo "<p><strong>Resultado:</strong> Dados são PRESERVADOS, apenas campos vazios são preenchidos</p>";
    echo "<p><strong>Log:</strong> \"Cliente X foi editado recentemente, preservando dados locais\"</p>";
    echo "</div>";
    echo "</div>";
    
    echo "<div style='margin: 20px 0;'>";
    echo "<h4>🎯 Cenário 2: Cliente Não Editado</h4>";
    echo "<div style='background: #fef3c7; padding: 15px; border-left: 4px solid #d97706; border-radius: 4px;'>";
    echo "<p><strong>Situação:</strong> Cliente não foi editado há mais de 24 horas</p>";
    echo "<p><strong>Resultado:</strong> Dados são ATUALIZADOS do Asaas, respeitando campos críticos</p>";
    echo "<p><strong>Log:</strong> \"Cliente X atualizado com sucesso\"</p>";
    echo "</div>";
    echo "</div>";
    
    echo "<div style='margin: 20px 0;'>";
    echo "<h4>🎯 Cenário 3: Novo Cliente</h4>";
    echo "<div style='background: #dbeafe; padding: 15px; border-left: 4px solid #2563eb; border-radius: 4px;'>";
    echo "<p><strong>Situação:</strong> Cliente existe no Asaas mas não no banco local</p>";
    echo "<p><strong>Resultado:</strong> Cliente é CRIADO com todos os dados do Asaas</p>";
    echo "<p><strong>Log:</strong> \"Novo cliente X criado com sucesso\"</p>";
    echo "</div>";
    echo "</div>";
}

// Função para mostrar configurações atuais
function mostrarConfiguracoes() {
    echo "<h3>⚙️ Configurações Atuais</h3>";
    $config = getConfigSincronizacao();
    
    echo "<div style='background: #f8fafc; padding: 15px; border-radius: 6px; margin: 20px 0;'>";
    echo "<p><strong>🕒 Proteção Temporal:</strong> {$config['protecao_horas']} horas</p>";
    echo "<p><strong>🎯 Campos Críticos:</strong> " . implode(', ', $config['campos_criticos']) . "</p>";
    echo "<p><strong>📝 Campos Apenas Vazios:</strong> " . implode(', ', $config['campos_apenas_vazios']) . "</p>";
    echo "<p><strong>📊 Log Detalhado:</strong> " . ($config['log_detalhado'] ? 'Ativado' : 'Desativado') . "</p>";
    echo "</div>";
    
    echo "<p><a href='config_sincronizacao_segura.php?ajustar_config' style='background: #7c2ae8; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px;'>🔧 Ajustar Configurações</a></p>";
}

// Função para mostrar próximos passos
function mostrarProximosPassos() {
    echo "<h3>🚀 Próximos Passos Recomendados</h3>";
    echo "<ol style='margin: 20px 0;'>";
    echo "<li><strong>Testar Proteção:</strong> Execute <code>php teste_protecao_sincronizacao.php</code> para verificar se está funcionando</li>";
    echo "<li><strong>Ajustar Configurações:</strong> Configure os parâmetros de proteção conforme sua necessidade</li>";
    echo "<li><strong>Executar Sincronização Segura:</strong> Use <code>php sincroniza_asaas_seguro.php</code> em vez da tradicional</li>";
    echo "<li><strong>Monitorar Logs:</strong> Acompanhe o arquivo <code>logs/sincronizacao_segura.log</code></li>";
    echo "<li><strong>Substituir Sincronização:</strong> Renomeie o script antigo e use o seguro como padrão</li>";
    echo "</ol>";
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Guia de Uso - Sincronização Segura</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); max-width: 1200px; margin: 0 auto; }
        .header { background: linear-gradient(135deg, #7c2ae8, #6d28d9); color: white; padding: 20px; border-radius: 8px; margin-bottom: 20px; }
        .section { margin: 20px 0; padding: 15px; border: 1px solid #e5e7eb; border-radius: 6px; }
        .warning { background: #fef3c7; border-left: 4px solid #d97706; padding: 15px; margin: 15px 0; }
        .success { background: #f0fdf4; border-left: 4px solid #059669; padding: 15px; margin: 15px 0; }
        .info { background: #dbeafe; border-left: 4px solid #2563eb; padding: 15px; margin: 15px 0; }
        code { background: #f3f4f6; padding: 2px 6px; border-radius: 3px; font-family: monospace; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🛡️ Guia de Uso - Sincronização Segura</h1>
            <p>Como usar a sincronização que preserva dados editados manualmente</p>
        </div>
        
        <div class="warning">
            <h3>⚠️ Importante</h3>
            <p>A sincronização tradicional (<code>sincroniza_asaas.php</code>) <strong>SOBRESCREVE TODOS OS DADOS</strong>, incluindo edições feitas via edição inline. Use a <strong>sincronização segura</strong> para preservar seus dados!</p>
        </div>
        
        <?php
        mostrarDiferencas();
        mostrarComandos();
        mostrarCenarios();
        mostrarConfiguracoes();
        mostrarProximosPassos();
        ?>
        
        <div class="info">
            <h3>📞 Suporte</h3>
            <p>Para dúvidas ou problemas:</p>
            <ul>
                <li>Execute o teste de proteção para verificar se está funcionando</li>
                <li>Verifique os logs de sincronização</li>
                <li>Ajuste as configurações conforme necessário</li>
                <li>Consulte a documentação completa em <code>README_PROTECAO_SINCRONIZACAO.md</code></li>
            </ul>
        </div>
        
        <div class="success">
            <h3>✅ Benefícios da Sincronização Segura</h3>
            <ul>
                <li><strong>Preserva Edições:</strong> Dados editados via edição inline não são perdidos</li>
                <li><strong>Proteção Inteligente:</strong> Campos críticos nunca são sobrescritos</li>
                <li><strong>Configurável:</strong> Ajuste parâmetros conforme sua necessidade</li>
                <li><strong>Log Detalhado:</strong> Acompanhe todas as operações</li>
                <li><strong>Compatível:</strong> Funciona com o sistema existente</li>
            </ul>
        </div>
    </div>
</body>
</html> 