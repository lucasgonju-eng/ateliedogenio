do $$
declare
  target_email text := 'venda@ateliedogenio.com.br';
  v_user_id uuid;
  v_role_id public.roles.id%TYPE;
begin
  select id into v_user_id from auth.users where email = target_email;
  if v_user_id is null then
    raise exception 'Usuario % nao encontrado em auth.users', target_email;
  end if;

  select id into v_role_id from public.roles where name ilike 'vendedor' limit 1;
  if v_role_id is null then
    raise exception 'Role vendedor nao encontrada em public.roles';
  end if;

  update public.users
     set role_id = v_role_id,
         email   = target_email,
         active  = true
   where id = v_user_id;

  if not found then
    insert into public.users (id, role_id, name, email, password_hash, active)
    values (v_user_id, v_role_id, 'Vendas', target_email, 'auth_managed', true);
  end if;

  update auth.users
     set raw_app_meta_data  = coalesce(raw_app_meta_data,  '{}'::jsonb) || jsonb_build_object('role','vendedor'),
         raw_user_meta_data = coalesce(raw_user_meta_data, '{}'::jsonb) || jsonb_build_object('role','vendedor')
   where id = v_user_id;
end $$;

select u.id, u.email, r.name as role
from public.users u
join public.roles r on r.id = u.role_id
where u.id = (select id from auth.users where email = 'venda@ateliedogenio.com.br');
