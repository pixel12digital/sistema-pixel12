<?php
// Template base para o painel Pixel12Digital
if (session_status() === PHP_SESSION_NONE) session_start();
$page_title = $page_title ?? 'Título da Página';
$custom_header = $custom_header ?? '';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?= htmlspecialchars($page_title) ?> • Pixel12Digital</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="assets/style.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
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
    // Verificação de canais WhatsApp desconectados
    function checarCanaisWhatsappDesconectados() {
      fetch('api/status_canais.php')
        .then(r => r.json())
        .then(statusList => {
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
          // Exibir notificação SOMENTE se houver pelo menos 1 desconectado e pelo menos 1 canal WhatsApp
          if (totalWhatsapp > 0 && desconectados > 0) {
            showPushNotification('Atenção: Existem canais WhatsApp desconectados!', 0);
          } else {
            // Esconde notificação se todos conectados
            var el = document.getElementById('push-notification');
            var msgEl = document.getElementById('push-notification-msg');
            if (el && msgEl && msgEl.textContent === 'Atenção: Existem canais WhatsApp desconectados!') {
              el.style.display = 'none';
            }
          }
        })
        .catch(() => {/* Silencioso */});
    }
    setInterval(checarCanaisWhatsappDesconectados, 60000); // 60 segundos
    document.addEventListener('DOMContentLoaded', checarCanaisWhatsappDesconectados);
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