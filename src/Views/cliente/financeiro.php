<?php
// Espera: $cobrancas (array de Cobranca)
?>
<div class="max-w-2xl mx-auto mt-8">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-semibold">Minhas Faturas</h1>
        <a href="/cliente/painel" class="text-purple-500 hover:underline">Voltar ao Painel</a>
    </div>
    <?php if (empty($cobrancas)): ?>
        <div class="text-gray-400 italic">Você não possui faturas no momento.</div>
    <?php else: ?>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <?php foreach ($cobrancas as $cobranca): ?>
        <div class="bg-white p-4 rounded shadow mb-4 flex flex-col">
            <div class="text-xl font-bold text-gray-800 mb-2">R$ <?= number_format($cobranca->valor, 2, ',', '.') ?></div>
            <div class="text-gray-600 mb-1">Vencimento: <?= date('d/m/Y', strtotime($cobranca->vencimento)) ?></div>
            <?php
                $status = strtoupper($cobranca->status);
                if ($status === 'PAID' || $status === 'RECEBIDO') {
                    $badge = '<span class="inline-block px-2 py-1 rounded bg-green-100 text-green-800 text-xs">Pago</span>';
                } elseif ($status === 'PENDING' || $status === 'PENDENTE') {
                    $badge = '<span class="inline-block px-2 py-1 rounded bg-yellow-100 text-yellow-800 text-xs">Pendente</span>';
                } else {
                    $badge = '<span class="inline-block px-2 py-1 rounded bg-red-100 text-red-800 text-xs">Vencido</span>';
                }
                echo $badge;
            ?>
            <?php if (!empty($cobranca->url_fatura) && ($status === 'PENDING' || $status === 'PENDENTE' || $status === 'OVERDUE' || $status === 'VENCIDO')): ?>
                <a href="<?= htmlspecialchars($cobranca->url_fatura) ?>" target="_blank" class="bg-purple-600 text-white px-4 py-2 rounded mt-2 hover:bg-purple-700 text-center">Pagar Agora</a>
            <?php elseif (!empty($cobranca->url_fatura) && ($status === 'PAID' || $status === 'RECEBIDO')): ?>
                <a href="<?= htmlspecialchars($cobranca->url_fatura) ?>" target="_blank" class="bg-purple-600 text-white px-4 py-2 rounded mt-2 hover:bg-purple-700 text-center">Visualizar Boleto</a>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div> 