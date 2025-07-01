<?php
// View: Cadastro de Cobrança Multi-Step
?>
<style>
    body { background: #1a202c; color: #fff; }
    .hidden { display: none; }
</style>
<div class="max-w-xl mx-auto mt-12">
    <form id="formCobranca" method="post" action="/admin/financeiro" class="bg-gray-800 border border-gray-700 p-6 rounded shadow-md">
        <!-- Step 1: Busca/Cadastro de Cliente -->
        <div class="step step-1">
            <h2 class="text-purple-600 text-lg font-bold mb-4">1. Cliente</h2>
            <div class="flex items-center space-x-2 mb-4">
                <input type="text" name="cpf_cnpj" id="cpfCnpj" placeholder="CPF ou CNPJ" class="p-2 rounded bg-gray-900 border border-gray-700 flex-1" />
                <button type="button" id="btnBuscarCliente" class="bg-purple-600 px-4 py-2 rounded text-white">Buscar Cliente</button>
            </div>
            <div id="clienteForm" class="hidden bg-gray-900 p-4 rounded mb-4">
                <div class="mb-2"><label class="text-purple-600">Nome</label><input type="text" name="nome" class="w-full p-2 rounded bg-gray-800 border border-gray-700" /></div>
                <div class="mb-2"><label class="text-purple-600">E-mail</label><input type="email" name="email" class="w-full p-2 rounded bg-gray-800 border border-gray-700" /></div>
                <div class="mb-2"><label class="text-purple-600">Celular</label><input type="text" name="celular" class="w-full p-2 rounded bg-gray-800 border border-gray-700" /></div>
                <div class="mb-2"><label class="text-purple-600">CEP</label><input type="text" name="cep" class="w-full p-2 rounded bg-gray-800 border border-gray-700" /></div>
                <div class="mb-2"><label class="text-purple-600">Rua</label><input type="text" name="rua" class="w-full p-2 rounded bg-gray-800 border border-gray-700" /></div>
                <div class="mb-2"><label class="text-purple-600">Número</label><input type="text" name="numero" class="w-full p-2 rounded bg-gray-800 border border-gray-700" /></div>
                <div class="mb-2"><label class="text-purple-600">Complemento</label><input type="text" name="complemento" class="w-full p-2 rounded bg-gray-800 border border-gray-700" /></div>
                <div class="mb-2"><label class="text-purple-600">Bairro</label><input type="text" name="bairro" class="w-full p-2 rounded bg-gray-800 border border-gray-700" /></div>
                <div class="mb-2"><label class="text-purple-600">Cidade</label><input type="text" name="cidade" class="w-full p-2 rounded bg-gray-800 border border-gray-700" /></div>
                <div class="text-yellow-400 text-xs mt-2">Se o cliente não quiser receber notificações do Asaas, marque a opção "Não notificar" ao cadastrar.</div>
            </div>
            <div class="flex justify-end mt-4">
                <button type="button" id="btnProx1" class="bg-purple-600 px-4 py-2 rounded text-white" disabled>Próximo</button>
            </div>
        </div>
        <!-- Step 2: Tipo de Cobrança e Dados -->
        <div class="step step-2 hidden">
            <h2 class="text-purple-600 text-lg font-bold mb-4">2. Tipo de Cobrança</h2>
            <div class="mb-4">
                <label class="text-purple-600">Tipo</label>
                <select id="tipoCobranca" name="tipo" class="w-full p-2 rounded bg-gray-800 border border-gray-700">
                    <option value="avulsa">Avulsa</option>
                    <option value="parcelamento">Parcelamento</option>
                    <option value="assinatura">Assinatura</option>
                </select>
            </div>
            <div id="dadosCobranca"></div>
            <div class="flex justify-between mt-4">
                <button type="button" id="btnVoltar1" class="bg-gray-600 px-4 py-2 rounded text-white">Voltar</button>
                <button type="button" id="btnProx2" class="bg-purple-600 px-4 py-2 rounded text-white">Próximo</button>
            </div>
        </div>
        <!-- Step 3: Resumo/Confirmação -->
        <div class="step step-3 hidden">
            <h2 class="text-purple-600 text-lg font-bold mb-4">3. Resumo</h2>
            <div id="resumoCobranca" class="bg-gray-900 p-4 rounded mb-4"></div>
            <div class="flex justify-between mt-4">
                <button type="button" id="btnVoltar2" class="bg-gray-600 px-4 py-2 rounded text-white">Voltar</button>
                <button type="submit" id="btnCadastrar" class="bg-green-600 px-4 py-2 rounded text-white">Cadastrar Cobrança</button>
            </div>
        </div>
        <input type="hidden" name="clienteId" id="clienteId" />
    </form>
</div>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="/path/to/cobranca_add.js"></script> 