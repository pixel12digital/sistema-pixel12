<?php
/**
 * FORÇAR ATUALIZAÇÃO DA PÁGINA DE CONFIGURAÇÕES
 * 
 * Adiciona headers para evitar cache e forçar atualização
 */

// Headers para evitar cache
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

// Redireciona para a página de configurações
header("Location: painel/configuracoes.php");
exit;
?> 