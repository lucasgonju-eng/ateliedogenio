-- Permite múltiplas faixas (parcelas) por método na mesma maquininha+bandeira
-- Execute no editor SQL do Supabase com role de Owner

alter table if exists public.payment_fees
  drop constraint if exists payment_fees_unique_combo;

alter table if exists public.payment_fees
  add constraint payment_fees_unique_combo_range
    unique (terminal_id, brand_id, payment_method, installments_min, installments_max);

-- Verificação rápida
-- select terminal_id, brand_id, payment_method, installments_min, installments_max,
--        fee_percentage, per_installment_percentage, fee_fixed, confirmation_fixed_fee
-- from public.payment_fees
-- order by terminal_id, brand_id, payment_method, installments_min;

