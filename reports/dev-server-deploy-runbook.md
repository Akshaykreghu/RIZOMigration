# Dev Server Deploy Runbook — rizo-dev-server-01

Picks up exactly where `rizo-dev-server-setup.txt`'s §8 pending list left off. Target: `dev-server-01` (`143.110.183.207`, Ubuntu 24.04, MySQL 8.0.46 configured to match legacy 5.7.44's `sql_mode`/charset). Run everything as `rizoadmin` unless a step says `sudo mysql` (admin DB access via `auth_socket`, no password).

Two parts:
- **Part A** — clone + build + run the app itself (repo URL known, `rizo_app`'s password from setup log §5 is the only remaining input needed).
- **Part B** — import the MySQL schema/data dump and wire it up to the app (blocked on the coworker delivering the dump).

The app will start after Part A alone, but login/data features won't work until Part B is done — these are separate milestones, not sequential requirements of each other.

---

## Part A — Deploy the app

### A1. Clone and install
```bash
git clone https://github.com/Akshaykreghu/rizo.git rizo-app
cd rizo-app/rizo
npm ci
```
Repo confirmed public — no deploy key/PAT needed for the clone.

### A2. Create `.env.local` (never commit this file)
```bash
nano .env.local
```
```bash
CONTROL_DB_HOST=localhost
CONTROL_DB_USER=rizo_app
CONTROL_DB_PASSWORD=<the password set when rizo_app was created, setup log §5>
CONTROL_DB_NAME=mypayrol_control_db

COMPANY_DB_HOST=localhost

# generate fresh, do not reuse any local/dev secret: openssl rand -base64 32
NEXTAUTH_SECRET=<generate-a-real-32+char-secret>
NEXTAUTH_URL=https://dev.rizo.one

UPLOAD_DIR=./public/uploads
NODE_ENV=production
```
```bash
chmod 600 .env.local
```

### A3. Build and run under PM2
```bash
npm run build
pm2 start npm --name rizo -- start
pm2 save
pm2 startup    # run the printed sudo command to persist across reboots
```
`next start` defaults to port 3000 (no custom port script in `package.json`) — matches Nginx's proxy target below, and UFW already only opens 22/80/443, so 3000 stays internal-only.

### A4. Nginx reverse proxy for `dev.rizo.one`
DNS already points at this droplet (setup log §8). Same pattern as the working Adminer block, `proxy_pass` instead of `fastcgi_pass`:
```bash
sudo nano /etc/nginx/sites-available/dev.rizo.one
```
```nginx
server {
    listen 80;
    server_name dev.rizo.one;

    location / {
        proxy_pass http://127.0.0.1:3000;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection 'upgrade';
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
        proxy_cache_bypass $http_upgrade;
    }

    client_max_body_size 20M;
}
```
```bash
sudo ln -s /etc/nginx/sites-available/dev.rizo.one /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

### A5. SSL
```bash
sudo certbot --nginx -d dev.rizo.one
```

### A6. Verify Part A
```bash
pm2 status                         # rizo -> online
curl -I https://dev.rizo.one       # 200/redirect, valid cert, no warning
```
Browser: `https://dev.rizo.one` loads the login page. Login itself will fail until Part B is done — that's expected at this point.

---

## Part B — Import the DB dump

### B1. Binlog precaution (once, admin account)
MySQL 8.0.26+ (this droplet is 8.0.46) defaults binary logging to ON, which blocks non-`DETERMINISTIC` `CREATE FUNCTION` statements (~half of the ~90 in the dumps) unless this is set first:
```bash
sudo mysql -e "SHOW VARIABLES LIKE 'log_bin';"
# if ON:
sudo mysql -e "SET GLOBAL log_bin_trust_function_creators = 1;"
```

### B2. Copy dump files onto the droplet
```bash
scp -i <key> mypayrol_control_db.sql rizoadmin@143.110.183.207:~/
scp -i <key> mypayrol_<company>.sql rizoadmin@143.110.183.207:~/   # repeat per company DB
```

### B3. Import as admin — not as `rizo_app`
Each dump's own unconditional `CREATE DATABASE ... latin1` needs privileges `rizo_app` deliberately doesn't have. Let the dump create its own DB (matching the server's latin1 config from setup log §4) — do **not** pre-create these databases yourself first, that's the sequencing bug the earlier report flagged (no `IF NOT EXISTS`, would fail).
```bash
sudo mysql < ~/mypayrol_control_db.sql
sudo mysql < ~/mypayrol_<company>.sql        # repeat per company DB
```
Note: importing `mypayrol_control_db.sql` also creates the `CreateDatabasesAndUsers` procedure as part of the schema — that's just a definition, it doesn't run anything. Leave it dormant; don't `CALL` it until the open product decision (dead code vs. live admin tool) is actually made.

### B4. Grant `rizo_app` on everything that now exists
```bash
sudo mysql -e "GRANT ALL PRIVILEGES ON mypayrol_control_db.* TO 'rizo_app'@'localhost';"
sudo mysql -e "GRANT ALL PRIVILEGES ON mypayrol_<company>.* TO 'rizo_app'@'localhost';"   # repeat per company DB
sudo mysql -e "FLUSH PRIVILEGES;"
```
`rizo_app` needs **both** the control DB and every company DB — company-DB stored procedures write back to `mypayrol_control_db.*` directly by fully-qualified name.

### B5. Resolve the per-company credential mismatch
In production mode, `getCompanyPool()` in `rizo/src/lib/db.ts` connects to each company DB using `Admin_name`/`user_pwd` **from the imported `central_control` row**, not `rizo_app` — this is what actually blocks logins even after B3/B4 succeed. Simpler than recreating legacy per-tenant MySQL accounts: repoint those rows at the one user just granted.
```bash
sudo mysql mypayrol_control_db -e \
  "UPDATE central_control SET Admin_name='rizo_app', user_pwd='<rizo_app's actual password>' WHERE active='active';"
```
Only do this after B4's grants are in place for every company DB a given row points at.

### B6. Verify Part B
```bash
mysql -u rizo_app -p mypayrol_control_db -e "SHOW TABLES;" | head
mysql -u rizo_app -p mypayrol_<company> -e "SHOW TABLES;" | head
```
Then, with Part A's app already running: log in through `https://dev.rizo.one` with a real test account and confirm it reaches `/dashboard`.

### B7. Post-import spot check (not blocking)
- 7 tables declared `utf8mb4` with no explicit collation may have picked up `utf8mb4_0900_ai_ci` instead of the source's `utf8mb4_general_ci` — only matters if an "Illegal mix of collations" error shows up; fix reactively with `ALTER TABLE ... COLLATE=utf8mb4_general_ci`.
- ~70 bare `GROUP BY` queries (no explicit `ORDER BY`) may return rows in a different order than the legacy app did — cosmetic, worth a glance during testing, not a blocker.

---

## Still open after both parts — not resolved by this runbook
- Product decision: is `CreateDatabasesAndUsers` a still-wanted admin tool or dead code? Its hardcoded password (`Localhost&*()`) and SHA1 hash need regenerating either way before it's ever invoked against real data.
- Full `my.cnf` diff against the legacy server, beyond the `sql_mode`/charset settings already matched in setup log §4, in case other non-default settings exist.
