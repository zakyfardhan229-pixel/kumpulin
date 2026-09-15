# AGENTS.md

Fresh Laravel 12 skeleton (`laravel/laravel`) renamed to Kumpulin. `routes/web.php` is still only the default `welcome` view — app logic not yet built.

## Stack

- PHP `^8.2`, Laravel 12 + Breeze (Blade, Alpine), Tailwind **v3** (PostCSS; Breeze stack) + Lucide icons (`lucide` npm, `data-lucide` + `createIcons()` in `app.js`), Axios.
- Auth: `routes/auth.php` (Breeze, restored from stub — do not delete), profile at `/profile`, admin area under `/admin` (`admin.` names). `RouteServiceProvider::HOME` is `/dashboard`, which redirects to `admin.dashboard`.
- Layouts: `layouts/admin.blade.php` (`<x-admin-layout>`, sidebar) for authenticated pages; `layouts/student.blade.php` (`<x-student-layout>`) for public pages; `layouts/guest.blade.php` for auth. Old Breeze `layouts/app` + `layouts/navigation` were removed.
- Blade layouts resolve via **class** components (`app/View/Components/*Layout.php`), not file convention — adding a new layout requires a matching component class (Breeze pattern).
- Validation: optional checkboxes must use `sometimes|accepted`, not `nullable|accepted` (`accepted` is implicit and fails on missing fields).
- Branding: Plus Jakarta Sans (Google Fonts import in `app.css` + layout `<link>`s), `primary` palette in `tailwind.config.js` (`#2563EB` = `primary-600`).
- AI: Arnaru-AI, **no API key** — `config/services.php` `arnaru` = `base_url`/`model`/`timeout` from `ARNARU_AI_*` env. File field is `files` (not `files[]`), max 9.
- Arnaru-AI returns **SSE even for normal POSTs**: `data: {"success":true,"answer":"<token>",...}` chunks + `data: [DONE]`. Client must concatenate `answer` tokens, then parse the full text as JSON. Verified live 2026-09-15.
- Local admin seed: `admin@kumpulin.test` / `password` (dev only).
- Entrypoints: `bootstrap/app.php` → `routes/web.php`, `routes/console.php`. Frontend inputs: `resources/css/app.css`, `resources/js/app.js`.
- Default DB is SQLite (`.env.example`), but local `.env` is switched to **MySQL** (`127.0.0.1:3306`, db `kumpulin`, user `root`, empty password). Tests ignore this (see below).

## Commands (use these, PowerShell on Windows)

- First setup: `composer setup` (install + `.env` copy + key + migrate + `npm install` + `npm run build`).
- Dev (runs 4 processes: serve + queue:listen + pail logs + vite): `composer dev`. Needs `concurrently` (already in devDeps) and a migrated DB.
- Tests: `composer test` (= `php artisan config:clear` + `php artisan test`). Single test: `php artisan test --filter=TestName` or `php artisan test tests/Feature/ExampleTest.php`.
- Format (Laravel Pint, no custom config = default `laravel` preset): `./vendor/bin/pint` / `./vendor/bin/pint --test` to check only.
- Frontend: `npm run dev` (HMR) / `npm run build` (production; outputs to gitignored `public/build`).
- Migrations: `php artisan migrate`; fresh + seed: `php artisan migrate:fresh --seed`.

## Gotchas

- `SESSION_DRIVER`, `CACHE_STORE`, `QUEUE_CONNECTION` are all `database` — `php artisan migrate` must run before serve/queue/session work, otherwise missing-table errors.
- `phpunit.xml` forces `DB_CONNECTION=sqlite`, `DB_DATABASE=:memory:` plus `array`/`sync` drivers, so tests run without MySQL. Do not change `.env` to fix test failures.
- `database/database.sqlite` is a leftover from project creation; active `.env` points at MySQL, so that file is currently unused.
- `.env` (with `APP_KEY`, currently set) is gitignored — never commit it; copy from `.env.example` on fresh clones.
- EditorConfig: 4-space indent, LF, trim trailing whitespace (except `.md`).
