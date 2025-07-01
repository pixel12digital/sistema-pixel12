<?php
// Espera: $cobrancas (array de Cobranca), $resumo (array associativo)
?>
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
    <div class="bg-white p-4 rounded shadow">
        <div class="text-gray-600">Receita do Mês</div>
        <div class="text-xl font-bold text-green-600">R$ <?= number_format($resumo['total_receita'] ?? 0, 2, ',', '.') ?></div>
    </div>
    <div class="bg-white p-4 rounded shadow">
        <div class="text-gray-600">Pendentes</div>
        <div class="text-xl font-bold text-yellow-600">R$ <?= number_format($resumo['total_pendente'] ?? 0, 2, ',', '.') ?></div>
    </div>
    <div class="bg-white p-4 rounded shadow">
        <div class="text-gray-600">Vencidas</div>
        <div class="text-xl font-bold text-red-600">R$ <?= number_format($resumo['total_vencido'] ?? 0, 2, ',', '.') ?></div>
    </div>
</div>

<form method="get" class="flex items-center space-x-2 mb-4">
    <select name="status" class="border rounded p-2">
        <option value="">Todos</option>
        <option value="PAID">Pago</option>
        <option value="PENDING">Pendente</option>
        <option value="OVERDUE">Vencido</option>
    </select>
    <input type="text" name="cliente" placeholder="Buscar cliente..." class="border rounded p-2" value="<?= htmlspecialchars($_GET['cliente'] ?? '') ?>">
    <button type="submit" class="bg-purple-600 text-white px-4 py-2 rounded">Filtrar</button>
</form>

<div class="overflow-x-auto">
<table class="table-auto w-full mb-4">
    <thead>
        <tr>
            <th class="bg-gray-100 p-2 text-left">Cliente</th>
            <th class="bg-gray-100 p-2 text-left">Valor</th>
            <th class="bg-gray-100 p-2 text-left">Vencimento</th>
            <th class="bg-gray-100 p-2 text-left">Status</th>
            <th class="bg-gray-100 p-2 text-left">Ações</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($cobrancas as $cobranca): ?>
        <tr>
            <td class="p-2"><?= htmlspecialchars($cobranca->cliente_nome ?? 'N/A') ?></td>
            <td class="p-2">R$ <?= number_format($cobranca->valor, 2, ',', '.') ?></td>
            <td class="p-2"><?= date('d/m/Y', strtotime($cobranca->vencimento)) ?></td>
            <td class="p-2">
                <?php
                $status = strtoupper($cobranca->status);
                $color = $status === 'PAID' || $status === 'RECEBIDO' ? 'green' : ($status === 'PENDING' || $status === 'PENDENTE' ? 'yellow' : 'red');
                ?>
                <span class="px-2 py-1 rounded text-white bg-<?= $color ?>-600">
                    <?= $status === 'PAID' || $status === 'RECEBIDO' ? 'Pago' : ($status === 'PENDING' || $status === 'PENDENTE' ? 'Pendente' : 'Vencido') ?>
                </span>
            </td>
            <td class="p-2 flex space-x-2">
                <a href="/admin/financeiro/reenviar?id=<?= $cobranca->id ?>" class="bg-blue-500 text-white px-3 py-1 rounded text-sm">Reenviar Link</a>
                <a href="/admin/financeiro/cancelar?id=<?= $cobranca->id ?>" class="bg-red-500 text-white px-3 py-1 rounded text-sm">Cancelar</a>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div>

<!-- Paginação simples -->
<div class="flex justify-between items-center">
    <a href="#" class="px-3 py-1 bg-gray-200 rounded">Prev</a>
    <a href="#" class="px-3 py-1 bg-gray-200 rounded">Next</a>
</div> 