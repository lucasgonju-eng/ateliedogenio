-- Monthly commission closings (admin confirmations and receipts)
create table if not exists public.commission_closings (
    id uuid primary key default gen_random_uuid(),
    vendor_id uuid not null references public.users(id) on delete cascade,
    period_start date not null,
    closing_date date not null,
    confirmed_at timestamptz,
    receipt_path text,
    receipt_filename text,
    created_at timestamptz not null default now()
);

create unique index if not exists commission_closings_vendor_period_unique
    on public.commission_closings(vendor_id, period_start);
