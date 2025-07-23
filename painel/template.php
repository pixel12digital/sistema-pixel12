<?php
// Template base para o painel Pixel12Digital
if (session_status() === PHP_SESSION_NONE) session_start();
$page_title = $page_title ?? 'Título da Página';
$custom_header = $custom_header ?? '';

// Detectar se estamos sendo acessados do diretório admin
$is_admin_access = strpos($_SERVER['REQUEST_URI'], '/admin/') !== false;
$asset_path = $is_admin_access ? '../painel/' : '';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?= htmlspecialchars($page_title) ?> • Pixel12Digital</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="<?= $asset_path ?>assets/style.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <?php if ($is_admin_access): ?>
  <style>
  /* Correções para acesso via admin */
  .sidebar-logo {
    background-image: url('<?= $asset_path ?>assets/images/logo-pixel12digital.png') !important;
  }
  </style>
  <?php endif; ?>
  <style>
  html body .painel-card {
    background: #f9f9fb !important;
    border-radius: 12px !important;
    box-shadow: 0 2px 12px #0001 !important;
    padding: 24px 20px !important;
    margin-bottom: 24px !important;
  }
  html body .painel-card h4 {
    color: #7c2ae8 !important;
    font-size: 1.1rem !important;
    margin-bottom: 12px !important;
    display: flex !important;
    align-items: center !important;
    gap: 8px !important;
  }
  html body .painel-card table {
    width: 100% !important;
    font-size: 0.98rem !important;
  }
  html body .painel-card td {
    padding: 4px 8px !important;
    border-bottom: 1px solid #ececec !important;
  }
  html body .painel-avatar {
    width: 56px !important; height: 56px !important;
    border-radius: 50% !important;
    background: #ede9fe !important;
    color: #7c2ae8 !important;
    font-size: 2rem !important;
    font-weight: bold !important;
    display: flex !important; align-items: center !important; justify-content: center !important;
    margin-right: 16px !important;
  }
  html body .painel-header {
    display: flex !important; align-items: center !important; gap: 16px !important; margin-bottom: 12px !important;
  }
  html body .painel-nome {
    font-size: 1.7rem !important; font-weight: bold !important; color: #7c2ae8 !important;
  }
  html body .painel-badge {
    display: inline-block !important; background: #e0e7ff !important; color: #3730a3 !important;
    border-radius: 6px !important; padding: 2px 10px !important; font-size: 0.85rem !important; margin-left: 8px !important;
  }
  @media (max-width: 900px) {
    html body .painel-grid { display: block !important; }
    html body .painel-card { margin-bottom: 18px !important; }
  }
  html body .painel-grid {
    display: grid !important;
    grid-template-columns: 1fr 1fr !important;
    gap: 24px !important;
  }
  </style>
</head>
<body class="bg-gray-100 text-gray-800">
  <!-- Notificação Push Global -->
  <div id="push-notification" style="display:none;position:fixed;bottom:28px;right:28px;z-index:99999;min-width:220px;max-width:90vw;box-shadow:0 2px 12px #0002;background:#fff6f6;border-left:4px solid #e11d48;padding:10px 18px 10px 14px;border-radius:8px;color:#b91c1c;font-size:0.98em;font-weight:500;align-items:center;gap:8px;transition:all 0.3s;pointer-events:auto;">
    <span id="push-notification-msg"></span>
    <button onclick="document.getElementById('push-notification').style.display='none'" style="background:none;border:none;font-size:1.1em;color:#b91c1c;position:absolute;top:4px;right:8px;cursor:pointer;line-height:1;">&times;</button>
  </div>
  <script>
    function showPushNotification(msg, timeout = 7000) {
      var el = document.getElementById('push-notification');
      var msgEl = document.getElementById('push-notification-msg');
      msgEl.textContent = msg;
      el.style.display = 'flex';
      if (timeout > 0) {
        setTimeout(function() { el.style.display = 'none'; }, timeout);
      }
    }
  </script>
  <script>
    // ===== FUNÇÃO GLOBAL PARA GERENCIAR NOTIFICAÇÃO DE STATUS =====
    function gerenciarNotificacaoWhatsApp(status) {
      const notification = document.getElementById('push-notification');
      if (!notification) return;
      
      if (status === 'conectado') {
        // WhatsApp conectado - fechar notificação
        notification.style.display = 'none';
        console.log('[Sistema] WhatsApp conectado - notificação fechada');
      } else if (status === 'desconectado') {
        // WhatsApp desconectado - mostrar notificação
        showPushNotification('Atenção: Existem canais WhatsApp desconectados!', 0);
        console.log('[Sistema] WhatsApp desconectado - notificação exibida');
      }
    }
    
    // Função para verificar status real do WhatsApp
    function verificarStatusRealWhatsApp() {
      return fetch('ajax_whatsapp.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: 'action=status'
      })
      .then(r => r.json())
      .then(resp => {
        // Extrair status real do WhatsApp
        let realStatus = null;
        if (resp.debug && resp.debug.raw_response_preview) {
          try {
            const parsedResponse = JSON.parse(resp.debug.raw_response_preview);
            realStatus = parsedResponse.status?.status || parsedResponse.status;
          } catch (e) {
            console.error('[Sistema] Erro ao fazer parse do raw_response_preview:', e);
          }
        }
        
        // Verificar se WhatsApp está realmente conectado
        const isWhatsAppConnected = 
          (realStatus && ['connected', 'already_connected', 'authenticated', 'ready'].includes(realStatus)) ||
          resp.ready === true ||
          resp.status === 'connected' ||
          resp.status === 'already_connected' ||
          resp.status === 'authenticated' ||
          resp.status === 'ready';
        
        console.log(`[Sistema] Status real do WhatsApp: ${realStatus}, isConnected: ${isWhatsAppConnected}`);
        
        // Gerenciar notificação baseado no status real
        gerenciarNotificacaoWhatsApp(isWhatsAppConnected ? 'conectado' : 'desconectado');
        
        return isWhatsAppConnected;
      })
      .catch(error => {
        console.error('[Sistema] Erro ao verificar status real do WhatsApp:', error);
        // Em caso de erro, assumir desconectado
        gerenciarNotificacaoWhatsApp('desconectado');
        return false;
      });
    }

    // Verificação de canais WhatsApp desconectados
    function checarCanaisWhatsappDesconectados() {
      // CORREÇÃO: Usar função global para verificar status real do WhatsApp
      verificarStatusRealWhatsApp()
      .then(isConnected => {
        if (isConnected) {
          console.log('[Template] WhatsApp conectado - notificação gerenciada automaticamente');
          return; // Parar aqui, não verificar mais
        }
        
        // Se WhatsApp não está conectado, verificar via endpoint como fallback
        return fetch('api/status_canais.php');
      })
      .then(r => {
        if (!r) return; // Se retornou undefined, WhatsApp está conectado
        return r.json();
      })
        .then(statusList => {
        if (!statusList) return; // Se retornou undefined, WhatsApp está conectado
        
          let totalWhatsapp = 0;
          let desconectados = 0;
        let conectados = 0;
        
          statusList.forEach(st => {
            if (st.tipo === 'whatsapp') {
              totalWhatsapp++;
              if (!st.conectado) desconectados++;
            else conectados++;
            }
          });
        
        // DEBUG: Log para verificar o que está acontecendo
        console.log(`[Template] Canais WhatsApp: ${totalWhatsapp} total, ${conectados} conectados, ${desconectados} desconectados`);
        
        // CORREÇÃO: Só mostrar notificação se houver pelo menos 1 WhatsApp desconectado E pelo menos 1 WhatsApp total
        if (desconectados > 0 && totalWhatsapp > 0) {
          console.log('[Template] Notificação de desconectados EXIBIDA');
            showPushNotification('Atenção: Existem canais WhatsApp desconectados!', 0);
          } else {
          console.log('[Template] Notificação de desconectados OCULTA');
          // Esconder notificação se todos conectados ou nenhum WhatsApp
          const notification = document.getElementById('push-notification');
          if (notification) {
            notification.style.display = 'none';
            }
          }
        })
      .catch(error => {
        console.error('[Template] Erro ao verificar canais:', error);
      });
    }

    // Executar verificação a cada 2 minutos (reduzido de 60s para 120s)
    setInterval(checarCanaisWhatsappDesconectados, 120000);
    
    // Executar verificação inicial após 5 segundos
    setTimeout(checarCanaisWhatsappDesconectados, 5000);
    
    // CORREÇÃO: Verificação inicial imediata do status do WhatsApp
    document.addEventListener('DOMContentLoaded', function() {
      console.log('[Sistema] Verificação inicial do status do WhatsApp...');
      verificarStatusRealWhatsApp();
    });
  </script>
  <?php include 'menu_lateral.php'; ?>
  <main class="main-content">
    <!-- Header padrão -->
    <header class="bg-purple-700 text-white p-4 flex flex-col gap-4 lg:flex-row lg:items-center lg:gap-6">
      <h1 class="text-2xl font-semibold flex-1"><?= htmlspecialchars($page_title) ?></h1>
      <?= $custom_header ?>
    </header>
    <!-- Conteúdo dinâmico -->
    <section class="p-4">
      <?php if (function_exists('render_content')) render_content(); ?>
    </section>
  </main>
</body>
</html> 