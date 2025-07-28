<?php
require_once '../config.php';
require_once 'db.php';

// Processar formulário
if ($_POST) {
    $cliente_id = $_POST['cliente_id'] ?? '';
    $mensagem = $_POST['mensagem'] ?? '';
    $data_hora = $_POST['data_hora'] ?? date('Y-m-d H:i:s');
    $numero_whatsapp = $_POST['numero_whatsapp'] ?? '';
    
    if ($cliente_id && $mensagem) {
        $sql = "INSERT INTO mensagens_comunicacao 
                (canal_id, cliente_id, mensagem, tipo, data_hora, direcao, status, numero_whatsapp) 
                VALUES (36, ?, ?, 'texto', ?, 'enviado', 'enviado', ?)";
        
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param('isss', $cliente_id, $mensagem, $data_hora, $numero_whatsapp);
        
        if ($stmt->execute()) {
            $success = "✅ Mensagem enviada salva com sucesso!";
        } else {
            $error = "❌ Erro ao salvar: " . $mysqli->error;
        }
    }
}

// Buscar clientes
$clientes = $mysqli->query("SELECT id, nome, numero_whatsapp FROM clientes ORDER BY nome");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Adicionar Mensagem Enviada</title>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .container { max-width: 600px; margin: 0 auto; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input, select, textarea { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; }
        button { background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; }
        .success { color: green; background: #d4edda; padding: 10px; border-radius: 4px; margin-bottom: 15px; }
        .error { color: red; background: #f8d7da; padding: 10px; border-radius: 4px; margin-bottom: 15px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>📤 Adicionar Mensagem Enviada</h1>
        <p>Use esta página para adicionar mensagens que você enviou pelo WhatsApp Web mas não foram salvas automaticamente.</p>
        <p><strong>Número conectado no sistema:</strong> +55 47 9714-6908</p>
        <p><strong>Número de teste:</strong> +55 47 9961-6469</p>
        
        <?php if (isset($success)): ?>
            <div class="success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label>Cliente:</label>
                <select name="cliente_id" required>
                    <option value="">Selecione um cliente...</option>
                    <?php while ($cliente = $clientes->fetch_assoc()): ?>
                        <option value="<?php echo $cliente['id']; ?>">
                            <?php echo $cliente['nome']; ?> (<?php echo $cliente['numero_whatsapp']; ?>)
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label>Mensagem:</label>
                <textarea name="mensagem" rows="4" required placeholder="Digite a mensagem que você enviou..."></textarea>
            </div>
            
            <div class="form-group">
                <label>Data/Hora:</label>
                <input type="datetime-local" name="data_hora" value="<?php echo date('Y-m-d\TH:i'); ?>" required>
            </div>
            
            <div class="form-group">
                <label>Número WhatsApp:</label>
                <input type="text" name="numero_whatsapp" placeholder="Ex: 554796164699">
            </div>
            
            <button type="submit">💾 Salvar Mensagem Enviada</button>
        </form>
        
        <hr>
        <p><a href="chat.php">← Voltar para o Chat</a></p>
    </div>
</body>
</html> 