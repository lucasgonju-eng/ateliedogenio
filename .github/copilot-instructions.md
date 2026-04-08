### Ateliê do Gênio — Agent instructions (concise)

This repository is a small PHP project (PSR-4, PHP 8.2) that provides an HTTP API and simple web frontend. The app is built with a minimal custom framework: a small DI container, FastRoute for routing, Nyholm PSR-7, and a thin HTTP kernel. Most persistence and business logic interact with Supabase through a small client layer.

What an AI agent must know up front
- Bootstrapping: see `bootstrap/app.php` and `bootstrap/container.php` — environment variables from `.env` are loaded and the app returns an `HttpKernel` instance.
- Routing: routes are registered in `routes/api.php` via `Infrastructure\Http\Routing\RouteGroup`. Middleware aliases live in `config/middleware.php` and are applied per-route (e.g. `['auth', 'role:admin']`).
- Dependency registration: `bootstrap/container.php` wires application services and repositories. Prefer resolving dependencies by their interfaces (e.g. `SaleRepositoryInterface`, `AuthGatewayInterface`) when editing or adding services.
- Supabase integration: `src/Infrastructure/Supabase/*` contains the HTTP client (`SupabaseClient`), RPC helper (`SupabaseRpcClient`) and gateways (auth, sales). Many repositories call Supabase directly — treat Supabase calls as external side effects and preserve error handling/logging patterns found in `SupabaseClient`.

Common tasks and helpful commands
- Serve locally (dev): composer scripts - open a terminal and run `composer run-script serve` (runs `php -S localhost:8080 -t public`).
- Run tests: `composer test` which executes `phpunit` (phpunit config at `phpunit.xml.dist`, bootstrap `tests/bootstrap.php`).
- Export API docs/endpoints: `composer run-script docs:endpoints` runs `scripts/export-routes.php`.

Project-specific conventions and patterns
- Controllers: controllers live under `src/Http/Controller` and their public methods accept a PSR-7 ServerRequest and route parameters array. Route handlers are registered as `[ControllerClass::class, 'method']`.
- Middleware pipeline: `RouteGroup` builds middleware from aliases. Middleware may receive parameters using `alias:parameter` (e.g. `role:admin`). Middleware objects are resolved from the container and may implement optional `setParameter()` and `setRouteParameters()` methods.
- DI container style: the simple `Infrastructure\Container\Container` stores closures that receive the container and return instances. When adding services, follow the file `bootstrap/container.php` style and register both concrete class and interface aliases when present.
- Error/logging: use `LoggerFactory` from the container for structured logging. `SupabaseClient` logs debug + error details around remote calls — follow its pattern for other HTTP clients.
- Views: the app uses `Infrastructure\View\ViewRenderer` (PSR-7 response factories) for web endpoints under `resources/views`.

Integration and external dependencies
- Supabase: required env vars are `SUPABASE_URL`, `SUPABASE_ANON_KEY`, `SUPABASE_SERVICE_ROLE_KEY`, `SUPABASE_JWT_SECRET`. Supabase calls are centralized in `src/Infrastructure/Supabase`.
- Mail: Symfony Mailer is wrapped by `Infrastructure\Email\SymfonyMailer`, configured from env vars like `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD`.
- Third-party libs of note (composer.json): `fast-route`, `nyholm/psr7`, `guzzlehttp/guzzle`, `monolog/monolog`, `firebase/php-jwt`.

Code editing guidelines for agents
- When changing a controller or service, update or add a container binding in `bootstrap/container.php` if the type is constructed in the container.
- Add route handlers to `routes/api.php` using the existing `RouteGroup` helpers. Preserve middleware alias semantics.
- Tests: put unit tests in `tests/Unit` and integration tests in `tests/Integration`. PHPUnit bootstrap is `tests/bootstrap.php`.
- Avoid hard-coding environment values in code. Read values from `$_ENV` or use the container factory pattern.

Examples from the repo
- Route with middleware: `routes/api.php` registers ` $group->get('/products', [ProductController::class, 'index'], ['auth', 'role:admin']);`
- Container binding example: `SaleCheckoutGatewayInterface::class => static function (Container $container): SaleCheckoutGatewayInterface { return new SupabaseSaleCheckoutGateway($container->get(SupabaseRpcClient::class)); }` in `bootstrap/container.php`.

When to ask the user
- If a change affects database schema or Supabase RPCs, ask for confirmation and testing guidance (supabase credentials or staging environment).
- If a runnable environment is required (DB, Supabase), request credentials or a local mock strategy.

What not to change
- Do not change the route registration format, middleware alias names in `config/middleware.php`, or the public signatures of controller methods (they must accept PSR-7 request and route params).

If anything here is unclear or you want more detail (example: specifics about tests, environment variables, or Supabase RPC names), tell me which section to expand and I'll iterate.
