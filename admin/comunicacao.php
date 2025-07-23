<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Força limpeza de cache com headers ainda mais agressivos
header('Cache-Control: no-cache, no-store, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');
header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
header('ETag: "' . md5(time()) . '"');

$page = 'comunicacao.php';
$page_title = 'Comunicação - Gerenciar Canais';
$custom_header = '<a href="../painel/index.php" class="btn btn-secondary">← Voltar para Painel</a>';

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../painel/db.php';

// Processa exclusão de canal antes de renderizar a página
if (
  $_SERVER['REQUEST_METHOD'] === 'POST' &&
  isset($_POST['acao']) && $_POST['acao'] === 'excluir_canal' &&
  isset($_POST['canal_id'])
) {
  $canal_id = intval($_POST['canal_id']);
  $mysqli->query("DELETE FROM canais_comunicacao WHERE id = $canal_id");
  echo '<script>location.href = location.pathname;</script>';
  exit;
}

// Processa cadastro de canal
if (
  $_SERVER['REQUEST_METHOD'] === 'POST' &&
  isset($_POST['acao']) && $_POST['acao'] === 'add_canal'
) {
  $identificador = '';
  $nome_exibicao = $mysqli->real_escape_string(trim($_POST['nome_exibicao']));
  $porta = intval($_POST['porta']);
  $tipo = 'whatsapp';
  $status = 'pendente';
  
  // Verifica se já existe um canal com esta porta
  $canal_existente = $mysqli->query("SELECT id FROM canais_comunicacao WHERE porta = $porta")->fetch_assoc();
  if ($canal_existente) {
    $erro_cadastro = 'Já existe um canal WhatsApp nesta porta.';
  } else {
    // Canal não existe, insere novo
    $mysqli->query("INSERT INTO canais_comunicacao (tipo, identificador, nome_exibicao, status, data_conexao, porta) VALUES ('$tipo', '$identificador', '$nome_exibicao', '$status', NULL, $porta)");
    $canal_id = $mysqli->insert_id;
  }
  
  // Redireciona para evitar resubmissão
  echo '<script>location.href = location.pathname;</script>';
  exit;
}

require_once __DIR__ . '/../painel/template.php';

function render_content() {
  global $mysqli, $erro_cadastro;
?>

<style>
.comunicacao-container {
  padding: 30px;
  max-width: 1400px;
  margin: 0 auto;
}

.header-comunicacao {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 30px;
  padding-bottom: 20px;
  border-bottom: 2px solid #e5e7eb;
}

.header-comunicacao h1 {
  color: #1f2937;
  margin: 0;
  font-size: 28px;
  font-weight: 600;
}

.btn {
  padding: 10px 20px;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-weight: 500;
  text-decoration: none;
  display: inline-block;
  transition: all 0.2s;
}

.btn-primary {
  background: #7c3aed;
  color: white;
}

.btn-primary:hover {
  background: #6d28d9;
  transform: translateY(-1px);
}

.btn-danger {
  background: #ef4444;
  color: white;
  font-size: 12px;
  padding: 6px 12px;
}

.btn-danger:hover {
  background: #dc2626;
}

.btn-secondary {
  background: #6b7280;
  color: white;
}

.btn-secondary:hover {
  background: #4b5563;
}

.canais-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
  gap: 20px;
  margin-bottom: 30px;
}

.canal-card {
  background: white;
  border-radius: 12px;
  padding: 20px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  border: 1px solid #e5e7eb;
  transition: all 0.2s;
}

.canal-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.canal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 15px;
}

.canal-nome {
  font-size: 18px;
  font-weight: 600;
  color: #1f2937;
  margin: 0;
}

.canal-status {
  padding: 4px 12px;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 500;
  text-transform: uppercase;
}

.status-ativo {
  background: #d1fae5;
  color: #065f46;
}

.status-pendente {
  background: #fef3c7;
  color: #92400e;
}

.status-erro {
  background: #fee2e2;
  color: #991b1b;
}

.canal-info {
  color: #6b7280;
  font-size: 14px;
  margin-bottom: 15px;
}

.canal-info strong {
  color: #374151;
}

.canal-actions {
  display: flex;
  gap: 10px;
  justify-content: flex-end;
}

.add-canal-form {
  background: white;
  border-radius: 12px;
  padding: 25px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  border: 1px solid #e5e7eb;
  margin-bottom: 30px;
}

.form-group {
  margin-bottom: 20px;
}

.form-group label {
  display: block;
  margin-bottom: 8px;
  font-weight: 500;
  color: #374151;
}

.form-group input {
  width: 100%;
  padding: 12px;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  font-size: 14px;
  transition: border-color 0.2s;
}

.form-group input:focus {
  outline: none;
  border-color: #7c3aed;
  box-shadow: 0 0 0 3px rgba(124, 58, 237, 0.1);
}

.erro-msg {
  background: #fee2e2;
  color: #991b1b;
  padding: 12px;
  border-radius: 6px;
  margin-bottom: 20px;
  border: 1px solid #fecaca;
}

.whatsapp-icon {
  color: #25d366;
  font-size: 20px;
}

.form-row {
  display: grid;
  grid-template-columns: 2fr 1fr;
  gap: 20px;
}

@media (max-width: 768px) {
  .form-row {
    grid-template-columns: 1fr;
  }
  
  .canais-grid {
    grid-template-columns: 1fr;
  }
  
  .header-comunicacao {
    flex-direction: column;
    gap: 15px;
    align-items: stretch;
  }
}
</style>

<div class="comunicacao-container">
  <div class="header-comunicacao">
    <h1>📱 Gerenciar Canais de Comunicação</h1>
  </div>

  <?php if (isset($erro_cadastro)): ?>
    <div class="erro-msg">
      ⚠️ <?= htmlspecialchars($erro_cadastro) ?>
    </div>
  <?php endif; ?>

  <!-- Formulário para adicionar novo canal -->
  <div class="add-canal-form">
    <h3 style="margin-bottom: 20px; color: #1f2937;">➕ Adicionar Novo Canal WhatsApp</h3>
    <form method="POST">
      <input type="hidden" name="acao" value="add_canal">
      
      <div class="form-row">
        <div class="form-group">
          <label for="nome_exibicao">Nome do Canal</label>
          <input type="text" id="nome_exibicao" name="nome_exibicao" 
                 placeholder="Ex: WhatsApp Principal" required>
        </div>
        
        <div class="form-group">
          <label for="porta">Porta</label>
          <input type="number" id="porta" name="porta" 
                 placeholder="Ex: 3000" min="3000" max="9999" required>
        </div>
      </div>
      
      <button type="submit" class="btn btn-primary">
        🚀 Criar Canal WhatsApp
      </button>
    </form>
  </div>

  <!-- Lista de canais existentes -->
  <h3 style="margin-bottom: 20px; color: #1f2937;">📋 Canais Configurados</h3>
  
  <div class="canais-grid">
    <?php
    $canais = $mysqli->query("SELECT * FROM canais_comunicacao ORDER BY data_criacao DESC");
    
    if ($canais && $canais->num_rows > 0):
      while ($canal = $canais->fetch_assoc()):
        $status_class = 'status-' . $canal['status'];
        $status_icon = $canal['status'] === 'ativo' ? '✅' : 
                      ($canal['status'] === 'pendente' ? '⏳' : '❌');
    ?>
        <div class="canal-card">
          <div class="canal-header">
            <h4 class="canal-nome">
              <span class="whatsapp-icon">📱</span>
              <?= htmlspecialchars($canal['nome_exibicao']) ?>
            </h4>
            <span class="canal-status <?= $status_class ?>">
              <?= $status_icon ?> <?= ucfirst($canal['status']) ?>
            </span>
          </div>
          
          <div class="canal-info">
            <strong>Porta:</strong> <?= $canal['porta'] ?><br>
            <strong>Tipo:</strong> <?= ucfirst($canal['tipo']) ?><br>
            <strong>Criado:</strong> <?= date('d/m/Y H:i', strtotime($canal['data_criacao'])) ?><br>
            <?php if ($canal['data_conexao']): ?>
              <strong>Última Conexão:</strong> <?= date('d/m/Y H:i', strtotime($canal['data_conexao'])) ?>
            <?php endif; ?>
          </div>
          
          <div class="canal-actions">
            <form method="POST" style="display: inline;" 
                  onsubmit="return confirm('Tem certeza que deseja excluir este canal?')">
              <input type="hidden" name="acao" value="excluir_canal">
              <input type="hidden" name="canal_id" value="<?= $canal['id'] ?>">
              <button type="submit" class="btn btn-danger">
                🗑️ Excluir
              </button>
            </form>
          </div>
        </div>
    <?php 
      endwhile;
    else:
    ?>
      <div style="grid-column: 1 / -1; text-align: center; padding: 40px; color: #6b7280;">
        <h3>📱 Nenhum canal configurado</h3>
        <p>Adicione seu primeiro canal WhatsApp usando o formulário acima.</p>
      </div>
    <?php endif; ?>
  </div>

  <!-- Instruções -->
  <div style="background: #f3f4f6; padding: 20px; border-radius: 8px; margin-top: 30px;">
    <h4 style="color: #1f2937; margin-bottom: 15px;">📚 Como Configurar um Canal WhatsApp</h4>
    <ol style="color: #4b5563; line-height: 1.6;">
      <li><strong>Adicionar Canal:</strong> Preencha o nome e escolha uma porta livre</li>
      <li><strong>Configurar Servidor:</strong> Configure o bot WhatsApp na porta escolhida</li>
      <li><strong>Conectar:</strong> Escaneie o QR Code para conectar o WhatsApp</li>
      <li><strong>Testar:</strong> Envie uma mensagem de teste para verificar</li>
    </ol>
  </div>
</div>

<?php
}
?> 