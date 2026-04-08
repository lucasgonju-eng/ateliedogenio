-- Table to register audit events from the application
create table if not exists public.audit_logs (
    id uuid primary key default gen_random_uuid(),
    actor_id uuid,
    actor_role text,
    entity text not null,
    entity_id uuid,
    action text not null,
    payload jsonb not null default '{}'::jsonb,
    created_at timestamptz not null default now()
);

create index if not exists audit_logs_entity_idx
    on public.audit_logs(entity, entity_id);

create index if not exists audit_logs_created_at_idx
    on public.audit_logs(created_at desc);
