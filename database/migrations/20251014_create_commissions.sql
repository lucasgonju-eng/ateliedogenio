-- Sales targets per vendor and period
create table if not exists public.sales_targets (
    id uuid primary key default gen_random_uuid(),
    vendor_id uuid not null references public.users(id) on delete cascade,
    period_start date not null,
    period_end date not null,
    goal_amount numeric(12,2) not null,
    commission_rate numeric(5,4) not null,
    created_at timestamptz not null default now()
);

create unique index if not exists sales_targets_vendor_period_unique
    on public.sales_targets(vendor_id, period_start, period_end);

-- Commissions generated after sale checkout
create table if not exists public.commissions (
    id uuid primary key default gen_random_uuid(),
    sale_id uuid not null references public.sales(id) on delete cascade,
    vendor_id uuid not null references public.users(id) on delete cascade,
    amount numeric(12,2) not null,
    status text not null default 'pending',
    created_at timestamptz not null default now(),
    paid_at timestamptz
);

create index if not exists commissions_vendor_idx on public.commissions(vendor_id);
create index if not exists commissions_status_idx on public.commissions(status);
