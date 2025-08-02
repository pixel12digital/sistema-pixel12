<?php
/**
 * Gerador de Documentação PDF - Sistema Loja Virtual
 * Converte o README.md e outras documentações em PDF
 */

require_once('config.php');

// Incluir TCPDF se disponível, senão usar HTML simples
$use_tcpdf = false;
if (class_exists('TCPDF')) {
    $use_tcpdf = true;
} else {
    // Tentar incluir TCPDF
    $tcpdf_paths = [
        'tcpdf/tcpdf.php',
        'vendor/tecnickcom/tcpdf/tcpdf.php',
        '../vendor/tecnickcom/tcpdf/tcpdf.php'
    ];
    
    foreach ($tcpdf_paths as $path) {
        if (file_exists($path)) {
            require_once($path);
            $use_tcpdf = true;
            break;
        }
    }
}

// Função para limpar HTML
function cleanHtml($html) {
    $html = str_replace(['<', '>'], ['&lt;', '&gt;'], $html);
    return $html;
}

// Função para formatar código
function formatCode($code, $language = 'php') {
    return '<pre style="background: #f4f4f4; padding: 10px; border-left: 4px solid #007cba; font-family: monospace; font-size: 12px; overflow-x: auto;">' . 
           htmlspecialchars($code) . 
           '</pre>';
}

// Função para criar tabela HTML
function createTable($headers, $rows) {
    $html = '<table style="width: 100%; border-collapse: collapse; margin: 10px 0; font-size: 12px;">';
    
    // Headers
    $html .= '<tr style="background: #007cba; color: white;">';
    foreach ($headers as $header) {
        $html .= '<th style="padding: 8px; border: 1px solid #ddd; text-align: left;">' . $header . '</th>';
    }
    $html .= '</tr>';
    
    // Rows
    foreach ($rows as $row) {
        $html .= '<tr>';
        foreach ($row as $cell) {
            $html .= '<td style="padding: 8px; border: 1px solid #ddd;">' . $cell . '</td>';
        }
        $html .= '</tr>';
    }
    
    $html .= '</table>';
    return $html;
}

// Conteúdo do PDF
$content = '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Documentação - Sistema Loja Virtual</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; line-height: 1.4; color: #333; }
        h1 { color: #007cba; font-size: 24px; border-bottom: 2px solid #007cba; padding-bottom: 10px; }
        h2 { color: #007cba; font-size: 20px; margin-top: 25px; }
        h3 { color: #007cba; font-size: 16px; margin-top: 20px; }
        h4 { color: #007cba; font-size: 14px; margin-top: 15px; }
        .highlight { background: #fff3cd; padding: 10px; border-left: 4px solid #ffc107; margin: 10px 0; }
        .warning { background: #f8d7da; padding: 10px; border-left: 4px solid #dc3545; margin: 10px 0; }
        .success { background: #d4edda; padding: 10px; border-left: 4px solid #28a745; margin: 10px 0; }
        .info { background: #d1ecf1; padding: 10px; border-left: 4px solid #17a2b8; margin: 10px 0; }
        pre { background: #f8f9fa; padding: 10px; border: 1px solid #e9ecef; border-radius: 4px; font-family: monospace; font-size: 11px; overflow-x: auto; }
        code { background: #f8f9fa; padding: 2px 4px; border-radius: 3px; font-family: monospace; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        th, td { padding: 8px; border: 1px solid #ddd; text-align: left; }
        th { background: #007cba; color: white; }
        .toc { background: #f8f9fa; padding: 15px; border: 1px solid #e9ecef; margin: 20px 0; }
        .toc h3 { margin-top: 0; }
        .toc ul { margin: 10px 0; }
        .toc li { margin: 5px 0; }
        .page-break { page-break-before: always; }
    </style>
</head>
<body>

<h1>🏪 Sistema de Loja Virtual com WhatsApp e Gestão Financeira</h1>

<div class="info">
<strong>Versão:</strong> 2.0.0<br>
<strong>Data:</strong> ' . date('d/m/Y H:i:s') . '<br>
<strong>Ambiente:</strong> ' . ($is_local ? 'Desenvolvimento Local' : 'Produção') . '<br>
<strong>Banco:</strong> ' . DB_HOST . '/' . DB_NAME . '
</div>

<div class="toc">
<h3>📋 Índice</h3>
<ul>
<li><a href="#funcionalidades">Principais Funcionalidades</a></li>
<li><a href="#arquitetura">Arquitetura do Sistema</a></li>
<li><a href="#instalacao">Instalação e Configuração</a></li>
<li><a href="#uso">Como Usar o Sistema</a></li>
<li><a href="#cache">Sistema de Cache</a></li>
<li><a href="#manutencao">Manutenção e Monitoramento</a></li>
<li><a href="#deploy">Ambientes de Deploy</a></li>
<li><a href="#api">API Reference</a></li>
<li><a href="#metricas">Estatísticas e Métricas</a></li>
<li><a href="#seguranca">Segurança</a></li>
<li><a href="#suporte">Suporte e Troubleshooting</a></li>
<li><a href="#changelog">Changelog</a></li>
<li><a href="#estrutura">Estrutura do Projeto</a></li>
</ul>
</div>

<div class="page-break"></div>

<h2 id="funcionalidades">🎯 Principais Funcionalidades</h2>

<h3>📱 Sistema de Chat WhatsApp</h3>
<ul>
<li><strong>Chat centralizado</strong> similar ao WhatsApp Web</li>
<li><strong>Aprovação manual de clientes</strong> (similar ao Kommo CRM)</li>
<li><strong>Interface responsiva</strong> com três colunas: Conversas | Detalhes | Chat</li>
<li><strong>Atualização em tempo real</strong> com polling adaptativo (2-30s)</li>
<li><strong>Sistema de cache inteligente</strong> para performance otimizada</li>
<li><strong>Webhook para recebimento automático</strong> de mensagens</li>
<li><strong>QR Code para conexão</strong> direta com WhatsApp Web</li>
</ul>

<h3>💳 Gestão Financeira Completa</h3>
<ul>
<li><strong>Integração com Asaas</strong> para cobranças e assinaturas</li>
<li><strong>Webhook automático</strong> para atualização de status de pagamentos</li>
<li><strong>Sistema de faturas</strong> com geração automática</li>
<li><strong>Gestão de assinaturas</strong> recorrentes</li>
<li><strong>Relatórios financeiros</strong> detalhados</li>
<li><strong>Sincronização bidirecional</strong> com Asaas</li>
</ul>

<h3>👥 Gestão de Clientes</h3>
<ul>
<li><strong>Cadastro completo</strong> com dados pessoais e endereço</li>
<li><strong>Sistema de aprovação</strong> para novos contatos WhatsApp</li>
<li><strong>Histórico de conversas</strong> e interações</li>
<li><strong>Gestão de status</strong> de clientes</li>
<li><strong>Busca avançada</strong> e filtros</li>
</ul>

<h3>🛠️ Painel Administrativo</h3>
<ul>
<li><strong>Dashboard</strong> com métricas em tempo real</li>
<li><strong>Monitoramento</strong> de status WhatsApp</li>
<li><strong>Configurações</strong> avançadas do sistema</li>
<li><strong>Logs detalhados</strong> de todas as operações</li>
<li><strong>Backup automático</strong> de dados importantes</li>
</ul>

<div class="page-break"></div>

<h2 id="arquitetura">🏗️ Arquitetura do Sistema</h2>

<h3>📊 Estrutura de Banco de Dados</h3>

<h4>Tabelas Principais:</h4>
<ul>
<li><code>clientes</code> - Clientes cadastrados e aprovados</li>
<li><code>clientes_pendentes</code> - Números aguardando aprovação</li>
<li><code>mensagens_comunicacao</code> - Mensagens dos clientes ativos</li>
<li><code>mensagens_pendentes</code> - Mensagens de clientes pendentes</li>
<li><code>cobrancas</code> - Cobranças e faturas</li>
<li><code>assinaturas</code> - Assinaturas recorrentes</li>
<li><code>canais_comunicacao</code> - Configurações dos canais</li>
</ul>

<h4>Sistema de Aprovação:</h4>
<pre>
Mensagem WhatsApp → Webhook → Verificação Cliente
                                     ↓
              Cliente Existente? ─── Sim ──→ Chat Normal
                     ↓
                    Não
                     ↓
              Tabela Pendentes ──→ Aguarda Aprovação
                     ↓                      ↓
               [Aprovado] ─────────→ Chat Normal
                     ↓
               [Rejeitado] ────────→ Mensagem Ignorada
</pre>

<h4>Fluxo de Integração Asaas:</h4>
<pre>
Cliente Criado → Sincronização Asaas → Webhook Notificações
     ↓                    ↓                    ↓
Sistema Local ←── Dados Atualizados ←── Status Pagamento
</pre>

<h3>🔄 Arquitetura de Conexão WhatsApp</h3>

<div class="info">
<strong>VPS WhatsApp:</strong> 212.85.11.238:3000<br>
<strong>Servidor Node.js:</strong> whatsapp-web.js<br>
<strong>Persistência:</strong> Sessões locais na VPS<br>
<strong>Multi-sessão:</strong> Suporte a múltiplos canais
</div>

<pre>
┌─────────────────┐    ┌──────────────────┐    ┌─────────────────┐
│   Local (XAMPP) │    │   VPS WhatsApp   │    │  Banco Remoto   │
│                 │    │                  │    │                 │
│ • Frontend      │◄──►│ • Node.js Server │    │ • MySQL         │
│ • PHP Backend   │    │ • WhatsApp Web   │    │ • Hostinger     │
│ • Webhook       │    │ • Porta 3000     │    │ • srv1607.hstgr │
└─────────────────┘    └──────────────────┘    └─────────────────┘
         │                       │                       │
         ▼                       ▼                       ▼
┌─────────────────┐    ┌──────────────────┐    ┌─────────────────┐
│  Produção       │    │  WhatsApp API    │    │  Asaas API      │
│  (Hostinger)    │    │  (Multi-sessão)  │    │  (Cobranças)    │
└─────────────────┘    └──────────────────┘    └─────────────────┘
</pre>

<div class="page-break"></div>

<h2 id="instalacao">🚀 Instalação e Configuração</h2>

<h3>1. Requisitos do Sistema</h3>
<ul>
<li><strong>PHP:</strong> 7.4 ou superior</li>
<li><strong>MySQL:</strong> 5.7 ou superior</li>
<li><strong>Servidor Web:</strong> Apache/Nginx</li>
<li><strong>Node.js:</strong> 14+ (para robô WhatsApp)</li>
<li><strong>Extensões PHP:</strong> mysqli, json, curl, mbstring</li>
</ul>

<h3>2. Configuração Inicial</h3>

<h4>a) Clone o Repositório:</h4>
' . formatCode('git clone https://github.com/pixel12digital/revenda-sites.git
cd revenda-sites', 'bash') . '

<h4>b) Instale as Dependências Node.js:</h4>
' . formatCode('npm install', 'bash') . '

<h4>c) Configure o Banco de Dados:</h4>
' . formatCode('// config.php (configuração automática por ambiente)
// Local (XAMPP): localhost/loja_virtual
// Produção: srv1607.hstgr.io/u342734079_revendaweb', 'php') . '

<h4>d) Execute a Verificação do Banco:</h4>
' . formatCode('php fix_database_structure.php', 'bash') . '

<h3>3. Configuração WhatsApp</h3>

<h4>a) Configure o Robô WhatsApp:</h4>
' . formatCode('# Inicie o servidor WhatsApp
node index.js

# Ou use PM2 para produção
pm2 start ecosystem.config.js', 'bash') . '

<h4>b) Configure o Webhook:</h4>
' . formatCode('# Local (XAMPP):
php painel/configurar_webhook_ambiente.php

# Produção (Hostinger):
php painel/diagnosticar_producao.php', 'bash') . '

<h3>4. Configuração Asaas</h3>

<h4>a) Configure as Chaves API:</h4>
' . formatCode('// config.php - Configuração automática
// Teste: $aact_test_CHAVE_DE_TESTE_AQUI
// Produção: $aact_prod_000MzkwODA2MWY2OGM3MWRlMDU2NWM3MzJlNzZmNGZhZGY6OjFkZGExMjcyLWMzN2MtNGM3MS1iMTBmLTY4YWU4MjM4ZmE1Nzo6JGFhY2hfM2EzNTI4OTUtOGFjNC00MmFlLTliZTItNjRkZDg2YTAzOWRj', 'php') . '

<h4>b) Configure o Webhook Asaas:</h4>
<pre>
URL: https://seudominio.com/api/webhooks.php
Eventos: PAYMENT_RECEIVED, PAYMENT_CONFIRMED, SUBSCRIPTION_CREATED
</pre>

<div class="page-break"></div>

<h2 id="uso">📋 Como Usar o Sistema</h2>

<h3>🎛️ Painel de Controle</h3>

<h4>1. Dashboard Principal</h4>
<pre>Acesse: painel/dashboard.php</pre>
<ul>
<li><strong>Métricas em tempo real</strong> de clientes, cobranças e conversas</li>
<li><strong>Status do WhatsApp</strong> e conectividade</li>
<li><strong>Últimas atividades</strong> do sistema</li>
</ul>

<h4>2. Chat Centralizado</h4>
<pre>Acesse: painel/chat.php</pre>
<ul>
<li><strong>Coluna 1:</strong> Lista de conversas ativas</li>
<li><strong>Coluna 2:</strong> Detalhes do cliente selecionado</li>
<li><strong>Coluna 3:</strong> Chat com mensagens em tempo real</li>
</ul>

<h4>3. Gestão de Clientes</h4>
<pre>Acesse: painel/clientes.php</pre>
<ul>
<li><strong>Lista de clientes</strong> com busca e filtros</li>
<li><strong>Cadastro de novos clientes</strong></li>
<li><strong>Edição de dados</strong> e histórico</li>
</ul>

<h4>4. Gestão Financeira</h4>
<pre>Acesse: painel/faturas.php</pre>
<ul>
<li><strong>Lista de cobranças</strong> e status</li>
<li><strong>Geração de faturas</strong></li>
<li><strong>Relatórios financeiros</strong></li>
</ul>

<h4>5. Comunicação WhatsApp</h4>
<pre>Acesse: painel/comunicacao.php</pre>
<ul>
<li><strong>Conexão via QR Code</strong></li>
<li><strong>Monitoramento de status</strong></li>
<li><strong>Gestão de sessões</strong></li>
</ul>

<h3>🔐 Gerenciamento de Clientes Pendentes</h3>

<h4>1. Listar Pendentes:</h4>
' . formatCode('GET /painel/api/clientes_pendentes.php?action=list', 'bash') . '

<h4>2. Ver Mensagens de um Pendente:</h4>
' . formatCode('GET /painel/api/clientes_pendentes.php?action=messages&pendente_id=123', 'bash') . '

<h4>3. Aprovar Cliente:</h4>
' . formatCode('POST /painel/api/clientes_pendentes.php
{
    "action": "approve",
    "pendente_id": 123,
    "nome_cliente": "João Silva",
    "email_cliente": "joao@email.com"
}', 'json') . '

<h4>4. Rejeitar Cliente:</h4>
' . formatCode('POST /painel/api/clientes_pendentes.php
{
    "action": "reject", 
    "pendente_id": 123,
    "motivo": "Número suspeito"
}', 'json') . '

<div class="page-break"></div>

<h2 id="cache">⚡ Sistema de Cache Inteligente</h2>

<h3>🧠 Cache Adaptativo:</h3>

' . createTable(
    ['Situação', 'Cache', 'Polling', 'Performance'],
    [
        ['🟢 Usuário ativo', '5s', '2s', 'Máxima responsividade'],
        ['🟡 Moderadamente ativo', '15s', '5s', 'Balanceado'],
        ['🔴 Usuário inativo', '30s', '30s', '80% menos consultas DB']
    ]
) . '

<h3>🔄 Invalidação Automática:</h3>
<ul>
<li>Cache limpo quando mensagem chega</li>
<li>Detecção de atividade do usuário</li>
<li>Transição automática entre modos</li>
</ul>

<div class="page-break"></div>

<h2 id="manutencao">🛠️ Manutenção e Monitoramento</h2>

<h3>📊 Monitoramento</h3>

<h4>1. Status do Sistema:</h4>
' . formatCode('# Verificar WhatsApp
php painel/monitorar_mensagens.php

# Testar webhook
php painel/testar_webhook.php

# Diagnosticar produção  
php painel/diagnosticar_producao.php

# Monitoramento automático
php painel/monitor_whatsapp_automatico.php', 'bash') . '

<h4>2. Logs Importantes:</h4>
<ul>
<li><code>logs/webhook_whatsapp_*.log</code> - Mensagens recebidas</li>
<li><code>painel/debug_*.log</code> - Debug do sistema</li>
<li><code>api/debug_webhook.log</code> - Debug do webhook</li>
<li><code>painel/logs/</code> - Logs do painel administrativo</li>
</ul>

<h3>🔧 Correções Comuns</h3>

<h4>1. Mensagens não aparecem:</h4>
' . formatCode('# Verificar webhook
curl -X POST https://seu-dominio.com/api/webhook_whatsapp.php

# Testar database
php painel/verificar_tabela_clientes.php

# Limpar cache
rm -rf /tmp/loja_virtual_cache/*', 'bash') . '

<h4>2. WhatsApp desconectado:</h4>
' . formatCode('# Reconectar
php painel/corrigir_canal.php

# Reconfigurar webhook
php painel/configurar_webhook_ambiente.php

# Verificar QR Code
php painel/iniciar_sessao.php', 'bash') . '

<h4>3. Performance lenta:</h4>
' . formatCode('# Verificar cache
php painel/api/record_activity.php

# Otimizar banco
php painel/otimizar_conexoes_db.php

# Limpar conexões
php painel/limpar_conexoes.php', 'bash') . '

<div class="page-break"></div>

<h2 id="deploy">🌐 Ambientes de Deploy</h2>

<h3>🏠 Local (XAMPP)</h3>
' . formatCode('# URL: http://localhost/loja-virtual-revenda/
# Webhook: http://localhost:8080/loja-virtual-revenda/api/webhook_whatsapp.php
# Requer ngrok para receber mensagens externas
# Banco: localhost/loja_virtual', 'bash') . '

<h3>☁️ Produção (Hostinger)</h3>
' . formatCode('# URL: https://pixel12digital.com.br/app/
# Webhook: https://pixel12digital.com.br/app/api/webhook_whatsapp.php
# Banco: srv1607.hstgr.io/u342734079_revendaweb
# Deploy via git pull', 'bash') . '

<h3>🔄 Deploy Automático:</h3>
' . formatCode('# Local → Produção
git add .
git commit -m "Suas mudanças"
git push

# Na Hostinger:
cd app
git pull
php painel/diagnosticar_producao.php', 'bash') . '

<div class="page-break"></div>

<h2 id="api">🔧 API Reference</h2>

<h3>📱 Chat APIs</h3>

<h4>Conversas:</h4>
<ul>
<li><code>GET /painel/api/conversas_recentes.php</code> - Lista conversas</li>
<li><code>GET /painel/api/conversas_nao_lidas.php</code> - Conversas não lidas</li>
<li><code>GET /painel/api/mensagens_cliente.php?cliente_id=X</code> - Mensagens</li>
</ul>

<h4>Mensagens:</h4>
<ul>
<li><code>POST /chat_enviar.php</code> - Enviar mensagem</li>
<li><code>GET /painel/api/check_new_messages.php</code> - Verificar novas</li>
<li><code>POST /painel/api/record_activity.php</code> - Registrar atividade</li>
</ul>

<h3>🔐 Aprovação APIs</h3>

<h4>Clientes Pendentes:</h4>
<ul>
<li><code>GET /painel/api/clientes_pendentes.php?action=list</code></li>
<li><code>GET /painel/api/clientes_pendentes.php?action=messages&pendente_id=X</code></li>
<li><code>POST /painel/api/clientes_pendentes.php</code> (approve/reject)</li>
<li><code>GET /painel/api/clientes_pendentes.php?action=stats</code></li>
</ul>

<h3>🤖 WhatsApp APIs</h3>

<h4>Webhook:</h4>
<ul>
<li><code>POST /api/webhook_whatsapp.php</code> - Receber mensagens</li>
<li><code>POST /ajax_whatsapp.php</code> - Controlar robô</li>
<li><code>GET /painel/api/whatsapp_webhook.php</code> - Status</li>
</ul>

<h3>💳 Asaas APIs</h3>

<h4>Cobranças:</h4>
<ul>
<li><code>GET /api/cobrancas.php</code> - Listar cobranças</li>
<li><code>POST /api/cobrancas.php</code> - Criar cobrança</li>
<li><code>GET /api/invoices.php</code> - Faturas</li>
</ul>

<h4>Webhook:</h4>
<ul>
<li><code>POST /api/webhooks.php</code> - Receber notificações Asaas</li>
<li><code>POST /public/webhook_asaas.php</code> - Webhook público</li>
</ul>

<div class="page-break"></div>

<h2 id="metricas">📈 Estatísticas e Métricas</h2>

<h3>📊 Métricas Disponíveis:</h3>
<ul>
<li>Total de clientes ativos e pendentes</li>
<li>Conversas ativas e não lidas</li>
<li>Cobranças pendentes e pagas</li>
<li>Taxa de aprovação/rejeição de clientes</li>
<li>Performance do cache e sistema</li>
<li>Status da conexão WhatsApp</li>
<li>Sincronização com Asaas</li>
</ul>

<h3>🎯 KPIs Importantes:</h3>
<ul>
<li><strong>Tempo de resposta:</strong> &lt; 5 segundos</li>
<li><strong>Taxa de entrega WhatsApp:</strong> &gt; 95%</li>
<li><strong>Uptime WhatsApp:</strong> &gt; 99%</li>
<li><strong>Cache hit rate:</strong> &gt; 80%</li>
<li><strong>Sincronização Asaas:</strong> &lt; 1 minuto</li>
</ul>

<div class="page-break"></div>

<h2 id="seguranca">🛡️ Segurança</h2>

<h3>🔒 Medidas de Segurança:</h3>
<ul>
<li>Validação de entrada em todos os endpoints</li>
<li>Escape de SQL para prevenir injection</li>
<li>Rate limiting nos webhooks</li>
<li>Logs de auditoria completos</li>
<li>Sistema de aprovação manual para novos clientes</li>
<li>Configuração automática por ambiente</li>
<li>Proteção contra CSRF</li>
</ul>

<h3>🚨 Monitoramento:</h3>
<ul>
<li>Logs de acesso suspeito</li>
<li>Verificação de integridade do webhook</li>
<li>Backup automático de mensagens importantes</li>
<li>Alertas de falhas na conexão</li>
<li>Monitoramento de status Asaas</li>
</ul>

<div class="page-break"></div>

<h2 id="suporte">📞 Suporte e Troubleshooting</h2>

<h3>🆘 Problemas Comuns:</h3>

<h4>1. "Mensagens não chegam"</h4>
' . formatCode('# Verificar webhook
php painel/testar_webhook.php

# Verificar VPS
curl http://212.85.11.238:3000/status

# Reconfigurar
php painel/diagnosticar_producao.php', 'bash') . '

<h4>2. "Sistema lento"</h4>
' . formatCode('# Limpar cache
rm -rf /tmp/loja_virtual_cache/*

# Verificar atividade
php painel/api/record_activity.php?cliente_id=1

# Otimizar DB
php painel/otimizar_conexoes_db.php', 'bash') . '

<h4>3. "QR Code não aparece"</h4>
' . formatCode('# Verificar modal
php painel/iniciar_sessao.php

# Testar endpoints QR
php painel/descobrir_endpoints_qr.php

# Limpar cache navegador
Ctrl + Shift + R', 'bash') . '

<h4>4. "Cobranças não sincronizam"</h4>
' . formatCode('# Verificar webhook Asaas
php test_webhook.php

# Sincronizar manualmente
php painel/sincronizar_asaas_ajax.php

# Verificar estrutura DB
php fix_database_structure.php', 'bash') . '

<h3>📧 Contato:</h3>
<ul>
<li><strong>Email:</strong> suporte@pixel12digital.com.br</li>
<li><strong>GitHub:</strong> https://github.com/pixel12digital/revenda-sites</li>
<li><strong>Documentação:</strong> Este README.md</li>
</ul>

<div class="page-break"></div>

<h2 id="changelog">📝 Changelog</h2>

<h3>v2.0.0 - WhatsApp Web Integration (Janeiro 2025)</h3>
<ul>
<li>✅ <strong>WhatsApp Web direto:</strong> Envio via WhatsApp Web sem APIs de terceiros</li>
<li>✅ <strong>Monitoramento automático:</strong> Verificação de status a cada 5 minutos</li>
<li>✅ <strong>Retry automático:</strong> Reenvio de mensagens não entregues após 1 hora</li>
<li>✅ <strong>Sistema de logs:</strong> Registro detalhado de todas as operações</li>
<li>✅ <strong>Formatação inteligente:</strong> DDD 61 sempre com nono dígito</li>
<li>✅ <strong>Limpeza de código:</strong> Remoção de arquivos antigos e desnecessários</li>
</ul>

<h3>v1.5.0 - Sistema de Aprovação Manual</h3>
<ul>
<li>✅ Sistema de aprovação similar ao Kommo CRM</li>
<li>✅ Tabelas de clientes pendentes</li>
<li>✅ API completa para gerenciamento</li>
<li>✅ Migração automática de mensagens</li>
<li>✅ Cache inteligente adaptativo</li>
</ul>

<h3>v1.0.0 - Sistema Base</h3>
<ul>
<li>✅ Sistema de cobranças com Asaas</li>
<li>✅ Painel administrativo básico</li>
<li>✅ Integração WhatsApp via WPPConnect</li>
<li>✅ Gestão de clientes</li>
</ul>

<div class="page-break"></div>

<h2 id="estrutura">📁 Estrutura do Projeto</h2>

<pre>
loja-virtual-revenda/
├── 📁 painel/                 # Painel administrativo
│   ├── 📁 api/               # APIs do painel
│   ├── 📁 assets/            # Assets (CSS, JS, imagens)
│   ├── 📁 cache/             # Cache do sistema
│   ├── 📁 cron/              # Scripts cron
│   ├── 📁 logs/              # Logs do painel
│   └── 📁 sql/               # Scripts SQL
├── 📁 api/                   # APIs públicas
│   ├── 📁 cache/             # Cache das APIs
│   └── webhook_*.php         # Webhooks
├── 📁 src/                   # Código fonte principal
│   ├── 📁 Controllers/       # Controladores
│   ├── 📁 Models/            # Modelos
│   ├── 📁 Services/          # Serviços
│   └── 📁 Views/             # Views
├── 📁 public/                # Arquivos públicos
│   └── 📁 assets/            # Assets públicos
├── 📁 docs/                  # Documentação
├── 📁 logs/                  # Logs gerais
├── 📁 cache/                 # Cache geral
├── 📁 canais/                # Configurações de canais
├── 📁 admin/                 # Área administrativa
├── 📁 node_modules/          # Dependências Node.js
├── 📄 index.js               # Servidor WhatsApp
├── 📄 whatsapp-api-server.js # Servidor API WhatsApp
├── 📄 config.php             # Configurações principais
├── 📄 package.json           # Dependências Node.js
└── 📄 README.md              # Este arquivo
</pre>

<div class="page-break"></div>

<h2>🎯 Roadmap Futuro</h2>

<h3>v2.1.0 - Planejado</h3>
<ul>
<li>[ ] Interface web para aprovação de clientes</li>
<li>[ ] Notificações push para novos pendentes</li>
<li>[ ] Integração com outros CRMs</li>
<li>[ ] Relatórios avançados de conversas</li>
<li>[ ] Sistema de tags para clientes</li>
</ul>

<h3>v2.2.0 - Planejado</h3>
<ul>
<li>[ ] WebSockets para tempo real</li>
<li>[ ] Suporte a múltiplos agentes</li>
<li>[ ] Automações baseadas em palavras-chave</li>
<li>[ ] Integração com outros gateways de pagamento</li>
</ul>

<div class="success">
<h3>🎉 Sistema totalmente funcional e documentado! Pronto para produção.</h3>
<p>Para suporte, consulte este README ou entre em contato com a equipe de desenvolvimento.</p>
</div>

</body>
</html>';

// Gerar PDF
if ($use_tcpdf) {
    // Usar TCPDF
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    
    // Configurar informações do documento
    $pdf->SetCreator('Sistema Loja Virtual');
    $pdf->SetAuthor('Pixel12Digital');
    $pdf->SetTitle('Documentação - Sistema Loja Virtual');
    $pdf->SetSubject('Documentação completa do sistema');
    
    // Configurar margens
    $pdf->SetMargins(15, 15, 15);
    $pdf->SetHeaderMargin(5);
    $pdf->SetFooterMargin(10);
    
    // Configurar quebra de página automática
    $pdf->SetAutoPageBreak(TRUE, 25);
    
    // Configurar fonte
    $pdf->SetFont('helvetica', '', 10);
    
    // Adicionar página
    $pdf->AddPage();
    
    // Escrever HTML
    $pdf->writeHTML($content, true, false, true, false, '');
    
    // Gerar PDF
    $pdf->Output('documentacao_sistema_loja_virtual.pdf', 'D');
    
} else {
    // Fallback: Salvar HTML em arquivo
    $html_file = 'documentacao_sistema_loja_virtual.html';
    file_put_contents($html_file, $content);
    
    echo "✅ Arquivo HTML gerado com sucesso: $html_file\n";
    echo "📁 Tamanho: " . number_format(filesize($html_file) / 1024, 2) . " KB\n\n";
    
    echo "🔄 Para converter em PDF, execute:\n";
    echo "   php converter_html_para_pdf.php\n\n";
    
    echo "💡 Ou abra o arquivo no navegador e use Ctrl+P → Salvar como PDF\n";
    echo "   Arquivo: " . realpath($html_file) . "\n";
}
?> 