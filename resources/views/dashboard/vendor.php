<?php

$title = 'Painel do Vendedor';
ob_start();
?>
<section data-section="dashboard" class="grid gap-6">
    <div class="grid gap-4 sm:grid-cols-3">
        <div class="rounded-xl border border-blue-100 bg-white p-5 shadow-sm">
            <p class="text-sm uppercase tracking-wide text-slate-500">Vendas Hoje</p>
            <p class="mt-2 text-3xl font-semibold text-blue-700">R$ <span id="today-total">0,00</span></p>
        </div>
        <div class="rounded-xl border border-blue-100 bg-white p-5 shadow-sm">
            <p class="text-sm uppercase tracking-wide text-slate-500">Vendas no Mês</p>
            <p class="mt-2 text-3xl font-semibold text-blue-700">R$ <span id="month-total">0,00</span></p>
        </div>
        <div class="rounded-xl border border-yellow-100 bg-white p-5 shadow-sm">
            <p class="text-sm uppercase tracking-wide text-slate-500">Vendas em Aberto</p>
            <p class="mt-2 text-3xl font-semibold text-yellow-500"><span id="open-count">0</span></p>
        </div>
    </div>

    <div class="rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
            <h2 class="text-lg font-semibold text-slate-700">Kanban de Vendas</h2>
            <a data-route="pdv" class="rounded-full bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-500 transition">
                Abrir PDV
            </a>
        </div>
        <div id="kanban-placeholder" class="grid gap-6 px-6 py-6 lg:grid-cols-5">
            <?php
            $columns = [
                ['label' => 'Aberta', 'status' => 'aberta', 'style' => 'border-blue-200 bg-blue-50'],
                ['label' => 'Pagamento Pendente', 'status' => 'pagamento_pendente', 'style' => 'border-yellow-200 bg-yellow-50'],
                ['label' => 'Paga', 'status' => 'paga', 'style' => 'border-emerald-200 bg-emerald-50'],
                ['label' => 'Entregue', 'status' => 'entregue', 'style' => 'border-indigo-200 bg-indigo-50'],
                ['label' => 'Cancelada', 'status' => 'cancelada', 'style' => 'border-rose-200 bg-rose-50'],
            ];
            foreach ($columns as $column): ?>
                <div class="rounded-xl border <?= $column['style'] ?> p-4">
                    <h3 class="mb-3 text-sm font-semibold uppercase tracking-wide text-slate-600"><?= $column['label'] ?></h3>
                    <div class="space-y-3 text-sm text-slate-600" data-kanban-column data-status="<?= $column['status'] ?>">
                        <p class="rounded-lg bg-white/70 p-3 shadow-sm">Nenhuma venda ainda.</p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section data-section="estoque" class="hidden">
    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm space-y-4">
        <h2 class="text-xl font-semibold text-slate-700">Estoque</h2>
        <p class="text-sm text-slate-600">
            A visualização completa do estoque está disponível para o administrador. Se precisar ajustar quantidades ou consultar itens,
            fale com o responsável pelo estoque ou utilize o formulário de PDV para registrar as saídas.
</p>
    </div>
    <div class="mt-6 rounded-xl border border-slate-200 bg-white p-6 shadow-sm space-y-6">
        <div class="flex items-center justify-between">
            <h3 class="text-lg font-semibold text-slate-700">Estoque Atual</h3>
            <span class="text-xs uppercase tracking-wide text-slate-400">Somente leitura</span>
        </div>
        <p id="vendor-inventory-status" class="text-sm text-slate-500">Carregando estoque...</p>
        <div class="overflow-x-auto rounded-lg border border-slate-200">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-slate-600">
                    <tr>
                        <th class="px-4 py-2">Produto</th>
                        <th class="px-4 py-2">Tamanho</th>
                        <th class="px-4 py-2 text-right">Quantidade</th>
                    </tr>
                </thead>
                <tbody id="vendor-inventory-body" class="divide-y divide-slate-100">
                    <tr>
                        <td colspan="3" class="px-4 py-3 text-slate-500 text-center">Carregando...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</section>

<section data-section="pdv" class="hidden space-y-6">
    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="text-lg font-semibold text-slate-700">Registrar venda</h2>
        <p class="text-sm text-slate-500 mb-4">
            Informe os itens abaixo usando o identificador do produto (UUID). Você pode copiar o ID direto do Supabase ou solicitar ao administrador.
        </p>

        <form id="pdv-item-form" class="grid gap-4 md:grid-cols-4">
            <div class="md:col-span-2">
                <label class="text-sm font-medium text-slate-600" for="pdv-product-select">Produto</label>
                <select id="pdv-product-select" name="product_id" class="mt-1 w-full rounded-lg border border-slate-200 px-4 py-2 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
                    <option value="">Selecione um produto</option>
                </select>
            </div>
            <div>
                <label class="text-sm font-medium text-slate-600" for="pdv-size-select">Tamanho</label>
                <select id="pdv-size-select" name="size" class="mt-1 w-full rounded-lg border border-slate-200 px-4 py-2 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100" disabled>
                    <option value="">Selecione um produto primeiro</option>
                </select>
                <p id="pdv-size-info" class="mt-1 text-xs text-slate-500"></p>
            </div>
            <div>
                <label class="text-sm font-medium text-slate-600" for="pdv-qty">Quantidade</label>
                <input id="pdv-qty" name="qty" type="number" min="1" value="1" class="mt-1 w-full rounded-lg border border-slate-200 px-4 py-2 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100" required>
            </div>
            <div class="md:col-span-4 flex justify-end">
                <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2 text-white font-medium hover:bg-blue-500 transition">
                    Adicionar item
                </button>
            </div>
        </form>

        <div class="mt-6">
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
                    <tbody id="pdv-items-body" class="divide-y divide-slate-100">
                        <tr>
                            <td colspan="6" class="px-4 py-3 text-slate-500 text-center">Nenhum item adicionado.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="mt-4 grid gap-1 text-sm text-slate-600">
                <div class="flex items-center justify-between">
                    <span>Total estimado:</span>
                    <span class="text-lg font-semibold text-slate-800">R$ <span id="pdv-total-amount">0,00</span></span>
                </div>
                <p class="text-xs text-slate-500">Descontos são aplicados somente pelo administrador.</p>
            </div>
        </div>

        <form id="pdv-form" class="mt-6 space-y-4">
            <div>
                <label class="text-sm font-medium text-slate-600" for="pdv-customer">Cliente (opcional)</label>
                <input id="pdv-customer" name="customer_id" class="mt-1 w-full rounded-lg border border-slate-200 px-4 py-2 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100" placeholder="UUID do cliente (deixe em branco para balcão)">
            </div>
            <div>
                <label class="text-sm font-medium text-slate-600" for="pdv-method">Método de pagamento</label>
                <select id="pdv-method" class="mt-1 w-full rounded-lg border border-slate-200 px-4 py-2 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100" required>
                    <option value="">Selecione</option>
                    <option value="pix">PIX</option>
                    <option value="credito">Crédito</option>
                    <option value="debito">Débito</option>
                    <option value="dinheiro">Dinheiro</option>
                    <option value="transferencia">Transferência</option>
                </select>
            </div>
            <div data-pdv-credit="wrap-terminal" class="hidden">
                <label class="text-sm font-medium text-slate-600" for="pdv-terminal">Maquininha</label>
                <select id="pdv-terminal" class="mt-1 w-full rounded-lg border border-slate-200 px-4 py-2 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
                    <option value="">Selecione</option>
                </select>
            </div>
            <div data-pdv-credit="wrap-brand" class="hidden">
                <label class="text-sm font-medium text-slate-600" for="pdv-brand">Bandeira</label>
                <select id="pdv-brand" class="mt-1 w-full rounded-lg border border-slate-200 px-4 py-2 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
                    <option value="">Selecione</option>
                </select>
            </div>
            <div data-pdv-credit="wrap-installments" class="hidden">
                <label class="text-sm font-medium text-slate-600" for="pdv-installments">Parcelas</label>
                <select id="pdv-installments" class="mt-1 w-full rounded-lg border border-slate-200 px-4 py-2 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
                    <option value="1">1x (à vista)</option>
                </select>
            </div>
            <div class="flex items-center gap-3">
                <button type="submit" class="rounded-lg bg-emerald-600 px-4 py-2 text-white font-medium hover:bg-emerald-500 transition">
                    Finalizar venda
                </button>
                <button type="button" id="pdv-reset" class="rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-600 hover:bg-slate-50 transition">
                    Limpar itens
                </button>
                <span id="pdv-feedback" class="text-sm text-slate-500"></span>
            </div>
        </form>
    </div>

    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-slate-700">Histórico das Últimas Vendas</h2>
            <button id="pdv-refresh" class="text-sm font-medium text-blue-600 hover:text-blue-500">Atualizar</button>
        </div>
        <div class="overflow-x-auto rounded-lg border border-slate-200">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-slate-600">
                    <tr>
                        <th class="px-4 py-2">ID</th>
                        <th class="px-4 py-2">Status</th>
                        <th class="px-4 py-2">Subtotal</th>
                        <th class="px-4 py-2">Criada em</th>
                    </tr>
                </thead>
                <tbody id="pdv-sales-body" class="divide-y divide-slate-100">
                    <tr>
                        <td colspan="4" class="px-4 py-3 text-slate-500 text-center">Nenhuma venda criada ainda.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</section>

<section data-section="fluxo" class="hidden space-y-6">
    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm space-y-4">
        <h2 class="text-xl font-semibold text-slate-700">Fluxo de Caixa</h2>
        <p class="text-sm text-slate-600">
            Acompanhe abaixo o resumo das movimentacoes registradas no caixa pela equipe.
        </p>
        <div id="cash-summary-status" class="text-sm text-slate-500">Carregando resumo do fluxo...</div>
        <div data-cash-summary="cards" class="mt-2 grid gap-4 md:grid-cols-3 hidden">
            <div class="rounded-lg border border-blue-100 bg-blue-50 p-4 shadow-sm">
                <p class="text-xs uppercase tracking-wide text-blue-700">Recebido bruto</p>
                <p class="mt-2 text-2xl font-semibold text-blue-700">R$ <span data-cash-total="gross">0,00</span></p>
            </div>
            <div class="rounded-lg border border-amber-100 bg-amber-50 p-4 shadow-sm">
                <p class="text-xs uppercase tracking-wide text-amber-700">Taxas</p>
                <p class="mt-2 text-2xl font-semibold text-amber-700">R$ <span data-cash-total="fee">0,00</span></p>
            </div>
            <div class="rounded-lg border border-emerald-100 bg-emerald-50 p-4 shadow-sm">
                <p class="text-xs uppercase tracking-wide text-emerald-700">Líquido</p>
                <p class="mt-2 text-2xl font-semibold text-emerald-700">R$ <span data-cash-total="net">0,00</span></p>
            </div>
        </div>
    </div>
    <div class="rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
            <h3 class="text-lg font-semibold text-slate-700">Totais por método de pagamento</h3>
        </div>
        <div class="overflow-x-auto px-6 py-6">
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
                <tbody id="cash-summary-table" class="divide-y divide-slate-100">
                    <tr>
                        <td colspan="5" class="px-4 py-3 text-slate-500 text-center">Carregando dados do fluxo...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</section>

<section data-section="comissao" class="hidden space-y-6">
    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm space-y-4">
        <h2 class="text-xl font-semibold text-slate-700">Comissao</h2>
        <p class="text-sm text-slate-600">
            Comissao de 2% sobre o valor bruto (subtotal) das vendas finalizadas do vendedor, com remuneracao base mensal fixa.
        </p>
        <div id="commission-status" class="text-sm text-slate-500">Carregando comissao...</div>
        <div data-commission-summary="cards" class="mt-2 grid gap-4 md:grid-cols-4 hidden">
            <div class="rounded-lg border border-blue-100 bg-blue-50 p-4 shadow-sm">
                <p class="text-xs uppercase tracking-wide text-blue-700">Comissao de hoje</p>
                <p class="mt-2 text-2xl font-semibold text-blue-700">R$ <span data-commission-total="today">0,00</span></p>
            </div>
            <div class="rounded-lg border border-indigo-100 bg-indigo-50 p-4 shadow-sm">
                <p class="text-xs uppercase tracking-wide text-indigo-700">Comissao do mes</p>
                <p class="mt-2 text-2xl font-semibold text-indigo-700">R$ <span data-commission-total="month">0,00</span></p>
            </div>
            <div class="rounded-lg border border-amber-100 bg-amber-50 p-4 shadow-sm">
                <p class="text-xs uppercase tracking-wide text-amber-700">Remuneracao base</p>
                <p class="mt-2 text-2xl font-semibold text-amber-700">R$ <span data-commission-total="base">0,00</span></p>
            </div>
            <div class="rounded-lg border border-emerald-100 bg-emerald-50 p-4 shadow-sm">
                <p class="text-xs uppercase tracking-wide text-emerald-700">Remuneracao base + comissao (2%)</p>
                <p class="mt-2 text-2xl font-semibold text-emerald-700">R$ <span data-commission-total="total-with-base">0,00</span></p>
            </div>
        </div>
    </div>
    <div class="rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
            <h3 class="text-lg font-semibold text-slate-700">Itens comissionados recentes</h3>
        </div>
        <div class="overflow-x-auto px-6 py-6">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-slate-600">
                    <tr>
                        <th class="px-4 py-2">Data</th>
                        <th class="px-4 py-2">Venda</th>
                        <th class="px-4 py-2">Produto</th>
                        <th class="px-4 py-2">Tam.</th>
                        <th class="px-4 py-2 text-right">Qtd</th>
                        <th class="px-4 py-2 text-right">Bruto</th>
                        <th class="px-4 py-2 text-right">Comissao (2%)</th>
                    </tr>
                </thead>
                <tbody id="commission-table-body" class="divide-y divide-slate-100">
                    <tr>
                        <td colspan="7" class="px-4 py-3 text-slate-500 text-center">Carregando dados de comissao...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    <div class="rounded-xl border border-amber-100 bg-amber-50 p-4 text-amber-900">
        <p class="text-xs">
            Observacao: valores exibidos sao calculados em tempo real (2% do subtotal das vendas com status Paga/Entregue) e lancados como ajuste negativo no Fluxo de Caixa. A comissao do mes nao inclui a remuneracao base; o total "Base + comissao" soma os dois.
        </p>
    </div>
    
</section>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // Mensagem especial para a vendedora Laís
    try {
        function base64UrlDecode(input) {
            try {
                const pad = input.length % 4 === 2 ? '==' : input.length % 4 === 3 ? '=' : '';
                const b64 = input.replace(/-/g, '+').replace(/_/g, '/') + pad;
                return atob(b64);
            } catch { return ''; }
        }

        function getEmailFromToken() {
            const token = (typeof getStoredValue === 'function') ? getStoredValue('access_token') : (sessionStorage.getItem('access_token') || localStorage.getItem('access_token'));
            if (!token || typeof token !== 'string') return null;
            const parts = token.split('.');
            if (parts.length < 2) return null;
            try {
                const json = base64UrlDecode(parts[1]);
                const payload = JSON.parse(json);
                return (
                    payload?.email ||
                    payload?.user_metadata?.email ||
                    payload?.raw_user_meta_data?.email ||
                    null
                );
            } catch { return null; }
        }

        const storedEmail = (typeof getStoredValue === 'function') ? getStoredValue('user_email') : (sessionStorage.getItem('user_email') || localStorage.getItem('user_email'));
        const email = (storedEmail && storedEmail !== '') ? storedEmail : (getEmailFromToken() || '');
        if (email && typeof email === 'string') {
            try { sessionStorage.setItem('user_email', email); } catch {}
            try { localStorage.setItem('user_email', email); } catch {}
        }

        if (String(email).toLowerCase() === 'lais@ateliedogenio.com.br') {
            const host = document.querySelector('main') || document.querySelector('section[data-section]:not(.hidden)') || document.querySelector('section[data-section="dashboard"]');
            if (host && !document.getElementById('lais-shoutout')) {
                const basePrefix = (window.__BASE_PATH__ || '').replace(/\/+$/, '');
                const origin = (window.location && window.location.origin) ? window.location.origin : '';
                const imgCandidates = [
                    'https://ateliedogenio.com.br/vendas/imagens/familia.jpg',
                    origin ? (origin + '/vendas/imagens/familia.jpg') : null,
                    basePrefix + '/imagens/familia.jpg',
                    basePrefix + '/imagens/familia.jpeg',
                    basePrefix + '/imagens/famlia.jpeg',
                    basePrefix + '/imagens/famlia.jpg'
                ].filter(Boolean);

                const banner = document.createElement('div');
                banner.setAttribute('id', 'lais-shoutout');
                banner.className = 'rounded-2xl p-6 md:p-10 shadow-xl bg-gradient-to-r from-blue-600 via-indigo-600 to-amber-400 text-white';
                banner.innerHTML = `
                    <div class="flex items-center gap-4">
                        <div class="relative h-14 w-14">
                            <img id="lais-avatar-img" src="" alt="Família" class="h-14 w-14 rounded-full ring-2 ring-white/50 object-cover shadow" loading="lazy" decoding="async" referrerpolicy="no-referrer" />
                            <div id="lais-avatar-fallback" class="absolute inset-0 flex items-center justify-center rounded-full bg-white/20 text-2xl font-bold">LG</div>
                        </div>
                        <div>
                            <h2 class="text-2xl md:text-4xl font-extrabold leading-tight">Laís, nada floresce por acaso.</h2>
                            <p class="mt-2 text-base md:text-lg text-white/90">É sua força que faz tudo crescer - obrigado por construir comigo o que antes eu só sonhava.</p>
                        </div>
                    </div>`;

                try {
                    const footer = document.createElement('div');
                    footer.className = 'mt-4 pt-3 border-t border-white/20';
                    footer.innerHTML = '<p class="text-sm md:text-base text-white/90 italic text-right">Eu te amo, Lucas Júnior</p>';
                    banner.appendChild(footer);
                } catch {}

                host.prepend(banner);

                try {
                    const img = banner.querySelector('#lais-avatar-img');
                    const fb = banner.querySelector('#lais-avatar-fallback');
                    if (img) {
                        let idx = 0;
                        function tryNext() {
                            if (idx >= imgCandidates.length) {
                                img.classList.add('hidden');
                                fb?.classList.remove('hidden');
                                return;
                            }
                            img.src = imgCandidates[idx] + '?v=' + Date.now();
                            idx++;
                        }
                        img.addEventListener('load', () => { img.classList.remove('hidden'); fb?.classList.add('hidden'); });
                        img.addEventListener('error', () => { tryNext(); });
                        tryNext();
                    }
                } catch {}
            }
        }
    } catch {}

    // Toast simples
    function showToast(message, tone = 'info') {
        const wrap = document.createElement('div');
        wrap.className = 'fixed bottom-4 right-4 z-50';
        const bg = tone === 'success' ? 'bg-emerald-600' : tone === 'error' ? 'bg-rose-600' : 'bg-slate-800';
        wrap.innerHTML = `<div class="${bg} text-white text-sm px-4 py-2 rounded shadow-lg">${message}</div>`;
        document.body.appendChild(wrap);
        setTimeout(() => { wrap.remove(); }, 3000);
    }

    try {
        window.addEventListener('storage', (e) => {
            if (e.key === 'adg_sale_updated') {
                loadDashboard();
                loadSalesList();
                loadCashSummary();
                try { loadCommission().catch(()=>{}); } catch {}
                showToast('Dados atualizados após nova venda.', 'success');
            }
        });
    } catch {}

    const sections = document.querySelectorAll('[data-section]');
    const productSelect = document.querySelector('#pdv-product-select');
    const sizeSelect = document.querySelector('#pdv-size-select');
    const sizeInfo = document.querySelector('#pdv-size-info');
    const sectionParam = new URLSearchParams(window.location.search).get('section') ?? 'pdv';

    function showSection(key) {
        sections.forEach((section) => {
            section.classList.toggle('hidden', section.dataset.section !== key);
        });
    }

    showSection(sectionParam);
    if (sectionParam === 'estoque') { try { loadVendorInventoryAll(); } catch {} }

    const kanbanColumns = {};
    let kanbanPlaceholderTemplate = '';
    const statusDisplay = {
        aberta: 'Aberta',
        pagamento_pendente: 'Pagamento pendente',
        paga: 'Paga',
        entregue: 'Entregue',
        cancelada: 'Cancelada',
    };
    const allowedTransitions = {
        aberta: ['pagamento_pendente', 'paga', 'cancelada'],
        pagamento_pendente: ['paga', 'cancelada'],
        paga: ['entregue', 'cancelada'],
        entregue: [],
        cancelada: [],
    };
    let draggedSaleId = null;
    let draggedSaleStatus = null;

    document.querySelectorAll('[data-kanban-column]').forEach((column) => {
        const status = column.dataset.status;
        if (!kanbanPlaceholderTemplate) {
            kanbanPlaceholderTemplate = column.innerHTML;
        }
        if (!status) {
            return;
        }
        kanbanColumns[status] = column;
        column.addEventListener('dragover', handleDragOver);
        column.addEventListener('dragleave', handleDragLeave);
        column.addEventListener('drop', handleDrop);
    });

    const itemsTableBody = document.querySelector('#pdv-items-body');
    const totalElement = document.querySelector('#pdv-total-amount');
    const pdvFeedback = document.querySelector('#pdv-feedback');
    const saleTableBody = document.querySelector('#pdv-sales-body');
    const saleRefreshButton = document.querySelector('#pdv-refresh');
    const resetButton = document.querySelector('#pdv-reset');
    const pdvForm = document.querySelector('#pdv-form');
    const pdvMethod = document.querySelector('#pdv-method');
    const pdvTerminal = document.querySelector('#pdv-terminal');
    const pdvBrand = document.querySelector('#pdv-brand');
    const pdvInstallments = document.querySelector('#pdv-installments');
    const pdvCreditWraps = document.querySelectorAll('[data-pdv-credit]');
    const itemForm = document.querySelector('#pdv-item-form');
    const cashSummaryCards = document.querySelector('[data-cash-summary="cards"]');
    const cashSummaryStatus = document.querySelector('#cash-summary-status');
    const cashSummaryTable = document.querySelector('#cash-summary-table');
    const cashTotals = {
        gross: document.querySelector('[data-cash-total="gross"]'),
        fee: document.querySelector('[data-cash-total="fee"]'),
        net: document.querySelector('[data-cash-total="net"]'),
    };

    // Comissao (somente leitura)
    const commissionSummaryCards = document.querySelector('[data-commission-summary="cards"]');
    const commissionStatus = document.querySelector('#commission-status');
    const commissionTableBody = document.querySelector('#commission-table-body');
    const commissionTotals = {
        today: document.querySelector('[data-commission-total="today"]'),
        month: document.querySelector('[data-commission-total="month"]'),
        base: document.querySelector('[data-commission-total="base"]'),
        totalWithBase: document.querySelector('[data-commission-total="total-with-base"]'),
    };

    // Remover campo de cliente para uma venda simples (evita erro de UUID)
    try {
        const customerInput = document.querySelector('#pdv-customer');
        if (customerInput && customerInput.closest('div')) {
            customerInput.closest('div').remove();
        }
    } catch {}

    // Estoque (somente leitura)
    const vendorInventoryStatus = document.querySelector('#vendor-inventory-status');
    const vendorInventoryBody = document.querySelector('#vendor-inventory-body');

    let productCatalog = [];
    let currentSizes = [];
    let currentItems = [];
    let brands = [];
    let terminals = [];
    const currencyOptions = { minimumFractionDigits: 2 };

    function formatCurrency(value) {
        return Number(value ?? 0).toLocaleString('pt-BR', currencyOptions);
    }

    function toggleCreditFields(show) {
        pdvCreditWraps.forEach((el) => {
            if (!(el instanceof HTMLElement)) return;
            if (show) el.classList.remove('hidden'); else el.classList.add('hidden');
        });
        if (!show && pdvInstallments) pdvInstallments.value = '1';
    }

    async function loadCreditOptions() {
        try {
            const resp = await window.apiFetch('/payment-catalog/credit-options');
            const data = await resp.json().catch(()=>({terminals:[],brands:[]}));
            terminals = Array.isArray(data.terminals) ? data.terminals : [];
            brands = Array.isArray(data.brands) ? data.brands : [];
            if (pdvTerminal) {
                pdvTerminal.innerHTML = '<option value="">Selecione</option>';
                terminals.filter(t=>t?.active!==false).forEach((t)=>{
                    const opt = document.createElement('option'); opt.value = t.id; opt.textContent = t.name; pdvTerminal.appendChild(opt);
                });
                // Auto-seleciona quando houver apenas 1 opção
                const activeTerms = terminals.filter(t=>t?.active!==false);
                if (activeTerms.length === 1) {
                    pdvTerminal.value = activeTerms[0].id;
                }
            }
            if (pdvBrand) {
                pdvBrand.innerHTML = '<option value="">Selecione</option>';
                brands.filter(b=>b?.active!==false).forEach((b)=>{
                    const opt = document.createElement('option'); opt.value = b.id; opt.textContent = b.name; pdvBrand.appendChild(opt);
                });
                const activeBrands = brands.filter(b=>b?.active!==false);
                if (activeBrands.length === 1) {
                    pdvBrand.value = activeBrands[0].id;
                }
            }
            // Se já houver método crédito selecionado, tenta carregar parcelas automaticamente
            if ((pdvMethod?.value || '') === 'credito') {
                await loadInstallments();
            }
        } catch {}
    }

    async function loadInstallments() {
        if (!pdvInstallments) return;
        const method = pdvMethod?.value || '';
        const term = pdvTerminal?.value || '';
        const brand = pdvBrand?.value || '';
        pdvInstallments.innerHTML = '<option value="1">1x (à vista)</option>';
        if (method !== 'credito' || !term || !brand) return;
        try {
            const url = `/payment-catalog/fees?terminal_id=${encodeURIComponent(term)}&brand_id=${encodeURIComponent(brand)}`;
            const resp = await window.apiFetch(url);
            const data = await resp.json().catch(()=>({items:[]}));
            const items = Array.isArray(data.items) ? data.items : [];
            const set = new Set([1]);
            items.forEach((f)=>{
                if (String(f.payment_method||'') !== 'credito') return;
                const min = Number(f.installments_min||1); const max = Number(f.installments_max||1);
                for (let i=min;i<=max && i<=24;i++){ set.add(i); }
            });
            const list = Array.from(set).sort((a,b)=>a-b);
            pdvInstallments.innerHTML = '';
            list.forEach((n)=>{
                const opt = document.createElement('option'); opt.value = String(n); opt.textContent = n===1?'1x (à vista)':`${n}x`; pdvInstallments.appendChild(opt);
            });
        } catch {}
    }

    function resetKanbanColumns() {
        Object.values(kanbanColumns).forEach((column) => {
            column.innerHTML = kanbanPlaceholderTemplate || '<p class="rounded-lg bg-white/70 p-3 shadow-sm">Nenhuma venda ainda.</p>';
            column.dataset.hasSales = 'false';
            column.classList.remove('ring-2', 'ring-blue-400', 'ring-offset-2');
        });
    }

    function renderKanban(sales) {
        resetKanbanColumns();

        const sorted = Array.isArray(sales)
            ? [...sales].sort((a, b) => {
                const left = new Date(b.created_at ?? 0).valueOf();
                const right = new Date(a.created_at ?? 0).valueOf();
                return left - right;
            })
            : [];

        sorted.forEach((sale) => {
            const status = sale.status ?? 'aberta';
            const column = kanbanColumns[status];
            if (!column) {
                return;
            }

            if (column.dataset.hasSales !== 'true') {
                column.innerHTML = '';
                column.dataset.hasSales = 'true';
            }

            const card = createKanbanCard(sale);
            column.appendChild(card);
        });

        Object.values(kanbanColumns).forEach((column) => {
            if (column.dataset.hasSales === 'true') {
                column.dataset.hasSales = 'false';
                return;
            }

            column.innerHTML = kanbanPlaceholderTemplate || column.innerHTML;
        });
    }

    function createKanbanCard(sale) {
        const card = document.createElement('div');
        card.className = 'rounded-lg bg-white p-3 shadow-sm cursor-grab';
        card.draggable = true;

        const saleId = sale.id ?? '';
        const status = sale.status ?? 'aberta';
        const label = sale.label ?? `Venda ${statusDisplay[status] ?? status}`;
        const shortCode = sale.short_code ?? null;
        const createdAt = sale.created_at
            ? new Date(sale.created_at).toLocaleString('pt-BR')
            : null;
        const totalValue = formatCurrency(sale.total ?? sale.subtotal ?? 0);
        const itemsSummary = Array.isArray(sale.items_summary) ? sale.items_summary : [];

        card.dataset.saleId = saleId;
        card.dataset.status = status;
        card.innerHTML = `
            <p class="text-sm font-semibold text-slate-700">${label}</p>
            ${createdAt ? `<p class="text-xs text-slate-500 mt-0.5">${createdAt}</p>` : ''}
            <p class="text-xs text-slate-500 mt-1">Total: R$ ${totalValue}</p>
            ${itemsSummary.length ? `<ul class=\"mt-2 space-y-1 text-[11px] text-slate-500\">${itemsSummary.slice(0,3).map(t => `<li>• ${t}</li>`).join('')}</ul>` : ''}
            <p class="text-[11px] text-slate-400 mt-1">Arraste para atualizar o status.</p>
        `;

        attachCardDragEvents(card);
        return card;
    }

    function attachCardDragEvents(card) {
        card.addEventListener('dragstart', handleDragStart);
        card.addEventListener('dragend', handleDragEnd);
    }

    function canTransition(fromStatus, toStatus) {
        if (!fromStatus || !toStatus || fromStatus === toStatus) {
            return false;
        }

        const allowed = allowedTransitions[fromStatus] ?? [];
        return allowed.includes(toStatus);
    }

    function handleDragStart(event) {
        const target = event.currentTarget;
        draggedSaleId = target.dataset.saleId ?? null;
        draggedSaleStatus = target.dataset.status ?? null;
        target.classList.add('opacity-60');

        if (event.dataTransfer && draggedSaleId) {
            event.dataTransfer.setData('text/plain', draggedSaleId);
            event.dataTransfer.effectAllowed = 'move';
        }
    }

    function handleDragEnd(event) {
        const target = event.currentTarget;
        target.classList.remove('opacity-60');
        draggedSaleId = null;
        draggedSaleStatus = null;
    }

    function handleDragOver(event) {
        if (!draggedSaleId) {
            return;
        }

        const column = event.currentTarget;
        const targetStatus = column.dataset.status;

        if (!canTransition(draggedSaleStatus, targetStatus)) {
            return;
        }

        event.preventDefault();
        event.dataTransfer.dropEffect = 'move';
        column.classList.add('ring-2', 'ring-blue-400', 'ring-offset-2');
    }

    function handleDragLeave(event) {
        event.currentTarget.classList.remove('ring-2', 'ring-blue-400', 'ring-offset-2');
    }

    async function handleDrop(event) {
        event.preventDefault();
        const column = event.currentTarget;
        column.classList.remove('ring-2', 'ring-blue-400', 'ring-offset-2');

        const newStatus = column.dataset.status ?? null;
        if (!draggedSaleId || !draggedSaleStatus || !newStatus) {
            draggedSaleId = null;
            draggedSaleStatus = null;
            return;
        }

        if (!canTransition(draggedSaleStatus, newStatus)) {
            setFeedback('Transição de status não permitida.', 'error');
            draggedSaleId = null;
            draggedSaleStatus = null;
            return;
        }

        try {
            await updateSaleStatusRequest(draggedSaleId, newStatus);
            setFeedback(`Status da venda atualizado para ${statusDisplay[newStatus] ?? newStatus}.`, 'success');
        } catch (error) {
            console.error('Falha ao atualizar status da venda', error);
            setFeedback('Não foi possível mover a venda. Tente novamente.', 'error');
        } finally {
            draggedSaleId = null;
            draggedSaleStatus = null;
            await loadDashboard();
        }
    }

    async function updateSaleStatusRequest(saleId, status) {
        const response = await window.apiFetch(`/sales/${saleId}/status`, {
            method: 'PATCH',
            body: JSON.stringify({ status }),
        });

        if (!response.ok) {
            const result = await response.json().catch(() => ({}));
            const message = result.error?.message ?? 'Falha ao atualizar status.';
            throw new Error(message);
        }

        return response.json().catch(() => ({}));
    }

    function setFeedback(message, tone = 'info') {
        pdvFeedback.textContent = message;
        const classes = {
            error: 'text-sm text-rose-600',
            success: 'text-sm text-emerald-600',
            info: 'text-sm text-slate-500',
        };
        pdvFeedback.className = classes[tone] ?? classes.info;
    }

    function resetSizeSelect(message = 'Selecione um produto primeiro') {
        if (!sizeSelect) {
            return;
        }
        sizeSelect.innerHTML = `<option value="">${message}</option>`;
        sizeSelect.disabled = true;
        if (sizeInfo) {
            sizeInfo.textContent = '';
        }
        currentSizes = [];
    }

    function updateSizeInfo(size) {
        if (!sizeInfo) {
            return;
        }
        if (!size) {
            sizeInfo.textContent = '';
            return;
        }
        const entry = currentSizes.find((item) => item.size === size);
        const available = entry ? entry.quantity : 0;
        sizeInfo.textContent = `Disponível: ${available}`;
    }

    async function loadCashSummary() {
        if (!cashSummaryTable) {
            return;
        }

        cashSummaryTable.innerHTML = '<tr><td colspan="5" class="px-4 py-3 text-slate-500 text-center">Carregando dados do fluxo...</td></tr>';

        if (cashSummaryCards) {
            cashSummaryCards.classList.add('hidden');
        }

        if (cashSummaryStatus) {
            cashSummaryStatus.textContent = 'Carregando resumo do fluxo...';
            cashSummaryStatus.className = 'text-sm text-slate-500';
        }

        try {
            const response = await window.apiFetch('/cash-ledger/summary');
            if (!response.ok) {
                throw new Error('Falha ao obter resumo do caixa');
            }

            const data = await response.json();
            const totals = data.totals ?? {};

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

            const byMethod = data.by_method ?? {};
            const methodKeys = Object.keys(byMethod).sort();

            if (methodKeys.length === 0) {
                cashSummaryTable.innerHTML = '<tr><td colspan="5" class="px-4 py-3 text-slate-500 text-center">Nenhum lançamento registrado ainda.</td></tr>';
            } else {
                cashSummaryTable.innerHTML = '';
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
                    cashSummaryTable.appendChild(row);
                });
            }

            if (cashSummaryStatus) {
                const totalCount = Number(data.count ?? 0);
                cashSummaryStatus.textContent = totalCount > 0
                    ? `Movimentações analisadas: ${totalCount}`
                    : 'Nenhum lançamento registrado no caixa.';
                cashSummaryStatus.className = 'text-sm text-slate-500';
            }
        } catch (error) {
            console.error('Falha ao carregar resumo do fluxo', error);
            if (cashSummaryStatus) {
                cashSummaryStatus.textContent = 'Não foi possível carregar o resumo do fluxo agora.';
                cashSummaryStatus.className = 'text-sm text-rose-600';
            }
            cashSummaryTable.innerHTML = '<tr><td colspan="5" class="px-4 py-3 text-rose-600 text-center">Erro ao carregar dados do fluxo.</td></tr>';
        }
    }

    async function loadDashboard() {
        try {
            const response = await window.apiFetch('/dashboard/vendor');
            if (!response.ok) {
                return;
            }

            const data = await response.json();
            document.querySelector('#today-total').textContent = formatCurrency(data.today_total ?? 0);
            document.querySelector('#month-total').textContent = formatCurrency(data.month_total ?? 0);
            document.querySelector('#open-count').textContent = data.open_count ?? 0;

            renderKanban(Array.isArray(data.kanban) ? data.kanban : []);
        } catch (error) {
            console.error('Falha ao carregar dashboard do vendedor', error);
        }
    }

    async function loadProductCatalog() {
        if (!productSelect) {
            return;
        }

        productSelect.innerHTML = '<option value="">Carregando produtos...</option>';
        resetSizeSelect();

        try {
            const response = await window.apiFetch('/products/options');
            if (!response.ok) {
                throw new Error('Falha ao carregar produtos');
            }

            const data = await response.json();
            productCatalog = Array.isArray(data.items) ? data.items : [];

            if (productCatalog.length === 0) {
                productSelect.innerHTML = '<option value="">Nenhum produto encontrado</option>';
                return;
            }

            productSelect.innerHTML = '<option value="">Selecione um produto</option>';
            productCatalog.forEach((product) => {
                const option = document.createElement('option');
                option.value = product.id;
                option.textContent = product.sku ? `${product.name} (${product.sku})` : product.name;
                option.dataset.price = product.sale_price != null ? String(product.sale_price) : '';
                productSelect.appendChild(option);
            });
        } catch (error) {
            console.error('Erro ao carregar catalogo de produtos', error);
            productSelect.innerHTML = '<option value="">Erro ao carregar produtos</option>';
        }
    }

    async function loadSizesForProduct(productId) {
        if (!sizeSelect) {
            return;
        }

        if (!productId) {
            resetSizeSelect();
            return;
        }

        sizeSelect.disabled = true;
        sizeSelect.innerHTML = '<option value="">Carregando tamanhos...</option>';
        if (sizeInfo) {
            sizeInfo.textContent = '';
        }

        try {
            const response = await window.apiFetch(`/products/${productId}/sizes`);
            if (!response.ok) {
                throw new Error('Falha ao carregar tamanhos');
            }

            const data = await response.json();
            currentSizes = Array.isArray(data.sizes) ? data.sizes : [];

            sizeSelect.innerHTML = '<option value="">Selecione um tamanho</option>';
            const groups = new Map();

            currentSizes.forEach((entry) => {
                const groupKey = entry.group ?? 'outros';
                const option = document.createElement('option');
                option.value = entry.size;
                option.textContent = `${entry.size} (disp.: ${entry.quantity})`;
                option.dataset.quantity = String(entry.quantity);

                if (groupKey === 'outros') {
                    sizeSelect.appendChild(option);
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
                sizeSelect.appendChild(optgroup);
            });

            sizeSelect.disabled = false;
            sizeSelect.value = '';
        } catch (error) {
            console.error('Erro ao carregar tamanhos', error);
            resetSizeSelect('Erro ao carregar tamanhos');
        }
    }

    function findProduct(productId) {
        return productCatalog.find((product) => product.id === productId) ?? null;
    }

    async function loadVendorInventoryAll() {
        if (!vendorInventoryBody) return;
        if (vendorInventoryStatus) { vendorInventoryStatus.textContent = 'Carregando estoque...'; vendorInventoryStatus.className = 'text-sm text-slate-500'; }
        vendorInventoryBody.innerHTML = '<tr><td colspan="3" class="px-4 py-3 text-slate-500 text-center">Carregando...</td></tr>';
        try {
            // Usa endpoint aberto ao vendedor (opções ativas)
            const resp = await window.apiFetch('/products/options');
            if (!resp.ok) throw new Error('Falha ao carregar produtos');
            const data = await resp.json();
            const products = Array.isArray(data.items) ? data.items : [];
            const rows = [];
            for (const p of products) {
                try {
                    const sresp = await window.apiFetch(`/products/${p.id}/sizes`);
                    if (!sresp.ok) continue;
                    const sdata = await sresp.json();
                    const sizes = Array.isArray(sdata.sizes) ? sdata.sizes : [];
                    sizes.forEach((entry) => {
                        rows.push({
                            product: p.sku ? `${p.name} (${p.sku})` : p.name,
                            size: entry.size,
                            quantity: entry.quantity,
                        });
                    });
                } catch {}
            }
            rows.sort((a,b)=> String(a.product).localeCompare(String(b.product)) || String(a.size).localeCompare(String(b.size)));
            if (rows.length === 0) {
                vendorInventoryBody.innerHTML = '<tr><td colspan="3" class="px-4 py-3 text-slate-500 text-center">Nenhum item em estoque.</td></tr>';
            } else {
                vendorInventoryBody.innerHTML = '';
                rows.forEach((r)=>{
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td class="px-4 py-2">${r.product}</td>
                        <td class="px-4 py-2">${r.size}</td>
                        <td class="px-4 py-2 text-right">${r.quantity}</td>
                    `;
                    vendorInventoryBody.appendChild(tr);
                });
            }
            if (vendorInventoryStatus) { vendorInventoryStatus.textContent = `Itens carregados: ${rows.length}`; vendorInventoryStatus.className = 'text-sm text-slate-500'; }
        } catch (e) {
            vendorInventoryBody.innerHTML = '<tr><td colspan="3" class="px-4 py-3 text-rose-600 text-center">Erro ao carregar estoque.</td></tr>';
            if (vendorInventoryStatus) { vendorInventoryStatus.textContent = 'Erro ao carregar estoque.'; vendorInventoryStatus.className = 'text-sm text-rose-600'; }
        }
    }

    function renderItems() {
        let runningTotal = 0;

        if (currentItems.length === 0) {
            itemsTableBody.innerHTML = '<tr><td colspan="6" class="px-4 py-3 text-slate-500 text-center">Nenhum item adicionado.</td></tr>';
            if (totalElement) {
                totalElement.textContent = '0,00';
            }
            return;
        }

        itemsTableBody.innerHTML = '';
        currentItems.forEach((item, index) => {
            const displayName = item.name ? `${item.name}${item.sku ? ` (${item.sku})` : ''}` : item.product_id;
            const unitPrice = Number(item.unit_price ?? 0);
            const lineTotal = unitPrice * item.qty;
            runningTotal += lineTotal;

            const row = document.createElement('tr');
            row.innerHTML = `
                <td class="px-4 py-2">${displayName}</td>
                <td class="px-4 py-2 text-center">${item.size}</td>
                <td class="px-4 py-2 text-right">${item.qty}</td>
                <td class="px-4 py-2 text-right">R$ ${unitPrice.toLocaleString('pt-BR', { minimumFractionDigits: 2 })}</td>
                <td class="px-4 py-2 text-right">R$ ${lineTotal.toLocaleString('pt-BR', { minimumFractionDigits: 2 })}</td>
                <td class="px-4 py-2 text-right"><button data-remove-index="${index}" class="text-sm text-rose-600 hover:text-rose-500">Remover</button></td>
            `;
            itemsTableBody.appendChild(row);
        });

        if (totalElement) {
            totalElement.textContent = runningTotal.toLocaleString('pt-BR', { minimumFractionDigits: 2 });
        }
    }

    async function loadSalesList() {
        try {
            const response = await window.apiFetch('/sales');
            if (!response.ok) {
                return;
            }

            const data = await response.json();
            const items = Array.isArray(data.items) ? data.items : [];

            if (items.length === 0) {
                saleTableBody.innerHTML = '<tr><td colspan="4" class="px-4 py-3 text-slate-500 text-center">Nenhuma venda criada ainda.</td></tr>';
                return;
            }

            saleTableBody.innerHTML = '';
            items.forEach((sale) => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td class="px-4 py-2 font-mono text-xs">${sale.id}</td>
                    <td class="px-4 py-2 capitalize">${sale.status}</td>
                    <td class="px-4 py-2">R$ ${Number(sale.subtotal ?? 0).toLocaleString('pt-BR', { minimumFractionDigits: 2 })}</td>
                    <td class="px-4 py-2">${new Date(sale.created_at ?? Date.now()).toLocaleString('pt-BR')}</td>
                `;
                saleTableBody.appendChild(row);
            });
        } catch (error) {
            console.error('Falha ao carregar vendas', error);
        }
    }

    itemForm?.addEventListener('submit', (event) => {
        event.preventDefault();

        const productId = productSelect ? productSelect.value : '';
        const size = sizeSelect ? sizeSelect.value : '';
        const qty = Number(itemForm.qty.value ?? 1);

        if (!productId || !size) {
            setFeedback('Selecione produto e tamanho antes de adicionar.', 'error');
            return;
        }

        if (qty <= 0) {
            setFeedback('Quantidade precisa ser positiva.', 'error');
            return;
        }

        const productData = findProduct(productId);
        const sizeData = currentSizes.find((entry) => entry.size === size);
        const available = sizeData ? sizeData.quantity : 0;

        if (available < qty) {
            setFeedback('Estoque insuficiente para o tamanho selecionado.', 'error');
            return;
        }

        currentItems.push({
            product_id: productId,
            qty,
            size,
            name: productData?.name ?? null,
            sku: productData?.sku ?? null,
            unit_price: productData?.sale_price ?? null,
        });

        renderItems();
        setFeedback('Item adicionado.', 'success');
        itemForm.qty.value = 1;
        if (sizeSelect) {
            sizeSelect.value = '';
            updateSizeInfo('');
        }
    });

    itemsTableBody?.addEventListener('click', (event) => {
        const button = event.target instanceof HTMLButtonElement ? event.target : null;
        if (!button || !button.dataset.removeIndex) {
            return;
        }

        const index = Number(button.dataset.removeIndex);
        currentItems.splice(index, 1);
        renderItems();
        setFeedback('Item removido.', 'info');
    });

    resetButton?.addEventListener('click', () => {
        currentItems = [];
        renderItems();
        setFeedback('Lista de itens limpa.', 'info');
    });

    saleRefreshButton?.addEventListener('click', (event) => {
        event.preventDefault();
        loadSalesList();
    });

    pdvForm?.addEventListener('submit', async (event) => {
        event.preventDefault();
        if (currentItems.length === 0) {
            setFeedback('Adicione pelo menos um item para registrar a venda.', 'error');
            return;
        }

        const submitButton = pdvForm.querySelector('button[type="submit"]');
        submitButton.disabled = true;
        setFeedback('Registrando venda, aguarde...', 'info');

        const payload = {
            items: currentItems.map((item) => ({
                product_id: item.product_id,
                size: item.size,
                qty: item.qty,
            })),
        };

        try {
            const response = await window.apiFetch('/sales', {
                method: 'POST',
                body: JSON.stringify(payload),
            });

            const result = await response.json().catch(() => ({}));

            if (!response.ok) {
                const message = result.error?.message ?? 'Falha ao registrar venda.';
                setFeedback(message, 'error');
                return;
            }

            const saleId = result.sale_id;
            const method = pdvMethod?.value || '';
            if (!method) { setFeedback('Escolha o método de pagamento.', 'error'); return; }
            if (method === 'credito') {
                if (!pdvTerminal?.value || !pdvBrand?.value) { setFeedback('Escolha maquininha e bandeira.', 'error'); return; }
            }

            const checkoutBody = {
                payment_method: method,
                discount_percent: 0,
                installments: method === 'credito' ? Number(pdvInstallments?.value || '1') : 1,
                terminal_id: method === 'credito' ? (pdvTerminal?.value || null) : null,
                brand_id: method === 'credito' ? (pdvBrand?.value || null) : null,
            };

            const chkRes = await window.apiFetch(`/sales/${saleId}/checkout`, { method: 'POST', body: JSON.stringify(checkoutBody) });
            const chk = await chkRes.json().catch(()=>({}));
            if (!chkRes.ok) { setFeedback(chk?.error?.message || 'Falha no checkout.', 'error'); return; }

            const grossNumber = Number(
                (chk.gross_amount !== undefined && chk.gross_amount !== null)
                    ? chk.gross_amount
                    : (Number(chk.subtotal ?? 0) - Number(chk.discount_total ?? 0))
            );
            const formattedTotal = Math.max(0, grossNumber).toLocaleString('pt-BR', { minimumFractionDigits: 2 });
            setFeedback(`Venda finalizada: ${saleId} | Total: R$ ${formattedTotal}`, 'success');
            currentItems = [];
            pdvForm.reset();
            renderItems();
            loadSalesList();
            loadDashboard();
            try { localStorage.setItem('adg_sale_updated', String(Date.now())); } catch {}
        } catch (error) {
            console.error('Erro ao registrar venda', error);
            setFeedback('Erro inesperado ao registrar venda.', 'error');
        } finally {
            submitButton.disabled = false;
        }
    });

    productSelect?.addEventListener('change', () => {
        loadSizesForProduct(productSelect.value);
    });

    sizeSelect?.addEventListener('change', () => {
        updateSizeInfo(sizeSelect.value);
    });

    async function loadCommission() {
        if (!commissionTableBody) { return; }

        commissionTableBody.innerHTML = '<tr><td colspan="6" class="px-4 py-3 text-slate-500 text-center">Carregando dados de comissao...</td></tr>';
        if (commissionSummaryCards) { commissionSummaryCards.classList.add('hidden'); }
        if (commissionStatus) {
            commissionStatus.textContent = 'Carregando comissao...';
            commissionStatus.className = 'text-sm text-slate-500';
        }

        try {
            const response = await window.apiFetch('/commission/vendor');
            if (!response.ok) { throw new Error('Falha ao obter comissao'); }
            const data = await response.json();

            const baseSalary = Number(data.base_salary ?? 1844);
            const monthCommission = Number(data.month_total ?? 0);
            const totalWithBase = Number(
                data.month_total_with_base ?? (baseSalary + monthCommission)
            );

            if (commissionTotals.today) { commissionTotals.today.textContent = formatCurrency(data.today_total ?? 0); }
            if (commissionTotals.month) { commissionTotals.month.textContent = formatCurrency(monthCommission); }
            if (commissionTotals.base) { commissionTotals.base.textContent = formatCurrency(baseSalary); }
            if (commissionTotals.totalWithBase) { commissionTotals.totalWithBase.textContent = formatCurrency(totalWithBase); }
            if (commissionSummaryCards) { commissionSummaryCards.classList.remove('hidden'); }

            const items = Array.isArray(data.items) ? data.items : [];
            if (items.length === 0) {
                commissionTableBody.innerHTML = '<tr><td colspan="7" class="px-4 py-3 text-slate-500 text-center">Nenhum item comissionado ainda.</td></tr>';
            } else {
                commissionTableBody.innerHTML = '';
                items.forEach((it) => {
                    const tr = document.createElement('tr');
                    const when = new Date(it.created_at ?? Date.now()).toLocaleString('pt-BR');
                    tr.innerHTML = `
                        <td class="px-4 py-2">${when}</td>
                        <td class="px-4 py-2">${(it.sale_short ?? (String(it.sale_id||'').slice(-5)))}</td>
                        <td class="px-4 py-2">${it.product ?? ''}</td>
                        <td class="px-4 py-2">${it.size ?? ''}</td>
                        <td class="px-4 py-2 text-right">${Number(it.qty ?? 0)}</td>
                        <td class="px-4 py-2 text-right">R$ ${formatCurrency(it.line_total)}</td>
                        <td class="px-4 py-2 text-right">R$ ${formatCurrency(it.commission)}</td>
                    `;
                    commissionTableBody.appendChild(tr);
                });

                // Total acumulado de comissao (lifetime)
                const totalRow = document.createElement('tr');
                totalRow.innerHTML = `
                    <td class="px-4 py-2 text-right font-semibold" colspan="6">Total de comissao acumulada</td>
                    <td class="px-4 py-2 text-right font-semibold">R$ ${formatCurrency(data.lifetime_total ?? 0)}</td>
                `;
                commissionTableBody.appendChild(totalRow);
            }

            if (commissionStatus) {
                commissionStatus.textContent = 'Comissao atualizada.';
                commissionStatus.className = 'text-sm text-slate-500';
            }
        } catch (error) {
            console.error('Falha ao carregar comissao', error);
            if (commissionStatus) {
                commissionStatus.textContent = 'Nao foi possivel carregar a comissao agora.';
                commissionStatus.className = 'text-sm text-rose-600';
            }
            commissionTableBody.innerHTML = '<tr><td colspan="6" class="px-4 py-3 text-rose-600 text-center">Erro ao carregar dados de comissao.</td></tr>';
        }
    }

    renderItems();
    loadProductCatalog();

    // Inicializa selects de pagamento
    toggleCreditFields(false);
    pdvMethod?.addEventListener('change', () => {
        const show = (pdvMethod?.value || '') === 'credito';
        toggleCreditFields(show);
        if (show) { loadCreditOptions().then(()=> setTimeout(loadInstallments, 150)); }
    });
    pdvTerminal?.addEventListener('change', loadInstallments);
    pdvBrand?.addEventListener('change', loadInstallments);

    // Leitura de estoque
    try { loadVendorInventoryAll(); } catch {}

    // Se entrou direto na aba Comissao, carrega depois que as referencias foram definidas
    try {
        const currentSection = new URLSearchParams(window.location.search).get('section') ?? 'pdv';
        if (currentSection === 'comissao') {
            loadCommission().catch(()=>{});
        }
    } catch {}
});
</script>
<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/base.php';
