<?php
require_once __DIR__ . '/../painel/db.php';
header('Content-Type: application/json');
$id = intval($_POST['id'] ?? 0);
if (!$id) {
    echo json_encode(['success' => false, 'error' => 'ID ausente.']);
    exit;
}
// Verifica se há assinaturas vinculadas
$res = $mysqli->query("SELECT COUNT(*) AS total FROM assinaturas WHERE plano_id = $id");
$total = $res ? $res->fetch_assoc()['total'] : 0;
if ($total > 0) {
    // Não pode excluir, apenas desativa
    $ok = $mysqli->query("UPDATE planos_assinatura SET ativo=0 WHERE id=$id");
    echo json_encode(['success' => $ok, 'desativado' => true]);
} else {
    $ok = $mysqli->query("DELETE FROM planos_assinatura WHERE id=$id");
    echo json_encode(['success' => $ok, 'excluido' => true]);
} 