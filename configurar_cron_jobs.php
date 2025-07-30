<?php
/**
 * Configurar Cron Jobs do Sistema de Monitoramento
 * Este script gera os comandos necessários para configurar os cron jobs
 */

echo "<h1>⚙️ Configuração de Cron Jobs - Sistema de Monitoramento</h1>";
echo "<p><strong>Data/Hora:</strong> " . date('d/m/Y H:i:s') . "</p>";

// Obter o caminho absoluto do projeto
$caminho_projeto = realpath(__DIR__);
echo "<p><strong>Caminho do projeto:</strong> $caminho_projeto</p>";

echo "<h2>📋 Cron Jobs Necessários</h2>";

echo "<div style='background: #f8f9fa; border: 1px solid #dee2e6; padding: 20px; border-radius: 5px; margin: 10px 0;'>";
echo "<h3>🔄 1. Processamento de Mensagens Agendadas</h3>";
echo "<p><strong>Frequência:</strong> A cada 5 minutos</p>";
echo "<p><strong>Função:</strong> Envia mensagens que estão agendadas para o horário atual</p>";
echo "<p><strong>Comando:</strong></p>";
echo "<code style='background: #e9ecef; padding: 10px; display: block; border-radius: 3px;'>";
echo "*/5 * * * * php $caminho_projeto/painel/cron/processar_mensagens_agendadas.php";
echo "</code>";
echo "</div>";

echo "<div style='background: #f8f9fa; border: 1px solid #dee2e6; padding: 20px; border-radius: 5px; margin: 10px 0;'>";
echo "<h3>🔍 2. Verificação Diária do Sistema</h3>";
echo "<p><strong>Frequência:</strong> Diariamente às 8h</p>";
echo "<p><strong>Função:</strong> Verifica se o sistema está funcionando corretamente</p>";
echo "<p><strong>Comando:</strong></p>";
echo "<code style='background: #e9ecef; padding: 10px; display: block; border-radius: 3px;'>";
echo "0 8 * * * php $caminho_projeto/painel/cron/verificacao_diaria_monitoramento.php";
echo "</code>";
echo "</div>";

echo "<div style='background: #f8f9fa; border: 1px solid #dee2e6; padding: 20px; border-radius: 5px; margin: 10px 0;'>";
echo "<h3>🔄 3. Monitoramento Automático (Opcional)</h3>";
echo "<p><strong>Frequência:</strong> A cada 6 horas</p>";
echo "<p><strong>Função:</strong> Adiciona automaticamente clientes com faturas ao monitoramento</p>";
echo "<p><strong>Comando:</strong></p>";
echo "<code style='background: #e9ecef; padding: 10px; display: block; border-radius: 3px;'>";
echo "0 */6 * * * php $caminho_projeto/painel/cron/monitoramento_automatico.php";
echo "</code>";
echo "<p><em>⚠️ Este cron job é opcional, pois você prefere adicionar clientes manualmente</em></p>";
echo "</div>";

echo "<div style='background: #f8f9fa; border: 1px solid #dee2e6; padding: 20px; border-radius: 5px; margin: 10px 0;'>";
echo "<h3>📊 4. Atualização de Faturas Vencidas</h3>";
echo "<p><strong>Frequência:</strong> A cada hora</p>";
echo "<p><strong>Função:</strong> Sincroniza status das faturas com o Asaas</p>";
echo "<p><strong>Comando:</strong></p>";
echo "<code style='background: #e9ecef; padding: 10px; display: block; border-radius: 3px;'>";
echo "0 * * * * php $caminho_projeto/painel/cron/atualizar_faturas_vencidas.php";
echo "</code>";
echo "</div>";

echo "<h2>🔧 Como Configurar</h2>";

echo "<div style='background: #d1ecf1; border: 1px solid #bee5eb; padding: 20px; border-radius: 5px; margin: 10px 0;'>";
echo "<h3>📝 Passo a Passo</h3>";
echo "<ol>";
echo "<li><strong>Acesse o cPanel</strong> do seu hosting</li>";
echo "<li>Vá em <strong>Cron Jobs</strong></li>";
echo "<li>Adicione cada comando acima</li>";
echo "<li>Configure a frequência conforme indicado</li>";
echo "<li>Salve as configurações</li>";
echo "</ol>";
echo "</div>";

echo "<h2>📁 Estrutura de Logs</h2>";

echo "<p>Os seguintes arquivos de log serão criados automaticamente:</p>";
echo "<ul>";
echo "<li><code>painel/logs/processamento_agendadas.log</code> - Log do processamento de mensagens</li>";
echo "<li><code>painel/logs/verificacao_diaria.log</code> - Log da verificação diária</li>";
echo "<li><code>painel/logs/monitoramento_automatico.log</code> - Log do monitoramento automático</li>";
echo "<li><code>painel/logs/atualizar_faturas_vencidas.log</code> - Log da atualização de faturas</li>";
echo "</ul>";

echo "<h2>✅ Verificação de Funcionamento</h2>";

echo "<p>Para verificar se os cron jobs estão funcionando:</p>";
echo "<ol>";
echo "<li>Execute manualmente: <code>php $caminho_projeto/painel/cron/verificacao_diaria_monitoramento.php</code></li>";
echo "<li>Verifique os logs em <code>painel/logs/</code></li>";
echo "<li>Monitore a tabela <code>relatorios_verificacao</code> no banco de dados</li>";
echo "</ol>";

echo "<h2>🚨 Alertas e Monitoramento</h2>";

echo "<div style='background: #fff3cd; border: 1px solid #ffeaa7; padding: 20px; border-radius: 5px; margin: 10px 0;'>";
echo "<h3>⚠️ Importante</h3>";
echo "<ul>";
echo "<li>Os cron jobs devem ser executados com permissões adequadas</li>";
echo "<li>Verifique se o PHP está no PATH do sistema</li>";
echo "<li>Monitore os logs regularmente</li>";
echo "<li>Configure alertas por email se necessário</li>";
echo "</ul>";
echo "</div>";

echo "<h2>📊 Resumo dos Cron Jobs</h2>";

echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 10px 0;'>";
echo "<tr style='background: #f8f9fa;'>";
echo "<th>Cron Job</th><th>Frequência</th><th>Função</th><th>Status</th>";
echo "</tr>";
echo "<tr>";
echo "<td>processar_mensagens_agendadas.php</td><td>*/5 * * * *</td><td>Enviar mensagens agendadas</td><td style='color: green;'>✅ Essencial</td>";
echo "</tr>";
echo "<tr>";
echo "<td>verificacao_diaria_monitoramento.php</td><td>0 8 * * *</td><td>Verificação diária do sistema</td><td style='color: green;'>✅ Essencial</td>";
echo "</tr>";
echo "<tr>";
echo "<td>monitoramento_automatico.php</td><td>0 */6 * * *</td><td>Adicionar clientes automaticamente</td><td style='color: orange;'>⚠️ Opcional</td>";
echo "</tr>";
echo "<tr>";
echo "<td>atualizar_faturas_vencidas.php</td><td>0 * * * *</td><td>Sincronizar com Asaas</td><td style='color: blue;'>💡 Recomendado</td>";
echo "</tr>";
echo "</table>";

echo "<hr>";
echo "<p><em>Configuração de cron jobs gerada em " . date('d/m/Y H:i:s') . "</em></p>";
?> 