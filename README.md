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

Sign in with the seeded demo account: `lejla.karic@rokovnik.test` /
`demo-lozinka` (fictional data only). Locally `DEMO_TODAY=2026-11-11` pins
"today" so the seeded scenario matches the prototype; remove it to use the
real date.

| URL                                          | What                                                 |
| -------------------------------------------- | ---------------------------------------------------- |
| `/`                                          | Public landing page (Inertia SSR)                    |
| `/demo`                                      | Live demo: a private 24-hour demo office per visitor |
| `/danas`, `/predmeti`, `/kalendar`, `/posta` | The app (after sign-in)                              |

Run `php artisan schedule:work` (or a cron entry for `schedule:run`) so expired
demo offices are pruned hourly (`php artisan demo:prune`).

## Mobile app

`mobile/` is an Expo (React Native) app using the same backend through the
JSON API `/api/v1` (Sanctum tokens). Screen data comes from the same
`App\Screens` classes and every change runs the same domain actions as the web
app. See `mobile/README.md`.

## Rules the code enforces

- Deadlines, events, notes and cost items start as `proposed`; only an explicit
  action confirms them (`confirmed_by`, `confirmed_at`). Proposals never create
  reminders.
- Confirming a deadline needs three separate acknowledgements — checked in
  `ConfirmDeadline`, `ConfirmDeadlineRequest` and the `deadlines_acknowledged_check`
  database constraint.
- Deadline arithmetic: last day = start + N calendar days; no holiday logic.
- No tariff data; amounts are entered by the lawyer.
- Every significant change goes through `App\Support\Audit::record()`.
- Document reading and dictation use `DocumentExtractor` / `SpeechTranscriber`
  with fake drivers only; nothing calls an external service and nothing is sent.
- Every query is scoped to the user's office (`BelongsToOffice`) and checked by
  policies; uploads live on the private disk and are served only through
  `/dokumenti/{id}/datoteka`.

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
