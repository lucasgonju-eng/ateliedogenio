<?php
$title = $title ?? 'Atelie do Genio';
$content = $content ?? '';
$csrfToken = $csrfToken ?? null;
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php if ($csrfToken): ?>
        <meta name="csrf-token" content="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
    <?php endif; ?>
    <title><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,typography"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        html, body { font-family: 'Inter', sans-serif; }
        .brand-gradient { background: linear-gradient(135deg, #1d4ed8 0%, #facc15 100%); }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 min-h-screen">
    <header class="brand-gradient text-white">
        <div class="max-w-6xl mx-auto px-6 py-4 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <span class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-white/20 text-xl font-bold">AE</span>
                <div>
                    <h1 class="text-xl font-semibold">Atelie do Genio</h1>
                    <p class="text-sm text-white/80">Controle de estoque, PDV e fluxo de caixa</p>
                </div>
            </div>
            <nav class="hidden md:flex gap-6 text-sm font-medium items-center">
                <a data-route="dashboard" class="hover:text-yellow-100 transition" href="#">Dashboard</a>
                <a data-route="estoque" class="hover:text-yellow-100 transition" href="#">Estoque</a>
                <a data-route="pdv" class="hover:text-yellow-100 transition" href="#">PDV</a>
                <a data-route="fluxo" class="hover:text-yellow-100 transition" href="#">Fluxo de Caixa</a>
                <a data-route="comissao" class="hover:text-yellow-100 transition" href="#">Comissao</a>
                <button id="logout-button" class="rounded-full bg-white/20 px-3 py-1 text-sm font-semibold text-white hover:bg-white/30 transition">
                    Sair
                </button>
            </nav>
        </div>
    </header>

    <main class="max-w-6xl mx-auto px-6 py-10">
        <?= $content ?>
    </main>

    <footer class="max-w-6xl mx-auto px-6 py-8 text-sm text-slate-500">
        <p>&copy; <?= date('Y') ?> Atelie do Genio. Inspiracao cientifica, resultados comerciais.</p>
    </footer>

    <script>
        // Descobre base path quando hospedado em subpasta (ex.: /vendas)
        (function(){
            try {
                var path = window.location.pathname || '/';
                // Detecta primeiro segmento (ex.: /vendas/..)
                var m = path.match(/^\/(\w+)(\/|$)/);
                var base = '';
                if (m && m[1] && m[1].toLowerCase() !== 'public') {
                    base = '/' + m[1];
                }
                window.__BASE_PATH__ = base; // '' ou '/vendas'
            } catch (e) { window.__BASE_PATH__ = ''; }
        })();
        // Base absoluta da API em produção
        const metaApiBase = document.querySelector('meta[name=\"api-base\"]');
        const apiBaseFromMeta = (metaApiBase?.getAttribute('content') || '').trim();
        const defaultApiBase = (window.location.origin || '').replace(/\/+$/, '') + (window.__BASE_PATH__ || '');
        window.__API_BASE__ = (apiBaseFromMeta || defaultApiBase).replace(/\/+$/, '');
        const metaToken = document.querySelector('meta[name="csrf-token"]');
        window.csrfToken = metaToken ? metaToken.getAttribute('content') : null;

        const TOKEN_KEY = 'access_token';
        const ROLE_KEY = 'user_role';

        function getStoredValue(key) {
            const sessionValue = sessionStorage.getItem(key);
            if (sessionValue && sessionValue !== '') {
                return sessionValue;
            }

            const localValue = localStorage.getItem(key);
            if (localValue && localValue !== '') {
                sessionStorage.setItem(key, localValue);
                return localValue;
            }

            return null;
        }

        window.apiFetch = async function (input, init = {}) {
            const headers = new Headers(init.headers || {});
            headers.set('Accept', 'application/json');

            const token = getStoredValue(TOKEN_KEY);
            if (token) {
                headers.set('Authorization', `Bearer ${token}`);
            }

            if (!headers.has('Content-Type') && !(init.body instanceof FormData)) {
                headers.set('Content-Type', 'application/json');
            }

            if (window.csrfToken && !headers.has('X-CSRF-TOKEN')) {
                headers.set('X-CSRF-TOKEN', window.csrfToken);
            }

            // Monta URL com base absoluta se definida; senão usa base path
            let url = input;
            let usedApiBase = '';
            if (typeof input === 'string') {
                if (input.startsWith('http://') || input.startsWith('https://')) {
                    url = input;
                } else if (window.__API_BASE__) {
                    const base = (window.__API_BASE__ || '').replace(/\/+$/, '');
                    usedApiBase = base;
                    url = input.startsWith('/') ? (base + input) : (base + '/' + input);
                } else if (input.startsWith('/')) {
                    const base = window.__BASE_PATH__ || '';
                    url = base + input;
                }
            }

            const options = { ...init, headers };

            if (!options.credentials) {
                options.credentials = 'same-origin';
            }

            try {
                return await fetch(url, options);
            } catch (error) {
                if (usedApiBase && typeof input === 'string') {
                    const fallback = input.startsWith('/') ? ((window.__BASE_PATH__ || '') + input) : input;
                    try {
                        return await fetch(fallback, options);
                    } catch {}
                }
                throw error;
            }
        };

        const role = getStoredValue(ROLE_KEY);
        const isAdmin = role === 'admin';

        const basePrefix = window.__BASE_PATH__ || '';
        const routes = {
            dashboard: (isAdmin ? '/painel/admin' : '/painel/vendedor?section=pdv'),
            estoque: (isAdmin ? '/painel/admin?section=estoque' : '/painel/vendedor?section=estoque'),
            pdv: (isAdmin ? '/painel/admin?section=pdv' : '/painel/vendedor?section=pdv'),
            fluxo: (isAdmin ? '/painel/admin?section=fluxo' : '/painel/vendedor?section=fluxo'),
            comissao: (isAdmin ? '/painel/admin?section=comissao' : '/painel/vendedor?section=comissao'),
        };

        document.querySelectorAll('[data-route]').forEach((link) => {
            const key = link.dataset.route;
            const target = routes[key] || '#';
            link.setAttribute('href', basePrefix + target);
        });

        // Oculta PDV e Fluxo de Caixa no topo para vendedores
        if (!isAdmin) {
            try {
                document.querySelector('[data-route="pdv"]')?.classList.add('hidden');
                document.querySelector('[data-route="fluxo"]')?.classList.add('hidden');
            } catch {}
        }

        const logoutButton = document.querySelector('#logout-button');
        if (logoutButton) {
            logoutButton.addEventListener('click', async () => {
                try {
                    await window.apiFetch('/auth/logout', { method: 'POST' });
                } catch (error) {
                    console.error('Erro ao encerrar sessão', error);
                }

                sessionStorage.removeItem(TOKEN_KEY);
                sessionStorage.removeItem(ROLE_KEY);
                localStorage.removeItem(TOKEN_KEY);
                localStorage.removeItem(ROLE_KEY);
                const baseAfterLogout = (window.__BASE_PATH__ || '') + '/';
                window.location.href = baseAfterLogout;
            });
        }
    </script>
</body>
</html>
