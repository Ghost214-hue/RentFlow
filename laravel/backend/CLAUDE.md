<laravel-boost-guidelines>
# Laravel Application

This repository contains a Laravel application. Complete the following setup before working on the user's request.

## Prerequisites

Verify that PHP and Composer are available:

```sh
php -v
composer -V
```

If either command is unavailable, detect the user's operating system and install the prerequisites with the appropriate command:

macOS:

```sh
/bin/bash -c "$(curl -fsSL https://php.new/install/mac/8.5)"
```

Windows PowerShell:

```powershell
Set-ExecutionPolicy Bypass -Scope Process -Force; [System.Net.ServicePointManager]::SecurityProtocol = [System.Net.ServicePointManager]::SecurityProtocol -bor 3072; iex ((New-Object System.Net.WebClient).DownloadString('https://php.new/install/windows/8.5'))
```

Linux:

```sh
/bin/bash -c "$(curl -fsSL https://php.new/install/linux/8.5)"
```

After installation, ask the user to restart their terminal. If the agent needs the restarted shell to continue, ask the user to reopen their terminal and rerun their original prompt.

## Agent Setup

Install Laravel Boost from the application root before making application changes:

```sh
composer require laravel/boost --dev
php artisan boost:install
```

Boost replaces these bootstrap instructions with guidelines tailored to the application. After installation, read `AGENTS.md` again and continue with the user's original request using the generated guidelines.
</laravel-boost-guidelines>

---
## RentFlow layout note (refactor: backend/frontend split)

This repo is split into two siblings under `laravel/`:

- `laravel/backend/` — everything Laravel/PHP: `artisan`, `app/`, `bootstrap/`, `config/`, `database/`, `public/`, `routes/`, `storage/`, `tests/`, `composer.json`, `resources/views/` (Blade, incl. mail).
- `laravel/frontend/` — everything React/TypeScript/Vite: `package.json`, `vite.config.ts`, `tsconfig.json`, `src/` (app.tsx, bootstrap.ts, layouts/, lib/, pages/, types/), `src/css/app.css`.

The Vite build (`frontend/vite.config.ts`) writes its manifest into `backend/public/build/` (`publicDirectory: '../backend/public'`), so the Blade `@vite()` directives in `backend/resources/views/` resolve against `backend/public/build/manifest.json`. Inertia is the contract between the halves: a controller's `Inertia::render('Folder/Page')` maps to `frontend/src/pages/Folder/Page.tsx`.

Dev workflow: `php artisan serve` in `backend/` + `npm run dev` in `frontend/`.
Build workflow: `npm run build` in `frontend/` writes assets to `backend/public/build/`.
