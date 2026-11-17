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

## Items 2-3: Schema migrations (users table, user_id FKs, caretaker_property pivot, child-table owner_id, admin/audit/idempotency schema)
- [x] Migration: users table (globally unique email, 2FA columns)
- [x] Migration: nullable user_id on owners, caretakers, tenants
- [x] Migration: caretaker_property pivot table
- [x] Migration: owner_id on bill_items, payment_allocations, password_setup_tokens
- [x] Migration: admins, audit_logs, support_access_grants, idempotency_keys, caretaker_permissions
- [ ] Artisan commands: identity:audit, identity:backfill
- [ ] Login overhaul: authenticate against users, return profiles, auto-select
- [ ] Move password reset / first-password setup onto users

## Item 4: Permissions and controlled bypass
- [x] config/permissions.php with role defaults
- [x] Rewrite policies to read the matrix (PaymentPolicy, RenterPolicy done)
- [x] CrossTenant::run() helper + architecture test
- [ ] Isolation test suite
- [ ] Replace remaining raw withoutGlobalScopes() calls

## Item 5: Super admin platform
- [x] Admin model created
- [x] Migration: admins, audit_logs, support_access_grants, idempotency_keys
- [ ] Admin guard + two-factor mandatory
- [ ] routes/admin.php with middleware group
- [ ] PlatformContext
- [ ] Admin pages (Overview, Owners, Owner detail, Support access, Email/queue health, Security, System)
- [ ] Support access grants + audit logging for tenant PII

## Item 6: API
- [ ] Sanctum SPA mode
- [ ] routes/api.php with /api/v1 prefix
- [ ] Query classes under app/Queries
- [ ] Idempotency-Key header + idempotency_keys table
- [ ] OpenAPI documentation + TypeScript types

## Item 7: React portals
- [ ] Tenant portal
- [ ] Caretaker portal
- [ ] Owner portal
- [ ] Admin portal

## Item 8: Database guarantees
- [ ] Read-only audit script
- [ ] Composite foreign keys (id, owner_id)
- [ ] Rollback testing

## Item 9: Launch readiness
- [ ] Backup/restore docs + restore test
- [ ] Incident runbook
- [ ] Owner-suspension runbook
- [ ] Monitoring hooks

Decisions:
- Monorepo: laravel/backend (PHP) + laravel/frontend (React)
- Build output: frontend/public/dist -> backend/public/build
- email_logs.body is a `text` column carrying live URLs — mask at render + log
- Users table introduced for unified identity; actors link via user_id
- Caretaker defaults: view assigned properties/renters, record payments, maintenance, complaints. No termination/reversal/bill generation unless owner grants.

Issues encountered:
- Vite config pointed input at `src/app.tsx` which no longer exists (pre-split)
- app.blade.php still loaded `resources/css/app.css` and `resources/js/app.js`
- Existing sms_logs migration FK fails locally (owners.id type mismatch — pre-existing)
