# Implementation Log

Date: 2026-11-12
Branch: feat/ui-refinements -> refactor for multi-portal platform

## Item 1: Safety net and build wiring
- [x] Vite: compile to backend/public/build, point app.blade.php at real entries
- [x] Remove stray frontend/src/app.js
- [x] docs/DATABASE.md written
- [x] email_logs.body mask (setup/reset/invoice links)
- [x] .gitignore hardened: *.sql, *.dump, .env
- [x] CI workflow (Pest + frontend build)

Decisions:
- Monorepo: laravel/backend (PHP) + laravel/frontend (React)
- Build output: frontend/public/dist -> backend/public/build
- email_logs.body is a `text` column carrying live URLs — mask at render + log

Issues encountered:
- Vite config pointed input at `src/app.tsx` which no longer exists (pre-split)
- app.blade.php still loaded `resources/css/app.css` and `resources/js/app.js`
