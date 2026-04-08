create extension if not exists "pgcrypto";

create table if not exists public.items (
  id uuid primary key default gen_random_uuid(),
  name text not null,
  description text,
  created_at timestamptz not null default now()
);

alter table public.items enable row level security;

-- Evita conflitos se a migration rodar mais de uma vez
drop policy if exists "Public read" on public.items;
drop policy if exists "Auth write" on public.items;
drop policy if exists "Auth update" on public.items;
drop policy if exists "Auth delete" on public.items;

-- Leitura aberta (ajuste depois para seu caso)
create policy "Public read" on public.items
  for select
  using (true);

-- Escrita apenas para usuários autenticados
create policy "Auth write" on public.items
  for insert
  with check (auth.role() = 'authenticated');

create policy "Auth update" on public.items
  for update
  using (auth.role() = 'authenticated')
  with check (auth.role() = 'authenticated');

create policy "Auth delete" on public.items
  for delete
  using (auth.role() = 'authenticated');
