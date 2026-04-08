-- Adiciona coluna de desconto explícito na tabela de vendas
-- Execute no editor SQL do Supabase (projeto: tuufavmczgdwcwblnumr) com role de Owner

alter table if exists public.sales
  add column if not exists discount_total numeric(12,2) not null default 0;

-- Opcional: se desejar também armazenar o percentual aplicado, descomente abaixo
-- alter table if exists public.sales
--   add column if not exists discount_percent numeric(5,2) default 0 check (discount_percent >= 0 and discount_percent <= 100);

-- Verificação rápida
-- select id, subtotal, discount_total, fees, total from public.sales order by created_at desc limit 5;