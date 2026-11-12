# Rokovnik

Mail register (knjiga pošte), deadline book (rokovnik), reminders and a draft
cost sheet (troškovnik) for a small law office in Bosnia and Herzegovina.
Laravel 13 + Inertia 3 + Vue 3 (TypeScript) + Tailwind 4, PostgreSQL.

The design reference lives in `design_handoff_rokovnik/` (README + clickable
prototype). UI copy is Bosnian; code and comments are English.

## Local setup

Requirements: PHP 8.3+, Composer, Node 20.19+, PostgreSQL 15+ (any standard
Postgres; no extensions).

```bash
# once: database role and databases
psql -U postgres -h 127.0.0.1 \
  -c "CREATE ROLE rokovnik LOGIN PASSWORD 'rokovnik';" \
  -c "CREATE DATABASE rokovnik OWNER rokovnik;" \
  -c "CREATE DATABASE rokovnik_test OWNER rokovnik;"

composer install && npm install
cp .env.example .env && php artisan key:generate
php artisan migrate:fresh --seed
composer dev            # Laravel on http://127.0.0.1:8000 + Vite (with SSR)
```

## Checks

```bash
./vendor/bin/pest       # PHP feature + unit tests (uses rokovnik_test)
npm test                # Vitest (TS helpers)
npm run check           # lint + format (Vite+)
npm run types:check     # vue-tsc
./vendor/bin/pint && ./vendor/bin/phpstan analyse
```

## Production SSR

```bash
npm run build:ssr && php artisan inertia:start-ssr
```
