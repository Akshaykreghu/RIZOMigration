# MySQL Server Setup — What's Needed Beyond Importing the Dump

Analysis of `legacy/schema/mypayrol_control_db.sql`, `mypayrol_trial.sql`, `mypayrol_mpm121.sql` to determine what has to exist on the new DigitalOcean droplet's MySQL instance *before* the real data dump is imported, since a schema/data dump only ever creates what's inside the databases it targets — never server-level accounts or config.

## 1. MySQL user accounts — must be created manually, every time

Confirmed: none of the three schema dumps contain a `CREATE USER` for the app's own connection user. MySQL accounts live in `mysql.user` (the system database), which a normal `mysqldump <appdb>` never touches. So:

- **`root`** — already exists on any fresh `apt install mysql-server` (set via `mysql_secure_installation`). Nothing to import; it's a new, unrelated root, not the old server's.
- **App connection user** (call it `mpm_cntrl_usr` to match legacy, or `rizo_app` per [DEPLOYMENT.md](../DEPLOYMENT.md#L69-L84)) — does **not** exist until someone runs `CREATE USER ... IDENTIFIED BY ...` + `GRANT` on the new server. This has to happen before or independent of the dump import; the dump import step doesn't create it.

**Extra found:** one exception. `mypayrol_control_db.sql:326` defines a stored procedure `CreateDatabasesAndUsers` that *dynamically* runs `CREATE DATABASE`, `CREATE USER '...'@'127.0.0.1' IDENTIFIED BY 'Localhost&*()'`, and `GRANT ALL ... WITH GRANT OPTION` to self-provision trial tenant DBs (see §4). That's app logic creating users at runtime, not something the schema import does on its own — flagged separately below since it has real implications for what privileges the app's own DB user needs.

## 2. Charset mismatch with DEPLOYMENT.md

All three dumps declare:
```sql
CREATE DATABASE `mypayrol_control_db` /*!40100 DEFAULT CHARACTER SET latin1 */;
```
482 of ~500 `CREATE TABLE` statements across the three files are `CHARSET=latin1` (only 18 are utf8/utf8mb4). But [DEPLOYMENT.md §4](../DEPLOYMENT.md#L74) currently has:
```sql
CREATE DATABASE mypayrol_control_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```
This is a real mismatch, not cosmetic — the ~90 stored procedures and ~50 views do string comparisons/joins against latin1 columns; mixing collations across a session can cause `Illegal mix of collations` errors in some of that SQL.

**Also a sequencing problem, independent of charset:** the dump itself contains an unconditional `CREATE DATABASE mypayrol_control_db ...;` with no `IF NOT EXISTS`. If DEPLOYMENT.md's own `CREATE DATABASE` step runs first, the import will immediately fail with "database exists" on that very first statement.

**Recommendation:** don't pre-create the database at all — just create the user (no `CREATE DATABASE`/`GRANT ... ON *.*`), then run the schema import (which creates the DB itself, matching legacy's latin1), then `GRANT` the app user on the DB(s) that now exist. A full utf8mb4 migration is a separate, deliberate project — not something to fold silently into this deploy.

## 3. Privileges the import needs beyond plain `INSERT`/`SELECT`

The dumps aren't just tables — per file:

| Object type | control_db | trial | mpm121 |
|---|---|---|---|
| Procedures | 18 | 45 | 44 |
| Functions | 2 | 46 | 45 |
| Triggers | 2 | 24 | 24 |
| Views (`SQL SECURITY DEFINER`) | 0 | 24 | 24 |

DEPLOYMENT.md's `GRANT ALL PRIVILEGES ON <db>.*` already covers `CREATE ROUTINE`, `TRIGGER`, `CREATE VIEW`, `EXECUTE` needed to import and run all of this — no extra grant needed there. Two real gotchas though:

- **Function creation + binary logging.** Only 19/46 and 26/45 `CREATE FUNCTION` statements declare `DETERMINISTIC`. If binary logging is ever turned on for this server (e.g. for backups/replication later) and the importing account isn't `SUPER`, MySQL will refuse to create the non-deterministic ones ("you do not have the SUPER privilege and binary logging is enabled") unless `SET GLOBAL log_bin_trust_function_creators = 1` is set first. A fresh `apt install mysql-server` ships with binlog off, so this likely won't bite on first import — but note it now so it's not a mystery failure later if binlogging gets enabled for backups.
- **View definer identity.** The views' `DEFINER=` clause has been stripped from this dump (only `SQL SECURITY DEFINER` remains), so whichever MySQL account actually runs the import becomes the implicit definer. **Import everything as the one dedicated app user** (not ad hoc as `root`), or views can later fail at query time with "the user specified as a definer does not exist" if that account is ever dropped.

## 4. `CreateDatabasesAndUsers` — self-service tenant provisioning is baked into the schema

[`mypayrol_control_db.sql:326-378`](../legacy/schema/mypayrol_control_db.sql#L326): a live stored procedure that, given a start/end range, loops and per company:
1. `CREATE DATABASE mypayrol_mpm<i>`
2. `CREATE USER 'mypayrol_mpm<i>'@'127.0.0.1' IDENTIFIED BY 'Localhost&*()'` — **hardcoded password, identical for every auto-created tenant DB user**
3. `GRANT ALL ON mypayrol_mpm<i>.* TO ... WITH GRANT OPTION`
4. Inserts a `central_control` row and two `user_credentials` logins (`support<i>` / `trialadmin<i>`) using a **hardcoded SHA1 hash** (`ae227f52e3dbd764815da2e23056fc27d577421c`) for the password

This directly conflicts with the least-privilege guidance already in DEPLOYMENT.md §4 ("never use root for the app's runtime connection", scoped grants only): to actually *run* this procedure, whatever account invokes it needs `CREATE`, `CREATE USER`, and `GRANT OPTION` — i.e., admin-level MySQL privileges, not the scoped `GRANT ALL ON <specific-db>.*` the app's runtime user is supposed to have.

**Needs a product decision before go-live, not just a config step:**
- Is trial self-signup still a live feature on the revamp, or dead weight from an earlier flow? (The dead, commented-out `SiteController::setup()` self-signup PHP code — noted in an earlier conversation — suggests the *web* signup flow was abandoned; this SQL procedure may be a leftover ops/admin tool instead, invoked manually rather than from user-facing code.)
- If kept: it should run under a separate admin-only MySQL account used just for provisioning, not the app's day-to-day runtime user — and the hardcoded password/hash need to change to something generated per tenant.
- If not needed: fine to leave un-invoked (it's dormant until someone calls it), but worth being aware it exists so nobody accidentally triggers it against production with the current hardcoded credentials.

## 5. Cross-database references — the app user needs grants on *both* DBs together

Company-DB stored procedures reference the control DB directly by fully-qualified name, e.g. (`mypayrol_trial.sql:27998`):
```sql
update mypayrol_control_db.emp_device_comp_branch set email=vemail ...
```
So the single MySQL app user needs `GRANT` on `mypayrol_control_db` **and** every company DB simultaneously — not just the "current" company's DB. DEPLOYMENT.md's per-DB grant loop already produces this as long as it's actually run for every DB (control + each company), just worth calling out explicitly since it's an easy step to under-scope by granting only the newest company DB and forgetting the cross-reference back to control_db.

## 6. sql_mode — only set for the import session, not for the running app

Each dump opens with:
```sql
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';
```
This affects only the import session, not how the MySQL *server* behaves for the app's live connections afterward. Dumps were taken from **MySQL 5.7.44**; DEPLOYMENT.md targets **MySQL 8.0.x**. 8.0's default `sql_mode` is stricter in a couple of ways 5.7 wasn't (mainly `NO_AUTO_CREATE_USER` removal aside, `ONLY_FULL_GROUP_BY` was already default in 5.7, so the gap is smaller than a 5.x→8.x jump usually implies) — but if the *old* production server had a customized, more lenient `sql_mode` in its `my.cnf`, that customization won't carry over automatically. Worth a quick check of the old server's `my.cnf`/`SELECT @@GLOBAL.sql_mode` before cutover, and mirroring it in the new droplet's config if it diverges from stock 8.0 defaults — otherwise strict-mode rejections on inserts/updates that the legacy app never had to handle are a plausible post-migration surprise.

## 7. Non-issues confirmed (checked and ruled out)

- No `CREATE EVENT` anywhere — no need to enable/configure the MySQL event scheduler.
- No `CONVERT_TZ`, `FULLTEXT`, `SPATIAL` indexes, `LOAD DATA INFILE`, or `SELECT ... INTO OUTFILE` — no `FILE` privilege or timezone-table (`mysql_tzinfo_to_sql`) loading required.
- No `FEDERATED` or other exotic storage engines — just `InnoDB` (dominant), `MyISAM` (45 tables in trial/mpm121, legacy leftovers — no FK/transaction support, but that's an existing legacy characteristic, not something new to provision), and `MEMORY` (1 table).
- Auth plugin: MySQL 8's default `caching_sha2_password` is fine — `rizo/src/lib/db.ts` uses `mysql2@^3.22.5`, which supports it natively. No need to force `mysql_native_password` in `my.cnf`.
- No `GET_LOCK`/advisory-lock usage, no `BLOB`/`LONGBLOB` columns in these schema files (so no `max_allowed_packet` concern from the schema itself — but the *real* data dump, provided separately, may still contain large rows if documents are stored as blobs elsewhere; worth checking that dump's largest row/packet size specifically, since it isn't visible in these structure-only files).

## 7b. Target is actually MySQL 8.4, not 8.0.x — one risk upgraded from "unlikely" to "check first"

Confirmed 2026-07-22: the droplet runs MySQL **8.4**, not the 8.0.x DEPLOYMENT.md assumed. Re-checked the dump specifically for 5.7→8.4-breaking content: no removed system variables (`query_cache_*` etc.), no `SQL_CACHE`/`SQL_NO_CACHE` hints (removed in 8.0+), and reserved-word collisions in 8.0+ (`role`, `last_value` appear as column/identifier names) are already backtick-quoted in the dump, so safe.

One risk from §3 is now materially more likely: **binary logging defaults to ON as of MySQL 8.0.26+, carrying through 8.4** (earlier assumption of "fresh install probably has binlog off" no longer holds). With ~90 `CREATE FUNCTION` statements across trial/mpm121 and only about half marked `DETERMINISTIC`, importing under a non-`SUPER` account with binlog on will likely fail with error 1418 partway through. **Run this first, as an admin-privileged account (not the scoped app user):**
```sql
SHOW VARIABLES LIKE 'log_bin';
-- if ON:
SET GLOBAL log_bin_trust_function_creators = 1;
```
Recommend a dry run of the schema-only dump (this repo's `legacy/schema/` files) against the droplet before running the real production dump, to catch anything like this cheaply.

## 7c. Dedicated risk register — MySQL 5.7.44 → 8.0.x

Requested separately: assuming the target really is 8.0.x (not 8.4), here's every version-specific risk checked against the actual dump content, ranked by severity. (§7b's binlog finding applies identically here — 8.0.26+ defaults binlog to ON, and `apt install mysql-server` today pulls a point release well past that, so it's listed again for completeness but isn't 8.4-specific.)

| Risk | Severity | Evidence | Action |
|---|---|---|---|
| Binary logging on by default (8.0.26+) breaks non-`DETERMINISTIC` `CREATE FUNCTION` import | **High — likely to fire** | ~90 `CREATE FUNCTION` statements, only ~half marked `DETERMINISTIC` | `SET GLOBAL log_bin_trust_function_creators = 1;` before import (admin account) |
| Implicit `GROUP BY` sort order removed in 8.0 | **Medium — silent behavior change, not a failure** | 74 (`trial`)/71 (`mpm121`) `group by` usages found; only 3/4 pair with an explicit `ORDER BY` in the same statement — the other ~70 relied on 5.7's implicit sort | Reports/views/procs using bare `GROUP BY` may return rows in a different order post-migration. Not a data-integrity issue, but flag to whoever owns payroll/report QA — spot-check a few of the ~70 for order-sensitive output before go-live, add explicit `ORDER BY` if needed |
| `utf8mb4` default collation changed (5.7: `utf8mb4_general_ci` → 8.0: `utf8mb4_0900_ai_ci`) | **Medium** | 7 tables declare `DEFAULT CHARSET=utf8mb4` with **no explicit `COLLATE`** (`mypayrol_trial.sql:43124`, `mypayrol_control_db.sql:1348/2446/2464/2796/2811`, `mypayrol_mpm121.sql:86818`) — these will silently pick up `utf8mb4_0900_ai_ci` on import into an 8.0 server, differing from whatever collation they actually had on the 5.7 source | If any of these 7 tables get joined/compared against other utf8mb4 data at a different collation (can't confirm from schema-only files — depends on the real data dump), expect possible "Illegal mix of collations" errors or subtly different string comparison behavior. Cheapest fix: add explicit `COLLATE=utf8mb4_general_ci` to these 7 tables' `CREATE TABLE` (or `ALTER TABLE ... COLLATE`) after import, to match the other 89% of the schema's default (`latin1`) collation semantics as closely as possible, unless there's a reason to want 8.0's newer collation |
| Removed system variables / `SQL_CACHE` hints / query cache | **None found** | Explicitly grepped for `query_cache_*`, `SQL_CACHE`/`SQL_NO_CACHE`, `old_passwords`, `thread_concurrency` — zero matches | No action |
| Reserved-word collisions (`role`, `last_value`, etc. became reserved in 8.0) | **None — already safe** | Every identifier in the dump is backtick-quoted, including `` `role` `` and `` `last_value` `` | No action |
| `NO_AUTO_CREATE_USER` sql_mode removed (implicit user creation via bare `GRANT` also removed) | **None found** | Only one `GRANT` in the entire dump set (inside `CreateDatabasesAndUsers`), and it follows an explicit `CREATE USER` first — no implicit-creation pattern present | No action |
| Auth plugin default change (`mysql_native_password` → `caching_sha2_password`) | **None — already handled** | App uses `mysql2@^3.22.5`, which supports `caching_sha2_password` natively | No action |
| Spatial/SRID changes, `FEDERATED`, `FULLTEXT` | **None found** | Already confirmed absent in §7 | No action |

**Net assessment for a straight 5.7→8.0 jump:** the import itself should succeed once the binlog precaution is taken. The two medium-severity items (implicit sort order, utf8mb4 collation default) are silent behavioral changes, not import failures — they won't stop the dump from loading, but could produce subtly different report ordering or occasional collation errors on the 7 affected tables. Worth a post-import spot-check pass rather than a pre-import blocker.

## 8. Per-company MySQL credentials — `rizo_app` alone is not enough to run the app

Tracing [`rizo/src/lib/db.ts`](../rizo/src/lib/db.ts#L53-L58): in production (`NODE_ENV=production`), company-DB connections do **not** use the `rizo_app` user set up in §4. `getCompanyPool()` looks up `Admin_name`/`user_pwd` from the imported `central_control` row for that tenant and connects with those exact credentials per company — only dev mode falls back to a single shared user.

This means: after importing the real `central_control` data, a matching MySQL user (same username as `Admin_name`, same password as `user_pwd`) must exist on the new droplet for **every company row**, or every company-DB login fails even though the control DB / app login itself works fine. Two ways to resolve, needs a decision:
- Recreate the legacy per-tenant MySQL users to match exactly what's stored in the imported data, or
- (Simpler, recommended) update the `central_control` rows post-import to point at one new shared user/password, and create just that one user with grants on every company DB.

Either way, this is a required step distinct from anything in §1-7 above — don't treat the MySQL setup as "done" once `rizo_app` exists and the schema imports cleanly.

## Summary — action items before importing the real dump

1. `CREATE USER` + `GRANT` for the app's runtime MySQL account — do this first, independently of the dump.
2. Don't pre-run `CREATE DATABASE ... utf8mb4` — let the dump create the DB (latin1, matching legacy) to avoid both the collation mismatch and the "database exists" failure.
3. Decide the fate of `CreateDatabasesAndUsers` (§4) before go-live — if kept, it needs its own elevated-privilege account, separate from the app's scoped runtime user, and its hardcoded credentials should be regenerated.
4. Grant the app user on `mypayrol_control_db` **and** every company DB, not just the newest one.
5. Diff the old server's `sql_mode`/charset config against stock MySQL 8.0 defaults before cutover, in case the legacy app depends on a laxer setting.
6. Resolve §8: either recreate every legacy per-tenant MySQL user matching `central_control.Admin_name`/`user_pwd`, or repoint those rows at one new shared user — without this, company-DB logins fail in production even after everything else above is done.
