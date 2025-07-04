<?php /* Menu lateral atualizado para painel Pixel 12 Digital */ ?>
<?php if (session_status() === PHP_SESSION_NONE) session_start(); ?>
<style>
body { background: #181c23; color: #f5f5f5; font-family: Arial, sans-serif; }
.sidebar { position: fixed; left: 0; top: 0; bottom: 0; width: 70px; background: #232836; display: flex; flex-direction: column; align-items: center; padding: 0.5rem 0; z-index: 10000; transition: width 0.25s cubic-bezier(.4,0,.2,1); overflow: hidden; min-height: 100vh; }
.sidebar.expanded { width: 280px; align-items: flex-start; }
.sidebar-logo { width: 38px; margin-bottom: 0.3rem; margin-left: 0.5rem; cursor: pointer; }
.sidebar-nav { display: flex; flex-direction: column; gap: 0.7rem; width: 100%; }
.sidebar-link { color: #fff; font-size: 1.1rem; text-decoration: none; display: flex; align-items: center; gap: 12px; transition: color 0.2s, background 0.2s, padding 0.2s; padding: 8px 12px; border-radius: 8px; width: 100%; white-space: nowrap; position: relative; }
.sidebar-link .sidebar-label { font-size: 1rem; display: none; transition: opacity 0.2s; }
.sidebar.expanded .sidebar-link .sidebar-label { display: inline; margin-left: 8px; }
.sidebar-link.active, .sidebar-link:hover { color: #fff; background: #23203a; border-left: 4px solid #a259e6; padding-left: 8px; }
.sidebar-group-title { color: #a259e6; font-size: 0.8rem; font-weight: bold; margin: 1.2rem 0 0.7rem 1.5rem; letter-spacing: 1px; display: none; }
.sidebar.expanded .sidebar-group-title { display: block; }
.sidebar-group { width: 100%; position: relative; }
.sidebar-link.has-sub:after { content: ''; display: inline-block; border: solid #fff; border-width: 0 2px 2px 0; padding: 3px; margin-left: auto; transform: rotate(45deg); transition: transform 0.2s; }
.sidebar-group.open > .sidebar-link.has-sub:after { transform: rotate(135deg); }
.sidebar-submenu { display: none; flex-direction: column; margin-left: 36px; margin-top: 0.2rem; }
.sidebar-group.open .sidebar-submenu { display: flex; }
.sidebar:not(.expanded) .sidebar-submenu { display: none !important; }
.sidebar.expanded .sidebar-group.open .sidebar-submenu { display: flex !important; }
.sidebar-sublink { color: #a259e6; font-size: 1rem; text-decoration: none; padding: 8px 18px; border-radius: 4px; transition: background 0.2s, color 0.2s; white-space: nowrap; }
.sidebar-sublink:hover, .sidebar-sublink.active { background: #2d2540; color: #fff; }
.sidebar-chat-widget { position: absolute; bottom: 24px; left: 0; width: 100%; display: flex; justify-content: center; z-index: 1003; }
.chat-widget-btn { background: #a259e6; color: #fff; border: none; border-radius: 50%; width: 44px; height: 44px; font-size: 1.5rem; cursor: pointer; box-shadow: 0 2px 8px #0002; transition: background 0.2s; }
.chat-widget-btn:hover { background: #7c2ae8; }
.main-content { margin-left: 90px; min-height: 100vh; transition: margin-left 0.25s cubic-bezier(.4,0,.2,1); }
.sidebar.expanded ~ .main-content { margin-left: 290px; }
@media (max-width: 600px) {
    .sidebar, .sidebar.expanded { width: 60px; }
    .main-content, .sidebar.expanded ~ .main-content { margin-left: 70px; }
    .sidebar-link .sidebar-label, .sidebar.expanded .sidebar-label { display: none; }
}
.sidebar-link .sidebar-tooltip {
    display: none;
    position: absolute;
    left: 70px;
    top: 50%;
    transform: translateY(-50%);
    background: #232836;
    color: #fff;
    padding: 4px 12px;
    border-radius: 4px;
    font-size: 0.95rem;
    white-space: nowrap;
    z-index: 1005;
    box-shadow: 0 2px 8px #0003;
}
.sidebar:not(.expanded) .sidebar-link:hover .sidebar-tooltip {
    display: block;
}
</style>
<div class="sidebar" id="sidebar">
    <img src="assets/images/logo-pixel12digital.png" alt="Pixel 12 Digital" class="sidebar-logo" id="sidebarToggle">
    <nav class="sidebar-nav">
        <!-- Dashboard -->
        <a href="dashboard.php" class="sidebar-link<?php if($page=='dashboard.php') echo ' active'; ?>" title="Dashboard">
            <!-- Lucide BarChart3 SVG -->
            <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><rect x="3" y="12" width="4" height="8"/><rect x="9" y="8" width="4" height="12"/><rect x="15" y="4" width="4" height="16"/></svg>
            <span class="sidebar-label">Dashboard</span>
            <span class="sidebar-tooltip">Dashboard</span>
        </a>
        <!-- Vendas & CRM -->
        <div class="sidebar-group<?php if(in_array($page,['leads.php','propostas.php','clientes.php'])) echo ' open'; ?>">
            <a href="#" class="sidebar-link has-sub" title="Vendas & CRM">
                <!-- Lucide Users SVG -->
                <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                <span class="sidebar-label">Vendas & CRM</span>
                <span class="sidebar-tooltip">Vendas & CRM</span>
            </a>
            <div class="sidebar-submenu">
                <a href="/admin/leads.php" class="sidebar-sublink<?php if($page=='leads.php') echo ' active'; ?>">
                    <!-- Lucide Magnet SVG -->
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M6 15a6 6 0 0 1 12 0"/><line x1="6" y1="15" x2="6" y2="19"/><line x1="18" y1="15" x2="18" y2="19"/></svg>
                    Leads
                </a>
                <a href="/admin/propostas.php" class="sidebar-sublink<?php if($page=='propostas.php') echo ' active'; ?>">
                    <!-- Lucide Briefcase SVG -->
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 3v4M8 3v4"/></svg>
                    Propostas
                </a>
                <a href="clientes.php" class="sidebar-sublink<?php if($page=='clientes.php') echo ' active'; ?>">
                    <!-- Lucide Users SVG -->
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    Clientes
                </a>
            </div>
        </div>
        <!-- Projetos -->
        <div class="sidebar-group<?php if(in_array($page,['solucoes.php','projetos.php','onboarding.php'])) echo ' open'; ?>">
            <a href="#" class="sidebar-link has-sub" title="Projetos">
                <!-- Lucide Folder SVG -->
                <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h6l2 3h8a2 2 0 0 1 2 2z"/></svg>
                <span class="sidebar-label">Projetos</span>
                <span class="sidebar-tooltip">Projetos</span>
            </a>
            <div class="sidebar-submenu">
                <a href="/admin/solucoes.php" class="sidebar-sublink<?php if($page=='solucoes.php') echo ' active'; ?>">
                    <!-- Lucide Blocks SVG -->
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                    Soluções
                </a>
                <a href="/admin/projetos.php" class="sidebar-sublink<?php if($page=='projetos.php') echo ' active'; ?>">
                    <!-- Lucide Folder SVG -->
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h6l2 3h8a2 2 0 0 1 2 2z"/></svg>
                    Projetos
                </a>
                <a href="/admin/onboarding.php" class="sidebar-sublink<?php if($page=='onboarding.php') echo ' active'; ?>">
                    <!-- Lucide Rocket SVG -->
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M4.5 16.5L3 21l4.5-1.5M15 3a2.5 2.5 0 0 1 3.5 3.5L7 18l-4 1 1-4 11.5-11.5z"/></svg>
                    Onboarding
                </a>
            </div>
        </div>
        <!-- Financeiro -->
        <div class="sidebar-group<?php if(in_array($page,['faturas.php','assinaturas.php','contas-pagar.php'])) echo ' open'; ?>">
            <a href="#" class="sidebar-link has-sub" title="Financeiro">
                <!-- Lucide CreditCard SVG -->
                <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>
                <span class="sidebar-label">Financeiro</span>
                <span class="sidebar-tooltip">Financeiro</span>
            </a>
            <div class="sidebar-submenu">
                <a href="/painel/faturas.php" class="sidebar-sublink<?php if($page=='faturas.php') echo ' active'; ?>">
                    <!-- Lucide CreditCard SVG -->
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>
                    Faturas
                </a>
                <a href="/financeiro/assinaturas" class="sidebar-sublink<?php if($page=='assinaturas.php') echo ' active'; ?>">
                    <!-- Lucide RefreshCcw SVG -->
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M21 2v6h-6"/><path d="M3 22v-6h6"/><path d="M21 2a12 12 0 0 1-19.36 9"/><path d="M3 22A12 12 0 0 0 21 2"/></svg>
                    Assinaturas
                </a>
                <a href="/admin/contas-pagar.php" class="sidebar-sublink<?php if($page=='contas-pagar.php') echo ' active'; ?>">
                    <!-- Lucide Banknote SVG -->
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><rect x="2" y="6" width="20" height="12" rx="2"/><circle cx="12" cy="12" r="4"/></svg>
                    Contas a Pagar
                </a>
            </div>
        </div>
        <!-- Suporte & Relacionamento -->
        <div class="sidebar-group<?php if(in_array($page,['tickets.php','comunicacoes.php'])) echo ' open'; ?>">
            <a href="#" class="sidebar-link has-sub" title="Suporte & Relacionamento">
                <!-- Lucide MessageCircle SVG -->
                <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M21 11.5a8.38 8.38 0 0 1-1.9 5.4A8.5 8.5 0 0 1 3 12c0-4.7 3.8-8.5 8.5-8.5a8.38 8.38 0 0 1 5.4 1.9l3.7-1v6h-6l1-3.7z"/></svg>
                <span class="sidebar-label">Suporte & Relacionamento</span>
                <span class="sidebar-tooltip">Suporte & Relacionamento</span>
            </a>
            <div class="sidebar-submenu">
                <a href="/admin/tickets.php" class="sidebar-sublink<?php if($page=='tickets.php') echo ' active'; ?>">
                    <!-- Lucide Ticket SVG -->
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><rect x="2" y="7" width="20" height="10" rx="2"/><path d="M2 9a2 2 0 0 0 2-2"/><path d="M22 15a2 2 0 0 1-2 2"/></svg>
                    Tickets
                </a>
                <a href="/admin/comunicacoes.php" class="sidebar-sublink<?php if($page=='comunicacoes.php') echo ' active'; ?>">
                    <!-- Lucide Mail SVG -->
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><rect x="2" y="4" width="20" height="16" rx="2"/><polyline points="22,6 12,13 2,6"/></svg>
                    Comunicações
                </a>
            </div>
        </div>
        <!-- Configurações -->
        <div class="sidebar-group<?php if(in_array($page,['usuarios.php','integracoes.php','termos.php'])) echo ' open'; ?>">
            <a href="#" class="sidebar-link has-sub" title="Configurações">
                <!-- Lucide Settings SVG -->
                <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09a1.65 1.65 0 0 0-1-1.51 1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09a1.65 1.65 0 0 0 1.51-1 1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33h.09a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51h.09a1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82v.09a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
                <span class="sidebar-label">Configurações</span>
                <span class="sidebar-tooltip">Configurações</span>
            </a>
            <div class="sidebar-submenu">
                <a href="/admin/usuarios.php" class="sidebar-sublink<?php if($page=='usuarios.php') echo ' active'; ?>">
                    <!-- Lucide User SVG -->
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-3-3.87"/><path d="M4 21v-2a4 4 0 0 1 3-3.87"/><circle cx="12" cy="7" r="4"/></svg>
                    Usuários
                </a>
                <a href="/admin/integracoes.php" class="sidebar-sublink<?php if($page=='integracoes.php') echo ' active'; ?>">
                    <!-- Lucide Settings SVG -->
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09a1.65 1.65 0 0 0-1-1.51 1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09a1.65 1.65 0 0 0 1.51-1 1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33h.09a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51h.09a1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82v.09a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
                    Integrações
                </a>
                <a href="/admin/termos.php" class="sidebar-sublink<?php if($page=='termos.php') echo ' active'; ?>">
                    <!-- Lucide FileText SVG -->
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                    Termos & Documentos
                </a>
            </div>
        </div>
        <!-- Ferramentas Avançadas (apenas admin) -->
        <?php if ($is_admin): ?>
        <div class="sidebar-group<?php if(in_array($page,['logs.php','webhook-test.php','entregas-manuais.php'])) echo ' open'; ?>">
            <a href="#" class="sidebar-link has-sub" title="Ferramentas Avançadas">
                <!-- Lucide Shield SVG -->
                <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                <span class="sidebar-label">Ferramentas Avançadas</span>
                <span class="sidebar-tooltip">Ferramentas Avançadas</span>
            </a>
            <div class="sidebar-submenu">
                <a href="/admin/logs.php" class="sidebar-sublink<?php if($page=='logs.php') echo ' active'; ?>">
                    <!-- Lucide Wrench SVG -->
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M14.7 6.3a5 5 0 0 0-7.1 7.1l7.1-7.1z"/><path d="M5.3 17.7a5 5 0 0 0 7.1-7.1l-7.1 7.1z"/></svg>
                    Logs de Sistema
                </a>
                <a href="/admin/webhook-test.php" class="sidebar-sublink<?php if($page=='webhook-test.php') echo ' active'; ?>">
                    <!-- Lucide Tool SVG -->
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M14.7 6.3a5 5 0 0 0-7.1 7.1l7.1-7.1z"/><path d="M5.3 17.7a5 5 0 0 0 7.1-7.1l-7.1 7.1z"/></svg>
                    Testes de Webhook
                </a>
                <a href="/admin/entregas-manuais.php" class="sidebar-sublink<?php if($page=='entregas-manuais.php') echo ' active'; ?>">
                    <!-- Lucide Package SVG -->
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4a2 2 0 0 0 1-1.73z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg>
                    Entregas Manuais
                </a>
            </div>
        </div>
        <?php endif; ?>
    </nav>
    <div class="sidebar-chat-widget">
        <button class="chat-widget-btn" title="Chat Unificado">💬</button>
    </div>
</div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
window.addEventListener('DOMContentLoaded', function() {
    var sidebar = document.getElementById('sidebar');
    sidebar.classList.remove('expanded');
    // Expande/fecha ao clicar no logo
    document.getElementById('sidebarToggle').onclick = function() {
        sidebar.classList.toggle('expanded');
    };
    // Expande/fecha ao clicar em qualquer item com submenu
    document.querySelectorAll('.sidebar-link.has-sub').forEach(function(link) {
        link.onclick = function(e) {
            e.preventDefault();
            var group = this.parentElement;
            var isOpen = group.classList.contains('open');
            // Fecha todos os grupos
            document.querySelectorAll('.sidebar-group').forEach(function(g) { g.classList.remove('open'); });
            if (!isOpen) {
                group.classList.add('open');
                sidebar.classList.add('expanded');
            } else {
                group.classList.remove('open');
                // Só recolhe o menu se nenhum grupo estiver aberto
                var algumAberto = false;
                document.querySelectorAll('.sidebar-group').forEach(function(g) { if (g.classList.contains('open')) algumAberto = true; });
                if (!algumAberto) sidebar.classList.remove('expanded');
            }
        };
    });
});
</script>
<!-- Proteção extra: não imprimir nada após o fechamento do menu -->
<?php /* Fim seguro do menu_lateral.php */ ?> 