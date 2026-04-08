-- Catálogo de pagamentos: bandeiras, maquininhas e taxas por combinação
-- Execute no editor SQL do Supabase (projeto: <seu projeto>) com role de Owner

create extension if not exists "pgcrypto";

-- Tabela de bandeiras de cartão (Visa, Master, etc.)
create table if not exists public.card_brands (
  id uuid primary key default gen_random_uuid(),
  name text not null unique,
  active boolean not null default true,
  created_at timestamptz not null default now(),
  updated_at timestamptz not null default now()
);

-- Tabela de maquininhas / adquirentes (Stone, PagSeguro, etc.)
create table if not exists public.card_terminals (
  id uuid primary key default gen_random_uuid(),
  name text not null unique,
  active boolean not null default true,
  created_at timestamptz not null default now(),
  updated_at timestamptz not null default now()
);

-- Tabela de taxas por combinação (maquininha + bandeira + método)
create table if not exists public.payment_fees (
  id uuid primary key default gen_random_uuid(),
  terminal_id uuid not null references public.card_terminals(id) on delete cascade,
  brand_id uuid not null references public.card_brands(id) on delete restrict,
  payment_method text not null check (payment_method in ('pix','credito','debito','dinheiro','transferencia')),
  fee_percentage numeric(6,4) not null default 0,
  fee_fixed numeric(10,2) not null default 0,
  allow_discount boolean not null default true,
  max_discount_percentage numeric(5,2) not null default 0,
  created_at timestamptz not null default now(),
  updated_at timestamptz not null default now(),
  constraint payment_fees_unique_combo unique (terminal_id, brand_id, payment_method)
);

-- Triggers para updated_at
create or replace function public.touch_updated_at()
returns trigger as $$
begin
  new.updated_at = now();
  return new;
end;
$$ language plpgsql;

drop trigger if exists card_brands_touch_updated_at on public.card_brands;
create trigger card_brands_touch_updated_at
before update on public.card_brands
for each row execute function public.touch_updated_at();

drop trigger if exists card_terminals_touch_updated_at on public.card_terminals;
create trigger card_terminals_touch_updated_at
before update on public.card_terminals
for each row execute function public.touch_updated_at();

drop trigger if exists payment_fees_touch_updated_at on public.payment_fees;
create trigger payment_fees_touch_updated_at
before update on public.payment_fees
for each row execute function public.touch_updated_at();

-- RLS (opcional). Como a API usa service role em rotas de admin, manter RLS desabilitado é suficiente.
-- Caso queira habilitar RLS, crie políticas apropriadas para admin.

-- select rápido
-- select * from public.card_brands order by name;
-- select * from public.card_terminals order by name;
-- select * from public.payment_fees order by created_at desc;

