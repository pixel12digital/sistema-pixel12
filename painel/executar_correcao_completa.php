<?php
/**
 * Script para executar correção completa do banco de dados
 * Este script executa todas as correções necessárias em sequência
 */

echo "=== CORREÇÃO COMPLETA DO BANCO DE DADOS ===\n";
echo "Este script irá:\n";
echo "1. Remover duplicatas de clientes e cobranças\n";
echo "2. Adicionar campos de proteção para dados manuais\n";
echo "3. Identificar dados editados manualmente\n";
echo "4. Limpar cobranças órfãs\n";
echo "5. Preparar para sincronização protegida\n\n";

echo "ATENÇÃO: Esta operação pode demorar alguns minutos.\n";
echo "Deseja continuar? (s/n): ";

$handle = fopen("php://stdin", "r");
$line = fgets($handle);
fclose($handle);

if (trim(strtolower($line)) !== 's') {
    echo "Operação cancelada.\n";
    exit(0);
}

echo "\nIniciando correção...\n\n";

// Executar correção do banco
echo "Executando correção do banco de dados...\n";
include __DIR__ . '/corrigir_banco_asaas.php';

echo "\n=== CORREÇÃO CONCLUÍDA ===\n";
echo "Agora você pode executar a sincronização protegida.\n";
echo "Os dados editados manualmente serão preservados.\n\n";

echo "Deseja executar a sincronização protegida agora? (s/n): ";

$handle = fopen("php://stdin", "r");
$line = fgets($handle);
fclose($handle);

if (trim(strtolower($line)) === 's') {
    echo "\nExecutando sincronização protegida...\n";
    include __DIR__ . '/sincroniza_asaas_protegido.php';
    echo "\n=== SINCRONIZAÇÃO CONCLUÍDA ===\n";
} else {
    echo "\nSincronização não executada. Execute manualmente quando desejar.\n";
}

echo "\nProcesso finalizado!\n";
?> 