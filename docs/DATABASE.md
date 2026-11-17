# Database & Schema Workflow

RentFlow maps an **existing production schema** — there is no `database/migrations` directory that defines the whole schema. The only migration present (`2026_11_10_000001_create_sms_logs_table.php`) is additive and was written after the initial split. All other tables live in a private MySQL dump.

## 1. The private dump (never commit it)

The production schema lives in `rentalflow_local.sql` at the repo root. This file:

- Is **never committed** to git (blocked by `.gitignore`: `*.sql`, `*.dump`).
- Contains real table definitions (DDL) and, in production dumps, real rows.
- Must be loaded into a local dev database (e.g. `rentalflow`) before running tests.

To load it locally (never into the repo):

```bash
mysql -u <user> -p<pass> rentalflow < rentalflow_local.sql
```

## 2. How tests build the schema

The test suite cannot use `migrate:fresh` / `RefreshDatabase` because that would `DROP` every table and leave an empty database. Instead:

1. **`php artisan rentflow:test-db`** (`app/Console/Commands/RebuildTestDatabase.php`) copies the **structure only** (no rows) from a source dev database into the test database `rentalflow_test`:
   ```bash
   php artisan rentflow:test-db --source=rentalflow --target=rentalflow_test
   ```
   Under the hood it `DROP DATABASE IF EXISTS rentalflow_test`, `CREATE DATABASE rentalflow_test`, then you import the structure via:
   ```bash
   mysqldump --no-data --skip-add-drop-table rentalflow | mysql rentalflow_test
   ```

2. **`tests/Pest.php`** wraps every Feature test in `DatabaseTransactions::class`, so each test runs in a transaction that is rolled back afterwards. The real DDL (enums, column types, absence of `updated_at` on some tables, etc.) is exercised because the test DB mirrors the dev schema.

3. `TenantContext` is cleared after every test so no owner context leaks between tests.

## 3. Adding new migrations

New tables and columns are added via **expand-then-contract migrations**:

| Step | What |
|------|------|
| **Expand** | Add the new table/column first. Backfill data from existing columns. |
| **Switch** | Update code to read/write the new column/table. |
| **Contract** | (Later, separate migration) Drop the old column only after all code has moved off it. |

Every migration must:

- Guard with `Schema::hasTable()` / `Schema::hasColumn()` so it is idempotent.
- Have a working `down()` that reverses the change.
- Be run against a **copy of the data first** (never production directly).

Example skeleton:

```php
Schema::create('caretaker_property', function (Blueprint $table) {
    $table->unsignedInteger('caretaker_id');
    $table->unsignedInteger('property_id');
    $table->unsignedInteger('owner_id');
    $table->unique(['caretaker_id', 'property_id'], 'caretaker_property_unique');
    // timestamps, foreign keys, etc.
});
```

## 4. Migrations in the test database

Because the test DB is rebuilt from the dev schema structure (no rows), any new migration must be applied to the dev database **before** `rentflow:test-db` rebuilds the test DB, so the test DB includes the new tables/columns:

```bash
# 1. Run migration against dev DB
php artisan migrate --database=rentalflow

# 2. Rebuild test DB from dev structure
php artisan rentflow:test-db --source=rentalflow --target=rentalflow_test

# 3. Run the suite
php artisan test
```

## 5. Golden rules

- Never commit `*.sql`, `*.dump`, `.env`, or any file containing real rows or secrets.
- The `database/migrations` directory contains only additive, reviewed migrations — never the full schema.
- `composer.lock` IS committed (it was previously ignored by mistake).
- `.gitignore` blocks `*.sql`, `*.sql.gz`, `*.sql.zip`, `*.dump`, `*.sqlite`, `*.db` at the repo root.
