<?php

$title = 'Entrar no Atelie do Genio';
$csrfToken = $csrfToken ?? null;

ob_start();
?>
<section class="mx-auto max-w-md rounded-2xl border border-blue-100 bg-white p-8 shadow-lg">
    <div class="mb-6 text-center">
        <div class="mx-auto mb-3 flex h-16 w-16 items-center justify-center rounded-full bg-blue-600 text-2xl font-semibold text-white">AE</div>
        <h1 class="text-2xl font-semibold text-slate-700">Bem-vindo ao Atelie do Genio</h1>
        <p class="mt-2 text-sm text-slate-500">Faça login para acessar o PDV, estoque e relatórios.</p>
    </div>
    <form id="login-form" class="space-y-4">
        <input type="hidden" name="_token" value="<?= htmlspecialchars($csrfToken ?? '', ENT_QUOTES, 'UTF-8') ?>">
        <div>
            <label class="block text-sm font-medium text-slate-600">E-mail</label>
            <input type="email" name="email" required class="mt-1 w-full rounded-lg border border-slate-200 px-4 py-2 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-600">Senha</label>
            <input type="password" name="password" required class="mt-1 w-full rounded-lg border border-slate-200 px-4 py-2 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
        </div>
        <button type="submit" class="w-full rounded-lg bg-blue-600 px-4 py-2 text-white font-medium hover:bg-blue-500 transition">
            Entrar
        </button>
    </form>
    <p id="login-feedback" class="mt-4 text-center text-sm text-rose-500 hidden"></p>
</section>

<script>
    document.querySelector('#login-form').addEventListener('submit', async (event) => {
        event.preventDefault();

        const form = event.currentTarget;
        const feedback = document.querySelector('#login-feedback');
        feedback.classList.add('hidden');

        const csrfField = form.querySelector('input[name="_token"]');
        const csrfToken = (csrfField && csrfField.value) ? csrfField.value : (window.csrfToken || '');

        const payload = {
            _token: csrfToken,
            email: form.email.value.trim(),
            password: form.password.value,
        };

        try {
            const response = await window.apiFetch('/auth/login', {
                method: 'POST',
                body: JSON.stringify(payload),
            });

            const result = await response.json();

            if (!response.ok) {
                const message = result.error?.message ?? 'Falha ao autenticar.';
                feedback.textContent = message;
                feedback.classList.remove('hidden');
                return;
            }

            sessionStorage.setItem('access_token', result.access_token);
            localStorage.setItem('access_token', result.access_token);

            if (result.csrf_token) {
                window.csrfToken = result.csrf_token;
            }

            const role =
                result.role ??
                result.claims?.role ??
                result.claims?.user_metadata?.role ??
                result.claims?.raw_user_meta_data?.role ??
                result.user?.role ??
                result.user?.user_metadata?.role ??
                result.user?.raw_user_meta_data?.role ??
                'vendedor';

            sessionStorage.setItem('user_role', role);
            localStorage.setItem('user_role', role);

            try {
                const emailCandidate =
                    result.user?.email ||
                    result.claims?.email ||
                    result.user?.user_metadata?.email ||
                    result.claims?.user_metadata?.email ||
                    null;
                if (emailCandidate) {
                    sessionStorage.setItem('user_email', emailCandidate);
                    localStorage.setItem('user_email', emailCandidate);
                }
            } catch {}

            const destination = role === 'admin' ? '/painel/admin' : '/painel/vendedor';
            const base = window.__BASE_PATH__ || '';
            window.location.href = base + destination;
        } catch (error) {
            console.error('Login error', error);
            feedback.textContent = 'Erro inesperado. Tente novamente.';
            feedback.classList.remove('hidden');
        }
    });
</script>
<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/base.php';
?>
