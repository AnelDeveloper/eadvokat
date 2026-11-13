# Ročište — mobile app

Expo (React Native, TypeScript, Expo Router) client for the same Laravel
backend, through the JSON API at `/api/v1` (Sanctum bearer tokens).

- Routes in `src/app/` mirror the web paths (`/danas`, `/predmeti/1`,
  `/rokovi/5/prijedlog`, `/diktat/4`, …), so paths returned by the API open
  directly.
- UI copy, date/money formatters and display types are shared with the web app
  (`@shared/*` → `../resources/js/*`); there is one Bosnian `bs.ts`.
- Design tokens live in `src/theme/tokens.ts` (same values as the web theme).
- The token is stored in the device keychain/keystore (`expo-secure-store`).
- Camera and file picking: `expo-image-picker`, `expo-document-picker`;
  dictation: `expo-audio`; the cost-sheet PDF is downloaded with the token and
  opened in the share sheet (`expo-file-system`, `expo-sharing`). Nothing is
  sent to courts, clients or third parties.

## Run

Requires Node 22.13+ (see `.nvmrc`; Homebrew: `/opt/homebrew/opt/node@22/bin`).

```bash
# backend (repo root), reachable from simulators/devices
php artisan serve --host=0.0.0.0 --port=8037

cd mobile
cp .env.example .env          # set EXPO_PUBLIC_API_URL for your device
npm install
npx expo start --ios          # iOS simulator (Expo Go)
npx expo start --android      # Android emulator: EXPO_PUBLIC_API_URL=http://10.0.2.2:8037
npx expo start --web          # browser, handy for quick checks
```

Sign in with the seeded demo account `lejla.karic@rokovnik.test` /
`demo-lozinka` (fictional data only).

## Checks

```bash
npx tsc --noEmit
npx expo lint
```

API tests live in the Laravel suite: `tests/Feature/Api/MobileApiTest.php`.
