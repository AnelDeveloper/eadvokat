{{-- Self-contained error page: no Vite assets, so it renders even when the app itself cannot. --}}
<!DOCTYPE html>
<html lang="bs">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="robots" content="noindex">
        <link rel="icon" href="/favicon.ico" sizes="any">
        <title>@yield('title') · Ročište</title>
        <style>
            *{box-sizing:border-box}
            body{margin:0;min-height:100svh;display:grid;place-items:center;padding:24px;background:#f4f6f9;color:#14273a;font:16px/1.5 ui-sans-serif,system-ui,-apple-system,"Segoe UI",sans-serif}
            main{width:100%;max-width:440px;background:#fff;border:1px solid #e0e6ec;border-radius:14px;padding:28px;box-shadow:0 1px 2px rgb(16 32 48/.04),0 4px 12px -4px rgb(16 32 48/.08)}
            .brand{display:flex;align-items:center;gap:10px;font:600 22px/1 Georgia,serif;color:#14273a;text-decoration:none}
            .code{margin:24px 0 4px;font:500 13px/1 ui-monospace,monospace;letter-spacing:.08em;color:#5c6e7e}
            h1{margin:0;font-size:24px;line-height:1.25}
            p{margin:8px 0 0;color:#4a5b6b}
            .actions{margin-top:22px;display:flex;flex-wrap:wrap;gap:10px}
            a.btn{display:inline-flex;align-items:center;min-height:44px;padding:0 18px;border-radius:10px;font-weight:600;text-decoration:none}
            .primary{background:#2366d1;color:#fff}.primary:hover{background:#1b54b0}
            .outline{border:1px solid #c9d3dc;color:#14273a}.outline:hover{border-color:#2366d1}
        </style>
    </head>
    <body>
        <main>
            <a class="brand" href="/"><img src="/brand/mark.svg" width="30" height="30" alt="">Ročište</a>
            <div class="code">GREŠKA @yield('code')</div>
            <h1>@yield('title')</h1>
            <p>@yield('message')</p>
            <div class="actions">
                <a class="btn primary" href="/danas">Idi na Danas</a>
                <a class="btn outline" href="/">Početna stranica</a>
            </div>
        </main>
    </body>
</html>
