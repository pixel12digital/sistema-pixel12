<?php include_once __DIR__ . '/../../../../painel/menu_lateral.php'; ?>
// detalhes de $assinatura
echo "<h1>Assinatura #{$assinatura['id']}</h1>";
echo "<p>Cliente: {$assinatura['cliente_id']}</p>";
echo "<p>Status: {$assinatura['status']}</p>";
echo "<p>Periodicidade: {$assinatura['periodicidade']}</p>";
echo "<p>Início: {$assinatura['start_date']}</p>";
echo "<p>Próximo Vencimento: {$assinatura['next_due_date']}</p>";
echo "<p>Criado em: {$assinatura['created_at']}</p>";
echo "<p>Atualizado em: {$assinatura['updated_at']}</p>"; 