<?php

$title = 'Painel do Admin';
ob_start();
?>
<div class="grid gap-6">

    <!-- Cadastrar Produto (Admin) -->
    <div class="rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
            <h2 class="text-lg font-semibold text-slate-700">Cadastrar produto</h2>
            <span class="text-xs uppercase tracking-wide text-slate-400">Admin</span>
        </div>
        <div class="px-6 py-6">
            <form id="product-form" class="grid gap-4 md:grid-cols-12">
                <div class="md:col-span-3">
                    <label for="product-sku" class="text-sm font-medium text-slate-600">SKU</label>
                    <input id="product-sku" name="sku" type="text" required class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100" placeholder="Ex.: SKU-001">
                </div>
                <div class="md:col-span-5">
                    <label for="product-name" class="text-sm font-medium text-slate-600">Nome</label>
                    <input id="product-name" name="name" type="text" required class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100" placeholder="Ex.: Camiseta Preta">
                </div>
                <div class="md:col-span-2">
                    <label for="product-size" class="text-sm font-medium text-slate-600">Tamanho</label>
                    <select id="product-size" name="size" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
                        <option value="">Selecione um tamanho</option>
                        <option value="PI">PI</option>
                        <option value="MI">MI</option>
                        <option value="GI">GI</option>
                        <option value="PP">PP</option>
                        <option value="P">P</option>
                        <option value="M">M</option>
                        <option value="G">G</option>
                        <option value="GG">GG</option>
                        <option value="EXG">EXG</option>
                        <option value="EXGG">EXGG</option>
                        <option value="12">12</option>
                        <option value="14">14</option>
                    </select>
                </div>
                <div class="md:col-span-4">
                    <label for="product-description" class="text-sm font-medium text-slate-600">Descrição (opcional)</label>
                    <input id="product-description" name="description" type="text" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100" placeholder="Detalhes">
                </div>

                <div class="md:col-span-2">
                    <label for="product-supplier-cost" class="text-sm font-medium text-slate-600">Custo (R$)</label>
                    <input id="product-supplier-cost" name="supplier_cost" type="number" step="0.01" min="0" required class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100" placeholder="0,00">
                </div>
                <div class="md:col-span-2">
                    <label for="product-sale-price" class="text-sm font-medium text-slate-600">Preço (R$)</label>
                    <input id="product-sale-price" name="sale_price" type="number" step="0.01" min="0" required class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100" placeholder="0,00">
                </div>
                <div class="md:col-span-2">
                    <label for="product-stock" class="text-sm font-medium text-slate-600">Estoque inicial</label>
                    <input id="product-stock" name="stock" type="number" step="1" min="0" value="0" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
                </div>
                <div class="md:col-span-2">
                    <label for="product-min-stock" class="text-sm font-medium text-slate-600">Alerta mínimo</label>
                    <input id="product-min-stock" name="min_stock_alert" type="number" step="1" min="0" value="0" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
                </div>
                <div class="md:col-span-2 flex items-end gap-2">
                    <input id="product-active" name="active" type="checkbox" class="h-5 w-5 rounded border-slate-300 text-emerald-600" checked>
                    <label for="product-active" class="text-sm font-medium text-slate-600">Ativo</label>
                </div>
                <div class="md:col-span-2 flex items-end">
                    <button type="submit" class="w-full rounded-lg bg-blue-600 px-4 py-2 text-white font-medium hover:bg-blue-500 transition">Salvar</button>
                </div>
            </form>
            <p id="product-feedback" class="mt-2 text-sm text-slate-500"></p>
        </div>
    </div>
    <div class="rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
            <h2 class="text-lg font-semibold text-slate-700">Gerenciar estoque por tamanho</h2>
            <span class="text-xs uppercase tracking-wide text-slate-400">Tamanhos</span>
        </div>
        <div class="px-6 py-6 space-y-6">
            <form id="stock-size-form" class="grid gap-4 md:grid-cols-4">
                <div class="md:col-span-2">
                    <label class="text-sm font-medium text-slate-600" for="stock-product-select">Produto</label>
                                        <select id="stock-product-select" class="mt-1 w-full rounded-lg border border-slate-200 px-4 py-2 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
                        <option value="">Selecione um produto</option>
                    </select>
                    <p id="stock-catalog-status" class="mt-1 text-xs text-slate-500"></p>
                </div>
                <div>
                    <label class="text-sm font-medium text-slate-600" for="stock-size-select">Tamanho</label>
                    <select id="stock-size-select" class="mt-1 w-full rounded-lg border border-slate-200 px-4 py-2 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100" disabled>
                        <option value="">Selecione um produto</option>
                    </select>
                </div>
                <div>
                    <label class="text-sm font-medium text-slate-600" for="stock-action-select">Acao</label>
                    <select id="stock-action-select" class="mt-1 w-full rounded-lg border border-slate-200 px-4 py-2 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
                        <option value="set">Definir valor</option>
                        <option value="increase">Adicionar</option>
                        <option value="decrease">Remover</option>
                    </select>
                </div>
                <div>
                    <label class="text-sm font-medium text-slate-600" for="stock-quantity">Quantidade</label>
                    <input id="stock-quantity" type="number" min="0" step="1" class="mt-1 w-full rounded-lg border border-slate-200 px-4 py-2 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100" value="0">
                </div>
                <div class="md:col-span-4 flex items-center gap-4">
                    <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2 text-white font-medium hover:bg-blue-500 transition">
                        Atualizar estoque
                    </button>
                    <span id="stock-size-feedback" class="text-sm"></span>
                </div>
            </form>
            <div>
                <h3 class="text-sm font-semibold text-slate-600 uppercase tracking-wide mb-3">Tamanhos e quantidades</h3>
                <div class="overflow-x-auto rounded-lg border border-slate-200">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-50 text-left text-slate-600">
                            <tr>
                                <th class="px-4 py-2">Tamanho</th>
                                <th class="px-4 py-2 text-right">Quantidade</th>
                            </tr>
                        </thead>
                        <tbody id="stock-size-table" class="divide-y divide-slate-100">
                            <tr>
                                <td colspan="2" class="px-4 py-3 text-slate-500 text-center">Selecione um produto para visualizar.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
    <div class="rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
            <h2 class="text-lg font-semibold text-slate-700">Relatórios</h2>
            <span class="text-xs uppercase tracking-wide text-slate-400">Exportações</span>
        </div>
        <div class="px-6 py-6 space-y-6">
            <div class="grid gap-6 md:grid-cols-2">
                <div class="space-y-3">
                    <h3 class="text-sm font-semibold text-slate-600 uppercase tracking-wide">Vendas</h3>
                    <p class="text-sm text-slate-500">Gere o relatório consolidado de vendas no período informado.</p>
                    <div class="flex flex-wrap gap-3">
                                                <form class="flex flex-wrap items-center gap-2" onsubmit="return false;">
                            <input type="date" name="from" class="rounded border border-slate-200 px-2 py-1 text-sm">
                            <input type="date" name="to" class="rounded border border-slate-200 px-2 py-1 text-sm">
                                                        <select name="format" class="rounded border border-slate-200 px-2 py-1 text-sm appearance-none bg-white pr-6">
                                <option value="csv">CSV</option>
                                <option value="pdf">PDF</option>
                            </select>
                            <button data-report-export="sales" class="rounded-lg border border-blue-200 px-4 py-2 text-sm font-medium text-blue-600 hover:bg-blue-50 transition">
                                Exportar
                            </button>
                        </form>
                    </div>
                </div>
                <div class="space-y-3">
                    <h3 class="text-sm font-semibold text-slate-600 uppercase tracking-wide">Estoque</h3>
                    <p class="text-sm text-slate-500">Baixe o panorama de estoque total e variantes por produto.</p>
                    <div class="flex flex-wrap gap-3">
                                                <form class="flex flex-wrap items-center gap-2" onsubmit="return false;">
                            <input type="text" name="sku" placeholder="Filtrar por SKU" class="rounded border border-slate-200 px-2 py-1 text-sm">
                            <label class="inline-flex items-center gap-1 text-sm text-slate-600">
                                <input type="checkbox" name="only_active" class="rounded border-slate-300"> Somente ativos
                            </label>
                                                        <select name="format" class="rounded border border-slate-200 px-2 py-1 text-sm appearance-none bg-white pr-6">
                                <option value="csv">CSV</option>
                                <option value="pdf">PDF</option>
                            </select>
                            <button data-report-export="inventory" class="rounded-lg border border-blue-200 px-4 py-2 text-sm font-medium text-blue-600 hover:bg-blue-50 transition">
                                Exportar
                            </button>
                        </form>
                    </div>
                </div>
            </div>
                        <div id="report-feedback" class="text-sm text-slate-500"></div>
        </div>
    </div>

    <div id="admin-commission-section" class="rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
            <h2 class="text-lg font-semibold text-slate-700">Administracao de Comissoes</h2>
            <span class="text-xs uppercase tracking-wide text-slate-400">Financeiro</span>
        </div>
        <div class="px-6 py-6 space-y-6">
            <div class="grid gap-4 md:grid-cols-3">
                <div class="md:col-span-1">
                    <label class="text-sm font-medium text-slate-600" for="admin-commission-vendor">Vendedora</label>
                    <select id="admin-commission-vendor" class="mt-1 w-full rounded-lg border border-slate-200 px-4 py-2 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
                        <option value="">Selecione uma vendedora</option>
                    </select>
                </div>
                <div class="md:col-span-2 flex items-end gap-3">
                    <span id="admin-commission-status" class="text-sm text-slate-500">Escolha uma vendedora para carregar os dados.</span>
                </div>
            </div>

            <div class="grid gap-4 md:grid-cols-3">
                <div class="rounded-lg border border-blue-100 bg-blue-50 p-4 shadow-sm">
                    <p class="text-xs uppercase tracking-wide text-blue-700">Total desde que comecou</p>
                    <p class="mt-2 text-2xl font-semibold text-blue-700">R$ <span data-admin-commission-total="lifetime">0,00</span></p>
                </div>
                <div class="rounded-lg border border-indigo-100 bg-indigo-50 p-4 shadow-sm">
                    <p class="text-xs uppercase tracking-wide text-indigo-700">Total do ultimo mes fechado</p>
                    <p class="mt-2 text-2xl font-semibold text-indigo-700">R$ <span data-admin-commission-total="last-closed">0,00</span></p>
                </div>
                <div class="rounded-lg border border-emerald-100 bg-emerald-50 p-4 shadow-sm">
                    <p class="text-xs uppercase tracking-wide text-emerald-700">Total do mes corrente</p>
                    <p class="mt-2 text-2xl font-semibold text-emerald-700">R$ <span data-admin-commission-total="current">0,00</span></p>
                </div>
            </div>

            <div class="grid gap-3 md:grid-cols-2 text-xs text-slate-500">
                <div>Periodo fechado: <span id="admin-commission-last-closed-range">-</span></div>
                <div>Periodo corrente: <span id="admin-commission-current-range">-</span></div>
            </div>

            <div class="rounded-xl border border-slate-200 bg-white shadow-sm">
                <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
                    <h3 class="text-lg font-semibold text-slate-700">Ultimas vendas</h3>
                </div>
                <div class="overflow-x-auto px-6 py-6">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-50 text-left text-slate-600">
                            <tr>
                                <th class="px-4 py-2">Data</th>
                                <th class="px-4 py-2">Venda</th>
                                <th class="px-4 py-2 text-right">Subtotal</th>
                                <th class="px-4 py-2 text-right">Comissao (2%)</th>
                            </tr>
                        </thead>
                        <tbody id="admin-commission-sales-body" class="divide-y divide-slate-100">
                            <tr>
                                <td colspan="4" class="px-4 py-3 text-slate-500 text-center">Selecione uma vendedora.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="grid gap-4 md:grid-cols-3">
                <div>
                    <label class="text-sm font-medium text-slate-600" for="admin-commission-close-date">Dia para fechar comissao</label>
                    <input id="admin-commission-close-date" type="date" class="mt-1 w-full rounded-lg border border-slate-200 px-4 py-2 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
                    <div id="admin-commission-quick-dates" class="mt-2 flex flex-wrap gap-2 text-xs text-slate-500"></div>
                </div>
                <div class="flex items-end gap-3">
                    <button id="admin-commission-close-btn" type="button" class="rounded-lg bg-emerald-600 px-4 py-2 text-white font-medium hover:bg-emerald-500 transition">
                        Fechar comissao
                    </button>
                    <span id="admin-commission-close-status" class="text-sm text-slate-500"></span>
                </div>
            </div>

            <div class="grid gap-4 md:grid-cols-3">
                <div class="md:col-span-2">
                    <label class="text-sm font-medium text-slate-600" for="admin-commission-receipt">Comprovante de pagamento (PIX)</label>
                    <input id="admin-commission-receipt" type="file" accept="image/*,application/pdf" class="mt-1 w-full rounded-lg border border-slate-200 px-4 py-2 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
                    <p class="mt-1 text-xs text-slate-500">Arquivos salvos em /vendas/financeiro/comissoes</p>
                    <a id="admin-commission-receipt-link" class="mt-2 inline-flex text-xs text-blue-600 hover:text-blue-500 hidden" href="#" target="_blank" rel="noreferrer">Ver comprovante atual</a>
                </div>
                <div class="flex items-end gap-3">
                    <button id="admin-commission-upload" type="button" class="rounded-lg bg-blue-600 px-4 py-2 text-white font-medium hover:bg-blue-500 transition">
                        Enviar comprovante
                    </button>
                    <span id="admin-commission-upload-status" class="text-sm text-slate-500"></span>
                </div>
            </div>
        </div>
    </div>


    <div class="rounded-xl border border-slate-200 bg-white shadow-sm">
                        <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
            <h2 class="text-lg font-semibold text-slate-700">PDV (Admin)</h2>
            <div class="flex items-center gap-3">
                <span class="text-xs uppercase tracking-wide text-slate-400">Vendas com desconto</span>
                <button id="admin-refresh" class="rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50 transition">Atualizar</button>
                <button id="debug-logs" class="rounded-lg border border-amber-300 px-3 py-1.5 text-xs font-medium text-amber-800 bg-amber-50 hover:bg-amber-100 transition">Ver Logs</button>
            </div>
        </div>
        <div class="px-6 py-6 space-y-6">
            <form id="apdv-item-form" class="grid gap-4 md:grid-cols-4">
                                <div class="md:col-span-2">
                    <label class="text-sm font-medium text-slate-600" for="apdv-product-select">Produto</label>
                    <select id="apdv-product-select" name="product_id" class="mt-1 w-full rounded-lg border border-slate-200 px-4 py-2 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
                        <option value="">Selecione um produto</option>
                    </select>
                    <p id="apdv-catalog-status" class="mt-1 text-xs text-slate-500"></p>
                </div>
                <div>
                    <label class="text-sm font-medium text-slate-600" for="apdv-size-select">Tamanho</label>
                    <select id="apdv-size-select" name="size" class="mt-1 w-full rounded-lg border border-slate-200 px-4 py-2 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100" disabled>
                        <option value="">Selecione um produto primeiro</option>
                    </select>
                    <p id="apdv-size-info" class="mt-1 text-xs text-slate-500"></p>
                </div>
                <div>
                    <label class="text-sm font-medium text-slate-600" for="apdv-qty">Quantidade</label>
                    <input id="apdv-qty" name="qty" type="number" min="1" value="1" class="mt-1 w-full rounded-lg border border-slate-200 px-4 py-2 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100" required>
                </div>
                <div class="md:col-span-4 flex justify-end">
                    <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2 text-white font-medium hover:bg-blue-500 transition">
                        Adicionar item
                    </button>
                </div>
            </form>

            <div>
                <h3 class="text-sm font-semibold text-slate-600 uppercase tracking-wide mb-3">Itens da venda</h3>
                <div class="overflow-x-auto rounded-lg border border-slate-200">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-50 text-left text-slate-600">
                            <tr>
                                <th class="px-4 py-2">Produto</th>
                                <th class="px-4 py-2 text-center">Tam.</th>
                                <th class="px-4 py-2 text-right">Qtd.</th>
                                <th class="px-4 py-2 text-right">Preço unidade</th>
                                <th class="px-4 py-2 text-right">Subtotal</th>
                                <th class="px-4 py-2"></th>
                            </tr>
                        </thead>
                        <tbody id="apdv-items-body" class="divide-y divide-slate-100">
                            <tr>
                                <td colspan="6" class="px-4 py-3 text-slate-500 text-center">Nenhum item adicionado.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                            <div class="mt-4 grid gap-1 text-sm text-slate-600">
                    <div class="flex items-center justify-between">
                        <span>Total estimado (sem descontos):</span>
                        <span class="text-lg font-semibold text-slate-800">R$ <span id="apdv-total-amount">0,00</span></span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span>Total com desconto:</span>
                        <span class="text-lg font-semibold text-slate-800">R$ <span id="apdv-total-discount">0,00</span></span>
                    </div>
                </div>
            </div>

            <form id="apdv-form" class="grid gap-4 md:grid-cols-6">
                <div data-apdv-credit="wrap-terminal" class="hidden">
                    <label class="text-sm font-medium text-slate-600" for="apdv-terminal">Maquininha</label>
                    <select id="apdv-terminal" class="mt-1 w-full rounded-lg border border-slate-200 px-4 py-2 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
                        <option value="">Selecione</option>
                    </select>
                </div>
                <div data-apdv-credit="wrap-brand" class="hidden">
                    <label class="text-sm font-medium text-slate-600" for="apdv-brand">Bandeira</label>
                    <select id="apdv-brand" class="mt-1 w-full rounded-lg border border-slate-200 px-4 py-2 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
                        <option value="">Selecione</option>
                    </select>
                </div>
                <div data-apdv-credit="wrap-installments" class="hidden">
                    <label class="text-sm font-medium text-slate-600" for="apdv-installments">Parcelas</label>
                    <select id="apdv-installments" class="mt-1 w-full rounded-lg border border-slate-200 px-4 py-2 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
                        <option value="1">1x (à vista)</option>
                    </select>
                </div>
                <div class="md:col-span-2">
                    <label class="text-sm font-medium text-slate-600" for="apdv-customer">Cliente (opcional)</label>
                    <input id="apdv-customer" name="customer_id" class="mt-1 w-full rounded-lg border border-slate-200 px-4 py-2 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100" placeholder="UUID do cliente (deixe em branco para balcão)">
                </div>
                <div>
                    <label class="text-sm font-medium text-slate-600" for="apdv-method">Método</label>
                    <select id="apdv-method" name="payment_method" class="mt-1 w-full rounded-lg border border-slate-200 px-4 py-2 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100" required>
                        <option value="">Selecione</option>
                        <option value="pix">PIX</option>
                        <option value="credito">Crédito</option>
                        <option value="debito">Débito</option>
                        <option value="dinheiro">Dinheiro</option>
                        <option value="transferencia">Transferência</option>
                    </select>
                </div>
                <div>
                    <label class="text-sm font-medium text-slate-600" for="apdv-discount">Desconto (%)</label>
                    <input id="apdv-discount" name="discount_percent" type="number" min="0" max="100" step="0.1" value="0" class="mt-1 w-full rounded-lg border border-slate-200 px-4 py-2 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
                </div>
                <div class="md:col-span-2 flex items-end gap-3">
                    <button type="submit" class="rounded-lg bg-emerald-600 px-4 py-2 text-white font-medium hover:bg-emerald-500 transition">
                        Finalizar venda
                    </button>
                    <button type="button" id="apdv-reset" class="rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-600 hover:bg-slate-50 transition">
                        Limpar itens
                    </button>
                    <span id="apdv-feedback" class="text-sm text-slate-500"></span>
                </div>
            </form>
        </div>
    </div>

    <div class="rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
            <div>
                <h2 class="text-lg font-semibold text-slate-700">Devolucao</h2>
                <p class="text-xs uppercase tracking-wide text-slate-400">Estorno PIX e reposicao de estoque</p>
            </div>
            <button id="return-focus" class="rounded-lg border border-amber-200 px-3 py-1.5 text-xs font-medium text-amber-700 bg-amber-50 hover:bg-amber-100 transition">
                Botao devolucao
            </button>
        </div>
        <div class="px-6 py-6 space-y-4">
            <form id="return-form" class="grid gap-4 md:grid-cols-6">
                <div class="md:col-span-2">
                    <label class="text-sm font-medium text-slate-600" for="return-product">Produto</label>
                    <select id="return-product" class="mt-1 w-full rounded-lg border border-slate-200 px-4 py-2 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
                        <option value="">Selecione um produto</option>
                    </select>
                </div>
                <div>
                    <label class="text-sm font-medium text-slate-600" for="return-size">Tamanho</label>
                    <select id="return-size" class="mt-1 w-full rounded-lg border border-slate-200 px-4 py-2 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100" disabled>
                        <option value="">Selecione um produto</option>
                    </select>
                </div>
                <div>
                    <label class="text-sm font-medium text-slate-600" for="return-qty">Quantidade</label>
                    <input id="return-qty" type="number" min="1" step="1" value="1" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
                </div>
                <div>
                    <label class="text-sm font-medium text-slate-600" for="return-date">Data da devolucao</label>
                    <input id="return-date" type="date" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
                </div>
                <div>
                    <label class="text-sm font-medium text-slate-600" for="return-amount">Valor estornado (PIX)</label>
                    <input id="return-amount" type="number" step="0.01" min="0" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100" placeholder="0,00">
                </div>
                <div class="md:col-span-2">
                    <label class="text-sm font-medium text-slate-600" for="return-note">Observacao (opcional)</label>
                    <input id="return-note" type="text" maxlength="140" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100" placeholder="Detalhes ou motivo">
                </div>
                <div class="md:col-span-2 flex items-end gap-3">
                    <button type="submit" class="rounded-lg bg-amber-600 px-4 py-2 text-white font-medium hover:bg-amber-500 transition">
                        Registrar devolucao
                    </button>
                    <button type="button" id="return-reset" class="rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-600 hover:bg-slate-50 transition">
                        Limpar
                    </button>
                </div>
            </form>
            <p id="return-feedback" class="text-sm text-slate-500"></p>
        </div>
    </div>

    <div class="rounded-xl border border-slate-200 bg-white shadow-sm">
    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 px-6 py-4">
        <div>
            <h2 class="text-lg font-semibold text-slate-700">Fluxo de Caixa</h2>
            <p class="text-xs uppercase tracking-wide text-slate-400">Resumo rápido</p>
        </div>
    </div>
    <div class="px-6 py-6 space-y-6">
        <form id="cash-filter-form" class="grid gap-4 md:grid-cols-7">
            <div class="md:col-span-2">
                <label class="text-sm font-medium text-slate-600" for="cash-filter-from">De</label>
                <input id="cash-filter-from" name="from" type="date" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
            </div>
            <div class="md:col-span-2">
                <label class="text-sm font-medium text-slate-600" for="cash-filter-to">Até</label>
                <input id="cash-filter-to" name="to" type="date" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
            </div>
            <div>
                <label class="text-sm font-medium text-slate-600" for="cash-filter-method">Método</label>
                <select id="cash-filter-method" name="payment_method" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
                    <option value="">Todos</option>
                    <option value="pix">PIX</option>
                    <option value="credito">Crédito</option>
                    <option value="debito">Débito</option>
                    <option value="dinheiro">Dinheiro</option>
                    <option value="transferencia">Transferência</option>
                </select>
            </div>
            <div class="md:col-span-2 flex items-end gap-3">
                <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2 text-white font-medium hover:bg-blue-500 transition">
                    Aplicar
                </button>
                <button type="button" id="cash-filter-reset" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 transition">
                    Limpar
                </button>
                <button type="button" id="cash-export-csv" class="rounded-lg border border-emerald-300 px-4 py-2 text-sm font-medium text-emerald-700 hover:bg-emerald-50 transition">
                    Exportar CSV
                </button>
            </div>
        </form>

        <div id="cash-overview-status" class="text-sm text-slate-500">Carregando dados do fluxo...</div>

        <div data-admin-cash="cards" class="grid gap-4 md:grid-cols-3 hidden">
            <div class="rounded-lg border border-blue-100 bg-blue-50 p-4 shadow-sm">
                <p class="text-xs uppercase tracking-wide text-blue-700">Recebido bruto</p>
                <p class="mt-2 text-2xl font-semibold text-blue-700">R$ <span data-admin-cash-total="gross">0,00</span></p>
            </div>
            <div class="rounded-lg border border-amber-100 bg-amber-50 p-4 shadow-sm">
                <p class="text-xs uppercase tracking-wide text-amber-700">Taxas</p>
                <p class="mt-2 text-2xl font-semibold text-amber-700">R$ <span data-admin-cash-total="fee">0,00</span></p>
            </div>
            <div class="rounded-lg border border-emerald-100 bg-emerald-50 p-4 shadow-sm">
                <p class="text-xs uppercase tracking-wide text-emerald-700">Líquido</p>
                <p class="mt-2 text-2xl font-semibold text-emerald-700">R$ <span data-admin-cash-total="net">0,00</span></p>
            </div>
        </div>

        <div class="space-y-3">
            <div class="flex items-center justify-between">
                <h3 class="text-sm font-semibold text-slate-600 uppercase tracking-wide">Totais por metodo</h3>
                <span class="text-xs text-slate-400">Valores consolidados</span>
            </div>
            <div class="overflow-x-auto rounded-lg border border-slate-200">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50 text-left text-slate-600">
                        <tr>
                            <th class="px-4 py-2">Método</th>
                            <th class="px-4 py-2 text-right">Movimentos</th>
                            <th class="px-4 py-2 text-right">Bruto</th>
                            <th class="px-4 py-2 text-right">Taxas</th>
                            <th class="px-4 py-2 text-right">Líquido</th>
                        </tr>
                    </thead>
                    <tbody id="cash-method-table" class="divide-y divide-slate-100">
                        <tr>
                            <td colspan="5" class="px-4 py-3 text-slate-500 text-center">Carregando resumo...</td>
                        </tr>
                    </tbody>
                </table>
        </div>
    </div>

    <div class="space-y-3">
        <div class="flex items-center justify-between">
            <h3 class="text-sm font-semibold text-slate-600 uppercase tracking-wide">Movimentações recentes</h3>
            <span class="text-xs text-slate-400">Últimos lançamentos</span>
        </div>
            <div class="overflow-x-auto rounded-lg border border-slate-200">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50 text-left text-slate-600">
                        <tr>
                            <th class="px-4 py-2">Data</th>
                            <th class="px-4 py-2">Método</th>
                            <th class="px-4 py-2 text-right">Bruto</th>
                            <th class="px-4 py-2 text-right">Taxa</th>
                            <th class="px-4 py-2 text-right">Líquido</th>
                            <th class="px-4 py-2">Origem</th>
                        </tr>
                    </thead>
                    <tbody id="cash-ledger-rows" class="divide-y divide-slate-100">
                        <tr>
                            <td colspan="6" class="px-4 py-3 text-slate-500 text-center">Carregando movimentacoes...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="space-y-4 rounded-lg border border-rose-200 bg-rose-50/40 p-6">
        <div class="flex items-center justify-between">
            <h3 class="text-sm font-semibold text-slate-700 uppercase tracking-wide">Registrar saida (despesa)</h3>
            <span class="text-xs text-slate-400">Fluxo de caixa</span>
        </div>
        <form id="cash-expense-form" class="grid gap-4 md:grid-cols-6">
            <div>
                <label class="text-sm font-medium text-slate-600" for="cash-expense-date">Data</label>
                <input id="cash-expense-date" name="date" type="date" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 focus:border-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-100">
            </div>
            <div>
                <label class="text-sm font-medium text-slate-600" for="cash-expense-method">Metodo</label>
                <select id="cash-expense-method" name="payment_method" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 focus:border-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-100" required>
                    <option value="">Selecione</option>
                    <option value="pix">PIX</option>
                    <option value="credito">Credito</option>
                    <option value="debito">Debito</option>
                    <option value="dinheiro">Dinheiro</option>
                    <option value="transferencia">Transferencia</option>
                </select>
            </div>
            <div>
                <label class="text-sm font-medium text-slate-600" for="cash-expense-amount">Valor</label>
                <input id="cash-expense-amount" name="amount" type="number" step="0.01" min="0" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 focus:border-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-100" placeholder="0,00">
            </div>
            <div class="md:col-span-2">
                <label class="text-sm font-medium text-slate-600" for="cash-expense-note">Descricao</label>
                <input id="cash-expense-note" name="note" type="text" maxlength="140" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 focus:border-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-100" placeholder="Ex.: Devolucao, frete, material">
            </div>
            <div class="flex items-end gap-3">
                <button type="submit" class="rounded-lg bg-rose-600 px-4 py-2 text-white font-medium hover:bg-rose-500 transition">
                    Registrar saida
                </button>
                <button type="button" id="cash-expense-reset" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 transition">
                    Limpar
                </button>
            </div>
        </form>
        <p id="cash-expense-feedback" class="text-sm text-slate-500"></p>
    </div>

    <div class="space-y-4 rounded-lg border border-slate-200 bg-white/60 p-6">
        <div class="flex items-center justify-between">
            <h3 class="text-sm font-semibold text-slate-600 uppercase tracking-wide">Registrar ajuste manual</h3>
            <span class="text-xs text-slate-400">Atualiza saldos fora do PDV</span>
        </div>
        <form id="cash-adjust-form" class="grid gap-4 md:grid-cols-6">
            <div>
                <label class="text-sm font-medium text-slate-600" for="cash-adjust-method">Método</label>
                <select id="cash-adjust-method" name="payment_method" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100" required>
                    <option value="">Selecione</option>
                    <option value="pix">PIX</option>
                    <option value="credito">Crédito</option>
                    <option value="debito">Débito</option>
                    <option value="dinheiro">Dinheiro</option>
                    <option value="transferencia">Transferência</option>
                </select>
            </div>
            <div>
                <label class="text-sm font-medium text-slate-600" for="cash-adjust-gross">Valor bruto</label>
                <input id="cash-adjust-gross" name="gross_amount" type="number" step="0.01" min="0" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100" placeholder="0,00" required>
            </div>
            <div>
                <label class="text-sm font-medium text-slate-600" for="cash-adjust-fee">Taxa (opcional)</label>
                <input id="cash-adjust-fee" name="fee_amount" type="number" step="0.01" min="0" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100" placeholder="0,00">
            </div>
            <div class="md:col-span-2">
                <label class="text-sm font-medium text-slate-600" for="cash-adjust-note">Observação</label>
                <input id="cash-adjust-note" name="note" type="text" maxlength="140" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100" placeholder="Detalhes do ajuste (opcional)">
            </div>
            <div class="flex items-end gap-3">
                <button type="submit" class="rounded-lg bg-emerald-600 px-4 py-2 text-white font-medium hover:bg-emerald-500 transition">
                    Registrar
                </button>
                <button type="button" id="cash-adjust-reset" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 transition">
                    Limpar
                </button>
            </div>
        </form>
        <p id="cash-adjust-feedback" class="text-sm text-slate-500"></p>
    </div>

    <div class="rounded-lg border border-slate-200 bg-white/60 p-6">
        <div class="flex items-center justify-between">
            <h3 class="text-sm font-semibold text-slate-600 uppercase tracking-wide">Taxas da maquininha</h3>
            <span class="text-xs text-slate-400">Somente administradores podem editar</span>
        </div>
        <form id="payment-config-form" class="mt-4 grid gap-4 md:grid-cols-6">
            <div>
                <label class="text-sm font-medium text-slate-600" for="payment-config-method">Método</label>
                <select id="payment-config-method" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
                    <option value="credito">Crédito</option>
                    <option value="debito">Débito</option>
                    <option value="pix">PIX</option>
                    <option value="dinheiro">Dinheiro</option>
                    <option value="transferencia">Transferência</option>
                </select>
            </div>
            <div>
                <label class="text-sm font-medium text-slate-600" for="payment-config-fee-percent">Taxa percentual (%)</label>
                <input id="payment-config-fee-percent" type="number" step="0.01" min="0" max="100" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100" placeholder="ex.: 3,49">
            </div>
            <div>
                <label class="text-sm font-medium text-slate-600" for="payment-config-fee-fixed">Taxa fixa (R$)</label>
                <input id="payment-config-fee-fixed" type="number" step="0.01" min="0" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100" placeholder="0,00">
            </div>
            <div class="flex items-center gap-2">
                <input id="payment-config-allow-discount" type="checkbox" class="h-5 w-5 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                <label class="text-sm font-medium text-slate-600" for="payment-config-allow-discount">Permitir desconto</label>
            </div>
            <div>
                <label class="text-sm font-medium text-slate-600" for="payment-config-max-discount">Desconto máximo (%)</label>
                <input id="payment-config-max-discount" type="number" step="0.1" min="0" max="100" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100" placeholder="ex.: 10">
            </div>
            <div class="flex items-end">
                <button type="submit" class="w-full rounded-lg bg-blue-600 px-4 py-2 text-white font-medium hover:bg-blue-500 transition">
                    Salvar taxas
                </button>
            </div>
        </form>
        <p id="payment-config-feedback" class="mt-2 text-sm text-slate-500"></p>
    </div>

    <div class="rounded-lg border border-slate-200 bg-white/60 p-6">
        <div class="flex items-center justify-between">
            <h3 class="text-sm font-semibold text-slate-600 uppercase tracking-wide">Bandeiras e Maquininhas</h3>
            <span class="text-xs text-slate-400">Gerencie catálogos</span>
        </div>
        <div class="mt-4 grid gap-6 md:grid-cols-2">
            <div class="space-y-3">
                <h4 class="text-sm font-semibold text-slate-700">Bandeiras</h4>
                <form id="brand-form" class="grid gap-2 md:grid-cols-6" onsubmit="return false;">
                    <input type="hidden" id="brand-id">
                    <div class="md:col-span-3">
                        <label for="brand-name" class="text-sm text-slate-600">Nome</label>
                        <input id="brand-name" type="text" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2" placeholder="ex.: Visa">
                    </div>
                    <div class="md:col-span-2 flex items-center gap-2">
                        <input id="brand-active" type="checkbox" class="h-5 w-5 rounded border-slate-300 text-emerald-600" checked>
                        <label for="brand-active" class="text-sm text-slate-600">Ativa</label>
                    </div>
                    <div class="flex items-end">
                        <button id="brand-save" class="w-full rounded-lg bg-blue-600 px-3 py-2 text-white">Salvar</button>
                    </div>
                </form>
                <p id="brand-feedback" class="text-sm text-slate-500"></p>
                <div class="overflow-x-auto rounded border border-slate-200">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-50 text-left text-slate-600">
                            <tr>
                                <th class="px-3 py-2">Nome</th>
                                <th class="px-3 py-2">Status</th>
                                <th class="px-3 py-2 text-right">Ações</th>
                            </tr>
                        </thead>
                        <tbody id="brand-table" class="divide-y divide-slate-100">
                            <tr><td colspan="3" class="px-3 py-3 text-slate-500 text-center">Carregando...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="space-y-3">
                <h4 class="text-sm font-semibold text-slate-700">Maquininhas</h4>
                <form id="terminal-form" class="grid gap-2 md:grid-cols-6" onsubmit="return false;">
                    <input type="hidden" id="terminal-id">
                    <div class="md:col-span-3">
                        <label for="terminal-name" class="text-sm text-slate-600">Nome</label>
                        <input id="terminal-name" type="text" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2" placeholder="ex.: Stone">
                    </div>
                    <div class="md:col-span-2 flex items-center gap-2">
                        <input id="terminal-active" type="checkbox" class="h-5 w-5 rounded border-slate-300 text-emerald-600" checked>
                        <label for="terminal-active" class="text-sm text-slate-600">Ativa</label>
                    </div>
                    <div class="flex items-end">
                        <button id="terminal-save" class="w-full rounded-lg bg-blue-600 px-3 py-2 text-white">Salvar</button>
                    </div>
                </form>
                <p id="terminal-feedback" class="text-sm text-slate-500"></p>
                <div class="overflow-x-auto rounded border border-slate-200">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-50 text-left text-slate-600">
                            <tr>
                                <th class="px-3 py-2">Nome</th>
                                <th class="px-3 py-2">Status</th>
                                <th class="px-3 py-2 text-right">Ações</th>
                            </tr>
                        </thead>
                        <tbody id="terminal-table" class="divide-y divide-slate-100">
                            <tr><td colspan="3" class="px-3 py-3 text-slate-500 text-center">Carregando...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="rounded-lg border border-slate-200 bg-white/60 p-6">
        <div class="flex items-center justify-between">
            <h3 class="text-sm font-semibold text-slate-600 uppercase tracking-wide">Taxas por combinação</h3>
            <span class="text-xs text-slate-400">Maquininha + Bandeira + Método</span>
        </div>
        <form id="fee-form" class="mt-4 grid gap-4 md:grid-cols-12" onsubmit="return false;">
            <div class="md:col-span-2">
                <label for="fee-terminal" class="text-sm text-slate-600">Maquininha</label>
                <select id="fee-terminal" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2"></select>
            </div>
            <div class="md:col-span-2">
                <label for="fee-brand" class="text-sm text-slate-600">Bandeira</label>
                <select id="fee-brand" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2"></select>
            </div>
            <div>
                <label for="fee-method" class="text-sm text-slate-600">Método</label>
                <select id="fee-method" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2">
                    <option value="credito">Crédito</option>
                    <option value="debito">Débito</option>
                    <option value="pix">PIX</option>
                    <option value="dinheiro">Dinheiro</option>
                    <option value="transferencia">Transferência</option>
                </select>
            </div>
            <div>
                <label for="fee-percent" class="text-sm text-slate-600">Percentual (%)</label>
                <input id="fee-percent" type="number" min="0" max="100" step="0.01" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2" placeholder="ex.: 3,49">
            </div>
            <div>
                <label for="fee-fixed" class="text-sm text-slate-600">Fixa (R$)</label>
                <input id="fee-fixed" type="number" min="0" step="0.01" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2" placeholder="0,00">
            </div>
            <div class="md:col-span-2">
                <label for="fee-installments-min" class="text-sm text-slate-600">Parcelas mín.</label>
                <input id="fee-installments-min" type="number" min="1" step="1" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2" value="1">
            </div>
            <div class="md:col-span-2">
                <label for="fee-installments-max" class="text-sm text-slate-600">Parcelas máx.</label>
                <input id="fee-installments-max" type="number" min="1" step="1" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2" value="1">
            </div>
            <div class="md:col-span-2">
                <label for="fee-per-installment" class="text-sm text-slate-600">% por parcela</label>
                <input id="fee-per-installment" type="number" min="0" max="100" step="0.01" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2" placeholder="ex.: 1,39">
            </div>
            <div class="md:col-span-2">
                <label for="fee-confirmation-fixed" class="text-sm text-slate-600">Tarifa conf. (R$)</label>
                <input id="fee-confirmation-fixed" type="number" min="0" step="0.01" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2" placeholder="ex.: 0,35">
            </div>
            <div class="md:col-span-2 flex items-end">
                <button id="fee-save" class="w-full rounded-lg bg-blue-600 px-4 py-2 text-white">Salvar</button>
            </div>
        </form>
        <p id="fee-feedback" class="mt-2 text-sm text-slate-500"></p>
        <div class="mt-3 overflow-x-auto rounded border border-slate-200">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-slate-600">
                    <tr>
                        <th class="px-3 py-2">Método</th>
                        <th class="px-3 py-2">Parcelas</th>
                        <th class="px-3 py-2 text-right">% / % por parc. / Fixa</th>
                        <th class="px-3 py-2 text-right">Tarifa conf.</th>
                        <th class="px-3 py-2 text-right">Ações</th>
                    </tr>
                </thead>
                <tbody id="fee-table" class="divide-y divide-slate-100">
                    <tr><td colspan="5" class="px-3 py-3 text-slate-500 text-center">Selecione maquininha e bandeira.</td></tr>
                </tbody>
            </table>
        </div>
    </div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // Verifica token; se ausente, redireciona para login
    try {
        const token = typeof getStoredValue === 'function' ? getStoredValue('access_token') : null;
        if (!token) {
            const warn = document.createElement('div');
            warn.className = 'max-w-6xl mx-auto px-6 py-3 text-sm text-amber-700 bg-amber-50 border border-amber-200 rounded mt-4';
            warn.textContent = 'Sessão expirada. Redirecionando para login...';
            document.body.prepend(warn);
            setTimeout(() => { window.location.href = '/'; }, 800);
            return;
        }
    } catch {}

    const grossRevenue = document.querySelector('#gross-revenue');
    const estimatedProfit = document.querySelector('#estimated-profit');
    const lowStockCount = document.querySelector('#low-stock-count');
    const productForm = document.querySelector('#product-form');
    const productFeedback = document.querySelector('#product-feedback');

    const cashFilterForm = document.querySelector('#cash-filter-form');
    const cashFilterReset = document.querySelector('#cash-filter-reset');
    const cashFilterFrom = document.querySelector('#cash-filter-from');
    const cashFilterTo = document.querySelector('#cash-filter-to');
    const cashFilterMethod = document.querySelector('#cash-filter-method');
    const cashSummaryStatus = document.querySelector('#cash-overview-status');
    const cashSummaryCards = document.querySelector('[data-admin-cash="cards"]');
    const cashTotals = {
        gross: document.querySelector('[data-admin-cash-total="gross"]'),
        fee: document.querySelector('[data-admin-cash-total="fee"]'),
        net: document.querySelector('[data-admin-cash-total="net"]'),
    };
    const cashMethodTable = document.querySelector('#cash-method-table');
    const cashLedgerRows = document.querySelector('#cash-ledger-rows');
    const cashAdjustForm = document.querySelector('#cash-adjust-form');
    const cashAdjustReset = document.querySelector('#cash-adjust-reset');
    const cashAdjustFeedback = document.querySelector('#cash-adjust-feedback');
    const cashExpenseForm = document.querySelector('#cash-expense-form');
    const cashExpenseDateInput = document.querySelector('#cash-expense-date');
    const cashExpenseReset = document.querySelector('#cash-expense-reset');
    const cashExpenseFeedback = document.querySelector('#cash-expense-feedback');
    const cashExportButton = document.querySelector('#cash-export-csv');
    const paymentConfigForm = document.querySelector('#payment-config-form');
    const paymentConfigMethod = document.querySelector('#payment-config-method');
    const paymentConfigFeePercent = document.querySelector('#payment-config-fee-percent');
    const paymentConfigFeeFixed = document.querySelector('#payment-config-fee-fixed');
    const paymentConfigAllowDiscount = document.querySelector('#payment-config-allow-discount');
    const paymentConfigMaxDiscount = document.querySelector('#payment-config-max-discount');
    const paymentConfigFeedback = document.querySelector('#payment-config-feedback');
    const returnForm = document.querySelector('#return-form');
    const returnProductSelect = document.querySelector('#return-product');
    const returnSizeSelect = document.querySelector('#return-size');
    const returnQtyInput = document.querySelector('#return-qty');
    const returnDateInput = document.querySelector('#return-date');
    const returnAmountInput = document.querySelector('#return-amount');
    const returnNoteInput = document.querySelector('#return-note');
    const returnFeedback = document.querySelector('#return-feedback');
    const returnReset = document.querySelector('#return-reset');
    const returnFocusBtn = document.querySelector('#return-focus');
    const adminCommissionSection = document.querySelector('#admin-commission-section');
    const adminCommissionVendorSelect = document.querySelector('#admin-commission-vendor');
    const adminCommissionStatus = document.querySelector('#admin-commission-status');
    const adminCommissionCloseDateInput = document.querySelector('#admin-commission-close-date');
    const adminCommissionQuickDates = document.querySelector('#admin-commission-quick-dates');
    const adminCommissionCloseButton = document.querySelector('#admin-commission-close-btn');
    const adminCommissionCloseStatus = document.querySelector('#admin-commission-close-status');
    const adminCommissionUploadButton = document.querySelector('#admin-commission-upload');
    const adminCommissionUploadStatus = document.querySelector('#admin-commission-upload-status');
    const adminCommissionReceiptInput = document.querySelector('#admin-commission-receipt');
    const adminCommissionReceiptLink = document.querySelector('#admin-commission-receipt-link');
    const adminCommissionSalesBody = document.querySelector('#admin-commission-sales-body');
    const adminCommissionLastClosedRange = document.querySelector('#admin-commission-last-closed-range');
    const adminCommissionCurrentRange = document.querySelector('#admin-commission-current-range');
    const adminCommissionTotals = {
        lifetime: document.querySelector('[data-admin-commission-total="lifetime"]'),
        lastClosed: document.querySelector('[data-admin-commission-total="last-closed"]'),
        current: document.querySelector('[data-admin-commission-total="current"]'),
    };

    // Pagamentos: bandeiras / maquininhas / taxas
    const brandForm = document.querySelector('#brand-form');
    const brandIdInput = document.querySelector('#brand-id');
    const brandNameInput = document.querySelector('#brand-name');
    const brandActiveInput = document.querySelector('#brand-active');
    const brandTable = document.querySelector('#brand-table');
    const brandFeedback = document.querySelector('#brand-feedback');

    const terminalForm = document.querySelector('#terminal-form');
    const terminalIdInput = document.querySelector('#terminal-id');
    const terminalNameInput = document.querySelector('#terminal-name');
    const terminalActiveInput = document.querySelector('#terminal-active');
    const terminalTable = document.querySelector('#terminal-table');
    const terminalFeedback = document.querySelector('#terminal-feedback');

    const feeForm = document.querySelector('#fee-form');
    const feeTerminalSelect = document.querySelector('#fee-terminal');
    const feeBrandSelect = document.querySelector('#fee-brand');
    const feeMethodSelect = document.querySelector('#fee-method');
    const feePercentInput = document.querySelector('#fee-percent');
    const feeFixedInput = document.querySelector('#fee-fixed');
    const feeInstallmentsMin = document.querySelector('#fee-installments-min');
    const feeInstallmentsMax = document.querySelector('#fee-installments-max');
    const feePerInstallmentInput = document.querySelector('#fee-per-installment');
    const feeConfirmationFixedInput = document.querySelector('#fee-confirmation-fixed');
    const feeTable = document.querySelector('#fee-table');
    const feeFeedback = document.querySelector('#fee-feedback');

    const stockForm = document.querySelector('#stock-size-form');
    const stockProductSelect = document.querySelector('#stock-product-select');
    const stockSizeSelect = document.querySelector('#stock-size-select');
    const stockActionSelect = document.querySelector('#stock-action-select');
    const stockQuantityInput = document.querySelector('#stock-quantity');
    const stockTableBody = document.querySelector('#stock-size-table');
    const stockFeedback = document.querySelector('#stock-size-feedback');
    const reportFeedback = document.querySelector('#report-feedback');
    const reportButtons = document.querySelectorAll('[data-report-export]');

    let stockProducts = [];
    let stockSizes = [];
    let paymentConfigs = {};
    let brands = [];
    let terminals = [];
    let currentFeeId = null;

    renderPaymentConfig(paymentConfigMethod?.value ?? 'credito');
    const currencyOptions = { minimumFractionDigits: 2 };

    function formatCurrency(value) {
        return Number(value ?? 0).toLocaleString('pt-BR', currencyOptions);
    }

    function setAdminCommissionStatus(target, message, tone = 'info') {
        if (!target) return;
        const color = tone === 'success' ? 'text-emerald-600' : tone === 'error' ? 'text-rose-600' : 'text-slate-500';
        target.className = `text-sm ${color}`;
        target.textContent = message || '';
    }

    function updateAdminCommissionReceiptLink(path) {
        if (!adminCommissionReceiptLink) return;
        if (!path) {
            adminCommissionReceiptLink.classList.add('hidden');
            adminCommissionReceiptLink.setAttribute('href', '#');
            return;
        }
        const base = window.__BASE_PATH__ || '';
        adminCommissionReceiptLink.setAttribute('href', `${base}${path}`);
        adminCommissionReceiptLink.classList.remove('hidden');
    }

    function formatDateLabel(value) {
        if (!value) return '-';
        const parsed = new Date(value);
        return Number.isNaN(parsed.getTime()) ? value : parsed.toLocaleDateString('pt-BR');
    }

    function applyAdminCommissionOverview(data) {
        if (adminCommissionTotals.lifetime) {
            adminCommissionTotals.lifetime.textContent = formatCurrency(data.lifetime_total ?? 0);
        }
        if (adminCommissionTotals.lastClosed) {
            adminCommissionTotals.lastClosed.textContent = formatCurrency(data.last_closed_total ?? 0);
        }
        if (adminCommissionTotals.current) {
            adminCommissionTotals.current.textContent = formatCurrency(data.current_total ?? 0);
        }

        if (adminCommissionLastClosedRange) {
            const start = formatDateLabel(data.last_closed_period_start);
            const end = formatDateLabel(data.last_closed_period_end);
            adminCommissionLastClosedRange.textContent = (data.last_closed_period_start && data.last_closed_period_end)
                ? `${start} - ${end}`
                : 'Nenhum fechamento ainda';
        }

        if (adminCommissionCurrentRange) {
            const start = formatDateLabel(data.current_period_start);
            const end = formatDateLabel(data.current_period_end);
            adminCommissionCurrentRange.textContent = `${start} - ${end}`;
        }

        if (adminCommissionSalesBody) {
            const sales = Array.isArray(data.recent_sales) ? data.recent_sales : [];
            if (sales.length === 0) {
                adminCommissionSalesBody.innerHTML = '<tr><td colspan="4" class="px-4 py-3 text-slate-500 text-center">Nenhuma venda encontrada.</td></tr>';
            } else {
                adminCommissionSalesBody.innerHTML = '';
                sales.forEach((sale) => {
                    const when = new Date(sale.created_at ?? Date.now()).toLocaleString('pt-BR');
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td class="px-4 py-2">${when}</td>
                        <td class="px-4 py-2">${sale.id ?? ''}</td>
                        <td class="px-4 py-2 text-right">R$ ${formatCurrency(sale.subtotal ?? 0)}</td>
                        <td class="px-4 py-2 text-right">R$ ${formatCurrency(sale.commission ?? 0)}</td>
                    `;
                    adminCommissionSalesBody.appendChild(row);
                });
            }
        }

        if (adminCommissionCloseDateInput && !adminCommissionCloseDateInput.value) {
            adminCommissionCloseDateInput.value = new Date().toISOString().slice(0, 10);
        }

        if (adminCommissionStatus) {
            setAdminCommissionStatus(adminCommissionStatus, 'Dados atualizados.', 'info');
        }

        updateAdminCommissionReceiptLink(data.receipt_path ?? null);
    }

    function renderQuickCloseDates() {
        if (!adminCommissionQuickDates) return;
        adminCommissionQuickDates.innerHTML = '';

        const today = new Date();
        const dates = [];
        for (let i = 0; i < 5; i += 1) {
            const d = new Date(today);
            d.setDate(today.getDate() - i);
            dates.push(d);
        }

        const monthEnd = new Date(today.getFullYear(), today.getMonth() + 1, 0);
        if (!dates.some((d) => d.toDateString() === monthEnd.toDateString())) {
            dates.push(monthEnd);
        }

        dates.forEach((date) => {
            const iso = date.toISOString().slice(0, 10);
            const label = date.toLocaleDateString('pt-BR');
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.dataset.date = iso;
            btn.className = 'rounded border border-slate-200 px-2 py-1 hover:bg-slate-50';
            btn.textContent = label;
            adminCommissionQuickDates.appendChild(btn);
        });
    }

    async function loadAdminCommissionVendors() {
        if (!adminCommissionVendorSelect) return;
        adminCommissionVendorSelect.innerHTML = '<option value="">Carregando vendedoras...</option>';
        try {
            const response = await window.apiFetch('/commission/admin/vendors');
            const data = await response.json().catch(() => ({ vendors: [] }));
            if (!response.ok) {
                throw new Error(data?.error?.message || 'Falha ao carregar vendedoras.');
            }

            const vendors = Array.isArray(data.vendors) ? data.vendors : [];
            if (vendors.length === 0) {
                adminCommissionVendorSelect.innerHTML = '<option value="">Nenhuma vendedora encontrada</option>';
                return;
            }

            adminCommissionVendorSelect.innerHTML = '<option value="">Selecione uma vendedora</option>';
            vendors.forEach((vendor) => {
                const opt = document.createElement('option');
                opt.value = vendor.id;
                opt.textContent = vendor.name || vendor.email || vendor.id;
                adminCommissionVendorSelect.appendChild(opt);
            });

            const saved = localStorage.getItem('admin_commission_vendor_id');
            if (saved && vendors.some((v) => v.id === saved)) {
                adminCommissionVendorSelect.value = saved;
                loadAdminCommissionOverview(saved);
            }
        } catch (error) {
            adminCommissionVendorSelect.innerHTML = '<option value="">Erro ao carregar vendedoras</option>';
            setAdminCommissionStatus(adminCommissionStatus, 'Erro ao carregar vendedoras.', 'error');
        }
    }

    async function loadAdminCommissionOverview(vendorId) {
        if (!vendorId) {
            return;
        }
        setAdminCommissionStatus(adminCommissionStatus, 'Carregando comissoes...', 'info');
        try {
            const response = await window.apiFetch(`/commission/admin/overview?vendor_id=${encodeURIComponent(vendorId)}`);
            const data = await response.json().catch(() => ({}));
            if (!response.ok) {
                throw new Error(data?.error?.message || 'Falha ao carregar resumo.');
            }
            applyAdminCommissionOverview(data);
        } catch (error) {
            setAdminCommissionStatus(adminCommissionStatus, 'Erro ao carregar resumo.', 'error');
        }
    }

    if (adminCommissionCloseDateInput && !adminCommissionCloseDateInput.value) {
        adminCommissionCloseDateInput.value = new Date().toISOString().slice(0, 10);
    }

    renderQuickCloseDates();

    if (adminCommissionVendorSelect) {
        adminCommissionVendorSelect.addEventListener('change', () => {
            const vendorId = adminCommissionVendorSelect.value;
            localStorage.setItem('admin_commission_vendor_id', vendorId);
            loadAdminCommissionOverview(vendorId);
        });
    }

    adminCommissionQuickDates?.addEventListener('click', (event) => {
        const button = event.target instanceof HTMLButtonElement ? event.target : null;
        if (!button || !button.dataset.date) return;
        if (adminCommissionCloseDateInput) {
            adminCommissionCloseDateInput.value = button.dataset.date;
        }
    });

    adminCommissionCloseButton?.addEventListener('click', async () => {
        const vendorId = adminCommissionVendorSelect?.value || '';
        if (!vendorId) {
            setAdminCommissionStatus(adminCommissionCloseStatus, 'Selecione a vendedora.', 'error');
            return;
        }
        const closingDate = adminCommissionCloseDateInput?.value || new Date().toISOString().slice(0, 10);
        setAdminCommissionStatus(adminCommissionCloseStatus, 'Fechando comissao...', 'info');
        try {
            const response = await window.apiFetch('/commission/admin/confirm', {
                method: 'POST',
                body: JSON.stringify({
                    vendor_id: vendorId,
                    closing_date: closingDate,
                }),
            });
            const data = await response.json().catch(() => ({}));
            if (!response.ok) {
                throw new Error(data?.error?.message || 'Falha ao confirmar.');
            }
            setAdminCommissionStatus(adminCommissionCloseStatus, 'Comissao fechada.', 'success');
            await loadAdminCommissionOverview(vendorId);
        } catch (error) {
            setAdminCommissionStatus(
                adminCommissionCloseStatus,
                error?.message || 'Erro ao fechar comissao.',
                'error'
            );
        }
    });

    adminCommissionUploadButton?.addEventListener('click', async () => {
        const vendorId = adminCommissionVendorSelect?.value || '';
        if (!vendorId) {
            setAdminCommissionStatus(adminCommissionUploadStatus, 'Selecione a vendedora.', 'error');
            return;
        }

        const file = adminCommissionReceiptInput?.files?.[0] ?? null;
        if (!file) {
            setAdminCommissionStatus(adminCommissionUploadStatus, 'Selecione o comprovante.', 'error');
            return;
        }

        const closingDate = adminCommissionCloseDateInput?.value || new Date().toISOString().slice(0, 10);
        setAdminCommissionStatus(adminCommissionUploadStatus, 'Enviando comprovante...', 'info');

        const formData = new FormData();
        formData.append('vendor_id', vendorId);
        formData.append('closing_date', closingDate);
        formData.append('receipt', file);

        try {
            const response = await window.apiFetch('/commission/admin/upload', {
                method: 'POST',
                body: formData,
            });
            const data = await response.json().catch(() => ({}));
            if (!response.ok) {
                throw new Error(data?.error?.message || 'Falha ao enviar comprovante.');
            }
            updateAdminCommissionReceiptLink(data.receipt_path ?? null);
            if (data.email_warning) {
                setAdminCommissionStatus(adminCommissionUploadStatus, data.email_warning, 'error');
            } else {
                setAdminCommissionStatus(adminCommissionUploadStatus, 'Comprovante enviado.', 'success');
            }
        } catch (error) {
            setAdminCommissionStatus(adminCommissionUploadStatus, 'Erro ao enviar comprovante.', 'error');
        }
    });

    try {
        const section = new URLSearchParams(window.location.search).get('section');
        if (section === 'comissao' && adminCommissionSection) {
            adminCommissionSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    } catch {}

    function getDefaultCashRange() {
        const today = new Date();
        return {
            from: new Date(today.getFullYear(), today.getMonth(), 1).toISOString().slice(0, 10),
            to: today.toISOString().slice(0, 10),
        };
    }

    if (cashFilterForm) {
        const defaults = getDefaultCashRange();

        if (cashFilterFrom && !cashFilterFrom.value) {
            cashFilterFrom.value = defaults.from;
        }

        if (cashFilterTo && !cashFilterTo.value) {
            cashFilterTo.value = defaults.to;
        }
    }

    ensureReturnDate();
    ensureCashExpenseDate();

    async function loadAdminDashboard() {
        try {
            const response = await window.apiFetch('/dashboard/admin');
            if (!response.ok) {
                return;
            }

            const data = await response.json();
            if (!grossRevenue || !estimatedProfit || !lowStockCount) {
                return;
            }
            grossRevenue.textContent = Number(data.gross_revenue ?? 0).toLocaleString('pt-BR', { minimumFractionDigits: 2 });
            estimatedProfit.textContent = Number(data.estimated_profit ?? 0).toLocaleString('pt-BR', { minimumFractionDigits: 2 });
            lowStockCount.textContent = Array.isArray(data.low_stock) ? data.low_stock.length : 0;

            const tableBody = document.querySelector('#low-stock-table');
            if (!tableBody) {
                return;
            }

            tableBody.innerHTML = '';
            const lowStock = Array.isArray(data.low_stock) ? data.low_stock : [];
            if (lowStock.length === 0) {
                tableBody.innerHTML = '<tr><td class="px-6 py-4 text-slate-500" colspan="2">Sem produtos em alerta.</td></tr>';
                return;
            }

            lowStock.forEach((entry) => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td class="px-6 py-4 font-medium text-slate-700">${entry.sku ?? ''}</td>
                    <td class="px-6 py-4 text-slate-600">${entry.stock ?? 0}</td>
                `;
                tableBody.appendChild(row);
            });
        } catch (error) {
            console.error('Falha ao carregar dashboard admin', error);
        }
    }

    function setCashStatus(message, tone = 'info') {
        if (!cashSummaryStatus) {
            return;
        }

        const classes = {
            error: 'text-sm text-rose-600',
            info: 'text-sm text-slate-500',
            success: 'text-sm text-emerald-600',
        };

        cashSummaryStatus.textContent = message;
        cashSummaryStatus.className = classes[tone] ?? classes.info;
    }

    function setCashAdjustFeedback(message, tone = 'info') {
        if (!cashAdjustFeedback) {
            return;
        }

        const classes = {
            error: 'text-sm text-rose-600',
            success: 'text-sm text-emerald-600',
            info: 'text-sm text-slate-500',
        };

        cashAdjustFeedback.textContent = message;
        cashAdjustFeedback.className = classes[tone] ?? classes.info;
    }

    function setCashExpenseFeedback(message, tone = 'info') {
        if (!cashExpenseFeedback) {
            return;
        }

        const classes = {
            error: 'text-sm text-rose-600',
            success: 'text-sm text-emerald-600',
            info: 'text-sm text-slate-500',
        };

        cashExpenseFeedback.textContent = message;
        cashExpenseFeedback.className = classes[tone] ?? classes.info;
    }

    function setPaymentConfigFeedback(message, tone = 'info') {
        if (!paymentConfigFeedback) {
            return;
        }

        const classes = {
            error: 'text-sm text-rose-600',
            success: 'text-sm text-emerald-600',
            info: 'text-sm text-slate-500',
        };

        paymentConfigFeedback.textContent = message;
        paymentConfigFeedback.className = classes[tone] ?? classes.info;
    }

    function setBrandFeedback(message, tone = 'info') {
        if (!brandFeedback) return;
        const classes = { error: 'text-sm text-rose-600', success: 'text-sm text-emerald-600', info: 'text-sm text-slate-500' };
        brandFeedback.textContent = message; brandFeedback.className = classes[tone] ?? classes.info;
    }

    function setTerminalFeedback(message, tone = 'info') {
        if (!terminalFeedback) return;
        const classes = { error: 'text-sm text-rose-600', success: 'text-sm text-emerald-600', info: 'text-sm text-slate-500' };
        terminalFeedback.textContent = message; terminalFeedback.className = classes[tone] ?? classes.info;
    }

    function setFeeFeedback(message, tone = 'info') {
        if (!feeFeedback) return;
        const classes = { error: 'text-sm text-rose-600', success: 'text-sm text-emerald-600', info: 'text-sm text-slate-500' };
        feeFeedback.textContent = message; feeFeedback.className = classes[tone] ?? classes.info;
    }

    function renderPaymentConfig(method) {
        if (!paymentConfigForm) {
            return;
        }

        const selectedMethod = method ?? (paymentConfigMethod?.value ?? 'credito');
        if (paymentConfigMethod && paymentConfigMethod.value !== selectedMethod) {
            paymentConfigMethod.value = selectedMethod;
        }

        const config = paymentConfigs[selectedMethod] ?? null;
        const percent = config !== null ? Number(config.fee_percentage ?? 0) : 0;
        const feeFixed = config !== null ? Number(config.fee_fixed ?? 0) : 0;
        const maxDiscount = config !== null ? Number(config.max_discount_percentage ?? 0) : 0;

        if (paymentConfigFeePercent) {
            paymentConfigFeePercent.value = config !== null ? percent.toString() : '0';
        }

        if (paymentConfigFeeFixed) {
            paymentConfigFeeFixed.value = config !== null ? feeFixed.toFixed(2) : '0.00';
        }

        const allow = config !== null ? Boolean(config.allow_discount ?? false) : true;
        if (paymentConfigAllowDiscount) {
            paymentConfigAllowDiscount.checked = allow;
        }

        if (paymentConfigMaxDiscount) {
            paymentConfigMaxDiscount.disabled = !allow;
            paymentConfigMaxDiscount.value = config !== null ? maxDiscount.toString() : (allow ? '10' : '0');
        }

        setPaymentConfigFeedback('', 'info');
    }

    async function loadPaymentConfigs() {
        if (!paymentConfigForm) {
            return;
        }

        try {
            const response = await window.apiFetch('/payment-config');
            if (!response.ok) {
                throw new Error('Resposta inválida ao buscar taxas');
            }

            const result = await response.json().catch(() => ({}));
            const items = Array.isArray(result.items) ? result.items : [];

            paymentConfigs = {};
            items.forEach((item) => {
                const method = typeof item?.payment_method === 'string' ? item.payment_method : null;
                if (method) {
                    paymentConfigs[method] = item;
                }
            });

            const currentMethod = paymentConfigMethod?.value ?? (items[0]?.payment_method ?? 'credito');
            renderPaymentConfig(currentMethod);
        } catch (error) {
            console.error('Falha ao carregar configurações de pagamento', error);
            setPaymentConfigFeedback('Não foi possível carregar as taxas agora.', 'error');
        }
    }

    async function loadBrands() {
        if (!brandTable) return;
        try {
            const resp = await window.apiFetch('/payment-catalog/brands');
            const data = await resp.json().catch(() => ({ items: [] }));
            brands = Array.isArray(data.items) ? data.items : [];
            // tabela
            brandTable.innerHTML = '';
            if (brands.length === 0) {
                brandTable.innerHTML = '<tr><td colspan="3" class="px-3 py-3 text-slate-500 text-center">Nenhuma bandeira cadastrada.</td></tr>';
            } else {
                brands.forEach((b) => {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td class="px-3 py-2">${b.name}</td>
                        <td class="px-3 py-2">${b.active ? 'Ativa' : 'Inativa'}</td>
                        <td class="px-3 py-2 text-right"><button class="text-blue-600" data-edit-brand="${b.id}">Editar</button></td>
                    `;
                    brandTable.appendChild(tr);
                });
            }
            // select de taxa
            if (feeBrandSelect) {
                feeBrandSelect.innerHTML = '<option value="">Selecione</option>';
                brands.filter(b => b.active).forEach((b) => {
                    const opt = document.createElement('option');
                    opt.value = b.id; opt.textContent = b.name; feeBrandSelect.appendChild(opt);
                });
            }
        } catch {
            brandTable.innerHTML = '<tr><td colspan="3" class="px-3 py-3 text-rose-600 text-center">Erro ao carregar bandeiras.</td></tr>';
        }
    }

    async function loadTerminals() {
        if (!terminalTable) return;
        try {
            const resp = await window.apiFetch('/payment-catalog/terminals');
            const data = await resp.json().catch(() => ({ items: [] }));
            terminals = Array.isArray(data.items) ? data.items : [];
            // tabela
            terminalTable.innerHTML = '';
            if (terminals.length === 0) {
                terminalTable.innerHTML = '<tr><td colspan="3" class="px-3 py-3 text-slate-500 text-center">Nenhuma maquininha cadastrada.</td></tr>';
            } else {
                terminals.forEach((t) => {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td class="px-3 py-2">${t.name}</td>
                        <td class="px-3 py-2">${t.active ? 'Ativa' : 'Inativa'}</td>
                        <td class="px-3 py-2 text-right"><button class="text-blue-600" data-edit-terminal="${t.id}">Editar</button></td>
                    `;
                    terminalTable.appendChild(tr);
                });
            }
            // select de taxa
            if (feeTerminalSelect) {
                feeTerminalSelect.innerHTML = '<option value="">Selecione</option>';
                terminals.filter(t => t.active).forEach((t) => {
                    const opt = document.createElement('option'); opt.value = t.id; opt.textContent = t.name; feeTerminalSelect.appendChild(opt);
                });
            }
        } catch {
            terminalTable.innerHTML = '<tr><td colspan="3" class="px-3 py-3 text-rose-600 text-center">Erro ao carregar maquininhas.</td></tr>';
        }
    }

    async function submitBrand() {
        const name = brandNameInput?.value?.trim() || '';
        const active = brandActiveInput?.checked ?? true;
        if (!name) { setBrandFeedback('Informe um nome.', 'error'); return; }
        setBrandFeedback('Salvando...', 'info');
        const payload = { id: brandIdInput?.value || null, name, active };
        try {
            const resp = await window.apiFetch('/payment-catalog/brands', { method: 'POST', body: JSON.stringify(payload) });
            const result = await resp.json().catch(()=>({}));
            if (!resp.ok) { setBrandFeedback(result?.error?.message || 'Falha ao salvar.', 'error'); return; }
            setBrandFeedback('Salvo com sucesso.', 'success');
            brandForm?.reset(); brandActiveInput.checked = true; if (brandIdInput) brandIdInput.value = '';
            await loadBrands();
        } catch { setBrandFeedback('Erro inesperado.', 'error'); }
    }

    async function submitTerminal() {
        const name = terminalNameInput?.value?.trim() || '';
        const active = terminalActiveInput?.checked ?? true;
        if (!name) { setTerminalFeedback('Informe um nome.', 'error'); return; }
        setTerminalFeedback('Salvando...', 'info');
        const payload = { id: terminalIdInput?.value || null, name, active };
        try {
            const resp = await window.apiFetch('/payment-catalog/terminals', { method: 'POST', body: JSON.stringify(payload) });
            const result = await resp.json().catch(()=>({}));
            if (!resp.ok) { setTerminalFeedback(result?.error?.message || 'Falha ao salvar.', 'error'); return; }
            setTerminalFeedback('Salvo com sucesso.', 'success');
            terminalForm?.reset(); terminalActiveInput.checked = true; if (terminalIdInput) terminalIdInput.value = '';
            await loadTerminals();
        } catch { setTerminalFeedback('Erro inesperado.', 'error'); }
    }

    function renderFeeTable(items) {
        if (!feeTable) return;
        feeTable.innerHTML = '';
        if (!Array.isArray(items) || items.length === 0) {
            feeTable.innerHTML = '<tr><td colspan="5" class="px-3 py-3 text-slate-500 text-center">Nenhuma taxa cadastrada para a seleção.</td></tr>';
            return;
        }
        items.forEach((f) => {
            const tr = document.createElement('tr');
            const parc = `${Number(f.installments_min||1)} - ${Number(f.installments_max||1)}x`;
            const desc = `${Number(f.fee_percentage||0).toFixed(2)}%` + (Number(f.per_installment_percentage||0) > 0 ? ` + ${Number(f.per_installment_percentage).toFixed(2)}%/parc` : '') + ` + R$ ${Number(f.fee_fixed||0).toFixed(2)}`;
            const conf = `R$ ${Number(f.confirmation_fixed_fee||0).toFixed(2)}`;
            tr.innerHTML = `
                <td class="px-3 py-2 uppercase">${(f.payment_method||'').toString()}</td>
                <td class="px-3 py-2">${parc}</td>
                <td class="px-3 py-2 text-right">${desc}</td>
                <td class="px-3 py-2 text-right">${conf}</td>
                <td class="px-3 py-2 text-right"><button class="text-blue-600" data-edit-fee="${f.id}">Editar</button></td>
            `;
            feeTable.appendChild(tr);
        });
    }

    async function loadFees() {
        currentFeeId = null;
        setFeeFeedback('', 'info');
        if (!feeTerminalSelect?.value || !feeBrandSelect?.value) {
            renderFeeTable([]);
            return;
        }
        try {
            const url = `/payment-catalog/fees?terminal_id=${encodeURIComponent(feeTerminalSelect.value)}&brand_id=${encodeURIComponent(feeBrandSelect.value)}`;
            const resp = await window.apiFetch(url);
            const data = await resp.json().catch(()=>({ items: [] }));
            const items = Array.isArray(data.items) ? data.items : [];
            renderFeeTable(items);
        } catch {
            setFeeFeedback('Erro ao carregar taxas.', 'error');
        }
    }

    async function submitFee() {
        const terminalId = feeTerminalSelect?.value || '';
        const brandId = feeBrandSelect?.value || '';
        const method = feeMethodSelect?.value || '';
        const percent = Number(feePercentInput?.value || '0');
        const fixed = Number(feeFixedInput?.value || '0');
        const instMin = Number(feeInstallmentsMin?.value || '1');
        const instMax = Number(feeInstallmentsMax?.value || String(instMin));
        const perInst = Number(feePerInstallmentInput?.value || '0');
        const confFixed = Number(feeConfirmationFixedInput?.value || '0');
        if (!terminalId || !brandId || !method) { setFeeFeedback('Selecione maquininha, bandeira e método.', 'error'); return; }
        setFeeFeedback('Salvando...', 'info');
        const payload = {
            id: currentFeeId,
            terminal_id: terminalId,
            brand_id: brandId,
            payment_method: method,
            fee_percentage: percent,
            fee_fixed: fixed,
            installments_min: instMin,
            installments_max: instMax,
            per_installment_percentage: perInst,
            confirmation_fixed_fee: confFixed,
        };
        try {
            const resp = await window.apiFetch('/payment-catalog/fees', { method: 'POST', body: JSON.stringify(payload) });
            const result = await resp.json().catch(()=>({}));
            if (!resp.ok) { setFeeFeedback(result?.error?.message || 'Falha ao salvar.', 'error'); return; }
            setFeeFeedback('Taxa salva com sucesso.', 'success');
            currentFeeId = result?.id || null;
            await loadFees();
        } catch { setFeeFeedback('Erro inesperado.', 'error'); }
    }

    function getCashFilters() {
        const filters = {};

        if (cashFilterFrom?.value) {
            filters.from = cashFilterFrom.value;
        }

        if (cashFilterTo?.value) {
            filters.to = cashFilterTo.value;
        }

        if (cashFilterMethod?.value) {
            filters.payment_method = cashFilterMethod.value;
        }

        return filters;
    }

    function buildQuery(filters, extra = {}) {
        const params = new URLSearchParams();
        const combined = { ...filters, ...extra };

        Object.entries(combined).forEach(([key, value]) => {
            if (value !== undefined && value !== null && value !== '') {
                params.append(key, String(value));
            }
        });

        return params;
    }

    async function exportCashCsv() {
        const filters = getCashFilters();
        const params = buildQuery(filters);
        const url = '/cash-ledger/export' + (params.toString() ? ('?' + params.toString()) : '');
        try {
            cashExportButton.disabled = true;
            const resp = await window.apiFetch(url);
            if (!resp.ok) {
                const result = await resp.json().catch(()=>({}));
                const msg = result?.error?.message || 'Falha ao exportar CSV.';
                throw new Error(msg);
            }
            const blob = await resp.blob();
            const disposition = resp.headers.get('Content-Disposition') || '';
            const match = /filename="?([^";]+)"?/i.exec(disposition);
            const filename = match ? match[1] : ('fluxo_caixa_' + new Date().toISOString().slice(0,19).replace(/[:T]/g,'-') + '.csv');
            const link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = filename;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        } catch (e) {
            console.error('Export CSV failed', e);
            setCashStatus('Nao foi possivel exportar o CSV.', 'error');
        } finally {
            cashExportButton.disabled = false;
        }
    }

    async function loadCashData() {
        if (!cashMethodTable || !cashLedgerRows) {
            return;
        }

        const filters = getCashFilters();

        cashMethodTable.innerHTML = '<tr><td colspan="5" class="px-4 py-3 text-slate-500 text-center">Carregando resumo...</td></tr>';
        cashLedgerRows.innerHTML = '<tr><td colspan="7" class="px-4 py-3 text-slate-500 text-center">Carregando movimentacoes...</td></tr>';
        if (cashSummaryCards) {
            cashSummaryCards.classList.add('hidden');
        }
        setCashStatus('Carregando dados do fluxo...', 'info');

        try {
            const summaryParams = buildQuery(filters);
            const summaryQuery = summaryParams.toString();
            const summaryUrl = summaryQuery ? `/cash-ledger/summary?${summaryQuery}` : '/cash-ledger/summary';

            const response = await window.apiFetch(summaryUrl);
            if (!response.ok) {
                throw new Error('Falha ao obter resumo do caixa');
            }

            const summary = await response.json();
            const totals = summary.totals ?? {};
            if (cashTotals.gross) {
                cashTotals.gross.textContent = formatCurrency(totals.gross);
            }
            if (cashTotals.fee) {
                cashTotals.fee.textContent = formatCurrency(totals.fee);
            }
            if (cashTotals.net) {
                cashTotals.net.textContent = formatCurrency(totals.net);
            }

            if (cashSummaryCards) {
                cashSummaryCards.classList.remove('hidden');
            }

            const byMethod = summary.by_method ?? {};
            const methodKeys = Object.keys(byMethod).sort();

            if (methodKeys.length === 0) {
                cashMethodTable.innerHTML = '<tr><td colspan="5" class="px-4 py-3 text-slate-500 text-center">Nenhum lancamento encontrado para os filtros aplicados.</td></tr>';
            } else {
                cashMethodTable.innerHTML = '';
                methodKeys.forEach((key) => {
                    const entry = byMethod[key];
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td class="px-4 py-2 uppercase tracking-wide">${key}</td>
                        <td class="px-4 py-2 text-right">${entry.count ?? 0}</td>
                        <td class="px-4 py-2 text-right">R$ ${formatCurrency(entry.gross)}</td>
                        <td class="px-4 py-2 text-right">R$ ${formatCurrency(entry.fee)}</td>
                        <td class="px-4 py-2 text-right">R$ ${formatCurrency(entry.net)}</td>
                    `;
                    cashMethodTable.appendChild(row);
                });
            }

            const movementCount = Number(summary.count ?? 0);
            setCashStatus(
                movementCount > 0
                    ? `Movimentações analisadas: ${movementCount}`
                    : 'Nenhum lancamento registrado no período selecionado.'
            );

            const ledgerParams = buildQuery(filters, { limit: 25 });
            const ledgerUrl = `/cash-ledger?${ledgerParams.toString()}`;
            const ledgerResponse = await window.apiFetch(ledgerUrl);
            if (!ledgerResponse.ok) {
                throw new Error('Falha ao obter movimentacoes do caixa');
            }

            const ledger = await ledgerResponse.json();
            const items = Array.isArray(ledger.items) ? ledger.items : [];

            if (items.length === 0) {
                cashLedgerRows.innerHTML = '<tr><td colspan="6" class="px-4 py-3 text-slate-500 text-center">Nenhum lancamento encontrado.</td></tr>';
            } else {
                // Garante coluna 'Vendedor' no cabeçalho
                try {
                    const ledgerHeaderRow = cashLedgerRows?.closest('table')?.querySelector('thead tr');
                    if (ledgerHeaderRow && !ledgerHeaderRow.querySelector('[data-col-vendedor]')) {
                        const th = document.createElement('th');
                        th.className = 'px-4 py-2';
                        th.setAttribute('data-col-vendedor', '');
                        th.textContent = 'Vendedor';
                        // Insere após a coluna Método (2ª posição)
                        const methodTh = ledgerHeaderRow.children[1] || null;
                        if (methodTh && methodTh.nextSibling) {
                            ledgerHeaderRow.insertBefore(th, methodTh.nextSibling);
                        } else {
                            ledgerHeaderRow.appendChild(th);
                        }
                    }
                } catch {}

                // Resolve nomes dos vendedores
                let userMap = {};
                try {
                    const ids = Array.from(new Set(items.map((e)=> (e.user_id || '').toString()).filter(Boolean)));
                    if (ids.length > 0) {
                        const respUsers = await window.apiFetch('/users/names?ids=' + encodeURIComponent(ids.join(',')));
                        const dataUsers = await respUsers.json().catch(()=>({ users: [] }));
                        const list = Array.isArray(dataUsers.users) ? dataUsers.users : [];
                        list.forEach((u)=>{ if (u && u.id) { userMap[u.id] = u.name || u.email || u.id; } });
                    }
                } catch {}

                cashLedgerRows.innerHTML = '';
                items.forEach((entry) => {
                    const row = document.createElement('tr');

                    const createdAt = entry.created_at ? new Date(entry.created_at).toLocaleString('pt-BR') : '-';
                    let methodLabel = (entry.payment_method ?? '').toString().toUpperCase();
                    try {
                        const note = String(entry.note || '');
                        const m = /installments=(\d+)/i.exec(note);
                        if (m) {
                            const n = parseInt(m[1] || '1', 10);
                            if (Number.isFinite(n) && n > 1) {
                                methodLabel += ` ${n}x`;
                            }
                        }
                    } catch {}
                    const saleLabel = (entry.sale_label || entry.origin_label || '').toString();
                    const noteLower = (entry.note || '').toString().toLowerCase();
                    const isRefund = String(entry.entry_type || '') === 'refund'
                        || noteLower.includes('devolucao')
                        || noteLower.includes('refund');
                    let origin = entry.sale_id
                        ? (saleLabel ? `Venda ${saleLabel}` : `Venda ${entry.sale_id}`)
                        : `Ajuste por ${entry.user_id ?? 'usuário'}`;
                    if (isRefund) {
                        origin = entry.note ? `Devolucao: ${entry.note}` : 'Devolucao/estorno';
                    } else if (!entry.sale_id && entry.note) {
                        origin = entry.note;
                    }

                    row.innerHTML = `
                        <td class="px-4 py-2">${createdAt}</td>
                        <td class="px-4 py-2 uppercase">${methodLabel}</td>
                        <td class="px-4 py-2">${userMap[entry.user_id] || (entry.user_id ?? '')}</td>
                        <td class="px-4 py-2 text-right">R$ ${formatCurrency(entry.gross_amount)}</td>
                        <td class="px-4 py-2 text-right">R$ ${formatCurrency(entry.fee_amount)}</td>
                        <td class="px-4 py-2 text-right">R$ ${formatCurrency(entry.net_amount)}</td>
                        <td class="px-4 py-2">${origin}</td>
                    `;

                    try {
                        // Garante th 'Comissao' no cabecalho
                        const tableEl = cashLedgerRows?.closest('table');
                        const headerRow = tableEl?.querySelector('thead tr');
                        if (headerRow && !headerRow.querySelector('[data-col-comissao]')) {
                            const tc = document.createElement('th');
                            tc.className = 'px-4 py-2 text-right';
                            tc.setAttribute('data-col-comissao', '');
                            tc.textContent = 'Comissao';
                            let taxaTh = null;
                            try { taxaTh = Array.from(headerRow.children).find((el)=> String(el.textContent||'').toLowerCase().includes('taxa')) || null; } catch {}
                            if (taxaTh && taxaTh.nextSibling) { headerRow.insertBefore(tc, taxaTh.nextSibling); } else { headerRow.appendChild(tc); }
                        }

                        // Se for ajuste de comissao (sale_id, gross=0, fee>0), nao exibe linha
                        const isCommissionAdj = String(entry.entry_type||'') === 'adjustment' && entry.sale_id && Number(entry.gross_amount||0) === 0 && Number(entry.fee_amount||0) > 0;
                        if (isCommissionAdj) { return; }

                        // Calcula comissao por venda e ajusta liquido
                        let commissionValue = 0;
                        try {
                            if (entry.sale_id) {
                                const adj = items.find((e)=> String(e.entry_type||'') === 'adjustment' && e.sale_id === entry.sale_id && Number(e.gross_amount||0) === 0 && Number(e.fee_amount||0) > 0);
                                commissionValue = Math.abs(Number(adj?.net_amount || 0));
                            }
                        } catch {}

                        const netAdjusted = Number(entry.net_amount || 0) - ((String(entry.entry_type||'') === 'sale') ? commissionValue : 0);

                        // Insere coluna 'Comissao' antes do liquido e ajusta liquido
                        const commissionTd = document.createElement('td');
                        commissionTd.className = 'px-4 py-2 text-right';
                        commissionTd.textContent = 'R$ ' + formatCurrency(commissionValue);
                        // Colunas: Data(0), Metodo(1), Vendedor(2), Bruto(3), Taxa(4), Liquido(5), Origem(6)
                        const netCell = row.children[5];
                        if (netCell) { netCell.textContent = 'R$ ' + formatCurrency(netAdjusted); }
                        if (row.children[5]) { row.insertBefore(commissionTd, row.children[5]); } else { row.appendChild(commissionTd); }

                        // Origem: encurta id da venda
                        const originCell = row.children[7] || row.lastElementChild;
                        if (originCell && entry.sale_id) {
                            if (saleLabel) {
                                originCell.textContent = 'Venda ' + saleLabel;
                            } else {
                                originCell.textContent = 'Venda ' + String(entry.sale_id).slice(-5);
                            }
                        }
                    } catch {}

                    cashLedgerRows.appendChild(row);
                });
            }
        } catch (error) {
            console.error('Falha ao carregar resumo do fluxo', error);
            setCashStatus('Não foi possível carregar o resumo do fluxo agora.', 'error');
            cashMethodTable.innerHTML = '<tr><td colspan="5" class="px-4 py-3 text-rose-600 text-center">Erro ao carregar resumo.</td></tr>';
            cashLedgerRows.innerHTML = '<tr><td colspan="6" class="px-4 py-3 text-rose-600 text-center">Erro ao carregar movimentacoes.</td></tr>';
        }
    }

    async function submitCashAdjustment(event) {
        event.preventDefault();

        if (!cashAdjustForm) {
            return;
        }

        const method = cashAdjustForm.payment_method?.value ?? '';
        const gross = parseFloat(cashAdjustForm.gross_amount?.value ?? '0');
        const fee = parseFloat(cashAdjustForm.fee_amount?.value ?? '0');
        const note = cashAdjustForm.note?.value?.trim() || null;

        if (!method) {
            setCashAdjustFeedback('Escolha o metodo para o ajuste.', 'error');
            return;
        }

        if (!Number.isFinite(gross) || gross <= 0) {
            setCashAdjustFeedback('Informe um valor bruto maior que zero.', 'error');
            return;
        }

        if (!Number.isFinite(fee) || fee < 0) {
            setCashAdjustFeedback('Informe uma taxa válida (maior ou igual a zero).', 'error');
            return;
        }

        const submitButton = cashAdjustForm.querySelector('button[type="submit"]');
        if (submitButton) {
            submitButton.disabled = true;
        }
        setCashAdjustFeedback('Registrando ajuste...', 'info');

        try {
            const response = await window.apiFetch('/cash-ledger/adjustment', {
                method: 'POST',
                body: JSON.stringify({
                    payment_method: method,
                    gross_amount: gross,
                    fee_amount: fee,
                    note,
                }),
            });

            const result = await response.json().catch(() => ({}));

            if (!response.ok) {
                const message = result.error?.message ?? 'Falha ao registrar ajuste.';
                setCashAdjustFeedback(message, 'error');
                return;
            }

            setCashAdjustFeedback('Ajuste registrado com sucesso.', 'success');
            if (cashAdjustForm) {
                cashAdjustForm.reset();
            }
            const defaults = getDefaultCashRange();
            if (cashFilterFrom && !cashFilterFrom.value) {
                cashFilterFrom.value = defaults.from;
            }
            if (cashFilterTo && !cashFilterTo.value) {
                cashFilterTo.value = defaults.to;
            }
            loadCashData();
        } catch (error) {
            console.error('Erro ao registrar ajuste de caixa', error);
            setCashAdjustFeedback('Erro inesperado ao registrar ajuste.', 'error');
        } finally {
            if (submitButton) {
                submitButton.disabled = false;
            }
        }
    }

    async function submitCashExpense(event) {
        event.preventDefault();

        if (!cashExpenseForm) {
            return;
        }

        const method = cashExpenseForm.payment_method?.value ?? '';
        const amount = parseFloat(cashExpenseForm.amount?.value ?? '0');
        const noteRaw = cashExpenseForm.note?.value?.trim() || '';
        const createdAt = cashExpenseDateInput?.value || null;

        if (!method) {
            setCashExpenseFeedback('Escolha o metodo da saida.', 'error');
            return;
        }

        if (!Number.isFinite(amount) || amount <= 0) {
            setCashExpenseFeedback('Informe um valor maior que zero.', 'error');
            return;
        }

        const note = noteRaw ? `Saida: ${noteRaw}` : 'Saida manual';
        const submitButton = cashExpenseForm.querySelector('button[type="submit"]');
        if (submitButton) {
            submitButton.disabled = true;
        }
        setCashExpenseFeedback('Registrando saida...', 'info');

        try {
            const response = await window.apiFetch('/cash-ledger/adjustment', {
                method: 'POST',
                body: JSON.stringify({
                    payment_method: method,
                    gross_amount: 0,
                    fee_amount: amount,
                    note,
                    created_at: createdAt,
                }),
            });

            const result = await response.json().catch(() => ({}));

            if (!response.ok) {
                const message = result.error?.message ?? 'Falha ao registrar saida.';
                setCashExpenseFeedback(message, 'error');
                return;
            }

            setCashExpenseFeedback('Saida registrada com sucesso.', 'success');
            cashExpenseForm.reset();
            ensureCashExpenseDate();
            const defaults = getDefaultCashRange();
            if (cashFilterFrom && !cashFilterFrom.value) {
                cashFilterFrom.value = defaults.from;
            }
            if (cashFilterTo && !cashFilterTo.value) {
                cashFilterTo.value = defaults.to;
            }
            loadCashData();
        } catch (error) {
            console.error('Erro ao registrar saida de caixa', error);
            setCashExpenseFeedback('Erro inesperado ao registrar saida.', 'error');
        } finally {
            if (submitButton) {
                submitButton.disabled = false;
            }
        }
    }

    async function submitPaymentConfig(event) {
        event.preventDefault();

        if (!paymentConfigForm) {
            return;
        }

        const method = paymentConfigMethod?.value ?? '';
        if (!method) {
            setPaymentConfigFeedback('Selecione o método de pagamento.', 'error');
            return;
        }

        const feePercent = Number(paymentConfigFeePercent?.value ?? '0');
        if (!Number.isFinite(feePercent) || feePercent < 0 || feePercent > 100) {
            setPaymentConfigFeedback('Informe uma taxa percentual entre 0 e 100.', 'error');
            return;
        }

        const feeFixed = Number(paymentConfigFeeFixed?.value ?? '0');
        if (!Number.isFinite(feeFixed) || feeFixed < 0) {
            setPaymentConfigFeedback('Informe uma taxa fixa maior ou igual a zero.', 'error');
            return;
        }

        const allowDiscount = paymentConfigAllowDiscount?.checked ?? false;
        const maxDiscount = Number(paymentConfigMaxDiscount?.value ?? '0');
        if (allowDiscount && (!Number.isFinite(maxDiscount) || maxDiscount < 0 || maxDiscount > 100)) {
            setPaymentConfigFeedback('Informe um desconto máximo entre 0 e 100.', 'error');
            return;
        }

        const submitButton = paymentConfigForm.querySelector('button[type="submit"]');
        if (submitButton) {
            submitButton.disabled = true;
        }
        setPaymentConfigFeedback('Salvando taxas...', 'info');

        try {
            const response = await window.apiFetch('/payment-config', {
                method: 'POST',
                body: JSON.stringify({
                    payment_method: method,
                    fee_percentage: feePercent,
                    fee_fixed: feeFixed,
                    allow_discount: allowDiscount,
                    max_discount_percentage: allowDiscount ? maxDiscount : 0,
                }),
            });

            const result = await response.json().catch(() => ({}));
            if (!response.ok) {
                const message = result.error?.message ?? 'Falha ao salvar as taxas.';
                setPaymentConfigFeedback(message, 'error');
                return;
            }

            const savedMethod = typeof result.payment_method === 'string' ? result.payment_method : method;
            paymentConfigs[savedMethod] = result;
            renderPaymentConfig(savedMethod);
            setPaymentConfigFeedback('Taxas atualizadas com sucesso.', 'success');
        } catch (error) {
            console.error('Erro ao salvar taxas da maquininha', error);
            setPaymentConfigFeedback('Erro inesperado ao salvar as taxas.', 'error');
        } finally {
            if (submitButton) {
                submitButton.disabled = false;
            }
        }
    }

    async function createProduct(event) {
        event.preventDefault();

        const form = event.currentTarget;
        productFeedback.className = 'text-sm';
        productFeedback.textContent = '';

        const payload = {
            sku: form.sku.value.trim(),
            name: form.name.value.trim(),
            description: form.description.value.trim() || null,
            size: (form.size?.value ?? '').trim() || null,
            supplier_cost: parseFloat(form.supplier_cost.value),
            sale_price: parseFloat(form.sale_price.value),
            stock: parseInt(form.stock.value || '0', 10),
            min_stock_alert: parseInt(form.min_stock_alert.value || '0', 10),
            active: form.active.checked,
        };

        if (!payload.sku || !payload.name) {
            productFeedback.textContent = 'Preencha os campos obrigatorios.';
            productFeedback.classList.add('text-rose-600');
            return;
        }

        if (payload.sale_price < payload.supplier_cost) {
            productFeedback.textContent = 'Preco de venda deve ser maior ou igual ao custo.';
            productFeedback.classList.add('text-rose-600');
            return;
        }

        try {
            const response = await window.apiFetch('/products', {
                method: 'POST',
                body: JSON.stringify(payload),
            });

            const result = await response.json().catch(() => ({}));
            if (!response.ok) {
                const message = result.error?.message ?? 'Falha ao salvar produto.';
                productFeedback.textContent = message;
                productFeedback.classList.add('text-rose-600');
                return;
            }

            productFeedback.textContent = 'Produto salvo com sucesso.';
            productFeedback.classList.add('text-emerald-600');
            form.reset();
            form.active.checked = true;
            loadAdminDashboard();
            loadStockProducts();
        } catch (error) {
            console.error('Erro ao salvar produto', error);
            productFeedback.textContent = 'Erro inesperado ao salvar produto.';
            productFeedback.classList.add('text-rose-600');
        }
    }

    function setStockFeedback(message, tone = 'info') {
        stockFeedback.textContent = message;
        const classes = {
            error: 'text-sm text-rose-600',
            success: 'text-sm text-emerald-600',
            info: 'text-sm text-slate-500',
        };
        stockFeedback.className = classes[tone] ?? classes.info;
    }

    function setReportFeedback(message, tone = 'info') {
        if (!reportFeedback) {
            return;
        }

        const classes = {
            error: 'text-sm text-rose-600',
            success: 'text-sm text-emerald-600',
            info: 'text-sm text-slate-500',
        };

        reportFeedback.textContent = message;
        reportFeedback.className = classes[tone] ?? classes.info;
    }

    function setReturnFeedback(message, tone = 'info') {
        if (!returnFeedback) {
            return;
        }

        const classes = {
            error: 'text-sm text-rose-600',
            success: 'text-sm text-emerald-600',
            info: 'text-sm text-slate-500',
        };

        returnFeedback.textContent = message;
        returnFeedback.className = classes[tone] ?? classes.info;
    }

    function ensureReturnDate() {
        if (!returnDateInput) {
            return;
        }

        if (!returnDateInput.value) {
            returnDateInput.value = new Date().toISOString().slice(0, 10);
        }
    }

    function ensureCashExpenseDate() {
        if (!cashExpenseDateInput) {
            return;
        }

        if (!cashExpenseDateInput.value) {
            cashExpenseDateInput.value = new Date().toISOString().slice(0, 10);
        }
    }

    function extractFilename(header) {
        if (!header) {
            return null;
        }

        const match = header.match(/filename=\"?([^\";]+)\"?/i);

        return match ? match[1] : null;
    }

    async function downloadReport(type, format, trigger) {
        if (!type || !format) {
            return;
        }

        setReportFeedback('Gerando relatório...', 'info');

        if (trigger) {
            trigger.disabled = true;
        }

        try {
            const response = await window.apiFetch(`/reports/${type}?format=${format}`);

            if (!response.ok) {
                const result = await response.json().catch(() => ({}));
                const message = result.error?.message ?? 'Falha ao gerar relatório.';
                throw new Error(message);
            }

            const blob = await response.blob();
            const disposition = response.headers.get('Content-Disposition') ?? '';
            const filename = extractFilename(disposition) ?? `relatório_${type}.${format}`;
            const url = URL.createObjectURL(blob);

            const link = document.createElement('a');
            link.href = url;
            link.download = filename;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);

            URL.revokeObjectURL(url);
            setReportFeedback('Relatório gerado com sucesso.', 'success');
        } catch (error) {
            console.error('Erro ao exportar relatório', error);
            setReportFeedback(error.message ?? 'Falha ao exportar relatório.', 'error');
        } finally {
            if (trigger) {
                trigger.disabled = false;
            }
        }
    }

        function renderStockTable() {
        if (!stockTableBody) {
            return;
        }

        if (stockSizes.length === 0) {
            stockTableBody.innerHTML = '<tr><td colspan="2" class="px-4 py-3 text-slate-500 text-center">Nenhum tamanho disponível.</td></tr>';
            return;
        }


        stockTableBody.innerHTML = '';

        const grouped = stockSizes.reduce((acc, item) => {
            const groupKey = item.group ?? 'outros';
            if (!acc[groupKey]) {
                acc[groupKey] = [];
            }
            acc[groupKey].push(item);
            return acc;
        }, {});

        const order = ['feminina', 'masculina', 'outros'];

        order.forEach((groupKey) => {
            const items = grouped[groupKey];
            if (!items || items.length === 0) {
                return;
            }

            const groupHeader = document.createElement('tr');
            const label = groupKey === 'feminina'
                ? 'Feminina'
                : groupKey === 'masculina'
                    ? 'Masculina'
                    : 'Outros';
            groupHeader.innerHTML = `
                <td class="px-4 py-2 font-semibold text-slate-600" colspan="2">${label}</td>
            `;
            stockTableBody.appendChild(groupHeader);

            items.forEach((item) => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td class="px-4 py-2">${item.size}</td>
                    <td class="px-4 py-2 text-right">${item.quantity}</td>
                `;
                stockTableBody.appendChild(row);
            });
        });
    }

    function resetStockSizeSelect(message = 'Selecione um produto') {
        if (!stockSizeSelect) {
            return;
        }
        stockSizeSelect.innerHTML = `<option value="">${message}</option>`;
        stockSizeSelect.disabled = true;
        stockSizes = [];
        renderStockTable();
    }

    function resetReturnSizeSelect(message = 'Selecione um produto') {
        if (!returnSizeSelect) {
            return;
        }
        returnSizeSelect.innerHTML = `<option value="">${message}</option>`;
        returnSizeSelect.disabled = true;
    }

    function populateReturnProducts(list = stockProducts) {
        if (!returnProductSelect) {
            return;
        }
        resetReturnSizeSelect();

        if (!Array.isArray(list) || list.length === 0) {
            returnProductSelect.innerHTML = '<option value=\"\">Nenhum produto encontrado</option>';
            return;
        }

        returnProductSelect.innerHTML = '<option value=\"\">Selecione um produto</option>';
        list.forEach((product) => {
            const option = document.createElement('option');
            option.value = product.id;
            option.textContent = product.sku ? `${product.name} (${product.sku})` : product.name;
            returnProductSelect.appendChild(option);
        });
    }

    async function loadReturnSizes(productId) {
        if (!returnSizeSelect) {
            return;
        }

        if (!productId) {
            resetReturnSizeSelect();
            return;
        }

        returnSizeSelect.disabled = true;
        returnSizeSelect.innerHTML = '<option value=\"\">Carregando tamanhos...</option>';
        try {
            const response = await window.apiFetch(`/products/${productId}/sizes`);
            if (!response.ok) {
                throw new Error('Falha ao carregar tamanhos.');
            }
            const data = await response.json();
            const sizes = Array.isArray(data.sizes) ? data.sizes : [];
            if (sizes.length === 0) {
                resetReturnSizeSelect('Nenhum tamanho cadastrado');
                return;
            }
            returnSizeSelect.innerHTML = '<option value=\"\">Selecione um tamanho</option>';
            sizes.forEach((entry) => {
                const opt = document.createElement('option');
                opt.value = entry.size;
                const qtyLabel = typeof entry.quantity === 'number' ? ` (disp.: ${entry.quantity})` : '';
                opt.textContent = `${entry.size}${qtyLabel}`;
                returnSizeSelect.appendChild(opt);
            });
            returnSizeSelect.disabled = false;
        } catch (error) {
            console.error('Erro ao carregar tamanhos para devolucao', error);
            resetReturnSizeSelect('Erro ao carregar tamanhos');
        }
    }

    function resetReturnForm() {
        if (!returnForm) {
            return;
        }
        returnForm.reset();
        ensureReturnDate();
        resetReturnSizeSelect();
        setReturnFeedback('', 'info');
    }

    async function submitReturn(event) {
        event.preventDefault();
        if (!returnForm) {
            return;
        }
        const productId = returnProductSelect?.value || '';
        const size = returnSizeSelect?.value || '';
        const quantity = Math.max(1, parseInt(returnQtyInput?.value || '1', 10));
        const refundAmount = parseFloat(returnAmountInput?.value || '0');
        const returnedAt = returnDateInput?.value ? returnDateInput.value : null;
        if (!productId || !size) {
            setReturnFeedback('Selecione produto e tamanho.', 'error');
            return;
        }
        if (!Number.isFinite(refundAmount) || refundAmount <= 0) {
            setReturnFeedback('Informe o valor estornado em PIX.', 'error');
            return;
        }

        const submitButton = returnForm.querySelector('button[type="submit"]');
        if (submitButton) {
            submitButton.disabled = true;
        }
        setReturnFeedback('Registrando devolucao...', 'info');

        try {
            const payload = {
                product_id: productId,
                size,
                quantity,
                refund_amount: refundAmount,
                payment_method: 'pix',
                returned_at: returnedAt,
                note: returnNoteInput?.value?.trim() || null,
            };

            let response = await window.apiFetch('/returns', {
                method: 'POST',
                body: JSON.stringify(payload),
            });
            let result = await response.json().catch(() => ({}));

            if (!response.ok && (response.status === 405 || result.error?.code === 'METHOD_NOT_ALLOWED')) {
                const params = new URLSearchParams();
                Object.entries(payload).forEach(([key, value]) => {
                    if (value === null || value === undefined || value === '') {
                        return;
                    }
                    params.set(key, String(value));
                });
                const fallbackUrl = `/returns?${params.toString()}`;
                response = await window.apiFetch(fallbackUrl, { method: 'GET' });
                result = await response.json().catch(() => ({}));
            }

            if (!response.ok) {
                const message = result.error?.message ?? 'Falha ao registrar devolucao.';
                throw new Error(message);
            }

            setReturnFeedback('Devolucao registrada: estoque reposto e estorno lancado no caixa.', 'success');
            returnForm.reset();
            ensureReturnDate();
            resetReturnSizeSelect();
            if (returnProductSelect) {
                returnProductSelect.value = productId;
            }

            try { await loadCashData(); } catch {}
            try {
                if (stockProductSelect?.value === productId) {
                    await loadStockSizes(productId);
                } else {
                    await loadStockProducts();
                }
            } catch {}
        } catch (error) {
            setReturnFeedback(error?.message ?? 'Erro ao registrar devolucao.', 'error');
        } finally {
            if (submitButton) {
                submitButton.disabled = false;
            }
        }
    }

            async function loadStockProducts() {
        if (!stockProductSelect) {
            return;
        }

        const statusEl = document.querySelector('#stock-catalog-status');
        if (statusEl) { statusEl.textContent = 'Carregando produtos...'; statusEl.className = 'mt-1 text-xs text-slate-500'; }
        stockProductSelect.innerHTML = '<option value="">Carregando produtos...</option>';
        if (returnProductSelect) { returnProductSelect.innerHTML = '<option value="">Carregando produtos...</option>'; }
        resetStockSizeSelect();
        resetReturnSizeSelect();

        try {
            // 1) Tenta opções (ativos)
            let items = [];
                        try {
                const resp1 = await window.apiFetch('/products/options');
                if (resp1.status === 401 || resp1.status === 403) {
                    if (statusEl) { statusEl.textContent = 'Sessão expirada. Faça login novamente.'; statusEl.className = 'mt-1 text-xs text-amber-600'; }
                    setTimeout(() => { window.location.href = '/'; }, 800);
                    return;
                }
                if (resp1.ok) {
                    const data1 = await resp1.json();
                    items = Array.isArray(data1.items) ? data1.items : [];
                }
            } catch {}

            // 2) Se vazio, tenta todos (admin)
            if (!items || items.length === 0) {
                                try {
                    const resp2 = await window.apiFetch('/products?limit=1000');
                    if (resp2.status === 401 || resp2.status === 403) {
                        if (statusEl) { statusEl.textContent = 'Sessão expirada. Faça login novamente.'; statusEl.className = 'mt-1 text-xs text-amber-600'; }
                        setTimeout(() => { window.location.href = '/'; }, 800);
                        return;
                    }
                    if (resp2.ok) {
                        const data2 = await resp2.json();
                        items = Array.isArray(data2.items) ? data2.items : [];
                    }
                } catch {}
            }

            stockProducts = items;
            populateReturnProducts(stockProducts);

            if (!Array.isArray(stockProducts) || stockProducts.length === 0) {
                stockProductSelect.innerHTML = '<option value="">Nenhum produto encontrado</option>';
                if (returnProductSelect) { returnProductSelect.innerHTML = '<option value="">Nenhum produto encontrado</option>'; }
                if (apdvProductSelect) {
                    apdvProductSelect.innerHTML = '<option value="">Nenhum produto encontrado</option>';
                }
                if (statusEl) { statusEl.textContent = 'Nenhum produto encontrado.'; statusEl.className = 'mt-1 text-xs text-amber-600'; }
                return;
            }

            stockProductSelect.innerHTML = '<option value="">Selecione um produto</option>';
            stockProducts.forEach((product) => {
                const option = document.createElement('option');
                option.value = product.id;
                option.textContent = product.sku ? `${product.name} (${product.sku})` : product.name;
                stockProductSelect.appendChild(option);
            });

            // Auto-seleciona o primeiro produto e carrega seus tamanhos
            try {
                const firstId = stockProducts[0]?.id || '';
                if (firstId) {
                    stockProductSelect.value = firstId;
                    await loadStockSizes(firstId);
                    if (returnProductSelect && !returnProductSelect.value) {
                        returnProductSelect.value = firstId;
                        loadReturnSizes(firstId);
                    }
                }
            } catch {}

            // Preenche o PDV (Admin) caso ainda não tenha sido carregado
            if (apdvProductSelect && apdvProductSelect.options.length <= 1) {
                apdvProductSelect.innerHTML = '<option value="">Selecione um produto</option>';
                stockProducts.forEach((p) => {
                    const opt = document.createElement('option');
                    opt.value = p.id;
                    opt.textContent = p.sku ? `${p.name} (${p.sku})` : p.name;
                    opt.dataset.price = p.sale_price != null ? String(p.sale_price) : '';
                    apdvProductSelect.appendChild(opt);
                });
            }

            if (statusEl) { statusEl.textContent = `Produtos carregados: ${stockProducts.length}`; statusEl.className = 'mt-1 text-xs text-slate-500'; }
        } catch (error) {
            console.error('Erro ao carregar catálogo de produtos', error);
            stockProductSelect.innerHTML = '<option value="">Erro ao carregar produtos</option>';
            if (apdvProductSelect) {
                apdvProductSelect.innerHTML = '<option value="">Erro ao carregar produtos</option>';
            }
            if (statusEl) { statusEl.textContent = 'Erro de rede ao carregar produtos.'; statusEl.className = 'mt-1 text-xs text-rose-600'; }
        }
    }

    async function loadStockSizes(productId) {
        if (!stockSizeSelect) {
            return;
        }

        if (!productId) {
            resetStockSizeSelect();
            return;
        }

        stockSizeSelect.disabled = true;
        stockSizeSelect.innerHTML = '<option value="">Carregando tamanhos...</option>';

        try {
            const response = await window.apiFetch(`/products/${productId}/sizes`);
            if (!response.ok) {
                throw new Error('Falha ao carregar tamanhos');
            }

            const data = await response.json();
            stockSizes = Array.isArray(data.sizes) ? data.sizes : [];
            renderStockTable();

            stockSizeSelect.innerHTML = '<option value="">Selecione um tamanho</option>';
            const groups = new Map();

            stockSizes.forEach((entry) => {
                const groupKey = entry.group ?? 'outros';
                const option = document.createElement('option');
                option.value = entry.size;
                option.textContent = `${entry.size} (disp.: ${entry.quantity})`;

                if (groupKey === 'outros') {
                    stockSizeSelect.appendChild(option);
                    return;
                }

                const label = groupKey === 'feminina' ? 'Feminina' : groupKey === 'masculina' ? 'Masculina' : 'Outros';
                let optgroup = groups.get(groupKey);
                if (!optgroup) {
                    optgroup = document.createElement('optgroup');
                    optgroup.label = label;
                    groups.set(groupKey, optgroup);
                }
                optgroup.appendChild(option);
            });

            groups.forEach((optgroup) => {
                stockSizeSelect.appendChild(optgroup);
            });

            stockSizeSelect.disabled = false;
            stockSizeSelect.value = '';
        } catch (error) {
            console.error('Erro ao carregar tamanhos', error);
            resetStockSizeSelect('Erro ao carregar tamanhos');
        }
    }

        reportButtons.forEach((button) => {
        button.addEventListener('click', (event) => {
            event.preventDefault();
            const target = event.currentTarget;
            const reportType = target?.getAttribute('data-report-export');
            if (!reportType) return;

            // Captura os filtros do formulário irmão
            const form = target.closest('form');
            const format = form?.querySelector('select[name="format"]')?.value || 'csv';
            const params = new URLSearchParams();

            if (reportType === 'sales') {
                const from = form?.querySelector('input[name="from"]')?.value || '';
                const to = form?.querySelector('input[name="to"]')?.value || '';
                if (from) params.append('from', from);
                if (to) params.append('to', to);
            }

            if (reportType === 'inventory') {
                const sku = form?.querySelector('input[name="sku"]')?.value || '';
                const onlyActive = form?.querySelector('input[name="only_active"]')?.checked || false;
                if (sku) params.append('sku', sku);
                if (onlyActive) params.append('active', 'true');
            }

            const qs = params.toString();
            const url = `/reports/${reportType}${qs ? `?${qs}&format=${format}` : `?format=${format}`}`;
            downloadReportDirect(url, target);
        });
    });

    async function downloadReportDirect(url, trigger) {
        setReportFeedback('Gerando relatório...', 'info');
        if (trigger) trigger.disabled = true;
        try {
            const response = await window.apiFetch(url);
            if (!response.ok) {
                const result = await response.json().catch(() => ({}));
                const message = result.error?.message ?? 'Falha ao gerar relatório.';
                throw new Error(message);
            }
            const blob = await response.blob();
            const disposition = response.headers.get('Content-Disposition') ?? '';
            const filename = extractFilename(disposition) ?? 'relatorio.bin';
            const link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = filename;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            setReportFeedback('Relatório gerado com sucesso.', 'success');
        } catch (error) {
            console.error('Erro ao exportar relatório', error);
            setReportFeedback(error.message ?? 'Falha ao exportar relatório.', 'error');
        } finally {
            if (trigger) trigger.disabled = false;
        }
    }

    cashFilterForm?.addEventListener('submit', (event) => {
        event.preventDefault();
        loadCashData();
    });

    cashFilterReset?.addEventListener('click', (event) => {
        event.preventDefault();
        if (!cashFilterForm) {
            return;
        }
        cashFilterForm.reset();
        const defaults = getDefaultCashRange();
        if (cashFilterFrom) {
            cashFilterFrom.value = defaults.from;
        }
        if (cashFilterTo) {
            cashFilterTo.value = defaults.to;
        }
        if (cashFilterMethod) {
            cashFilterMethod.value = '';
        }
        loadCashData();
    });

        productForm?.addEventListener('submit', createProduct);
    cashAdjustForm?.addEventListener('submit', submitCashAdjustment);
    cashExpenseForm?.addEventListener('submit', submitCashExpense);
    cashExportButton?.addEventListener('click', exportCashCsv);

    cashAdjustReset?.addEventListener('click', (event) => {
        event.preventDefault();
        cashAdjustForm?.reset();
        setCashAdjustFeedback('Formulário limpo.', 'info');
    });

    cashExpenseReset?.addEventListener('click', (event) => {
        event.preventDefault();
        cashExpenseForm?.reset();
        ensureCashExpenseDate();
        setCashExpenseFeedback('Formulario limpo.', 'info');
    });

    paymentConfigMethod?.addEventListener('change', () => {
        renderPaymentConfig(paymentConfigMethod.value);
    });

    paymentConfigAllowDiscount?.addEventListener('change', () => {
        if (!paymentConfigMaxDiscount) {
            return;
        }
        const allowed = paymentConfigAllowDiscount.checked;
        paymentConfigMaxDiscount.disabled = !allowed;
        if (!allowed) {
            paymentConfigMaxDiscount.value = '0';
        }
    });

    // Eventos: brands / terminals / fees
    brandForm?.addEventListener('submit', (e) => { e.preventDefault(); submitBrand(); });
    document.querySelector('#brand-save')?.addEventListener('click', (e) => { e.preventDefault(); submitBrand(); });
    terminalForm?.addEventListener('submit', (e) => { e.preventDefault(); submitTerminal(); });
    document.querySelector('#terminal-save')?.addEventListener('click', (e) => { e.preventDefault(); submitTerminal(); });
    document.addEventListener('click', (e) => {
        const target = e.target instanceof HTMLElement ? e.target : null;
        if (!target) return;
        const brandId = target.getAttribute('data-edit-brand');
        if (brandId && brands) {
            const b = brands.find(x => x.id === brandId);
            if (b) { brandIdInput.value = b.id; brandNameInput.value = b.name || ''; brandActiveInput.checked = !!b.active; }
        }
        const termId = target.getAttribute('data-edit-terminal');
        if (termId && terminals) {
            const t = terminals.find(x => x.id === termId);
            if (t) { terminalIdInput.value = t.id; terminalNameInput.value = t.name || ''; terminalActiveInput.checked = !!t.active; }
        }
        const feeId = target.getAttribute('data-edit-fee');
        if (feeId && feeTable) {
            // Busca a linha correspondente na tabela já carregada
            // Otimização simples: refaz o GET e preenche com a primeira correspondência
            (async () => {
                try {
                    const url = `/payment-catalog/fees?terminal_id=${encodeURIComponent(feeTerminalSelect.value)}&brand_id=${encodeURIComponent(feeBrandSelect.value)}`;
                    const resp = await window.apiFetch(url);
                    const data = await resp.json().catch(()=>({ items: [] }));
                    const items = Array.isArray(data.items) ? data.items : [];
                    const f = items.find(x => x.id === feeId);
                    if (f) {
                        currentFeeId = f.id;
                        feeMethodSelect.value = f.payment_method;
                        feePercentInput.value = String(f.fee_percentage ?? 0);
                        feeFixedInput.value = Number(f.fee_fixed ?? 0).toFixed(2);
                        feeInstallmentsMin.value = String(f.installments_min ?? 1);
                        feeInstallmentsMax.value = String(f.installments_max ?? 1);
                        feePerInstallmentInput.value = String(f.per_installment_percentage ?? 0);
                        feeConfirmationFixedInput.value = Number(f.confirmation_fixed_fee ?? 0).toFixed(2);
                        setFeeFeedback('Editando taxa selecionada.', 'info');
                    }
                } catch {}
            })();
        }
    });
    document.querySelector('#fee-save')?.addEventListener('click', (e) => { e.preventDefault(); submitFee(); });
    feeTerminalSelect?.addEventListener('change', loadFees);
    feeBrandSelect?.addEventListener('change', loadFees);

    function refreshInstallmentsVisibility() {
        const isCredito = (feeMethodSelect?.value || '') === 'credito';
        [feeInstallmentsMin, feeInstallmentsMax, feePerInstallmentInput].forEach((el) => {
            if (!el) return;
            el.disabled = !isCredito;
            if (!isCredito) { if (el === feePerInstallmentInput) el.value = '0'; else el.value = '1'; }
        });
    }
    feeMethodSelect?.addEventListener('change', refreshInstallmentsVisibility);
    refreshInstallmentsVisibility();

    paymentConfigForm?.addEventListener('submit', submitPaymentConfig);

        // Toast simples
    function showToast(message, tone = 'info') {
        const wrap = document.createElement('div');
        wrap.className = 'fixed bottom-4 right-4 z-50';
        const bg = tone === 'success' ? 'bg-emerald-600' : tone === 'error' ? 'bg-rose-600' : 'bg-slate-800';
        wrap.innerHTML = `<div class="${bg} text-white text-sm px-4 py-2 rounded shadow-lg">${message}</div>`;
        document.body.appendChild(wrap);
        setTimeout(() => { wrap.remove(); }, 3000);
    }

        // Botão manual de atualização (Admin)
    const adminRefreshBtn = document.querySelector('#admin-refresh');
    adminRefreshBtn?.addEventListener('click', async (e) => {
        e.preventDefault();
        try { loadAdminDashboard(); } catch {}
        try { loadCashData(); } catch {}
        const selectedProductId = stockProductSelect?.value || '';
        if (selectedProductId) {
            try { await loadStockSizes(selectedProductId); } catch {}
        }
        showToast('Painéis atualizados.', 'success');
    });

    // Botão de debug de logs (temporário)
    const debugBtn = document.querySelector('#debug-logs');
    debugBtn?.addEventListener('click', async (e) => {
        e.preventDefault();
        try {
            const resp = await window.apiFetch('/debug/logs?limit=800');
            const result = await resp.json().catch(()=>({}));
            if (!resp.ok) { showToast('Falha ao ler logs', 'error'); return; }
            const text = (result.data?.content) || JSON.stringify(result, null, 2);
            const pre = document.createElement('pre');
            pre.className = 'whitespace-pre-wrap text-xs bg-slate-50 border border-slate-200 rounded p-3 max-h-[60vh] overflow-auto mt-3';
            pre.textContent = text;
            const panel = document.createElement('div');
            panel.className = 'fixed inset-0 bg-black/40 z-50 flex items-center justify-center p-4';
            panel.innerHTML = `<div class="bg-white rounded-lg shadow-xl max-w-4xl w-full p-4">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="text-sm font-semibold text-slate-700">Logs recentes</h3>
                    <button id="close-logs" class="text-sm text-slate-600 hover:text-slate-800">Fechar</button>
                </div>
            </div>`;
            panel.querySelector('div')?.appendChild(pre);
            document.body.appendChild(panel);
            panel.querySelector('#close-logs')?.addEventListener('click', ()=> panel.remove());
        } catch (err) {
            console.error('Erro ao ler logs', err);
            showToast('Erro ao abrir logs', 'error');
        }
    });

    // Listener de storage para atualizar quando outra aba finalizar venda
    try {
        window.addEventListener('storage', async (ev) => {
            if (ev.key === 'adg_sale_updated') {
                try { loadAdminDashboard(); } catch {}
                try { loadCashData(); } catch {}
                const selectedProductId = stockProductSelect?.value || '';
                if (selectedProductId) {
                    try { await loadStockSizes(selectedProductId); } catch {}
                }
                showToast('Dados atualizados após nova venda.', 'success');
            }
        });
    } catch {}

    stockProductSelect?.addEventListener('change', () => {
        loadStockSizes(stockProductSelect.value);
        setStockFeedback('', 'info');
    });

    returnProductSelect?.addEventListener('change', () => {
        loadReturnSizes(returnProductSelect.value);
        setReturnFeedback('', 'info');
    });

    returnReset?.addEventListener('click', () => {
        resetReturnForm();
    });

    returnFocusBtn?.addEventListener('click', () => {
        ensureReturnDate();
        if (returnForm) {
            returnForm.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
        returnProductSelect?.focus();
    });

    returnForm?.addEventListener('submit', submitReturn);

    stockForm?.addEventListener('submit', async (event) => {
        event.preventDefault();
        setStockFeedback('', 'info');

        const productId = stockProductSelect ? stockProductSelect.value : '';
        const size = stockSizeSelect ? stockSizeSelect.value : '';
        const action = stockActionSelect ? stockActionSelect.value : 'set';
        const quantity = Number(stockQuantityInput?.value ?? 0);

        if (!productId || !size) {
            setStockFeedback('Selecione produto e tamanho.', 'error');
            return;
        }

        if ((action === 'set' && quantity < 0) || (action !== 'set' && quantity <= 0)) {
            setStockFeedback('Informe uma quantidade válida.', 'error');
            return;
        }

        try {
            const response = await window.apiFetch(`/products/${productId}/sizes`, {
                method: 'POST',
                body: JSON.stringify({
                    size,
                    mode: action,
                    quantity,
                }),
            });

            const result = await response.json().catch(() => ({}));
            if (!response.ok) {
                const message = result.error?.message ?? 'Falha ao atualizar estoque.';
                setStockFeedback(message, 'error');
                return;
            }

            stockSizes = Array.isArray(result.sizes) ? result.sizes : [];
            renderStockTable();
            setStockFeedback('Estoque atualizado com sucesso.', 'success');
            loadAdminDashboard();
            loadStockSizes(productId);
        } catch (error) {
            console.error('Erro ao atualizar estoque', error);
            setStockFeedback('Erro inesperado ao atualizar estoque.', 'error');
        }
    });

        // --- PDV (Admin) ---
    const apdvProductSelect = document.querySelector('#apdv-product-select');
    const apdvSizeSelect = document.querySelector('#apdv-size-select');
    const apdvSizeInfo = document.querySelector('#apdv-size-info');
    const apdvItemForm = document.querySelector('#apdv-item-form');
    const apdvItemsBody = document.querySelector('#apdv-items-body');
    const apdvTotalEl = document.querySelector('#apdv-total-amount');
    const apdvForm = document.querySelector('#apdv-form');
    const apdvReset = document.querySelector('#apdv-reset');
    const apdvFeedback = document.querySelector('#apdv-feedback');

    let apdvCatalog = [];
    let apdvSizes = [];
    let apdvItems = [];

    function apdvFormat(value) { return Number(value ?? 0).toLocaleString('pt-BR', { minimumFractionDigits: 2 }); }

    function apdvToggleCreditFields(show) {
        const wraps = document.querySelectorAll('[data-apdv-credit]');
        wraps.forEach((el) => {
            if (!(el instanceof HTMLElement)) return;
            if (show) { el.classList.remove('hidden'); } else { el.classList.add('hidden'); }
        });
        if (!show) {
            const inst = document.querySelector('#apdv-installments');
            if (inst) inst.value = '1';
        }
    }

    function apdvPopulateTerminals() {
        const select = document.querySelector('#apdv-terminal');
        if (!select) return;
        select.innerHTML = '<option value="">Selecione</option>';
        (Array.isArray(terminals) ? terminals : []).filter(t => t?.active !== false).forEach((t) => {
            const opt = document.createElement('option'); opt.value = t.id; opt.textContent = t.name; select.appendChild(opt);
        });
    }

    function apdvPopulateBrands() {
        const select = document.querySelector('#apdv-brand');
        if (!select) return;
        select.innerHTML = '<option value="">Selecione</option>';
        (Array.isArray(brands) ? brands : []).filter(b => b?.active !== false).forEach((b) => {
            const opt = document.createElement('option'); opt.value = b.id; opt.textContent = b.name; select.appendChild(opt);
        });
    }

    async function apdvLoadInstallments() {
        const instSel = document.querySelector('#apdv-installments');
        const method = document.querySelector('#apdv-method')?.value || '';
        const term = document.querySelector('#apdv-terminal')?.value || '';
        const brand = document.querySelector('#apdv-brand')?.value || '';
        if (!instSel) return;
        instSel.innerHTML = '<option value="1">1x (à vista)</option>';
        if (method !== 'credito' || !term || !brand) return;
        try {
            const url = `/payment-catalog/fees?terminal_id=${encodeURIComponent(term)}&brand_id=${encodeURIComponent(brand)}`;
            const resp = await window.apiFetch(url);
            const data = await resp.json().catch(()=>({ items: [] }));
            const items = Array.isArray(data.items) ? data.items : [];
            const set = new Set([1]);
            items.forEach((f) => {
                if (String(f.payment_method||'') !== 'credito') return;
                const min = Number(f.installments_min||1); const max = Number(f.installments_max||1);
                for (let i=min; i<=max && i<=24; i++){ set.add(i); }
            });
            const list = Array.from(set).sort((a,b)=>a-b);
            instSel.innerHTML = '';
            list.forEach((n) => {
                const opt = document.createElement('option'); opt.value = String(n);
                opt.textContent = n === 1 ? '1x (à vista)' : `${n}x`;
                instSel.appendChild(opt);
            });
        } catch {}
    }

            async function apdvLoadCatalog(force = false) {
        if (!apdvProductSelect) return;
        if (!force && apdvProductSelect.options.length > 1) return; // já carregado
        const statusEl = document.querySelector('#apdv-catalog-status');
        if (statusEl) { statusEl.textContent = 'Carregando catálogo de produtos...'; statusEl.className = 'mt-1 text-xs text-slate-500'; }
        apdvProductSelect.innerHTML = '<option value="">Carregando produtos...</option>';
        try {
            const response = await window.apiFetch('/products/options');
            const httpStatus = response.status;
            let rawBody = '';
            try { rawBody = await response.clone().text(); } catch {}
            console.debug('PDV Admin /products/options status:', httpStatus, rawBody);
            if (!response.ok) {
                if (statusEl) { statusEl.textContent = `Erro (${httpStatus}) ao carregar produtos.`; statusEl.className = 'mt-1 text-xs text-rose-600'; }
                apdvProductSelect.innerHTML = '<option value="">Erro ao carregar produtos</option>';
                return;
            }
                        const data = await response.json().catch(() => ({ items: [] }));
            apdvCatalog = Array.isArray(data.items) ? data.items : [];
            apdvProductSelect.innerHTML = '<option value="">Selecione um produto</option>';
            const source = apdvCatalog.length > 0 ? apdvCatalog : (Array.isArray(stockProducts) ? stockProducts : []);
            source.forEach((p) => {
                const opt = document.createElement('option');
                opt.value = p.id; opt.textContent = p.sku ? `${p.name} (${p.sku})` : p.name;
                opt.dataset.price = p.sale_price != null ? String(p.sale_price) : '';
                apdvProductSelect.appendChild(opt);
            });
            if (statusEl) {
                const count = source.length;
                statusEl.textContent = count > 0 ? `Produtos carregados: ${count}` : 'Nenhum produto encontrado.';
                statusEl.className = 'mt-1 text-xs ' + (count > 0 ? 'text-slate-500' : 'text-amber-600');
            }
        } catch (e) {
            console.error('PDV Admin: erro ao carregar produtos', e);
            apdvProductSelect.innerHTML = '<option value="">Erro ao carregar produtos</option>';
            if (statusEl) { statusEl.textContent = 'Erro de rede ao carregar produtos.'; statusEl.className = 'mt-1 text-xs text-rose-600'; }
        }
    }

    async function apdvLoadSizes(productId) {
        if (!apdvSizeSelect) return;
        if (!productId) { apdvSizes = []; apdvSizeSelect.innerHTML = '<option value="">Selecione um produto</option>'; apdvSizeSelect.disabled = true; return; }
        apdvSizeSelect.disabled = true; apdvSizeSelect.innerHTML = '<option value="">Carregando tamanhos...</option>';
        try {
            const response = await window.apiFetch(`/products/${productId}/sizes`);
            const data = await response.json();
            apdvSizes = Array.isArray(data.sizes) ? data.sizes : [];
            apdvSizeSelect.innerHTML = '<option value="">Selecione um tamanho</option>';
            const groups = new Map();
            apdvSizes.forEach((entry) => {
                const groupKey = entry.group ?? 'outros';
                const opt = document.createElement('option');
                opt.value = entry.size; opt.textContent = `${entry.size} (disp.: ${entry.quantity})`;
                if (groupKey === 'outros') { apdvSizeSelect.appendChild(opt); return; }
                const label = groupKey === 'feminina' ? 'Feminina' : groupKey === 'masculina' ? 'Masculina' : 'Outros';
                let og = groups.get(groupKey);
                if (!og) { og = document.createElement('optgroup'); og.label = label; groups.set(groupKey, og); }
                og.appendChild(opt);
            });
            groups.forEach((og) => apdvSizeSelect.appendChild(og));
            apdvSizeSelect.disabled = false; apdvSizeSelect.value = '';
        } catch { apdvSizeSelect.innerHTML = '<option value="">Erro ao carregar tamanhos</option>'; }
    }

    function apdvFindProduct(id) { return apdvCatalog.find((p) => p.id === id) || null; }

        function apdvRenderItems() {
        let total = 0;
        if (apdvItems.length === 0) {
            apdvItemsBody.innerHTML = '<tr><td colspan="6" class="px-4 py-3 text-slate-500 text-center">Nenhum item adicionado.</td></tr>';
            apdvTotalEl.textContent = '0,00';
            const td = document.querySelector('#apdv-total-discount'); if (td) td.textContent = '0,00';
            return;
        }
        apdvItemsBody.innerHTML = '';
        apdvItems.forEach((item, idx) => {
            const unit = Number(item.unit_price ?? 0);
            const line = unit * item.qty; total += line;
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td class="px-4 py-2">${item.name || item.product_id}</td>
                <td class="px-4 py-2 text-center">${item.size}</td>
                <td class="px-4 py-2 text-right">${item.qty}</td>
                <td class="px-4 py-2 text-right">R$ ${apdvFormat(unit)}</td>
                <td class="px-4 py-2 text-right">R$ ${apdvFormat(line)}</td>
                <td class="px-4 py-2 text-right"><button data-apdv-remove="${idx}" class="text-sm text-rose-600 hover:text-rose-500">Remover</button></td>`;
            apdvItemsBody.appendChild(tr);
        });
        apdvTotalEl.textContent = apdvFormat(total);
        // Atualiza o total com desconto (pré-visualização)
        apdvUpdateDiscountPreview();
    }

    function apdvUpdateDiscountPreview() {
        const discInput = document.querySelector('#apdv-discount');
        const td = document.querySelector('#apdv-total-discount');
        if (!discInput || !td) return;
        const percent = Math.max(0, Math.min(100, Number(discInput.value || '0')));
        const gross = apdvItems.reduce((acc, i) => acc + (Number(i.unit_price ?? 0) * Number(i.qty ?? 0)), 0);
        const discountValue = (percent / 100) * gross;
        const previewTotal = Math.max(0, gross - discountValue);
        td.textContent = apdvFormat(previewTotal);
    }

    apdvProductSelect?.addEventListener('focus', () => { apdvLoadCatalog(false); });
    apdvProductSelect?.addEventListener('click', () => { apdvLoadCatalog(false); });
    apdvProductSelect?.addEventListener('change', () => { apdvLoadSizes(apdvProductSelect.value); apdvSizeInfo.textContent=''; });
        apdvSizeSelect?.addEventListener('change', () => {
        const size = apdvSizeSelect.value;
        const entry = apdvSizes.find((s)=>s.size===size);
        apdvSizeInfo.textContent = entry ? `Disponível: ${entry.quantity}` : '';
    });

    // Atualiza pré-visualização ao alterar desconto
    document.querySelector('#apdv-discount')?.addEventListener('input', apdvUpdateDiscountPreview);
    document.querySelector('#apdv-method')?.addEventListener('change', () => {
        const method = document.querySelector('#apdv-method')?.value || '';
        apdvToggleCreditFields(method === 'credito');
        if (method === 'credito') { apdvPopulateTerminals(); apdvPopulateBrands(); apdvLoadInstallments(); }
    });
    document.querySelector('#apdv-terminal')?.addEventListener('change', apdvLoadInstallments);
    document.querySelector('#apdv-brand')?.addEventListener('change', apdvLoadInstallments);

    apdvItemForm?.addEventListener('submit', (e) => {
        e.preventDefault();
        const pid = apdvProductSelect?.value || '';
        const size = apdvSizeSelect?.value || '';
        const qty = Number(document.querySelector('#apdv-qty')?.value || '1');
        if (!pid || !size || qty <= 0) { apdvFeedback.textContent='Selecione produto/tamanho e quantidade válida.'; return; }
        const p = apdvFindProduct(pid);
        apdvItems.push({ product_id: pid, size, qty, name: p?.name || null, unit_price: p?.sale_price || 0 });
        apdvRenderItems();
        document.querySelector('#apdv-qty').value = '1';
        apdvSizeSelect.value = ''; apdvSizeInfo.textContent = '';
        apdvFeedback.textContent='Item adicionado.';
    });

    apdvItemsBody?.addEventListener('click', (e) => {
        const btn = e.target instanceof HTMLButtonElement ? e.target : null;
        if (!btn?.dataset.apdvRemove) return;
        const idx = Number(btn.dataset.apdvRemove);
        apdvItems.splice(idx,1); apdvRenderItems(); apdvFeedback.textContent='Item removido.';
    });

    apdvReset?.addEventListener('click', () => { apdvItems = []; apdvRenderItems(); apdvFeedback.textContent='Itens limpos.'; });

        apdvForm?.addEventListener('submit', async (e) => {
        e.preventDefault();
        if (apdvItems.length === 0) { apdvFeedback.textContent='Adicione itens.'; return; }
        const submitBtn = apdvForm.querySelector('button[type="submit"]');
        submitBtn.disabled = true; apdvFeedback.textContent='Registrando venda...';
        try {
            const draftRes = await window.apiFetch('/sales', { method: 'POST', body: JSON.stringify({
                customer_id: document.querySelector('#apdv-customer')?.value || null,
                items: apdvItems.map(i=>({ product_id: i.product_id, size: i.size, qty: i.qty })),
            })});
            const draft = await draftRes.json();
            if (!draftRes.ok) throw new Error(draft.error?.message || 'Falha no rascunho.');

            const method = document.querySelector('#apdv-method')?.value || '';
            const discount = Number(document.querySelector('#apdv-discount')?.value || '0');
            const installments = method === 'credito' ? Number(document.querySelector('#apdv-installments')?.value || '1') : 1;
            const terminalId = method === 'credito' ? (document.querySelector('#apdv-terminal')?.value || null) : null;
            const brandId = method === 'credito' ? (document.querySelector('#apdv-brand')?.value || null) : null;

            // Pré-visualização do total com desconto (sem taxas) para feedback imediato
            const gross = apdvItems.reduce((acc, i) => acc + (Number(i.unit_price ?? 0) * Number(i.qty ?? 0)), 0);
            const discountValue = Math.max(0, Math.min(100, discount)) / 100 * gross;
            const previewTotal = Math.max(0, gross - discountValue);
            apdvFeedback.textContent = `Aplicando desconto de ${discount.toFixed(1)}% (− R$ ${apdvFormat(discountValue)}). Total estimado: R$ ${apdvFormat(previewTotal)}`;

            const chkRes = await window.apiFetch(`/sales/${draft.sale_id}/checkout`, { method: 'POST', body: JSON.stringify({
                payment_method: method,
                discount_percent: discount,
                installments,
                terminal_id: terminalId,
                brand_id: brandId,
            })});
            const out = await chkRes.json();
            if (!chkRes.ok) throw new Error(out.error?.message || 'Falha ao finalizar.');


            // Atualiza estoque exibido (tamanhos) do produto atualmente selecionado na seção de estoque
            const selectedProductId = stockProductSelect?.value || '';

            apdvItems = []; apdvRenderItems(); apdvForm.reset();
            apdvFeedback.textContent = `Venda finalizada. Total: R$ ${apdvFormat(out.total ?? 0)}`;

            if (selectedProductId) {
                try { await loadStockSizes(selectedProductId); } catch {}
            }
            // Dispara refresh de painéis e Kanban (outras abas)
            try { loadAdminDashboard(); } catch {}
            try { loadCashData(); } catch {}
            try { localStorage.setItem('adg_sale_updated', String(Date.now())); } catch {}
        } catch (err) {
            apdvFeedback.textContent = err?.message || 'Erro no PDV.';
        } finally { submitBtn.disabled = false; }
    });

    // inicializações
        // Inicializa primeiro estoque, depois tenta catálogo do PDV (e re-tenta em seguida)
    apdvRenderItems();
    loadStockProducts();
    apdvLoadCatalog(true);
    setTimeout(() => apdvLoadCatalog(true), 800);

    setReportFeedback('', 'info');
    setCashAdjustFeedback('', 'info');
    loadPaymentConfigs();
    loadBrands();
    loadTerminals();
    loadCashData();
    loadAdminCommissionVendors();
    loadAdminDashboard();
    loadStockProducts();
});
</script>
</div>
<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/base.php';
?>



