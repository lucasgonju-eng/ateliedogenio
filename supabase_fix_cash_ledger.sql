-- Migração corretiva para alinhar a tabela cash_ledger com o domínio da aplicação.
-- Execute no editor SQL do Supabase (projeto: tuufavmczgdwcwblnumr) com a role de Owner.
-- O script é idempotente: pode ser executado múltiplas vezes sem quebrar dados já migrados.

begin;

create extension if not exists "pgcrypto";

-- Garante colunas modernas na tabela
alter table if exists public.cash_ledger
    add column if not exists payment_method text,
    add column if not exists gross_amount numeric(14,2),
    add column if not exists fee_amount numeric(14,2),
    add column if not exists net_amount numeric(14,2),
    add column if not exists user_id uuid,
    add column if not exists entry_type text,
    add column if not exists note text,
    add column if not exists created_at timestamptz default timezone('UTC', now());

-- Copia dados das colunas antigas (quando existirem) e remove o legado
update public.cash_ledger
    set payment_method = coalesce(payment_method, lower(method))
    where method is not null;
alter table if exists public.cash_ledger drop column if exists method;

update public.cash_ledger
    set gross_amount = coalesce(gross_amount, amount)
    where amount is not null;
alter table if exists public.cash_ledger drop column if exists amount;

update public.cash_ledger
    set fee_amount = coalesce(fee_amount, fees, 0)
    where fees is not null;
alter table if exists public.cash_ledger drop column if exists fees;

update public.cash_ledger
    set entry_type = coalesce(entry_type, lower(type))
    where type is not null;
alter table if exists public.cash_ledger drop column if exists type;

-- Normaliza métodos de pagamento para os valores esperados pelo enum
update public.cash_ledger
set payment_method = case
    when payment_method in ('credito', 'credit', 'cartao_credito', 'cartao de credito', 'credit_card') then 'credito'
    when payment_method in ('debito', 'debit', 'cartao_debito', 'cartao debito') then 'debito'
    when payment_method in ('pix', 'pix_qr') then 'pix'
    when payment_method in ('dinheiro', 'cash', 'money') then 'dinheiro'
    when payment_method in ('transferencia', 'transfer', 'ted', 'doc') then 'transferencia'
    else payment_method
end
where payment_method is not null;

update public.cash_ledger
set payment_method = 'dinheiro'
where payment_method is null or payment_method not in ('credito','debito','pix','dinheiro','transferencia');

-- Calcula net_amount e garante fee_amount/gross_amount não nulos
update public.cash_ledger
set fee_amount = coalesce(fee_amount, 0);

update public.cash_ledger
set gross_amount = coalesce(gross_amount, 0);

update public.cash_ledger
set net_amount = gross_amount - fee_amount
where net_amount is null or net_amount = 0;

-- Define o usuário responsável: tenta puxar do vendor da venda associada
update public.cash_ledger ledger
set user_id = coalesce(ledger.user_id, sales.vendor_id)
from public.sales
where ledger.sale_id = sales.id
  and ledger.user_id is null;

update public.cash_ledger
set user_id = coalesce(user_id, '00000000-0000-0000-0000-000000000000');

-- Defaults e constraints finais
alter table if exists public.cash_ledger
    alter column id set default gen_random_uuid(),
    alter column payment_method set not null,
    alter column gross_amount set not null,
    alter column fee_amount set default 0,
    alter column fee_amount set not null,
    alter column net_amount set not null,
    alter column entry_type set default 'sale',
    alter column entry_type set not null,
    alter column created_at set default timezone('UTC', now());

-- Índices úteis para consultas
create index if not exists idx_cash_ledger_created_at on public.cash_ledger (created_at desc);
create index if not exists idx_cash_ledger_payment_method on public.cash_ledger (payment_method);
create index if not exists idx_cash_ledger_user_id on public.cash_ledger (user_id);

-- Configurações das taxas da maquininha (payments_config)
DO $$
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM information_schema.columns
        WHERE table_schema = 'public'
          AND table_name = 'payments_config'
          AND column_name = 'payment_method'
    ) THEN
        CREATE TABLE public.payments_config_v2 (
            id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
            payment_method text NOT NULL UNIQUE,
            fee_percentage numeric(8,4) NOT NULL DEFAULT 0,
            fee_fixed numeric(12,2) NOT NULL DEFAULT 0,
            allow_discount boolean NOT NULL DEFAULT true,
            max_discount_percentage numeric(5,2) NOT NULL DEFAULT 0,
            updated_at timestamptz NOT NULL DEFAULT timezone('UTC', now())
        );

        IF EXISTS (
            SELECT 1 FROM information_schema.columns
            WHERE table_schema = 'public'
              AND table_name = 'payments_config'
              AND column_name = 'cc_fee_percent'
        ) THEN
            WITH legacy AS (
                SELECT
                    coalesce(cc_fee_percent, 0) AS cc_percent,
                    coalesce(dc_fee_percent, 0) AS dc_percent,
                    coalesce(pix_fee_percent, 0) AS pix_percent,
                    coalesce(allow_discount, true) AS allow_discount,
                    coalesce(max_vendor_discount_percent, 0) AS max_discount
                FROM public.payments_config
                LIMIT 1
            )
            INSERT INTO public.payments_config_v2 (payment_method, fee_percentage, fee_fixed, allow_discount, max_discount_percentage)
            SELECT payment_method, fee_percentage, 0::numeric(12,2), allow_discount, max_discount
            FROM (
                SELECT 'credito'::text AS payment_method, cc_percent AS fee_percentage, allow_discount, max_discount FROM legacy
                UNION ALL
                SELECT 'debito', dc_percent, allow_discount, max_discount FROM legacy
                UNION ALL
                SELECT 'pix', pix_percent, allow_discount, max_discount FROM legacy
            ) AS seed
            ON CONFLICT (payment_method) DO UPDATE
                SET fee_percentage = excluded.fee_percentage,
                    allow_discount = excluded.allow_discount,
                    max_discount_percentage = excluded.max_discount_percentage,
                    updated_at = timezone('UTC', now());
        END IF;

        DROP TABLE IF EXISTS public.payments_config;
        ALTER TABLE public.payments_config_v2 RENAME TO payments_config;
    END IF;
END $$;

ALTER TABLE IF EXISTS public.payments_config
    ADD COLUMN IF NOT EXISTS fee_percentage numeric(8,4) DEFAULT 0,
    ADD COLUMN IF NOT EXISTS fee_fixed numeric(12,2) DEFAULT 0,
    ADD COLUMN IF NOT EXISTS allow_discount boolean DEFAULT true,
    ADD COLUMN IF NOT EXISTS max_discount_percentage numeric(5,2) DEFAULT 0,
    ADD COLUMN IF NOT EXISTS updated_at timestamptz DEFAULT timezone('UTC', now());

ALTER TABLE IF EXISTS public.payments_config
    ALTER COLUMN fee_percentage SET NOT NULL,
    ALTER COLUMN fee_fixed SET NOT NULL,
    ALTER COLUMN allow_discount SET NOT NULL,
    ALTER COLUMN max_discount_percentage SET NOT NULL,
    ALTER COLUMN updated_at SET NOT NULL;

CREATE UNIQUE INDEX IF NOT EXISTS payments_config_payment_method_key ON public.payments_config (payment_method);

commit;

-- Verificação rápida
-- select id, payment_method, gross_amount, fee_amount, net_amount, entry_type, created_at
-- from public.cash_ledger
-- order by created_at desc
-- limit 10;
