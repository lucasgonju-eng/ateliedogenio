-- Tabela de variantes por tamanho
-- Execute no editor SQL do Supabase (com a role de Owner) 

create table if not exists public.product_variants (
  product_id uuid not null references public.products(id) on delete cascade,
  size text not null,
  quantity integer not null default 0,
  constraint product_variants_pkey primary key (product_id, size)
);

-- Índices auxiliares
create index if not exists idx_product_variants_product on public.product_variants(product_id);
create index if not exists idx_product_variants_size on public.product_variants(size);

-- RLS (opcional). Como nossa API usa a service role para estes endpoints, RLS não é estritamente necessária.
-- Caso queira habilitar RLS, você pode liberar leitura/escrita para usuários autenticados conforme necessidade.

-- alter table public.product_variants enable row level security;
-- create policy "allow read product_variants to authenticated" on public.product_variants
--   for select to authenticated using (true);
-- create policy "allow write product_variants to service" on public.product_variants
--   for all to service_role using (true) with check (true);

-- Verificação rápida
-- select * from public.product_variants limit 1;
