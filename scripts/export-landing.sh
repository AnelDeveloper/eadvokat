#!/bin/sh
# Exports the public landing page as a static site (server-rendered HTML + CSS,
# no JavaScript) for hosting on Vercel. App links point to APP_ORIGIN.
#
#   scripts/export-landing.sh https://web-production-xxxx.up.railway.app [out-dir]
set -e

APP_ORIGIN="${1:?usage: export-landing.sh <app-origin> [out-dir]}"
OUT="${2:-dist-landing}"
PORT=8099
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

# The Vite dev server's "hot" file would make Laravel point at the dev server.
[ -f public/hot ] && mv public/hot public/hot.export-backup
cleanup() {
    [ -n "$SSR_PID" ] && kill "$SSR_PID" 2>/dev/null || true
    [ -n "$WEB_PID" ] && kill "$WEB_PID" 2>/dev/null || true
    [ -f public/hot.export-backup ] && mv public/hot.export-backup public/hot
}
trap cleanup EXIT

VITE_APP_ORIGIN="$APP_ORIGIN" npm run build:ssr

node bootstrap/ssr/app.js >/dev/null 2>&1 &
SSR_PID=$!
APP_URL="http://127.0.0.1:$PORT" INERTIA_SSR_ENABLED=true DEMO_TODAY= \
    php artisan serve --port=$PORT >/dev/null 2>&1 &
WEB_PID=$!

for _ in $(seq 1 30); do
    curl -sf "http://127.0.0.1:$PORT/up" >/dev/null && break
    sleep 1
done
sleep 2

rm -rf "$OUT" && mkdir -p "$OUT"
curl -sf "http://127.0.0.1:$PORT/" \
    | sed -e "s#http://127.0.0.1:$PORT##g" \
    | perl -0pe 's#<script\b[^>]*>.*?</script>\s*##gs; s#<link rel="modulepreload"[^>]*>\s*##g' \
    > "$OUT/index.html"

grep -q 'Od primljene pošte do potvrđenog roka' "$OUT/index.html" || { echo "SSR output missing"; exit 1; }

mkdir -p "$OUT/build"
cp -R public/build/assets "$OUT/build/assets"
cp public/favicon.ico public/apple-touch-icon.png public/robots.txt "$OUT/"
cp -R public/brand "$OUT/brand"

cat > "$OUT/vercel.json" <<JSON
{
    "cleanUrls": true,
    "headers": [
        { "source": "/build/assets/(.*)", "headers": [{ "key": "Cache-Control", "value": "public, max-age=31536000, immutable" }] },
        { "source": "/(.*)", "headers": [
            { "key": "X-Content-Type-Options", "value": "nosniff" },
            { "key": "Referrer-Policy", "value": "strict-origin-when-cross-origin" }
        ] }
    ],
    "redirects": [
        { "source": "/login", "destination": "$APP_ORIGIN/login", "permanent": false },
        { "source": "/demo", "destination": "$APP_ORIGIN/demo", "permanent": false },
        { "source": "/registracija", "destination": "$APP_ORIGIN/registracija", "permanent": false }
    ]
}
JSON

echo "Exported landing page to $OUT"
