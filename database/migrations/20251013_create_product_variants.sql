-- Create table to store stock per size
create table if not exists public.product_variants (
    id uuid primary key default gen_random_uuid(),
    product_id uuid not null references public.products(id) on delete cascade,
    size text not null,
    quantity integer not null default 0,
    created_at timestamptz not null default now(),
    updated_at timestamptz not null default now()
);

create unique index if not exists product_variants_product_size_unique
    on public.product_variants(product_id, size);

-- Optional trigger to maintain updated_at
create or replace function public.touch_product_variant_updated_at()
returns trigger as $$
begin
    new.updated_at = now();
    return new;
end;
$$ language plpgsql;

drop trigger if exists product_variants_touch_updated_at on public.product_variants;
create trigger product_variants_touch_updated_at
before update on public.product_variants
for each row execute function public.touch_product_variant_updated_at();

-- Track size in sale items
alter table public.sale_items
    add column if not exists size text;
