Configuração de Rate Limiting

- Middleware: AtelieDoGenio\Http\Middleware\RateLimitMiddleware
- Padrão: 5 tentativas a cada 900 segundos (15 min) por sessão/usuário
- Variáveis de ambiente:
  - RATE_LIMIT_MAX_ATTEMPTS (ex.: 60)
  - RATE_LIMIT_DECAY_SECONDS (ex.: 60)
  - RATE_LIMIT_ENABLED (0/false para desativar globalmente)

Notas:
- Só é aplicado quando existe cookie de sessão ADG_SESSION (estadoful)
- Requisições que excedem o limite retornam 429 com Retry-After
- Cabeçalhos expostos: X-RateLimit-Limit, X-RateLimit-Remaining, X-RateLimit-Reset
