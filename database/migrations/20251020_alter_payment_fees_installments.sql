-- Ajusta tabela de taxas para suportar parcelas e tarifa de confirmação
-- Execute no editor SQL do Supabase com role de Owner

alter table if exists public.payment_fees
  drop column if exists allow_discount,
  drop column if exists max_discount_percentage;

alter table if exists public.payment_fees
  add column if not exists installments_min integer not null default 1 check (installments_min >= 1),
  add column if not exists installments_max integer not null default 1 check (installments_max >= installments_min),
  add column if not exists per_installment_percentage numeric(6,4) not null default 0,
  add column if not exists confirmation_fixed_fee numeric(10,2) not null default 0;

-- Verificação rápida
-- select payment_method, installments_min, installments_max, fee_percentage, per_installment_percentage, fee_fixed, confirmation_fixed_fee from public.payment_fees order by created_at desc;

