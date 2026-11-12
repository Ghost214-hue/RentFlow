# RentFlow

RentFlow is a property-management application built with Laravel 13 (backend) and React 19 + TypeScript + Vite (frontend), glued together by Inertia.js.

## Repository layout

```
laravel/
  backend/        Laravel/PHP application
  frontend/       React/TypeScript Vite application
```

### `laravel/backend/` — Laravel/PHP

Everything server-side lives here: `artisan`, `app/`, `bootstrap/`, `config/`,
`database/`, `public/`, `routes/`, `storage/`, `tests/`, `composer.json`,
`resources/views/` (Blade templates, including mail).

### `laravel/frontend/` — React/TypeScript/Vite

Everything client-side lives here: `package.json`, `vite.config.ts`,
`tsconfig.json`, `src/` (`app.tsx`, `bootstrap.ts`, `layouts/`, `lib/`,
`pages/`, `types/`), and `src/css/app.css`.

The Vite build writes its manifest into `backend/public/build/`
(`publicDirectory: '../backend/public'` in `frontend/vite.config.ts`), so the
Blade `@vite()` directives in `backend/resources/views/app.blade.php` resolve
against `backend/public/build/manifest.json`.

## How the halves connect (Inertia is the contract)

There is **no REST API**. Auth is session + CSRF cookie. The backend and
frontend are coupled by Inertia:

- A controller returns `Inertia::render('Folder/Page', [...])`.
- That maps to `frontend/src/pages/Folder/Page.tsx` (resolved via
  `import.meta.glob` in `frontend/src/app.tsx`).
- Route names, controller names, Inertia page names and props are unchanged by
  the split.

## Development workflow (two terminals)

Terminal 1 — backend (Laravel):
```
cd laravel/backend
composer install   # (run once; move your .env into backend/ first)
cp .env.example .env
php artisan key:generate
php artisan serve   # http://127.0.0.1:8000
```

Terminal 2 — frontend (Vite dev server):
```
cd laravel/frontend
npm install   # (run once)
npm run dev   # http://localhost:5173 (HMR proxied to Laravel)
```

Load `http://127.0.0.1:8000/login` — assets are served via the Vite dev
server (no 404s on `/build`).

## Build / deploy workflow

From `laravel/frontend/`:
```
npm install
npm run build
```
This writes versioned assets to `laravel/backend/public/build/` and produces
`laravel/backend/public/build/manifest.json`, which the Blade views read via
`@vite()`.

Then serve `laravel/backend/public/` with your web server (nginx, Apache, or
`php artisan serve`).

## Stray debug script

`laravel/_t1.php` is a leftover debug script that boots the app and renders
mail templates. It was intentionally **not** moved during this refactor — decide
whether to delete it or move it into `laravel/backend/`.