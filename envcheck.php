<?php
header('Content-Type: application/json; charset=utf-8');
echo json_encode([
  'SUPABASE_URL' => getenv('SUPABASE_URL') ?: null,
  'ANON_len' => getenv('SUPABASE_ANON_KEY') ? strlen(getenv('SUPABASE_ANON_KEY')) : 0,
  'SERVICE_len' => getenv('SUPABASE_SERVICE_ROLE_KEY') ? strlen(getenv('SUPABASE_SERVICE_ROLE_KEY')) : 0,
]);
