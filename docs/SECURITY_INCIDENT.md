# SECURITY INCIDENT — Production database dump committed to git

**Status:** Contained in the working tree. **Outstanding:** history purge + credential rotation (owner action).
**Severity:** High. **Discovered:** 2026-02-10, during Phase 0 of the Laravel migration.

---

## 1. What happened

A full production MySQL dump of the `cufxccec_rentalflow` database was committed to this repository and pushed to `origin`.

| | |
|---|---|
| **File (removed from tree)** | `backend/database/cufxccec_rentalflow (1).sql` |
| **Size** | ~726 KB |
| **Commit** | `10c6ec7` — *"Apply remaining working-tree changes: controller updates, index routing, and production DB dump"* |
| **Contained in** | `git show 10c6ec7:<path>` on every branch, tag and clone |
| **Origin** | `https://github.com/Ghost214-hue/RentFlow.git` |

### Confirmed exposure classes

| Class | Where in the dump | Risk |
|---|---|---|
| **Bcrypt password hashes** | `owners.password`, `caretakers.password`, `tenants.password` | Offline cracking. `tenants.password` covers renter self-service logins. |
| **Live JWTs, signed with `JWT_SECRET`** | `email_logs.message` (invoice links) | Anyone holding `JWT_SECRET` mints valid tenant/admin tokens. Expiry is 7 days (`config/jwt.php`). |
| **Password-setup tokens** | `email_logs.message` (*"set your account"*) | **Plaintext bearer tokens.** Anyone can take over the named account before the renter does. Invalidate regardless of visibility. |
| **PII** | `tenants.name/email/phone/id_number`, `next_of_kin_*`, `caretakers.id_number` | Direct identifiers, national ID numbers, next-of-kin contacts. |
| **Financial records** | `payments.amount/receipt`, `bills.total`, `email_logs` bodies | Landlord income and renter payment history. |

> **Most time-critical: the password-setup tokens.** Plaintext, single-use, directly grant account access. Treat as compromised regardless of repo visibility.

---

## 2. Containment already applied (PR0, branch `integration/laravel-inertia`)

- [x] File moved **out of the repository** to an ignored location outside the working tree (`RentFlow-private/`, outside `htdocs`, not served by Apache).
- [x] `.gitignore` hardened: `*.sql` / `*.dump` / `*.db` ignored by default, with explicit un-ignore rules for **source** only:
  ```gitignore
  !backend/database/migrations/*.sql
  !backend/database/seeders/*.sql
  !laravel/database/schema/*.sql
  ```
  This matters on its own: without it the next `mysqldump` would be re-added.
- [x] Removed the incorrect `backend/composer.lock` ignore rule (an application must commit its lock file).

---

## 3. REQUIRED OWNER ACTIONS

Cannot be performed from this repository. Ordered by urgency.

### 3.1 Invalidate live secrets — do this first

| # | Action | Why |
|---|---|---|
| 1 | **Invalidate all `password_setup_tokens`** (`DELETE FROM password_setup_tokens;`) | Plaintext bearer tokens in the dump. Highest risk. |
| 2 | **Rotate `JWT_SECRET`** | Every JWT in `email_logs` stays forgeable until it changes. Logs everyone out. |
| 3 | **Rotate DB credentials** | Dump proves host/user/schema. |
| 4 | **Rotate the SMTP/mail password** | If credentials were reused. |
| 5 | **Force password reset for all `owners` and `caretakers`** | Hashes are public. |
| 6 | Treat renter passwords as compromised; notify renters where law requires | `tenants.password` is public. |
| 7 | Review access logs for the exposure window | Unauthorised access cannot be excluded while the dump was public. |

### 3.2 Purge git history

Rewriting history rewrites SHAs for **every** commit — do it once, with the remote quiesced.

```bash
# 1. Back up the current state outside the repo first.
# 2. Ensure no other clone is in use and no CI holds a reference.
git filter-repo --invert-paths \
  --path 'backend/database/cufxccec_rentalflow (1).sql'
```

`git-filter-repo` is the right tool (`git filter-branch` is deprecated). Verify **before** pushing:

```bash
git log --all --oneline -- 'backend/database/cufxccec_rentalflow (1).sql'  # must be empty
git rev-list --all --objects | grep -i 'cufxccec'                          # must be empty
```

Then force-push and have every collaborator re-clone (not pull):

```bash
git push --force --mirror origin
```

**If the repository is public**, a dangling object may stay reachable via its direct blob URL or a cached fork. Also file a request at <https://support.github.com/contact> for *"Remove sensitive data from the repository"* — that is the only step that purges GitHub's own caches. Treat rewrite **and** Support ticket as both, not either.

### 3.3 Verify

- [ ] `JWT_SECRET` rotated; all users signed out; login works on both apps
- [ ] `password_setup_tokens` emptied; setup-password screen asks renters to re-request a link
- [ ] New setup emails still deliver (re-verify mail after rotation)
- [ ] History purge verified with both commands in 3.2
- [ ] A fresh `git clone` contains no `.sql` outside `migrations/`

---

## 4. Why `.gitignore` alone was never enough

The dump was already pushed; `.gitignore` affects **new** commits only. Both steps are required, and **rotation is independent of both**: a secret that was once public must be rotated even after the history is scrubbed, because you cannot know who fetched the repo.

## 5. Ongoing prevention

- Dumps are produced only into the ignored private directory, never into `htdocs`.
- `laravel/database/schema/mysql-schema.sql` (Phase 1) holds **structure only, no rows** — the CI/dev baseline, explicitly not a data dump.
- Phase 0 restores from a **sanitised** copy: real schema, fabricated rows. Golden masters and fixtures use generated PII.
- `composer audit`, `npm audit` and secret scanning enter CI in Phase 1.